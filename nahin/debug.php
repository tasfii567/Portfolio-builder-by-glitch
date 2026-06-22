<?php
require 'config/db.php';

$testName = 'farin';
$testPassword = 'PUT_THE_EXACT_PASSWORD_YOU_TYPED_HERE';

$stmt = $pdo->prepare("SELECT * FROM users WHERE name = ?");
$stmt->execute([$testName]);
$user = $stmt->fetch();

echo "<pre>";
echo "User found: ";
var_dump($user);

if ($user) {
    echo "Password column length: " . strlen($user['password']) . "\n";
    echo "Password starts with: " . substr($user['password'], 0, 4) . "\n";
    echo "Verify result: ";
    var_dump(password_verify($testPassword, $user['password']));
}
echo "</pre>";