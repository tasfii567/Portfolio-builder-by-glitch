<?php
/**
 * register.php — create a new account.
 * Stores name, email and a HASHED password in the `users` table.
 */
session_start();
require __DIR__ . '/db.php';

if (isset($_SESSION['user_id'])) { header('Location: Dashboard.php'); exit; }

$error = '';
$old   = ['name' => '', 'email' => '', 'role' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $role    = trim($_POST['role'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm']  ?? '';
    $old     = ['name' => $name, 'email' => $email, 'role' => $role];

    if ($name === '' || $email === '' || $pass === '') {
        $error = 'Name, email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($pass !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'That email is already registered. Try logging in.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)")
                ->execute([$name, $email, $hash, $role !== '' ? $role : 'Member']);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account — PortfolioBuilder</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#f4f1ea;--surface:#fff;--surface-2:#efeade;--line:#e4ddcc;--text:#1d211a;--muted:#797f6f;
  --primary:#3a4a23;--accent:#7d9e58;font-family:"Plus Jakarta Sans",system-ui,Arial,sans-serif}
*{box-sizing:border-box;margin:0;padding:0}a{text-decoration:none}
body{background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.wrap{width:100%;max-width:500px}
.brand{font-size:24px;font-weight:800;letter-spacing:-.5px;text-align:center;margin-bottom:20px}
.brand .g{color:var(--accent)}
.card{background:var(--surface);border:1px solid var(--line);border-radius:18px;padding:34px;box-shadow:0 12px 30px rgba(40,45,30,.08)}
.card h2{font-size:26px;font-weight:800;letter-spacing:-.4px}
.card .sub{color:var(--muted);font-size:14px;margin:6px 0 24px}
.row{display:flex;gap:14px;flex-wrap:wrap}
.row .col{flex:1;min-width:160px}
.field{margin-bottom:16px}
label{display:block;font-size:13px;color:var(--muted);font-weight:600;margin-bottom:6px}
input{width:100%;background:var(--surface);border:1px solid var(--line);color:var(--text);
  padding:12px 14px;border-radius:11px;font-size:14px;font-family:inherit}
input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(125,158,88,.18)}
input::placeholder{color:#a7ab9c}
.btn{width:100%;background:var(--primary);color:#fff;font-weight:700;font-size:15px;padding:13px;
  border:none;border-radius:12px;cursor:pointer;transition:.18s;font-family:inherit;margin-top:6px}
.btn:hover{background:#2f3d1c;transform:translateY(-1px);box-shadow:0 8px 18px rgba(40,45,30,.18)}
.alt{text-align:center;color:var(--muted);font-size:14px;margin-top:20px}
.alt a{color:var(--primary);font-weight:700}
.err{background:#f6e2dd;border:1px solid #e9c4ba;color:#a8442f;border-radius:11px;padding:12px 14px;font-size:14px;margin-bottom:18px}
</style>
</head>
<body>
<div class="wrap">
  <div class="brand">Portfolio<span class="g">Builder</span></div>
  <div class="card">
    <h2>Create your account</h2>
    <p class="sub">Register to start building your portfolio.</p>
    <?php if ($error): ?><div class="err">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" novalidate>
      <div class="field"><label>Full name *</label>
        <input type="text" name="name" placeholder="e.g. Alex Morgan" value="<?= htmlspecialchars($old['name']) ?>" required></div>
      <div class="field"><label>Email *</label>
        <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($old['email']) ?>" required></div>
      <div class="field"><label>Professional title (optional)</label>
        <input type="text" name="role" placeholder="e.g. AI Engineer" value="<?= htmlspecialchars($old['role']) ?>"></div>
      <div class="row">
        <div class="col field"><label>Password *</label>
          <input type="password" name="password" placeholder="Min. 6 characters" required></div>
        <div class="col field"><label>Confirm password *</label>
          <input type="password" name="confirm" placeholder="Re-type password" required></div>
      </div>
      <button type="submit" class="btn">Create account</button>
    </form>
    <p class="alt">Already have an account? <a href="login.php">Log in</a></p>
  </div>
</div>
</body>
</html>
