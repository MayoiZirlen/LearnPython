/* Web Worker que ejecuta Python con Pyodide, fuera del hilo de la página.
 * Así un bucle infinito no congela el sitio: la página puede terminar el worker y crear otro. */
let pyodide = null;
let listo = null;

async function iniciar({ pyodideUrl, harnessUrl, datasets }) {
  importScripts(pyodideUrl + 'pyodide.js');
  postMessage({ tipo: 'estado', texto: 'Despertando a Python…' });
  pyodide = await loadPyodide({ indexURL: pyodideUrl });
  pyodide.setStdout({ batched: () => {} });

  const harness = await (await fetch(harnessUrl)).text();
  pyodide.FS.writeFile('/home/pyodide/harness.py', harness);

  // Copiamos los datasets al disco virtual para que pd.read_csv('ventas.csv') funcione.
  for (const d of datasets) {
    try {
      const datos = new Uint8Array(await (await fetch(d.url)).arrayBuffer());
      pyodide.FS.writeFile('/home/pyodide/' + d.nombre, datos);
    } catch (e) { /* un dataset faltante no debe impedir usar Python */ }
  }
  await pyodide.runPythonAsync('import os, sys\nos.chdir("/home/pyodide")\nsys.path.insert(0, "/home/pyodide")\nimport harness');
  postMessage({ tipo: 'listo' });
}

async function correr({ id, codigo, check, entradas }) {
  await listo;
  const paquetes = [];
  if (/\.(plot|hist|boxplot)\s*\(/.test(codigo) || /matplotlib/.test(codigo)) paquetes.push('matplotlib');
  if (paquetes.length || /\bimport\b/.test(codigo + (check || ''))) {
    postMessage({ tipo: 'estado', id, texto: 'Cargando librerías (solo la primera vez)…' });
    // Si algo no se puede descargar seguimos: el error aparecerá al importar, explicado en español.
    try {
      if (paquetes.length) await pyodide.loadPackage(paquetes);
      await pyodide.loadPackagesFromImports(codigo);
      if (check) await pyodide.loadPackagesFromImports(check);
    } catch (e) { /* sin conexión o paquete inexistente */ }
    const precargar = pyodide.globals.get('harness').precargar;
    try { precargar(codigo); } catch (e) { /* si falla, el error se verá al ejecutar */ }
    precargar.destroy();
  }
  postMessage({ tipo: 'corriendo', id });
  const f = pyodide.globals.get('harness').ejecutar_json;
  const json = f(codigo, check || null, pyodide.toPy(entradas || []));
  f.destroy();
  postMessage({ tipo: 'resultado', id, resultado: JSON.parse(json) });
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
  }
};
