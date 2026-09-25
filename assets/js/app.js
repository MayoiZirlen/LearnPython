/* Utilidades generales: llamadas a la API, notificaciones, confeti y XP. */
const App = {
  audio: null,

  sonidoActivo() {
    try { return localStorage.getItem('pyaprende:sonido') !== 'no'; } catch (e) { return true; }
  },

  /** Sonidos cortitos generados con WebAudio: [frecuencia, inicio, duración, forma]. */
  tono(notas) {
    if (!App.sonidoActivo()) return;
    try {
      App.audio = App.audio || new (window.AudioContext || window.webkitAudioContext)();
      const a = App.audio;
      notas.forEach(([f, t, d, forma]) => {
        const o = a.createOscillator(); const g = a.createGain();
        o.frequency.value = f; o.type = forma || 'square';
        g.gain.setValueAtTime(0.06, a.currentTime + t);
        g.gain.exponentialRampToValueAtTime(0.001, a.currentTime + t + d);
        o.connect(g).connect(a.destination);
        o.start(a.currentTime + t); o.stop(a.currentTime + t + d);
      });
    } catch (e) { /* sin audio */ }
  },

  sfx(tipo) {
    const sonidos = {
      move: [[880, 0, 0.04]],
      tab: [[440, 0, 0.05], [660, 0.04, 0.06]],
      go: [[523, 0, 0.08], [784, 0.07, 0.08], [1047, 0.14, 0.18]],
      bien: [[660, 0, 0.1], [990, 0.09, 0.2]],
      mal: [[196, 0, 0.18, 'sawtooth'], [147, 0.12, 0.22, 'sawtooth']],
      fin: [[523, 0, 0.12], [659, 0.1, 0.12], [784, 0.2, 0.12], [1047, 0.3, 0.4]],
    };
    App.tono(sonidos[tipo] || sonidos.move);
  },

  async api(action, data = {}) {
    if (!window.PYAPRENDE.logged) return null;
    try {
      const r = await fetch('api/progress.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.PYAPRENDE.csrf },
        body: JSON.stringify({ action, ...data }),
      });
      const json = await r.json();
      if (!r.ok) return { error: json.error || 'Error', ...json };
      App.celebrate(json);
      return json;
    } catch (e) {
      return null;
    }
  },

  /** Muestra XP ganada, subidas de nivel y logros nuevos. */
  celebrate(res) {
    if (!res || !res.ok) return;
    const xpEl = document.getElementById('hdr-xp');
    const stEl = document.getElementById('hdr-streak');
    if (xpEl) App.countUp(xpEl, res.xp);
    if (stEl) stEl.textContent = res.streak;
    if (res.xp_gained > 0) App.floatXp('+' + res.xp_gained + ' XP');
    if (res.level_up) {
      App.toast(`${res.level.icon} ¡Subiste al nivel ${res.level.number}: ${res.level.name}!`, 'level', 5000);
      App.confetti(160);
    }
    (res.achievements || []).forEach((a, i) => setTimeout(() => {
      App.toast(`<span class="toast-icon">${a.icon}</span><div><b>¡Logro desbloqueado!</b><br>${a.title}: ${a.desc}</div>`, 'achievement', 6000);
      App.confetti(80);
    }, 600 * i));
  },

  toast(html, type = 'info', ms = 3500) {
    const box = document.getElementById('toasts');
    const t = document.createElement('div');
    t.className = 'toast toast-' + type;
    t.innerHTML = html;
    box.appendChild(t);
    setTimeout(() => t.classList.add('hide'), ms);
    setTimeout(() => t.remove(), ms + 400);
  },

  floatXp(text) {
    const el = document.createElement('div');
    el.className = 'float-xp';
    el.textContent = text;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 1600);
  },

  countUp(el, to) {
    const from = parseInt(el.textContent, 10) || 0;
    const t0 = performance.now();
    const step = (t) => {
      const k = Math.min(1, (t - t0) / 700);
      el.textContent = Math.round(from + (to - from) * k);
      if (k < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
    el.parentElement.classList.add('bump');
    setTimeout(() => el.parentElement.classList.remove('bump'), 600);
  },

  confetti(n = 120) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const c = document.createElement('canvas');
    c.className = 'confetti';
    c.width = innerWidth; c.height = innerHeight;
    document.body.appendChild(c);
    const ctx = c.getContext('2d');
    const colors = ['#e5191c', '#ffffff', '#ffd21f', '#3fe0ff', '#e5191c', '#111111'];
    const ps = Array.from({ length: n }, () => ({
      x: innerWidth / 2 + (Math.random() - 0.5) * 200, y: innerHeight / 3,
      vx: (Math.random() - 0.5) * 14, vy: Math.random() * -14 - 4,
      r: Math.random() * 6 + 4, c: colors[(Math.random() * colors.length) | 0],
      a: Math.random() * Math.PI, va: (Math.random() - 0.5) * 0.3,
    }));
    let frames = 0;
    (function loop() {
      ctx.clearRect(0, 0, c.width, c.height);
      ps.forEach((p) => {
        p.vy += 0.35; p.x += p.vx; p.y += p.vy; p.a += p.va;
        ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.a);
        ctx.fillStyle = p.c; ctx.fillRect(-p.r / 2, -p.r / 4, p.r, p.r / 2);
        ctx.restore();
      });
      if (++frames < 150) requestAnimationFrame(loop); else c.remove();
    })();
  },

  escape(s) {
    return String(s).replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
  },
};
