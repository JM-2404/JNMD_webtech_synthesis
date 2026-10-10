/* QuizUp Campus - chat (polls the server every 5 seconds) */
(function () {
    'use strict';

    var app = document.getElementById('chatApp');
    if (!app) { return; }

    var me = parseInt(app.dataset.me, 10);
    var log = document.getElementById('chatLog');
    var form = document.getElementById('chatForm');
    var input = document.getElementById('chatInput');
    var errBox = document.getElementById('chatErr');
    var onlineList = document.getElementById('onlineList');
    var onlineCount = document.getElementById('onlineCount');
    var lastId = 0;

    function timeLabel(sqlDate) {
        // "2026-10-10 14:03:05" -> "14:03"
        return String(sqlDate).slice(11, 16);
    }

    function addMessage(m) {
        var empty = document.getElementById('chatEmpty');
        if (empty) { empty.remove(); }

        var nearBottom = (log.scrollHeight - log.scrollTop - log.clientHeight) < 80;

        var row = document.createElement('div');
        row.className = 'msg' + (parseInt(m.user_id, 10) === me ? ' mine' : '');
        var who = document.createElement('span');
        who.className = 'msg-who';
        who.textContent = m.username + ' ' + timeLabel(m.created_at);
        var bubble = document.createElement('span');
        bubble.className = 'msg-text';
        bubble.textContent = m.message;            // textContent keeps user text safe
        row.appendChild(who);
        row.appendChild(bubble);
        log.appendChild(row);

        if (nearBottom || parseInt(m.user_id, 10) === me) { log.scrollTop = log.scrollHeight; }
    }

    function paintOnline(names) {
        onlineList.innerHTML = '';
        names.forEach(function (n) {
            var li = document.createElement('li');
            li.textContent = n;
            onlineList.appendChild(li);
        });
        onlineCount.textContent = String(names.length);
    }

    function poll() {
        QC.getJSON('api/chat_fetch.php?since=' + lastId).then(function (data) {
            if (lastId === 0 && data.messages.length === 0) {
                var e = document.getElementById('chatEmpty');
                if (e) { e.textContent = 'No messages yet. Say hello!'; }
            }
            data.messages.forEach(function (m) {
                addMessage(m);
                lastId = Math.max(lastId, parseInt(m.id, 10));
            });
            paintOnline(data.online);
        }).catch(function () { /* try again next tick */ });
    }

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var text = input.value.trim();
        if (text === '') { return; }
        errBox.textContent = '';
        input.value = '';
        QC.postJSON('api/chat_send.php', { message: text }).then(poll).catch(function (err) {
            errBox.textContent = err.message;
            input.value = text;
        });
    });

    poll();
    var timer = setInterval(poll, 5000);
    // Pause polling while the tab is hidden to save requests
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) { clearInterval(timer); }
        else { poll(); timer = setInterval(poll, 5000); }
    });
})();
