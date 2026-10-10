<?php
/**
 * Shared start-up code: session, database, helper functions.
 * Every page and API file includes this first.
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$CONFIG = require __DIR__ . '/../config/config.php';
// Optional private overrides (keep this file out of GitHub, see .gitignore)
$localCfg = __DIR__ . '/../config/config.local.php';
if (is_file($localCfg)) {
    $CONFIG = array_merge($CONFIG, require $localCfg);
}

/** Get the PDO connection (created once). */
function db(): PDO
{
    global $CONFIG;
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host={$CONFIG['db_host']};dbname={$CONFIG['db_name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $CONFIG['db_user'], $CONFIG['db_pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            error_log('QuizUp DB error: ' . $e->getMessage());
            die('Database connection failed. Check config/config.php and that MySQL is running.');
        }
    }
    return $pdo;
}

/** Escape text for safe output in HTML. */
function e(?string $text): string
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function cfg(string $key)
{
    global $CONFIG;
    return $CONFIG[$key] ?? null;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok(?string $sent): bool
{
    return is_string($sent) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $sent);
}

/* ---------- Auth ---------- */
function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['uid'])) {
            $st = db()->prepare('SELECT * FROM users WHERE id = ?');
            $st->execute([$_SESSION['uid']]);
            $user = $st->fetch() ?: null;
        }
    }
    return $user;
}

function require_login(): array
{
    $u = current_user();
    if (!$u) {
        header('Location: login.php');
        exit;
    }
    db()->prepare('UPDATE users SET last_seen = NOW() WHERE id = ?')->execute([$u['id']]);
    return $u;
}

function login_user(int $id): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = $id;
}

/* ---------- Game maths ---------- */
function level_from_xp(int $xp): int
{
    return intdiv($xp, (int)cfg('xp_per_level')) + 1;
}

function xp_into_level(int $xp): int
{
    return $xp % (int)cfg('xp_per_level');
}

/**
 * Update the daily streak. Call once whenever the user studies.
 * Same day -> unchanged, yesterday -> +1, otherwise restart at 1.
 */
function touch_streak(int $uid): array
{
    $pdo = db();
    $st = $pdo->prepare('SELECT streak, best_streak, last_active_date FROM users WHERE id = ?');
    $st->execute([$uid]);
    $row = $st->fetch();

    $today  = new DateTimeImmutable('today');
    $last   = $row['last_active_date'] ? new DateTimeImmutable($row['last_active_date']) : null;
    $streak = (int)$row['streak'];

    if ($last === null) {
        $streak = 1;
    } else {
        $days = (int)$last->diff($today)->format('%r%a');
        if ($days === 1) {
            $streak++;
        } elseif ($days > 1) {
            $streak = 1;
        } elseif ($streak < 1) {
            $streak = 1;
        }
    }
    $best = max((int)$row['best_streak'], $streak);

    $pdo->prepare('UPDATE users SET streak = ?, best_streak = ?, last_active_date = CURDATE() WHERE id = ?')
        ->execute([$streak, $best, $uid]);

    return ['streak' => $streak, 'best' => $best];
}

function add_xp(int $uid, int $amount): array
{
    $pdo = db();
    $st = $pdo->prepare('SELECT xp FROM users WHERE id = ?');
    $st->execute([$uid]);
    $before = (int)$st->fetchColumn();
    $after  = max(0, $before + $amount);
    $pdo->prepare('UPDATE users SET xp = ? WHERE id = ?')->execute([$after, $uid]);
    return [
        'xp'      => $after,
        'level'   => level_from_xp($after),
        'leveled' => level_from_xp($after) > level_from_xp($before),
    ];
}

/* ---------- JSON helpers for /api ---------- */
function json_out(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function api_user(): array
{
    $u = current_user();
    if (!$u) {
        json_out(['error' => 'Please log in again.'], 401);
    }
    db()->prepare('UPDATE users SET last_seen = NOW() WHERE id = ?')->execute([$u['id']]);
    return $u;
}

function api_post_guard(): array
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_out(['error' => 'POST required.'], 405);
    }
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!csrf_ok($token)) {
        json_out(['error' => 'Session expired. Refresh the page.'], 403);
    }
    $body = json_decode(file_get_contents('php://input'), true);
    return is_array($body) ? $body : [];
}
