/* Modo GitHub Pages: "servidor" dentro del navegador.
 *
 * Reemplaza a MySQL + api/progress.php guardando el perfil en localStorage:
 * XP, racha, pasos resueltos, lecciones, logros y estadísticas. La lógica replica la del
 * servidor (includes/gamification.php y api/progress.php) usando window.CATALOGO,
 * que genera tools/build_static.php. También dibuja el perfil, el ranking entre amigos,
 * el registro y el panel de inicio, y ajusta el mapa y las lecciones al progreso local.
 */
const Local = (() => {
  const C = window.CATALOGO;
  const CLAVE = 'pyaprende:perfil';
  const AMIGOS = 'pyaprende:amigos';
  const E = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const url = (p, slug) => (p === 'lesson' ? `lesson-${slug}.html` : `${p}.html`);

  // ---------- Almacenamiento ----------
  function leer() {
    try { const p = JSON.parse(localStorage.getItem(CLAVE)); return p && p.nombre ? p : null; } catch (e) { return null; }
  }
  function guardar(p) {
    try { localStorage.setItem(CLAVE, JSON.stringify(p)); } catch (e) { /* sin almacenamiento */ }
  }
  function crear(nombre, avatar) {
    const p = { version: 1, nombre, avatar, xp: 0, racha: 0, ultimo_dia: null, pasos: {}, lecciones: {}, logros: {}, stats: { runs: 0, charts: 0 }, creado: new Date().toISOString() };
    guardar(p);
    return p;
  }

  // ---------- Reglas del juego (espejo del servidor) ----------
  function nivel(xp) {
    let n = 0;
    C.niveles.forEach((l, i) => { if (xp >= l[0]) n = i; });
    const cur = C.niveles[n], sig = C.niveles[n + 1];
    return {
      number: n + 1, name: cur[1], icon: cur[2], xp, next_xp: sig ? sig[0] : null,
      pct: sig ? Math.floor(100 * (xp - cur[0]) / (sig[0] - cur[0])) : 100,
    };
  }
  const hoy = (d = new Date()) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  function tocarRacha(p) {
    const h = hoy();
    if (p.ultimo_dia === h) return;
    const ayer = new Date(); ayer.setDate(ayer.getDate() - 1);
    p.racha = p.ultimo_dia === hoy(ayer) ? p.racha + 1 : 1;
    p.ultimo_dia = h;
  }
  const terminadas = (p) => Object.keys(p.lecciones);
  const deModo = (modo) => C.orden.filter((s) => C.lecciones[s].modo === modo);
  const todas = (lista, hechas) => lista.every((s) => hechas.includes(s));

  function rangos(p) {
    const r = {};
    Object.entries(p.pasos).forEach(([slug, pasos]) => {
      if (Object.keys(pasos).length) r[slug] = 'progress';
    });
    terminadas(p).forEach((slug) => {
      const vistas = Object.values(p.pasos[slug] || {}).filter((x) => x.sol).length;
      r[slug] = vistas === 0 ? 'perfect' : vistas === 1 ? 'great' : 'cleared';
    });
    return r;
  }

  function revisarLogros(p, eventos = []) {
    const hechas = terminadas(p);
    const gana = {
      primer_codigo: p.stats.runs >= 1,
      cien_ejecuciones: p.stats.runs >= 100,
      primera_leccion: hechas.length >= 1,
      cinco_lecciones: hechas.length >= 5,
      diez_lecciones: hechas.length >= 10,
      primer_grafico: p.stats.charts >= 1,
      racha_3: p.racha >= 3,
      racha_7: p.racha >= 7,
      xp_1000: p.xp >= 1000,
      ngplus_inicio: deModo('ngplus').some((s) => hechas.includes(s)),
      platino: todas([...deModo('main'), ...deModo('ngplus')], hechas),
      infierno_inicio: deModo('infierno').some((s) => hechas.includes(s)),
    };
    C.modulos.forEach((m) => { if (m.logro) gana[m.logro] = todas(m.lecciones, hechas); });
    eventos.forEach((e) => { gana[e] = true; });
    const nuevos = [];
    Object.entries(gana).forEach(([code, ok]) => {
      if (ok && !p.logros[code] && C.logros[code]) {
        p.logros[code] = new Date().toISOString();
        const [icon, title, desc] = C.logros[code];
        nuevos.push({ code, icon, title, desc });
      }
    });
    return nuevos;
  }

  /** Misma interfaz y respuestas que api/progress.php. */
  function api(action, data = {}) {
    const p = leer();
    if (!p) return null;
    let ganado = 0;
    const extra = {};
    const eventos = [];
    const lec = C.lecciones[data.lesson];
    switch (action) {
      case 'step': {
        if (!lec) return { error: 'Paso inválido' };
        let xp = lec.pasos[data.step] || 0;
        if (data.used_solution) xp = Math.floor(xp / 5);
        p.pasos[data.lesson] = p.pasos[data.lesson] || {};
        if (!p.pasos[data.lesson][data.step]) {
          p.pasos[data.lesson][data.step] = { xp, sol: !!data.used_solution };
          ganado = xp;
        }
        break;
      }
      case 'lesson': {
        if (!lec) return { error: 'Lección inválida' };
        const hechos = Object.keys(p.pasos[data.lesson] || {});
        const faltan = Object.keys(lec.pasos).filter((i) => !hechos.includes(i));
        if (faltan.length) return { error: 'Aún faltan pasos por resolver', missing: faltan };
        if (!p.lecciones[data.lesson]) {
          p.lecciones[data.lesson] = new Date().toISOString();
          ganado = lec.bono;
        }
        if (lec.modo === 'infierno' && +data.vidas === 3) eventos.push('intocable');
        const i = C.orden.indexOf(data.lesson);
        extra.next = C.orden[i + 1] || null;
        break;
      }
      case 'reset': {
        if (!lec || lec.modo !== 'infierno') return { error: 'Solo las lecciones del Infierno se reinician' };
        if (p.lecciones[data.lesson]) return { ok: true, xp_perdida: 0 };
        const perdida = Object.values(p.pasos[data.lesson] || {}).reduce((a, x) => a + x.xp, 0);
        delete p.pasos[data.lesson];
        p.xp = Math.max(0, p.xp - perdida);
        extra.xp_perdida = perdida;
        eventos.push('primera_muerte');
        break;
      }
      case 'run':
        p.stats.runs++;
        if (data.chart) p.stats.charts++;
        break;
      default:
        return { error: 'Acción desconocida' };
    }
    p.xp += ganado;
    tocarRacha(p);
    const nuevos = revisarLogros(p, eventos);
    guardar(p);
    const antes = nivel(Math.max(0, p.xp - ganado));
    const despues = nivel(p.xp);
    return { ...extra, ok: true, xp_gained: ganado, xp: p.xp, streak: p.racha, level: despues, level_up: despues.number > antes.number, achievements: nuevos };
  }

  // ---------- Código de jugador (para el ranking entre amigos) ----------
  function codigo(p) {
    const datos = { n: p.nombre, a: p.avatar, x: p.xp, l: terminadas(p).length, r: p.racha, t: Date.now() };
    return 'PYA1.' + btoa(unescape(encodeURIComponent(JSON.stringify(datos))));
  }
  function leerCodigo(txt) {
    const m = String(txt).trim().match(/^PYA1\.([A-Za-z0-9+/=]+)$/);
    if (!m) return null;
    try {
      const d = JSON.parse(decodeURIComponent(escape(atob(m[1]))));
      return d && typeof d.n === 'string' && Number.isFinite(d.x) ? d : null;
    } catch (e) { return null; }
  }
  function amigos() {
    try { return JSON.parse(localStorage.getItem(AMIGOS)) || {}; } catch (e) { return {}; }
  }
  function guardarAmigos(a) {
    try { localStorage.setItem(AMIGOS, JSON.stringify(a)); } catch (e) { /* */ }
  }

  // ---------- Dibujo de páginas ----------
  function header(p) {
    const cont = document.getElementById('local-user');
    if (!cont) return;
    if (p) {
      const lv = nivel(p.xp);
      cont.outerHTML = `<span class="pill streak" title="Racha de días">🔥 <b id="hdr-streak">${p.racha}</b></span>
        <span class="pill xp" title="Experiencia">⭐ <b id="hdr-xp">${p.xp}</b> XP</span>
        <a class="avatar" href="${url('profile')}" title="${E(p.nombre)} · ${E(lv.name)}">${E(p.avatar)}</a>`;
    } else {
      cont.outerHTML = `<a class="btn btn-primary btn-sm" href="${url('register')}">Crear perfil</a>`;
    }
  }

  function avisosInvitado(p) {
    document.querySelectorAll('.guest-alert').forEach((el) => {
      if (p) el.remove();
      else el.innerHTML = `Aún no tienes perfil: <a href="${url('register')}">créalo en 5 segundos</a> para guardar tu progreso, ganar XP y logros (se guarda en este navegador).`;
    });
  }

  /** Mapa de lecciones: rangos, lección actual, candados y contadores. */
  function mapa(p) {
    const lista = document.getElementById('song-list');
    if (!lista) return;
    const hechas = p ? terminadas(p) : [];
    const r = p ? rangos(p) : {};
    const actual = C.orden.find((s) => !hechas.includes(s)) || C.orden[0];
    lista.querySelectorAll('.song-row').forEach((row) => {
      const slug = row.dataset.slug;
      const lec = C.lecciones[slug];
      row.classList.toggle('selected', slug === actual);
      row.querySelector('.song-badge').innerHTML = !r[slug] && slug === actual
        ? '<span class="new-badge">New</span>' : `<span class="stage-num">${E(lec.etapa)}</span>`;
      row.querySelector('.song-rank').innerHTML = r[slug] ? `<span class="rank rank-${r[slug]}">${E(C.rangos[r[slug]])}</span>` : '';
    });
    const tier = C.lecciones[actual].tier;
    document.querySelectorAll('.tier-tab').forEach((t) => t.classList.toggle('active', t.dataset.tier === tier));
    const ngAbierto = todas(deModo('main'), hechas);
    const infAbierto = ngAbierto && todas(deModo('ngplus'), hechas);
    const candado = (sel, abierto) => {
      const t = document.querySelector(`.tier-tab[data-tier="${sel}"] .tab-lock`);
      if (t) t.hidden = abierto;
    };
    candado('ngplus', ngAbierto);
    candado('infierno', infAbierto);
    const pancarta = (sel, abierto) => document.querySelectorAll(`${sel} [data-lock]`).forEach((el) => { el.hidden = (el.dataset.lock === 'open') !== abierto; });
    pancarta('.ngplus-banner', ngAbierto);
    pancarta('.infierno-banner', infAbierto);
    const cuenta = (modos) => modos.reduce((a, m) => a + deModo(m).filter((s) => hechas.includes(s)).length, 0);
    document.querySelectorAll('[data-cuenta="main"]').forEach((el) => { el.textContent = cuenta(['main']); });
    document.querySelectorAll('[data-cuenta="main+ngplus"]').forEach((el) => { el.textContent = cuenta(['main', 'ngplus']); });
  }

  /** En una lección: recuperar los pasos ya resueltos. */
  function leccion(p) {
    const L = window.LESSON;
    if (!L || !p) return;
    L.doneSteps = Object.keys(p.pasos[L.slug] || {}).map(Number);
    L.lessonDone = !!p.lecciones[L.slug];
  }

  function panel(p) {
    const main = document.querySelector('main');
    if (!p || !document.querySelector('.hero')) return;
    const hechas = terminadas(p);
    const lv = nivel(p.xp);
    const sig = C.orden.find((s) => !hechas.includes(s)) || C.orden[0];
    const lec = C.lecciones[sig];
    const mod = C.modulos.find((m) => m.slug === lec.modulo);
    const tips = [
      'Los errores no son fracasos: son pistas. Léelos con calma.',
      'Escribir código poquito cada día vale más que un maratón al mes. ¡Cuida tu racha! 🔥',
      'Ctrl + Enter ejecuta tu código en cualquier editor del sitio.',
      'En Python, las posiciones empiezan en 0.',
      'Pandas se llama así por "panel data", no por el oso. 🐼',
    ];
    const tip = tips[new Date().getDate() % tips.length];
    main.className = 'container';
    main.innerHTML = `<section class="dash">
      <div class="card dash-hello"><div class="mascot">🐍</div><div><h1>¡Hola, ${E(p.nombre)}!</h1><p class="muted">${E(tip)}</p></div></div>
      <div class="dash-grid">
        <div class="card level-card"><div class="level-icon">${lv.icon}</div><div class="level-body">
          <div class="muted small">Nivel ${lv.number}</div><h2>${E(lv.name)}</h2>
          <div class="bar"><div style="width:${lv.pct}%"></div></div>
          <div class="muted small">${lv.xp} XP${lv.next_xp ? ` · faltan ${lv.next_xp - lv.xp} para el siguiente nivel` : ' · ¡nivel máximo!'}</div></div></div>
        <div class="card stat-card"><div class="big">🔥 ${p.racha}</div><div class="muted">días de racha</div></div>
        <div class="card stat-card"><div class="big">📚 ${hechas.length}/${C.orden.length}</div><div class="muted">lecciones</div></div>
        <div class="card stat-card"><div class="big">🏅 ${Object.keys(p.logros).length}</div><div class="muted">logros</div></div>
      </div>
      <div class="card continue-card is-${lec.modo}" style="--mod:${mod.color}">
        <div><div class="muted small">${hechas.length ? 'Continúa donde te quedaste' : 'Tu aventura empieza aquí'} · ${E(mod.titulo)}</div>
        <h2>${mod.icono} ${E(lec.titulo)}</h2><p class="muted">${E(lec.resumen)}</p></div>
        <a class="btn btn-primary btn-lg" href="${url('lesson', sig)}">¡Vamos! ▶</a>
      </div></section>`;
  }

  function registro(p) {
    const form = document.getElementById('local-register');
    if (!form) return;
    if (p) {
      const aviso = document.getElementById('perfil-existente');
      aviso.hidden = false;
      aviso.innerHTML = `Ya tienes el perfil <b>${E(p.avatar)} ${E(p.nombre)}</b> con ${p.xp} XP. <a href="${url('learn')}">Seguir jugando</a> · Si creas uno nuevo, <b>se reemplazará</b>.`;
    }
    form.addEventListener('submit', (ev) => {
      ev.preventDefault();
      const nombre = form.nombre.value.trim().slice(0, 24);
      if (nombre.length < 2) return;
      if (leer() && !confirm('Esto reemplaza tu perfil actual y su progreso en este navegador. ¿Continuar?')) return;
      crear(nombre, form.avatar.value || '🐍');
      location.href = url('lesson', C.orden[0]);
    });
  }

  function perfil(p) {
    const cont = document.getElementById('local-profile');
    if (!cont) return;
    if (!p) {
      cont.innerHTML = `<div class="card auth-card center"><h1>Aún no tienes perfil</h1><p class="muted">Crea uno para guardar tu progreso.</p><a class="btn btn-primary" href="${url('register')}">Crear perfil</a></div>`;
      return;
    }
    const lv = nivel(p.xp);
    const hechas = terminadas(p);
    cont.innerHTML = `
      <div class="card profile-card"><div class="profile-avatar">${E(p.avatar)}</div><div class="profile-info">
        <h1>${E(p.nombre)}</h1><div class="muted">Nivel ${lv.number} · ${lv.icon} ${E(lv.name)}</div>
        <div class="bar"><div style="width:${lv.pct}%"></div></div><div class="muted small">${lv.xp} XP${lv.next_xp ? ' / ' + lv.next_xp : ''}</div></div></div>
      <div class="dash-grid">
        <div class="card stat-card"><div class="big">🔥 ${p.racha}</div><div class="muted">días de racha</div></div>
        <div class="card stat-card"><div class="big">📚 ${hechas.length}</div><div class="muted">lecciones completadas</div></div>
        <div class="card stat-card"><div class="big">▶ ${p.stats.runs}</div><div class="muted">veces que ejecutaste código</div></div>
        <div class="card stat-card"><div class="big">📈 ${p.stats.charts}</div><div class="muted">gráficos creados</div></div>
      </div>
      <h2>🏅 Logros (${Object.keys(p.logros).length}/${Object.keys(C.logros).length})</h2>
      <div class="badges">${Object.entries(C.logros).map(([code, [icon, title, desc]]) => {
        const tiene = !!p.logros[code];
        return `<div class="badge ${tiene ? 'unlocked' : 'locked'}" title="${E(desc)}"><div class="badge-icon">${tiene ? icon : '🔒'}</div><b>${E(title)}</b><small class="muted">${E(desc)}</small></div>`;
      }).join('')}</div>
      <h2>💾 Respaldo de tu partida</h2>
      <div class="card">
        <p class="muted">Tu progreso vive en <b>este navegador</b>. Descárgalo para guardarlo o para seguir en otra computadora.</p>
        <div class="runner-actions">
          <button class="btn btn-primary" id="lp-exportar">⬇️ Descargar mi progreso</button>
          <label class="btn btn-ghost">⬆️ Cargar un progreso<input type="file" accept=".json,application/json" id="lp-importar" hidden></label>
          <button class="btn btn-ghost" id="lp-borrar">🗑️ Borrar perfil</button>
        </div>
      </div>`;
    document.getElementById('lp-exportar').onclick = () => {
      const blob = new Blob([JSON.stringify(leer(), null, 2)], { type: 'application/json' });
      const a = Object.assign(document.createElement('a'), { href: URL.createObjectURL(blob), download: `pyaprende-${p.nombre}.json` });
      a.click();
      setTimeout(() => URL.revokeObjectURL(a.href), 3000);
    };
    document.getElementById('lp-importar').onchange = async (ev) => {
      const f = ev.target.files[0];
      if (!f) return;
      try {
        const nuevo = JSON.parse(await f.text());
        if (!nuevo || !nuevo.nombre || typeof nuevo.xp !== 'number' || !nuevo.pasos || !nuevo.lecciones) throw new Error('formato');
        if (!confirm(`¿Cargar el progreso de ${nuevo.nombre} (${nuevo.xp} XP)? Reemplaza el de este navegador.`)) return;
        guardar(Object.assign({ stats: { runs: 0, charts: 0 }, logros: {}, racha: 0 }, nuevo));
        location.reload();
      } catch (e) {
        App.toast('😬 Ese archivo no es un progreso de PyAprende válido.', 'info', 4000);
      }
    };
    document.getElementById('lp-borrar').onclick = () => {
      if (!confirm('¿Borrar tu perfil y todo tu progreso de este navegador? No se puede deshacer (salvo que tengas un respaldo).')) return;
      localStorage.removeItem(CLAVE);
      location.href = url('index');
    };
  }

  function ranking(p) {
    const cont = document.getElementById('local-ranking');
    if (!cont) return;
    const lista = Object.values(amigos()).map((a) => ({ ...a, amigo: true }));
    if (p) lista.push({ n: p.nombre, a: p.avatar, x: p.xp, l: terminadas(p).length, r: p.racha, yo: true });
    lista.sort((a, b) => b.x - a.x);
    cont.innerHTML = `
      <p class="muted">En la versión de GitHub Pages cada quien guarda su progreso en su navegador. Para competir, <b>intercambien sus códigos de jugador</b>: pega aquí el de tu amiga (y ella el tuyo). Actualízalo cuando quieran comparar de nuevo.</p>
      <div class="card ranking">${lista.length ? lista.map((r, i) => {
        const lv = nivel(r.x);
        return `<div class="rank-row ${r.yo ? 'me' : ''}"><div class="rank-pos">${['🥇', '🥈', '🥉'][i] || i + 1}</div><div class="rank-avatar">${E(r.a)}</div>
          <div class="rank-name"><b>${E(r.n)}${r.yo ? ' (tú)' : ''}</b><div class="muted small">${lv.icon} ${E(lv.name)} · 📚 ${r.l} · 🔥 ${r.r}${r.amigo ? ` · actualizado ${new Date(r.t).toLocaleDateString('es')}` : ''}</div></div>
          <div class="rank-xp">${r.x} XP</div>${r.amigo ? `<button class="btn btn-ghost btn-sm" data-quitar="${E(r.n)}" title="Quitar">✕</button>` : ''}</div>`;
      }).join('') : '<p class="muted center">Todavía no hay nadie. ¡Crea tu perfil!</p>'}</div>
      <div class="card" style="margin-top:20px">
        ${p ? `<h3>📤 Tu código de jugador</h3><p class="muted small">Cópialo y mándaselo a tu amiga por WhatsApp o como prefieran.</p>
        <textarea id="lr-mio" readonly rows="2" style="width:100%;font-family:'JetBrains Mono',monospace;background:#000;color:#fff;border:2px solid #444;padding:8px">${codigo(p)}</textarea>
        <div class="runner-actions"><button class="btn btn-primary btn-sm" id="lr-copiar">📋 Copiar mi código</button></div>` : `<p><a href="${url('register')}">Crea tu perfil</a> para tener tu propio código.</p>`}
        <h3>📥 Agregar el código de una amiga</h3>
        <textarea id="lr-otro" rows="2" placeholder="Pega aquí un código que empiece con PYA1." style="width:100%;font-family:'JetBrains Mono',monospace;background:#000;color:#fff;border:2px solid #444;padding:8px"></textarea>
        <div class="runner-actions"><button class="btn btn-primary btn-sm" id="lr-agregar">➕ Agregar al ranking</button></div>
      </div>`;
    const copiar = document.getElementById('lr-copiar');
    if (copiar) copiar.onclick = async () => {
      const t = document.getElementById('lr-mio');
      try { await navigator.clipboard.writeText(t.value); } catch (e) { t.select(); document.execCommand('copy'); }
      App.toast('📋 ¡Código copiado! Mándaselo a tu amiga.', 'info', 3000);
    };
    document.getElementById('lr-agregar').onclick = () => {
      const d = leerCodigo(document.getElementById('lr-otro').value);
      if (!d) { App.toast('😬 Ese código no es válido. Debe empezar con PYA1.', 'info', 3500); return; }
      if (p && d.n === p.nombre) { App.toast('Ese es tu propio código 😄', 'info', 3000); return; }
      const a = amigos();
      a[d.n] = d;
      guardarAmigos(a);
      App.sfx('logro');
      ranking(leer());
    };
    cont.querySelectorAll('[data-quitar]').forEach((b) => b.addEventListener('click', () => {
      const a = amigos();
      delete a[b.dataset.quitar];
      guardarAmigos(a);
      ranking(leer());
    }));
  }

  // ---------- Arranque (se ejecuta antes de app.js, select.js y lesson.js) ----------
  const p = leer();
  window.PYAPRENDE.logged = !!p;
  header(p);
  avisosInvitado(p);
  mapa(p);
  leccion(p);
  panel(p);
  registro(p);
  perfil(p);
  ranking(p);

  return { api, leer, nivel };
})();
