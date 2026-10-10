<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$pdo  = db();

$msg = '';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cur  = $_POST['current'] ?? '';
    $new  = $_POST['new'] ?? '';
    $new2 = $_POST['new2'] ?? '';
    if (!csrf_ok($_POST['csrf'] ?? '')) {
        $err = 'Your session expired. Please try again.';
    } elseif (!password_verify($cur, $user['password_hash'])) {
        $err = 'Your current password is not correct.';
    } elseif (strlen($new) < 8) {
        $err = 'The new password must be at least 8 characters.';
    } elseif ($new !== $new2) {
        $err = 'The new passwords do not match.';
    } else {
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        $msg = 'Password updated.';
    }
}

// Per-category best scores
$st = $pdo->prepare('SELECT c.name, COUNT(*) AS plays, MAX(ROUND(a.score / a.total * 100)) AS best
                     FROM quiz_attempts a JOIN categories c ON c.id = a.category_id
                     WHERE a.user_id = ? GROUP BY c.id, c.name ORDER BY c.id');
$st->execute([$user['id']]);
$byCat = $st->fetchAll();

$xp = (int)$user['xp'];

$pageTitle = 'Profile';
$active    = 'profile';
require __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <h1><?= e($user['username']) ?></h1>
    <p class="muted"><?= e($user['email']) ?> &middot; joined <?= e(date('j M Y', strtotime($user['created_at']))) ?></p>
</section>

<section class="grid-top three">
    <article class="panel stat"><span class="stat-num"><?= level_from_xp($xp) ?></span><span class="muted">level</span></article>
    <article class="panel stat"><span class="stat-num"><?= $xp ?></span><span class="muted">total XP</span></article>
    <article class="panel stat"><span class="stat-num"><?= (int)$user['best_streak'] ?></span><span class="muted">best streak (days)</span></article>
</section>

<section class="grid-2">
    <article class="panel">
        <h2>Best score by category</h2>
        <?php if (!$byCat): ?>
            <p class="empty">Take a quiz to see your best scores here.</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Category</th><th>Plays</th><th>Best</th></tr></thead>
                <tbody>
                <?php foreach ($byCat as $b): ?>
                    <tr><td><?= e($b['name']) ?></td><td><?= (int)$b['plays'] ?></td><td><?= (int)$b['best'] ?>%</td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </article>

    <form class="panel" method="post" novalidate>
        <h2>Change password</h2>
        <?php if ($err): ?><div class="alert" role="alert"><?= e($err) ?></div><?php endif; ?>
        <?php if ($msg): ?><div class="alert ok" role="status"><?= e($msg) ?></div><?php endif; ?>
        <?= csrf_field() ?>
        <label for="current">Current password</label>
        <input id="current" name="current" type="password" autocomplete="current-password" required>
        <label for="new">New password</label>
        <input id="new" name="new" type="password" autocomplete="new-password" minlength="8" required>
        <label for="new2">Confirm new password</label>
        <input id="new2" name="new2" type="password" autocomplete="new-password" required>
        <button class="btn primary" type="submit">Save password</button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
