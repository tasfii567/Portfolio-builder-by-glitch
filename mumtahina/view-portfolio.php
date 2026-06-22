<?php
/**
 * view-portfolio.php — the public, shareable portfolio page.
 *   URL:  view-portfolio.php?u=<slug>
 * Loads the owner's profile + projects, counts the visit, then renders
 * whichever of the 5 templates they picked (files in /templates).
 */
require __DIR__ . '/db.php';
require __DIR__ . '/inc/profile.php';
require __DIR__ . '/inc/templates.php';

// --- Resolve the portfolio by slug -------------------------------------
$slug = trim($_GET['u'] ?? '');
$portfolio = null;
if ($slug !== '') {
    $stmt = $pdo->prepare("SELECT * FROM portfolios WHERE slug = ?");
    $stmt->execute([$slug]);
    $portfolio = $stmt->fetch();
}
if (!$portfolio) {
    http_response_code(404);
    echo '<!doctype html><meta charset="utf-8"><title>Not found</title>'
       . '<body style="background:#0f1117;color:#e8eaf0;font-family:system-ui;display:grid;place-items:center;height:100vh;margin:0">'
       . '<div style="text-align:center"><h1 style="font-size:60px;margin:0">404</h1>'
       . '<p style="color:#9aa3b5">This portfolio doesn\'t exist or the link is wrong.</p></div>';
    exit;
}
$userId = (int) $portfolio['user_id'];

// --- Count this visit --------------------------------------------------
$pdo->prepare("INSERT INTO visitors (portfolio_slug, ip) VALUES (?,?)")
    ->execute([$slug, $_SERVER['REMOTE_ADDR'] ?? null]);
$vc = $pdo->prepare("SELECT COUNT(*) FROM visitors WHERE portfolio_slug = ?");
$vc->execute([$slug]);
$visitCount = (int) $vc->fetchColumn();

// --- Owner info (users + profiles) -------------------------------------
$uu = $pdo->prepare("SELECT name, email, role FROM users WHERE id = ?");
$uu->execute([$userId]); $uRow = $uu->fetch() ?: ['name'=>$portfolio['owner_name'],'email'=>'','role'=>$portfolio['owner_role']];
$prof = get_profile($pdo, $userId);

$owner = [
    'name'     => $portfolio['owner_name'] ?: ($uRow['name'] ?: 'Portfolio'),
    'role'     => $portfolio['owner_role'] ?: ($uRow['role'] ?: ''),
    'email'    => $uRow['email'] ?? '',
    'image'    => $prof['image'] ?? '',
    'github'   => $prof['github'] ?? '',
    'website'  => $prof['website'] ?? '',
    'location' => $prof['location'] ?? '',
    'summary'  => $prof['summary'] ?? '',
    'skills'   => skills_to_array($prof['skills'] ?? ''),
];
$owner['initials'] = strtoupper(substr($owner['name'],0,1) . (strpos($owner['name'],' ')!==false ? substr($owner['name'],strpos($owner['name'],' ')+1,1) : ''));

// --- Projects + images -------------------------------------------------
$ps = $pdo->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC");
$ps->execute([$userId]);
$projects = $ps->fetchAll();
foreach ($projects as &$pr) {
    $im = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ?");
    $im->execute([$pr['id']]);
    $pr['images'] = $im->fetchAll(PDO::FETCH_COLUMN);
    $pr['cover']  = $pr['images'][0] ?? null;
}
unset($pr);

// --- Render the chosen template ----------------------------------------
$tplKey  = valid_template($portfolio['template'] ?? 'midnight');
$tplFile = __DIR__ . '/templates/' . $tplKey . '.php';
if (!is_file($tplFile)) $tplFile = __DIR__ . '/templates/midnight.php';

// $owner, $projects, $visitCount are available inside the template.
include $tplFile;
