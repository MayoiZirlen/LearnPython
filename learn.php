<?php
require __DIR__ . '/includes/bootstrap.php';

$user = current_user();
$done = $user ? completed_lessons((int) $user['id']) : [];
$ranks = $user ? lesson_ranks((int) $user['id']) : [];
$current = recommended_lesson($done);
$currentTier = module_of($current)['tier'];

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
            'xp' => array_sum(array_map('step_xp', $l['steps'])) + 50,
            'steps' => count($l['steps']),
            'stars' => lesson_difficulty($slug),
            'rank' => $ranks[$slug] ?? null,
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
        <button class="tier-tab <?= $key === $currentTier ? 'active' : '' ?>" data-tier="<?= $key ?>" role="tab"><?= e($label) ?></button>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (!$user): ?>
    <div class="alert alert-info">Estás jugando como invitado: <a href="register.php">crea una cuenta</a> para guardar tu partida, ganar XP y sellos de rango.</div>
  <?php endif; ?>

  <div class="select-body">
    <div class="song-list" id="song-list">
      <?php foreach (course() as $mi => $m): ?>
        <div class="song-group" data-tier="<?= e($m['tier']) ?>" style="--mod: <?= e($m['color']) ?>">
          <div class="song-group-title"><span><?= $m['icon'] ?> Módulo <?= $mi + 1 ?></span> <?= e($m['title']) ?></div>
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
