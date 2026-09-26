/* Motor de lecciones: muestra un paso a la vez (teoría, ejemplo, quiz o ejercicio). */
(function () {
  const L = window.LESSON;
  const E = App.escape;
  const $ = (id) => document.getElementById(id);
  const cont = $('step-container');
  const hechos = new Set(L.doneSteps);
  const codigos = {};          // código escrito en cada paso (para no perderlo al navegar)
  const vioSolucion = new Set();
  let actual = 0;

  const BIEN = ['¡Excelente! 🎉', '¡Lo lograste! 💪', '¡Eres una máquina! 🤖', '¡Perfecto! ✨', '¡Así se hace! 🙌', '¡Brillante! 💡'];
  const MAL = ['Casi… ¡inténtalo otra vez! 💪', 'No pasa nada, así se aprende 🌱', 'Buen intento, revisa la pista 🔍', 'Uy, todavía no. ¡Tú puedes! 🚀'];
  const azar = (a) => a[Math.floor(Math.random() * a.length)];
  if (L.infierno) {
    MAL.splice(0, MAL.length, '¡Te quemaste! 🔥', '¡Auch! Eso dolió 💀', 'El Infierno no perdona 😈', '¡Cuidado, te quedan pocas vidas! ❤️‍🔥');
  }

  let sonido = App.sonidoActivo();
  const sonidoBien = () => App.sfx('bien');
  const sonidoMal = () => App.sfx('mal');

  const btnSonido = document.createElement('button');
  btnSonido.className = 'sound-toggle';
  const pintarSonido = () => { btnSonido.textContent = sonido ? '🔊' : '🔇'; btnSonido.title = sonido ? 'Silenciar' : 'Activar sonido'; };
  btnSonido.onclick = () => { sonido = !sonido; pintarSonido(); try { localStorage.setItem('pyaprende:sonido', sonido ? 'si' : 'no'); } catch (e) { /* */ } };
  pintarSonido();
  document.querySelector('.lesson-top').appendChild(btnSonido);

  // ---------- Modo Infierno: 3 vidas, sin pistas ni soluciones ----------
  const INFIERNO = !!L.infierno;
  const VIDAS_MAX = 3;
  let vidas = VIDAS_MAX;
  const hud = document.createElement('div');
  if (INFIERNO) {
    hud.className = 'hearts';
    document.querySelector('.lesson-top').insertBefore(hud, document.querySelector('.lesson-progress'));
  }
  function pintarVidas(perdida) {
    if (!INFIERNO) return;
    hud.innerHTML = Array.from({ length: VIDAS_MAX }, (_, k) => `<span class="heart ${k < vidas ? '' : 'lost'} ${perdida && k === vidas ? 'breaking' : ''}">${k < vidas ? '❤️' : '🖤'}</span>`).join('');
  }
  pintarVidas();

  /** Se llama en cada respuesta incorrecta o revisión fallida. */
  function perderVida() {
    if (!INFIERNO || vidas <= 0) return;
    vidas--;
    pintarVidas(true);
    setTimeout(() => App.sfx('vida'), 250);
    document.body.classList.remove('hurt'); void document.body.offsetWidth; document.body.classList.add('hurt');
    if (vidas === 0) setTimeout(gameOver, 700);
  }

  async function gameOver() {
    App.sfx('gameover');
    const res = await App.api('reset', { lesson: L.slug });
    const perdida = res && res.ok ? res.xp_perdida : 0;
    const capa = document.createElement('div');
    capa.className = 'game-over';
    capa.innerHTML = `<div class="go-box">
        <div class="go-title">GAME OVER</div>
        <div class="go-skull">☠️</div>
        <p>Te quedaste sin vidas en <b>${E(L.title)}</b>.</p>
        ${perdida ? `<p class="go-lost">−${perdida} XP · el progreso de esta lección se perdió</p>` : '<p class="go-lost">El progreso de esta lección se perdió</p>'}
        <div class="finish-actions">
          <button class="btn btn-primary btn-lg" data-retry>🔥 Reintentar</button>
          <a class="btn btn-ghost" href="learn.php">🏳️ Huir al mapa</a>
        </div>
      </div>`;
    document.body.appendChild(capa);
    capa.querySelector('[data-retry]').onclick = () => location.reload();
  }

  const califica = (s) => s.type === 'quiz' || s.type === 'exercise';
  const puedeAvanzar = (i) => !califica(L.steps[i]) || hechos.has(i);

  function marcarHecho(i) {
    if (hechos.has(i)) return;
    hechos.add(i);
    App.api('step', { lesson: L.slug, step: i, used_solution: vioSolucion.has(i) });
    actualizarNav();
  }

  function actualizarNav() {
    const total = L.steps.length;
    $('lesson-bar').style.width = (100 * (actual + (puedeAvanzar(actual) ? 1 : 0.5)) / total) + '%';
    $('lesson-counter').textContent = `${actual + 1} / ${total}`;
    $('btn-prev').disabled = actual === 0;
    const ultimo = actual === total - 1;
    $('btn-next').textContent = ultimo ? '🏁 Terminar lección' : 'Continuar ▶';
    $('btn-next').disabled = !puedeAvanzar(actual);
    $('btn-next').title = puedeAvanzar(actual) ? '' : 'Resuelve este paso para continuar';
    $('dots').innerHTML = L.steps.map((s, i) => {
      const icon = { text: '📖', example: '🧪', quiz: '❓', exercise: '💻' }[s.type] || '•';
      const cls = [i === actual ? 'on' : '', hechos.has(i) || (!califica(s) && i < actual) ? 'ok' : ''].join(' ');
      // Solo se puede saltar a pasos ya visitados o resueltos.
      const alcanzable = i <= maxAlcanzable();
      return `<button class="dot ${cls}" data-i="${i}" ${alcanzable ? '' : 'disabled'} title="${E(s.title || s.type)}">${icon}</button>`;
    }).join('');
  }

  function maxAlcanzable() {
    let i = 0;
    while (i < L.steps.length - 1 && puedeAvanzar(i)) i++;
    return i;
  }

  function ir(i) {
    guardarCodigo();
    actual = Math.max(0, Math.min(L.steps.length - 1, i));
    pintar();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  let editorActual = null;
  function guardarCodigo() {
    if (editorActual) codigos[actual] = editorActual.getValue();
  }

  function pintar() {
    const s = L.steps[actual];
    editorActual = null;
    cont.innerHTML = '';
    const card = document.createElement('div');
    card.className = 'step-card step-' + s.type;
    cont.appendChild(card);
    ({ text: pintarTexto, example: pintarEjemplo, quiz: pintarQuiz, exercise: pintarEjercicio })[s.type](card, s, actual);
    actualizarNav();
  }

  function titulo(s, etiqueta) {
    return `<div class="step-kind">${etiqueta}</div>${s.title ? `<h2>${E(s.title)}</h2>` : ''}`;
  }

  function pintarTexto(card, s) {
    card.innerHTML = titulo(s, '📖 Aprende') + `<div class="prose">${s.html}</div>`;
  }

  function pintarEjemplo(card, s, i) {
    card.innerHTML = titulo(s, '🧪 Pruébalo') + `<div class="prose">${s.html || ''}</div>
      <div class="runner">
        <textarea class="code" data-code></textarea>
        <div class="runner-actions">
          <button class="btn btn-primary" data-run>▶ Ejecutar</button>
          <button class="btn btn-ghost" data-reset>↺ Restaurar</button>
          <span class="muted small">Cambia el código y vuelve a ejecutarlo. ¡Experimentar es la mejor forma de aprender!</span>
        </div>
        <div class="output" data-output></div>
      </div>`;
    card.querySelector('[data-code]').value = codigos[i] ?? s.code;
    const r = montarRunner(card.querySelector('.runner'));
    editorActual = r.editor;
    card.querySelector('[data-reset]').onclick = () => r.editor.setValue(s.code);
  }

  function pintarQuiz(card, s, i) {
    const resuelto = hechos.has(i);
    card.innerHTML = titulo(s, '❓ Pregunta rápida') + `<div class="prose quiz-q">${s.question}</div>
      ${s.code ? `<pre class="code-block">${E(s.code)}</pre>` : ''}
      <div class="options">${s.options.map((o, k) => `<button class="option" data-k="${k}"><span class="opt-key">${'ABCD'[k]}</span><span>${E(o)}</span></button>`).join('')}</div>
      <div class="feedback" data-fb></div>`;
    const fb = card.querySelector('[data-fb]');
    const mostrarCorrecta = () => {
      card.querySelectorAll('.option').forEach((b) => { b.disabled = true; });
      card.querySelector(`.option[data-k="${s.answer}"]`).classList.add('correct');
      fb.className = 'feedback good';
      fb.innerHTML = `<b>${azar(BIEN)}</b>${s.explain ? `<div>${s.explain}</div>` : ''}`;
    };
    if (resuelto) mostrarCorrecta();
    card.querySelectorAll('.option').forEach((b) => b.addEventListener('click', () => {
      const k = +b.dataset.k;
      if (k === s.answer) {
        sonidoBien();
        mostrarCorrecta();
        marcarHecho(i);
      } else {
        sonidoMal();
        perderVida();
        b.classList.add('wrong', 'shake');
        b.disabled = true;
        fb.className = 'feedback bad';
        fb.innerHTML = `<b>${azar(MAL)}</b>${s.wrong && s.wrong[k] ? `<div>${s.wrong[k]}</div>` : ''}`;
        setTimeout(() => b.classList.remove('shake'), 500);
      }
    }));
  }

  function pintarEjercicio(card, s, i) {
    const pistas = Array.isArray(s.hint) ? s.hint : (s.hint ? [s.hint] : []);
    let pistasVistas = 0;
    let fallos = 0;
    card.innerHTML = titulo(s, '💻 Reto de código') + `<div class="prose">${s.html}</div>
      <div class="runner">
        <textarea class="code" data-code></textarea>
        <div class="runner-actions">
          <button class="btn btn-ghost" data-run>▶ Ejecutar</button>
          <button class="btn btn-primary" data-check>✅ Comprobar</button>
          ${pistas.length && !INFIERNO ? '<button class="btn btn-ghost" data-hint>💡 Pista</button>' : ''}
          <button class="btn btn-ghost" data-sol hidden>👀 Ver solución</button>
          <button class="btn btn-ghost" data-reset title="Volver al código inicial">↺</button>
        </div>
        <div class="hints" data-hints></div>
        <div class="feedback" data-fb></div>
        <div class="output" data-output></div>
      </div>`;
    card.querySelector('[data-code]').value = codigos[i] ?? s.starter ?? '';
    const r = montarRunner(card.querySelector('.runner'));
    editorActual = r.editor;
    const out = card.querySelector('[data-output]');
    const fb = card.querySelector('[data-fb]');
    const btnCheck = card.querySelector('[data-check]');
    const btnSol = card.querySelector('[data-sol]');
    card.querySelector('[data-reset]').onclick = () => r.editor.setValue(s.starter || '');

    if (hechos.has(i)) {
      fb.className = 'feedback good';
      fb.innerHTML = '<b>✅ Ya resolviste este reto.</b> Puedes seguir practicando o continuar.';
    }

    const btnHint = card.querySelector('[data-hint]');
    if (btnHint) btnHint.onclick = () => {
      if (pistasVistas < pistas.length) {
        card.querySelector('[data-hints]').insertAdjacentHTML('beforeend', `<div class="hint">💡 <b>Pista ${pistasVistas + 1}:</b> ${pistas[pistasVistas]}</div>`);
        pistasVistas++;
      }
      if (pistasVistas >= pistas.length) btnHint.disabled = true;
    };

    btnSol.onclick = () => {
      if (!s.solution) return;
      if (!hechos.has(i) && !confirm('Si ves la solución ganarás menos XP en este reto. ¿Quieres verla?')) return;
      vioSolucion.add(i);
      r.editor.setValue(s.solution);
      card.querySelector('[data-hints]').insertAdjacentHTML('beforeend', '<div class="hint">👀 Esta es una posible solución. Léela con calma, pulsa <b>Comprobar</b> y trata de entender cada línea.</div>');
    };

    btnCheck.onclick = async () => {
      btnCheck.disabled = true;
      const texto = btnCheck.innerHTML;
      btnCheck.innerHTML = '⏳ Revisando…';
      out.innerHTML = '<div class="out-loading"><span class="spinner"></span> <span data-msg>Preparando…</span></div>';
      fb.className = 'feedback'; fb.innerHTML = '';
      const res = await Py.ejecutar(r.editor.getValue(), {
        check: s.check,
        onEstado: (t) => { const m = out.querySelector('[data-msg]'); if (m) m.textContent = t; },
      });
      mostrarSalida(out, res);
      r.editor.marcarLinea(res.error ? res.linea : null);
      btnCheck.disabled = false;
      btnCheck.innerHTML = texto;
      App.api('run', { chart: (res.imagenes || []).length > 0 });

      if (res.check_ok) {
        sonidoBien();
        fb.className = 'feedback good';
        fb.innerHTML = `<b>${azar(BIEN)}</b> ${s.success ? `<div>${s.success}</div>` : ''}`;
        App.confetti(60);
        marcarHecho(i);
      } else {
        sonidoMal();
        fallos++;
        fb.className = 'feedback bad shake';
        const msg = res.error ? 'Tu código tiene un error. Míralo abajo 👇' : E(res.check_msg);
        fb.innerHTML = `<b>${azar(MAL)}</b><div>${msg}</div>`;
        setTimeout(() => fb.classList.remove('shake'), 500);
        if (fallos >= 2 && s.solution && !INFIERNO) btnSol.hidden = false;
        perderVida();
      }
    };
  }

  async function terminar() {
    guardarCodigo();
    const ganado = L.steps.reduce((acc, s, i) => acc + (hechos.has(i) ? s.xp : 0), 0);
    const rango = vioSolucion.size === 0 ? 'Perfect' : vioSolucion.size === 1 ? 'Great' : 'Cleared';
    const res = await App.api('lesson', { lesson: L.slug, vidas });
    App.sfx('fin');
    App.confetti(220);
    const siguiente = L.next
      ? `<a class="btn btn-primary btn-lg" href="lesson.php?l=${encodeURIComponent(L.next)}">Siguiente: ${E(L.nextTitle)} ▶</a>`
      : '<a class="btn btn-primary btn-lg" href="profile.php">🏆 Ver mis logros</a>';
    cont.innerHTML = `<div class="step-card finish">
      <div class="finish-banner">¡Lección superada!</div>
      ${INFIERNO ? `<div class="hearts final">${Array.from({ length: VIDAS_MAX }, (_, k) => `<span class="heart ${k < vidas ? '' : 'lost'}">${k < vidas ? '❤️' : '🖤'}</span>`).join('')}${vidas === VIDAS_MAX ? '<b>¡INTOCABLE!</b>' : ''}</div>` : ''}
      ${L.unlocksInfierno ? '<div class="infierno-unlock"><div class="infierno-logo">INFIERNO</div><b>¡LAS PUERTAS SE ABRIERON!</b><small>9 lecciones sin pistas, sin soluciones y con 3 vidas. XP ×2.</small></div>' : ''}
      ${L.unlocksNgPlus ? '<div class="ngplus-unlock"><div class="ngplus-logo">NEW GAME<span>+</span></div><b>¡DESBLOQUEADO!</b><small>12 lecciones nuevas y un jefe final te esperan</small></div>' : ''}
      <div class="finish-rank rank rank-${rango.toLowerCase()}">${rango}</div>
      <p class="muted">${E(L.title)}</p>
      <div class="finish-stats">
        <div><b>${hechos.size}</b><small>retos resueltos</small></div>
        <div><b>${res && res.ok ? res.xp_gained + ganado : ganado + L.bonus}</b><small>XP de la lección</small></div>
        ${res && res.ok ? `<div><b>🔥 ${res.streak}</b><small>días de racha</small></div>` : ''}
      </div>
      ${window.PYAPRENDE.logged ? '' : '<div class="alert alert-info">Crea una cuenta para guardar este progreso 😉</div>'}
      <div class="finish-actions">${siguiente}<a class="btn btn-ghost" href="learn.php">🗺️ Volver al mapa</a></div>
    </div>`;
    document.querySelector('.lesson-nav').hidden = true;
    $('lesson-bar').style.width = '100%';
  }

  $('btn-prev').onclick = () => ir(actual - 1);
  $('btn-next').onclick = () => {
    if (!puedeAvanzar(actual)) return;
    if (actual === L.steps.length - 1) terminar(); else { App.sfx('move'); ir(actual + 1); }
  };
  $('dots').addEventListener('click', (ev) => {
    const b = ev.target.closest('.dot');
    if (b && !b.disabled) ir(+b.dataset.i);
  });
  document.addEventListener('keydown', (ev) => {
    const enEditor = ev.target.closest('.CodeMirror, textarea, input');
    if (ev.key === 'Enter' && !enEditor && !ev.ctrlKey && !$('btn-next').disabled && !document.querySelector('.lesson-nav').hidden) {
      ev.preventDefault();
      $('btn-next').click();
    }
  });

  // Si ya había avanzado antes, continuar en el primer paso pendiente.
  if (!L.lessonDone) {
    const pendiente = L.steps.findIndex((s, i) => califica(s) && !hechos.has(i));
    if (pendiente > 0 && hechos.size > 0) actual = pendiente;
  }
  pintar();
})();
