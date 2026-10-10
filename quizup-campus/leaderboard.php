<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();

$rows = db()->query('SELECT id, username, xp, streak FROM users ORDER BY xp DESC, id ASC LIMIT 20')->fetchAll();

$pageTitle = 'Leaderboard';
$active    = 'leaderboard';
require __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <h1>Leaderboard</h1>
    <p class="muted">Top 20 by total XP.</p>
</section>

<section class="panel">
    <table class="table">
        <thead><tr><th>Rank</th><th>Player</th><th>Level</th><th>Streak</th><th>XP</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $i => $r): ?>
            <tr class="<?= (int)$r['id'] === (int)$user['id'] ? 'me' : '' ?>">
                <td><?= $i + 1 ?></td>
                <td><?= e($r['username']) ?><?= (int)$r['id'] === (int)$user['id'] ? ' (you)' : '' ?></td>
                <td><?= level_from_xp((int)$r['xp']) ?></td>
                <td><?= (int)$r['streak'] ?></td>
                <td><?= (int)$r['xp'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
