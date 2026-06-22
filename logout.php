<?php
/**
 * Logout — destroys the session and returns to the login page.
 */
session_start();
 
// Clear all session data
$_SESSION = [];
 
// Remove the session cookie
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}
 
session_destroy();
 
// Send the user back to login (change to your login page if different)
header('Location: login.php');
exit;
 