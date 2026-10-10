<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();

$pageTitle   = 'Chat';
$active      = 'chat';
$pageScripts = ['chat.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="page-head">
    <h1>Campus chat</h1>
    <p class="muted">One shared room. Be kind and keep it about learning.</p>
</section>

<section class="chat-layout" id="chatApp" data-me="<?= (int)$user['id'] ?>">
    <div class="panel chat-box">
        <div class="chat-log" id="chatLog" aria-live="polite">
            <p class="empty" id="chatEmpty">Loading messages...</p>
        </div>
        <form class="chat-form" id="chatForm" autocomplete="off">
            <input id="chatInput" type="text" maxlength="300" placeholder="Write a message" aria-label="Message">
            <button class="btn primary" type="submit">Send</button>
        </form>
        <p class="muted small-note" id="chatErr" role="alert"></p>
    </div>
    <aside class="panel online">
        <h3>Online now <span id="onlineCount" class="tag">0</span></h3>
        <ul id="onlineList" class="online-list"></ul>
    </aside>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
