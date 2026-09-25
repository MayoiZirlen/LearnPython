<?php
// Carga del contenido del curso (módulos y lecciones viven en /content).

function course(): array
{
    static $modules = null;
    if ($modules === null) {
        $modules = require ROOT . '/content/course.php';
    }
    return $modules;
}

/** Lista plana de slugs de lecciones en orden. */
function lesson_order(): array
{
    $order = [];
    foreach (course() as $m) {
        foreach ($m['lessons'] as $slug) {
            $order[] = $slug;
        }
    }
    return $order;
}

function lesson_exists(string $slug): bool
{
    return preg_match('/^[a-z0-9-]+$/', $slug) === 1 && in_array($slug, lesson_order(), true);
}

function load_lesson(string $slug): ?array
{
    static $cache = [];
    if (!lesson_exists($slug)) {
        return null;
    }
    if (!isset($cache[$slug])) {
        $lesson = require ROOT . '/content/lessons/' . $slug . '.php';
        $lesson['slug'] = $slug;
        $cache[$slug] = $lesson;
    }
    return $cache[$slug];
}

function module_of(string $slug): ?array
{
    foreach (course() as $m) {
        if (in_array($slug, $m['lessons'], true)) {
            return $m;
        }
    }
    return null;
}

function next_lesson(string $slug): ?string
{
    $order = lesson_order();
    $i = array_search($slug, $order, true);
    return ($i !== false && isset($order[$i + 1])) ? $order[$i + 1] : null;
}

/** XP que otorga un paso según su tipo. */
function step_xp(array $step): int
{
    return match ($step['type']) {
        'quiz' => 10,
        'exercise' => 25,
        default => 0,
    };
}

/** Índices de pasos que se califican (quiz y ejercicios). */
function graded_steps(array $lesson): array
{
    $idx = [];
    foreach ($lesson['steps'] as $i => $s) {
        if (step_xp($s) > 0) {
            $idx[] = $i;
        }
    }
    return $idx;
}

function completed_lessons(int $userId): array
{
    $st = db()->prepare('SELECT lesson_slug FROM lesson_progress WHERE user_id = ?');
    $st->execute([$userId]);
    return array_column($st->fetchAll(), 'lesson_slug');
}

function completed_steps(int $userId, string $slug): array
{
    $st = db()->prepare('SELECT step_index FROM step_progress WHERE user_id = ? AND lesson_slug = ?');
    $st->execute([$userId, $slug]);
    return array_map('intval', array_column($st->fetchAll(), 'step_index'));
}

/** Primera lección sin completar (la "siguiente" recomendada). */
function recommended_lesson(array $done): string
{
    foreach (lesson_order() as $slug) {
        if (!in_array($slug, $done, true)) {
            return $slug;
        }
    }
    return lesson_order()[0];
}

const TIERS = [
    'facil' => 'Fácil',
    'normal' => 'Normal',
    'dificil' => 'Difícil',
    'experto' => 'Experto',
    'ngplus' => 'NG+',
    'infierno' => 'Infierno',
];

/** Modo de juego de un módulo: 'main' (juego principal), 'ngplus' o 'infierno'. */
function module_mode(?array $m): string
{
    return $m['modo'] ?? (!empty($m['ngplus']) ? 'ngplus' : 'main');
}

function lesson_mode(string $slug): string
{
    return module_mode(module_of($slug));
}

function is_ngplus(string $slug): bool
{
    return lesson_mode($slug) === 'ngplus';
}

function is_infierno(string $slug): bool
{
    return lesson_mode($slug) === 'infierno';
}

function lessons_of_mode(string $mode): array
{
    return array_values(array_filter(lesson_order(), fn($s) => lesson_mode($s) === $mode));
}

/** Lecciones del juego principal (sin NG+ ni Infierno). */
function main_lessons(): array
{
    return lessons_of_mode('main');
}

function ngplus_lessons(): array
{
    return lessons_of_mode('ngplus');
}

function infierno_lessons(): array
{
    return lessons_of_mode('infierno');
}

/** El New Game Plus se "desbloquea" al terminar todo el juego principal. */
function ngplus_unlocked(array $done): bool
{
    return !array_diff(main_lessons(), $done);
}

/** El Infierno se desbloquea al terminar el juego principal y el NG+. */
function infierno_unlocked(array $done): bool
{
    return ngplus_unlocked($done) && !array_diff(ngplus_lessons(), $done);
}

/** En el Infierno todo da el doble de XP. */
function xp_multiplier(string $slug): int
{
    return is_infierno($slug) ? 2 : 1;
}

/** XP total que puede dar una lección (pasos + bono por terminarla). */
function lesson_total_xp(array $lesson): int
{
    return (array_sum(array_map('step_xp', $lesson['steps'])) + 50) * xp_multiplier($lesson['slug']);
}

const MODE_PREFIX = ['main' => '', 'ngplus' => 'NG+ ', 'infierno' => '🔥 '];

/** Dificultad de 1 a 9 estrellas según la posición de la lección en el curso. */
function lesson_difficulty(string $slug): int
{
    // Cada modo (principal, NG+, Infierno) tiene su propia escala de 1 a 9 estrellas.
    $order = lessons_of_mode(lesson_mode($slug));
    $i = array_search($slug, $order, true);
    return 1 + (int) floor(($i ?: 0) * 8 / max(1, count($order) - 1));
}

/** Número de "nivel" estilo videojuego: módulo-lección (ej. 2-1). */
function stage_label(string $slug): string
{
    $cuenta = [];
    foreach (course() as $m) {
        $modo = module_mode($m);
        $n = $cuenta[$modo] = ($cuenta[$modo] ?? 0) + 1;
        $li = array_search($slug, $m['lessons'], true);
        if ($li !== false) {
            return MODE_PREFIX[$modo] . $n . '-' . ($li + 1);
        }
    }
    return '?';
}

/** Número de módulo para mostrar (cada modo reinicia la cuenta). */
function module_number(array $module): string
{
    $cuenta = [];
    foreach (course() as $m) {
        $modo = module_mode($m);
        $n = $cuenta[$modo] = ($cuenta[$modo] ?? 0) + 1;
        if ($m['slug'] === $module['slug']) {
            return MODE_PREFIX[$modo] . $n;
        }
    }
    return '?';
}

/**
 * Rango obtenido en cada lección:
 * PERFECT (sin ver soluciones), GREAT (1 solución vista), CLEARED (más) o EN CURSO.
 */
function lesson_ranks(int $userId): array
{
    $st = db()->prepare('SELECT lesson_slug, SUM(xp_earned = 5) AS vistas FROM step_progress WHERE user_id = ? GROUP BY lesson_slug');
    $st->execute([$userId]);
    $solutions = array_column($st->fetchAll(), 'vistas', 'lesson_slug');
    $ranks = [];
    foreach ($solutions as $slug => $n) {
        $ranks[$slug] = 'progress';
    }
    foreach (completed_lessons($userId) as $slug) {
        $n = (int) ($solutions[$slug] ?? 0);
        $ranks[$slug] = $n === 0 ? 'perfect' : ($n === 1 ? 'great' : 'cleared');
    }
    return $ranks;
}

const RANK_LABELS = [
    'perfect' => 'Perfect',
    'great' => 'Great',
    'cleared' => 'Cleared',
    'progress' => 'En curso',
];
