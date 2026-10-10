/*
 * QuizUp Campus - quiz engine
 * Demonstrates: conditions, functions, arrays, DOM manipulation,
 * event handling and calculations.
 */
(function () {
    'use strict';

    var app = document.getElementById('quizApp');
    if (!app) { return; }

    // ---- Settings read from data-* attributes (set by PHP) ----
    var CATEGORY = app.dataset.cat;
    var COUNT = parseInt(app.dataset.count, 10);
    var SECONDS = parseInt(app.dataset.seconds, 10);
    var PENALTY = parseInt(app.dataset.penalty, 10);

    // ---- State (arrays and counters) ----
    var questions = [];   // array of question objects from the server
    var answers = [];     // array of { id, picked, correct, time }
    var index = 0;        // current question number
    var score = 0;
    var hintsUsed = 0;
    var timeLeft = SECONDS;
    var timerId = null;
    var locked = false;   // true once the current question is answered

    // ---- DOM references ----
    var $ = function (id) { return document.getElementById(id); };
    var screens = { start: $('screenStart'), quiz: $('screenQuiz'), result: $('screenResult') };
    var el = {
        counter: $('qCounter'), timerNum: $('timerNum'), timerBox: $('timerBox'), timerFill: $('timerFill'),
        liveScore: $('liveScore'), progress: $('progressFill'), text: $('qText'), options: $('options'),
        hint: $('hintBox'), feedback: $('feedback'), btnHint: $('btnHint'), btnFifty: $('btnFifty'),
        btnNext: $('btnNext'), btnStart: $('btnStart'), btnRestart: $('btnRestart'), loadMsg: $('loadMsg')
    };

    // ---- Functions ----
    function showScreen(name) {
        Object.keys(screens).forEach(function (key) {
            screens[key].hidden = (key !== name);
        });
    }

    function loadQuestions() {
        el.btnStart.disabled = true;
        el.loadMsg.hidden = false;
        el.loadMsg.textContent = 'Loading questions...';
        return QC.getJSON('api/questions.php?cat=' + encodeURIComponent(CATEGORY) + '&n=' + COUNT)
            .then(function (data) {
                questions = data.questions;
                if (questions.length === 0) { throw new Error('This category has no questions yet.'); }
            });
    }

    function startQuiz() {
        loadQuestions().then(function () {
            answers = [];
            index = 0;
            score = 0;
            hintsUsed = 0;
            showScreen('quiz');
            renderQuestion();
        }).catch(function (err) {
            el.loadMsg.textContent = err.message;
            el.btnStart.disabled = false;
        });
    }

    function renderQuestion() {
        var q = questions[index];
        locked = false;

        // Update text
        el.counter.textContent = 'Question ' + (index + 1) + ' of ' + questions.length;
        el.liveScore.textContent = 'Score ' + score;
        el.text.textContent = q.q;
        el.feedback.textContent = '';
        el.feedback.className = 'feedback';
        el.hint.hidden = true;
        el.hint.textContent = '';
        el.btnNext.disabled = true;
        el.btnNext.textContent = (index === questions.length - 1) ? 'See results' : 'Next';
        el.btnHint.disabled = !q.hint;
        el.btnFifty.disabled = false;

        // Progress bar = questions finished / total
        el.progress.style.width = Math.round((index / questions.length) * 100) + '%';

        // Build option buttons (DOM creation)
        el.options.innerHTML = '';
        q.options.forEach(function (text, i) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'opt';
            btn.dataset.index = String(i);
            var key = document.createElement('span');
            key.className = 'opt-key';
            key.textContent = String(i + 1);
            var label = document.createElement('span');
            label.textContent = text;
            btn.appendChild(key);
            btn.appendChild(label);
            btn.addEventListener('click', function () { chooseAnswer(i); });
            el.options.appendChild(btn);
        });

        startTimer();
    }

    function startTimer() {
        stopTimer();
        timeLeft = SECONDS;
        paintTimer();
        timerId = setInterval(function () {
            timeLeft--;
            paintTimer();
            if (timeLeft <= 0) {
                stopTimer();
                chooseAnswer(-1);            // -1 means time ran out
            }
        }, 1000);
    }

    function stopTimer() {
        if (timerId !== null) { clearInterval(timerId); timerId = null; }
    }

    function paintTimer() {
        el.timerNum.textContent = String(Math.max(timeLeft, 0));
        var fraction = Math.max(timeLeft, 0) / SECONDS;            // calculation
        el.timerFill.style.width = (fraction * 100) + '%';
        el.timerBox.classList.toggle('low', timeLeft <= 5);        // condition
    }

    function chooseAnswer(choice) {
        if (locked) { return; }
        locked = true;
        stopTimer();

        var q = questions[index];
        var isCorrect = (choice === q.correct);
        var used = SECONDS - Math.max(timeLeft, 0);

        if (isCorrect) { score++; }

        answers.push({
            id: q.id,
            question: q.q,
            picked: choice >= 0 ? q.options[choice] : null,
            correctText: q.options[q.correct],
            isCorrect: isCorrect,
            time: used
        });

        // Show feedback on the buttons
        var buttons = el.options.querySelectorAll('.opt');
        buttons.forEach(function (b, i) {
            b.disabled = true;
            if (i === q.correct) { b.classList.add('right'); }
            else if (i === choice) { b.classList.add('wrong'); }
        });

        if (choice === -1) {
            el.feedback.textContent = 'Time is up. The answer is highlighted.';
            el.feedback.className = 'feedback bad';
        } else if (isCorrect) {
            el.feedback.textContent = 'Correct!';
            el.feedback.className = 'feedback good';
        } else {
            el.feedback.textContent = 'Not quite. The correct answer is highlighted.';
            el.feedback.className = 'feedback bad';
        }

        el.liveScore.textContent = 'Score ' + score;
        el.btnHint.disabled = true;
        el.btnFifty.disabled = true;
        el.btnNext.disabled = false;
        el.btnNext.focus();
    }

    function useHint() {
        if (locked) { return; }
        var q = questions[index];
        if (!q.hint || !el.hint.hidden) { return; }
        hintsUsed++;
        el.hint.textContent = 'Hint: ' + q.hint + '  (-' + PENALTY + ' XP)';
        el.hint.hidden = false;
        el.btnHint.disabled = true;
    }

    function useFiftyFifty() {
        if (locked) { return; }
        var q = questions[index];
        var wrong = [];
        for (var i = 0; i < q.options.length; i++) {
            if (i !== q.correct) { wrong.push(i); }
        }
        // shuffle wrong answers, hide two of them
        wrong.sort(function () { return Math.random() - 0.5; });
        wrong.slice(0, 2).forEach(function (i) {
            var b = el.options.querySelector('[data-index="' + i + '"]');
            if (b) { b.disabled = true; b.classList.add('gone'); }
        });
        hintsUsed++;
        el.btnFifty.disabled = true;
        el.feedback.textContent = 'Two wrong answers removed (-' + PENALTY + ' XP).';
        el.feedback.className = 'feedback';
    }

    function nextQuestion() {
        if (!locked) { return; }
        index++;
        if (index >= questions.length) { finishQuiz(); }
        else { renderQuestion(); }
    }

    function finishQuiz() {
        stopTimer();
        el.progress.style.width = '100%';

        var total = questions.length;
        var percent = Math.round((score / total) * 100);
        var seconds = answers.reduce(function (sum, a) { return sum + a.time; }, 0);
        var avg = (seconds / total).toFixed(1);

        showScreen('result');
        $('resScore').textContent = score + '/' + total;
        $('resPct').textContent = percent + '%';
        $('resAvg').textContent = avg + 's';
        $('resXp').textContent = '...';
        $('resNote').textContent = 'Saving your result...';

        // Title depends on score (conditions)
        var title;
        if (percent === 100) { title = 'Perfect score!'; }
        else if (percent >= 70) { title = 'Great work'; }
        else if (percent >= 40) { title = 'Getting there'; }
        else { title = 'Keep practising'; }
        $('resTitle').textContent = title;

        renderReview();

        QC.postJSON('api/submit_attempt.php', {
            category: CATEGORY,
            hints: hintsUsed,
            seconds: seconds,
            answers: answers.map(function (a) { return { id: a.id, picked: a.picked }; })
        }).then(function (res) {
            $('resXp').textContent = '+' + res.xp;
            var note = 'You are now Level ' + res.level + ' with ' + res.totalXp + ' XP. Streak: ' + res.streak + ' day' + (res.streak === 1 ? '' : 's') + '.';
            if (res.leveled) { note = 'Level up! ' + note; }
            $('resNote').textContent = note;
        }).catch(function (err) {
            $('resXp').textContent = '+0';
            $('resNote').textContent = 'Could not save this result: ' + err.message;
        });
    }

    function renderReview() {
        var list = $('reviewList');
        list.innerHTML = '';
        answers.forEach(function (a) {
            var li = document.createElement('li');
            li.className = a.isCorrect ? 'ok' : 'no';
            var q = document.createElement('strong');
            q.textContent = a.question;
            var line = document.createElement('span');
            if (a.isCorrect) {
                line.textContent = 'Correct: ' + a.correctText;
            } else if (a.picked === null) {
                line.textContent = 'No answer. Correct: ' + a.correctText;
            } else {
                line.textContent = 'You chose: ' + a.picked + '. Correct: ' + a.correctText;
            }
            li.appendChild(q);
            li.appendChild(line);
            list.appendChild(li);
        });
    }

    function restart() {
        stopTimer();
        el.btnStart.disabled = false;
        el.loadMsg.hidden = true;
        startQuiz();
    }

    // ---- Event handling ----
    el.btnStart.addEventListener('click', startQuiz);
    el.btnNext.addEventListener('click', nextQuestion);
    el.btnHint.addEventListener('click', useHint);
    el.btnFifty.addEventListener('click', useFiftyFifty);
    el.btnRestart.addEventListener('click', restart);

    document.addEventListener('keydown', function (ev) {
        if (screens.quiz.hidden) { return; }
        if (ev.key >= '1' && ev.key <= '4' && !locked) {
            var b = el.options.querySelector('[data-index="' + (parseInt(ev.key, 10) - 1) + '"]');
            if (b && !b.disabled) { b.click(); }
        } else if (ev.key === 'Enter' && locked) {
            ev.preventDefault();
            nextQuestion();
        }
    });

    // Stop the timer if the tab is left mid-question
    window.addEventListener('beforeunload', stopTimer);
})();
