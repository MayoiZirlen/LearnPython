<?php
/**
 * Genera la versión estática del sitio para GitHub Pages en la carpeta dist/.
 *
 *   php tools/build_static.php [carpeta_destino]
 *
 * - Cada página PHP se renderiza en modo estático (sin sesión ni MySQL).
 * - Cada lección queda como lesson-<slug>.html y los enlaces .php se cambian a .html.
 * - El progreso, la XP y los logros se guardan en el navegador (assets/js/local.js),
 *   usando el catálogo del curso que se genera aquí (assets/js/catalogo.js).
 */
define('STATIC_BUILD', true);
define('ROOT', dirname(__DIR__));
require ROOT . '/includes/course.php';
require ROOT . '/includes/gamification.php';

$dist = rtrim($argv[1] ?? ROOT . '/dist', '/');
$php = PHP_BINARY;

function borrar_dir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $f) {
        $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($dir);
}

function copiar_dir(string $origen, string $destino, array $excluir = []): void
{
    @mkdir($destino, 0777, true);
    foreach (scandir($origen) as $f) {
        if ($f === '.' || $f === '..' || $f === '.htaccess' || $f === '__pycache__' || in_array($f, $excluir, true)) {
            continue;
        }
        $o = "$origen/$f";
        is_dir($o) ? copiar_dir($o, "$destino/$f", $excluir) : copy($o, "$destino/$f");
    }
}

/** Cambia los enlaces a páginas PHP por sus equivalentes .html. */
function reescribir_enlaces(string $html): string
{
    $html = preg_replace('/lesson\.php\?l=([a-z0-9-]+)/', 'lesson-$1.html', $html);
    $html = preg_replace('/\blogout\.php\b/', 'index.html', $html);
    return preg_replace('/\b(index|learn|playground|ranking|profile|ajustes|login|register)\.php(\?[^"\'\s<]*)?/', '$1.html', $html);
}

borrar_dir($dist);
mkdir($dist, 0777, true);

// 1) Páginas
$paginas = ['index' => null, 'learn' => null, 'playground' => null, 'ranking' => null,
            'profile' => null, 'ajustes' => null, 'register' => null];
$trabajos = [];
foreach ($paginas as $p => $_) {
    $trabajos["$p.html"] = [$p, null];
}
foreach (lesson_order() as $slug) {
    $trabajos["lesson-$slug.html"] = ['lesson', $slug];
}
$errores = 0;
foreach ($trabajos as $archivo => [$pagina, $slug]) {
    $cmd = escapeshellarg($php) . ' -d display_errors=1 -d error_reporting=-1 ' . escapeshellarg(ROOT . '/tools/render_page.php')
         . ' ' . escapeshellarg($pagina) . ($slug ? ' ' . escapeshellarg($slug) : '');
    $html = shell_exec($cmd) ?? '';
    if ($html === '' || preg_match('/(Warning|Notice|Deprecated|Fatal error|Parse error)(<\/b>)?:/', $html)) {
        fwrite(STDERR, "❌ $archivo: la página tiene errores o salió vacía\n");
        $errores++;
    }
    file_put_contents("$dist/$archivo", reescribir_enlaces($html));
}
copy("$dist/register.html", "$dist/login.html");
copy("$dist/index.html", "$dist/404.html");

// 2) Archivos estáticos
copiar_dir(ROOT . '/assets', "$dist/assets", ['pyodide']);
copiar_dir(ROOT . '/data', "$dist/data");
touch("$dist/.nojekyll");   // que GitHub Pages sirva los archivos tal cual

// 3) Catálogo del curso para el progreso local
$lecciones = [];
foreach (course() as $m) {
    foreach ($m['lessons'] as $slug) {
        $l = load_lesson($slug);
        $mult = xp_multiplier($slug);
        $pasos = [];
        foreach (graded_steps($l) as $i) {
            $pasos[$i] = step_xp($l['steps'][$i]) * $mult;
        }
        $lecciones[$slug] = [
            'titulo' => $l['title'], 'resumen' => $l['summary'], 'icono' => $l['icon'] ?? '📘',
            'modulo' => $m['slug'], 'modo' => module_mode($m), 'tier' => $m['tier'],
            'pasos' => (object) $pasos, 'bono' => 50 * $mult, 'etapa' => stage_label($slug),
        ];
    }
}
$modulos = array_map(fn($m) => [
    'slug' => $m['slug'], 'titulo' => $m['title'], 'icono' => $m['icon'], 'color' => $m['color'],
    'modo' => module_mode($m), 'tier' => $m['tier'], 'logro' => $m['achievement'] ?? null, 'lecciones' => $m['lessons'],
], course());
$catalogo = [
    'orden' => lesson_order(),
    'lecciones' => $lecciones,
    'modulos' => $modulos,
    'niveles' => LEVELS,
    'logros' => achievements(),
    'rangos' => RANK_LABELS,
];
file_put_contents("$dist/assets/js/catalogo.js",
    "/* Generado por tools/build_static.php: estructura del curso para el progreso local. */\nwindow.CATALOGO = "
    . json_encode($catalogo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ";\n");

$n = count($trabajos);
echo $errores ? "❌ $errores páginas con errores\n" : "✅ Sitio estático generado en $dist ($n páginas)\n";
exit($errores ? 1 : 0);
