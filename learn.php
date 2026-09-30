<?php
require __DIR__ . '/includes/bootstrap.php';

$user = current_user();
$done = $user ? completed_lessons((int) $user['id']) : [];
$ranks = $user ? lesson_ranks((int) $user['id']) : [];
$current = recommended_lesson($done);
$currentTier = module_of($current)['tier'];
$ngUnlocked = ngplus_unlocked($done);
$infUnlocked = infierno_unlocked($done);
$ngDone = count(array_intersect(ngplus_lessons(), $done));
$mainDone = count(array_intersect(main_lessons(), $done));

// Datos de cada lección para el panel de detalle.
$details = [];
foreach (course() as $m) {
    foreach ($m['lessons'] as $slug) {
        $l = load_lesson($slug);
        $details[$slug] = [
            'title' => $l['title'],
            'summary' => $l['summary'],
            'icon' => $l['icon'] ?? '📘',
            'module' => $m['icon'] . ' ' . $m['title'],
            'color' => $m['color'],
            'stage' => stage_label($slug),
            'minutes' => (int) ($l['minutes'] ?? 8),
            'xp' => lesson_total_xp($l),
            'steps' => count($l['steps']),
            'stars' => lesson_difficulty($slug),
            'rank' => $ranks[$slug] ?? null,
            'ngplus' => !empty($m['ngplus']),
            'modo' => module_mode($m),
        ];
    }
}

$pageTitle = 'Selección de lección';
$page = 'learn';
$mainClass = 'select-main';
$extraScripts = ['assets/js/select.js'];
require __DIR__ . '/includes/header.php';
?>
<div class="select-screen">
  <div class="select-head">
    <h1 class="game-title">Selección de lección</h1>
    <div class="clock" id="clock">00:00</div>
    <div class="tier-tabs" role="tablist">
      <?php foreach (TIERS as $key => $label): ?>
        <button class="tier-tab <?= $key === $currentTier ? 'active' : '' ?> <?= $key === 'ngplus' ? 'tier-ngplus' : '' ?> <?= $key === 'infierno' ? 'tier-infierno' : '' ?>" data-tier="<?= $key ?>" role="tab"><?php if (in_array($key, ['ngplus', 'infierno'], true)): ?><span class="tab-lock" <?= ($key === 'ngplus' ? $ngUnlocked : $infUnlocked) ? 'hidden' : '' ?>>🔒 </span><?php endif; ?><?= $key === 'infierno' ? '🔥 ' : '' ?><?= e($label) ?></button>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (!$user): ?>
    <div class="alert alert-info guest-alert">Estás jugando como invitado: <a href="register.php">crea una cuenta</a> para guardar tu partida, ganar XP y sellos de rango.</div>
  <?php endif; ?>

  <div class="select-body">
    <div class="song-list" id="song-list">
      <?php $ngBanner = false; $infBanner = false; foreach (course() as $mi => $m): ?>
        <div class="song-group is-<?= e(module_mode($m)) ?>" data-tier="<?= e($m['tier']) ?>" style="--mod: <?= e($m['color']) ?>">
          <?php if (module_mode($m) === 'infierno' && !$infBanner): $infBanner = true; ?>
            <div class="infierno-banner">
              <div class="infierno-logo">INFIERNO</div>
              <ul class="infierno-rules">
                <li>🚫💡 Sin pistas</li><li>🚫👀 Sin soluciones</li><li>❤️❤️❤️ 3 vidas por lección</li><li>☠️ Game Over = pierdes el progreso</li><li>⭐ XP ×2</li>
              </ul>
              <p data-lock="open" <?= $infUnlocked ? '' : 'hidden' ?>>🔓 <b>Las puertas están abiertas.</b> Buena suerte… la vas a necesitar. 😈</p>
              <p data-lock="closed" <?= $infUnlocked ? 'hidden' : '' ?>>🔒 Se desbloquea al terminar el juego principal y el New Game Plus (<b><span data-cuenta="main+ngplus"><?= $mainDone + $ngDone ?></span>/<?= count(main_lessons()) + count(ngplus_lessons()) ?></b>). Puedes asomarte… bajo tu propio riesgo. 🔥</p>
            </div>
          <?php endif; ?>
          <?php if (!empty($m['ngplus']) && !$ngBanner): $ngBanner = true; ?>
            <div class="ngplus-banner">
              <div class="ngplus-logo">NEW GAME<span>+</span></div>
              <p data-lock="open" <?= $ngUnlocked ? '' : 'hidden' ?>>🔓 <b>¡Desbloqueado!</b> Terminaste el juego principal. Ahora los enemigos son más fuertes… y tú también.</p>
              <p data-lock="closed" <?= $ngUnlocked ? 'hidden' : '' ?>>🔒 Se desbloquea al terminar el juego principal (<b><span data-cuenta="main"><?= $mainDone ?></span>/<?= count(main_lessons()) ?></b> lecciones). Puedes entrar de todos modos, pero ¡la dificultad sube mucho! 😈</p>
            </div>
          <?php endif; ?>
          <div class="song-group-title"><span><?= $m['icon'] ?> Módulo <?= e(module_number($m)) ?></span> <?= e($m['title']) ?></div>
          <?php foreach ($m['lessons'] as $slug): $d = $details[$slug];
              $isNew = !isset($ranks[$slug]) && $slug === $current;
          ?>
            <a class="song-row <?= $slug === $current ? 'selected' : '' ?>" href="lesson.php?l=<?= e($slug) ?>" data-slug="<?= e($slug) ?>">
              <span class="song-badge"><?php if ($isNew): ?><span class="new-badge">New</span><?php else: ?><span class="stage-num"><?= e($d['stage']) ?></span><?php endif; ?></span>
              <span class="song-name"><?= e($d['icon'] . ' ' . $d['title']) ?></span>
              <span class="song-rank"><?php if ($d['rank']): ?><span class="rank rank-<?= e($d['rank']) ?>"><?= e(RANK_LABELS[$d['rank']]) ?></span><?php endif; ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <aside class="detail-panel" id="detail">
      <div class="mascot-stage">
        <div class="burst"></div>
        <div class="ring ring-a"></div>
        <div class="ring ring-b"></div>
        <div class="mascot-big" id="d-icon">🐍</div>
        <div class="mascot-sidekick">🐍</div>
      </div>
      <div class="detail-card">
        <div class="detail-stage">Nivel <b id="d-stage"></b> · <span id="d-module"></span></div>
        <h2 id="d-title"></h2>
        <p id="d-summary"></p>
        <div class="detail-stats">
          <span>⏱ <b id="d-minutes"></b> min</span>
          <span>⭐ <b id="d-xp"></b> XP</span>
          <span>🧩 <b id="d-steps"></b> pasos</span>
        </div>
      </div>
      <div class="difficulty">
        <div class="difficulty-label">Dificultad</div>
        <div class="stars" id="d-stars"></div>
      </div>
    </aside>
  </div>

  <div class="select-foot">
    <a class="btn-game" href="index.php">Atrás</a>
    <span class="key-help">↑ ↓ elegir · ← → dificultad · Enter jugar</span>
    <a class="btn-game btn-game-go" id="btn-go" href="#">¡Jugar!</a>
  </div>
</div>
<script>window.LESSONS = <?= json_encode($details, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
