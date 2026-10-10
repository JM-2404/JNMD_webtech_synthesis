<?php
/**
 * QuizUp Campus - configuration
 * Local (AMPPS) defaults are below. When you host online (InfinityFree),
 * replace the database values with the ones from your hosting control panel.
 */
return [
    'app_name' => 'QuizUp Campus',

    // ---- Database ----
    // AMPPS default: host localhost, user root, password mysql
    'db_host' => 'localhost',
    'db_name' => 'quizup_campus',
    'db_user' => 'root',
    'db_pass' => 'mysql',

    // ---- Game rules ----
    'questions_per_quiz'   => 8,
    'seconds_per_question' => 20,
    'xp_per_correct'       => 10,
    'xp_completion'        => 5,
    'xp_perfect_bonus'     => 20,
    'xp_hint_penalty'      => 2,
    'xp_per_level'         => 100,
];
