<?php
require __DIR__ . '/includes/bootstrap.php';

$user = current_user();
$usesPython = !$user;
$pageTitle = 'Inicio';
$page = 'home';

$tips = [
    '¿Sabías que el nombre Python viene de los Monty Python y no de la serpiente? 🎬',
    'Los errores no son fracasos: son pistas. Léelos con calma, casi siempre dicen dónde está el problema.',
    'Un analista pasa cerca del 80% del tiempo limpiando datos. ¡Por eso hay un módulo entero de limpieza!',
    'En Python, las posiciones empiezan en 0. La primera fila de una tabla es la fila 0.',
    'Escribir código poquito cada día vale más que un maratón una vez al mes. ¡Cuida tu racha! 🔥',
    'Pandas se llama así por "panel data", no por el oso. Aunque el oso es más bonito. 🐼',
    'Ctrl + Enter ejecuta tu código en cualquier editor del sitio.',
];
$tip = $tips[(int) date('z') % count($tips)];

if ($user) {
    $uid = (int) $user['id'];
    $done = completed_lessons($uid);
    $lvl = level_info((int) $user['xp']);
    $nextSlug = recommended_lesson($done);
    $next = load_lesson($nextSlug);
    $nextModule = module_of($nextSlug);
    $total = count(lesson_order());
    $mine = user_achievements($uid);
}

require __DIR__ . '/includes/header.php';
?>
<?php if ($user): ?>
<section class="dash">
  <div class="card dash-hello">
    <div class="mascot">🐍</div>
    <div>
      <h1>¡Hola, <?= e($user['username']) ?>!</h1>
      <p class="muted"><?= e($tip) ?></p>
    </div>
  </div>

  <div class="dash-grid">
    <div class="card level-card">
      <div class="level-icon"><?= $lvl['icon'] ?></div>
      <div class="level-body">
        <div class="muted small">Nivel <?= $lvl['number'] ?></div>
        <h2><?= e($lvl['name']) ?></h2>
        <div class="bar"><div style="width: <?= $lvl['pct'] ?>%"></div></div>
        <div class="muted small"><?= $lvl['xp'] ?> XP<?= $lvl['next_xp'] ? ' · faltan ' . ($lvl['next_xp'] - $lvl['xp']) . ' para el siguiente nivel' : ' · ¡nivel máximo!' ?></div>
      </div>
    </div>
    <div class="card stat-card">
      <div class="big">🔥 <?= (int) $user['streak'] ?></div>
      <div class="muted">días de racha</div>
    </div>
    <div class="card stat-card">
      <div class="big">📚 <?= count($done) ?>/<?= $total ?></div>
      <div class="muted">lecciones</div>
    </div>
    <div class="card stat-card">
      <div class="big">🏅 <?= count($mine) ?></div>
      <div class="muted">logros</div>
    </div>
  </div>

  <?php if (ngplus_unlocked($done) && !array_intersect(ngplus_lessons(), $done)): ?>
    <div class="ngplus-unlock dash-unlock">
      <div class="ngplus-logo">NEW GAME<span>+</span></div>
      <b>¡Desbloqueado!</b><small>Terminaste el juego principal. 12 lecciones nuevas y un jefe final te esperan.</small>
    </div>
  <?php endif; ?>
  <?php if (infierno_unlocked($done) && !array_intersect(infierno_lessons(), $done)): ?>
    <div class="infierno-unlock dash-unlock">
      <div class="infierno-logo">INFIERNO</div>
      <b>¡Las puertas se abrieron!</b><small>Sin pistas, sin soluciones, 3 vidas por lección y XP ×2. ¿Te atreves? 😈</small>
    </div>
  <?php endif; ?>
  <div class="card continue-card is-<?= e(module_mode($nextModule)) ?>" style="--mod: <?= e($nextModule['color']) ?>">
    <div>
      <div class="muted small"><?= count($done) ? 'Continúa donde te quedaste' : 'Tu aventura empieza aquí' ?> · <?= e($nextModule['title']) ?></div>
      <h2><?= e($nextModule['icon'] . ' ' . $next['title']) ?></h2>
      <p class="muted"><?= e($next['summary']) ?></p>
    </div>
    <a class="btn btn-primary btn-lg" href="lesson.php?l=<?= e($nextSlug) ?>">¡Vamos! ▶</a>
  </div>
</section>
<?php else: ?>
<section class="hero">
  <div class="hero-text">
    <h1>Aprende <span class="hl">Python</span> desde cero y conviértete en <span class="hl2">analista de datos</span></h1>
    <p class="lead">Lecciones cortas, ejercicios que se corrigen solos, XP, rachas y logros. Sin instalar nada: Python corre directo en tu navegador.</p>
    <div class="hero-cta">
      <a class="btn btn-primary btn-lg" href="register.php">Empieza gratis 🚀</a>
      <a class="btn btn-ghost btn-lg" href="lesson.php?l=<?= e(lesson_order()[0]) ?>">Probar la primera lección</a>
    </div>
  </div>
  <div class="card try-card">
    <div class="try-title">✨ Pruébalo ahora: cambia el nombre y pulsa Ejecutar</div>
    <div class="runner" data-runner>
      <textarea class="code" data-code>nombre = "Ana"
edad = 25
print(f"Hola, {nombre}!")
print(f"En 10 años tendrás {edad + 10} años 🎂")</textarea>
      <div class="runner-actions"><button class="btn btn-primary" data-run>▶ Ejecutar</button></div>
      <div class="output" data-output></div>
    </div>
  </div>
</section>

<section class="features">
  <div class="card feature"><div class="f-icon">🧩</div><h3>Paso a paso</h3><p>Micro-lecciones de 5 a 10 minutos: explicación, ejemplo, quiz y reto práctico.</p></div>
  <div class="card feature"><div class="f-icon">🤖</div><h3>Corrección al instante</h3><p>Cada ejercicio se revisa automáticamente y los errores se explican en español.</p></div>
  <div class="card feature"><div class="f-icon">🎮</div><h3>Como un juego</h3><p>Gana XP, sube de nivel, mantén tu racha y desbloquea logros.</p></div>
  <div class="card feature"><div class="f-icon">📊</div><h3>Datos reales</h3><p>Llega a NumPy, pandas y gráficos analizando ventas y clima de verdad.</p></div>
</section>

<section class="route">
  <h2>Tu ruta de aprendizaje</h2>
  <div class="route-list">
    <?php foreach (course() as $i => $m): ?>
      <div class="route-item" style="--mod: <?= e($m['color']) ?>">
        <div class="route-num"><?= $m['icon'] ?></div>
        <div><b><?= e($m['title']) ?></b><div class="muted small"><?= e($m['description']) ?></div></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
