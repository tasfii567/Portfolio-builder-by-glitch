<?php
require 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, skip straight to dashboard
if (isset($_SESSION['user_id'])) {

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: ../admin/admin_dashboard.php");
    } else {
        header("Location: dashboard.php");
    }

    exit;
}

$errors = [];
$email = '';
$justRegistered = isset($_GET['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = "Please enter both email and password.";
    } else {
        // Look up the account by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }

            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}

require 'includes/header.php';
?>

<section class="login-wrap">
    <div class="login-split">

        <!-- Left branding panel -->
        <div class="login-left">
            <div class="login-left-inner">
                <span class="login-eyebrow">Portfolio Builder</span>
                <h2 class="login-tagline">Build your story.<br><em>Share your work.</em></h2>
                <p class="login-desc">Join thousands of creators who showcase their best work and land their dream opportunities.</p>

                <div class="login-stats">
                    <div class="lstat">
                        <div class="lstat-num">12k+</div>
                        <div class="lstat-lbl">Portfolios live</div>
                    </div>
                    <div class="lstat-divider"></div>
                    <div class="lstat">
                        <div class="lstat-num">98%</div>
                        <div class="lstat-lbl">Satisfaction rate</div>
                    </div>
                    <div class="lstat-divider"></div>
                    <div class="lstat">
                        <div class="lstat-num">5+</div>
                        <div class="lstat-lbl">Templates</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="login-right">
            <div class="login-card">

                <div class="login-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>

                <h1 class="login-h1">Welcome back</h1>
                <p class="login-sub">Sign in to your account</p>

                <?php if ($justRegistered && empty($errors)): ?>
                    <div class="login-success">Registration successful! Please log in.</div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="login-errors">
                        <ul>
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>

                    <div class="lf-group">
                        <label class="lf-label" for="lf-email">Email Address</label>
                        <div class="lf-input-wrap">
                            <i class="bi bi-envelope lf-icon"></i>
                            <input id="lf-email"
                                type="email"
                                name="email"
                                class="lf-input"
                                placeholder="you@email.com"
                                value="<?= htmlspecialchars($email) ?>"
                                required>
                        </div>
                    </div>

                    <div class="lf-group">
                        <div class="lf-label-row">
                            <label class="lf-label" for="lf-pass">Password</label>
                            <a href="#" class="lf-forgot">Forgot password?</a>
                        </div>
                        <div class="lf-input-wrap">
                            <i class="bi bi-lock lf-icon"></i>
                            <input id="lf-pass"
                                type="password"
                                name="password"
                                class="lf-input"
                                placeholder="Enter your password"
                                required>
                        </div>
                    </div>

                    <button type="submit" class="lf-btn">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
                    </button>

                </form>

                <div class="lf-divider"><span>or</span></div>

                <p class="lf-register">
                    Don't have an account?
                    <a href="reg.php">Create one free</a>
                </p>

            </div>
        </div>

    </div>
</section>

<style>
    .login-wrap {
        min-height: calc(100vh - 72px);
        background: var(--bg);
        display: flex;
        align-items: stretch;
    }

    .login-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        width: 100%;
    }

    /* ── Left panel ─────────────────────── */
    .login-left {
        background: var(--brand-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 64px 56px;
        position: relative;
        overflow: hidden;
    }

    .login-left::before {
        content: '';
        position: absolute;
        width: 360px;
        height: 360px;
        border-radius: 50%;
        background: rgba(107, 140, 90, .15);
        top: -80px;
        right: -80px;
    }

    .login-left::after {
        content: '';
        position: absolute;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: rgba(107, 140, 90, .1);
        bottom: -60px;
        left: -60px;
    }

    .login-left-inner {
        position: relative;
        z-index: 1;
        max-width: 360px;
    }

    .login-eyebrow {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 20px;
    }

    .login-tagline {
        font-size: 38px;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -.04em;
        color: #fff;
        margin-bottom: 18px;
    }

    .login-tagline em {
        font-style: normal;
        color: var(--accent);
    }

    .login-desc {
        font-size: 14px;
        color: rgba(255, 255, 255, .55);
        line-height: 1.7;
        margin-bottom: 44px;
    }

    .login-stats {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .lstat-num {
        font-size: 22px;
        font-weight: 800;
        color: #fff;
        letter-spacing: -.03em;
    }

    .lstat-lbl {
        font-size: 11px;
        color: rgba(255, 255, 255, .45);
        margin-top: 2px;
    }

    .lstat-divider {
        width: 1px;
        height: 36px;
        background: rgba(255, 255, 255, .12);
    }

    /* ── Right panel ────────────────────── */
    .login-right {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 64px 40px;
        background: var(--bg);
    }

    .login-card {
        width: 100%;
        max-width: 400px;
    }

    .login-avatar {
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

    .login-h1 {
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -.04em;
        color: var(--text-dark);
        margin-bottom: 6px;
    }

    .login-sub {
        font-size: 14px;
        color: var(--text-mid);
        margin-bottom: 32px;
    }

    /* ── Success / error boxes ──────────── */
    .login-success {
        background: #e6f4ea;
        border: 1px solid #b7dfc3;
        color: #1e6b34;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 13px;
    }

    .login-errors {
        background: #fdecea;
        border: 1px solid #f5c6cb;
        color: #842029;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 13px;
    }

    .login-errors ul {
        margin: 0;
        padding-left: 18px;
    }

    .login-errors li {
        margin-bottom: 2px;
    }

    /* ── Fields ─────────────────────────── */
    .lf-group {
        margin-bottom: 18px;
    }

    .lf-label-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 7px;
    }

    .lf-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 7px;
        letter-spacing: -.01em;
    }

    .lf-label-row .lf-label {
        margin-bottom: 0;
    }

    .lf-forgot {
        font-size: 12px;
        color: var(--accent);
        text-decoration: none;
        font-weight: 500;
    }

    .lf-forgot:hover {
        text-decoration: underline;
    }

    .lf-input-wrap {
        position: relative;
    }

    .lf-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 15px;
        pointer-events: none;
    }

    .lf-input {
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

    .lf-input::placeholder {
        color: var(--text-muted);
    }

    .lf-input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(107, 140, 90, .15);
    }

    /* ── Button ─────────────────────────── */
    .lf-btn {
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

    .lf-btn:hover {
        background: var(--brand-hover);
        transform: translateY(-1px);
    }

    .lf-btn:active {
        transform: translateY(0);
    }

    /* ── Divider ────────────────────────── */
    .lf-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 24px 0 20px;
        color: var(--text-muted);
        font-size: 12px;
    }

    .lf-divider::before,
    .lf-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    /* ── Register link ──────────────────── */
    .lf-register {
        text-align: center;
        font-size: 13px;
        color: var(--text-mid);
        margin: 0;
    }

    .lf-register a {
        color: var(--accent);
        font-weight: 600;
        text-decoration: none;
    }

    .lf-register a:hover {
        text-decoration: underline;
    }

    /* ── Responsive ─────────────────────── */
    @media (max-width: 860px) {
        .login-split {
            grid-template-columns: 1fr;
        }

        .login-left {
            padding: 48px 32px;
            min-height: auto;
        }

        .login-tagline {
            font-size: 28px;
        }

        .login-right {
            padding: 48px 24px;
        }
    }
</style>

<?php require 'includes/footer.php'; ?>