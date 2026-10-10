<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();

$cats = db()->query('SELECT c.id, c.slug, c.name, c.description,
        (SELECT COUNT(*) FROM questions q WHERE q.category_id = c.id) AS qn
        FROM categories c ORDER BY c.id')->fetchAll();

$pageTitle = 'Quizzes';
$active    = 'quizzes';
require __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <h1>Choose a quiz</h1>
    <p class="muted"><?= (int)cfg('questions_per_quiz') ?> questions, <?= (int)cfg('seconds_per_question') ?> seconds each. Hints cost XP.</p>
</section>

<section class="cards">
    <?php foreach ($cats as $c): ?>
        <a class="panel card-link" href="quiz.php?cat=<?= e($c['slug']) ?>">
            <h3><?= e($c['name']) ?></h3>
            <p class="muted"><?= e($c['description']) ?></p>
            <span class="tag"><?= (int)$c['qn'] ?> questions</span>
        </a>
    <?php endforeach; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
