<?php
// Renderiza una página en modo estático (sin sesión ni base de datos) y la imprime.
// Uso: php tools/render_page.php <pagina> [slug]      (lo llama tools/build_static.php)
define('STATIC_BUILD', true);
$pagina = $argv[1] ?? 'index';
if (!preg_match('/^[a-z_]+$/', $pagina)) {
    fwrite(STDERR, "Página inválida\n");
    exit(1);
}
$_GET = isset($argv[2]) ? ['l' => $argv[2]] : [];
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = "/$pagina.php";
$_SERVER['QUERY_STRING'] = http_build_query($_GET);
chdir(dirname(__DIR__));
require dirname(__DIR__) . "/$pagina.php";
