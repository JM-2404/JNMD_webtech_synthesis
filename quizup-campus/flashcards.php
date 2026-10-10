<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();

$cats = db()->query('SELECT c.id, c.slug, c.name,
        (SELECT COUNT(*) FROM flashcards f WHERE f.category_id = c.id) AS fn
        FROM categories c ORDER BY c.id')->fetchAll();
$selected = $_GET['cat'] ?? ($cats[0]['slug'] ?? '');

$pageTitle   = 'Flashcards';
$active      = 'flashcards';
$pageScripts = ['flashcards.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <h1>Flashcards</h1>
    <p class="muted">Flip the card, then mark whether you knew it. Finish a deck to earn XP.</p>
</section>

<section class="fc-layout" id="fcApp" data-uid="<?= (int)$user['id'] ?>">
    <aside class="panel fc-side">
        <h3>Decks</h3>
        <div class="deck-list" id="deckList">
            <?php foreach ($cats as $c): ?>
                <button type="button" class="deck <?= $c['slug'] === $selected ? 'on' : '' ?>" data-cat="<?= e($c['slug']) ?>">
                    <span><?= e($c['name']) ?></span><small><?= (int)$c['fn'] ?> cards</small>
                </button>
            <?php endforeach; ?>
        </div>
    </aside>

    <div class="fc-main">
        <div class="fc-meta">
            <span id="fcCounter">Card 0 of 0</span>
            <span id="fcKnown">Known 0</span>
            <button class="btn ghost small" id="fcShuffle" type="button">Shuffle</button>
            <button class="btn ghost small" id="fcReset" type="button">Reset deck</button>
        </div>
        <div class="bar thin"><span id="fcProgress" style="width:0%"></span></div>

        <button class="flip" id="fcCard" type="button" aria-label="Flip card">
            <span class="flip-inner" id="fcInner">
                <span class="face front" id="fcFront">Loading...</span>
                <span class="face back"  id="fcBack"></span>
            </span>
        </button>
        <p class="muted center small-note">Click the card or press Space to flip. Arrow keys move between cards.</p>

        <div class="fc-actions">
            <button class="btn ghost" id="fcPrev" type="button">Previous</button>
            <button class="btn ghost" id="fcAgain" type="button">Still learning</button>
            <button class="btn primary" id="fcGot" type="button">I knew it</button>
            <button class="btn ghost" id="fcNext" type="button">Next</button>
        </div>
        <p id="fcDone" class="alert ok" hidden></p>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
