<?php
require __DIR__ . '/../includes/bootstrap.php';
$user = api_user();
$body = api_post_guard();

$slug    = (string)($body['category'] ?? '');
$answers = $body['answers'] ?? [];
$hints   = max(0, (int)($body['hints'] ?? 0));
$secs    = max(0, (int)($body['seconds'] ?? 0));

if (!is_array($answers) || count($answers) === 0 || count($answers) > 30) {
    json_out(['error' => 'No answers received.'], 422);
}

$pdo = db();
$st = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
$st->execute([$slug]);
$catId = (int)$st->fetchColumn();
if (!$catId) {
    json_out(['error' => 'Unknown category.'], 404);
}

// Re-check every answer on the server so the score cannot be faked.
// The client sends the answer TEXT it picked (options are shuffled per quiz).
$score = 0;
$total = 0;
$seen  = [];
$sel   = $pdo->prepare('SELECT option_a, option_b, option_c, option_d, correct FROM questions WHERE id = ? AND category_id = ?');
foreach ($answers as $a) {
    $qid = (int)($a['id'] ?? 0);
    if ($qid <= 0 || isset($seen[$qid])) { continue; }
    $seen[$qid] = true;
    $sel->execute([$qid, $catId]);
    $row = $sel->fetch();
    if (!$row) { continue; }
    $total++;
    $opts = [$row['option_a'], $row['option_b'], $row['option_c'], $row['option_d']];
    if (isset($a['picked']) && $a['picked'] === $opts[(int)$row['correct']]) {
        $score++;
    }
}
if ($total === 0) {
    json_out(['error' => 'No valid questions.'], 422);
}

// XP calculation
$xp = $score * (int)cfg('xp_per_correct') + (int)cfg('xp_completion');
if ($score === $total) {
    $xp += (int)cfg('xp_perfect_bonus');
}
$xp -= $hints * (int)cfg('xp_hint_penalty');
$xp  = max(0, $xp);

$pdo->prepare('INSERT INTO quiz_attempts (user_id, category_id, score, total, xp_earned, time_taken) VALUES (?,?,?,?,?,?)')
    ->execute([$user['id'], $catId, $score, $total, $xp, $secs]);

$streak = touch_streak((int)$user['id']);
$gain   = add_xp((int)$user['id'], $xp);

json_out([
    'score'   => $score,
    'total'   => $total,
    'xp'      => $xp,
    'totalXp' => $gain['xp'],
    'level'   => $gain['level'],
    'leveled' => $gain['leveled'],
    'streak'  => $streak['streak'],
]);
