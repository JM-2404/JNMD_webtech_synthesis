<?php
require __DIR__ . '/../includes/bootstrap.php';
api_user();

$slug = $_GET['cat'] ?? '';
$st = db()->prepare('SELECT id, name FROM categories WHERE slug = ?');
$st->execute([$slug]);
$cat = $st->fetch();
if (!$cat) {
    json_out(['error' => 'Unknown category.'], 404);
}

$st = db()->prepare('SELECT id, front, back FROM flashcards WHERE category_id = ? ORDER BY id');
$st->execute([$cat['id']]);
json_out(['category' => $cat['name'], 'cards' => $st->fetchAll()]);
