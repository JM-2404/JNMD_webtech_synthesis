<?php
require __DIR__ . '/../includes/bootstrap.php';
$user = api_user();
$body = api_post_guard();

$text = trim((string)($body['message'] ?? ''));
$text = preg_replace('/\s+/u', ' ', $text);

if ($text === '') {
    json_out(['error' => 'Write something first.'], 422);
}
$len = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
if ($len > 300) {
    json_out(['error' => 'Keep messages under 300 characters.'], 422);
}

// Simple flood control: one message per 1.5 seconds per session
$now = microtime(true);
if (isset($_SESSION['last_msg']) && ($now - $_SESSION['last_msg']) < 1.5) {
    json_out(['error' => 'Slow down a little.'], 429);
}
$_SESSION['last_msg'] = $now;

db()->prepare('INSERT INTO community_messages (user_id, message) VALUES (?, ?)')->execute([$user['id'], $text]);
json_out(['ok' => true]);
