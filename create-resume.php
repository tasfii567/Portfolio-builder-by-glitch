<?php
/**
 * create-resume.php — Step 4. Builds a standard CV from your profile + projects
 * and lets you DOWNLOAD it as a real PDF (uses html2pdf.js — no server setup).
 */
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/inc/profile.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = (int) $_SESSION['user_id'];

$u = $pdo->prepare("SELECT * FROM users WHERE id = ?"); $u->execute([$userId]); $user = $u->fetch();
$prof = get_profile($pdo, $userId);

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_resume') {
    $pdo->prepare("UPDATE profiles SET summary=?, skills=?, experience=?, education=? WHERE user_id=?")
        ->execute([trim($_POST['summary'] ?? ''), trim($_POST['skills'] ?? ''),
                   trim($_POST['experience'] ?? ''), trim($_POST['education'] ?? ''), $userId]);
    header('Location: create-resume.php?saved=1'); exit;
}
if (isset($_GET['saved'])) $notice = 'Resume details saved.';

$pr = $pdo->prepare("SELECT title, description, github_link, live_link FROM projects WHERE user_id = ? ORDER BY created_at DESC");
$pr->execute([$userId]); $projects = $pr->fetchAll();

$skillsArr = skills_to_array($prof['skills'] ?? '');
$safeName  = preg_replace('/[^A-Za-z0-9]+/', '-', $user['name']);

$userName = $user['name']; $userRole = $user['role'];
$initials = strtoupper(substr($userName,0,1) . (strpos($userName,' ')!==false ? substr($userName,strpos($userName,' ')+1,1) : ''));
$ACTIVE = 'resume';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Resume — Portfolio Builder</title>
<link rel="stylesheet" href="assets/app.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style>
.steps{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:22px}
.step{font-size:12px;font-weight:600;color:var(--muted);background:var(--surface);border:1px solid var(--line);padding:8px 14px;border-radius:20px}
.step.on{color:#fff;background:linear-gradient(135deg,var(--primary),var(--accent));border-color:transparent}
.resume-wrap{display:grid;grid-template-columns:1fr 1.05fr;gap:24px}
@media(max-width:980px){.resume-wrap{grid-template-columns:1fr}}
/* Printable CV sheet (A4 feel) */
.sheet{background:#fff;color:#1b1f2a;border-radius:var(--radius);padding:36px 40px;box-shadow:var(--shadow)}
.sheet .r-head{display:flex;align-items:center;gap:18px;border-bottom:3px solid #6c5ce7;padding-bottom:16px;margin-bottom:18px}
.sheet .r-photo{width:74px;height:74px;border-radius:50%;object-fit:cover;border:2px solid #ece9fb;flex-shrink:0}
.sheet .r-photo.ph{background:#6c5ce7;color:#fff;display:grid;place-items:center;font-weight:700;font-size:26px}
.sheet h1{font-size:25px;color:#241b4a;line-height:1.1}
.sheet .r-role{color:#6c5ce7;font-weight:700;font-size:14px;margin-top:3px}
.sheet .r-contact{font-size:11.5px;color:#5a6275;margin-top:7px;display:flex;gap:12px;flex-wrap:wrap}
.sheet h2{font-size:13px;text-transform:uppercase;letter-spacing:1px;color:#241b4a;margin:18px 0 8px;border-bottom:1px solid #e6e9f2;padding-bottom:5px}
.sheet p,.sheet li{font-size:12.5px;line-height:1.6;color:#333a4d}
.sheet pre{white-space:pre-wrap;font-family:inherit;font-size:12.5px;color:#333a4d;line-height:1.6}
.sheet .chips{display:flex;flex-wrap:wrap;gap:7px}
.sheet .chip{font-size:11.5px;font-weight:600;background:#f0eefc;color:#6c5ce7;border:1px solid #ddd6f7;padding:4px 11px;border-radius:20px}
.sheet .proj-item{margin-bottom:9px}
.sheet .proj-item b{font-size:12.5px;color:#241b4a}
.sheet .proj-item small{color:#6c5ce7}
.dl-row{display:flex;justify-content:flex-end;gap:10px;margin-bottom:14px;flex-wrap:wrap}
</style>
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/inc/sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:14px">
        <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
        <div><h2>Create Resume / CV</h2><p>Step 4 — your CV builds live; download it as PDF.</p></div>
      </div>
      <div class="profile">
        <div class="who"><b><?= htmlspecialchars($userName) ?></b><small><?= htmlspecialchars($userRole) ?></small></div>
        <div class="avatar"><?= htmlspecialchars($initials) ?></div>
      </div>
    </div>

    <div class="steps">
      <a href="edit-profile.php" class="step">1 · Profile</a>
      <a href="templates.php" class="step">2 · Choose Template</a>
      <a href="create-portfolio.php" class="step">3 · Create Portfolio</a>
      <span class="step on">4 · Resume / CV</span>
    </div>

    <?php if ($notice): ?><div class="notice">✅ <?= htmlspecialchars($notice) ?></div><?php endif; ?>

    <div class="resume-wrap">
      <!-- Editor -->
      <section class="panel editor">
        <h3>📝 Resume Details</h3>
        <p class="lead">Name, photo, title, email and GitHub come from your profile — edit those in <a href="edit-profile.php" style="color:var(--primary-soft)">Edit Profile</a>.</p>
        <form method="post">
          <input type="hidden" name="action" value="save_resume">
          <div class="field full" style="margin-bottom:14px"><label>Professional summary</label>
            <textarea name="summary" placeholder="2–3 lines about your experience and goals…"><?= htmlspecialchars($prof['summary'] ?? '') ?></textarea></div>
          <div class="field full" style="margin-bottom:14px"><label>Skills (comma separated)</label>
            <textarea name="skills" placeholder="Python, SQL, HTML, CSS, JavaScript"><?= htmlspecialchars($prof['skills'] ?? '') ?></textarea></div>
          <div class="field full" style="margin-bottom:14px"><label>Experience</label>
            <textarea name="experience" style="min-height:120px" placeholder="Job title — Company (2023–2024)&#10;• What you did…"><?= htmlspecialchars($prof['experience'] ?? '') ?></textarea></div>
          <div class="field full" style="margin-bottom:14px"><label>Education</label>
            <textarea name="education" placeholder="B.Sc in Computer Science — University (2020–2024)"><?= htmlspecialchars($prof['education'] ?? '') ?></textarea></div>
          <div class="form-actions">
            <a class="btn btn-ghost" href="job-match.php">See Job Matches →</a>
            <button class="btn btn-primary" type="submit">Save resume</button>
          </div>
        </form>
      </section>

      <!-- Live CV preview -->
      <div>
        <div class="dl-row">
          <button class="btn btn-ghost" onclick="window.print()">🖨 Print</button>
          <button class="btn btn-primary" id="dlBtn" onclick="downloadPDF()">⬇ Download CV (PDF)</button>
        </div>
        <section class="sheet" id="cvSheet">
          <div class="r-head">
            <?php if (!empty($prof['image'])): ?>
              <img class="r-photo" src="<?= htmlspecialchars($prof['image']) ?>" alt="">
            <?php else: ?>
              <div class="r-photo ph"><?= htmlspecialchars($initials) ?></div>
            <?php endif; ?>
            <div>
              <h1><?= htmlspecialchars($userName) ?></h1>
              <div class="r-role"><?= htmlspecialchars($userRole) ?></div>
              <div class="r-contact">
                <span><?= htmlspecialchars($user['email']) ?></span>
                <?php if ($prof['phone']):    ?><span><?= htmlspecialchars($prof['phone']) ?></span><?php endif; ?>
                <?php if ($prof['location']): ?><span><?= htmlspecialchars($prof['location']) ?></span><?php endif; ?>
                <?php if ($prof['github']):   ?><span><?= htmlspecialchars($prof['github']) ?></span><?php endif; ?>
                <?php if ($prof['website']):  ?><span><?= htmlspecialchars($prof['website']) ?></span><?php endif; ?>
              </div>
            </div>
          </div>

          <?php if (trim($prof['summary'] ?? '')): ?>
            <h2>Summary</h2><p><?= nl2br(htmlspecialchars($prof['summary'])) ?></p>
          <?php endif; ?>

          <?php if ($skillsArr): ?>
            <h2>Skills</h2>
            <div class="chips"><?php foreach ($skillsArr as $s): ?><span class="chip"><?= htmlspecialchars($s) ?></span><?php endforeach; ?></div>
          <?php endif; ?>

          <?php if (trim($prof['experience'] ?? '')): ?>
            <h2>Experience</h2><pre><?= htmlspecialchars($prof['experience']) ?></pre>
          <?php endif; ?>

          <?php if (trim($prof['education'] ?? '')): ?>
            <h2>Education</h2><pre><?= htmlspecialchars($prof['education']) ?></pre>
          <?php endif; ?>

          <?php if ($projects): ?>
            <h2>Projects</h2>
            <?php foreach ($projects as $pj): ?>
              <div class="proj-item">
                <b><?= htmlspecialchars($pj['title']) ?></b>
                <?php if ($pj['github_link'] || $pj['live_link']): ?>
                  <small> — <?= $pj['github_link'] ? 'GitHub' : '' ?><?= ($pj['github_link'] && $pj['live_link']) ? ' · ' : '' ?><?= $pj['live_link'] ? 'Live Demo' : '' ?></small>
                <?php endif; ?>
                <?php if (trim($pj['description'])): ?><p><?= nl2br(htmlspecialchars($pj['description'])) ?></p><?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </section>
      </div>
    </div>
  </main>
</div>
<script>
function downloadPDF(){
  var el = document.getElementById('cvSheet');
  var btn = document.getElementById('dlBtn');
  var label = btn.textContent;
  btn.textContent = 'Preparing…'; btn.disabled = true;
  var opt = {
    margin:       [10,10,10,10],
    filename:     '<?= $safeName ?>-CV.pdf',
    image:        { type:'jpeg', quality:0.98 },
    html2canvas:  { scale:2, useCORS:true, backgroundColor:'#ffffff' },
    jsPDF:        { unit:'mm', format:'a4', orientation:'portrait' },
    pagebreak:    { mode:['avoid-all','css','legacy'] }
  };
  html2pdf().set(opt).from(el).save().then(function(){
    btn.textContent = label; btn.disabled = false;
  }).catch(function(){
    btn.textContent = label; btn.disabled = false;
    alert('Could not generate the PDF. You can use the Print button and choose "Save as PDF".');
  });
}
</script>
</body>
</html>
