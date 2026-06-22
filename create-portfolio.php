<?php
/**
 * create-portfolio.php — where a logged-in user builds their portfolio.
 *  - Add / list / delete projects (CRUD)
 *  - Upload one or more images per project (File Storage)
 *  - Attach GitHub + Live Demo links
 *  - See & copy the shareable portfolio URL
 */

session_start();
require __DIR__ . '/db.php';

// --- Auth guard --------------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId   = (int) $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'Member';

// --- Make sure this user has a portfolio + shareable slug --------------
$stmt = $pdo->prepare("SELECT * FROM portfolios WHERE user_id = ?");
$stmt->execute([$userId]);
$portfolio = $stmt->fetch();

if (!$portfolio) {
    // build a readable, unique slug: name-XXXX
    $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $userName));
    $base = trim($base, '-') ?: 'portfolio';
    $slug = $base . '-' . bin2hex(random_bytes(3));
    $ins  = $pdo->prepare(
        "INSERT INTO portfolios (user_id, slug, title, owner_name, owner_role)
         VALUES (?,?,?,?,?)"
    );
    $ins->execute([$userId, $slug, $userName . "'s Portfolio", $userName, $userRole]);
    $stmt->execute([$userId]);
    $portfolio = $stmt->fetch();
} else {
    // keep owner display info fresh
    $pdo->prepare("UPDATE portfolios SET owner_name=?, owner_role=? WHERE user_id=?")
        ->execute([$userName, $userRole, $userId]);
}



// --- Build the absolute shareable URL ----------------------------------
$scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$dir      = rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/');
$shareUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $dir . '/view-portfolio.php?u=' . urlencode($portfolio['slug']);

$initials = strtoupper(substr($userName, 0, 1) . (strpos($userName, ' ') !== false ? substr($userName, strpos($userName, ' ') + 1, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Portfolio — Portfolio Builder</title>
<style>
:root{
  --bg:#0f1117;--surface:#171a23;--surface-2:#1f2430;--line:#2a3040;
  --text:#e8eaf0;--muted:#9aa3b5;--primary:#6c5ce7;--primary-soft:#8b7cf0;
  --accent:#ff7a59;--good:#2ecc71;--radius:16px;--shadow:0 10px 30px rgba(0,0,0,.35);
  font-family:"Segoe UI",system-ui,-apple-system,Roboto,Helvetica,Arial,sans-serif;
}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);min-height:100vh;padding:28px 20px}
a{text-decoration:none;color:inherit}
.wrap{max-width:980px;margin:0 auto}

.top{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:24px}
.top h2{font-size:22px}
.top p{font-size:13px;color:var(--muted);margin-top:2px}
.back{font-size:13px;color:var(--primary-soft);font-weight:600}
.who{display:flex;align-items:center;gap:12px}
.avatar{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-soft));display:grid;place-items:center;font-weight:700;color:#fff}

.notice,.error{border-radius:12px;padding:12px 16px;font-size:14px;margin-bottom:20px}
.notice{background:rgba(46,204,113,.12);border:1px solid rgba(46,204,113,.3);color:#8be9b0}
.error{background:rgba(255,99,99,.12);border:1px solid rgba(255,99,99,.3);color:#ff8585}

.panel{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:24px;margin-bottom:24px}
.panel h3{font-size:16px;margin-bottom:16px;display:flex;align-items:center;gap:8px}

/* Share box */
.share{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.share input{flex:1;min-width:220px;background:var(--surface-2);border:1px solid var(--line);color:var(--text);
  padding:12px 14px;border-radius:10px;font-size:14px}
.btn{cursor:pointer;border:none;font-weight:600;font-size:14px;padding:12px 18px;border-radius:10px;transition:.18s}
.btn-primary{background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.3)}
.btn-ghost{background:var(--surface-2);border:1px solid var(--line);color:var(--text)}
.btn-ghost:hover{border-color:var(--primary)}







/* Done / publish bar */
.done-bar{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;
  background:linear-gradient(120deg,#241b4a,#5a2f4d);border:1px solid var(--line);border-radius:var(--radius);
  padding:22px 24px;margin-bottom:24px}
.done-bar .txt b{font-size:16px;display:block;margin-bottom:3px}
.done-bar .txt span{font-size:13px;color:#cfc8e8}
.btn-done{background:#fff;color:#241b4a;font-weight:700;font-size:14px;padding:12px 24px;border-radius:30px;
  border:none;cursor:pointer;transition:.18s;white-space:nowrap}
.btn-done:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.3)}

/* Live preview */
.previewbox{border:1px solid var(--line);border-radius:14px;overflow:hidden;background:var(--surface-2)}
.previewbar{display:flex;align-items:center;gap:8px;padding:10px 14px;background:#11141c;border-bottom:1px solid var(--line)}
.previewbar .dot{width:11px;height:11px;border-radius:50%}
.previewbar .dot.r{background:#ff5f56}.previewbar .dot.y{background:#ffbd2e}.previewbar .dot.g{background:#27c93f}
.previewbar .url{flex:1;margin-left:8px;font-size:12px;color:var(--muted);background:var(--surface);border:1px solid var(--line);
  border-radius:8px;padding:6px 12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.previewbar a{font-size:12px;font-weight:600;color:var(--primary-soft)}
.previewframe{width:100%;height:520px;border:0;display:block;background:#0f1117}
.refresh{cursor:pointer;border:none;background:transparent;color:var(--primary-soft);font-size:14px}
@media(max-width:600px){.previewframe{height:420px}}
</style>
</head>
<body>
<div class="wrap">

  <div class="top">
    <div>
      <a href="Dashboard.php" class="back">← Back to Dashboard</a>
      <h2>Create Your Portfolio</h2>
      <p>Add your projects, images and links — then share your live portfolio.</p>
    </div>
    <div class="who">
      <div style="text-align:right">
        <b style="font-size:14px;display:block"><?= htmlspecialchars($userName) ?></b>
        <small style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($userRole) ?></small>
      </div>
      <div class="avatar"><?= htmlspecialchars($initials) ?></div>
    </div>
  </div>


  
  <section class="panel" id="shareSection">
    <h3>👁 Live Preview of Your Portfolio</h3>
    <div class="previewbox">
      
      <iframe id="previewFrame" class="previewframe" src="<?= htmlspecialchars($shareUrl) ?>" title="Portfolio preview"></iframe>
    </div>

    <p class="hint" style="margin:14px 0 8px">🔗 Your shareable portfolio link:</p>
    <div class="share">
      <input id="shareUrl" type="text" readonly value="<?= htmlspecialchars($shareUrl) ?>">
      <button class="btn btn-ghost" onclick="copyUrl()">Copy</button>
      <a class="btn btn-primary" href="<?= htmlspecialchars($shareUrl) ?>" target="_blank">Open ↗</a>
    </div>
    <p class="hint" style="margin-top:10px">Send this link to recruiters — anyone can view it, no login needed. The preview updates each time you add or delete a project.</p>
  </section>

 


  

  <!-- Done -->
  <section class="done-bar">
    <div class="txt">
      <b>✅ Portfolio ready!</b>
      <span>Preview your live link above, then build your downloadable CV.</span>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <!-- <button class="btn-done" style="background:var(--surface-2);color:var(--text);border:1px solid var(--line)" onclick="document.getElementById('shareSection').scrollIntoView({behavior:'smooth',block:'center'});window.open(<?= json_encode($shareUrl) ?>,'_blank');">Preview Portfolio ↗</button> -->
      <a class="btn-done" href="create-resume.php" style="text-decoration:none;display:inline-grid;place-items:center">Build my Resume →</a>
    </div>
  </section>

</div>

<script>
function finishPortfolio(){
  // Reveal/scroll to the share box, then open the live portfolio.
  document.getElementById('shareSection').scrollIntoView({behavior:'smooth', block:'center'});
  setTimeout(function(){ window.open(<?= json_encode($shareUrl) ?>, '_blank'); }, 600);
}
</script>

<script>
function copyUrl(){
  var f = document.getElementById('shareUrl');
  f.select(); f.setSelectionRange(0, 99999);
  navigator.clipboard.writeText(f.value).then(function(){
    var b = event.target; var t = b.textContent;
    b.textContent = 'Copied!'; setTimeout(function(){ b.textContent = t; }, 1400);
  });
}
</script>
</body>
</html>