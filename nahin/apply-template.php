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
$allowedTemplates = ['temp1.html']; // add more as you build them
$template = $_POST['template'] ?? '';

if (!in_array($template, $allowedTemplates, true)) {
    header("Location: choose-template.php");
    exit;
}

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
?>
<!doctype html>
<html lang="en" x-data="app()" :class="{'dark':dark}" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= h($name) ?> — Portfolio</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans:ital,wght@0,400;0,700;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet" />

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    fontFamily: {
                        display: ["PT Sans", "sans-serif"],
                        body: ["DM Sans", "sans-serif"]
                    },
                    colors: {
                        accent: "#FF6B2B",
                        "accent-light": "#FF8F5C"
                    }
                }
            }
        };
    </script>

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box
        }

        html,
        body {
            font-family: "DM Sans", sans-serif
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: "PT Sans", sans-serif
        }

        body {
            transition: background-color .3s, color .3s
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            opacity: .35;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.05'/%3E%3C/svg%3E")
        }

        ::-webkit-scrollbar {
            width: 5px
        }

        ::-webkit-scrollbar-track {
            background: transparent
        }

        ::-webkit-scrollbar-thumb {
            background: #ff6b2b;
            border-radius: 99px
        }

        .reveal {
            opacity: 0;
            transform: translateY(26px);
            transition: opacity .6s cubic-bezier(.4, 0, .2, 1), transform .6s cubic-bezier(.4, 0, .2, 1)
        }

        .reveal.in {
            opacity: 1;
            transform: none
        }

        .d1 {
            transition-delay: .08s
        }

        .d2 {
            transition-delay: .16s
        }

        .d3 {
            transition-delay: .24s
        }

        .d4 {
            transition-delay: .32s
        }

        .nl {
            position: relative
        }

        .nl::after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1.5px;
            background: currentColor;
            transition: width .22s cubic-bezier(.4, 0, .2, 1)
        }

        .nl:hover::after,
        .nl.on::after {
            width: 100%
        }

        .nl.on {
            font-weight: 500
        }

        .shimmer {
            position: relative;
            overflow: hidden
        }

        .shimmer::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: rgba(255, 255, 255, .18);
            transform: skewX(-20deg);
            transition: left .4s cubic-bezier(.4, 0, .2, 1)
        }

        .shimmer:hover::after {
            left: 160%
        }

        .pf {
            overflow: hidden;
            background: #d4d4d8
        }

        .pf img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block
        }

        .card-h {
            transition: transform .28s cubic-bezier(.4, 0, .2, 1), border-color .18s
        }

        .card-h:hover {
            transform: translateY(-4px)
        }

        .stag {
            transition: border-color .18s
        }

        [x-cloak] {
            display: none !important
        }

        /* Back-to-dashboard bar */
        .edit-bar {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            gap: 10px
        }

        .edit-bar a {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: .18s
        }

        .edit-bar .btn-edit {
            background: #36402c;
            color: #fff
        }

        .edit-bar .btn-edit:hover {
            background: #46532f
        }

        .edit-bar .btn-dash {
            background: #fff;
            color: #36402c;
            border: 1.5px solid #e8e1d3
        }

        .edit-bar .btn-dash:hover {
            background: #f7f3ea
        }
    </style>
</head>

<body class="bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased">

    <!-- ═══ NAV ═══ -->
    <header class="fixed inset-x-0 top-0 z-50 transition-all duration-300" :class="sc?'bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md shadow-sm shadow-black/5':''">
        <nav class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between" aria-label="Main navigation">
            <a href="#hero" class="font-display font-bold text-xl tracking-tight relative z-10">
                <span class="text-zinc-900 dark:text-white"><?= h(strtolower(substr($firstName, 0, 3))) ?></span><span class="text-accent"><?= h(strtolower(substr($firstName, 3))) ?: 'io' ?></span>
            </a>
            <ul class="hidden md:flex items-center gap-8 text-sm" role="list">
                <li><a href="#about" class="nl text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" :class="s==='about'?'on !text-zinc-900 dark:!text-white':''">About</a></li>
                <li><a href="#experience" class="nl text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" :class="s==='experience'?'on !text-zinc-900 dark:!text-white':''">Experience</a></li>
                <li><a href="#work" class="nl text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" :class="s==='work'?'on !text-zinc-900 dark:!text-white':''">Projects</a></li>
                <li><a href="#education" class="nl text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" :class="s==='education'?'on !text-zinc-900 dark:!text-white':''">Education</a></li>
                <?= $extraNavMd ?>
                <li><a href="#contact" class="nl text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors" :class="s==='contact'?'on !text-zinc-900 dark:!text-white':''">Contact</a></li>
            </ul>
            <div class="flex items-center gap-3">
                <button @click="dark=!dark" class="w-9 h-9 flex items-center justify-center rounded-full border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors" :aria-label="dark?'Light mode':'Dark mode'">
                    <svg x-show="!dark" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                    </svg>
                    <svg x-show="dark" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                </button>
                <a href="Dashboard.php" class="hidden md:inline-flex items-center gap-2 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium px-4 py-2 rounded-full hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-colors">
                    🏠 Dashboard
                </a>
                <?php if ($contactEmail): ?>
                    <a href="#contact" class="hidden md:inline-flex items-center gap-2 shimmer bg-accent text-white text-sm font-medium px-5 py-2 rounded-full hover:bg-accent-light transition-colors">
                        Contact me
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                <?php endif; ?>
                <button @click="mm=!mm" class="md:hidden w-9 h-9 flex items-center justify-center rounded-full border border-zinc-200 dark:border-zinc-800" :aria-expanded="mm" aria-label="Toggle menu">
                    <svg x-show="!mm" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mm" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </nav>
        <div x-show="mm" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="md:hidden bg-white dark:bg-zinc-950 border-t border-zinc-100 dark:border-zinc-900">
            <ul class="flex flex-col px-6 py-5 gap-4 text-sm font-medium" role="list">
                <li><a href="#about" @click="mm=false" class="block text-zinc-700 dark:text-zinc-300 hover:text-accent transition-colors">About</a></li>
                <li><a href="#experience" @click="mm=false" class="block text-zinc-700 dark:text-zinc-300 hover:text-accent transition-colors">Experience</a></li>
                <li><a href="#work" @click="mm=false" class="block text-zinc-700 dark:text-zinc-300 hover:text-accent transition-colors">Projects</a></li>
                <li><a href="#education" @click="mm=false" class="block text-zinc-700 dark:text-zinc-300 hover:text-accent transition-colors">Education</a></li>
                <?= $extraNavMobile ?>
                <li><a href="#contact" @click="mm=false" class="block text-zinc-700 dark:text-zinc-300 hover:text-accent transition-colors">Contact</a></li>
                <li class="pt-2 border-t border-zinc-100 dark:border-zinc-900">
                    <a href="Dashboard.php" @click="mm=false" class="inline-flex items-center gap-2 text-zinc-700 dark:text-zinc-300 hover:text-accent transition-colors font-medium">🏠 Dashboard</a>
                </li>
            </ul>
        </div>
    </header>

    <main>
        <!-- ═══ HERO ═══ -->
        <section id="hero" class="relative min-h-screen flex items-center pt-16 overflow-hidden">
            <div class="absolute top-1/4 right-0 w-96 h-96 bg-accent/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
            <div class="absolute bottom-1/4 left-0 w-64 h-64 bg-zinc-200/50 dark:bg-zinc-800/30 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
            <div class="relative z-10 max-w-6xl mx-auto px-6 py-24 w-full">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <?php if ($location): ?>
                            <p class="reveal text-sm font-medium text-accent tracking-widest uppercase mb-4">📍 <?= h($location) ?></p>
                        <?php endif; ?>
                        <h1 class="reveal d1 font-display font-bold text-5xl md:text-6xl lg:text-7xl leading-[1.05] tracking-tight text-zinc-900 dark:text-white mb-4">
                            Hi, I'm <span class="text-accent"><?= h($firstName) ?></span>
                        </h1>
                        <p class="reveal d2 text-lg md:text-xl text-zinc-600 dark:text-zinc-300 font-medium mb-4"><?= h($title) ?></p>
                        <p class="reveal d2 text-base text-zinc-500 dark:text-zinc-400 font-light leading-relaxed max-w-md mb-10"><?= h($bio) ?></p>
                        <div class="reveal d3 flex flex-wrap gap-4">
                            <a href="#work" class="shimmer inline-flex items-center gap-2 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-medium px-7 py-3.5 rounded-full hover:bg-zinc-700 dark:hover:bg-zinc-200 transition-colors text-sm">
                                View my projects
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </a>
                            <a href="#contact" class="inline-flex items-center gap-2 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 font-medium px-7 py-3.5 rounded-full hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-colors text-sm">Get in touch</a>
                        </div>
                        <div class="reveal d4 flex gap-8 mt-14 pt-8 border-t border-zinc-100 dark:border-zinc-900">
                            <div>
                                <p class="font-display font-bold text-3xl text-zinc-900 dark:text-white"><?= count($projects) ?>+</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Projects</p>
                            </div>
                            <div>
                                <p class="font-display font-bold text-3xl text-zinc-900 dark:text-white"><?= count($skills) ?>+</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Skills</p>
                            </div>
                            <div>
                                <p class="font-display font-bold text-3xl text-zinc-900 dark:text-white"><?= h($expYears) ?></p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Experience</p>
                            </div>
                        </div>
                    </div>
                    <div class="reveal d2 flex justify-center md:justify-end">
                        <div class="relative w-72 h-72 md:w-80 md:h-80 lg:w-96 lg:h-96">
                            <div class="pf w-full h-full rounded-3xl"><?= $avatarHtml ?></div>
                            <div class="absolute -bottom-4 -left-4 bg-accent text-white font-display font-bold text-sm px-4 py-2.5 rounded-2xl shadow-lg">
                                <?= h($title) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ ABOUT ═══ -->
        <section id="about" class="py-24 bg-zinc-50 dark:bg-zinc-900/40">
            <div class="max-w-6xl mx-auto px-6">
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div class="reveal order-2 md:order-1">
                        <div class="pf w-full aspect-square max-w-sm mx-auto rounded-3xl"><?= $avatarHtml ?></div>
                    </div>
                    <div class="order-1 md:order-2">
                        <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">About me</p>
                        <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-zinc-900 dark:text-white leading-tight mb-6">A bit about<br />who I am</h2>
                        <p class="reveal d2 text-zinc-500 dark:text-zinc-400 leading-relaxed mb-4"><?= nl2br(h($bio)) ?></p>
                        <?php if ($location || $phone || $email): ?>
                            <div class="reveal d3 flex flex-col gap-2 mb-6 text-sm text-zinc-500 dark:text-zinc-400">
                                <?php if ($location): ?><span>📍 <?= h($location) ?></span><?php endif; ?>
                                <?php if ($contactPhone): ?><span>📞 <?= h($contactPhone) ?></span><?php endif; ?>
                                <?php if ($contactEmail): ?><span>✉️ <?= h($contactEmail) ?></span><?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($skills)): ?>
                            <div class="reveal d4">
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-widest mb-3">Skills &amp; tools</p>
                                <div class="flex flex-wrap gap-2" role="list" aria-label="Skills"><?= $skillTagsHtml ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ EXPERIENCE ═══ -->
        <section id="experience" class="py-24">
            <div class="max-w-6xl mx-auto px-6">
                <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">Career</p>
                <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-zinc-900 dark:text-white mb-12">Work Experience</h2>
                <div class="flex flex-col gap-5"><?= $experienceHtml ?></div>
            </div>
        </section>

        <!-- ═══ PROJECTS ═══ -->
        <section id="work" class="py-24 bg-zinc-50 dark:bg-zinc-900/40">
            <div class="max-w-6xl mx-auto px-6">
                <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">Portfolio</p>
                <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-zinc-900 dark:text-white mb-12">Selected Projects</h2>
                <div class="grid md:grid-cols-2 gap-6"><?= $projectsHtml ?></div>
            </div>
        </section>

        <!-- ═══ EDUCATION ═══ -->
        <section id="education" class="py-24">
            <div class="max-w-6xl mx-auto px-6">
                <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">Background</p>
                <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-zinc-900 dark:text-white mb-12">Education</h2>
                <div class="flex flex-col gap-5"><?= $educationHtml ?></div>
            </div>
        </section>

        <?php if ($showCerts || $showAchievements): ?>
            <!-- ═══ CERTS & ACHIEVEMENTS ═══ -->
            <section id="extras" class="py-24 bg-zinc-50 dark:bg-zinc-900/40">
                <div class="max-w-6xl mx-auto px-6">
                    <?php if ($showCerts): ?>
                        <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">Credentials</p>
                        <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-zinc-900 dark:text-white mb-10">Certifications</h2>
                        <div class="grid md:grid-cols-2 gap-5 mb-16"><?= $certsHtml ?></div>
                    <?php endif; ?>
                    <?php if ($showAchievements): ?>
                        <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">Recognition</p>
                        <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-zinc-900 dark:text-white mb-10">Achievements</h2>
                        <div class="grid md:grid-cols-2 gap-5"><?= $achievementsHtml ?></div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ═══ CONTACT ═══ -->
        <section id="contact" class="py-24">
            <div class="max-w-6xl mx-auto px-6">
                <div class="bg-zinc-900 dark:bg-zinc-800 rounded-3xl p-10 md:p-16 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-accent/20 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
                    <div class="absolute bottom-0 left-0 w-40 h-40 bg-accent/10 rounded-full blur-2xl pointer-events-none" aria-hidden="true"></div>
                    <div class="relative z-10 grid md:grid-cols-2 gap-12 items-start">
                        <div>
                            <p class="reveal text-xs font-medium text-accent tracking-widest uppercase mb-3">Get in touch</p>
                            <h2 class="reveal d1 font-display font-bold text-4xl md:text-5xl text-white leading-tight mb-5">Let's work<br />together</h2>
                            <p class="reveal d2 text-zinc-400 leading-relaxed mb-8">I'm open to new opportunities. Feel free to reach out!</p>
                            <div class="reveal d3 flex flex-col gap-4"><?= $socialLinksHtml ?></div>
                        </div>
                        <?php if (!empty($profile['enable_contact_form'])): ?>
                            <div class="reveal d2">
                                <form action="contact-handler.php" method="POST" novalidate>
                                    <input type="hidden" name="portfolio_user_id" value="<?= (int)$userId ?>">
                                    <div class="flex flex-col gap-4">
                                        <div class="grid sm:grid-cols-2 gap-4">
                                            <div>
                                                <label for="fname" class="block text-xs font-medium text-zinc-400 mb-1.5">Name *</label>
                                                <input type="text" id="fname" name="name" placeholder="Jane Smith" required autocomplete="name" class="w-full bg-zinc-800 border border-zinc-700 text-white text-sm rounded-xl px-4 py-3 placeholder-zinc-600 focus:outline-none focus:border-accent transition-colors" />
                                            </div>
                                            <div>
                                                <label for="femail" class="block text-xs font-medium text-zinc-400 mb-1.5">Email *</label>
                                                <input type="email" id="femail" name="email" placeholder="jane@company.com" required autocomplete="email" class="w-full bg-zinc-800 border border-zinc-700 text-white text-sm rounded-xl px-4 py-3 placeholder-zinc-600 focus:outline-none focus:border-accent transition-colors" />
                                            </div>
                                        </div>
                                        <div>
                                            <label for="fsubject" class="block text-xs font-medium text-zinc-400 mb-1.5">Subject</label>
                                            <input type="text" id="fsubject" name="subject" placeholder="Project inquiry" class="w-full bg-zinc-800 border border-zinc-700 text-white text-sm rounded-xl px-4 py-3 placeholder-zinc-600 focus:outline-none focus:border-accent transition-colors" />
                                        </div>
                                        <div>
                                            <label for="fmessage" class="block text-xs font-medium text-zinc-400 mb-1.5">Message *</label>
                                            <textarea id="fmessage" name="message" rows="4" placeholder="Tell me about your project..." required class="w-full bg-zinc-800 border border-zinc-700 text-white text-sm rounded-xl px-4 py-3 placeholder-zinc-600 focus:outline-none focus:border-accent transition-colors resize-none"></textarea>
                                        </div>
                                        <button type="submit" class="shimmer w-full bg-accent text-white font-display font-bold text-sm py-3.5 rounded-xl hover:bg-accent-light transition-colors">Send message →</button>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="reveal d2 flex items-center justify-center">
                                <?php if ($contactEmail): ?>
                                    <a href="mailto:<?= h($contactEmail) ?>" class="shimmer inline-flex items-center gap-2 bg-accent text-white font-medium px-8 py-4 rounded-full hover:bg-accent-light transition-colors text-base">
                                        Email me →
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-zinc-100 dark:border-zinc-900">
        <div class="max-w-6xl mx-auto px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-zinc-400">© <span id="yr"></span> <?= h($name) ?>. All rights reserved.</p>
            <p class="text-xs text-zinc-500">Built with <span class="text-accent">PortfolioBuilder</span></p>
        </div>
    </footer>

    <!-- ═══ EDIT BAR (only visible when logged in — this page is always authenticated) ═══ -->
    <div class="edit-bar">
        <a href="choose-template.php" class="btn-dash">← Change Template</a>
        <a href="edit-portfolio.php" class="btn-edit">✏️ Edit Portfolio</a>
    </div>

    <script>
        function app() {
            return {
                dark: false,
                mm: false,
                sc: false,
                s: 'hero',
                init() {
                    this.dark = localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    this.$watch('dark', v => localStorage.setItem('theme', v ? 'dark' : 'light'));
                    window.addEventListener('scroll', () => {
                        this.sc = window.scrollY > 20;
                        this.updateSection();
                    }, {
                        passive: true
                    });
                    const io = new IntersectionObserver(entries => {
                        entries.forEach(e => {
                            if (e.isIntersecting) {
                                e.target.classList.add('in');
                                io.unobserve(e.target);
                            }
                        });
                    }, {
                        threshold: .1,
                        rootMargin: '0px 0px -40px 0px'
                    });
                    document.querySelectorAll('.reveal').forEach(el => io.observe(el));
                    document.getElementById('yr').textContent = new Date().getFullYear();
                },
                updateSection() {
                    const atBottom = window.innerHeight + window.scrollY >= document.body.scrollHeight - 60;
                    if (atBottom) {
                        this.s = 'contact';
                        return;
                    }
                    const ids = ['contact', 'extras', 'education', 'work', 'experience', 'about', 'hero'];
                    for (const id of ids) {
                        const el = document.getElementById(id);
                        if (el && window.scrollY >= el.offsetTop - 130) {
                            this.s = id;
                            return;
                        }
                    }
                }
            };
        }
    </script>
</body>

</html>