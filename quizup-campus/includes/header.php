<?php
/** Expects: $pageTitle (string), $active (string), optional $user, optional $bodyClass */
$user      = $user ?? current_user();
$pageTitle = $pageTitle ?? 'QuizUp Campus';
$active    = $active ?? '';
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?> | QuizUp Campus</title>
    <script>
        /* Apply saved theme before first paint (prevents a flash) */
        (function () {
            var saved = localStorage.getItem('qc-theme');
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', saved || (prefersDark ? 'dark' : 'light'));
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip" href="#main">Skip to content</a>
<header class="topbar">
    <a class="brand" href="<?= $user ? 'dashboard.php' : 'index.php' ?>">
        <span class="brand-mark" aria-hidden="true">Q</span>
        <span class="brand-name">QuizUp <b>Campus</b></span>
    </a>

    <?php if ($user): ?>
    <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false">Menu</button>
    <nav class="nav" id="mainNav" aria-label="Main">
        <a href="dashboard.php"   class="<?= $active === 'dashboard'   ? 'on' : '' ?>">Dashboard</a>
        <a href="quizzes.php"     class="<?= $active === 'quizzes'     ? 'on' : '' ?>">Quizzes</a>
        <a href="flashcards.php"  class="<?= $active === 'flashcards'  ? 'on' : '' ?>">Flashcards</a>
        <a href="chat.php"        class="<?= $active === 'chat'        ? 'on' : '' ?>">Chat</a>
        <a href="leaderboard.php" class="<?= $active === 'leaderboard' ? 'on' : '' ?>">Leaderboard</a>
        <a href="profile.php"     class="<?= $active === 'profile'     ? 'on' : '' ?>">Profile</a>
    </nav>
    <div class="topbar-right">
        <span class="chip" title="Daily streak"><span class="flame" aria-hidden="true"></span><?= (int)$user['streak'] ?></span>
        <span class="chip" title="Experience points"><?= (int)$user['xp'] ?> XP</span>
        <button class="theme-btn" id="themeToggle" type="button" aria-label="Switch light or dark mode"></button>
        <a class="btn ghost small" href="logout.php">Log out</a>
    </div>
    <?php else: ?>
    <div class="topbar-right">
        <button class="theme-btn" id="themeToggle" type="button" aria-label="Switch light or dark mode"></button>
    </div>
    <?php endif; ?>
</header>
<main id="main" class="page">
