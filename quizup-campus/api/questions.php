<?php
require __DIR__ . '/../includes/bootstrap.php';
api_user();

$slug  = $_GET['cat'] ?? '';
$count = max(1, min(30, (int)($_GET['n'] ?? cfg('questions_per_quiz'))));

$st = db()->prepare('SELECT id FROM categories WHERE slug = ?');
$st->execute([$slug]);
$catId = $st->fetchColumn();
if (!$catId) {
    json_out(['error' => 'Unknown category.'], 404);
}

// LIMIT cannot be bound with native prepares, so it is cast to int above.
$st = db()->prepare("SELECT id, question, option_a, option_b, option_c, option_d, correct, hint
                     FROM questions WHERE category_id = ? ORDER BY RAND() LIMIT $count");
$st->execute([$catId]);

$out = [];
foreach ($st->fetchAll() as $row) {
    $options = [$row['option_a'], $row['option_b'], $row['option_c'], $row['option_d']];
    $correctText = $options[(int)$row['correct']];
    shuffle($options);                                   // mix answer positions
    $out[] = [
        'id'      => (int)$row['id'],
        'q'       => $row['question'],
        'options' => $options,
        'correct' => array_search($correctText, $options, true),
        'hint'    => $row['hint'],
    ];
}
json_out(['questions' => $out]);
