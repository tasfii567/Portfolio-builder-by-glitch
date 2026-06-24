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

// ── Validate chosen template ──────────────────────────────────────────────────
$template = $_POST['template'] ?? '';

if (!preg_match('/^[A-Za-z0-9._-]+\.html$/', $template)) {
    header("Location: choose-template.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT renderer_file
    FROM templates
    WHERE html_file = ? AND status = 'active'
    LIMIT 1
");
$stmt->execute([$template]);
$templateRow = $stmt->fetch();

if (!$templateRow || !preg_match('/^[A-Za-z0-9._-]+\.php$/', $templateRow['renderer_file'])) {
    header("Location: choose-template.php");
    exit;
}

$templateRenderer = __DIR__ . '/templates/' . basename($templateRow['renderer_file']);

// ── Fetch all user data ───────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch() ?: [];

function fetch_all($pdo, $table, $userId)
{
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE user_id = ? ORDER BY id ASC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

$socialLinks    = fetch_all($pdo, 'social_links',   $userId);
$skills         = fetch_all($pdo, 'skills',          $userId);
$experience     = fetch_all($pdo, 'experience',      $userId);
$education      = fetch_all($pdo, 'education',       $userId);
$projects       = fetch_all($pdo, 'projects',        $userId);
$certifications = fetch_all($pdo, 'certifications',  $userId);
$achievements   = fetch_all($pdo, 'achievements',    $userId);

// ── Helper: safe HTML output ──────────────────────────────────────────────────
function h($v)
{
    return htmlspecialchars($v ?? '', ENT_QUOTES);
}

// ── Social icon map (platform → SVG path) ────────────────────────────────────
function social_icon($platform)
{
    $icons = [
        'GitHub'            => '<path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>',
        'LinkedIn'          => '<path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>',
        'Twitter/X'         => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>',
        'Facebook'          => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>',
        'Instagram'         => '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>',
        'Portfolio Website' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>',
    ];
    return $icons[$platform] ?? $icons['Portfolio Website'];
}

// ── Build derived display values ──────────────────────────────────────────────
$name     = $user['name'] ?? 'Your Name';
$email    = $user['email'] ?? '';
$title    = $profile['title'] ?? 'Professional';
$bio      = $profile['bio'] ?? 'Welcome to my portfolio.';
$location = $profile['location'] ?? '';
$phone    = $profile['phone'] ?? '';
$avatar   = !empty($profile['avatar']) ? $profile['avatar'] : null;
$contactEmail = $profile['contact_email'] ?: $email;
$contactPhone = $profile['contact_phone'] ?: $phone;

// First name for the hero "Hi, I'm ___"
$firstName = explode(' ', trim($name))[0];

// Year of earliest experience (for "X years experience" stat)
$expYears = '0';
if (!empty($experience)) {
    $earliest = null;
    foreach ($experience as $e) {
        if (!empty($e['start_date'])) {
            $y = (int) date('Y', strtotime($e['start_date']));
            if ($earliest === null || $y < $earliest) $earliest = $y;
        }
    }
    if ($earliest) $expYears = (date('Y') - $earliest) . 'y';
}

// ── Build skill tags HTML ─────────────────────────────────────────────────────
$skillTagsHtml = '';
if (!empty($skills)) {
    foreach ($skills as $sk) {
        $skillTagsHtml .= '<span role="listitem" class="stag text-sm bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 px-3.5 py-1.5 rounded-full hover:border-accent">' . h($sk['skill_name']) . '</span>';
    }
} else {
    $skillTagsHtml = '<span class="stag text-sm bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 px-3.5 py-1.5 rounded-full">Add skills in Edit Portfolio</span>';
}

// ── Build experience HTML ─────────────────────────────────────────────────────
$experienceHtml = '';
if (!empty($experience)) {
    foreach ($experience as $exp) {
        $startFmt = !empty($exp['start_date']) ? date('M Y', strtotime($exp['start_date'])) : '';
        $endFmt   = $exp['currently_working'] ? 'Present' : (!empty($exp['end_date']) ? date('M Y', strtotime($exp['end_date'])) : '');
        $period   = $startFmt . ($endFmt ? ' – ' . $endFmt : '');
        $desc     = !empty($exp['description']) ? '<p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed mt-2">' . nl2br(h($exp['description'])) . '</p>' : '';
        $experienceHtml .= '
        <article class="reveal card-h bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-100 dark:border-zinc-800 hover:border-accent">
            <div class="flex items-start justify-between gap-4 mb-1">
                <div>
                    <h3 class="font-display font-bold text-lg text-zinc-900 dark:text-white">' . h($exp['position']) . '</h3>
                    <p class="text-accent font-medium text-sm">' . h($exp['company']) . '</p>
                </div>
                ' . ($period ? '<span class="text-xs text-zinc-400 bg-zinc-50 dark:bg-zinc-800 px-3 py-1 rounded-full whitespace-nowrap">' . h($period) . '</span>' : '') . '
            </div>
            ' . $desc . '
        </article>';
    }
} else {
    $experienceHtml = '<p class="text-zinc-400 text-sm">No work experience added yet. <a href="edit-portfolio.php" class="text-accent hover:underline">Add some →</a></p>';
}

// ── Build education HTML ──────────────────────────────────────────────────────
$educationHtml = '';
if (!empty($education)) {
    foreach ($education as $edu) {
        $period = trim(($edu['start_year'] ?? '') . ($edu['end_year'] ? ' – ' . $edu['end_year'] : ''));
        $field  = !empty($edu['field_of_study']) ? ' · ' . h($edu['field_of_study']) : '';
        $gpa    = !empty($edu['gpa']) ? '<span class="text-xs text-zinc-400 ml-2">GPA: ' . h($edu['gpa']) . '</span>' : '';
        $educationHtml .= '
        <article class="reveal card-h bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-100 dark:border-zinc-800 hover:border-accent">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-display font-bold text-lg text-zinc-900 dark:text-white">' . h($edu['institution']) . '</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">' . h($edu['degree']) . $field . $gpa . '</p>
                </div>
                ' . ($period ? '<span class="text-xs text-zinc-400 bg-zinc-50 dark:bg-zinc-800 px-3 py-1 rounded-full whitespace-nowrap">' . h($period) . '</span>' : '') . '
            </div>
        </article>';
    }
} else {
    $educationHtml = '<p class="text-zinc-400 text-sm">No education added yet. <a href="edit-portfolio.php" class="text-accent hover:underline">Add some →</a></p>';
}

// ── Build projects HTML ───────────────────────────────────────────────────────
$projectsHtml = '';
if (!empty($projects)) {
    foreach ($projects as $proj) {
        $imgHtml = '';
        if (!empty($proj['image_path'])) {
            $imgHtml = '<div class="pf w-full h-48"><img src="' . h($proj['image_path']) . '" alt="' . h($proj['title']) . '" loading="lazy"/></div>';
        }
        $tags = '';
        if (!empty($proj['technologies'])) {
            foreach (array_slice(explode(',', $proj['technologies']), 0, 3) as $tech) {
                $tags .= '<span class="text-xs bg-orange-50 dark:bg-zinc-800 text-accent border border-orange-200 dark:border-zinc-700 px-3 py-1 rounded-full">' . h(trim($tech)) . '</span>';
            }
        }
        $links = '';
        if (!empty($proj['demo_url'])) {
            $links .= '<a href="' . h($proj['demo_url']) . '" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-sm font-medium text-zinc-900 dark:text-white nl">Live Demo →</a>';
        }
        if (!empty($proj['github_url'])) {
            $links .= ($links ? ' &nbsp;·&nbsp; ' : '') . '<a href="' . h($proj['github_url']) . '" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-sm font-medium text-zinc-900 dark:text-white nl">GitHub →</a>';
        }
        $featured = $proj['featured'] ? ' md:col-span-2' : '';
        $projectsHtml .= '
        <article class="reveal card-h group rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 hover:border-accent' . $featured . '">
            ' . $imgHtml . '
            <div class="p-6">
                ' . ($tags ? '<div class="flex flex-wrap gap-2 mb-3">' . $tags . '</div>' : '') . '
                <h3 class="font-display font-bold text-xl text-zinc-900 dark:text-white mb-1.5">' . h($proj['title']) . '</h3>
                ' . (!empty($proj['description']) ? '<p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed mb-4">' . h($proj['description']) . '</p>' : '') . '
                ' . ($links ? '<div class="flex flex-wrap gap-3">' . $links . '</div>' : '') . '
            </div>
        </article>';
    }
} else {
    $projectsHtml = '<p class="text-zinc-400 text-sm col-span-2">No projects added yet. <a href="edit-portfolio.php" class="text-accent hover:underline">Add some →</a></p>';
}

// ── Build certifications HTML ─────────────────────────────────────────────────
$certsHtml = '';
if (!empty($certifications)) {
    foreach ($certifications as $cert) {
        $date = !empty($cert['date_issued']) ? date('M Y', strtotime($cert['date_issued'])) : '';
        $link = !empty($cert['credential_url']) ? '<a href="' . h($cert['credential_url']) . '" target="_blank" rel="noopener" class="text-xs text-accent hover:underline mt-1 inline-block">View credential →</a>' : '';
        $certsHtml .= '
        <article class="reveal card-h bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-100 dark:border-zinc-800 hover:border-accent">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-display font-bold text-base text-zinc-900 dark:text-white">' . h($cert['name']) . '</h3>
                    ' . (!empty($cert['issuing_org']) ? '<p class="text-sm text-zinc-500 dark:text-zinc-400">' . h($cert['issuing_org']) . '</p>' : '') . '
                    ' . $link . '
                </div>
                ' . ($date ? '<span class="text-xs text-zinc-400 bg-zinc-50 dark:bg-zinc-800 px-3 py-1 rounded-full whitespace-nowrap">' . h($date) . '</span>' : '') . '
            </div>
        </article>';
    }
}

// ── Build achievements HTML ───────────────────────────────────────────────────
$achievementsHtml = '';
if (!empty($achievements)) {
    foreach ($achievements as $ach) {
        $date = !empty($ach['achieved_on']) ? date('M Y', strtotime($ach['achieved_on'])) : '';
        $achievementsHtml .= '
        <article class="reveal card-h bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-100 dark:border-zinc-800 hover:border-accent">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-display font-bold text-base text-zinc-900 dark:text-white">' . h($ach['title']) . '</h3>
                    ' . (!empty($ach['description']) ? '<p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed mt-1">' . h($ach['description']) . '</p>' : '') . '
                </div>
                ' . ($date ? '<span class="text-xs text-zinc-400 bg-zinc-50 dark:bg-zinc-800 px-3 py-1 rounded-full whitespace-nowrap">' . h($date) . '</span>' : '') . '
            </div>
        </article>';
    }
}

// ── Build social links HTML (for contact section) ─────────────────────────────
$socialLinksHtml = '';
foreach ($socialLinks as $link) {
    $isStroke = in_array($link['platform'], ['Portfolio Website']);
    $svgAttrs = $isStroke
        ? 'fill="none" viewBox="0 0 24 24" stroke="currentColor"'
        : 'fill="currentColor" viewBox="0 0 24 24"';
    $socialLinksHtml .= '
    <a href="' . h($link['url']) . '" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 text-zinc-400 hover:text-white transition-colors">
        <span class="w-9 h-9 flex items-center justify-center bg-zinc-800 rounded-lg group-hover:bg-accent/20 transition-colors shrink-0">
            <svg class="w-4 h-4" ' . $svgAttrs . ' aria-hidden="true">' . social_icon($link['platform']) . '</svg>
        </span>
        <span class="text-sm">' . h($link['platform']) . '</span>
    </a>';
}
if ($contactEmail) {
    $socialLinksHtml = '
    <a href="mailto:' . h($contactEmail) . '" class="group flex items-center gap-3 text-zinc-400 hover:text-white transition-colors">
        <span class="w-9 h-9 flex items-center justify-center bg-zinc-800 rounded-lg group-hover:bg-accent/20 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </span>
        <span class="text-sm">' . h($contactEmail) . '</span>
    </a>' . $socialLinksHtml;
}

// ── Avatar HTML ───────────────────────────────────────────────────────────────
if ($avatar) {
    $avatarHtml = '<img src="' . h($avatar) . '" alt="' . h($name) . '" loading="eager" style="width:100%;height:100%;object-fit:cover;display:block;"/>';
} else {
    // Initials fallback
    $initials = strtoupper(substr($name, 0, 1) . (strpos($name, ' ') !== false ? substr($name, strpos($name, ' ') + 1, 1) : ''));
    $avatarHtml = '<div style="width:100%;height:100%;background:#36402c;display:flex;align-items:center;justify-content:center;font-size:72px;font-weight:800;color:#fff;font-family:PT Sans,sans-serif;">' . h($initials) . '</div>';
}

// ── Optional sections (only show if data exists) ──────────────────────────────
$showCerts    = !empty($certifications);
$showAchievements = !empty($achievements);

// ── Conditional nav items ─────────────────────────────────────────────────────
$extraNavMd   = '';
$extraNavMobile = '';
if ($showCerts || $showAchievements) {
    $extraNavMd     .= '<li><a href="#extras" class="nl text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" :class="s===\'extras\'?\'on !text-zinc-900 dark:!text-white\':\'\'">More</a></li>';
    $extraNavMobile .= '<li><a href="#extras" @click="mm=false" class="block text-zinc-700 dark:text-zinc-300 hover:text-accent transition-colors">More</a></li>';
}

if (!is_file($templateRenderer)) {
    header("Location: choose-template.php");
    exit;
}

require $templateRenderer;
