<?php
require __DIR__ . '/includes/bootstrap.php';

$user = require_login();
$uid = (int) $user['id'];
$lvl = level_info((int) $user['xp']);
$mine = user_achievements($uid);
$done = completed_lessons($uid);
$runs = get_stat($uid, 'runs');
$charts = get_stat($uid, 'charts');

$pageTitle = 'Mi perfil';
$page = 'profile';
require __DIR__ . '/includes/header.php';
?>
<div class="card profile-card">
  <div class="profile-avatar"><?= e($user['avatar']) ?></div>
  <div class="profile-info">
    <h1><?= e($user['username']) ?></h1>
    <div class="muted">Nivel <?= $lvl['number'] ?> · <?= $lvl['icon'] ?> <?= e($lvl['name']) ?></div>
    <div class="bar"><div style="width: <?= $lvl['pct'] ?>%"></div></div>
    <div class="muted small"><?= $lvl['xp'] ?> XP<?= $lvl['next_xp'] ? ' / ' . $lvl['next_xp'] : '' ?></div>
  </div>
  <a class="btn btn-ghost btn-sm" href="logout.php">Cerrar sesión</a>
</div>

<div class="dash-grid">
  <div class="card stat-card"><div class="big">🔥 <?= (int) $user['streak'] ?></div><div class="muted">días de racha</div></div>
  <div class="card stat-card"><div class="big">📚 <?= count($done) ?></div><div class="muted">lecciones completadas</div></div>
  <div class="card stat-card"><div class="big">▶ <?= $runs ?></div><div class="muted">veces que ejecutaste código</div></div>
  <div class="card stat-card"><div class="big">📈 <?= $charts ?></div><div class="muted">gráficos creados</div></div>
</div>

<h2>🏅 Logros (<?= count($mine) ?>/<?= count(achievements()) ?>)</h2>
<div class="badges">
  <?php foreach (achievements() as $code => [$icon, $title, $desc]): $has = isset($mine[$code]); ?>
    <div class="badge <?= $has ? 'unlocked' : 'locked' ?>" title="<?= e($desc) ?>">
      <div class="badge-icon"><?= $has ? $icon : '🔒' ?></div>
      <b><?= e($title) ?></b>
      <small class="muted"><?= e($desc) ?></small>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
