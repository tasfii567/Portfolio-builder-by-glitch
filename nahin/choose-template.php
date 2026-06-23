<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'config/db.php';
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: login.php");
    exit;
}

$initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') !== false ? substr($user['name'], strpos($user['name'], ' ') + 1, 1) : ''));

// Same template list as the public demo.php gallery.
// NOTE: every entry currently points at temp1.html as a placeholder —
// swap in real files here as more templates are built.
$templates = [
    ['img' => '01', 'title' => 'Creative Portfolio',    'desc' => 'Modern Creative Agency Design',   'tag' => 'Popular',   'file' => 'temp1.html'],
    ['img' => '02', 'title' => 'Developer Portfolio',   'desc' => 'Clean Tech & Code Showcase',      'tag' => 'Dev',       'file' => 'temp1.html'],
    ['img' => '01', 'title' => 'Photography Portfolio', 'desc' => 'Elegant Visual Portfolio',        'tag' => 'Visual',    'file' => 'temp1.html'],
    ['img' => '02', 'title' => 'Designer Portfolio',    'desc' => 'Bold UI/UX Portfolio Design',     'tag' => 'Design',    'file' => 'temp1.html'],
    ['img' => '01', 'title' => 'Freelancer Portfolio',  'desc' => 'Professional Services Showcase',  'tag' => 'Freelance', 'file' => 'temp1.html'],
    ['img' => '02', 'title' => 'Student Portfolio',     'desc' => 'Academic & Project Highlight',    'tag' => 'Student',   'file' => 'temp1.html'],
    ['img' => '01', 'title' => 'Artist Portfolio',      'desc' => 'Gallery Style Creative Layout',   'tag' => 'Art',       'file' => 'temp1.html'],
    ['img' => '02', 'title' => 'Business Portfolio',    'desc' => 'Corporate Professional Design',   'tag' => 'Business',  'file' => 'temp1.html'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Template — PortfolioBuilder</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f7f3ea;
            --surface: #fff;
            --surface-2: #ede7d6;
            --border: #e8e1d3;
            --text-dark: #2b2926;
            --text-mid: #8a8270;
            --text-mute: #a39c89;
            --brand-dark: #36402c;
            --brand-dark-soft: #46532f;
            --accent: #6b8c5a;
            --good: #6f9a52;
            --radius: 14px;
            --shadow: 0 8px 24px rgba(43, 41, 38, .07);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif !important;
            background: var(--bg);
            color: var(--text-dark);
            min-height: 100vh;
            margin: 0;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .app {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        /* ===== Sidebar (same as edit-portfolio.php) ===== */
        .sidebar {
            background: var(--surface);
            border-right: 1px solid var(--border);
            padding: 24px 18px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 8px 22px;
        }

        .brand .logo {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            background: var(--brand-dark);
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 18px;
            color: #fff;
            flex-shrink: 0;
        }

        .brand h1 {
            font-size: 16px;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: -.02em;
            margin: 0;
        }

        .brand h1 .g {
            color: var(--accent);
        }

        .brand span {
            font-size: 11px;
            color: var(--text-mute);
            letter-spacing: .5px;
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 6px;
        }

        .nav-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-mute);
            padding: 14px 12px 6px;
        }

        .nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border-radius: 10px;
            color: var(--text-mid);
            font-size: 14px;
            font-weight: 500;
            transition: .18s;
        }

        .nav a .ic {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .nav a:hover {
            background: var(--surface-2);
            color: var(--text-dark);
        }

        .nav a.active {
            background: var(--brand-dark);
            color: #fff;
        }

        .logout {
            margin-top: auto;
        }

        .logout a {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            background: #f3e3df;
            color: #a8442f;
            border: 1px solid #ecc9c1;
            transition: .18s;
        }

        .logout a:hover {
            background: #eed6d0;
        }

        /* ===== Main / topbar ===== */
        .main {
            padding: 28px 34px;
            overflow-x: hidden;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .topbar h2 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -.02em;
            margin: 0;
            color: var(--text-dark);
        }

        .topbar p {
            font-size: 13px;
            color: var(--text-mid);
            margin: 2px 0 0;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--brand-dark);
            display: grid;
            place-items: center;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
            font-size: 14px;
        }

        .profile .who {
            text-align: right;
        }

        .profile .who b {
            font-size: 14px;
            display: block;
            color: var(--text-dark);
        }

        .profile .who small {
            font-size: 12px;
            color: var(--text-mid);
        }

        .menu-btn {
            display: none;
        }

        @media (max-width: 760px) {
            .app {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: fixed;
                left: -280px;
                z-index: 50;
                transition: .25s;
                width: 260px;
            }

            .sidebar.open {
                left: 0;
            }

            .menu-btn {
                display: grid;
                place-items: center;
                width: 42px;
                height: 42px;
                border-radius: 10px;
                background: var(--surface);
                border: 1px solid var(--border);
                color: var(--text-dark);
                font-size: 20px;
                cursor: pointer;
            }

            .main {
                padding: 20px;
            }

            .profile .who {
                display: none;
            }
        }

        /* ===== Template gallery (same design language as demo.php) ===== */
        .demo-wrap {
            padding: 10px 0 40px;
        }

        .demo-head {
            margin-bottom: 36px;
        }

        .demo-eyebrow {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 10px;
        }

        .demo-sub {
            font-size: 14px;
            color: var(--text-mid);
            max-width: 460px;
            line-height: 1.6;
            margin: 0;
        }

        .demo-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .demo-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            transition: transform .25s, box-shadow .25s;
        }

        .demo-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow);
        }

        .demo-img-wrap {
            position: relative;
            overflow: hidden;
            height: 190px;
        }

        .demo-iframe-wrap {
            width: 100%;
            height: 190px;
            overflow: hidden;
            position: relative;
            background: var(--surface-2);
        }

        .demo-iframe {
            width: 1280px;
            height: 900px;
            border: none;
            display: block;
            transform: scale(0.21);
            transform-origin: top left;
            pointer-events: none;
            transition: transform .4s;
        }

        .demo-iframe-block {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        .demo-card:hover .demo-iframe {
            transform: scale(0.21) translateY(-12px);
        }

        .demo-overlay {
            position: absolute;
            inset: 0;
            background: rgba(30, 26, 22, .45);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            z-index: 3;
            transition: opacity .3s;
        }

        .demo-card:hover .demo-overlay {
            opacity: 1;
        }

        .demo-preview-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #fff;
            color: var(--text-dark);
            border: none;
            cursor: pointer;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            transition: background .2s, transform .15s;
        }

        .demo-preview-btn:hover {
            background: var(--surface-2);
            transform: scale(1.04);
        }

        .demo-tag {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 4;
            background: var(--brand-dark);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .demo-card-body {
            padding: 16px 18px 18px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .demo-card-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -.02em;
            margin: 0;
        }

        .demo-card-desc {
            font-size: 13px;
            color: var(--text-mid);
            line-height: 1.5;
            margin: 0 0 8px;
        }

        .demo-use-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: fit-content;
            background: var(--brand-dark);
            border: 1.5px solid var(--brand-dark);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            letter-spacing: -.01em;
            transition: background .2s, transform .15s;
        }

        .demo-use-btn:hover {
            background: var(--brand-dark-soft);
            transform: translateY(-1px);
        }

        @media (max-width: 1100px) {
            .demo-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 780px) {
            .demo-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 500px) {
            .demo-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ===== Preview modal (same as demo.php) ===== */
        .tpl-modal-bg {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, .6);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .tpl-modal-bg.active {
            display: flex;
            animation: tplFadeIn .2s ease;
        }

        @keyframes tplFadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .tpl-modal {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 1100px;
            height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0, 0, 0, .25);
            animation: tplSlideUp .25s cubic-bezier(.4, 0, .2, 1);
        }

        @keyframes tplSlideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .tpl-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            gap: 12px;
            flex-shrink: 0;
        }

        .tpl-modal-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }

        .tpl-modal-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-dark);
            white-space: nowrap;
        }

        .tpl-modal-url {
            font-size: 12px;
            font-family: monospace;
            color: #888;
            background: var(--surface-2);
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid var(--border);
            white-space: nowrap;
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tpl-modal-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .tpl-action-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: none;
            color: #666;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s, color .15s;
        }

        .tpl-action-btn:hover {
            background: var(--surface-2);
            color: var(--text-dark);
        }

        .tpl-modal-body {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .tpl-loading {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: #faf9f7;
            font-size: 13px;
            color: #888;
            z-index: 2;
        }

        .tpl-spinner {
            width: 28px;
            height: 28px;
            border: 2.5px solid var(--border);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: tplSpin .7s linear infinite;
        }

        @keyframes tplSpin {
            to {
                transform: rotate(360deg);
            }
        }

        #templateFrame {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        .tpl-modal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }

        .tpl-footer-note {
            font-size: 12px;
            color: #aaa;
        }

        .tpl-use-btn-modal {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--brand-dark);
            border: none;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: 10px;
            cursor: pointer;
            transition: background .2s;
        }

        .tpl-use-btn-modal:hover {
            background: var(--brand-dark-soft);
        }

        @media (max-width: 600px) {
            .tpl-modal {
                height: 95vh;
                border-radius: 12px;
            }

            .tpl-modal-url {
                display: none;
            }

            .tpl-footer-note {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="app">

        <aside class="sidebar" id="sidebar">
            <div class="brand">
                <div class="logo">P</div>
                <div>
                    <h1>Portfolio<span class="g">Builder</span></h1>
                    <span>BUILD · SHOWCASE · GET HIRED</span>
                </div>
            </div>
            <nav class="nav">
                <div class="nav-label">Menu</div>
                <a href="Dashboard.php"><span class="ic">🏠</span> Dashboard</a>
                <a href="edit-portfolio.php"><span class="ic">👤</span> Edit Portfolio</a>
                <a href="choose-template.php" class="active"><span class="ic">🎨</span> Choose Template</a>
                <a href="create-resume.php"><span class="ic">📄</span> Create Resume</a>
                <a href="job-match.php"><span class="ic">📊</span> Job Match</a>
            </nav>
            <div class="logout">
                <a href="Logout.php"><span>⏻</span> Logout</a>
            </div>
        </aside>

        <main class="main">
            <div class="topbar">
                <div style="display:flex;align-items:center;gap:14px">
                    <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                    <div>
                        <h2>Choose Template</h2>
                        <p>Pick a design — we'll build your portfolio with your saved info.</p>
                    </div>
                </div>
                <div class="profile">
                    <div class="who"><b><?= htmlspecialchars($user['name']) ?></b><small><?= htmlspecialchars($user['email']) ?></small></div>
                    <div class="avatar"><?= htmlspecialchars($initials) ?></div>
                </div>
            </div>

            <section class="demo-wrap">
                <div class="demo-head">
                    <span class="demo-eyebrow">Templates</span>
                    <p class="demo-sub">Preview any design, then hit "Use this template" to apply it to your portfolio.</p>
                </div>

                <div class="demo-grid">
                    <?php foreach ($templates as $t): ?>
                        <div class="demo-card">

                            <div class="demo-img-wrap">
                                <div class="demo-iframe-wrap">
                                    <iframe
                                        src="<?= htmlspecialchars($t['file']) ?>"
                                        class="demo-iframe"
                                        scrolling="no"
                                        tabindex="-1"
                                        loading="lazy"
                                        title="<?= htmlspecialchars($t['title']) ?> preview">
                                    </iframe>
                                    <div class="demo-iframe-block"></div>
                                </div>
                                <div class="demo-overlay">
                                    <button class="demo-preview-btn" onclick="openPreview('<?= htmlspecialchars($t['file']) ?>', '<?= htmlspecialchars($t['title']) ?>')">
                                        <i class="bi bi-eye"></i> See Template
                                    </button>
                                </div>
                                <span class="demo-tag"><?= htmlspecialchars($t['tag']) ?></span>
                            </div>

                            <div class="demo-card-body">
                                <h3 class="demo-card-title"><?= htmlspecialchars($t['title']) ?></h3>
                                <p class="demo-card-desc"><?= htmlspecialchars($t['desc']) ?></p>
                                <button class="demo-use-btn" onclick="useTemplate('<?= htmlspecialchars($t['file']) ?>')">
                                    Use this template →
                                </button>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>

    <!-- ═══ TEMPLATE PREVIEW MODAL ═══ -->
    <div id="templateModal" class="tpl-modal-bg" onclick="handleModalBgClick(event)">
        <div class="tpl-modal">

            <div class="tpl-modal-header">
                <div class="tpl-modal-meta">
                    <span class="tpl-modal-title" id="modalTitle">Preview</span>
                    <span class="tpl-modal-url" id="modalUrl">temp1.html</span>
                </div>
                <div class="tpl-modal-actions">
                    <a id="modalOpenLink" href="#" target="_blank" class="tpl-action-btn" title="Open in new tab">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    <button class="tpl-action-btn" onclick="closePreview()" title="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="tpl-modal-body">
                <div class="tpl-loading" id="modalLoading">
                    <div class="tpl-spinner"></div>
                    <span>Loading preview…</span>
                </div>
                <iframe id="templateFrame" title="Template preview" onload="hideLoading()"></iframe>
            </div>

            <div class="tpl-modal-footer">
                <span class="tpl-footer-note">This is a live preview of the template</span>
                <button type="button" id="modalUseBtn" class="tpl-use-btn-modal" onclick="useTemplate(currentTemplateFile)">Use this template →</button>
            </div>

        </div>
    </div>

    <!-- Hidden form that actually applies the chosen template to the user's portfolio -->
    <form id="useTemplateForm" method="POST" action="apply-template.php" style="display:none;">
        <input type="hidden" name="template" id="useTemplateInput" value="">
    </form>

    <script>
        let currentTemplateFile = '';

        function openPreview(file, title) {
            currentTemplateFile = file;

            const modal = document.getElementById('templateModal');
            const frame = document.getElementById('templateFrame');
            const loader = document.getElementById('modalLoading');
            const mTitle = document.getElementById('modalTitle');
            const mUrl = document.getElementById('modalUrl');
            const mLink = document.getElementById('modalOpenLink');

            mTitle.textContent = title;
            mUrl.textContent = file;
            mLink.href = file;

            loader.style.display = 'flex';
            frame.src = '';
            frame.src = file;

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closePreview() {
            const modal = document.getElementById('templateModal');
            const frame = document.getElementById('templateFrame');
            modal.classList.remove('active');
            frame.src = '';
            document.body.style.overflow = '';
        }

        function hideLoading() {
            document.getElementById('modalLoading').style.display = 'none';
        }

        function handleModalBgClick(e) {
            if (e.target === document.getElementById('templateModal')) closePreview();
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closePreview();
        });

        // Applies the chosen template to the logged-in user's portfolio
        function useTemplate(file) {
            if (!file) return;
            document.getElementById('useTemplateInput').value = file;
            document.getElementById('useTemplateForm').submit();
        }
    </script>

</body>

</html>