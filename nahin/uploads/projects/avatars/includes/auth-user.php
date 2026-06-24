<?php
/**
 * includes/auth-user.php
 * Shared authentication + current-user bootstrap for logged-in pages.
 *
 * After requiring this file the following are available:
 *   $pdo        PDO connection
 *   $userId     (int) logged-in user id
 *   $userName   (string) full name
 *   $userEmail  (string) email
 *   $userRole   (string) profile title if set, else the account role
 *   $userAvatar (string|null) avatar path
 *   $initials   (string) 1-2 letter fallback for the avatar bubble
 *
 * Not logged in -> redirect to login.php.
 */

require_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
$stmt->execute([$userId]);
$__user = $stmt->fetch();

if (!$__user) {
    // Session points at a user that no longer exists.
    session_destroy();
    header('Location: login.php');
    exit;
}

$userName  = $__user['name'];
$userEmail = $__user['email'];

// Profile holds the display title + avatar (may not exist yet).
$stmt = $pdo->prepare('SELECT title, avatar FROM profiles WHERE user_id = ?');
$stmt->execute([$userId]);
$__profile = $stmt->fetch() ?: ['title' => null, 'avatar' => null];

$userRole   = $__profile['title'] ?: ucfirst($__user['role'] ?? 'Member');
$userAvatar = $__profile['avatar'] ?: null;

$__parts  = preg_split('/\s+/', trim((string) $userName));
$initials = strtoupper(substr($__parts[0] ?? '', 0, 1) . (isset($__parts[1]) ? substr($__parts[1], 0, 1) : ''));
if ($initials === '') {
    $initials = 'U';
}
