<?php
require 'config/db.php';

$errors = [];
$success = false;
$name = '';
$email = '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') {
        $errors[] = 'Please enter your name.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($subject === '') {
        $errors[] = 'Please enter a subject.';
    }

    if ($message === '') {
        $errors[] = 'Please enter your message.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$name, $email, $subject, $message]);

            $success = true;
            $name = '';
            $email = '';
            $subject = '';
            $message = '';
        } catch (PDOException $e) {
            $errors[] = 'Message could not be sent. Please try again later.';
        }
    }
}

include 'includes/header.php';
?>

<section class="contact-wrap">
    <div class="contact-grid">

        <!-- Left panel -->
        <div class="contact-left">
            <span class="contact-eyebrow">Get in touch</span>
            <h1 class="contact-h1">We'd love to<br><em>hear from you.</em></h1>
            <p class="contact-sub">Drop us a message and we'll get back to you within 24 hours.</p>

            <div class="contact-info-list">

                <div class="contact-info-item">
                    <div class="info-icon"><i class="bi bi-envelope-fill"></i></div>
                    <div>
                        <div class="info-label">Email</div>
                        <div class="info-val">hello@portfoliobuilder.io</div>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <div>
                        <div class="info-label">Location</div>
                        <div class="info-val">Remote &mdash; Worldwide</div>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="info-icon"><i class="bi bi-clock-fill"></i></div>
                    <div>
                        <div class="info-label">Response time</div>
                        <div class="info-val">Within 24 hours</div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right panel — form card -->
        <div class="contact-right">
            <div class="contact-card">

                <p class="form-section-label">Send a message</p>

                <?php if ($success): ?>
                    <div class="contact-alert contact-success">Message sent successfully.</div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="contact-alert contact-error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="contact.php" method="POST" novalidate>

                    <div class="cf-row">
                        <div class="cf-group">
                            <label class="cf-label" for="cf-name">Full Name</label>
                            <input id="cf-name"
                                type="text"
                                name="name"
                                class="cf-input"
                                placeholder="Alex Johnson"
                                value="<?= htmlspecialchars($name) ?>"
                                required>
                        </div>

                        <div class="cf-group">
                            <label class="cf-label" for="cf-email">Email Address</label>
                            <input id="cf-email"
                                type="email"
                                name="email"
                                class="cf-input"
                                placeholder="alex@email.com"
                                value="<?= htmlspecialchars($email) ?>"
                                required>
                        </div>
                    </div>

                    <div class="cf-group">
                        <label class="cf-label" for="cf-subject">Subject</label>
                        <input id="cf-subject"
                            type="text"
                            name="subject"
                            class="cf-input"
                            placeholder="What's this about?"
                            value="<?= htmlspecialchars($subject) ?>"
                            required>
                    </div>

                    <div class="cf-group">
                        <label class="cf-label" for="cf-message">Message</label>
                        <textarea id="cf-message"
                            name="message"
                            rows="5"
                            class="cf-input cf-textarea"
                            placeholder="Tell us what's on your mind..."
                            required><?= htmlspecialchars($message) ?></textarea>
                    </div>

                    <button type="submit" class="cf-btn">
                        <i class="bi bi-send-fill me-2"></i> Send Message
                    </button>

                </form>
            </div>
        </div>

    </div>
</section>

<style>
    .contact-wrap {
        min-height: calc(100vh - 72px);
        background: var(--bg);
        display: flex;
        align-items: center;
        padding: 80px 5%;
    }

    .contact-grid {
        max-width: 1060px;
        width: 100%;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 64px;
        align-items: center;
    }

    .contact-eyebrow {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 16px;
    }

    .contact-h1 {
        font-size: 44px;
        font-weight: 800;
        line-height: 1.08;
        letter-spacing: -.04em;
        color: var(--text-dark);
        margin-bottom: 18px;
    }

    .contact-h1 em {
        font-style: normal;
        color: var(--accent);
    }

    .contact-sub {
        font-size: 15px;
        color: var(--text-mid);
        line-height: 1.65;
        margin-bottom: 44px;
        max-width: 320px;
    }

    .contact-info-list {
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    .contact-info-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--chip-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent);
        font-size: 16px;
        flex-shrink: 0;
    }

    .info-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 2px;
    }

    .info-val {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-dark);
    }

    .contact-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 24px;
        padding: 40px;
    }

    .form-section-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .1em;
        margin-bottom: 28px;
    }

    .contact-alert {
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 13px;
        line-height: 1.5;
    }

    .contact-alert ul {
        margin: 0;
        padding-left: 18px;
    }

    .contact-success {
        background: #e6f4ea;
        border: 1px solid #b7dfc3;
        color: #1e6b34;
    }

    .contact-error {
        background: #fdecea;
        border: 1px solid #f5c6cb;
        color: #842029;
    }

    .cf-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .cf-group {
        margin-bottom: 20px;
    }

    .cf-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 7px;
        letter-spacing: -.01em;
    }

    .cf-input {
        width: 100%;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 14px;
        color: var(--text-dark);
        font-family: inherit;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .cf-input::placeholder {
        color: var(--text-muted);
    }

    .cf-input:focus {
        border-color: var(--accent);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(107, 140, 90, .15);
    }

    .cf-textarea {
        resize: vertical;
        min-height: 120px;
    }

    .cf-btn {
        width: 100%;
        background: var(--brand-dark);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 14px 24px;
        font-size: 14px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        letter-spacing: -.01em;
        transition: background .2s, transform .15s;
        margin-top: 4px;
    }

    .cf-btn:hover {
        background: var(--brand-hover);
        transform: translateY(-1px);
    }

    .cf-btn:active {
        transform: translateY(0);
    }

    @media (max-width: 900px) {
        .contact-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .contact-h1 {
            font-size: 34px;
        }

        .contact-sub {
            max-width: 100%;
        }
    }

    @media (max-width: 560px) {
        .contact-wrap {
            padding: 48px 5%;
        }

        .contact-card {
            padding: 28px 22px;
        }

        .cf-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>
