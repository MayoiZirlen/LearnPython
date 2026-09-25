<?php
// Punto de arranque común: sesión, configuración, base de datos y utilidades.
declare(strict_types=1);

session_start();

define('ROOT', dirname(__DIR__));

$configFile = ROOT . '/config/config.php';
if (!is_file($configFile)) {
    $configFile = ROOT . '/config/config.example.php';
}
$CONFIG = require $configFile;

require_once ROOT . '/includes/course.php';
require_once ROOT . '/includes/gamification.php';

function config(string $key)
{
    global $CONFIG;
    return $CONFIG[$key] ?? null;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            config('db_host'), (int) config('db_port'), config('db_name')
        );
        try {
            $pdo = new PDO($dsn, config('db_user'), config('db_pass'), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $ex) {
            http_response_code(500);
            $msg = e($ex->getMessage());
            exit("<h1>No se pudo conectar a MySQL</h1><p>¿Iniciaste MySQL en el panel de XAMPP y "
                . "importaste <code>database/schema.sql</code>?</p><pre>$msg</pre>");
        }
    }
    return $pdo;
}

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): bool
{
    return is_string($token) && hash_equals(csrf_token(), $token);
}

function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $st = db()->prepare('SELECT * FROM users WHERE id = ?');
            $st->execute([$_SESSION['user_id']]);
            $user = $st->fetch() ?: null;
        }
    }
    return $user;
}

function require_login(): array
{
    $u = current_user();
    if (!$u) {
        $here = basename($_SERVER['SCRIPT_NAME'] ?? 'learn.php');
        if (!empty($_SERVER['QUERY_STRING'])) {
            $here .= '?' . $_SERVER['QUERY_STRING'];
        }
        redirect('login.php?next=' . urlencode($here));
    }
    return $u;
}

function flash(?string $msg = null, string $type = 'info'): ?array
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/** Ruta de un archivo estático con su versión (fecha de modificación) para evitar la caché vieja. */
function asset(string $path): string
{
    $file = ROOT . '/' . $path;
    return $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}

/** Versión conjunta del motor de Python (worker + arnés): cambia cuando cualquiera cambia. */
function python_version(): int
{
    return max(array_map('filemtime', [
        ROOT . '/assets/js/py-worker.js',
        ROOT . '/assets/py/harness.py',
    ]));
}
