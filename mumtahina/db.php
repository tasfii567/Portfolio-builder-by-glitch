<?php
/**
 * db.php — shared PDO connection + share-link helper.
 * XAMPP defaults: host=localhost, user=root, no password.
 */

$DB_HOST = 'localhost';
$DB_NAME = 'portfolio_builder';
$DB_USER = 'root';
$DB_PASS = '';

/* ------------------------------------------------------------------
 * PUBLIC LINK SETTING
 * Leave EMPTY to auto-detect from whatever address you opened the app
 * with (localhost, your 192.168.x.x LAN IP, an ngrok URL, a real
 * domain — it adapts automatically).
 *
 * To FORCE every share link to a fixed public address, set it here,
 * WITHOUT a trailing slash, e.g.:
 *   $APP_BASE_URL = 'https://abc123.ngrok-free.app/Portfolio-builder-by-glitch';
 *   $APP_BASE_URL = 'http://192.168.0.105/Portfolio-builder-by-glitch';
 * ------------------------------------------------------------------ */
$APP_BASE_URL = '';

/** Build the public, shareable URL for a portfolio slug. */
function build_share_url(string $slug): string {
    global $APP_BASE_URL;
    if (!empty($APP_BASE_URL)) {
        return rtrim($APP_BASE_URL, '/') . '/view-portfolio.php?u=' . urlencode($slug);
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'] ?? '')), '/');
    return $scheme . '://' . $host . $dir . '/view-portfolio.php?u=' . urlencode($slug);
}

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}