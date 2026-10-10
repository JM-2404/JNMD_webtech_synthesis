<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) { header('Location: dashboard.php'); exit; }
$pageTitle = 'Revise like it is a game';
$bodyClass = 'landing';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-copy">
        <h1>Keep the streak.<br>Pass the exam.</h1>
        <p class="lead">Short quizzes and flashcards for computing, web development and general knowledge. Earn XP, climb the leaderboard and talk it through with other students.</p>
        <div class="row">
            <a class="btn primary big" href="register.php">Create free account</a>
            <a class="btn ghost big" href="login.php">I already have one</a>
        </div>
    </div>
    <div class="hero-card" aria-hidden="true">
        <div class="demo-q">Which HTML tag holds the main content of a page?</div>
        <div class="demo-opt">&lt;header&gt;</div>
        <div class="demo-opt good">&lt;main&gt;</div>
        <div class="demo-opt">&lt;aside&gt;</div>
        <div class="demo-foot"><span>+10 XP</span><span>12s left</span></div>
    </div>
</section>

<section class="features">
    <article class="panel"><h3>Quizzes with a clock</h3><p>Timed questions, hints, and a full result summary you can review.</p></article>
    <article class="panel"><h3>Flashcards by category</h3><p>Flip, shuffle and mark what you know so you only revise what you do not.</p></article>
    <article class="panel"><h3>Streaks and XP</h3><p>Study a little every day. Your streak grows and so does your level.</p></article>
    <article class="panel"><h3>Campus chat</h3><p>See who is online and swap tips in the shared chat room.</p></article>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
