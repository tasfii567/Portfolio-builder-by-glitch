<?php
/**
 * login.php — sign in with email + password.
 */
session_start();
require __DIR__ . '/db.php';

if (isset($_SESSION['user_id'])) { header('Location: Dashboard.php'); exit; }

$error  = '';
$notice = isset($_GET['registered']) ? 'Account created! Please log in.' : '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: Dashboard.php');
            exit;
        } else {
            $error = 'Wrong email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log In — PortfolioBuilder</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#f4f1ea;--surface:#fff;--surface-2:#efeade;--line:#e4ddcc;--text:#1d211a;--muted:#797f6f;
  --primary:#3a4a23;--accent:#7d9e58;font-family:"Plus Jakarta Sans",system-ui,Arial,sans-serif}
*{box-sizing:border-box;margin:0;padding:0}a{text-decoration:none}
body{background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.wrap{width:100%;max-width:430px}
.brand{font-size:24px;font-weight:800;letter-spacing:-.5px;text-align:center;margin-bottom:20px}
.brand .g{color:var(--accent)}
.card{background:var(--surface);border:1px solid var(--line);border-radius:18px;padding:34px;box-shadow:0 12px 30px rgba(40,45,30,.08)}
.card h2{font-size:26px;font-weight:800;letter-spacing:-.4px}
.card .sub{color:var(--muted);font-size:14px;margin:6px 0 24px}
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
.ok{background:#e7efd9;border:1px solid #cfe0b6;color:#4a6a2c;border-radius:11px;padding:12px 14px;font-size:14px;margin-bottom:18px}
</style>
</head>
<body>
<div class="wrap">
  <div class="brand">Portfolio<span class="g">Builder</span></div>
  <div class="card">
    <h2>Welcome back</h2>
    <p class="sub">Log in to manage your portfolio.</p>
    <?php if ($notice): ?><div class="ok">✅ <?= htmlspecialchars($notice) ?></div><?php endif; ?>
    <?php if ($error):  ?><div class="err">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" novalidate>
      <div class="field"><label>Email</label>
        <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($email) ?>" required></div>
      <div class="field"><label>Password</label>
        <input type="password" name="password" placeholder="Your password" required></div>
      <button type="submit" class="btn">Log in</button>
    </form>
    <p class="alt">New here? <a href="register.php">Create an account</a></p>
  </div>
</div>
</body>
</html>
