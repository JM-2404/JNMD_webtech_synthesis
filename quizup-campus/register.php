<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) { header('Location: dashboard.php'); exit; }

$errors   = [];
$username = '';
$email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $pass     = $_POST['password'] ?? '';
    $pass2    = $_POST['password2'] ?? '';

    if (!csrf_ok($_POST['csrf'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    }
    if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
        $errors[] = 'Username must be 3-20 characters: letters, numbers or underscore.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($pass) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($pass !== $pass2) {
        $errors[] = 'The two passwords do not match.';
    }

    if (!$errors) {
        $st = db()->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
        $st->execute([$username, $email]);
        if ((int)$st->fetchColumn() > 0) {
            $errors[] = 'That username or email is already registered.';
        }
    }

    if (!$errors) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        db()->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)')
            ->execute([$username, $email, $hash]);
        login_user((int)db()->lastInsertId());
        header('Location: dashboard.php');
        exit;
    }
}

$pageTitle = 'Create account';
$bodyClass = 'auth';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
    <form class="panel auth-card" method="post" novalidate>
        <h1>Create your account</h1>
        <p class="muted">It takes under a minute.</p>
        <?php if ($errors): ?>
            <div class="alert" role="alert">
                <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?= csrf_field() ?>
        <label for="username">Username</label>
        <input id="username" name="username" type="text" autocomplete="username" value="<?= e($username) ?>" required>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" autocomplete="email" value="<?= e($email) ?>" required>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
        <label for="password2">Confirm password</label>
        <input id="password2" name="password2" type="password" autocomplete="new-password" required>
        <button class="btn primary full" type="submit">Create account</button>
        <p class="muted center">Already registered? <a href="login.php">Log in</a></p>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
