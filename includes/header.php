<?php
/** @var string $pageTitle */
/** @var string $page  identificador de la sección activa */
$user = current_user();
$lvl = $user ? level_info((int) $user['xp']) : null;
$page = $page ?? '';
$usesPython = $usesPython ?? false;
$f = flash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0a0a0a">
<title><?= e($pageTitle ?? 'PyAprende') ?> · PyAprende</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🐍</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Barlow+Condensed:ital,wght@0,600;0,800;1,800&family=Nunito:wght@400;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<?php if ($usesPython): ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/python/python.min.js"></script>
<?php endif; ?>
<link rel="stylesheet" href="assets/css/style.css">
<script>
window.PYAPRENDE = <?= json_encode([
    'logged' => (bool) $user,
    'csrf' => csrf_token(),
    'pyodideUrl' => config('pyodide_url'),
], JSON_UNESCAPED_SLASHES) ?>;
</script>
</head>
<body>
<div class="bg-fx" aria-hidden="true">
  <span class="deco-star d1">★</span><span class="deco-star d2">★</span><span class="deco-star d3">★</span><span class="deco-star d4">★</span>
  <span class="deco-ring r1"></span><span class="deco-ring r2"></span>
</div>
<header class="topbar">
  <a class="brand" href="index.php"><span class="brand-logo">🐍</span><span class="brand-text">Py<b>Aprende</b></span></a>
  <nav class="nav">
    <a href="learn.php" class="<?= $page === 'learn' ? 'active' : '' ?>">🗺️ <span>Aprender</span></a>
    <a href="playground.php" class="<?= $page === 'playground' ? 'active' : '' ?>">🧪 <span>Laboratorio</span></a>
    <a href="ranking.php" class="<?= $page === 'ranking' ? 'active' : '' ?>">🏆 <span>Ranking</span></a>
  </nav>
  <div class="user-area">
    <?php if ($user): ?>
      <span class="pill streak" title="Racha de días">🔥 <b id="hdr-streak"><?= (int) $user['streak'] ?></b></span>
      <span class="pill xp" title="Experiencia">⭐ <b id="hdr-xp"><?= (int) $user['xp'] ?></b> XP</span>
      <a class="avatar" href="profile.php" title="<?= e($lvl['name']) ?>"><?= e($user['avatar']) ?></a>
    <?php else: ?>
      <a class="btn btn-ghost btn-sm" href="login.php">Entrar</a>
      <a class="btn btn-primary btn-sm" href="register.php">Crear cuenta</a>
    <?php endif; ?>
  </div>
</header>
<?php if ($f): ?>
  <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endif; ?>
<main class="<?= e($mainClass ?? 'container') ?>">
