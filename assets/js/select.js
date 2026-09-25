/* Pantalla de selección de lección: navegación tipo videojuego con teclado, ratón o toque. */
(function () {
  const data = window.LESSONS;
  const list = document.getElementById('song-list');
  const tabs = [...document.querySelectorAll('.tier-tab')];
  const $ = (id) => document.getElementById(id);
  let seleccion = document.querySelector('.song-row.selected') || document.querySelector('.song-row');

  function filasVisibles() {
    return [...document.querySelectorAll('.song-group:not([hidden]) .song-row')];
  }

  function mostrarNivel(tier) {
    tabs.forEach((t) => t.classList.toggle('active', t.dataset.tier === tier));
    document.querySelectorAll('.song-group').forEach((g) => { g.hidden = g.dataset.tier !== tier; });
    const filas = filasVisibles();
    if (!filas.includes(seleccion)) elegir(filas[0], false);
  }

  function elegir(fila, sonar = true) {
    if (!fila) return;
    if (seleccion) seleccion.classList.remove('selected');
    seleccion = fila;
    fila.classList.add('selected');
    // Desplazamos solo dentro de la lista (en celular la lista no tiene scroll propio).
    if (list.scrollHeight > list.clientHeight + 4) {
      const arriba = fila.offsetTop; // la lista es el offsetParent (position: relative)
      if (arriba < list.scrollTop + 40) list.scrollTop = arriba - 60;
      else if (arriba + fila.offsetHeight > list.scrollTop + list.clientHeight - 20) list.scrollTop = arriba + fila.offsetHeight - list.clientHeight + 40;
    }
    const d = data[fila.dataset.slug];
    $('d-icon').textContent = d.icon;
    $('d-stage').textContent = d.stage;
    $('d-module').textContent = d.module;
    $('d-title').textContent = d.title;
    $('d-summary').textContent = d.summary;
    $('d-minutes').textContent = d.minutes;
    $('d-xp').textContent = d.xp;
    $('d-steps').textContent = d.steps;
    $('d-stars').innerHTML = Array.from({ length: 9 }, (_, i) => {
      const nivel = i < 3 ? 'y' : i < 6 ? 'o' : 'r';
      return `<span class="star ${i < d.stars ? 'on s-' + nivel : ''}" style="--i:${i}">★</span>`;
    }).join('');
    document.getElementById('detail').style.setProperty('--mod', d.color);
    const icono = $('d-icon');
    icono.classList.remove('pop'); void icono.offsetWidth; icono.classList.add('pop');
    $('btn-go').href = fila.href;
    if (sonar) App.sfx('move');
  }

  list.addEventListener('mouseover', (ev) => {
    const fila = ev.target.closest('.song-row');
    if (fila && fila !== seleccion && matchMedia('(hover: hover)').matches) elegir(fila);
  });
  // En pantallas táctiles: el primer toque elige, el segundo entra.
  list.addEventListener('click', (ev) => {
    const fila = ev.target.closest('.song-row');
    if (fila && fila !== seleccion) { ev.preventDefault(); elegir(fila); }
    else if (fila) App.sfx('go');
  });
  tabs.forEach((t) => t.addEventListener('click', () => { App.sfx('tab'); mostrarNivel(t.dataset.tier); }));
  $('btn-go').addEventListener('click', () => App.sfx('go'));

  document.addEventListener('keydown', (ev) => {
    const filas = filasVisibles();
    const i = filas.indexOf(seleccion);
    const t = tabs.findIndex((x) => x.classList.contains('active'));
    if (ev.key === 'ArrowDown') { ev.preventDefault(); elegir(filas[Math.min(filas.length - 1, i + 1)]); }
    else if (ev.key === 'ArrowUp') { ev.preventDefault(); elegir(filas[Math.max(0, i - 1)]); }
    else if (ev.key === 'ArrowRight' && t < tabs.length - 1) { App.sfx('tab'); mostrarNivel(tabs[t + 1].dataset.tier); }
    else if (ev.key === 'ArrowLeft' && t > 0) { App.sfx('tab'); mostrarNivel(tabs[t - 1].dataset.tier); }
    else if (ev.key === 'Enter' && seleccion) { App.sfx('go'); location.href = seleccion.href; }
  });

  // Reloj del HUD
  const reloj = () => { $('clock').textContent = new Date().toLocaleTimeString('es', { hour: '2-digit', minute: '2-digit' }); };
  reloj(); setInterval(reloj, 10000);

  mostrarNivel(document.querySelector('.tier-tab.active').dataset.tier);
  elegir(seleccion, false);
})();
