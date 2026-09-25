<?php
// Niveles, rachas y logros.

const LEVELS = [
    [0,    'Huevo de pitón',       '🥚'],
    [100,  'Culebrita curiosa',    '🐣'],
    [250,  'Pitón aprendiz',       '🐍'],
    [500,  'Domador de listas',    '📋'],
    [800,  'Explorador de datos',  '🔎'],
    [1200, 'Pandas padawan',       '🐼'],
    [1700, 'Analista de datos',    '📊'],
    [2300, 'Mago de los datos',    '🧙'],
    [3200, 'Leyenda de los datos', '🐉'],
    [4200, 'Maestro New Game+',    '👑'],
    [5200, 'Deidad de los datos',  '🌌'],
];

function level_info(int $xp): array
{
    $n = 0;
    foreach (LEVELS as $i => $lvl) {
        if ($xp >= $lvl[0]) {
            $n = $i;
        }
    }
    $cur = LEVELS[$n];
    $next = LEVELS[$n + 1] ?? null;
    $pct = $next ? (int) floor(100 * ($xp - $cur[0]) / ($next[0] - $cur[0])) : 100;
    return [
        'number' => $n + 1,
        'name' => $cur[1],
        'icon' => $cur[2],
        'xp' => $xp,
        'next_xp' => $next[0] ?? null,
        'pct' => $pct,
    ];
}

function achievements(): array
{
    return [
        'primer_codigo'    => ['🚀', 'Despegue', 'Ejecutaste tu primer código.'],
        'cien_ejecuciones' => ['⚡', 'Teclado en llamas', 'Ejecutaste código 100 veces.'],
        'primera_leccion'  => ['🎓', 'Primer paso', 'Completaste tu primera lección.'],
        'cinco_lecciones'  => ['🔥', 'Imparable', 'Completaste 5 lecciones.'],
        'diez_lecciones'   => ['🏅', 'Constancia', 'Completaste 10 lecciones.'],
        'primer_grafico'   => ['📈', 'Artista de datos', 'Creaste tu primer gráfico.'],
        'racha_3'          => ['📅', 'Tres días seguidos', 'Practicaste 3 días seguidos.'],
        'racha_7'          => ['🌟', 'Semana perfecta', 'Practicaste 7 días seguidos.'],
        'xp_1000'          => ['💎', 'Mil puntos', 'Juntaste 1000 XP.'],
        'mod_fundamentos'  => ['🧱', 'Cimientos sólidos', 'Terminaste el módulo Primeros pasos.'],
        'mod_numpy'        => ['🔢', 'Números a toda velocidad', 'Terminaste el módulo de NumPy.'],
        'mod_pandas'       => ['🐼', 'Amigo de los pandas', 'Terminaste el módulo de pandas.'],
        'mod_graficos'     => ['🎨', 'Pintor de datos', 'Terminaste el módulo de visualización.'],
        'mod_proyecto'     => ['🏆', 'Analista graduado', 'Terminaste el proyecto final.'],
        'ngplus_inicio'    => ['🌀', 'New Game Plus', 'Completaste tu primera lección del New Game Plus.'],
        'mod_ngplus'       => ['👹', 'Jefe NG+ derrotado', 'Terminaste el Jefe Final del New Game Plus.'],
        'platino'          => ['💠', 'Trofeo de platino', 'Completaste TODAS las lecciones, incluido el New Game Plus.'],
    ];
}

function user_achievements(int $userId): array
{
    $st = db()->prepare('SELECT code, unlocked_at FROM user_achievements WHERE user_id = ?');
    $st->execute([$userId]);
    return array_column($st->fetchAll(), 'unlocked_at', 'code');
}

function get_stat(int $userId, string $stat): int
{
    $st = db()->prepare('SELECT value FROM user_stats WHERE user_id = ? AND stat = ?');
    $st->execute([$userId, $stat]);
    return (int) ($st->fetchColumn() ?: 0);
}

function bump_stat(int $userId, string $stat): int
{
    db()->prepare('INSERT INTO user_stats (user_id, stat, value) VALUES (?, ?, 1)
                   ON DUPLICATE KEY UPDATE value = value + 1')->execute([$userId, $stat]);
    return get_stat($userId, $stat);
}

function add_xp(int $userId, int $xp): void
{
    if ($xp > 0) {
        db()->prepare('UPDATE users SET xp = xp + ? WHERE id = ?')->execute([$xp, $userId]);
    }
}

/** Actualiza la racha de días: se llama con cada actividad del usuario. */
function touch_streak(int $userId): void
{
    $st = db()->prepare('SELECT streak, last_active FROM users WHERE id = ?');
    $st->execute([$userId]);
    $u = $st->fetch();
    $today = date('Y-m-d');
    if ($u['last_active'] === $today) {
        return;
    }
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $streak = ($u['last_active'] === $yesterday) ? (int) $u['streak'] + 1 : 1;
    db()->prepare('UPDATE users SET streak = ?, last_active = ? WHERE id = ?')
        ->execute([$streak, $today, $userId]);
}

/**
 * Revisa qué logros se cumplen y desbloquea los nuevos.
 * Devuelve la lista de logros recién ganados (para celebrarlos en pantalla).
 */
function check_achievements(int $userId): array
{
    $have = user_achievements($userId);
    $st = db()->prepare('SELECT xp, streak FROM users WHERE id = ?');
    $st->execute([$userId]);
    $u = $st->fetch();
    $done = completed_lessons($userId);
    $runs = get_stat($userId, 'runs');
    $charts = get_stat($userId, 'charts');

    $earned = [
        'primer_codigo' => $runs >= 1,
        'cien_ejecuciones' => $runs >= 100,
        'primera_leccion' => count($done) >= 1,
        'cinco_lecciones' => count($done) >= 5,
        'diez_lecciones' => count($done) >= 10,
        'primer_grafico' => $charts >= 1,
        'racha_3' => $u['streak'] >= 3,
        'racha_7' => $u['streak'] >= 7,
        'xp_1000' => $u['xp'] >= 1000,
        'ngplus_inicio' => (bool) array_intersect(ngplus_lessons(), $done),
        'platino' => !array_diff(lesson_order(), $done),
    ];
    foreach (course() as $m) {
        if (!empty($m['achievement'])) {
            $earned[$m['achievement']] = !array_diff($m['lessons'], $done);
        }
    }

    $all = achievements();
    $new = [];
    $ins = db()->prepare('INSERT IGNORE INTO user_achievements (user_id, code) VALUES (?, ?)');
    foreach ($earned as $code => $ok) {
        if ($ok && !isset($have[$code]) && isset($all[$code])) {
            $ins->execute([$userId, $code]);
            [$icon, $title, $desc] = $all[$code];
            $new[] = ['code' => $code, 'icon' => $icon, 'title' => $title, 'desc' => $desc];
        }
    }
    return $new;
}
