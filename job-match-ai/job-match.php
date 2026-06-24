<?php

declare(strict_types=1);

require_once __DIR__ . '/../nahin/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Pull user info (same as dashboard + edit-portfolio)
$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: login.php');
    exit;
}

$userName = htmlspecialchars($user['name']);

$stmt = $pdo->prepare("SELECT title, avatar FROM profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch();

$userRole   = htmlspecialchars($profile['title'] ?? 'Member');
$userAvatar = $profile['avatar'] ?? null;
$initials   = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') !== false ? substr($user['name'], strpos($user['name'], ' ') + 1, 1) : ''));

// Pull skills from DB to pre-fill portfolio text
$stmt = $pdo->prepare("SELECT skill_name, level FROM skills WHERE user_id = ? ORDER BY id ASC");
$stmt->execute([$userId]);
$dbSkills = $stmt->fetchAll();
$skillsText = implode(', ', array_column($dbSkills, 'skill_name'));

// Pull projects from DB
$stmt = $pdo->prepare("SELECT title, description, technologies FROM projects WHERE user_id = ? ORDER BY id ASC");
$stmt->execute([$userId]);
$dbProjects = $stmt->fetchAll();
$projectsText = '';
foreach ($dbProjects as $p) {
    $projectsText .= $p['title'] . ': ' . $p['description'] . ' Technologies: ' . $p['technologies'] . "\n";
}

// Auto-build portfolio text from DB if nothing in session
$autoPortfolio = trim($skillsText . "\n\n" . $projectsText);

// ── Job match logic (unchanged from original) ────────────────────────

$skillCatalog = [
    'Languages'       => ['php', 'javascript', 'typescript', 'python', 'java', 'c#', 'c++', 'go', 'ruby', 'sql', 'html', 'css', 'bash'],
    'Backend'         => ['laravel', 'symfony', 'wordpress', 'node.js', 'express', 'django', 'flask', 'spring', 'rest api', 'graphql', 'microservices', 'api integration'],
    'Frontend'        => ['react', 'vue', 'angular', 'next.js', 'tailwind', 'bootstrap', 'jquery', 'responsive design', 'accessibility'],
    'Data'            => ['mysql', 'postgresql', 'mongodb', 'redis', 'elasticsearch', 'etl', 'data analysis', 'pandas', 'power bi', 'tableau'],
    'Cloud & DevOps'  => ['aws', 'azure', 'gcp', 'docker', 'kubernetes', 'ci/cd', 'github actions', 'linux', 'nginx', 'apache'],
    'AI & Automation' => ['machine learning', 'ai', 'openai', 'prompt engineering', 'chatbot', 'nlp', 'automation', 'recommendation system'],
    'Quality'         => ['testing', 'unit testing', 'phpunit', 'jest', 'selenium', 'playwright', 'code review', 'security'],
    'Professional'    => ['project management', 'communication', 'leadership', 'agile', 'scrum', 'client management', 'documentation'],
];

$synonyms = [
    'js'                  => 'javascript',
    'ts'                  => 'typescript',
    'nodejs'              => 'node.js',
    'node'                => 'node.js',
    'postgres'            => 'postgresql',
    'mysql database'      => 'mysql',
    'ci cd'               => 'ci/cd',
    'llm'                 => 'ai',
    'large language model' => 'ai',
    'wp'                  => 'wordpress',
];

$defaultPortfolio = $autoPortfolio !== ''
    ? $autoPortfolio
    : "Paste your portfolio, resume, LinkedIn About section, GitHub profile, or project descriptions here.\n\nExample:\nPHP developer with Laravel, MySQL, REST API, JavaScript, React, Docker, AWS, testing, and AI automation experience.";

$portfolio   = trim((string)($_POST['portfolio']    ?? ($_SESSION['portfolio']    ?? $defaultPortfolio)));
$targetRole  = trim((string)($_POST['target_role']  ?? ($_SESSION['target_role']  ?? 'PHP Developer')));
$location    = trim((string)($_POST['location']     ?? ($_SESSION['location']     ?? 'Remote')));
$experience  = trim((string)($_POST['experience']   ?? ($_SESSION['experience']   ?? 'mid')));
$jobText     = trim((string)($_POST['job_text']     ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['portfolio']    = $portfolio;
    $_SESSION['target_role']  = $targetRole;
    $_SESSION['location']     = $location;
    $_SESSION['experience']   = $experience;
}

function normalize_text(string $text): string
{
    $text = strtolower($text);
    $text = str_replace(['+', '#'], [' plus ', ' sharp '], $text);
    $text = preg_replace('/[^\p{L}\p{N}\/\.\s-]+/u', ' ', $text) ?? $text;
    return preg_replace('/\s+/', ' ', $text) ?? $text;
}

function flatten_catalog(array $catalog): array
{
    $skills = [];
    foreach ($catalog as $group => $items) {
        foreach ($items as $skill) {
            $skills[$skill] = $group;
        }
    }
    return $skills;
}

function extract_skills(string $text, array $catalog, array $synonyms): array
{
    $normalized = normalize_text($text);
    foreach ($synonyms as $from => $to) {
        $normalized = preg_replace('/\b' . preg_quote($from, '/') . '\\b/u', $to, $normalized) ?? $normalized;
    }
    $flat  = flatten_catalog($catalog);
    $found = [];
    foreach ($flat as $skill => $group) {
        $needle  = normalize_text($skill);
        $pattern = '/(?<![\p{L}\p{N}])' . preg_quote($needle, '/') . '(?![\p{L}\p{N}])/u';
        if (preg_match($pattern, $normalized)) {
            $found[$skill] = $group;
        }
    }
    ksort($found);
    return $found;
}

function rank_skills(array $skills, array $priorityWords): array
{
    $ranked = [];
    foreach ($skills as $skill => $group) {
        $score = 50;
        foreach ($priorityWords as $word) {
            if ($word !== '' && str_contains($skill, $word)) {
                $score += 25;
            }
        }
        if (in_array($group, ['Backend', 'AI & Automation', 'Cloud & DevOps'], true)) {
            $score += 10;
        }
        $ranked[] = ['skill' => $skill, 'group' => $group, 'score' => $score];
    }
    usort($ranked, fn($a, $b) => $b['score'] <=> $a['score'] ?: strcmp($a['skill'], $b['skill']));
    return $ranked;
}

function build_searches(string $role, string $location, string $experience, array $rankedSkills): array
{
    $top            = array_slice(array_column($rankedSkills, 'skill'), 0, 6);
    $core           = array_values(array_unique(array_filter([$role, ...$top])));
    $query          = implode(' ', $core);
    $encodedQuery   = rawurlencode($query);
    $encodedLocation = rawurlencode($location);
    $experienceMap  = ['entry' => '2', 'mid' => '3', 'senior' => '4'];
    $linkedInLevel  = $experienceMap[$experience] ?? '3';
    return [
        ['board' => 'LinkedIn',        'title' => $role . ' roles matching ' . count($top) . ' portfolio skills', 'url' => "https://www.linkedin.com/jobs/search/?keywords={$encodedQuery}&location={$encodedLocation}&f_E={$linkedInLevel}", 'query' => $query, 'note' => 'Opens LinkedIn with your role, location, and strongest extracted skills.'],
        ['board' => 'Indeed',          'title' => $role . ' openings filtered by your strongest skills',    'url' => "https://www.indeed.com/jobs?q={$encodedQuery}&l={$encodedLocation}",                                                                           'query' => $query, 'note' => 'Opens Indeed with the same matching query so you can compare postings quickly.'],
        ['board' => 'LinkedIn Boolean', 'title' => 'Narrow search for high-confidence matches',            'url' => 'https://www.linkedin.com/jobs/search/?keywords=' . rawurlencode('"' . $role . '" ' . implode(' OR ', array_map(fn($s) => '"' . $s . '"', array_slice($top, 0, 4)))) . "&location={$encodedLocation}", 'query' => '"' . $role . '" ' . implode(' OR ', array_map(fn($s) => '"' . $s . '"', array_slice($top, 0, 4))), 'note' => 'Useful when broad searches produce noisy results.'],
    ];
}

function score_job_text(array $portfolioSkills, string $jobText, array $catalog, array $synonyms): array
{
    if ($jobText === '') {
        return ['score' => null, 'matched' => [], 'missing' => []];
    }
    $jobSkills = extract_skills($jobText, $catalog, $synonyms);
    $matched   = array_intersect_key($portfolioSkills, $jobSkills);
    $missing   = array_diff_key($jobSkills, $portfolioSkills);
    $score     = count($jobSkills) === 0 ? 0 : (int)round((count($matched) / count($jobSkills)) * 100);
    return ['score' => $score, 'matched' => $matched, 'missing' => $missing];
}

$portfolioSkills = extract_skills($portfolio, $skillCatalog, $synonyms);
$priorityWords   = preg_split('/\s+/', normalize_text($targetRole)) ?: [];
$rankedSkills    = rank_skills($portfolioSkills, $priorityWords);
$searches        = build_searches($targetRole, $location, $experience, $rankedSkills);
$jobScore        = score_job_text($portfolioSkills, $jobText, $skillCatalog, $synonyms);
$grouped         = [];
foreach ($rankedSkills as $row) {
    $grouped[$row['group']][] = $row['skill'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Match — PortfolioBuilder</title>
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
            --danger: #a8442f;
            --danger-bg: #f3e3df;
            --danger-line: #ecc9c1;
            --radius: 14px;
            --shadow: 0 8px 24px rgba(40, 45, 30, .07);
            font-family: "Plus Jakarta Sans", "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
        }

        *,
        *::before,
        *::after {
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

        /* ── Layout ── */
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
            overflow-y: auto;
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
            background: var(--danger-bg);
            color: var(--danger);
            border: 1px solid var(--danger-line);
            transition: .18s;
        }

        .logout a:hover {
            background: #eed6d0
        }

        /* ── Main ── */
        .main {
            padding: 28px 34px;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            gap: 0
        }

        /* ── Topbar ── */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 26px;
            flex-wrap: wrap;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px
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

        .topbar .eyebrow {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 2px
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

        .menu-btn {
            display: none
        }

        /* ── Skills found badge ── */
        .skills-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #edf1e4;
            border: 1px solid #cfe0b6;
            border-radius: 30px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 700;
            color: var(--primary);
        }

        .skills-badge span {
            font-size: 18px;
            font-weight: 800
        }

        /* ── Two-column content area ── */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
            align-items: start;
        }

        /* ── Panel / card ── */
        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .panel:last-child {
            margin-bottom: 0
        }

        .panel-heading {
            margin-bottom: 16px
        }

        .panel-heading h2 {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 4px
        }

        .panel-heading p {
            font-size: 13px;
            color: var(--muted)
        }

        /* ── Form inputs ── */
        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        label {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
        }

        input,
        select,
        textarea {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 14px;
            color: var(--text);
            font-family: inherit;
            transition: border-color .18s, box-shadow .18s;
            width: 100%;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(125, 158, 88, .15);
        }

        textarea {
            resize: vertical;
            line-height: 1.6
        }

        .submit-btn {
            margin-top: 16px;
            width: 100%;
            padding: 13px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: background .18s, transform .15s;
        }

        .submit-btn:hover {
            background: var(--primary-soft);
            transform: translateY(-1px)
        }

        /* ── Skill chips ── */
        .skill-groups {
            display: flex;
            flex-direction: column;
            gap: 14px
        }

        .skill-groups h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px
        }

        .chips span {
            background: #edf1e4;
            color: var(--primary);
            border: 1px solid #cfe0b6;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .chips.missing span {
            background: var(--danger-bg);
            color: var(--danger);
            border-color: var(--danger-line);
        }

        .chips.compact span {
            font-size: 12px;
            padding: 3px 10px
        }

        /* ── Job cards ── */
        .job-list {
            display: flex;
            flex-direction: column;
            gap: 12px
        }

        .job-card {
            background: var(--surface-2);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
        }

        .job-card > div {
            min-width: 0;
            flex: 1;
        }

        .job-card .board {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--accent);
            margin-bottom: 4px;
            display: block;
        }

        .job-card h3 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px
        }

        .job-card p {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 6px;
            line-height: 1.5
        }

        .job-card code {
            font-size: 11px;
            color: var(--muted);
            background: var(--line);
            padding: 2px 8px;
            border-radius: 6px;
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .job-card a {
            flex-shrink: 0;
            background: var(--primary);
            color: #fff;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            transition: .18s;
            white-space: nowrap;
        }

        .job-card a:hover {
            background: var(--primary-soft);
            transform: translateY(-1px)
        }

        /* ── Score ring ── */
        .score {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            margin: 10px 0 18px;
        }

        .score-ring {
            position: relative;
            width: 90px;
            height: 90px;
        }

        .score-ring svg {
            transform: rotate(-90deg)
        }

        .score-ring .ring-bg {
            fill: none;
            stroke: var(--surface-2);
            stroke-width: 7
        }

        .score-ring .ring-fill {
            fill: none;
            stroke: url(#scoreGrad);
            stroke-width: 7;
            stroke-linecap: round;
            transition: stroke-dashoffset .8s ease;
        }

        .score-ring .ring-num {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .score-ring .ring-num strong {
            font-size: 20px;
            font-weight: 800;
            line-height: 1
        }

        .score-ring .ring-num span {
            font-size: 10px;
            color: var(--muted)
        }

        .score-label h3 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
            text-align: center
        }

        .score-label p {
            font-size: 12px;
            color: var(--muted);
            text-align: center
        }

        .chips-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .8px;
            margin: 14px 0 8px
        }

        .empty {
            font-size: 13px;
            color: var(--muted);
            font-style: italic
        }

        /* ── Responsive ── */
        @media(max-width:1100px) {
            .content-grid {
                grid-template-columns: 1fr
            }
        }

        @media(max-width:980px) {
            .field-row {
                grid-template-columns: 1fr 1fr
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

            .field-row {
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
                <a href="../nahin/dashboard.php"><span class="ic">🏠</span> Dashboard</a>
                <a href="../nahin/edit-portfolio.php"><span class="ic">👤</span> Edit Portfolio</a>
                <a href="../nahin/demo.php"><span class="ic">🎨</span> Choose Template</a>
                <a href="../nahin/edit-portfolio.php"><span class="ic">📁</span> Create Portfolio</a>
                <a href="../samina/resume_module/resume_preview.php"><span class="ic">📄</span> Create Resume</a>
                <a href="job-match.php" class="active"><span class="ic">📊</span> Job Match</a>
            </nav>
            <div class="logout">
                <a href="../nahin/logout.php"><span>⏻</span> Logout</a>
            </div>
        </aside>

        <!-- ── Main ── -->
        <main class="main">

            <!-- Topbar -->
            <div class="topbar">
                <div class="topbar-left">
                    <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                    <div>
                        <p class="eyebrow">Portfolio-driven search assistant</p>
                        <h2>Job Match</h2>
                        <p>Analyse your skills and find matching roles.</p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                    <div class="skills-badge"><span><?= count($portfolioSkills) ?></span> skills found</div>
                    <div class="profile">
                        <div class="who"><b><?= $userName ?></b><small><?= $userRole ?></small></div>
                        <div class="avatar">
                            <?php if ($userAvatar): ?>
                                <img src="../nahin/<?= htmlspecialchars($userAvatar) ?>" alt="<?= $userName ?>"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <span style="display:none;width:100%;height:100%;align-items:center;justify-content:center;"><?= $initials ?></span>
                            <?php else: ?>
                                <?= $initials ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content grid: form left, results right -->
            <div class="content-grid">

                <!-- LEFT: input form -->
                <div>
                    <form method="post" class="panel">
                        <div class="panel-heading">
                            <h2>Your Profile</h2>
                            <p>Paste your portfolio text or edit skills below — pre-filled from your profile.</p>
                        </div>

                        <div class="field-row">
                            <label>
                                Target role
                                <input name="target_role" value="<?= htmlspecialchars($targetRole) ?>" placeholder="PHP Developer">
                            </label>
                            <label>
                                Location
                                <input name="location" value="<?= htmlspecialchars($location) ?>" placeholder="Remote, Dhaka, New York">
                            </label>
                            <label>
                                Experience
                                <select name="experience">
                                    <option value="entry" <?= $experience === 'entry'  ? 'selected' : '' ?>>Entry</option>
                                    <option value="mid" <?= $experience === 'mid'    ? 'selected' : '' ?>>Mid</option>
                                    <option value="senior" <?= $experience === 'senior' ? 'selected' : '' ?>>Senior</option>
                                </select>
                            </label>
                        </div>

                        <label style="margin-bottom:14px">
                            Portfolio / resume text
                            <textarea name="portfolio" rows="10"><?= htmlspecialchars($portfolio) ?></textarea>
                        </label>

                        <label>
                            Optional: paste a job description to score it
                            <textarea name="job_text" rows="5" placeholder="Paste a job post here to compare it against your portfolio."><?= htmlspecialchars($jobText) ?></textarea>
                        </label>

                        <button type="submit" class="submit-btn">Analyze and match jobs →</button>
                    </form>
                </div>

                <!-- RIGHT: results -->
                <div>

                    <!-- Matched skills -->
                    <div class="panel">
                        <div class="panel-heading">
                            <h2>Matched Skills</h2>
                            <p>Ranked from your portfolio text.</p>
                        </div>
                        <?php if ($grouped === []): ?>
                            <p class="empty">No catalog skills found yet. Add project details, tools, and technologies.</p>
                        <?php else: ?>
                            <div class="skill-groups">
                                <?php foreach ($grouped as $group => $skills): ?>
                                    <div>
                                        <h3><?= htmlspecialchars($group) ?></h3>
                                        <div class="chips">
                                            <?php foreach ($skills as $skill): ?>
                                                <span><?= htmlspecialchars($skill) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Live job searches -->
                    <div class="panel">
                        <div class="panel-heading">
                            <h2>Live Job Searches</h2>
                            <p>Opens current listings on each board.</p>
                        </div>
                        <div class="job-list">
                            <?php foreach ($searches as $search): ?>
                                <article class="job-card">
                                    <div>
                                        <span class="board"><?= htmlspecialchars($search['board']) ?></span>
                                        <h3><?= htmlspecialchars($search['title']) ?></h3>
                                        <p><?= htmlspecialchars($search['note']) ?></p>
                                        <code><?= htmlspecialchars($search['query']) ?></code>
                                    </div>
                                    <a href="<?= htmlspecialchars($search['url']) ?>" target="_blank" rel="noopener">Open jobs</a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Job fit score -->
                    <div class="panel">
                        <div class="panel-heading">
                            <h2>Job Fit Score</h2>
                            <p>Paste a job description on the left to compare.</p>
                        </div>
                        <?php if ($jobScore['score'] === null): ?>
                            <p class="empty">No job description pasted yet.</p>
                        <?php else: ?>
                            <div class="score">
                                <div class="score-ring">
                                    <svg width="90" height="90" viewBox="0 0 90 90">
                                        <defs>
                                            <linearGradient id="scoreGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                                <stop offset="0%" stop-color="#3a4a23" />
                                                <stop offset="100%" stop-color="#7d9e58" />
                                            </linearGradient>
                                        </defs>
                                        <circle class="ring-bg" cx="45" cy="45" r="38" />
                                        <circle class="ring-fill" cx="45" cy="45" r="38"
                                            stroke-dasharray="238.8"
                                            stroke-dashoffset="<?= 238.8 - (238.8 * (int)$jobScore['score'] / 100) ?>" />
                                    </svg>
                                    <div class="ring-num">
                                        <strong><?= (int)$jobScore['score'] ?>%</strong>
                                        <span>match</span>
                                    </div>
                                </div>
                                <div class="score-label">
                                    <h3>Skill match score</h3>
                                    <p>Based on skills found in the job post vs your portfolio.</p>
                                </div>
                            </div>
                            <p class="chips-label">Matched</p>
                            <div class="chips compact">
                                <?php foreach (array_keys($jobScore['matched']) as $skill): ?>
                                    <span><?= htmlspecialchars($skill) ?></span>
                                <?php endforeach; ?>
                                <?php if ($jobScore['matched'] === []): ?><em class="empty">None found</em><?php endif; ?>
                            </div>
                            <p class="chips-label">Missing from your portfolio</p>
                            <div class="chips compact missing">
                                <?php foreach (array_keys($jobScore['missing']) as $skill): ?>
                                    <span><?= htmlspecialchars($skill) ?></span>
                                <?php endforeach; ?>
                                <?php if ($jobScore['missing'] === []): ?><em class="empty">No catalog gaps detected</em><?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div><!-- /content-grid -->

        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ring = document.querySelector('.ring-fill');
            if (ring) {
                var target = ring.getAttribute('stroke-dashoffset');
                ring.style.strokeDashoffset = '238.8';
                requestAnimationFrame(function() {
                    ring.style.transition = 'stroke-dashoffset .8s ease';
                    ring.style.strokeDashoffset = target;
                });
            }
        });
    </script>
</body>

</html>