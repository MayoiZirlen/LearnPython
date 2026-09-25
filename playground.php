<?php
require __DIR__ . '/includes/bootstrap.php';

$tools = require __DIR__ . '/content/lab_tools.php';
$inicial = $tools['📥 Leer datos']['Leer Excel (todas las hojas)'];

$pageTitle = 'Laboratorio';
$page = 'playground';
$usesPython = true;
$mainClass = 'container lab-main';
$extraScripts = ['assets/js/lab.js', 'assets/js/asistente.js'];
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

  <div class="lab-main-col">
  <div class="mode-tabs" role="tablist">
    <button class="mode-tab" data-modo="asistente" role="tab">🪄 Asistente de Excel <small>sin código</small></button>
    <button class="mode-tab" data-modo="codigo" role="tab">💻 Código libre</button>
  </div>

  <section class="wizard" id="asistente" data-panel="asistente">
    <div class="wz-source">
      <label>📗 Archivo <select id="wz-archivo"></select></label>
      <label>📄 Hoja <select id="wz-hoja"></select></label>
      <span class="wz-carga" id="wz-carga"></span>
      <button class="btn btn-ghost btn-sm" id="wz-reiniciar">↺ Empezar de nuevo</button>
    </div>
    <div class="wz-recipes"><span class="muted small">Recetas rápidas:</span> <span id="wz-recetas"></span></div>
    <div class="wz-grid">
      <div class="wz-left">
        <div class="lab-panel-title">🪄 Tus acciones</div>
        <p class="muted small">Elige qué quieres hacerle a tu tabla. Cada acción se aplica sobre el resultado de la anterior, y te mostramos el código Python que la hace.</p>
        <ol class="wz-steps" id="wz-pasos"></ol>
        <button class="btn btn-primary" id="wz-agregar">➕ Agregar acción</button>
        <div class="wz-menu" id="wz-menu" hidden></div>
      </div>
      <div class="wz-right">
        <div class="lab-panel-title">🐍 Así se hace en Python</div>
        <p class="muted small">Pasa el mouse sobre una acción para ver sus líneas resaltadas.</p>
        <div class="wz-code" id="wz-codigo"></div>
        <div class="runner-actions">
          <button class="btn btn-ghost btn-sm" id="wz-abrir">✏️ Abrir en el editor</button>
          <button class="btn btn-ghost btn-sm" id="wz-copiar">📋 Copiar código</button>
        </div>
      </div>
    </div>
    <div class="output-title">📊 Resultado</div>
    <div class="output lab-output" id="wz-salida"></div>
  </section>

  <div class="runner" data-panel="codigo" data-runner data-persist="playground-v2" data-limite="60">
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
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
