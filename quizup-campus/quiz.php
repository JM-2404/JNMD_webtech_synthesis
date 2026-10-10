<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();

$slug = $_GET['cat'] ?? '';
$st = db()->prepare('SELECT id, slug, name FROM categories WHERE slug = ?');
$st->execute([$slug]);
$cat = $st->fetch();
if (!$cat) { header('Location: quizzes.php'); exit; }

$pageTitle   = $cat['name'] . ' quiz';
$active      = 'quizzes';
$pageScripts = ['quiz.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="quiz-shell"
         id="quizApp"
         data-cat="<?= e($cat['slug']) ?>"
         data-count="<?= (int)cfg('questions_per_quiz') ?>"
         data-seconds="<?= (int)cfg('seconds_per_question') ?>"
         data-penalty="<?= (int)cfg('xp_hint_penalty') ?>">

    <!-- Start screen -->
    <div class="panel quiz-card" id="screenStart">
        <h1><?= e($cat['name']) ?></h1>
        <ul class="rules">
            <li><?= (int)cfg('questions_per_quiz') ?> multiple choice questions</li>
            <li><?= (int)cfg('seconds_per_question') ?> seconds per question</li>
            <li>+<?= (int)cfg('xp_per_correct') ?> XP per correct answer, each hint costs <?= (int)cfg('xp_hint_penalty') ?> XP</li>
            <li>Keys 1 to 4 pick an answer, Enter moves on</li>
        </ul>
        <button class="btn primary big" id="btnStart" type="button">Start quiz</button>
        <p class="muted" id="loadMsg" hidden>Loading questions...</p>
    </div>

    <!-- Question screen -->
    <div class="panel quiz-card" id="screenQuiz" hidden>
        <div class="quiz-top">
            <span id="qCounter">Question 1 of 10</span>
            <span class="timer" id="timerBox"><span id="timerNum">20</span>s</span>
            <span id="liveScore">Score 0</span>
        </div>
        <div class="bar thin"><span id="progressFill" style="width:0%"></span></div>
        <div class="timer-bar"><span id="timerFill"></span></div>

        <h2 id="qText" class="q-text"></h2>
        <div id="options" class="options" role="group" aria-label="Answer choices"></div>

        <p id="hintBox" class="hint" hidden></p>
        <p id="feedback" class="feedback" aria-live="polite"></p>

        <div class="quiz-actions">
            <div class="row">
                <button class="btn ghost small" id="btnHint" type="button">Show hint</button>
                <button class="btn ghost small" id="btnFifty" type="button">50 / 50</button>
            </div>
            <button class="btn primary" id="btnNext" type="button" disabled>Next</button>
        </div>
    </div>

    <!-- Result screen -->
    <div class="panel quiz-card" id="screenResult" hidden>
        <h1 id="resTitle">Quiz complete</h1>
        <div class="result-grid">
            <div class="result-big"><span id="resScore">0/0</span><small>score</small></div>
            <div class="result-big"><span id="resPct">0%</span><small>accuracy</small></div>
            <div class="result-big"><span id="resXp">+0</span><small>XP earned</small></div>
            <div class="result-big"><span id="resAvg">0s</span><small>avg per question</small></div>
        </div>
        <p id="resNote" class="muted"></p>
        <div class="row">
            <button class="btn primary" id="btnRestart" type="button">Restart</button>
            <a class="btn ghost" href="quizzes.php">Other categories</a>
            <a class="btn ghost" href="dashboard.php">Dashboard</a>
        </div>
        <h3 class="spaced">Review</h3>
        <ol class="review" id="reviewList"></ol>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
