<?php
require 'config/db.php';

$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ---- Validation ----
    if ($name === '') {
        $errors[] = "Name is required.";
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // check if email already used
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "That email is already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
            );
            $stmt->execute([$name, $email, $hashed]);

            // Registration successful -> go to login page
            header("Location: login.php?registered=1");
            exit;
        }
    }
}

require 'includes/header.php';
?>

<section class="reg-wrap">
    <div class="reg-split">

        <!-- Left form panel -->
        <div class="reg-right">
            <div class="reg-card">

                <div class="reg-avatar">
                    <i class="bi bi-person-plus-fill"></i>
                </div>

                <h1 class="reg-h1">Create account</h1>
                <p class="reg-sub">Join thousands of portfolio creators</p>

                <?php if (!empty($errors)): ?>
                    <div class="reg-errors">
                        <ul>
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>

                    <div class="rf-group">
                        <label class="rf-label" for="rf-name">Name</label>
                        <div class="rf-input-wrap">
                            <i class="bi bi-person rf-icon"></i>
                            <input id="rf-name"
                                   type="text"
                                   name="name"
                                   class="rf-input"
                                   placeholder="Alex Johnson"
                                   value="<?= htmlspecialchars($name) ?>"
                                   required>
                        </div>
                    </div>

                    <div class="rf-group">
                        <label class="rf-label" for="rf-email">Email</label>
                        <div class="rf-input-wrap">
                            <i class="bi bi-envelope rf-icon"></i>
                            <input id="rf-email"
                                   type="email"
                                   name="email"
                                   class="rf-input"
                                   placeholder="you@email.com"
                                   value="<?= htmlspecialchars($email) ?>"
                                   required>
                        </div>
                    </div>

                    <div class="rf-group">
                        <label class="rf-label" for="rf-pass">Password</label>
                        <div class="rf-input-wrap">
                            <i class="bi bi-lock rf-icon"></i>
                            <input id="rf-pass"
                                   type="password"
                                   name="password"
                                   class="rf-input rf-input-pass"
                                   placeholder="Create a strong password"
                                   minlength="6"
                                   required>
                            <button type="button" class="rf-eye" onclick="togglePass('rf-pass', this)" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="rf-group">
                        <label class="rf-label" for="rf-confirm-pass">Confirm Password</label>
                        <div class="rf-input-wrap">
                            <i class="bi bi-lock rf-icon"></i>
                            <input id="rf-confirm-pass"
                                   type="password"
                                   name="confirm_password"
                                   class="rf-input rf-input-pass"
                                   placeholder="Re-enter your password"
                                   minlength="6"
                                   required>
                            <button type="button" class="rf-eye" onclick="togglePass('rf-confirm-pass', this)" aria-label="Show confirm password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="rf-btn">
                        <i class="bi bi-person-check-fill me-2"></i> Create Account
                    </button>

                </form>

                <div class="rf-divider"><span>or</span></div>

                <p class="rf-login">
                    Already have an account?
                    <a href="login.php">Sign in here</a>
                </p>

            </div>
        </div>

        <!-- Right branding panel -->
        <div class="reg-left">
            <div class="reg-left-inner">
                <span class="reg-eyebrow">Portfolio Builder</span>
                <h2 class="reg-tagline">Your work<br>deserves to<br><em>be seen.</em></h2>
                <p class="reg-desc">Create a stunning portfolio in minutes. No coding needed — just your story, beautifully told.</p>

                <div class="reg-perks">
                    <div class="reg-perk">
                        <div class="perk-icon"><i class="bi bi-grid-1x2-fill"></i></div>
                        <div class="perk-text">5+ professional templates</div>
                    </div>
                    <div class="reg-perk">
                        <div class="perk-icon"><i class="bi bi-link-45deg"></i></div>
                        <div class="perk-text">Custom shareable link</div>
                    </div>
                    <div class="reg-perk">
                        <div class="perk-icon"><i class="bi bi-rocket-takeoff-fill"></i></div>
                        <div class="perk-text">Live in under 5 minutes</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
.reg-wrap {
    min-height: calc(100vh - 72px);
    background: var(--bg);
    display: flex;
    align-items: stretch;
}

.reg-split {
    display: grid;
    grid-template-columns: 1fr 1fr;
    width: 100%;
}

/* ── Left form panel ────────────────── */
.reg-right {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 64px 40px;
    background: var(--bg);
}

.reg-card {
    width: 100%;
    max-width: 400px;
}

.reg-avatar {
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

.reg-h1 {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -.04em;
    color: var(--text-dark);
    margin-bottom: 6px;
}

.reg-sub {
    font-size: 14px;
    color: var(--text-mid);
    margin-bottom: 32px;
}

/* ── Error box ──────────────────────── */
.reg-errors {
    background: #fdecea;
    border: 1px solid #f5c6cb;
    color: #842029;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 20px;
    font-size: 13px;
}

.reg-errors ul {
    margin: 0;
    padding-left: 18px;
}

.reg-errors li { margin-bottom: 2px; }

/* ── Fields ─────────────────────────── */
.rf-group { margin-bottom: 18px; }

.rf-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 7px;
    letter-spacing: -.01em;
}

.rf-input-wrap { position: relative; }

.rf-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 15px;
    pointer-events: none;
}

.rf-input {
    width: 100%;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 12px 14px 12px 40px;
    font-size: 14px;
    color: var(--text-dark);
    font-family: inherit;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}

.rf-input::placeholder { color: var(--text-muted); }

.rf-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(107,140,90,.15);
}

/* ── Eye toggle (ADDED) ─────────────── */
.rf-input-pass { padding-right: 40px; }

.rf-eye {
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

.rf-eye:hover { color: var(--text-dark); }

/* ── Button ─────────────────────────── */
.rf-btn {
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
    margin-top: 8px;
}

.rf-btn:hover {
    background: var(--brand-hover);
    transform: translateY(-1px);
}

.rf-btn:active { transform: translateY(0); }

/* ── Divider ────────────────────────── */
.rf-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 24px 0 20px;
    color: var(--text-muted);
    font-size: 12px;
}

.rf-divider::before,
.rf-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

/* ── Login link ─────────────────────── */
.rf-login {
    text-align: center;
    font-size: 13px;
    color: var(--text-mid);
    margin: 0;
}

.rf-login a {
    color: var(--accent);
    font-weight: 600;
    text-decoration: none;
}

.rf-login a:hover { text-decoration: underline; }

/* ── Right branding panel ───────────── */
.reg-left {
    background: var(--brand-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 64px 56px;
    position: relative;
    overflow: hidden;
}

.reg-left::before {
    content: '';
    position: absolute;
    width: 380px;
    height: 380px;
    border-radius: 50%;
    background: rgba(107,140,90,.13);
    top: -100px;
    left: -100px;
}

.reg-left::after {
    content: '';
    position: absolute;
    width: 260px;
    height: 260px;
    border-radius: 50%;
    background: rgba(107,140,90,.08);
    bottom: -70px;
    right: -70px;
}

.reg-left-inner { position: relative; z-index: 1; max-width: 360px; }

.reg-eyebrow {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 20px;
}

.reg-tagline {
    font-size: 40px;
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -.04em;
    color: #fff;
    margin-bottom: 18px;
}

.reg-tagline em {
    font-style: normal;
    color: var(--accent);
}

.reg-desc {
    font-size: 14px;
    color: rgba(255,255,255,.5);
    line-height: 1.7;
    margin-bottom: 44px;
}

.reg-perks {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.reg-perk {
    display: flex;
    align-items: center;
    gap: 14px;
}

.perk-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    background: rgba(107,140,90,.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent);
    font-size: 15px;
    flex-shrink: 0;
}

.perk-text {
    font-size: 13px;
    color: rgba(255,255,255,.65);
    font-weight: 500;
}

/* ── Responsive ─────────────────────── */
@media (max-width: 860px) {
    .reg-split {
        grid-template-columns: 1fr;
    }
    .reg-left {
        padding: 48px 32px;
        min-height: auto;
        order: -1;
    }
    .reg-tagline { font-size: 30px; }
    .reg-right { padding: 48px 24px; }
}
</style>

<script>
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type    = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type    = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

<?php require 'includes/footer.php'; ?>