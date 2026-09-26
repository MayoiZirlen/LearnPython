/* Motor de sonidos personalizable.
 *
 * Cada "evento" (respuesta correcta, logro, Game Over…) puede sonar con:
 *   - un paquete sintetizado (se genera con WebAudio, no necesita archivos),
 *   - un archivo propio que el usuario sube (se guarda en IndexedDB, solo en su navegador),
 *   - un archivo del sitio en assets/sounds/<evento>.mp3|wav|ogg (lo pone el administrador),
 *   - o silencio.
 * Los ajustes viven en localStorage ("pyaprende:sonidos"). El interruptor general
 * sigue siendo "pyaprende:sonido" (lo usa también el botón 🔊 de las lecciones).
 */
const Sonidos = (() => {
  const CLAVE = 'pyaprende:sonidos';
  const EVENTOS = {
    move: '🕹️ Moverse por los menús',
    tab: '📑 Cambiar de pestaña',
    go: '▶️ Entrar / confirmar',
    bien: '✅ Respuesta correcta',
    mal: '❌ Respuesta incorrecta',
    xp: '⭐ Ganar XP',
    fin: '🏁 Lección terminada',
    logro: '🏅 Logro desbloqueado',
    nivel: '⬆️ Subir de nivel',
    vida: '💔 Perder una vida (Infierno)',
    gameover: '☠️ Game Over',
  };

  // Melodías base: [frecuencia, inicio (s), duración (s)]. Cada paquete las "interpreta" a su estilo.
  const MELODIAS = {
    move: [[880, 0, 0.04]],
    tab: [[440, 0, 0.05], [660, 0.04, 0.06]],
    go: [[523, 0, 0.08], [784, 0.07, 0.08], [1047, 0.14, 0.18]],
    bien: [[660, 0, 0.1], [990, 0.09, 0.2]],
    mal: [[196, 0, 0.18], [147, 0.12, 0.22]],
    xp: [[1319, 0, 0.05], [1760, 0.05, 0.08]],
    fin: [[523, 0, 0.12], [659, 0.1, 0.12], [784, 0.2, 0.12], [1047, 0.3, 0.4]],
    logro: [[784, 0, 0.1], [988, 0.09, 0.1], [1175, 0.18, 0.1], [1568, 0.27, 0.35]],
    nivel: [[523, 0, 0.09], [659, 0.08, 0.09], [784, 0.16, 0.09], [1047, 0.24, 0.09], [1319, 0.32, 0.09], [1568, 0.4, 0.4]],
    vida: [[330, 0, 0.12], [233, 0.1, 0.25]],
    gameover: [[392, 0, 0.25], [330, 0.25, 0.25], [262, 0.5, 0.25], [196, 0.75, 0.7]],
  };

  // Paquetes sintetizados: forma de onda, transposición (×frecuencia), tempo (×tiempo) y volumen relativo.
  const PAQUETES = {
    retro: { nombre: '🕹️ Retro 8-bit', desc: 'El clásico sonido de consola.', onda: 'square', tono: 1, tempo: 1, vol: 0.06, error: 'sawtooth' },
    suave: { nombre: '🌸 Suave', desc: 'Tonos redondos y tranquilos.', onda: 'sine', tono: 1, tempo: 1.35, vol: 0.16 },
    arcade: { nombre: '👾 Arcade', desc: 'Agresivo y rápido, de maquinita.', onda: 'sawtooth', tono: 1, tempo: 0.8, vol: 0.05 },
    cristal: { nombre: '💎 Cristal', desc: 'Agudo y brillante, como campanitas.', onda: 'triangle', tono: 2, tempo: 0.85, vol: 0.14 },
    grave: { nombre: '🎸 Grave', desc: 'Una octava abajo, con cuerpo.', onda: 'triangle', tono: 0.5, tempo: 1.15, vol: 0.2 },
    silencio: { nombre: '🔇 Silencio', desc: 'Sin sonidos (salvo los que personalices).', silencio: true },
  };

  const SITIO = (window.PYAPRENDE && window.PYAPRENDE.sonidosSitio) || {};

  function porDefecto() {
    return { paquete: 'retro', volumen: 80, eventos: {} };
  }

  function leer() {
    try { return Object.assign(porDefecto(), JSON.parse(localStorage.getItem(CLAVE) || '{}')); } catch (e) { return porDefecto(); }
  }
  function guardar(aj) {
    try { localStorage.setItem(CLAVE, JSON.stringify(aj)); } catch (e) { /* sin almacenamiento */ }
  }
  function activo() {
    try { return localStorage.getItem('pyaprende:sonido') !== 'no'; } catch (e) { return true; }
  }
  function setActivo(si) {
    try { localStorage.setItem('pyaprende:sonido', si ? 'si' : 'no'); } catch (e) { /* */ }
  }

  let ctx = null;
  function audio() {
    ctx = ctx || new (window.AudioContext || window.webkitAudioContext)();
    if (ctx.state === 'suspended') ctx.resume();
    return ctx;
  }

  function sintetizar(evento, idPaquete, volumen) {
    const p = PAQUETES[idPaquete] || PAQUETES.retro;
    if (p.silencio) return;
    const a = audio();
    const esError = evento === 'mal' || evento === 'vida' || evento === 'gameover';
    (MELODIAS[evento] || MELODIAS.move).forEach(([f, t, d]) => {
      const o = a.createOscillator();
      const g = a.createGain();
      const inicio = a.currentTime + t * p.tempo;
      const dur = d * p.tempo;
      o.type = esError && p.error ? p.error : p.onda;
      o.frequency.value = f * p.tono;
      g.gain.setValueAtTime(Math.max(0.0002, p.vol * volumen), inicio);
      g.gain.exponentialRampToValueAtTime(0.0001, inicio + dur);
      o.connect(g).connect(a.destination);
      o.start(inicio);
      o.stop(inicio + dur + 0.02);
    });
  }

  // ---------- Archivos propios (IndexedDB) ----------
  const DB = 'pyaprende-sonidos';
  function abrirDB() {
    return new Promise((ok, mal) => {
      const r = indexedDB.open(DB, 1);
      r.onupgradeneeded = () => r.result.createObjectStore('archivos');
      r.onsuccess = () => ok(r.result);
      r.onerror = () => mal(r.error);
    });
  }
  async function operar(modo, fn) {
    const db = await abrirDB();
    return new Promise((ok, mal) => {
      const tx = db.transaction('archivos', modo);
      const req = fn(tx.objectStore('archivos'));
      tx.oncomplete = () => ok(req && req.result);
      tx.onerror = () => mal(tx.error);
    });
  }
  const archivos = {
    guardar: (evento, file) => operar('readwrite', (s) => s.put({ nombre: file.name, blob: file }, evento)),
    leer: (evento) => operar('readonly', (s) => s.get(evento)),
    borrar: (evento) => operar('readwrite', (s) => s.delete(evento)),
  };

  const buffers = new Map();   // caché de audio ya decodificado
  async function reproducirBlob(clave, obtenerDatos, volumen) {
    const a = audio();
    let buf = buffers.get(clave);
    if (!buf) {
      const datos = await obtenerDatos();
      if (!datos) return false;
      buf = await a.decodeAudioData(datos);
      buffers.set(clave, buf);
    }
    const src = a.createBufferSource();
    const g = a.createGain();
    g.gain.value = volumen;
    src.buffer = buf;
    src.connect(g).connect(a.destination);
    src.start();
    return true;
  }

  /**
   * Reproduce un evento según los ajustes. "forzar" sirve para las vistas previas
   * (suena aunque el interruptor general esté apagado).
   */
  async function reproducir(evento, { forzar = false, fuente = null } = {}) {
    if (!forzar && !activo()) return;
    const aj = leer();
    const vol = Math.max(0, Math.min(100, aj.volumen)) / 100;
    if (vol === 0) return;
    const f = fuente || (aj.eventos[evento] || 'paquete');
    try {
      if (f === 'nada') return;
      if (f === 'archivo') {
        const ok = await reproducirBlob('archivo:' + evento, async () => {
          const r = await archivos.leer(evento);
          return r ? r.blob.arrayBuffer() : null;
        }, vol);
        if (!ok) sintetizar(evento, aj.paquete, vol);
        return;
      }
      if (f === 'sitio' && SITIO[evento]) {
        await reproducirBlob('sitio:' + evento, async () => (await fetch(SITIO[evento])).arrayBuffer(), vol);
        return;
      }
      if (f.startsWith('paquete:')) return sintetizar(evento, f.slice(8), vol);
      // "paquete" = el paquete general elegido; si es el del sitio y hay archivo, se usa
      if (aj.paquete === 'sitio') {
        if (SITIO[evento]) return reproducirBlob('sitio:' + evento, async () => (await fetch(SITIO[evento])).arrayBuffer(), vol);
        return sintetizar(evento, 'retro', vol);
      }
      sintetizar(evento, aj.paquete, vol);
    } catch (e) { /* un sonido nunca debe romper la página */ }
  }

  /** Olvida el audio decodificado (tras subir o borrar un archivo). */
  function olvidar(evento) {
    buffers.delete('archivo:' + evento);
  }

  return { EVENTOS, PAQUETES, SITIO, leer, guardar, activo, setActivo, reproducir, archivos, olvidar, porDefecto };
})();
