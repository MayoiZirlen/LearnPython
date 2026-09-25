<?php
// Exporta todo el curso como JSON (lo usa tools/test_lessons.py).
define('ROOT', dirname(__DIR__));
require ROOT . '/includes/course.php';
$out = [];
foreach (lesson_order() as $slug) {
    $out[] = load_lesson($slug);
}
echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
