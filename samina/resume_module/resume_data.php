<?php

/**
 * resume_data.php — pulls real user data from the DB.
 * Included by resume_preview.php and export_pdf.php.
 * Populates the same six variables the dummy file used.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // consumers (resume_preview / export_pdf) should handle the redirect,
    // but guard here just in case this file is included directly.
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../../nahin/config/db.php';
$userId = $_SESSION['user_id'];

// ── Fetch base user row ───────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userRow = $stmt->fetch();

if (!$userRow) {
    header("Location: login.php");
    exit;
}

// ── Fetch profile row ─────────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch() ?: [];

// ── Fetch social links — pick out LinkedIn, GitHub, portfolio ─────────────────
$stmt = $pdo->prepare("SELECT platform, url FROM social_links WHERE user_id = ?");
$stmt->execute([$userId]);
$socialRows = $stmt->fetchAll();

$linkedin  = '';
$github    = '';
$portfolio = '';
foreach ($socialRows as $s) {
    switch ($s['platform']) {
        case 'LinkedIn':
            $linkedin  = $s['url'];
            break;
        case 'GitHub':
            $github    = $s['url'];
            break;
        case 'Portfolio Website':
            $portfolio = $s['url'];
            break;
    }
}

// ── Build $user ───────────────────────────────────────────────────────────────
$user = [
    'name'      => $userRow['name']               ?? '',
    'location'  => $profile['location']           ?? '',
    'phone'     => $profile['phone']              ?? '',
    'email'     => $profile['contact_email'] ?: ($userRow['email'] ?? ''),
    'linkedin'  => $linkedin,
    'github'    => $github,
    'portfolio' => $portfolio,
    'summary'   => $profile['bio']                ?? '',
];

// ── Build $education ──────────────────────────────────────────────────────────
$stmt = $pdo->prepare(
    "SELECT institution, degree, field_of_study, start_year, end_year, gpa
     FROM education WHERE user_id = ? ORDER BY id ASC"
);
$stmt->execute([$userId]);
$eduRows = $stmt->fetchAll();

$education = [];
foreach ($eduRows as $e) {
    // "Degree in Field" label
    $degreeLabel = trim($e['degree'] . ($e['field_of_study'] ? ' in ' . $e['field_of_study'] : ''));

    // duration string  e.g. "2020 – 2024" or "2022 – Present"
    $start    = $e['start_year'] ?? '';
    $end      = $e['end_year']   ?? '';
    $duration = trim($start . ($end ? ' – ' . $end : ($start ? ' – Present' : '')));

    $score = $e['gpa'] ? 'CGPA: ' . $e['gpa'] : '';

    $education[] = [
        'degree'      => $degreeLabel,
        'institution' => $e['institution'],
        'duration'    => $duration,
        'score'       => $score,
        'notes'       => [],   // edit-portfolio has no thesis/notes field; keep array contract
    ];
}

// ── Build $skills ─────────────────────────────────────────────────────────────
// Group by level (Expert / Intermediate / Beginner) so the resume has labelled
// categories — mirrors the dummy file's structure.
$stmt = $pdo->prepare(
    "SELECT skill_name, level FROM skills WHERE user_id = ? ORDER BY
     FIELD(level,'Expert','Intermediate','Beginner'), skill_name ASC"
);
$stmt->execute([$userId]);
$skillRows = $stmt->fetchAll();

$skillGroups = [];
foreach ($skillRows as $sk) {
    $lvl = $sk['level'] ?: 'Other';
    $skillGroups[$lvl][] = $sk['skill_name'];
}

$skills = [];
foreach ($skillGroups as $category => $items) {
    $skills[] = [
        'category' => $category,
        'items'    => implode(', ', $items),
    ];
}

// ── Build $projects ───────────────────────────────────────────────────────────
$stmt = $pdo->prepare(
    "SELECT title, description, technologies, demo_url, github_url
     FROM projects WHERE user_id = ? ORDER BY featured DESC, id ASC"
);
$stmt->execute([$userId]);
$projRows = $stmt->fetchAll();

$projects = [];
foreach ($projRows as $p) {
    $links = [];
    if (!empty($p['github_url'])) {
        $links[] = ['label' => 'GitHub', 'url' => $p['github_url']];
    }
    if (!empty($p['demo_url'])) {
        $links[] = ['label' => 'Live',   'url' => $p['demo_url']];
    }

    // Split description into bullet points on newlines; fall back to single bullet
    $bullets = [];
    if (!empty($p['description'])) {
        $lines = array_filter(array_map('trim', explode("\n", $p['description'])));
        $bullets = array_values($lines);
    }

    $projects[] = [
        'title'   => $p['title'],
        'tech'    => $p['technologies'] ?? '',
        'links'   => $links,
        'bullets' => $bullets,
    ];
}

// ── Build $certifications ─────────────────────────────────────────────────────
$stmt = $pdo->prepare(
    "SELECT name, issuing_org, date_issued FROM certifications WHERE user_id = ? ORDER BY id ASC"
);
$stmt->execute([$userId]);
$certRows = $stmt->fetchAll();

$certifications = [];
foreach ($certRows as $c) {
    $desc = $c['issuing_org'] ?? '';
    if (!empty($c['date_issued'])) {
        $desc .= ($desc ? ' · ' : '') . date('M Y', strtotime($c['date_issued']));
    }
    $certifications[] = [
        'title'       => $c['name'],
        'description' => $desc,
    ];
}

// ── Build $volunteering from achievements ─────────────────────────────────────
// The edit-portfolio form has no dedicated volunteering section, so we map
// achievements → volunteering.  Each achievement becomes one volunteering entry.
$stmt = $pdo->prepare(
    "SELECT title, description, achieved_on FROM achievements WHERE user_id = ? ORDER BY id ASC"
);
$stmt->execute([$userId]);
$achRows = $stmt->fetchAll();

$volunteering = [];
foreach ($achRows as $a) {
    $bullets = [];
    if (!empty($a['description'])) {
        $lines   = array_filter(array_map('trim', explode("\n", $a['description'])));
        $bullets = array_values($lines);
    }
    $volunteering[] = [
        'role'         => $a['title'],
        'organization' => '',
        'year'         => !empty($a['achieved_on']) ? date('Y', strtotime($a['achieved_on'])) : '',
        'bullets'      => $bullets,
    ];
}
