<?php
require __DIR__ . '/includes/bootstrap.php';

$errors = [];
$avatars = ['🐍', '🐼', '🦊', '🐱', '🦉', '🐸', '🐧', '🦄', '🤖', '👩‍💻', '👨‍💻', '🧙'];
$data = ['username' => '', 'email' => '', 'avatar' => '🐍'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['username'] = trim($_POST['username'] ?? '');
    $data['email'] = trim($_POST['email'] ?? '');
    $data['avatar'] = in_array($_POST['avatar'] ?? '', $avatars, true) ? $_POST['avatar'] : '🐍';
    $pass = $_POST['password'] ?? '';

    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors[] = 'La sesión expiró, intenta de nuevo.';
    }
    if (!preg_match('/^[\p{L}0-9_.-]{3,40}$/u', $data['username'])) {
        $errors[] = 'El nombre de usuario debe tener de 3 a 40 letras, números, guion o punto.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Escribe un correo válido.';
    }
    if (strlen($pass) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    if (!$errors) {
        $st = db()->prepare('SELECT 1 FROM users WHERE username = ? OR email = ?');
        $st->execute([$data['username'], $data['email']]);
        if ($st->fetch()) {
            $errors[] = 'Ese usuario o correo ya está registrado.';
        }
    }
    if (!$errors) {
        db()->prepare('INSERT INTO users (username, email, password_hash, avatar) VALUES (?, ?, ?, ?)')
            ->execute([$data['username'], $data['email'], password_hash($pass, PASSWORD_DEFAULT), $data['avatar']]);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) db()->lastInsertId();
        flash('¡Bienvenido/a a PyAprende, ' . $data['username'] . '! Empecemos por lo básico.', 'success');
        redirect('lesson.php?l=' . lesson_order()[0]);
    }
}

$pageTitle = 'Crear cuenta';
$mainClass = 'container narrow';
require __DIR__ . '/includes/header.php';
?>
<div class="card auth-card">
  <h1>Crea tu cuenta 🎉</h1>
  <p class="muted">Guarda tu progreso, gana XP y desbloquea logros.</p>
  <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post" class="form">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Elige tu avatar</label>
    <div class="avatar-picker">
      <?php foreach ($avatars as $a): ?>
        <label><input type="radio" name="avatar" value="<?= e($a) ?>" <?= $a === $data['avatar'] ? 'checked' : '' ?>><span><?= e($a) ?></span></label>
      <?php endforeach; ?>
    </div>
    <label for="username">Nombre de usuario</label>
    <input id="username" name="username" required minlength="3" maxlength="40" value="<?= e($data['username']) ?>">
    <label for="email">Correo</label>
    <input id="email" type="email" name="email" required value="<?= e($data['email']) ?>">
    <label for="password">Contraseña</label>
    <input id="password" type="password" name="password" required minlength="6">
    <button class="btn btn-primary btn-block">Crear cuenta</button>
  </form>
  <p class="muted center">¿Ya tienes cuenta? <a href="login.php">Entra aquí</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
