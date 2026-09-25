/* Puente entre la página y el worker de Python + componentes de editor y salida. */
const Py = {
  worker: null,
  estado: 'apagado',
  pendientes: new Map(),
  contador: 0,
  LIMITE_MS: 12000,
  DATASETS: ['ventas.csv', 'ventas_sucias.csv', 'clima.csv'],

  iniciar() {
    if (this.worker) return;
    const base = new URL('.', location.href).href;
    let url = window.PYAPRENDE.pyodideUrl;
    if (!/^https?:/.test(url)) url = new URL(url, base).href;
    if (!url.endsWith('/')) url += '/';
    this.worker = new Worker('assets/js/py-worker.js');
    this.worker.onmessage = (ev) => this.mensaje(ev.data);
    this.worker.onerror = () => this.cambiarEstado('error', 'No se pudo iniciar Python');
    this.worker.postMessage({
      tipo: 'iniciar',
      pyodideUrl: url,
      harnessUrl: base + 'assets/py/harness.py',
      datasets: this.DATASETS.map((n) => ({ nombre: n, url: base + 'data/' + n })),
    });
    this.cambiarEstado('cargando', 'Despertando a Python…');
  },

  cambiarEstado(estado, texto) {
    this.estado = estado;
    document.querySelectorAll('[data-py-status]').forEach((el) => {
      el.dataset.state = estado;
      el.textContent = texto;
    });
    document.dispatchEvent(new CustomEvent('py-estado', { detail: { estado, texto } }));
  },

  mensaje(m) {
    const p = this.pendientes.get(m.id);
    if (m.tipo === 'listo') this.cambiarEstado('listo', '🐍 Python listo');
    else if (m.tipo === 'fallo') this.cambiarEstado('error', '⚠️ No se pudo cargar Python. ¿Tienes internet?');
    else if (m.tipo === 'estado') {
      if (p && p.onEstado) p.onEstado(m.texto);
      else if (!m.id) this.cambiarEstado('cargando', m.texto);
    } else if (m.tipo === 'corriendo' && p) {
      if (p.onEstado) p.onEstado('Ejecutando…');
      p.timer = setTimeout(() => this.cortar(m.id), this.LIMITE_MS);
    } else if (m.tipo === 'resultado' && p) {
      clearTimeout(p.timer);
      this.pendientes.delete(m.id);
      p.resolve(m.resultado);
    }
  },

  /** Si el código tarda demasiado (p. ej. un bucle infinito) reiniciamos Python. */
  cortar(id) {
    const p = this.pendientes.get(id);
    this.worker.terminate();
    this.worker = null;
    this.pendientes.forEach((q) => clearTimeout(q.timer));
    this.pendientes.clear();
    if (p) p.resolve({
      stdout: '', imagenes: [], check_ok: p.check ? false : null,
      error: 'TimeoutError: tu código tardó más de ' + this.LIMITE_MS / 1000 + ' segundos',
      pista: '¿Tienes un bucle infinito? Revisa que la condición de tu while llegue a ser False en algún momento.',
    });
    this.iniciar();
  },

  ejecutar(codigo, { check = null, entradas = [], onEstado = null } = {}) {
    this.iniciar();
    const id = ++this.contador;
    return new Promise((resolve) => {
      this.pendientes.set(id, { resolve, onEstado, check });
      this.worker.postMessage({ tipo: 'correr', id, codigo, check, entradas });
    });
  },
};

/** Crea un editor con resaltado (CodeMirror) o un textarea mejorado si no hay internet. */
function crearEditor(textarea, { alEjecutar } = {}) {
  if (window.CodeMirror) {
    const cm = CodeMirror.fromTextArea(textarea, {
      mode: 'python', lineNumbers: true, indentUnit: 4, tabSize: 4,
      indentWithTabs: false, matchBrackets: true, viewportMargin: Infinity,
      extraKeys: {
        Tab: (c) => c.somethingSelected() ? c.indentSelection('add') : c.replaceSelection('    ', 'end'),
        'Shift-Tab': (c) => c.indentSelection('subtract'),
        'Ctrl-Enter': () => alEjecutar && alEjecutar(),
        'Cmd-Enter': () => alEjecutar && alEjecutar(),
      },
    });
    let marca = null;
    return {
      getValue: () => cm.getValue(),
      setValue: (v) => cm.setValue(v),
      onChange: (fn) => cm.on('change', fn),
      marcarLinea(n) {
        if (marca !== null) cm.removeLineClass(marca, 'background', 'line-error');
        marca = null;
        if (n) { marca = n - 1; cm.addLineClass(marca, 'background', 'line-error'); }
      },
      refresh: () => cm.refresh(),
    };
  }
  textarea.classList.add('code-plain');
  textarea.spellcheck = false;
  textarea.addEventListener('keydown', (ev) => {
    if (ev.key === 'Tab') {
      ev.preventDefault();
      textarea.setRangeText('    ', textarea.selectionStart, textarea.selectionEnd, 'end');
    } else if (ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey)) {
      ev.preventDefault();
      alEjecutar && alEjecutar();
    }
  });
  const ajustar = () => { textarea.style.height = 'auto'; textarea.style.height = textarea.scrollHeight + 4 + 'px'; };
  textarea.addEventListener('input', ajustar);
  setTimeout(ajustar);
  return {
    getValue: () => textarea.value,
    setValue: (v) => { textarea.value = v; ajustar(); },
    onChange: (fn) => textarea.addEventListener('input', fn),
    marcarLinea() {},
    refresh: ajustar,
  };
}

/** Dibuja el resultado de una ejecución en un contenedor. */
function mostrarSalida(el, r) {
  const E = App.escape;
  let html = '';
  if (r.stdout) html += `<pre class="out-text">${E(r.stdout)}</pre>`;
  if (r.html) html += `<div class="out-html">${r.html}</div>`;
  (r.imagenes || []).forEach((b64) => { html += `<img class="out-img" alt="Gráfico" src="data:image/png;base64,${b64}">`; });
  if (r.error) {
    html += `<div class="out-error"><div class="out-error-title">😬 ¡Ups! Algo salió mal${r.linea ? ' en la línea ' + r.linea : ''}</div>`
      + `<pre>${E(r.error)}</pre>${r.pista ? `<div class="out-hint">💡 ${E(r.pista)}</div>` : ''}</div>`;
  }
  if (!html) html = '<span class="muted">(Tu código corrió sin imprimir nada. Usa <code>print()</code> para ver resultados.)</span>';
  el.innerHTML = html;
}

/** Componente reutilizable: [data-runner] con [data-code], [data-run] y [data-output]. */
function montarRunner(root) {
  const ta = root.querySelector('[data-code]');
  const out = root.querySelector('[data-output]');
  const btn = root.querySelector('[data-run]');
  const inputs = root.querySelector('[data-inputs]');
  const clave = root.dataset.persist ? 'pyaprende:' + root.dataset.persist : null;
  if (clave) {
    try { const guardado = localStorage.getItem(clave); if (guardado) ta.value = guardado; } catch (e) { /* sin almacenamiento */ }
  }
  const editor = crearEditor(ta, { alEjecutar: () => correr() });
  if (clave) editor.onChange(() => { try { localStorage.setItem(clave, editor.getValue()); } catch (e) { /* ignorar */ } });

  async function correr() {
    if (btn.disabled) return;
    btn.disabled = true;
    const texto = btn.innerHTML;
    btn.innerHTML = '⏳ Ejecutando…';
    out.innerHTML = '<div class="out-loading"><span class="spinner"></span> <span data-msg>Preparando…</span></div>';
    const entradas = inputs ? inputs.value.split('\n').filter((l) => l !== '') : [];
    const r = await Py.ejecutar(editor.getValue(), {
      entradas,
      onEstado: (t) => { const m = out.querySelector('[data-msg]'); if (m) m.textContent = t; },
    });
    mostrarSalida(out, r);
    editor.marcarLinea(r.error ? r.linea : null);
    btn.disabled = false;
    btn.innerHTML = texto;
    App.api('run', { chart: (r.imagenes || []).length > 0 });
    return r;
  }

  btn.addEventListener('click', correr);
  const limpiar = root.querySelector('[data-clear]');
  if (limpiar) limpiar.addEventListener('click', () => { out.innerHTML = ''; });
  return { editor, correr };
}

document.addEventListener('DOMContentLoaded', () => {
  const status = document.createElement('div');
  status.className = 'py-status';
  status.dataset.pyStatus = '';
  document.body.appendChild(status);
  Py.iniciar();

  document.querySelectorAll('[data-runner]').forEach((root) => {
    const r = montarRunner(root);
    root._runner = r;
  });
  document.querySelectorAll('[data-snippet]').forEach((b) => b.addEventListener('click', () => {
    const runner = document.querySelector('[data-runner]')._runner;
    runner.editor.setValue(b.dataset.snippet);
  }));
});
