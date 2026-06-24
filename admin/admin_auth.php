<?php
session_start();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../nahin/login.php");
    exit;
}

$_SESSION['admin_name'] = $_SESSION['admin_name'] ?? ($_SESSION['name'] ?? 'Admin');
$_SESSION['admin_email'] = $_SESSION['admin_email'] ?? ($_SESSION['email'] ?? '');
?>
