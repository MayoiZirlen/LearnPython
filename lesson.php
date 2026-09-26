<?php
require __DIR__ . '/includes/bootstrap.php';

$slug = (string) ($_GET['l'] ?? '');
$lesson = load_lesson($slug);
if (!$lesson) {
    http_response_code(404);
    flash('No encontramos esa lección.', 'error');
    redirect('learn.php');
}
$user = current_user();
$module = module_of($slug);
$doneSteps = $user ? completed_steps((int) $user['id'], $slug) : [];
$lessonDone = $user && in_array($slug, completed_lessons((int) $user['id']), true);
$next = next_lesson($slug);

$mult = xp_multiplier($slug);
// Solo enviamos al navegador lo que necesita para mostrar la lección.
$payload = [
    'slug' => $slug,
    'title' => $lesson['title'],
    'steps' => array_map(function ($s) use ($mult) {
        $s['xp'] = step_xp($s) * $mult;
        return $s;
    }, $lesson['steps']),
    'doneSteps' => $doneSteps,
    'lessonDone' => $lessonDone,
    'next' => $next,
    'nextTitle' => $next ? load_lesson($next)['title'] : null,
    'unlocksNgPlus' => $next && is_ngplus($next) && !is_ngplus($slug),
    'unlocksInfierno' => $next && is_infierno($next) && !is_infierno($slug),
    'infierno' => is_infierno($slug),
    'bonus' => 50 * $mult,
];

$pageTitle = $lesson['title'];
$page = 'learn';
$usesPython = true;
$mainClass = 'lesson-main';
$extraScripts = ['assets/js/lesson.js'];
require __DIR__ . '/includes/header.php';
?>
<div class="lesson is-<?= e(module_mode($module)) ?>" style="--mod: <?= e($module['color']) ?>">
  <div class="lesson-top">
    <a class="close" href="learn.php" title="Volver al mapa">✕</a>
    <div class="lesson-progress"><div id="lesson-bar"></div></div>
    <div class="lesson-counter" id="lesson-counter"></div>
  </div>
  <div class="lesson-title">
    <span class="stage-tag">Nivel <?= e(stage_label($slug)) ?></span>
    <span class="muted small"><?= e($module['icon'] . ' ' . $module['title']) ?></span>
    <h1><?= e(($lesson['icon'] ?? '') . ' ' . $lesson['title']) ?></h1>
  </div>
  <?php if (!$user): ?>
    <div class="alert alert-info">Modo invitado: tu progreso no se guardará. <a href="register.php">Crea una cuenta</a> para ganar XP.</div>
  <?php endif; ?>
  <div id="step-container"></div>
  <div class="lesson-nav">
    <button class="btn btn-ghost" id="btn-prev">◀ Atrás</button>
    <div class="dots" id="dots"></div>
    <button class="btn btn-primary" id="btn-next">Continuar ▶</button>
  </div>
</div>
<script>window.LESSON = <?= json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
