<?php
require __DIR__ . '/includes/bootstrap.php';

$tools = require __DIR__ . '/content/lab_tools.php';
$inicial = $tools['📥 Leer datos']['Leer Excel (todas las hojas)'];

$pageTitle = 'Laboratorio';
$page = 'playground';
$usesPython = true;
$mainClass = 'container lab-main';
$extraScripts = ['assets/js/lab.js'];
require __DIR__ . '/includes/header.php';
?>
<div class="playground-head">
  <div>
    <h1 class="game-title">Laboratorio de datos</h1>
    <p class="muted">Sube tus archivos de <b>Excel</b> o <b>CSV</b> y analízalos con Python. Tus archivos <b>no salen de tu computadora</b>: todo corre dentro de tu navegador.</p>
  </div>
</div>

<div class="lab">
  <aside class="lab-side">
    <section class="lab-panel">
      <h2 class="lab-panel-title">📂 Mis archivos</h2>
      <label class="dropzone" id="dropzone">
        <input type="file" id="file-input" accept=".xlsx,.xlsm,.xls,.csv,.tsv,.txt,.json" multiple hidden>
        <span class="dz-icon">⬆️</span>
        <b>Arrastra tu Excel aquí</b>
        <small>o haz clic para elegirlo · .xlsx .xls .csv</small>
      </label>
      <ul class="file-list" id="file-list"><li class="muted small">Cargando…</li></ul>
      <p class="muted small">💡 Lo que guardes con <code>to_excel()</code> o <code>to_csv()</code> aparece aquí para descargarlo.</p>
    </section>

    <section class="lab-panel">
      <h2 class="lab-panel-title">🧰 Caja de herramientas</h2>
      <div class="toolbox">
        <?php foreach ($tools as $grupo => $items): ?>
          <details class="tool-group" <?= $grupo === '📥 Leer datos' ? 'open' : '' ?>>
            <summary><?= e($grupo) ?></summary>
            <?php foreach ($items as $nombre => $codigo): ?>
              <button class="tool" data-snippet="<?= e($codigo) ?>"><?= e($nombre) ?></button>
            <?php endforeach; ?>
          </details>
        <?php endforeach; ?>
      </div>
      <div class="libs">
        <span>pandas</span><span>numpy</span><span>matplotlib</span><span>openpyxl</span>
        <span>seaborn</span><span>scipy</span><span>scikit-learn</span><span>statsmodels</span>
      </div>
    </section>
  </aside>

  <div class="lab-main-col runner" data-runner data-persist="playground-v2" data-limite="60">
    <div class="lab-editor-head">
      <span class="lab-panel-title">💻 Código</span>
      <span class="muted small">Ctrl + Enter para ejecutar · La primera vez que uses una librería tarda unos segundos</span>
    </div>
    <textarea class="code" data-code><?= e($inicial) ?></textarea>
    <details class="inputs">
      <summary>⌨️ Entradas para <code>input()</code> (una por línea)</summary>
      <textarea data-inputs rows="3" placeholder="Ana&#10;25"></textarea>
    </details>
    <div class="runner-actions">
      <button class="btn btn-primary" data-run>▶ Ejecutar <kbd>Ctrl+Enter</kbd></button>
      <button class="btn btn-ghost" data-clear>🧹 Limpiar salida</button>
    </div>
    <div class="output-title">📊 Resultado</div>
    <div class="output lab-output" data-output><span class="muted">Aquí aparecerán tus tablas, gráficos y mensajes.</span></div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
