<?php
/**
 * Portfolio Builder — User Dashboard (PortfolioBuilder light theme)
 */
session_start();

// --- Auth guard --------------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userName = htmlspecialchars($_SESSION['user_name'] ?? 'User');
$userRole = htmlspecialchars($_SESSION['user_role'] ?? 'Member');
$initials = strtoupper(substr($userName, 0, 1) . (strpos($userName, ' ') !== false ? substr($userName, strpos($userName, ' ') + 1, 1) : ''));

require __DIR__ . '/db.php';
$userId = (int) $_SESSION['user_id'];

// --- Make sure this user has a portfolio + shareable slug --------------
$pf = $pdo->prepare("SELECT * FROM portfolios WHERE user_id = ?");
$pf->execute([$userId]);
$portfolio = $pf->fetch();
if (!$portfolio) {
    $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $_SESSION['user_name'] ?? 'portfolio'));
    $base = trim($base, '-') ?: 'portfolio';
    $slug = $base . '-' . bin2hex(random_bytes(3));
    $pdo->prepare("INSERT INTO portfolios (user_id, slug, title, owner_name, owner_role) VALUES (?,?,?,?,?)")
        ->execute([$userId, $slug, ($_SESSION['user_name'] ?? 'My') . "'s Portfolio", $_SESSION['user_name'] ?? '', $_SESSION['user_role'] ?? '']);
    $pf->execute([$userId]);
    $portfolio = $pf->fetch();
}

// --- Live numbers ------------------------------------------------------
$cp = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ?");
$cp->execute([$userId]);
$projectCount = (int) $cp->fetchColumn();

$ci = $pdo->prepare("SELECT COUNT(*) FROM project_images pi JOIN projects p ON p.id = pi.project_id WHERE p.user_id = ?");
$ci->execute([$userId]);
$imageCount = (int) $ci->fetchColumn();

$cv = $pdo->prepare("SELECT COUNT(*) FROM visitors WHERE portfolio_slug = ?");
$cv->execute([$portfolio['slug']]);
$visitCount = (int) $cv->fetchColumn();

$cln = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ? AND (github_link <> '' OR live_link <> '')");
$cln->execute([$userId]);
$hasLinks = (int) $cln->fetchColumn() > 0;

$stats = [
    ['label' => 'Projects',      'value' => $projectCount, 'icon' => '📁'],
    ['label' => 'Images',        'value' => $imageCount,   'icon' => '🖼'],
    ['label' => 'Profile Views', 'value' => $visitCount,   'icon' => '👁'],
    ['label' => 'Templates',     'value' => 5,             'icon' => '🎨'],
];

$scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$dir      = rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/');
$shareUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $dir . '/view-portfolio.php?u=' . urlencode($portfolio['slug']);

$jobMatches = [
    ['role' => 'AI Engineer',        'percent' => 92],
    ['role' => 'Software Engineer',  'percent' => 78],
    ['role' => 'Developer',          'percent' => 64],
    ['role' => 'UI/UX Designer',     'percent' => 55],
];

$checklist = [
    ['task' => 'Complete your profile details',     'done' => true],
    ['task' => 'Create your first project',         'done' => $projectCount > 0],
    ['task' => 'Upload project images',             'done' => $imageCount > 0],
    ['task' => 'Add GitHub / live demo links',      'done' => $hasLinks],
    ['task' => 'Share your portfolio link',         'done' => $visitCount > 0],
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
:root{
  --bg:#f4f1ea;--surface:#fff;--surface-2:#efeade;--line:#e4ddcc;
  --text:#1d211a;--muted:#797f6f;--primary:#3a4a23;--primary-soft:#5c7038;
  --accent:#7d9e58;--good:#5c8a3a;--radius:14px;--shadow:0 8px 24px rgba(40,45,30,.07);
  font-family:"Plus Jakarta Sans","Segoe UI",system-ui,-apple-system,Roboto,Arial,sans-serif;
}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);min-height:100vh}
a{text-decoration:none;color:inherit}
.app{display:grid;grid-template-columns:260px 1fr;min-height:100vh}

/* Sidebar */
.sidebar{background:var(--surface);border-right:1px solid var(--line);padding:24px 18px;
  display:flex;flex-direction:column;gap:8px;position:sticky;top:0;height:100vh}
.brand{display:flex;align-items:center;gap:12px;padding:6px 8px 22px}
.brand .logo{width:40px;height:40px;border-radius:11px;background:var(--primary);
  display:grid;place-items:center;font-weight:800;font-size:18px;color:#fff}
.brand h1{font-size:16px;line-height:1.2;font-weight:800}
.brand h1 .g{color:var(--accent)}
.brand span{font-size:11px;color:var(--muted);letter-spacing:.5px}
.nav{display:flex;flex-direction:column;gap:4px;margin-top:6px}
.nav-label{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);padding:14px 12px 6px}
.nav a{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:10px;color:var(--muted);
  font-size:14px;font-weight:600;transition:.18s}
.nav a .ic{width:20px;text-align:center;font-size:16px}
.nav a:hover{background:var(--surface-2);color:var(--text)}
.nav a.active{background:var(--primary);color:#fff}
.logout{margin-top:auto}
.logout a{display:flex;align-items:center;gap:12px;justify-content:center;padding:12px;border-radius:10px;
  font-weight:700;font-size:14px;background:#f3e3df;color:#a8442f;border:1px solid #ecc9c1;transition:.18s}
.logout a:hover{background:#eed6d0}

/* Main */
.main{padding:28px 34px;overflow-x:hidden}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:26px}
.topbar h2{font-size:24px;font-weight:800;letter-spacing:-.3px}
.topbar p{font-size:13px;color:var(--muted);margin-top:2px}
.profile{display:flex;align-items:center;gap:12px}
.profile .avatar{width:42px;height:42px;border-radius:50%;background:var(--primary);
  display:grid;place-items:center;font-weight:700;color:#fff}
.profile .who{text-align:right}
.profile .who b{font-size:14px;display:block}
.profile .who small{font-size:12px;color:var(--muted)}

/* Welcome hero */
.hero{position:relative;overflow:hidden;background:linear-gradient(120deg,#3a4a23,#4e6230 55%,#6b8540);
  border:1px solid #e4ddcc;border-radius:var(--radius);padding:36px 38px;margin-bottom:28px;box-shadow:var(--shadow)}
.hero::after{content:"";position:absolute;right:-60px;top:-60px;width:240px;height:240px;border-radius:50%;
  background:radial-gradient(circle,rgba(200,220,150,.4),transparent 70%)}
.hero h3{font-size:26px;margin-bottom:10px;color:#f3f5ec;font-weight:800;position:relative;z-index:1}
.hero p{color:#dde5cd;max-width:560px;line-height:1.6;font-size:15px;position:relative;z-index:1}
.hero .cta{margin-top:20px;display:inline-flex;align-items:center;gap:8px;background:#fff;color:#3a4a23;
  font-weight:700;font-size:14px;padding:12px 22px;border-radius:30px;transition:.18s;position:relative;z-index:1}
.hero .cta:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.18)}

/* Stat cards */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px}
.stat{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:18px 20px;
  display:flex;align-items:center;gap:14px;box-shadow:var(--shadow)}
.stat .emoji{font-size:24px;width:46px;height:46px;border-radius:12px;background:#edf1e4;display:grid;place-items:center}
.stat b{font-size:22px;display:block;font-weight:800}
.stat span{font-size:12px;color:var(--muted)}

.section-title{font-size:16px;font-weight:800;margin:6px 0 16px;display:flex;align-items:center;gap:8px}

/* Panels */
.panel{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:26px;margin-bottom:24px;box-shadow:var(--shadow)}
.match{margin-bottom:18px}.match:last-child{margin-bottom:0}
.match .row{display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px}
.match .row b{font-weight:600}
.match .row .pct{color:var(--primary);font-weight:700}
.bar{height:10px;background:var(--surface-2);border-radius:20px;overflow:hidden}
.bar i{display:block;height:100%;border-radius:20px;background:linear-gradient(90deg,var(--primary),var(--accent))}


/* Checklist */
.checklist .cl-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;font-size:14px}
.checklist .cl-head .pct{color:var(--primary);font-weight:700}
.checklist .cl-bar{height:10px;background:var(--surface-2);border-radius:20px;overflow:hidden;margin-bottom:22px}
.checklist .cl-bar i{display:block;height:100%;border-radius:20px;background:linear-gradient(90deg,var(--primary),var(--accent))}
.cl-item{display:flex;align-items:center;gap:12px;padding:11px 0;font-size:14px;border-bottom:1px solid var(--line)}
.cl-item:last-child{border-bottom:none}
.cl-item .tick{width:24px;height:24px;border-radius:50%;display:grid;place-items:center;font-size:12px;flex-shrink:0}
.cl-item .tick.on{background:#e7efd9;color:var(--good);border:1px solid #cfe0b6}
.cl-item .tick.off{background:var(--surface-2);color:var(--muted);border:1px solid var(--line)}
.cl-item.done span{color:var(--muted);text-decoration:line-through}

/* Share row */
.share{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.share input{flex:1;min-width:220px;background:var(--surface);border:1px solid var(--line);color:var(--text);
  padding:12px 14px;border-radius:10px;font-size:14px}
.sbtn{cursor:pointer;border:none;font-weight:700;font-size:14px;padding:12px 18px;border-radius:10px;transition:.18s;display:inline-grid;place-items:center;font-family:inherit}
.sbtn.solid{background:var(--primary);color:#fff}
.sbtn.solid:hover{background:#2f3d1c;transform:translateY(-1px)}
.sbtn.ghost{background:transparent;border:1px solid var(--line);color:var(--text)}
.sbtn.ghost:hover{border-color:var(--primary)}
.share-hint{font-size:12px;color:var(--muted);margin-top:10px}

/* Responsive */
.menu-btn{display:none}
@media(max-width:980px){.stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:760px){
  .app{grid-template-columns:1fr}
  .sidebar{position:fixed;left:-280px;z-index:50;transition:.25s;width:260px}
  .sidebar.open{left:0}
  .menu-btn{display:grid;place-items:center;width:42px;height:42px;border-radius:10px;
    background:var(--surface);border:1px solid var(--line);color:var(--text);font-size:20px;cursor:pointer}
  .main{padding:20px}
  .stats{grid-template-columns:1fr}
  .profile .who{display:none}
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
      <a href="Dashboard.php" class="active"><span class="ic">🏠</span> Dashboard</a>
      <a href="edit-profile.php"><span class="ic">👤</span> Edit Profile</a>
      <a href="templates.php"><span class="ic">🎨</span> Choose Template</a>
      <a href="create-portfolio.php"><span class="ic">📁</span> Create Portfolio</a>
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
          <h2>Dashboard</h2>
          <p>Welcome back, <?= $userName ?> 👋</p>
        </div>
      </div>
      <div class="profile">
        <div class="who"><b><?= $userName ?></b><small><?= $userRole ?></small></div>
        <div class="avatar"><?= $initials ?></div>
      </div>
    </div>

    <section class="hero">
      <h3>Welcome to PortfolioBuilder</h3>
      <p>Create a portfolio and get an amazing experience. Showcase your work, build a professional resume, and discover which jobs match your skills — all in one place.</p>
      <a href="edit-profile.php" class="cta">＋ Start Building Your Portfolio</a>
    </section>

    <section class="stats">
      <?php foreach ($stats as $s): ?>
      <div class="stat">
        <div class="emoji"><?= $s['icon'] ?></div>
        <div><b><?= $s['value'] ?></b><span><?= $s['label'] ?></span></div>
      </div>
      <?php endforeach; ?>
    </section>

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

    <h3 class="section-title">📊 Jobs Matched to Your Skills</h3>
    <section class="panel">
      <?php foreach ($jobMatches as $m): ?>
      <div class="match">
        <div class="row"><b><?= htmlspecialchars($m['role']) ?></b><span class="pct"><?= (int)$m['percent'] ?>%</span></div>
        <div class="bar"><i style="width:<?= (int)$m['percent'] ?>%"></i></div>
      </div>
      <?php endforeach; ?>
    </section>

  </main>
</div>
<script>
function copyUrl(e){
  var f = document.getElementById('shareUrl');
  f.select(); f.setSelectionRange(0, 99999);
  navigator.clipboard.writeText(f.value).then(function(){
    var b = e.target, t = b.textContent;
    b.textContent = 'Copied!'; setTimeout(function(){ b.textContent = t; }, 1400);
  });
}
</script>
</body>
</html>