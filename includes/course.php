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
];

/** Dificultad de 1 a 9 estrellas según la posición de la lección en el curso. */
function lesson_difficulty(string $slug): int
{
    $order = lesson_order();
    $i = array_search($slug, $order, true);
    return 1 + (int) floor(($i ?: 0) * 8 / max(1, count($order) - 1));
}

/** Número de "nivel" estilo videojuego: módulo-lección (ej. 2-1). */
function stage_label(string $slug): string
{
    foreach (course() as $mi => $m) {
        $li = array_search($slug, $m['lessons'], true);
        if ($li !== false) {
            return ($mi + 1) . '-' . ($li + 1);
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
