<?php
require __DIR__ . '/includes/bootstrap.php';

$error = null;
$next = $_GET['next'] ?? $_POST['next'] ?? 'learn.php';
// Solo permitimos redirecciones a páginas de este mismo sitio.
if (!is_string($next) || !preg_match('#^[a-z_]+\.php(\?[\w=&%.-]*)?$#i', $next)) {
    $next = 'learn.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $st = db()->prepare('SELECT id, password_hash FROM users WHERE username = ? OR email = ?');
    $st->execute([$login, $login]);
    $u = $st->fetch();
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'La sesión expiró, intenta de nuevo.';
    } elseif ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $u['id'];
        redirect($next);
    } else {
        $error = 'Usuario o contraseña incorrectos.';
    }
}

$pageTitle = 'Entrar';
$mainClass = 'container narrow';
require __DIR__ . '/includes/header.php';
?>
<div class="card auth-card">
  <h1>¡Hola de nuevo! 👋</h1>
  <p class="muted">Tu racha te está esperando.</p>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <label for="login">Usuario o correo</label>
    <input id="login" name="login" required autofocus>
    <label for="password">Contraseña</label>
    <input id="password" type="password" name="password" required>
    <button class="btn btn-primary btn-block">Entrar</button>
  </form>
  <p class="muted center">¿Nuevo por aquí? <a href="register.php">Crea una cuenta gratis</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
