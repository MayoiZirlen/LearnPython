<?php
require __DIR__ . '/includes/bootstrap.php';

$user = current_user();
$done = $user ? completed_lessons((int) $user['id']) : [];
$current = recommended_lesson($done);
$pageTitle = 'Aprender';
$page = 'learn';
require __DIR__ . '/includes/header.php';
?>
<div class="learn-head">
  <h1>🗺️ Mapa de aprendizaje</h1>
  <p class="muted">Sigue el camino en orden o salta a lo que te interese. Cada lección tiene explicación, ejemplos, quiz y retos.</p>
  <?php if (!$user): ?>
    <div class="alert alert-info">Estás como invitado: puedes practicar todo, pero <a href="register.php">crea una cuenta</a> para guardar tu progreso y ganar XP.</div>
  <?php endif; ?>
</div>

<?php foreach (course() as $mi => $m):
    $mdone = count(array_intersect($m['lessons'], $done));
    $mtotal = count($m['lessons']);
?>
<section class="module" style="--mod: <?= e($m['color']) ?>">
  <div class="module-head">
    <div class="module-icon"><?= $m['icon'] ?></div>
    <div class="module-info">
      <div class="muted small">Módulo <?= $mi + 1 ?></div>
      <h2><?= e($m['title']) ?></h2>
      <p class="muted"><?= e($m['description']) ?></p>
    </div>
    <div class="module-progress">
      <div class="ring" style="--p: <?= $mtotal ? round(100 * $mdone / $mtotal) : 0 ?>"><span><?= $mdone ?>/<?= $mtotal ?></span></div>
    </div>
  </div>
  <div class="path">
    <?php foreach ($m['lessons'] as $li => $slug):
        $l = load_lesson($slug);
        $state = in_array($slug, $done, true) ? 'done' : ($slug === $current ? 'current' : 'todo');
    ?>
      <a class="node node-<?= $state ?>" href="lesson.php?l=<?= e($slug) ?>" style="--i: <?= $li ?>">
        <span class="node-circle"><?= $state === 'done' ? '✓' : ($l['icon'] ?? '📘') ?></span>
        <span class="node-label">
          <b><?= e($l['title']) ?></b>
          <small><?= e($l['summary']) ?></small>
          <small class="node-meta">⏱ <?= (int) ($l['minutes'] ?? 8) ?> min · ⭐ <?= array_sum(array_map('step_xp', $l['steps'])) + 50 ?> XP</small>
        </span>
        <?php if ($state === 'current'): ?><span class="node-flag">¡Aquí!</span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endforeach; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
