<?php
require __DIR__ . '/../includes/bootstrap.php';
$user  = api_user();
$since = max(0, (int)($_GET['since'] ?? 0));
$pdo   = db();

if ($since === 0) {
    // First load: last 50 messages, oldest first
    $st = $pdo->query('SELECT m.id, m.user_id, m.message, m.created_at, u.username
                       FROM community_messages m JOIN users u ON u.id = m.user_id
                       ORDER BY m.id DESC LIMIT 50');
    $msgs = array_reverse($st->fetchAll());
} else {
    $st = $pdo->prepare('SELECT m.id, m.user_id, m.message, m.created_at, u.username
                         FROM community_messages m JOIN users u ON u.id = m.user_id
                         WHERE m.id > ? ORDER BY m.id ASC LIMIT 100');
    $st->execute([$since]);
    $msgs = $st->fetchAll();
}

$online = $pdo->query('SELECT username FROM users WHERE last_seen >= (NOW() - INTERVAL 5 MINUTE) ORDER BY username')->fetchAll(PDO::FETCH_COLUMN);

json_out(['messages' => $msgs, 'online' => $online]);
