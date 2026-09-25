/* Web Worker que ejecuta Python con Pyodide, fuera del hilo de la página.
 * Así un bucle infinito no congela el sitio: la página puede terminar el worker y crear otro. */
let pyodide = null;
let listo = null;
let wheelsUrl = '';
const instaladas = new Set();
const CARPETA = '/home/pyodide/';
const INTERNOS = new Set(['harness.py']);

async function iniciar({ pyodideUrl, harnessUrl, datasets, wheels }) {
  wheelsUrl = wheels;
  importScripts(pyodideUrl + 'pyodide.js');
  postMessage({ tipo: 'estado', texto: 'Despertando a Python…' });
  pyodide = await loadPyodide({ indexURL: pyodideUrl });
  pyodide.setStdout({ batched: () => {} });

  const harness = await (await fetch(harnessUrl)).text();
  pyodide.FS.writeFile(CARPETA + 'harness.py', harness);

  // Copiamos los datasets al disco virtual para que pd.read_csv('ventas.csv') funcione.
  for (const d of datasets) {
    try {
      const datos = new Uint8Array(await (await fetch(d.url)).arrayBuffer());
      pyodide.FS.writeFile(CARPETA + d.nombre, datos);
    } catch (e) { /* un dataset faltante no debe impedir usar Python */ }
  }
  await pyodide.runPythonAsync('import os, sys\nos.chdir("/home/pyodide")\nsys.path.insert(0, "/home/pyodide")\nimport harness');
  postMessage({ tipo: 'listo' });
}

/** Paquetes del catálogo de Pyodide que el código necesita aunque no aparezcan en un import. */
function paquetesExtra(codigo) {
  const p = [];
  if (/\.(plot|hist|boxplot)\s*\(|matplotlib|seaborn|sns\./.test(codigo)) p.push('matplotlib');
  if (/\.xls["']/.test(codigo)) p.push('xlrd');
  return p;
}

/**
 * Librerías de Python puro que NO vienen en Pyodide: van incluidas en assets/wheels/
 * y se instalan desde ahí (funciona también sin internet).
 * Cada una: cuándo se necesita, qué paquetes de Pyodide requiere y sus archivos .whl en orden.
 */
const RUEDAS = {
  openpyxl: {
    cuando: /read_excel|to_excel|ExcelWriter|ExcelFile|openpyxl|\.xlsx?m?\b/,
    requiere: [],
    archivos: ['et_xmlfile-2.0.0-py3-none-any.whl', 'openpyxl-3.1.5-py2.py3-none-any.whl'],
  },
  seaborn: {
    cuando: /\b(import|from)\s+seaborn\b/,
    requiere: ['numpy', 'pandas', 'matplotlib'],
    archivos: ['seaborn-0.13.2-py3-none-any.whl'],
  },
};

async function instalarRuedas(codigo) {
  for (const [nombre, r] of Object.entries(RUEDAS)) {
    if (instaladas.has(nombre) || !r.cuando.test(codigo)) continue;
    if (r.requiere.length) await pyodide.loadPackage(r.requiere);
    // loadPackage acepta URLs de archivos .whl (no usamos micropip: su versión 0.8 falla con deps=False).
    await pyodide.loadPackage(r.archivos.map((a) => wheelsUrl + a));
    instaladas.add(nombre);
  }
}

async function correr({ id, codigo, check, entradas, antes, despues }) {
  await listo;
  const todo = codigo + '\n' + (check || '');
  const paquetes = paquetesExtra(codigo);
  const ruedas = Object.values(RUEDAS).some((r) => r.cuando.test(codigo));
  if (paquetes.length || ruedas || /\bimport\b/.test(todo)) {
    postMessage({ tipo: 'estado', id, texto: 'Cargando librerías (solo la primera vez)…' });
    // Si algo no se puede descargar seguimos: el error aparecerá al importar, explicado en español.
    for (const p of paquetes) {
      try { await pyodide.loadPackage(p); } catch (e) { /* paquete no disponible */ }
    }
    try { await pyodide.loadPackagesFromImports(todo); } catch (e) { /* sin conexión */ }
    try { await instalarRuedas(codigo); } catch (e) { console.error('No se pudo instalar una librería', String(e).slice(-700)); }
    const precargar = pyodide.globals.get('harness').precargar;
    try { precargar(codigo); } catch (e) { /* si falla, el error se verá al ejecutar */ }
    precargar.destroy();
  }
  postMessage({ tipo: 'corriendo', id });
  const f = pyodide.globals.get('harness').ejecutar_json;
  const json = f(codigo, check || null, pyodide.toPy(entradas || []), antes || null, despues || null);
  f.destroy();
  postMessage({ tipo: 'resultado', id, resultado: JSON.parse(json) });
}

/** Solo permitimos nombres simples dentro de la carpeta de trabajo. */
function nombreSeguro(nombre) {
  const limpio = String(nombre || '').split(/[\\/]/).pop().replace(/[^\w.\- ()áéíóúñÁÉÍÓÚÑ]/g, '_');
  if (!limpio || limpio.startsWith('.') || INTERNOS.has(limpio)) throw new Error('Nombre de archivo no permitido');
  return limpio;
}

/** Operaciones con archivos: subir, listar, descargar y borrar. */
async function archivos(m) {
  await listo;
  const FS = pyodide.FS;
  if (m.tipo === 'subir') {
    const nombre = nombreSeguro(m.nombre);
    FS.writeFile(CARPETA + nombre, new Uint8Array(m.datos));
    return { nombre };
  }
  if (m.tipo === 'listar') {
    return {
      lista: FS.readdir(CARPETA)
        .filter((n) => !n.startsWith('.') && !INTERNOS.has(n) && n !== '__pycache__')
        .map((n) => ({ nombre: n, tamano: FS.stat(CARPETA + n).size, esDir: FS.isDir(FS.stat(CARPETA + n).mode) }))
        .filter((a) => !a.esDir)
        .sort((a, b) => a.nombre.localeCompare(b.nombre)),
    };
  }
  if (m.tipo === 'leer') {
    const datos = FS.readFile(CARPETA + nombreSeguro(m.nombre));
    return { datos: datos.buffer, transferir: true };
  }
  if (m.tipo === 'borrar') {
    FS.unlink(CARPETA + nombreSeguro(m.nombre));
    return {};
  }
  throw new Error('Operación desconocida');
}

onmessage = (ev) => {
  const m = ev.data;
  if (m.tipo === 'iniciar') {
    listo = iniciar(m).catch((e) => postMessage({ tipo: 'fallo', texto: String(e) }));
  } else if (m.tipo === 'correr') {
    correr(m).catch((e) => postMessage({
      tipo: 'resultado', id: m.id,
      resultado: { stdout: '', error: String(e), pista: 'Error interno al ejecutar. Intenta de nuevo.', imagenes: [], check_ok: m.check ? false : null },
    }));
  } else {
    archivos(m).then((r) => {
      const { transferir, ...datos } = r;
      postMessage({ tipo: 'respuesta', id: m.id, ...datos }, transferir ? [datos.datos] : []);
    }).catch((e) => postMessage({ tipo: 'respuesta', id: m.id, error: String(e.message || e) }));
  }
};
