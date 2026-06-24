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

function cp_h($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function cp_make_slug(string $name): string
{
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
    return $slug !== '' ? $slug : 'portfolio';
}

function cp_unique_slug(PDO $pdo, string $baseSlug, int $userId): string
{
    $slug = $baseSlug;
    $suffix = 2;
    while (true) {
        $stmt = $pdo->prepare("SELECT user_id FROM profiles WHERE public_slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $owner = $stmt->fetchColumn();
        if ($owner === false || (int) $owner === $userId) {
            return $slug;
        }
        $slug = $baseSlug . '-' . $suffix;
        $suffix++;
    }
}

function cp_app_base_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/nahin/create-portfolio.php');
    $basePath = rtrim(str_replace('\\', '/', dirname($script)), '/');
    return $scheme . '://' . $host . $basePath;
}

$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$profile = $stmt->fetch() ?: [];

$ownerName = trim((string) ($user['name'] ?? 'User'));
$ownerEmail = trim((string) ($user['email'] ?? ''));

if (empty($profile['public_slug'])) {
    $publicSlug = cp_unique_slug($pdo, cp_make_slug($ownerName), $userId);
    $stmt = $pdo->prepare("
        INSERT INTO profiles (user_id, public_slug)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE
            public_slug = VALUES(public_slug)
    ");
    $stmt->execute([$userId, $publicSlug]);
    $profile['public_slug'] = $publicSlug;
} else {
    $currentSlug = (string) $profile['public_slug'];
    $resolvedSlug = cp_unique_slug($pdo, $currentSlug, $userId);
    if ($resolvedSlug !== $currentSlug) {
        $stmt = $pdo->prepare("UPDATE profiles SET public_slug = ? WHERE user_id = ?");
        $stmt->execute([$resolvedSlug, $userId]);
        $profile['public_slug'] = $resolvedSlug;
    }
}

if (!empty($profile['selected_template_id'])) {
    $stmt = $pdo->prepare("SELECT name FROM templates WHERE id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([(int) $profile['selected_template_id']]);
    $selectedTemplateName = $stmt->fetchColumn() ?: '';
    if ($selectedTemplateName === '') {
        $profile['selected_template_id'] = null;
    }
} else {
    $selectedTemplateName = '';
}

$notice = '';
$error = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'template_saved') {
    $notice = 'Template saved.';
}
if (isset($_GET['msg']) && $_GET['msg'] === 'publish_updated') {
    $notice = 'Portfolio publish status updated.';
}
if (isset($_GET['msg']) && $_GET['msg'] === 'choose_template_first') {
    $notice = 'Choose a template before publishing your portfolio.';
}
if (isset($_GET['deleted'])) {
    $notice = 'Project deleted.';
}

$isPublished = !empty($profile['is_published']);
$hasSelectedTemplate = !empty($profile['selected_template_id']);
$hasPublicPortfolio = $hasSelectedTemplate && $isPublished;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_publish') {
    if (!$isPublished && !$hasSelectedTemplate) {
        header('Location: create-portfolio.php?msg=choose_template_first');
        exit;
    }

    $newState = $isPublished ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE profiles SET is_published = ? WHERE user_id = ?");
    $stmt->execute([$newState, $userId]);
    header('Location: create-portfolio.php?msg=publish_updated');
    exit;
}

if (isset($_GET['delete'])) {
    $projectId = (int) $_GET['delete'];
    $stmt = $pdo->prepare("SELECT id, image_path FROM projects WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$projectId, $userId]);
    $project = $stmt->fetch();
    if ($project) {
        if (!empty($project['image_path'])) {
            $imagePath = __DIR__ . '/' . ltrim((string) $project['image_path'], '/');
            if (is_file($imagePath)) {
                @unlink($imagePath);
            }
        }
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$projectId, $userId]);
    }
    header('Location: create-portfolio.php?deleted=1');
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ?");
$stmt->execute([$userId]);
$projectCount = (int) $stmt->fetchColumn();

$shareUrl = cp_app_base_url() . '/view-portfolio.php?u=' . rawurlencode((string) $profile['public_slug']);
$templateUrl = $hasSelectedTemplate
    ? 'view-portfolio.php?u=' . rawurlencode((string) $profile['public_slug'])
    : 'choose-template.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Portfolio</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root{--bg:#f7f3ea;--surface:#fff;--surface-2:#ede7d6;--line:#e8e1d3;--text:#2b2926;--muted:#8a8270;--primary:#36402c;--primary-soft:#46532f;--accent:#6b8c5a;--good:#5c8a3a;--danger:#a8442f;--radius:14px;--shadow:0 8px 24px rgba(43,41,38,.07);font-family:"Inter",system-ui,-apple-system,Roboto,Arial,sans-serif}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--bg);color:var(--text);min-height:100vh}
    a{text-decoration:none;color:inherit}
    .app{display:grid;grid-template-columns:260px 1fr;min-height:100vh}
    .sidebar{background:var(--surface);border-right:1px solid var(--line);padding:24px 18px;display:flex;flex-direction:column;gap:8px;position:sticky;top:0;height:100vh}
    .brand{display:flex;align-items:center;gap:12px;padding:6px 8px 22px}
    .brand .logo{width:40px;height:40px;border-radius:11px;background:var(--primary);display:grid;place-items:center;font-weight:800;font-size:18px;color:#fff;flex-shrink:0}
    .brand h1{font-size:16px;line-height:1.2;font-weight:800;margin:0}
    .brand span{font-size:11px;color:var(--muted);letter-spacing:.5px}
    .nav{display:flex;flex-direction:column;gap:4px;margin-top:6px}
    .nav-label{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);padding:14px 12px 6px}
    .nav a{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:10px;color:var(--muted);font-size:14px;font-weight:500}
    .nav a.active,.nav a:hover{background:var(--surface-2);color:var(--text)}
    .logout{margin-top:auto}
    .logout a{display:flex;align-items:center;justify-content:center;padding:12px;border-radius:10px;font-weight:700;font-size:14px;background:rgba(255,99,99,.12);color:#9c403f;border:1px solid rgba(255,99,99,.25)}
    .main{padding:28px 34px;overflow-x:hidden}
    .topbar{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:26px}
    .topbar h2{font-size:22px}
    .topbar p{font-size:13px;color:var(--muted);margin-top:2px}
    .profile{display:flex;align-items:center;gap:12px}
    .profile .avatar{width:42px;height:42px;border-radius:50%;background:var(--primary-soft);display:grid;place-items:center;font-weight:700;color:#fff}
    .profile .who{text-align:right}
    .profile .who b{font-size:14px;display:block}
    .profile .who small{font-size:12px;color:var(--muted)}
    .panel{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:24px;margin-bottom:24px;box-shadow:var(--shadow)}
    .panel h3{font-size:16px;font-weight:800;margin-bottom:16px}
    .notice,.error{border-radius:12px;padding:12px 16px;font-size:14px;margin-bottom:20px}
    .notice{background:rgba(92,138,58,.12);border:1px solid rgba(92,138,58,.3);color:var(--good)}
    .error{background:rgba(255,99,99,.12);border:1px solid rgba(255,99,99,.3);color:#9c403f}
    .meta-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
    .meta{background:var(--surface-2);border:1px solid var(--line);border-radius:12px;padding:16px}
    .meta span{display:block;font-size:12px;color:var(--muted);margin-bottom:6px}
    .meta b{font-size:18px}
    .share{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
    .share input{flex:1;min-width:220px;background:var(--surface-2);border:1px solid var(--line);color:var(--text);padding:12px 14px;border-radius:10px;font-size:14px}
    .btn{cursor:pointer;border:none;font-weight:700;font-size:14px;padding:12px 18px;border-radius:11px;display:inline-grid;place-items:center}
    .btn-primary{background:var(--primary);color:#fff}
    .btn-ghost{background:transparent;border:1px solid var(--line);color:var(--text)}
    .btn-ghost:hover{border-color:var(--primary)}
    .hero{background:linear-gradient(135deg,#eef2e4 0%,#faf8f1 100%);border:1px solid var(--line);border-radius:20px;padding:24px 26px;margin-bottom:24px;position:relative;overflow:hidden;box-shadow:var(--shadow)}
    .hero:after{content:'';position:absolute;right:-50px;top:-50px;width:180px;height:180px;border-radius:50%;background:rgba(125,158,88,.12);pointer-events:none}
    .hero h3{font-size:20px;font-weight:800;margin-bottom:8px;position:relative;z-index:1}
    .hero p{font-size:14px;color:var(--muted);line-height:1.6;max-width:820px;position:relative;z-index:1}
    .hero .cta-row{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px;position:relative;z-index:1}
    .hero .cta{display:inline-flex;align-items:center;gap:8px;padding:11px 16px;border-radius:10px;font-size:13px;font-weight:700;border:1px solid var(--line);background:var(--surface);color:var(--text)}
    .hero .cta.primary{background:var(--primary);border-color:var(--primary);color:#fff}
    .hero .cta.primary:hover{background:var(--primary-soft)}
    .actions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:24px}
    .action-card{background:var(--surface);border:1px solid var(--line);border-radius:16px;padding:18px;box-shadow:var(--shadow);display:flex;flex-direction:column;gap:10px;min-height:132px}
    .action-card:hover{transform:translateY(-1px);border-color:#d7cfbe}
    .action-card .ac-icon{width:42px;height:42px;border-radius:11px;background:#edf1e4;display:grid;place-items:center;font-size:20px}
    .action-card h4{font-size:14px;font-weight:800;margin:0}
    .action-card p{font-size:12px;color:var(--muted);line-height:1.5;flex:1}
    .action-card .go{font-size:12px;font-weight:800;color:var(--accent)}
    .empty{color:var(--muted);font-size:14px;text-align:center;padding:24px}
    .tpl-note{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;font-size:14px}
    .tpl-note b{color:var(--primary-soft)}
    @media(max-width:980px){.actions{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:760px){.app{grid-template-columns:1fr}.sidebar{position:fixed;left:-280px;z-index:50;transition:.25s;width:260px}.sidebar.open{left:0}.main{padding:20px}.meta-grid{grid-template-columns:1fr}.actions{grid-template-columns:1fr}.hero{padding:20px}}
</style>
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="logo">P</div>
            <div>
                <h1>PortfolioBuilder</h1>
                <span>BUILD - SHOWCASE - GET HIRED</span>
            </div>
        </div>
        <nav class="nav">
            <div class="nav-label">Menu</div>
            <a href="dashboard.php"><span class="ic">🏠</span> Dashboard</a>
            <a href="edit-portfolio.php"><span class="ic">👤</span> Edit Profile</a>
            <a href="choose-template.php"><span class="ic">🎨</span> Choose Template</a>
            <a href="create-portfolio.php" class="active"><span class="ic">📁</span> Create Portfolio</a>
            <a href="../samina/resume_module/resume_preview.php"><span class="ic">📄</span> Create Resume</a>
            <a href="../job-match-ai/job-match.php"><span class="ic">📊</span> Job Match</a>
        </nav>
        <div class="logout">
            <a href="logout.php"><span>⏻</span> Logout</a>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <h2>Create Portfolio</h2>
                <p>Publish your public link, choose a template, and keep the live page ready.</p>
            </div>
            <div class="profile">
                <div class="who">
                    <b><?= cp_h($ownerName) ?></b>
                    <small><?= cp_h($ownerEmail) ?></small>
                </div>
                <div class="avatar"><?= cp_h(strtoupper(substr($ownerName, 0, 1))) ?></div>
            </div>
        </div>

        <?php if ($notice): ?><div class="notice"><?= cp_h($notice) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?= cp_h($error) ?></div><?php endif; ?>

        <section class="hero">
            <h3>Your portfolio publishing hub</h3>
            <p>Use this page to copy your public URL, switch templates, and jump back into your main editor when you need to update content.</p>
            <div class="cta-row">
                <a class="cta primary" href="edit-portfolio.php">Edit Portfolio</a>
                <a class="cta" href="choose-template.php">Choose Template</a>
                <?php if ($hasSelectedTemplate): ?>
                    <a class="cta" href="<?= cp_h($shareUrl) ?>" target="_blank" rel="noopener">Open Live Portfolio</a>
                <?php endif; ?>
            </div>
        </section>

        <section class="panel">
            <h3>Public Link</h3>
            <div class="share">
                <input type="text" readonly value="<?= cp_h($shareUrl) ?>">
                <button class="btn btn-ghost" type="button" onclick="navigator.clipboard.writeText(this.parentElement.querySelector('input').value)">Copy</button>
                <?php if ($hasPublicPortfolio): ?>
                    <a class="btn btn-primary" href="<?= cp_h($shareUrl) ?>" target="_blank" rel="noopener">View Portfolio</a>
                <?php elseif ($hasSelectedTemplate): ?>
                    <a class="btn btn-primary" href="#publishing">Publish Portfolio</a>
                <?php else: ?>
                    <a class="btn btn-primary" href="choose-template.php">Choose Template</a>
                <?php endif; ?>
            </div>
        </section>

        <section class="panel">
            <h3>Portfolio Status</h3>
            <div class="meta-grid">
                <div class="meta">
                    <span>Public slug</span>
                    <b><?= cp_h($profile['public_slug']) ?></b>
                </div>
                <div class="meta">
                    <span>Template</span>
                    <b><?= $selectedTemplateName !== '' ? cp_h($selectedTemplateName) : 'Not selected' ?></b>
                </div>
                <div class="meta">
                    <span>Projects</span>
                    <b><?= (int) $projectCount ?></b>
                </div>
            </div>
            <?php if (!$hasSelectedTemplate): ?>
                <p class="empty" style="margin-top:14px;">Your portfolio is not published yet. Select a template first.</p>
            <?php elseif (!$isPublished): ?>
                <p class="empty" style="margin-top:14px;">Your portfolio is saved, but the public link is currently unpublished.</p>
            <?php endif; ?>
        </section>

        <section class="panel">
            <div class="tpl-note">
                <div>Template link: <b><?= $hasPublicPortfolio ? cp_h($templateUrl) : ($hasSelectedTemplate ? 'Publish portfolio to activate link' : 'Choose a template to publish') ?></b></div>
                <a class="btn btn-ghost" href="choose-template.php">Change Template</a>
            </div>
        </section>

        <section class="panel" id="publishing">
            <h3>Publishing</h3>
            <div class="tpl-note">
                <div>Status: <b><?= $isPublished ? 'Published' : 'Unpublished' ?></b></div>
                <?php if ($isPublished || $hasSelectedTemplate): ?>
                    <form method="post" style="margin-left:auto;">
                        <input type="hidden" name="action" value="toggle_publish">
                        <button type="submit" class="btn btn-primary"><?= $isPublished ? 'Unpublish Portfolio' : 'Publish Portfolio' ?></button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-primary" href="choose-template.php">Choose Template to Publish</a>
                <?php endif; ?>
            </div>
        </section>

        <section class="actions">
            <a href="edit-portfolio.php" class="action-card">
                <div class="ac-icon">👤</div>
                <h4>Edit Portfolio</h4>
                <p>Update your profile, projects, skills, and contact information from one place.</p>
                <span class="go">Go →</span>
            </a>
            <a href="choose-template.php" class="action-card">
                <div class="ac-icon">🎨</div>
                <h4>Choose Template</h4>
                <p>Switch to a new layout whenever you want to refresh the look of your public page.</p>
                <span class="go">Go →</span>
            </a>
            <a href="../samina/resume_module/resume_preview.php" class="action-card">
                <div class="ac-icon">📄</div>
                <h4>Create Resume</h4>
                <p>Open the resume builder to turn the same profile data into a clean PDF-ready CV.</p>
                <span class="go">Go →</span>
            </a>
        </section>
    </main>
</div>
</body>
</html>
