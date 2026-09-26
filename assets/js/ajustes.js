/* Página de ajustes de sonido. */
(function () {
  const $ = (id) => document.getElementById(id);
  const E = App.escape;
  const MAX_MB = 2;
  let aj = Sonidos.leer();
  const nombresArchivo = {};   // evento -> nombre del archivo propio (si hay)
  const haySitio = Object.keys(Sonidos.SITIO).length > 0;

  function guardar() { Sonidos.guardar(aj); }

  // ---------- Interruptor y volumen ----------
  $('aj-activo').checked = Sonidos.activo();
  $('aj-activo').onchange = (ev) => { Sonidos.setActivo(ev.target.checked); if (ev.target.checked) Sonidos.reproducir('go'); };
  $('aj-volumen').value = aj.volumen;
  $('aj-volumen-num').textContent = aj.volumen + '%';
  $('aj-volumen').oninput = (ev) => { aj.volumen = +ev.target.value; $('aj-volumen-num').textContent = aj.volumen + '%'; guardar(); };
  $('aj-volumen').onchange = () => Sonidos.reproducir('bien', { forzar: true });
  $('aj-probar').onclick = () => Sonidos.reproducir('fin', { forzar: true });

  // ---------- Paquetes ----------
  function paquetes() {
    const lista = Object.entries(Sonidos.PAQUETES).map(([id, p]) => [id, p.nombre, p.desc]);
    if (haySitio) lista.push(['sitio', '🏠 Sonidos del sitio', 'Los archivos que el administrador puso en assets/sounds/.']);
    return lista;
  }
  function pintarPaquetes() {
    $('aj-paquetes').innerHTML = paquetes().map(([id, nombre, desc]) => `
      <label class="aj-paquete ${aj.paquete === id ? 'on' : ''}" data-id="${id}">
        <input type="radio" name="paquete" value="${id}" ${aj.paquete === id ? 'checked' : ''}>
        <b>${E(nombre)}</b><small>${E(desc)}</small>
        <button type="button" class="aj-play" data-demo="${id}" title="Escuchar">▶</button>
      </label>`).join('');
  }
  $('aj-paquetes').addEventListener('change', (ev) => {
    aj.paquete = ev.target.value;
    guardar();
    pintarPaquetes();
    pintarEventos();
    Sonidos.reproducir('bien', { forzar: true });
  });
  $('aj-paquetes').addEventListener('click', (ev) => {
    const b = ev.target.closest('[data-demo]');
    if (!b) return;
    ev.preventDefault();
    const id = b.dataset.demo;
    const fuente = id === 'sitio' ? 'sitio' : 'paquete:' + id;
    Sonidos.reproducir('bien', { forzar: true, fuente });
    setTimeout(() => Sonidos.reproducir(id === 'sitio' ? 'logro' : 'fin', { forzar: true, fuente }), 500);
  });

  // ---------- Eventos ----------
  function opciones(evento) {
    const general = paquetes().find(([id]) => id === aj.paquete);
    const ops = [['paquete', `Paquete general (${general ? general[1] : '—'})`]];
    Object.entries(Sonidos.PAQUETES).forEach(([id, p]) => { if (!p.silencio) ops.push(['paquete:' + id, p.nombre]); });
    if (Sonidos.SITIO[evento]) ops.push(['sitio', '🏠 Sonido del sitio']);
    ops.push(['archivo', nombresArchivo[evento] ? `📁 Mi archivo: ${nombresArchivo[evento]}` : '📁 Mi archivo…']);
    ops.push(['nada', '🔇 Silencio']);
    return ops;
  }
  function pintarEventos() {
    $('aj-eventos').innerHTML = Object.entries(Sonidos.EVENTOS).map(([ev, nombre]) => {
      const actual = aj.eventos[ev] || 'paquete';
      return `<div class="aj-evento" data-ev="${ev}">
        <span class="aj-ev-nombre">${E(nombre)}</span>
        <select data-fuente>${opciones(ev).map(([v, t]) => `<option value="${v}" ${v === actual ? 'selected' : ''}>${E(t)}</option>`).join('')}</select>
        <span class="aj-ev-btns">
          <button type="button" class="btn btn-ghost btn-sm" data-probar title="Escuchar">▶</button>
          <label class="btn btn-ghost btn-sm aj-subir" title="Subir un archivo de audio">📁<input type="file" accept="audio/*,.mp3,.wav,.ogg,.m4a" hidden data-archivo></label>
          ${nombresArchivo[ev] ? '<button type="button" class="btn btn-ghost btn-sm" data-borrar title="Quitar mi archivo">🗑️</button>' : ''}
        </span>
      </div>`;
    }).join('');
  }

  $('aj-eventos').addEventListener('change', async (ev) => {
    const fila = ev.target.closest('[data-ev]');
    if (!fila) return;
    const evento = fila.dataset.ev;
    if (ev.target.matches('[data-fuente]')) {
      if (ev.target.value === 'archivo' && !nombresArchivo[evento]) {
        fila.querySelector('[data-archivo]').click();   // primero hay que elegir el archivo
        ev.target.value = aj.eventos[evento] || 'paquete';
        return;
      }
      aj.eventos[evento] = ev.target.value;
      if (ev.target.value === 'paquete') delete aj.eventos[evento];
      guardar();
      Sonidos.reproducir(evento, { forzar: true });
    }
    if (ev.target.matches('[data-archivo]')) {
      const f = ev.target.files[0];
      if (!f) return;
      if (f.size > MAX_MB * 1048576) { App.toast(`😬 ${E(f.name)} pesa más de ${MAX_MB} MB. Usa un sonido más corto.`, 'info', 4500); return; }
      try {
        // Validamos que el navegador pueda decodificarlo antes de guardarlo
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        await ctx.decodeAudioData(await f.arrayBuffer());
        ctx.close();
      } catch (e) {
        App.toast(`😬 No pude leer ${E(f.name)} como audio. Prueba con mp3, wav u ogg.`, 'info', 4500);
        return;
      }
      await Sonidos.archivos.guardar(evento, f);
      Sonidos.olvidar(evento);
      nombresArchivo[evento] = f.name;
      aj.eventos[evento] = 'archivo';
      guardar();
      pintarEventos();
      App.toast(`🎵 <b>${E(f.name)}</b> ahora suena en «${E(Sonidos.EVENTOS[evento])}».`, 'achievement', 3500);
      Sonidos.reproducir(evento, { forzar: true });
    }
  });

  $('aj-eventos').addEventListener('click', async (ev) => {
    const fila = ev.target.closest('[data-ev]');
    if (!fila) return;
    const evento = fila.dataset.ev;
    if (ev.target.closest('[data-probar]')) Sonidos.reproducir(evento, { forzar: true });
    if (ev.target.closest('[data-borrar]')) {
      await Sonidos.archivos.borrar(evento);
      Sonidos.olvidar(evento);
      delete nombresArchivo[evento];
      if (aj.eventos[evento] === 'archivo') delete aj.eventos[evento];
      guardar();
      pintarEventos();
    }
  });

  $('aj-reset').onclick = async () => {
    if (!confirm('¿Volver a los sonidos originales y borrar tus archivos de audio?')) return;
    for (const ev of Object.keys(nombresArchivo)) { await Sonidos.archivos.borrar(ev); Sonidos.olvidar(ev); delete nombresArchivo[ev]; }
    aj = Sonidos.porDefecto();
    guardar();
    Sonidos.setActivo(true);
    $('aj-activo').checked = true;
    $('aj-volumen').value = aj.volumen;
    $('aj-volumen-num').textContent = aj.volumen + '%';
    pintarPaquetes();
    pintarEventos();
    App.toast('↺ Sonidos restablecidos', 'info', 2500);
  };

  // Arranque: averiguamos qué archivos propios hay guardados
  (async () => {
    for (const ev of Object.keys(Sonidos.EVENTOS)) {
      try { const r = await Sonidos.archivos.leer(ev); if (r) nombresArchivo[ev] = r.nombre; } catch (e) { /* sin IndexedDB */ }
    }
    pintarPaquetes();
    pintarEventos();
  })();
})();
