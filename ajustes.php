<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Ajustes de sonido';
$page = 'ajustes';
$mainClass = 'container ajustes-main';
$extraScripts = ['assets/js/ajustes.js'];
require __DIR__ . '/includes/header.php';
?>
<h1 class="game-title">⚙️ Ajustes de sonido</h1>
<p class="muted">Elige cómo suena tu aventura. Todo se guarda en <b>este navegador</b>: tus archivos de audio no se suben a ningún servidor.</p>

<section class="card aj-master">
  <label class="switch">
    <input type="checkbox" id="aj-activo">
    <span class="switch-ui"></span>
    <b>Sonidos activados</b>
  </label>
  <label class="aj-vol">
    <span>🔈</span>
    <input type="range" id="aj-volumen" min="0" max="100" step="5">
    <span>🔊</span>
    <b id="aj-volumen-num">80%</b>
  </label>
  <button class="btn btn-ghost btn-sm" id="aj-probar">▶ Probar</button>
</section>

<h2>🎛️ Paquete de sonidos</h2>
<p class="muted small">Es el sonido base de toda la app. Toca ▶ para escuchar cada paquete.</p>
<div class="aj-paquetes" id="aj-paquetes"></div>

<h2>🎚️ Personaliza cada evento</h2>
<p class="muted small">Cada evento puede usar el paquete general, el de otro paquete, un archivo tuyo o quedarse en silencio. Formatos: mp3, wav, ogg o m4a (máx. 2 MB).</p>
<div class="card aj-eventos" id="aj-eventos"></div>

<div class="runner-actions">
  <button class="btn btn-ghost" id="aj-reset">↺ Restablecer todo</button>
  <a class="btn btn-primary" href="learn.php">🎮 Volver a jugar</a>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
