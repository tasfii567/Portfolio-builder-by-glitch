<?php
/**
 * register.php — create a new account.
 * Stores name, email and a HASHED password in the `users` table.
 */
session_start();
require __DIR__ . '/db.php';

// If already logged in, go straight to the dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: Dashboard.php');
    exit;
}

$error = '';
$old   = ['name' => '', 'email' => '', 'role' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $role    = trim($_POST['role'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm']  ?? '';
    $old     = ['name' => $name, 'email' => $email, 'role' => $role];

    // --- Validate ---
    if ($name === '' || $email === '' || $pass === '') {
        $error = 'Name, email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($pass !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // --- Is the email already taken? ---
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'That email is already registered. Try logging in.';
        } else {
            // --- Store with a hashed password ---
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare(
                "INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)"
            )->execute([$name, $email, $hash, $role !== '' ? $role : 'Member']);

            // Success → send to login with a friendly flag.
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
<title>Create Account — Portfolio Builder</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{
  --bg:#0f1117;--surface:#171a23;--surface-2:#1f2430;--line:#2a3040;
  --text:#e8eaf0;--muted:#9aa3b5;--primary:#6c5ce7;--primary-soft:#8b7cf0;--accent:#ff7a59;
}
body{background:var(--bg);color:var(--text);min-height:100vh;
  font-family:"Segoe UI",system-ui,-apple-system,Roboto,Arial,sans-serif;
  display:flex;align-items:center;justify-content:center;padding:24px}
.auth-wrap{width:100%;max-width:480px}
.brand{display:flex;align-items:center;gap:12px;justify-content:center;margin-bottom:22px}
.brand .logo{width:46px;height:46px;border-radius:12px;
  background:linear-gradient(135deg,var(--primary),var(--accent));
  display:grid;place-items:center;font-weight:800;font-size:20px;color:#fff}
.brand h1{font-size:18px;margin:0}
.brand span{font-size:11px;color:var(--muted);letter-spacing:.5px}
.card-dark{background:var(--surface);border:1px solid var(--line);border-radius:18px;
  padding:30px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
.card-dark h2{font-size:22px;margin-bottom:4px}
.card-dark .sub{color:var(--muted);font-size:14px;margin-bottom:22px}
.form-label{font-size:13px;color:var(--muted);margin-bottom:6px}
.form-control{background:var(--surface-2);border:1px solid var(--line);color:var(--text);
  padding:11px 13px;border-radius:10px}
.form-control:focus{background:var(--surface-2);color:var(--text);
  border-color:var(--primary);box-shadow:0 0 0 .2rem rgba(108,92,231,.25)}
.form-control::placeholder{color:#5d6577}
.btn-grad{background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;
  font-weight:600;padding:12px;border-radius:10px;border:none;width:100%;transition:.18s}
.btn-grad:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.3);color:#fff}
.alt{text-align:center;color:var(--muted);font-size:14px;margin-top:18px}
.alt a{color:var(--primary-soft);font-weight:600;text-decoration:none}
.err{background:rgba(255,99,99,.12);border:1px solid rgba(255,99,99,.3);color:#ff8585;
  border-radius:10px;padding:11px 14px;font-size:14px;margin-bottom:18px}
</style>
</head>
<body>
<div class="auth-wrap">

  <div class="brand">
    <div class="logo">P</div>
    <div>
      <h1>Portfolio Builder</h1>
      <span>BUILD · SHOWCASE · GET HIRED</span>
    </div>
  </div>

  <div class="card-dark">
    <h2>Create your account</h2>
    <p class="sub">Register to start building your portfolio.</p>

    <?php if ($error): ?><div class="err">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="post" novalidate>
      <div class="mb-3">
        <label class="form-label">Full name *</label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Alex Morgan"
               value="<?= htmlspecialchars($old['name']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email *</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com"
               value="<?= htmlspecialchars($old['email']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Professional title <span style="opacity:.6">(optional)</span></label>
        <input type="text" name="role" class="form-control" placeholder="e.g. Frontend Developer"
               value="<?= htmlspecialchars($old['role']) ?>">
      </div>
      <div class="row">
        <div class="col-12 col-sm-6 mb-3">
          <label class="form-label">Password *</label>
          <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
        </div>
        <div class="col-12 col-sm-6 mb-3">
          <label class="form-label">Confirm password *</label>
          <input type="password" name="confirm" class="form-control" placeholder="Re-type password" required>
        </div>
      </div>
      <button type="submit" class="btn-grad mt-2">Create account</button>
    </form>

    <p class="alt">Already have an account? <a href="login.php">Log in</a></p>
  </div>
</div>
</body>
</html>
