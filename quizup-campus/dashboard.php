<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$pdo  = db();

$xp        = (int)$user['xp'];
$level     = level_from_xp($xp);
$into      = xp_into_level($xp);
$perLevel  = (int)cfg('xp_per_level');
$pct       = (int)round(($into / $perLevel) * 100);

// Overall stats (calculations)
$st = $pdo->prepare('SELECT COUNT(*) AS n, COALESCE(SUM(score),0) AS s, COALESCE(SUM(total),0) AS t FROM quiz_attempts WHERE user_id = ?');
$st->execute([$user['id']]);
$stats    = $st->fetch();
$accuracy = ((int)$stats['t'] > 0) ? (int)round(($stats['s'] / $stats['t']) * 100) : 0;

// Recent attempts
$st = $pdo->prepare('SELECT a.score, a.total, a.xp_earned, a.created_at, c.name
                     FROM quiz_attempts a JOIN categories c ON c.id = a.category_id
                     WHERE a.user_id = ? ORDER BY a.created_at DESC LIMIT 5');
$st->execute([$user['id']]);
$recent = $st->fetchAll();

$cats = $pdo->query('SELECT id, slug, name, description FROM categories ORDER BY id')->fetchAll();
$top  = $pdo->query('SELECT username, xp, streak FROM users ORDER BY xp DESC, id ASC LIMIT 5')->fetchAll();

$studiedToday = ($user['last_active_date'] === date('Y-m-d'));

$pageTitle = 'Dashboard';
$active    = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<section class="dash-head">
    <div>
        <h1>Hi, <?= e($user['username']) ?></h1>
        <p class="muted"><?= $studiedToday ? 'You have studied today. Your streak is safe.' : 'Do one quiz today to keep your streak alive.' ?></p>
    </div>
    <a class="btn primary big" href="quizzes.php">Start a quiz</a>
</section>

<section class="grid-top">
    <article class="panel streak-panel">
        <div class="streak-num"><?= (int)$user['streak'] ?></div>
        <div>
            <strong>day streak</strong>
            <p class="muted">Best: <?= (int)$user['best_streak'] ?> days</p>
        </div>
    </article>

    <article class="panel level-panel">
        <div class="level-head">
            <strong>Level <?= $level ?></strong>
            <span class="muted"><?= $into ?> / <?= $perLevel ?> XP</span>
        </div>
        <div class="bar" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
            <span style="width: <?= $pct ?>%"></span>
        </div>
        <p class="muted"><?= $perLevel - $into ?> XP to Level <?= $level + 1 ?>. Total: <?= $xp ?> XP.</p>
    </article>

    <article class="panel stat">
        <span class="stat-num"><?= (int)$stats['n'] ?></span>
        <span class="muted">quizzes taken</span>
    </article>
    <article class="panel stat">
        <span class="stat-num"><?= $accuracy ?>%</span>
        <span class="muted">overall accuracy</span>
    </article>
</section>

<section class="grid-2">
    <article class="panel">
        <div class="panel-head"><h2>Pick a category</h2><a href="quizzes.php">See all</a></div>
        <div class="cat-list">
            <?php foreach ($cats as $c): ?>
                <a class="cat-row" href="quiz.php?cat=<?= e($c['slug']) ?>">
                    <span class="cat-dot"></span>
                    <span><strong><?= e($c['name']) ?></strong><small><?= e($c['description']) ?></small></span>
                </a>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel">
        <div class="panel-head"><h2>Recent results</h2></div>
        <?php if (!$recent): ?>
            <p class="empty">No quizzes yet. Start one and your results will show here.</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Category</th><th>Score</th><th>XP</th></tr></thead>
                <tbody>
                <?php foreach ($recent as $r): ?>
                    <tr>
                        <td><?= e($r['name']) ?></td>
                        <td><?= (int)$r['score'] ?>/<?= (int)$r['total'] ?></td>
                        <td>+<?= (int)$r['xp_earned'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="panel-head spaced"><h2>Top players</h2><a href="leaderboard.php">Leaderboard</a></div>
        <ol class="mini-board">
            <?php foreach ($top as $t): ?>
                <li><span><?= e($t['username']) ?></span><span><?= (int)$t['xp'] ?> XP</span></li>
            <?php endforeach; ?>
        </ol>
    </article>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
