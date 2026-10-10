<?php
/**
 * QuizUp Campus - Configuration & Database Connection Loader
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Load default settings (local/AMPPS defaults)
$default_config = [
    'app_name'               => 'QuizUp Campus',
    'db_host'                => 'localhost',
    'db_name'                => 'quizup_campus',
    'db_user'                => 'root',
    'db_pass'                => 'mysql', // Change to '' if your AMPPS requires no password
    'questions_per_quiz'     => 8,
    'seconds_per_question'   => 20,
    'xp_per_correct'         => 10,
    'xp_completion'          => 5,
    'xp_perfect_bonus'       => 20,
    'xp_hint_penalty'        => 2,
    'xp_per_level'           => 100,
];

// 2. Check if a local override file exists (used for live hosting / InfinityFree)
$local_config_file = __DIR__ . '/config.local.php';
if (file_exists($local_config_file)) {
    $local_config = include $local_config_file;
    if (is_array($local_config)) {
        // Merge local settings over defaults
        $config = array_merge($default_config, $local_config);
    } else {
        $config = $default_config;
    }
} else {
    $config = $default_config;
}

// Make config globally accessible if needed
global $config;

// 3. Establish PDO Database Connection
try {
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    http_response_code(500);
    die("<div style='font-family: Arial, sans-serif; padding: 20px; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; max-width: 600px; margin: 40px auto;'>
            <h3>⚠️ Database Connection Failed</h3>
            <p>Could not connect to database <code>{$config['db_name']}</code> on host <code>{$config['db_host']}</code>.</p>
            <p style='font-size: 0.9em; color: #555;'>Technical details: " . htmlspecialchars($e->getMessage()) . "</p>
         </div>");
}
?>