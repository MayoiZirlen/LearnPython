/* Utilidades generales: llamadas a la API, notificaciones, confeti y XP. */
const App = {
  sonidoActivo() {
    return Sonidos.activo();
  },

  /** Sonidos del juego: ahora pasan por el motor personalizable (assets/js/sonidos.js). */
  sfx(tipo) {
    Sonidos.reproducir(tipo);
  },

  /** Rutas internas: en GitHub Pages las páginas son .html (lesson-<slug>.html). */
  url(pagina, slug) {
    if (window.PYAPRENDE.static) return pagina === 'lesson' ? `lesson-${slug}.html` : `${pagina}.html`;
    return pagina === 'lesson' ? `lesson.php?l=${encodeURIComponent(slug)}` : `${pagina}.php`;
  },

  async api(action, data = {}) {
    if (window.PYAPRENDE.static) {
      // Sin servidor: el progreso se guarda en este navegador (assets/js/local.js)
      const res = Local.api(action, data);
      if (res && res.ok) App.celebrate(res);
      return res;
    }
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
    if (res.xp_gained > 0) { App.floatXp('+' + res.xp_gained + ' XP'); App.sfx('xp'); }
    if (res.level_up) {
      setTimeout(() => App.sfx('nivel'), 350);
      App.toast(`${res.level.icon} ¡Subiste al nivel ${res.level.number}: ${res.level.name}!`, 'level', 5000);
      App.confetti(160);
    }
    const logros = res.achievements || [];
    if (logros.length > 3) {
      // Muchos logros a la vez (por ejemplo al desbloquear el NG+): una sola notificación.
      setTimeout(() => App.sfx('logro'), 700);
      App.toast(`<span class="toast-icon">🏅</span><div><b>¡${logros.length} logros desbloqueados!</b><br>${logros.map((a) => `${a.icon} ${a.title}`).join('<br>')}</div>`, 'achievement', 8000);
      App.confetti(160);
    } else {
      logros.forEach((a, i) => setTimeout(() => {
        App.sfx('logro');
        App.toast(`<span class="toast-icon">${a.icon}</span><div><b>¡Logro desbloqueado!</b><br>${a.title}: ${a.desc}</div>`, 'achievement', 6000);
        App.confetti(80);
      }, 600 * i));
    }
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
