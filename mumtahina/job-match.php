<?php
/**
 * job-match.php — matches your skills against 5 fixed job roles,
 * suggests the best fit and shows every role's percentage.
 */
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/inc/profile.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = (int) $_SESSION['user_id'];

$u = $pdo->prepare("SELECT * FROM users WHERE id = ?"); $u->execute([$userId]); $user = $u->fetch();
$prof = get_profile($pdo, $userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_skills') {
    $pdo->prepare("UPDATE profiles SET skills=? WHERE user_id=?")->execute([trim($_POST['skills'] ?? ''), $userId]);
    header('Location: job-match.php'); exit;
}

// --- The 5 fixed roles and the skills they look for --------------------
$roles = [
  'AI Engineer'        => ['python','machine learning','deep learning','tensorflow','pytorch','nlp','data','statistics'],
  'SQA Engineer'       => ['testing','selenium','automation','manual testing','test case','bug','qa','jira'],
  'UI/UX Designer'     => ['figma','ui','ux','design','prototyping','wireframe','user research','accessibility'],
  'Software Engineer'  => ['java','c++','algorithms','data structures','oop','git','system design','problem solving'],
  'Developer'          => ['html','css','javascript','php','mysql','react','api','git'],
];

// --- Normalise user skills into a searchable blob ----------------------
$userSkills = array_map(fn($s) => strtolower(trim($s)), skills_to_array($prof['skills'] ?? ''));
$blob = ' ' . implode(' | ', $userSkills) . ' ';

// --- Score each role (substring match is forgiving for multi-word skills)
$matches = [];
foreach ($roles as $role => $need) {
    $hits = [];
    foreach ($need as $kw) {
        if (strpos($blob, $kw) !== false) $hits[] = $kw;
    }
    $pct = count($need) ? (int) round(count($hits) / count($need) * 100) : 0;
    $matches[] = ['role' => $role, 'percent' => $pct, 'need' => $need, 'hits' => $hits];
}
usort($matches, fn($a, $b) => $b['percent'] <=> $a['percent']);
$best = $matches[0] ?? null;

$userName = $user['name']; $userRole = $user['role'];
$initials = strtoupper(substr($userName,0,1) . (strpos($userName,' ')!==false ? substr($userName,strpos($userName,' ')+1,1) : ''));
$ACTIVE = 'jobmatch';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Job Match — Portfolio Builder</title>
<link rel="stylesheet" href="assets/app.css">
<style>
.suggest{background:linear-gradient(120deg,#3a4a23,#6b8540);border:1px solid var(--line);border-radius:var(--radius);
  padding:24px 26px;margin-bottom:24px;position:relative;overflow:hidden}
.suggest::after{content:"";position:absolute;right:-50px;top:-50px;width:200px;height:200px;border-radius:50%;
  background:radial-gradient(circle,rgba(200,220,150,.45),transparent 70%)}
.suggest small{color:#dde5cd;font-size:12px;letter-spacing:.5px;text-transform:uppercase;font-weight:700}
.suggest h3{font-size:24px;margin:6px 0 4px;position:relative;z-index:1}
.suggest .big{font-size:30px;font-weight:800;color:#fff}
.suggest p{color:#dde5cd;font-size:14px;position:relative;z-index:1}
.match{margin-bottom:20px}
.match:last-child{margin-bottom:0}
.match .row{display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px;align-items:center}
.match .row b{font-weight:600}
.match .row .pct{color:var(--primary-soft);font-weight:700;font-size:15px}
.match.lead-role .row b::after{content:" ⭐ best fit";color:var(--accent);font-size:12px;font-weight:700}
.bar{height:10px;background:var(--surface-2);border-radius:20px;overflow:hidden}
.bar i{display:block;height:100%;border-radius:20px;background:linear-gradient(90deg,var(--primary),var(--accent));transition:width .5s}
.have{margin-top:8px;display:flex;flex-wrap:wrap;gap:6px}
.tagpill{font-size:11px;font-weight:600;padding:3px 9px;border-radius:14px;border:1px solid var(--line)}
.tagpill.on{background:rgba(46,204,113,.14);color:var(--good);border-color:rgba(46,204,113,.3)}
.tagpill.off{background:var(--surface-2);color:var(--muted)}
</style>
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/inc/sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:14px">
        <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
        <div><h2>Job Match</h2><p>Which of the 5 roles fits your skills best.</p></div>
      </div>
      <div class="profile">
        <div class="who"><b><?= htmlspecialchars($userName) ?></b><small><?= htmlspecialchars($userRole) ?></small></div>
        <div class="avatar"><?= htmlspecialchars($initials) ?></div>
      </div>
    </div>

    <?php if ($best && $best['percent'] > 0): ?>
    <section class="suggest">
      <small>★ Suggested position</small>
      <h3><span class="big"><?= htmlspecialchars($best['role']) ?></span></h3>
      <p>Your skills match this role <b><?= $best['percent'] ?>%</b> — your strongest fit of the five.</p>
    </section>
    <?php endif; ?>

    <section class="panel">
      <h3>🛠 Your Skills</h3>
      <p class="lead">Edit your skills (comma separated). Matches recalculate when you save.</p>
      <form method="post">
        <input type="hidden" name="action" value="save_skills">
        <div class="field full">
          <textarea name="skills" placeholder="Python, Machine Learning, SQL, HTML, CSS, JavaScript, Figma, Testing"><?= htmlspecialchars($prof['skills'] ?? '') ?></textarea>
        </div>
        <div class="form-actions"><button class="btn btn-primary" type="submit">Update matches</button></div>
      </form>
    </section>

    <h3 class="section-title">📊 All 5 Roles</h3>
    <section class="panel">
      <?php if (!$userSkills): ?>
        <p class="lead" style="margin:0">Add some skills above (or in <a href="edit-profile.php" style="color:var(--primary-soft)">Edit Profile</a>) to see your matches.</p>
      <?php else: ?>
        <?php foreach ($matches as $idx => $m): ?>
        <div class="match <?= $idx === 0 && $m['percent'] > 0 ? 'lead-role' : '' ?>">
          <div class="row"><b><?= htmlspecialchars($m['role']) ?></b><span class="pct"><?= $m['percent'] ?>%</span></div>
          <div class="bar"><i style="width:<?= $m['percent'] ?>%"></i></div>
          <div class="have">
            <?php foreach ($m['need'] as $kw): $has = in_array($kw, $m['hits'], true); ?>
              <span class="tagpill <?= $has ? 'on' : 'off' ?>"><?= $has ? '✓ ' : '' ?><?= htmlspecialchars($kw) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </main>
</div>
</body>
</html>
