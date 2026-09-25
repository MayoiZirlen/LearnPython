/* Laboratorio: subir, explorar y descargar archivos del usuario (Excel, CSV…). */
(function () {
  const lista = document.getElementById('file-list');
  const input = document.getElementById('file-input');
  const zona = document.getElementById('dropzone');
  const runnerEl = document.querySelector('.lab [data-runner]');
  const E = App.escape;
  const MAX_MB = 30;
  // Archivos de ejemplo que trae el sitio (se pueden usar pero no borrar).
  const EJEMPLOS = new Set(Py.DATASETS);

  const icono = (n) => (/\.(xlsx|xlsm|xls)$/i.test(n) ? '📗' : /\.csv$|\.tsv$/i.test(n) ? '📄' : /\.(png|jpg)$/i.test(n) ? '🖼️' : '📦');
  const tamano = (b) => (b > 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB');

  function editor() { return runnerEl._runner.editor; }

  /** Código de arranque para explorar un archivo recién subido. */
  function codigoExplorar(nombre) {
    const n = JSON.stringify(nombre);
    if (/\.(xlsx|xlsm|xls)$/i.test(nombre)) {
      return `import pandas as pd

archivo = ${n}

# 1) ¿Qué hojas tiene?
hojas = pd.read_excel(archivo, sheet_name=None)
for nombre, tabla in hojas.items():
    print(f"📄 Hoja '{nombre}': {tabla.shape[0]} filas × {tabla.shape[1]} columnas")

# 2) Miramos la primera hoja
df = pd.read_excel(archivo, sheet_name=0)
print("\\nColumnas:", list(df.columns))
print("Datos faltantes:", int(df.isna().sum().sum()))
df.head(10)
`;
    }
    const sep = /\.tsv$/i.test(nombre) ? ', sep="\\t"' : '';
    return `import pandas as pd

archivo = ${n}
df = pd.read_csv(archivo${sep})   # si se ve raro, prueba sep=";" o encoding="latin-1"

print(f"{df.shape[0]} filas × {df.shape[1]} columnas")
print("Columnas:", list(df.columns))
print("Datos faltantes:", int(df.isna().sum().sum()))
df.head(10)
`;
  }

  async function refrescar() {
    let archivos;
    try { archivos = await Py.listar(); } catch (e) { return; }
    document.dispatchEvent(new CustomEvent('lab-archivos', { detail: archivos }));
    lista.innerHTML = archivos.map((a) => {
      const propio = !EJEMPLOS.has(a.nombre);
      const datos = /\.(xlsx|xlsm|xls|csv|tsv)$/i.test(a.nombre);
      return `<li class="file ${propio ? 'mine' : ''}">
        <span class="file-icon">${icono(a.nombre)}</span>
        <span class="file-name" title="${E(a.nombre)}">${E(a.nombre)}<small>${tamano(a.tamano)}${propio ? '' : ' · ejemplo'}</small></span>
        <span class="file-actions">
          ${datos ? `<button data-explorar="${E(a.nombre)}" title="Generar código para explorarlo">🔍</button>` : ''}
          <button data-bajar="${E(a.nombre)}" title="Descargar">⬇️</button>
          ${propio ? `<button data-borrar="${E(a.nombre)}" title="Quitar">🗑️</button>` : ''}
        </span>
      </li>`;
    }).join('') || '<li class="muted small">Sin archivos todavía.</li>';
  }

  async function subir(files) {
    for (const f of files) {
      if (f.size > MAX_MB * 1048576) {
        App.toast(`😬 ${E(f.name)} pesa más de ${MAX_MB} MB. Prueba con un archivo más pequeño.`, 'info', 5000);
        continue;
      }
      try {
        const nombre = await Py.subir(f);
        App.sfx('go');
        App.toast(`<span class="toast-icon">${icono(nombre)}</span><div><b>${E(nombre)}</b> listo para usar.<br>Generé código para explorarlo 👇</div>`, 'achievement', 4500);
        editor().setValue(codigoExplorar(nombre));
        document.dispatchEvent(new CustomEvent('lab-subido', { detail: nombre }));
      } catch (e) {
        App.toast('No pude cargar ' + E(f.name) + ': ' + E(e.message), 'info', 5000);
      }
    }
    refrescar();
  }

  input.addEventListener('change', () => { subir([...input.files]); input.value = ''; });
  ['dragenter', 'dragover'].forEach((t) => zona.addEventListener(t, (ev) => { ev.preventDefault(); zona.classList.add('over'); }));
  ['dragleave', 'drop'].forEach((t) => zona.addEventListener(t, (ev) => { ev.preventDefault(); zona.classList.remove('over'); }));
  zona.addEventListener('drop', (ev) => subir([...ev.dataTransfer.files]));

  lista.addEventListener('click', async (ev) => {
    const b = ev.target.closest('button');
    if (!b) return;
    try {
      if (b.dataset.explorar) { App.sfx('tab'); modo('codigo', false); editor().setValue(codigoExplorar(b.dataset.explorar)); }
      if (b.dataset.bajar) await Py.descargar(b.dataset.bajar);
      if (b.dataset.borrar && confirm(`¿Quitar ${b.dataset.borrar} del laboratorio?`)) { await Py.borrar(b.dataset.borrar); refrescar(); }
    } catch (e) { App.toast('😬 ' + E(e.message), 'info'); }
  });

  document.querySelectorAll('.tool').forEach((b) => b.addEventListener('click', () => {
    App.sfx('tab');
    modo('codigo', false);
    document.querySelectorAll('.tool.on').forEach((x) => x.classList.remove('on'));
    b.classList.add('on');
  }));

  // Tras cada ejecución, el código pudo crear archivos nuevos (to_excel, to_csv…).
  runnerEl.addEventListener('py-ejecutado', refrescar);
  document.addEventListener('py-estado', (ev) => { if (ev.detail.estado === 'listo') refrescar(); });
  document.addEventListener('lab-refrescar', refrescar);

  // Pestañas: Asistente de Excel / Código libre (se recuerda la última elegida)
  const pestanas = [...document.querySelectorAll('.mode-tab')];
  function modo(m, sonar) {
    pestanas.forEach((t) => t.classList.toggle('active', t.dataset.modo === m));
    document.querySelectorAll('.lab [data-panel]').forEach((p) => { p.hidden = p.dataset.panel !== m; });
    try { localStorage.setItem('pyaprende:modo-lab', m); } catch (e) { /* */ }
    if (sonar) App.sfx('tab');
    if (m === 'codigo' && runnerEl._runner) setTimeout(() => runnerEl._runner.editor.refresh(), 0);
    document.dispatchEvent(new CustomEvent('lab-modo', { detail: m }));
  }
  pestanas.forEach((t) => t.addEventListener('click', () => modo(t.dataset.modo, true)));
  document.addEventListener('lab-modo', (ev) => {
    if (!pestanas.find((t) => t.dataset.modo === ev.detail && t.classList.contains('active'))) modo(ev.detail, false);
  });
  let inicial = 'asistente';
  try { inicial = localStorage.getItem('pyaprende:modo-lab') || 'asistente'; } catch (e) { /* */ }
  modo(inicial, false);
})();
