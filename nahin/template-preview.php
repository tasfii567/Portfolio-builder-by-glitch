<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/config/db.php';

function tp_h($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if (!function_exists('h')) {
    function h($value): string
    {
        return tp_h($value);
    }
}

function tp_fetch_all(PDO $pdo, string $table, int $userId): array
{
    $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE user_id = ? ORDER BY id ASC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function tp_social_icon(string $platform): string
{
    $icons = [
        'GitHub' => '<path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.44 9.8 8.2 11.39.6.11.8-.26.8-.58v-2.23C5.67 21.3 5 19.25 5 19.25c-.55-1.39-1.33-1.75-1.33-1.75-1.09-.75.08-.73.08-.73 1.2.08 1.84 1.24 1.84 1.24 1.07 1.83 2.8 1.3 3.49 1 .11-.78.42-1.3.76-1.6-2.67-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.12-.3-.54-1.52.12-3.18 0 0 1-.32 3.3 1.23.96-.27 1.98-.4 3-.4s2.05.13 3 .4c2.29-1.55 3.3-1.23 3.3-1.23.65 1.66.23 2.88.11 3.18.77.84 1.24 1.91 1.24 3.22 0 4.61-2.8 5.63-5.48 5.92.43.37.82 1.1.82 2.22v3.3c0 .32.19.69.8.57C20.56 21.8 24 17.3 24 12c0-6.63-5.37-12-12-12z"/>',
        'LinkedIn' => '<path d="M19 0H5C2.24 0 0 2.24 0 5v14c0 2.76 2.24 5 5 5h14c2.76 0 5-2.24 5-5V5c0-2.76-2.24-5-5-5zM8 19H5V8h3v11zM6.5 6.73A1.75 1.75 0 116.5 3.23a1.75 1.75 0 010 3.5zM19 19h-3v-5.6c0-3.37-4-3.11-4 0V19H9V8h3v1.76c1.4-2.59 7-2.78 7 2.47V19z"/>',
        'Twitter/X' => '<path d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24H16.17l-5.21-6.82L4.99 21.75H1.68l7.73-8.84L1.25 2.25H8.08l4.71 6.23z"/>',
        'Facebook' => '<path d="M24 12.07C24 5.37 18.63 0 12 0S0 5.37 0 12.07C0 18.06 4.39 23.02 10.13 23.93v-8.39H7.08v-3.47h3.05V9.43c0-3.01 1.79-4.67 4.53-4.67 1.31 0 2.69.24 2.69.24v2.95h-1.52c-1.49 0-1.96.93-1.96 1.87v2.25h3.33l-.53 3.47h-2.8v8.39C19.61 23.03 24 18.07 24 12.07z"/>',
        'Instagram' => '<path d="M12 2.16c3.2 0 3.58.01 4.85.07 3.25.15 4.77 1.69 4.92 4.92.06 1.26.07 1.64.07 4.85 0 3.2-.01 3.58-.07 4.85-.15 3.23-1.67 4.77-4.92 4.92-1.27.06-1.65.07-4.85.07-3.21 0-3.59-.01-4.85-.07-3.24-.15-4.77-1.7-4.92-4.92-.06-1.27-.07-1.65-.07-4.85 0-3.21.01-3.59.07-4.85.15-3.23 1.68-4.77 4.92-4.92 1.26-.06 1.64-.07 4.85-.07zM12 7.99a4.01 4.01 0 100 8.02 4.01 4.01 0 000-8.02zm6.41-1.14a1.44 1.44 0 10-2.88 0 1.44 1.44 0 002.88 0z"/>',
        'Portfolio Website' => '<path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.66 0 3-4.03 3-9s-1.34-9-3-9m0 18c-1.66 0-3-4.03-3-9s1.34-9 3-9m-9 9a9 9 0 019-9"/>',
    ];
    return $icons[$platform] ?? $icons['Portfolio Website'];
}

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$profile = $stmt->fetch() ?: [];

$templateId = (int) ($_GET['template_id'] ?? ($profile['selected_template_id'] ?? 0));
if ($templateId <= 0) {
    $stmt = $pdo->query("SELECT id FROM templates WHERE status = 'active' ORDER BY id ASC LIMIT 1");
    $templateId = (int) $stmt->fetchColumn();
}

$stmt = $pdo->prepare("SELECT id, name, renderer_file, status FROM templates WHERE id = ? LIMIT 1");
$stmt->execute([$templateId]);
$template = $stmt->fetch();

if (!$template || ($template['status'] ?? '') !== 'active') {
    http_response_code(404);
    echo 'Template not found.';
    exit;
}

$rendererFile = __DIR__ . '/templates/' . basename((string) $template['renderer_file']);
if (!is_file($rendererFile)) {
    http_response_code(500);
    echo 'Template renderer is missing.';
    exit;
}

$skills = tp_fetch_all($pdo, 'skills', $userId);
$projects = tp_fetch_all($pdo, 'projects', $userId);
$experience = tp_fetch_all($pdo, 'experience', $userId);
$education = tp_fetch_all($pdo, 'education', $userId);
$certifications = tp_fetch_all($pdo, 'certifications', $userId);
$achievements = tp_fetch_all($pdo, 'achievements', $userId);
$socialLinks = tp_fetch_all($pdo, 'social_links', $userId);

$name = $user['name'] ?? 'User';
$email = $user['email'] ?? '';
$title = $profile['title'] ?? 'Professional';
$bio = $profile['bio'] ?? 'Welcome to my portfolio.';
$location = $profile['location'] ?? '';
$phone = $profile['phone'] ?? '';
$avatar = !empty($profile['avatar']) ? $profile['avatar'] : null;
$contactEmail = $profile['contact_email'] ?: $email;
$contactPhone = $profile['contact_phone'] ?: $phone;
$firstName = explode(' ', trim($name))[0];
$expYears = '0';

if (!empty($experience)) {
    $earliest = null;
    foreach ($experience as $exp) {
        if (!empty($exp['start_date'])) {
            $year = (int) date('Y', strtotime($exp['start_date']));
            if ($earliest === null || $year < $earliest) {
                $earliest = $year;
            }
        }
    }
    if ($earliest) {
        $expYears = (date('Y') - $earliest) . 'y';
    }
}

$skillTagsHtml = '';
foreach ($skills as $skill) {
    $skillTagsHtml .= '<span class="stag">' . tp_h($skill['skill_name']) . '</span>';
}
if ($skillTagsHtml === '') {
    $skillTagsHtml = '<span class="stag">Add skills in Edit Portfolio</span>';
}

$experienceHtml = '';
foreach ($experience as $exp) {
    $startFmt = !empty($exp['start_date']) ? date('M Y', strtotime($exp['start_date'])) : '';
    $endFmt = !empty($exp['currently_working']) ? 'Present' : (!empty($exp['end_date']) ? date('M Y', strtotime($exp['end_date'])) : '');
    $period = trim($startFmt . ($endFmt ? ' - ' . $endFmt : ''));
    $experienceHtml .= '<article class="item"><h3>' . tp_h($exp['position']) . '</h3><p>' . tp_h($exp['company']) . '</p>' . ($period !== '' ? '<span>' . tp_h($period) . '</span>' : '') . '</article>';
}

$educationHtml = '';
foreach ($education as $edu) {
    $period = trim(($edu['start_year'] ?? '') . (!empty($edu['end_year']) ? ' - ' . $edu['end_year'] : ''));
    $educationHtml .= '<article class="item"><h3>' . tp_h($edu['institution']) . '</h3><p>' . tp_h($edu['degree']) . '</p>' . ($period !== '' ? '<span>' . tp_h($period) . '</span>' : '') . '</article>';
}

$projectsHtml = '';
foreach ($projects as $project) {
    $projectsHtml .= '<article class="item">';
    if (!empty($project['image_path'])) {
        $projectsHtml .= '<img src="' . tp_h($project['image_path']) . '" alt="">';
    }
    $projectsHtml .= '<h3>' . tp_h($project['title']) . '</h3>';
    if (!empty($project['description'])) {
        $projectsHtml .= '<p>' . nl2br(tp_h($project['description'])) . '</p>';
    }
    if (!empty($project['technologies'])) {
        $projectsHtml .= '<div class="chips">';
        foreach (array_filter(array_map('trim', explode(',', (string) $project['technologies']))) as $tech) {
            $projectsHtml .= '<span>' . tp_h($tech) . '</span>';
        }
        $projectsHtml .= '</div>';
    }
    if (!empty($project['demo_url'])) {
        $projectsHtml .= '<a href="' . tp_h($project['demo_url']) . '" target="_blank" rel="noopener">Live Demo</a>';
    }
    if (!empty($project['github_url'])) {
        $projectsHtml .= '<a href="' . tp_h($project['github_url']) . '" target="_blank" rel="noopener">GitHub</a>';
    }
    $projectsHtml .= '</article>';
}

$certsHtml = '';
foreach ($certifications as $cert) {
    $certsHtml .= '<article class="item"><h3>' . tp_h($cert['name']) . '</h3>' . (!empty($cert['issuing_org']) ? '<p>' . tp_h($cert['issuing_org']) . '</p>' : '') . '</article>';
}

$achievementsHtml = '';
foreach ($achievements as $ach) {
    $achievementsHtml .= '<article class="item"><h3>' . tp_h($ach['title']) . '</h3>' . (!empty($ach['description']) ? '<p>' . tp_h($ach['description']) . '</p>' : '') . '</article>';
}

$socialLinksHtml = '';
foreach ($socialLinks as $link) {
    $socialLinksHtml .= '<a href="' . tp_h($link['url']) . '" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true">' . tp_social_icon((string) $link['platform']) . '</svg><span>' . tp_h($link['platform']) . '</span></a>';
}
if ($contactEmail) {
    $socialLinksHtml = '<a href="mailto:' . tp_h($contactEmail) . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><span>' . tp_h($contactEmail) . '</span></a>' . $socialLinksHtml;
}

$avatarHtml = $avatar
    ? '<img src="' . tp_h($avatar) . '" alt="' . tp_h($name) . '">'
    : '<div class="avatar-fallback">' . tp_h(strtoupper(substr($name, 0, 1) . (strpos($name, ' ') !== false ? substr($name, strpos($name, ' ') + 1, 1) : ''))) . '</div>';

$showCerts = !empty($certifications);
$showAchievements = !empty($achievements);
$showOwnerControls = false;

require $rendererFile;
