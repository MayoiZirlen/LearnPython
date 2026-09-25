<?php
require __DIR__ . '/includes/bootstrap.php';

$user = current_user();
$rows = db()->query('SELECT u.id, u.username, u.avatar, u.xp, u.streak,
        (SELECT COUNT(*) FROM lesson_progress lp WHERE lp.user_id = u.id) AS lessons
    FROM users u ORDER BY u.xp DESC, u.id ASC LIMIT 50')->fetchAll();

$pageTitle = 'Ranking';
$page = 'ranking';
$mainClass = 'container narrow';
require __DIR__ . '/includes/header.php';
?>
<h1>🏆 Ranking</h1>
<p class="muted">Las personas con más experiencia. ¡Cada ejercicio cuenta!</p>
<div class="card ranking">
  <?php if (!$rows): ?>
    <p class="muted center">Todavía no hay nadie. ¡Sé la primera persona!</p>
  <?php endif; ?>
  <?php foreach ($rows as $i => $r): $lvl = level_info((int) $r['xp']); ?>
    <div class="rank-row <?= $user && $user['id'] == $r['id'] ? 'me' : '' ?>">
      <div class="rank-pos"><?= ['🥇', '🥈', '🥉'][$i] ?? ($i + 1) ?></div>
      <div class="rank-avatar"><?= e($r['avatar']) ?></div>
      <div class="rank-name"><b><?= e($r['username']) ?></b><div class="muted small"><?= $lvl['icon'] ?> <?= e($lvl['name']) ?> · 📚 <?= (int) $r['lessons'] ?> · 🔥 <?= (int) $r['streak'] ?></div></div>
      <div class="rank-xp"><?= (int) $r['xp'] ?> XP</div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
