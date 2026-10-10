<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) { header('Location: dashboard.php'); exit; }

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_ok($_POST['csrf'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } elseif ($login === '' || $password === '') {
        $error = 'Enter your username or email and your password.';
    } else {
        $st = db()->prepare('SELECT id, password_hash FROM users WHERE username = ? OR email = ? LIMIT 1');
        $st->execute([$login, $login]);
        $row = $st->fetch();
        if ($row && password_verify($password, $row['password_hash'])) {
            login_user((int)$row['id']);
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Those details do not match an account.';
    }
}

$pageTitle = 'Log in';
$bodyClass = 'auth';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
    <form class="panel auth-card" method="post" novalidate>
        <h1>Welcome back</h1>
        <p class="muted">Log in to keep your streak going.</p>
        <?php if ($error): ?><div class="alert" role="alert"><?= e($error) ?></div><?php endif; ?>
        <?= csrf_field() ?>
        <label for="login">Username or email</label>
        <input id="login" name="login" type="text" autocomplete="username" value="<?= e($login) ?>" required>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button class="btn primary full" type="submit">Log in</button>
        <p class="muted center">New here? <a href="register.php">Create an account</a></p>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
