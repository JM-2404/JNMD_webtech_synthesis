<?php
require __DIR__ . '/../includes/bootstrap.php';
$user = api_user();
$body = api_post_guard();

$known = max(0, min(50, (int)($body['known'] ?? 0)));
$xp    = min(30, $known * 2);          // 2 XP per known card, capped at 30 per deck run

$streak = touch_streak((int)$user['id']);
$gain   = $xp > 0 ? add_xp((int)$user['id'], $xp) : ['xp' => (int)$user['xp'], 'level' => level_from_xp((int)$user['xp']), 'leveled' => false];

json_out(['xp' => $xp, 'totalXp' => $gain['xp'], 'level' => $gain['level'], 'streak' => $streak['streak']]);
