/* QuizUp Campus - flashcards */
(function () {
    'use strict';

    var app = document.getElementById('fcApp');
    if (!app) { return; }

    var uid = app.dataset.uid;
    var $ = function (id) { return document.getElementById(id); };
    var card = $('fcCard'), inner = $('fcInner'), front = $('fcFront'), back = $('fcBack');
    var deckList = $('deckList');

    var cat = '';
    var cards = [];         // array of {id, front, back}
    var known = {};         // object: card id -> true
    var pos = 0;
    var rewarded = false;

    function storeKey() { return 'qc-fc-' + uid + '-' + cat; }

    function loadKnown() {
        try { known = JSON.parse(localStorage.getItem(storeKey())) || {}; }
        catch (e) { known = {}; }
    }
    function saveKnown() {
        try { localStorage.setItem(storeKey(), JSON.stringify(known)); } catch (e) { /* ignore */ }
    }

    function knownCount() {
        return cards.filter(function (c) { return known[c.id]; }).length;
    }

    function paint() {
        if (cards.length === 0) {
            front.textContent = 'No cards in this deck yet.';
            back.textContent = '';
            $('fcCounter').textContent = 'Card 0 of 0';
            return;
        }
        var c = cards[pos];
        inner.classList.remove('flipped');
        front.textContent = c.front;
        back.textContent = c.back;
        card.classList.toggle('is-known', !!known[c.id]);

        var kc = knownCount();
        $('fcCounter').textContent = 'Card ' + (pos + 1) + ' of ' + cards.length;
        $('fcKnown').textContent = 'Known ' + kc + '/' + cards.length;
        $('fcProgress').style.width = Math.round((kc / cards.length) * 100) + '%';
    }

    function flip() { inner.classList.toggle('flipped'); }

    function go(step) {
        if (cards.length === 0) { return; }
        pos = (pos + step + cards.length) % cards.length;   // wraps around
        paint();
    }

    function mark(isKnown) {
        if (cards.length === 0) { return; }
        var c = cards[pos];
        if (isKnown) { known[c.id] = true; } else { delete known[c.id]; }
        saveKnown();
        paint();
        checkDeckDone();
        go(1);
    }

    function checkDeckDone() {
        if (!rewarded && cards.length > 0 && knownCount() === cards.length) {
            rewarded = true;
            QC.postJSON('api/flashcard_done.php', { known: knownCount() }).then(function (res) {
                var msg = $('fcDone');
                msg.hidden = false;
                msg.textContent = 'Deck complete! +' + res.xp + ' XP. Streak: ' + res.streak + ' day' + (res.streak === 1 ? '' : 's') + '.';
            }).catch(function () { /* silent */ });
        }
    }

    function shuffle() {
        // Fisher-Yates
        for (var i = cards.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var t = cards[i]; cards[i] = cards[j]; cards[j] = t;
        }
        pos = 0;
        paint();
    }

    function resetDeck() {
        known = {};
        saveKnown();
        rewarded = false;
        $('fcDone').hidden = true;
        pos = 0;
        paint();
    }

    function loadDeck(slug) {
        cat = slug;
        front.textContent = 'Loading...';
        back.textContent = '';
        $('fcDone').hidden = true;
        QC.getJSON('api/flashcards.php?cat=' + encodeURIComponent(slug)).then(function (data) {
            cards = data.cards;
            pos = 0;
            rewarded = false;
            loadKnown();
            paint();
        }).catch(function (err) {
            front.textContent = err.message;
        });
        Array.prototype.forEach.call(deckList.querySelectorAll('.deck'), function (b) {
            b.classList.toggle('on', b.dataset.cat === slug);
        });
    }

    // Events
    card.addEventListener('click', flip);
    $('fcNext').addEventListener('click', function () { go(1); });
    $('fcPrev').addEventListener('click', function () { go(-1); });
    $('fcGot').addEventListener('click', function () { mark(true); });
    $('fcAgain').addEventListener('click', function () { mark(false); });
    $('fcShuffle').addEventListener('click', shuffle);
    $('fcReset').addEventListener('click', resetDeck);
    deckList.addEventListener('click', function (ev) {
        var b = ev.target.closest('.deck');
        if (b) { loadDeck(b.dataset.cat); }
    });
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'ArrowRight') { go(1); }
        else if (ev.key === 'ArrowLeft') { go(-1); }
        else if (ev.key === ' ' && document.activeElement !== card) { ev.preventDefault(); flip(); }
    });

    var first = deckList.querySelector('.deck.on') || deckList.querySelector('.deck');
    if (first) { loadDeck(first.dataset.cat); }
})();
