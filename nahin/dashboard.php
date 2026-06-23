<?php

/**
 * Portfolio Builder — User Dashboard
 * DB: config/db.php (same as edit-portfolio.php)
 * Tables: users, profiles, projects, skills, certifications, achievements, social_links
 */

require 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Pull name/email from users table
$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: login.php');
    exit;
}

$userName = htmlspecialchars($user['name']);

// Pull profile title + avatar from profiles table
$stmt = $pdo->prepare("SELECT title, avatar, bio, location FROM profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch();

$userRole   = htmlspecialchars($profile['title'] ?? 'Member');
$userAvatar = $profile['avatar'] ?? null;
$initials   = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') !== false ? substr($user['name'], strpos($user['name'], ' ') + 1, 1) : ''));

// Live counts from real tables
$stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ?");
$stmt->execute([$userId]);
$projectCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM skills WHERE user_id = ?");
$stmt->execute([$userId]);
$skillCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM certifications WHERE user_id = ?");
$stmt->execute([$userId]);
$certCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM achievements WHERE user_id = ?");
$stmt->execute([$userId]);
$achievementCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ? AND (demo_url <> '' OR github_url <> '')");
$stmt->execute([$userId]);
$hasLinks = (int) $stmt->fetchColumn() > 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM social_links WHERE user_id = ?");
$stmt->execute([$userId]);
$hasSocial = (int) $stmt->fetchColumn() > 0;

$hasAvatar    = !empty($userAvatar);
$profileFilled = !empty($profile['bio']) && !empty($profile['location']);

$stats = [
    ['label' => 'Projects',       'value' => $projectCount,    'icon' => '📁'],
    ['label' => 'Skills',         'value' => $skillCount,       'icon' => '💡'],
    ['label' => 'Certifications', 'value' => $certCount,        'icon' => '🏆'],
    ['label' => 'Achievements',   'value' => $achievementCount, 'icon' => '🎯'],
];

$jobMatches = [
    ['role' => 'AI Engineer',       'percent' => 92],
    ['role' => 'Software Engineer', 'percent' => 78],
    ['role' => 'Developer',         'percent' => 64],
    ['role' => 'UI/UX Designer',    'percent' => 55],
];

$checklist = [
    ['task' => 'Complete your profile details',  'done' => $profileFilled],
    ['task' => 'Add at least one skill',         'done' => $skillCount > 0],
    ['task' => 'Create your first project',      'done' => $projectCount > 0],
    ['task' => 'Upload a profile photo',         'done' => $hasAvatar],
    ['task' => 'Add GitHub / live demo links',   'done' => $hasLinks],
    ['task' => 'Add social links',               'done' => $hasSocial],
];
$doneCount   = count(array_filter($checklist, fn($c) => $c['done']));
$totalTasks  = count($checklist);
$completePct = $totalTasks ? (int) round($doneCount / $totalTasks * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — PortfolioBuilder</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f4f1ea;
            --surface: #fff;
            --surface-2: #efeade;
            --line: #e4ddcc;
            --text: #1d211a;
            --muted: #797f6f;
            --primary: #3a4a23;
            --primary-soft: #5c7038;
            --accent: #7d9e58;
            --good: #5c8a3a;
            --radius: 14px;
            --shadow: 0 8px 24px rgba(40, 45, 30, .07);
            font-family: "Plus Jakarta Sans", "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        body {
            background: var(--bg);
            color: var(--text);
            min-height: 100vh
        }

        a {
            text-decoration: none;
            color: inherit
        }

        .app {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            background: var(--surface);
            border-right: 1px solid var(--line);
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
            background: var(--primary);
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
            margin: 0
        }

        .brand h1 .g {
            color: var(--accent)
        }

        .brand span {
            font-size: 11px;
            color: var(--muted);
            letter-spacing: .5px
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 6px
        }

        .nav-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            padding: 14px 12px 6px;
        }

        .nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border-radius: 10px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 600;
            transition: .18s;
        }

        .nav a .ic {
            width: 20px;
            text-align: center;
            font-size: 16px
        }

        .nav a:hover {
            background: var(--surface-2);
            color: var(--text)
        }

        .nav a.active {
            background: var(--primary);
            color: #fff
        }

        .logout {
            margin-top: auto
        }

        .logout a {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            background: #f3e3df;
            color: #a8442f;
            border: 1px solid #ecc9c1;
            transition: .18s;
        }

        .logout a:hover {
            background: #eed6d0
        }

        /* ── Main ── */
        .main {
            padding: 28px 34px;
            overflow-x: hidden
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 26px;
        }

        .topbar h2 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -.3px
        }

        .topbar p {
            font-size: 13px;
            color: var(--muted);
            margin-top: 2px
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 12px
        }

        .profile .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--primary);
            display: grid;
            place-items: center;
            font-weight: 700;
            color: #fff;
            overflow: hidden;
            flex-shrink: 0;
        }

        .profile .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .profile .who {
            text-align: right
        }

        .profile .who b {
            font-size: 14px;
            display: block
        }

        .profile .who small {
            font-size: 12px;
            color: var(--muted)
        }

        /* ── Hero ── */
        .hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(120deg, #3a4a23, #4e6230 55%, #6b8540);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 36px 38px;
            margin-bottom: 28px;
            box-shadow: var(--shadow);
        }

        .hero::after {
            content: "";
            position: absolute;
            right: -60px;
            top: -60px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(200, 220, 150, .4), transparent 70%);
        }

        .hero h3 {
            font-size: 26px;
            margin-bottom: 10px;
            color: #f3f5ec;
            font-weight: 800;
            position: relative;
            z-index: 1
        }

        .hero p {
            color: #dde5cd;
            max-width: 560px;
            line-height: 1.6;
            font-size: 15px;
            position: relative;
            z-index: 1
        }

        .hero .cta {
            margin-top: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            color: #3a4a23;
            font-weight: 700;
            font-size: 14px;
            padding: 12px 22px;
            border-radius: 30px;
            transition: .18s;
            position: relative;
            z-index: 1;
        }

        .hero .cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, .18)
        }

        /* ── Stat cards ── */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: var(--shadow);
        }

        .stat .emoji {
            font-size: 24px;
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: #edf1e4;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .stat b {
            font-size: 22px;
            display: block;
            font-weight: 800
        }

        .stat span {
            font-size: 12px;
            color: var(--muted)
        }

        .section-title {
            font-size: 16px;
            font-weight: 800;
            margin: 6px 0 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Quick actions ── */
        .actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 28px
        }

        .action-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: .2s;
            cursor: pointer;
        }

        .action-card:hover {
            transform: translateY(-3px);
            border-color: var(--accent);
            box-shadow: var(--shadow)
        }

        .action-card .ac-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            background: #edf1e4;
            display: grid;
            place-items: center;
            font-size: 20px;
        }

        .action-card h4 {
            font-size: 14px;
            font-weight: 700
        }

        .action-card p {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.4
        }

        .action-card .go {
            font-size: 12px;
            font-weight: 700;
            color: var(--accent);
            margin-top: 4px
        }

        /* ── Completion banner ── */
        .completion-banner {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 20px 26px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: var(--shadow);
            flex-wrap: wrap;
        }

        .completion-ring {
            position: relative;
            width: 64px;
            height: 64px;
            flex-shrink: 0;
        }

        .completion-ring svg {
            transform: rotate(-90deg)
        }

        .completion-ring .ring-bg {
            fill: none;
            stroke: var(--surface-2);
            stroke-width: 6
        }

        .completion-ring .ring-fill {
            fill: none;
            stroke: url(#ringGrad);
            stroke-width: 6;
            stroke-linecap: round
        }

        .completion-ring .ring-label {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
            color: var(--text);
        }

        .completion-text h4 {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 4px
        }

        .completion-text p {
            font-size: 13px;
            color: var(--muted)
        }

        .completion-btn {
            margin-left: auto;
            background: var(--primary);
            color: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
            transition: .18s;
        }

        .completion-btn:hover {
            background: var(--primary-soft);
            transform: translateY(-1px)
        }

        /* ── Two-column layout ── */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px
        }

        /* ── Panel ── */
        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 26px;
            margin-bottom: 0;
            box-shadow: var(--shadow);
        }

        /* ── Job match bars ── */
        .match {
            margin-bottom: 18px
        }

        .match:last-child {
            margin-bottom: 0
        }

        .match .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px
        }

        .match .row b {
            font-weight: 600
        }

        .match .row .pct {
            color: var(--primary);
            font-weight: 700
        }

        .bar {
            height: 10px;
            background: var(--surface-2);
            border-radius: 20px;
            overflow: hidden
        }

        .bar i {
            display: block;
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, var(--primary), var(--accent))
        }

        /* ── Checklist ── */
        .checklist .cl-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            font-size: 14px;
        }

        .checklist .cl-head .pct {
            color: var(--primary);
            font-weight: 700
        }

        .checklist .cl-bar {
            height: 10px;
            background: var(--surface-2);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 22px;
        }

        .checklist .cl-bar i {
            display: block;
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .cl-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 0;
            font-size: 14px;
            border-bottom: 1px solid var(--line);
        }

        .cl-item:last-child {
            border-bottom: none
        }

        .cl-item .tick {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .cl-item .tick.on {
            background: #e7efd9;
            color: var(--good);
            border: 1px solid #cfe0b6
        }

        .cl-item .tick.off {
            background: var(--surface-2);
            color: var(--muted);
            border: 1px solid var(--line)
        }

        .cl-item.done span {
            color: var(--muted);
            text-decoration: line-through
        }

        /* ── Responsive ── */
        .menu-btn {
            display: none
        }

        @media(max-width:980px) {
            .stats {
                grid-template-columns: repeat(2, 1fr)
            }

            .two-col {
                grid-template-columns: 1fr
            }

            .actions {
                grid-template-columns: repeat(2, 1fr)
            }
        }

        @media(max-width:760px) {
            .app {
                grid-template-columns: 1fr
            }

            .sidebar {
                position: fixed;
                left: -280px;
                z-index: 50;
                transition: .25s;
                width: 260px
            }

            .sidebar.open {
                left: 0
            }

            .menu-btn {
                display: grid;
                place-items: center;
                width: 42px;
                height: 42px;
                border-radius: 10px;
                background: var(--surface);
                border: 1px solid var(--line);
                color: var(--text);
                font-size: 20px;
                cursor: pointer;
            }

            .main {
                padding: 20px
            }

            .stats {
                grid-template-columns: 1fr
            }

            .actions {
                grid-template-columns: 1fr
            }

            .profile .who {
                display: none
            }

            .completion-banner {
                flex-direction: column;
                text-align: center
            }

            .completion-btn {
                margin-left: 0
            }
        }
    </style>
</head>

<body>
    <div class="app">

        <!-- ── Sidebar ── -->
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
                <a href="Dashboard.php" class="active"><span class="ic">🏠</span> Dashboard</a>
                <a href="edit-portfolio.php"><span class="ic">👤</span> Edit Profile</a>
                <a href="choose-template.php"><span class="ic">🎨</span> Choose Template</a>
                <a href="create-portfolio.php"><span class="ic">📁</span> Create Portfolio</a>
                <a href="../samina/resume_module/resume_preview.php"><span class="ic">📄</span> Create Resume</a>
                <a href="job-match.php"><span class="ic">📊</span> Job Match</a>
            </nav>
            <div class="logout">
                <a href="Logout.php"><span>⏻</span> Logout</a>
            </div>
        </aside>

        <!-- ── Main ── -->
        <main class="main">

            <!-- Topbar -->
            <div class="topbar">
                <div style="display:flex;align-items:center;gap:14px">
                    <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                    <div>
                        <h2>Dashboard</h2>
                        <p>Welcome back, <?= $userName ?> 👋</p>
                    </div>
                </div>
                <div class="profile">
                    <div class="who"><b><?= $userName ?></b><small><?= $userRole ?></small></div>
                    <div class="avatar">
                        <?php if ($userAvatar): ?>
                            <img src="<?= htmlspecialchars($userAvatar) ?>" alt="<?= $userName ?>">
                        <?php else: ?>
                            <?= $initials ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Hero -->
            <section class="hero">
                <h3>Welcome to PortfolioBuilder</h3>
                <p>Create a portfolio and get an amazing experience. Showcase your work, build a professional resume, and discover which jobs match your skills — all in one place.</p>
                <a href="edit-portfolio.php" class="cta">＋ Start Building Your Portfolio</a>
            </section>

            <!-- Stats -->
            <section class="stats">
                <?php foreach ($stats as $s): ?>
                    <div class="stat">
                        <div class="emoji"><?= $s['icon'] ?></div>
                        <div><b><?= $s['value'] ?></b><span><?= $s['label'] ?></span></div>
                    </div>
                <?php endforeach; ?>
            </section>

            <!-- Quick actions -->
            <h3 class="section-title">⚡ Quick Actions</h3>
            <section class="actions">
                <a href="edit-portfolio.php" class="action-card">
                    <div class="ac-icon">👤</div>
                    <h4>Edit Portfolio</h4>
                    <p>Update your projects, skills, bio, and more.</p>
                    <span class="go">Go →</span>
                </a>
                <a href="choose-template.php" class="action-card">
                    <div class="ac-icon">🎨</div>
                    <h4>Choose Template</h4>
                    <p>Pick a design that fits your personal brand.</p>
                    <span class="go">Go →</span>
                </a>
                <a href="job-match.php" class="action-card">
                    <div class="ac-icon">📊</div>
                    <h4>Job Match</h4>
                    <p>See which roles best match your current skills.</p>
                    <span class="go">Go →</span>
                </a>
            </section>

            <!-- Completion banner -->
            <div class="completion-banner">
                <div class="completion-ring">
                    <svg width="64" height="64" viewBox="0 0 64 64">
                        <defs>
                            <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#3a4a23" />
                                <stop offset="100%" stop-color="#7d9e58" />
                            </linearGradient>
                        </defs>
                        <circle class="ring-bg" cx="32" cy="32" r="26" />
                        <circle class="ring-fill" cx="32" cy="32" r="26"
                            stroke-dasharray="163.4"
                            stroke-dashoffset="<?= 163.4 - (163.4 * $completePct / 100) ?>" />
                    </svg>
                    <div class="ring-label"><?= $completePct ?>%</div>
                </div>
                <div class="completion-text">
                    <h4>Profile <?= $completePct ?>% complete</h4>
                    <p><?= $doneCount ?> of <?= $totalTasks ?> tasks done — complete your profile to attract more employers.</p>
                </div>
                <a href="edit-portfolio.php" class="completion-btn">Complete Profile →</a>
            </div>

            <!-- Two-column: checklist + job match -->
            <div class="two-col">

                <div>
                    <h3 class="section-title">✅ Get Hire-Ready</h3>
                    <section class="panel checklist">
                        <div class="cl-head"><b>Profile completion</b><span class="pct"><?= $completePct ?>% complete</span></div>
                        <div class="cl-bar"><i style="width:<?= $completePct ?>%"></i></div>
                        <?php foreach ($checklist as $c): ?>
                            <div class="cl-item <?= $c['done'] ? 'done' : '' ?>">
                                <span class="tick <?= $c['done'] ? 'on' : 'off' ?>"><?= $c['done'] ? '✓' : '○' ?></span>
                                <span><?= htmlspecialchars($c['task']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </section>
                </div>

                <div>
                    <h3 class="section-title">📊 Jobs Matched to Your Skills</h3>
                    <section class="panel">
                        <?php foreach ($jobMatches as $m): ?>
                            <div class="match">
                                <div class="row">
                                    <b><?= htmlspecialchars($m['role']) ?></b>
                                    <span class="pct"><?= (int)$m['percent'] ?>%</span>
                                </div>
                                <div class="bar"><i style="width:<?= (int)$m['percent'] ?>%"></i></div>
                            </div>
                        <?php endforeach; ?>
                    </section>
                </div>

            </div>

        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ring = document.querySelector('.ring-fill');
            if (ring) {
                var target = ring.getAttribute('stroke-dashoffset');
                ring.style.strokeDashoffset = '163.4';
                requestAnimationFrame(function() {
                    ring.style.transition = 'stroke-dashoffset .8s ease';
                    ring.style.strokeDashoffset = target;
                });
            }
        });
    </script>
</body>

</html>