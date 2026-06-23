<?php

/**
 * Responsive Portfolio Builder — User Dashboard
 * -------------------------------------------------
 */

require 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Auth guard: real session check (no demo auto-login) ---------------
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Pull name/email from users table (set by login.php)
$userName = htmlspecialchars($_SESSION['name'] ?? 'User');

// Pull profile title (acts as "role") and avatar from profiles table
$stmt = $pdo->prepare("SELECT title, avatar FROM profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch();

$userRole = htmlspecialchars($profile['title'] ?? 'Member');
$userAvatar = $profile['avatar'] ?? null;

$initials = strtoupper(substr($userName, 0, 1) . (strpos($userName, ' ') !== false ? substr($userName, strpos($userName, ' ') + 1, 1) : ''));

// --- Demo data (replace with real DB queries later) --------------------
// Job-match percentages based on the user's skills.
$jobMatches = [
    ['role' => 'Frontend Developer',  'percent' => 92],
    ['role' => 'UI/UX Designer',      'percent' => 78],
    ['role' => 'Full-Stack Engineer', 'percent' => 64],
    ['role' => 'WordPress Developer',  'percent' => 55],
];

// Quick stats for the cards row.
$stats = [
    ['label' => 'Portfolios',  'value' => 2,   'icon' => '📁'],
    ['label' => 'Resumes',     'value' => 1,   'icon' => '📄'],
    ['label' => 'Profile',     'value' => '85%', 'icon' => '👤'],
    ['label' => 'Templates',   'value' => 6,   'icon' => '🎨'],
];

// The user's portfolios (replace with a DB query, e.g. SELECT * FROM portfolios WHERE user_id = ?).
$portfolios = [
    ['title' => 'My Developer Portfolio', 'template' => 'Modern Dark', 'status' => 'Published', 'updated' => '2 days ago', 'views' => 134, 'icon' => '💼'],
    ['title' => 'UI/UX Case Studies',     'template' => 'Minimal',     'status' => 'Draft',     'updated' => '5 days ago', 'views' => 0,   'icon' => '🎨'],
];

// Profile-completion checklist (replace 'done' values with real checks).
$checklist = [
    ['task' => 'Complete your profile details',         'done' => true],
    ['task' => 'Add at least 3 skills',                 'done' => true],
    ['task' => 'Create your first portfolio',           'done' => true],
    ['task' => 'Upload a profile photo',                'done' => !empty($userAvatar)],
    ['task' => 'Publish & share your portfolio link',   'done' => false],
];
$doneCount    = count(array_filter($checklist, fn($c) => $c['done']));
$totalTasks   = count($checklist);
$completePct  = $totalTasks ? (int) round($doneCount / $totalTasks * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Portfolio Builder</title>
    <style>
        :root {
            --bg: #0f1117;
            --surface: #171a23;
            --surface-2: #1f2430;
            --line: #2a3040;
            --text: #e8eaf0;
            --muted: #9aa3b5;
            --primary: #6c5ce7;
            --primary-soft: #8b7cf0;
            --accent: #ff7a59;
            --good: #2ecc71;
            --radius: 16px;
            --shadow: 0 10px 30px rgba(0, 0, 0, .35);
            font-family: "Segoe UI", system-ui, -apple-system, Roboto, Helvetica, Arial, sans-serif;
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

        /* Layout */
        .app {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh
        }

        /* Sidebar */
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
            padding: 6px 8px 22px
        }

        .brand .logo {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 18px;
            color: #fff;
        }

        .brand h1 {
            font-size: 16px;
            line-height: 1.2
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
            padding: 14px 12px 6px
        }

        .nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border-radius: 10px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
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
            background: linear-gradient(135deg, rgba(108, 92, 231, .22), rgba(255, 122, 89, .12));
            color: #fff
        }

        .nav a.active .ic {
            filter: none
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
            font-weight: 600;
            font-size: 14px;
            background: rgba(255, 99, 99, .12);
            color: #ff8585;
            border: 1px solid rgba(255, 99, 99, .25);
            transition: .18s;
        }

        .logout a:hover {
            background: rgba(255, 99, 99, .22)
        }

        /* Main */
        .main {
            padding: 28px 34px;
            overflow-x: hidden
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 26px
        }

        .topbar h2 {
            font-size: 22px
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
            background: linear-gradient(135deg, var(--primary), var(--primary-soft));
            display: grid;
            place-items: center;
            font-weight: 700;
            color: #fff;
            overflow: hidden;
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

        /* Welcome hero */
        .hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(120deg, #241b4a 0%, #3a2a5e 45%, #5a2f4d 100%);
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
            background: radial-gradient(circle, rgba(255, 122, 89, .45), transparent 70%);
        }

        .hero h3 {
            font-size: 26px;
            margin-bottom: 10px
        }

        .hero p {
            color: #cfc8e8;
            max-width: 560px;
            line-height: 1.6;
            font-size: 15px
        }

        .hero .cta {
            margin-top: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            color: #241b4a;
            font-weight: 700;
            font-size: 14px;
            padding: 12px 22px;
            border-radius: 30px;
            transition: .18s;
        }

        .hero .cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, .3)
        }

        /* Stat cards */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px
        }

        .stat {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .stat .emoji {
            font-size: 24px;
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: var(--surface-2);
            display: grid;
            place-items: center
        }

        .stat b {
            font-size: 22px;
            display: block
        }

        .stat span {
            font-size: 12px;
            color: var(--muted)
        }

        /* Section headers */
        .section-title {
            font-size: 16px;
            margin: 6px 0 16px;
            display: flex;
            align-items: center;
            gap: 8px
        }

        /* Action grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 30px
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 24px;
            transition: .2s;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: var(--shadow)
        }

        .card .badge {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 22px;
            margin-bottom: 16px;
            background: linear-gradient(135deg, rgba(108, 92, 231, .25), rgba(255, 122, 89, .18));
        }

        .card h4 {
            font-size: 16px;
            margin-bottom: 6px
        }

        .card p {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.5
        }

        .card .go {
            margin-top: 14px;
            font-size: 13px;
            font-weight: 600;
            color: var(--primary-soft)
        }

        /* Job match panel */
        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 26px;
            margin-bottom: 24px;
        }

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
            color: var(--primary-soft);
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

        /* Your Portfolios */
        .pf-card {
            display: flex;
            flex-direction: column;
            cursor: default
        }

        .pf-card .pf-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px
        }

        .pf-card .badge {
            margin-bottom: 0
        }

        .badge-status {
            font-size: 11px;
            font-weight: 700;
            padding: 5px 11px;
            border-radius: 20px;
            letter-spacing: .3px;
            white-space: nowrap
        }

        .badge-status.published {
            background: rgba(46, 204, 113, .15);
            color: var(--good);
            border: 1px solid rgba(46, 204, 113, .3)
        }

        .badge-status.draft {
            background: rgba(154, 163, 181, .12);
            color: var(--muted);
            border: 1px solid var(--line)
        }

        .pf-card h4 {
            font-size: 16px;
            margin-bottom: 4px
        }

        .pf-card .pf-tmpl {
            font-size: 12px;
            color: var(--muted)
        }

        .pf-card .pf-foot {
            margin-top: auto;
            padding-top: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--line);
            font-size: 13px;
            color: var(--muted)
        }

        .pf-card .pf-foot a {
            color: var(--primary-soft);
            font-weight: 600
        }

        /* Create-new tile */
        .new-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-style: dashed;
            color: var(--muted);
            min-height: 190px;
            gap: 6px
        }

        .new-card:hover {
            color: var(--text);
            border-color: var(--primary)
        }

        .new-card .plus {
            font-size: 36px;
            line-height: 1;
            color: var(--primary-soft)
        }

        .new-card span {
            font-size: 13px
        }

        /* Get hire-ready checklist */
        .checklist .cl-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            font-size: 14px
        }

        .checklist .cl-head .pct {
            color: var(--primary-soft);
            font-weight: 700
        }

        .checklist .cl-bar {
            height: 10px;
            background: var(--surface-2);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 22px
        }

        .checklist .cl-bar i {
            display: block;
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, var(--primary), var(--accent))
        }

        .cl-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 0;
            font-size: 14px;
            border-bottom: 1px solid var(--line)
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
            flex-shrink: 0
        }

        .cl-item .tick.on {
            background: rgba(46, 204, 113, .18);
            color: var(--good);
            border: 1px solid rgba(46, 204, 113, .35)
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

        /* Responsive */
        .menu-btn {
            display: none
        }

        @media(max-width:980px) {
            .stats {
                grid-template-columns: repeat(2, 1fr)
            }

            .grid {
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
                cursor: pointer
            }

            .main {
                padding: 20px
            }

            .grid,
            .stats {
                grid-template-columns: 1fr
            }

            .profile .who {
                display: none
            }
        }
    </style>
</head>

<body>
    <div class="app">

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="brand">
                <div class="logo">P</div>
                <div>
                    <h1>Portfolio Builder</h1>
                    <span>BUILD · SHOWCASE · GET HIRED</span>
                </div>
            </div>

            <nav class="nav">
                <div class="nav-label">Menu</div>
                <a href="dashboard.php" class="active"><span class="ic">🏠</span> Dashboard</a>
                <a href="create-portfolio.php"><span class="ic">📁</span> Create Portfolio</a>
                <a href="edit-portfolio.php"><span class="ic">👤</span> Edit Portfolio</a>
                <a href="create-resume.php"><span class="ic">📄</span> Create Resume</a>
                <a href="job-match.php"><span class="ic">📊</span> Job Match</a>
                <a href="templates.php"><span class="ic">🎨</span> Choose Template</a>
            </nav>
            <div class="logout">
                <a href="logout.php"><span>⏻</span> Logout</a>
            </div>
        </aside>

        <!-- Main -->
        <main class="main">
            <div class="topbar">
                <div style="display:flex;align-items:center;gap:14px">
                    <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                    <div>
                        <h2>Dashboard</h2>
                        <p>Welcome back, <?= $userName ?> 👋</p>
                    </div>
                </div>
                <div class="profile">
                    <div class="who">
                        <b><?= $userName ?></b>
                        <small><?= $userRole ?></small>
                    </div>
                    <div class="avatar">
                        <?php if ($userAvatar): ?>
                            <img src="<?= htmlspecialchars($userAvatar) ?>" alt="<?= $userName ?>">
                        <?php else: ?>
                            <?= $initials ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Welcome hero -->
            <section class="hero">
                <h3>Welcome to our Portfolio Builder</h3>
                <p>Create a portfolio and get an amazing experience. Showcase your work, build a professional resume, and discover which jobs match your skills — all in one place. Create your portfolio now!</p>
                <a href="edit-portfolio.php" class="cta">＋ Edit Your Portfolio Now</a>
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

            <!-- Your Portfolios -->
            <h3 class="section-title">📁 Your Portfolios</h3>
            <section class="grid">
                <?php foreach ($portfolios as $p): ?>
                    <div class="card pf-card">
                        <div class="pf-top">
                            <div class="badge"><?= $p['icon'] ?></div>
                            <span class="badge-status <?= strtolower($p['status']) ?>"><?= htmlspecialchars($p['status']) ?></span>
                        </div>
                        <h4><?= htmlspecialchars($p['title']) ?></h4>
                        <p class="pf-tmpl">Template: <?= htmlspecialchars($p['template']) ?> · Updated <?= htmlspecialchars($p['updated']) ?></p>
                        <div class="pf-foot">
                            <span>👁 <?= (int)$p['views'] ?> views</span>
                            <a href="edit-portfolio.php">Edit →</a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <a href="edit-portfolio.php" class="card new-card">
                    <div class="plus">＋</div>
                    <span>Edit your portfolio</span>
                </a>
            </section>

            <!-- Get hire-ready -->
            <h3 class="section-title">✅ Get Hire-Ready</h3>
            <section class="panel checklist">
                <div class="cl-head">
                    <b>Profile completion</b>
                    <span class="pct"><?= $completePct ?>% complete</span>
                </div>
                <div class="cl-bar"><i style="width:<?= $completePct ?>%"></i></div>
                <?php foreach ($checklist as $c): ?>
                    <div class="cl-item <?= $c['done'] ? 'done' : '' ?>">
                        <span class="tick <?= $c['done'] ? 'on' : 'off' ?>"><?= $c['done'] ? '✓' : '○' ?></span>
                        <span><?= htmlspecialchars($c['task']) ?></span>
                    </div>
                <?php endforeach; ?>
            </section>

            <!-- Job match -->
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

        </main>
    </div>
</body>

</html>