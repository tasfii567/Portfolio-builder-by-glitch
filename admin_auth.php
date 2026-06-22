<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SESSION['admin_role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}
?>