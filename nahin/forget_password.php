<?php
require 'config/db.php';

$step    = 1;   // 1 = enter email, 2 = enter new password
$errors  = [];
$success = false;
$email   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Step 1: verify email ──────────────────────────────
    if (isset($_POST['step']) && $_POST['step'] === '1') {
        $email = trim($_POST['email'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
            $step = 1;
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if (!$stmt->fetch()) {
                $errors[] = "No account found with that email.";
                $step = 1;
            } else {
                $step = 2;  // email found, show password fields
            }
        }
    }

    // ── Step 2: update password ───────────────────────────
    elseif (isset($_POST['step']) && $_POST['step'] === '2') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
            $step = 2;
        } elseif ($password !== $confirm) {
            $errors[] = "Passwords do not match.";
            $step = 2;
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed, $email]);

            header("Location: login.php?reset=1");
            exit;
        }
    }
}

require 'includes/header.php';
?>

<section class="fp-wrap">
    <div class="fp-card">

        <div class="fp-avatar">
            <i class="bi bi-key-fill"></i>
        </div>

        <h1 class="fp-h1">Reset password</h1>
        <p class="fp-sub">
            <?= $step === 1
                ? "Enter your account email to get started."
                : "Choose a new password for <strong>" . htmlspecialchars($email) . "</strong>" ?>
        </p>

        <?php if (!empty($errors)): ?>
            <div class="fp-errors">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="step" value="<?= $step ?>">
            <?php if ($step === 2): ?>
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <?php endif; ?>

            <?php if ($step === 1): ?>
            <!-- Step 1: Email -->
            <div class="fp-group">
                <label class="fp-label" for="fp-email">Email Address</label>
                <div class="fp-input-wrap">
                    <i class="bi bi-envelope fp-icon"></i>
                    <input id="fp-email"
                           type="email"
                           name="email"
                           class="fp-input"
                           placeholder="you@email.com"
                           value="<?= htmlspecialchars($email) ?>"
                           required>
                </div>
            </div>

            <button type="submit" class="fp-btn">
                <i class="bi bi-arrow-right-circle me-2"></i> Continue
            </button>

            <?php else: ?>
            <!-- Step 2: New password -->
            <div class="fp-group">
                <label class="fp-label" for="fp-pass">New Password</label>
                <div class="fp-input-wrap">
                    <i class="bi bi-lock fp-icon"></i>
                    <input id="fp-pass"
                           type="password"
                           name="password"
                           class="fp-input fp-input-pass"
                           placeholder="Create a new password"
                           minlength="6"
                           required>
                    <button type="button" class="fp-eye" onclick="togglePass('fp-pass', this)" aria-label="Show password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="fp-group">
                <label class="fp-label" for="fp-confirm">Confirm New Password</label>
                <div class="fp-input-wrap">
                    <i class="bi bi-lock fp-icon"></i>
                    <input id="fp-confirm"
                           type="password"
                           name="confirm_password"
                           class="fp-input fp-input-pass"
                           placeholder="Re-enter your new password"
                           minlength="6"
                           required>
                    <button type="button" class="fp-eye" onclick="togglePass('fp-confirm', this)" aria-label="Show confirm password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="fp-btn">
                <i class="bi bi-check-circle me-2"></i> Reset Password
            </button>
            <?php endif; ?>

        </form>

        <div class="fp-back">
            <a href="login.php"><i class="bi bi-arrow-left me-1"></i> Back to login</a>
        </div>

    </div>
</section>

<style>
.fp-wrap {
    min-height: calc(100vh - 72px);
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 24px;
}

.fp-card {
    width: 100%;
    max-width: 420px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 40px 36px;
}

.fp-avatar {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: var(--chip-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent);
    font-size: 22px;
    margin-bottom: 20px;
}

.fp-h1 {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -.04em;
    color: var(--text-dark);
    margin-bottom: 6px;
}

.fp-sub {
    font-size: 14px;
    color: var(--text-mid);
    margin-bottom: 28px;
    line-height: 1.5;
}

/* ── Errors ─────────────────────────── */
.fp-errors {
    background: #fdecea;
    border: 1px solid #f5c6cb;
    color: #842029;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 20px;
    font-size: 13px;
}

.fp-errors ul { margin: 0; padding-left: 18px; }
.fp-errors li { margin-bottom: 2px; }

/* ── Fields ─────────────────────────── */
.fp-group { margin-bottom: 18px; }

.fp-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 7px;
    letter-spacing: -.01em;
}

.fp-input-wrap { position: relative; }

.fp-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 15px;
    pointer-events: none;
}

.fp-input {
    width: 100%;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 12px 14px 12px 40px;
    font-size: 14px;
    color: var(--text-dark);
    font-family: inherit;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    box-sizing: border-box;
}

.fp-input::placeholder { color: var(--text-muted); }

.fp-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(107,140,90,.15);
}

/* ── Eye toggle ─────────────────────── */
.fp-input-pass { padding-right: 40px; }

.fp-eye {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    font-size: 16px;
    padding: 0;
    line-height: 1;
    display: flex;
    align-items: center;
}

.fp-eye:hover { color: var(--text-dark); }

/* ── Button ─────────────────────────── */
.fp-btn {
    width: 100%;
    background: var(--brand-dark);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 13px 24px;
    font-size: 14px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    letter-spacing: -.01em;
    transition: background .2s, transform .15s;
    margin-top: 4px;
}

.fp-btn:hover {
    background: var(--brand-hover);
    transform: translateY(-1px);
}

.fp-btn:active { transform: translateY(0); }

/* ── Back link ──────────────────────── */
.fp-back {
    text-align: center;
    margin-top: 24px;
    font-size: 13px;
}

.fp-back a {
    color: var(--text-mid);
    text-decoration: none;
    font-weight: 500;
}

.fp-back a:hover {
    color: var(--accent);
    text-decoration: underline;
}
</style>

<script>
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type     = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type     = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

<?php require 'includes/footer.php'; ?>