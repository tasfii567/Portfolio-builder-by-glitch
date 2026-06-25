<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/config/db.php';

$userId = (int) $_SESSION['user_id'];
$templateId = (int) ($_POST['template_id'] ?? 0);

if ($templateId <= 0 && !empty($_POST['template'])) {
    $legacy = trim((string) $_POST['template']);
    if (preg_match('/^[A-Za-z0-9._-]+\.html$/', $legacy)) {
        $stmt = $pdo->prepare("SELECT id FROM templates WHERE html_file = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$legacy]);
        $templateId = (int) $stmt->fetchColumn();
    }
}

if ($templateId <= 0) {
    header('Location: choose-template.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM templates WHERE id = ? AND status = 'active' LIMIT 1");
$stmt->execute([$templateId]);
if (!$stmt->fetchColumn()) {
    header('Location: choose-template.php');
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO profiles (user_id, selected_template_id)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE
        selected_template_id = VALUES(selected_template_id)
");
$stmt->execute([$userId, $templateId]);

header('Location: create-portfolio.php?msg=template_saved');
exit;
