<?php
// API JSON para registrar progreso: pasos resueltos, lecciones terminadas y estadísticas.
require dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function respond(array $data, int $code = 200): never
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}
if (!csrf_check($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    respond(['error' => 'Token inválido'], 403);
}
$user = current_user();
if (!$user) {
    respond(['error' => 'Inicia sesión para guardar tu progreso'], 401);
}

$uid = (int) $user['id'];
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $in['action'] ?? '';
$gained = 0;
$extra = [];

switch ($action) {
    case 'step':
        $lesson = load_lesson((string) ($in['lesson'] ?? ''));
        $idx = (int) ($in['step'] ?? -1);
        if (!$lesson || !isset($lesson['steps'][$idx])) {
            respond(['error' => 'Paso inválido'], 400);
        }
        $xp = step_xp($lesson['steps'][$idx]);
        // Si vio la solución, gana menos XP (pero igual avanza).
        if (!empty($in['used_solution'])) {
            $xp = intdiv($xp, 5);
        }
        $st = db()->prepare('INSERT IGNORE INTO step_progress (user_id, lesson_slug, step_index, xp_earned) VALUES (?, ?, ?, ?)');
        $st->execute([$uid, $lesson['slug'], $idx, $xp]);
        if ($st->rowCount() > 0) {
            $gained = $xp;
        }
        break;

    case 'lesson':
        $lesson = load_lesson((string) ($in['lesson'] ?? ''));
        if (!$lesson) {
            respond(['error' => 'Lección inválida'], 400);
        }
        $missing = array_diff(graded_steps($lesson), completed_steps($uid, $lesson['slug']));
        if ($missing) {
            respond(['error' => 'Aún faltan pasos por resolver', 'missing' => array_values($missing)], 409);
        }
        $st = db()->prepare('INSERT IGNORE INTO lesson_progress (user_id, lesson_slug) VALUES (?, ?)');
        $st->execute([$uid, $lesson['slug']]);
        if ($st->rowCount() > 0) {
            $gained = 50;
        }
        $extra['next'] = next_lesson($lesson['slug']);
        break;

    case 'run':
        bump_stat($uid, 'runs');
        if (!empty($in['chart'])) {
            bump_stat($uid, 'charts');
        }
        break;

    default:
        respond(['error' => 'Acción desconocida'], 400);
}

add_xp($uid, $gained);
touch_streak($uid);
$new = check_achievements($uid);

$st = db()->prepare('SELECT xp, streak FROM users WHERE id = ?');
$st->execute([$uid]);
$u = $st->fetch();
$before = level_info((int) $u['xp'] - $gained);
$after = level_info((int) $u['xp']);

respond($extra + [
    'ok' => true,
    'xp_gained' => $gained,
    'xp' => (int) $u['xp'],
    'streak' => (int) $u['streak'],
    'level' => $after,
    'level_up' => $after['number'] > $before['number'],
    'achievements' => $new,
]);
