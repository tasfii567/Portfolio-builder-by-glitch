<?php
/**
 * create-portfolio.php — add/list/delete projects, upload images,
 * see a live preview + shareable link, then move on to the resume.
 */
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/inc/templates.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId   = (int) $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'Member';

// --- Ensure portfolio + slug -------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM portfolios WHERE user_id = ?");
$stmt->execute([$userId]);
$portfolio = $stmt->fetch();
if (!$portfolio) {
    $base = trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $userName)), '-') ?: 'portfolio';
    $slug = $base . '-' . bin2hex(random_bytes(3));
    $pdo->prepare("INSERT INTO portfolios (user_id, slug, title, owner_name, owner_role) VALUES (?,?,?,?,?)")
        ->execute([$userId, $slug, $userName . "'s Portfolio", $userName, $userRole]);
    $stmt->execute([$userId]);
    $portfolio = $stmt->fetch();
} else {
    $pdo->prepare("UPDATE portfolios SET owner_name=?, owner_role=? WHERE user_id=?")
        ->execute([$userName, $userRole, $userId]);
}

$uploadDir = __DIR__ . '/uploads/projects/';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);

$error = '';
$notice = '';

// --- Delete a project --------------------------------------------------
if (isset($_GET['delete'])) {
    $pid = (int) $_GET['delete'];
    $own = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
    $own->execute([$pid, $userId]);
    if ($own->fetch()) {
        $imgs = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ?");
        $imgs->execute([$pid]);
        foreach ($imgs->fetchAll(PDO::FETCH_COLUMN) as $rel) {
            $abs = __DIR__ . '/' . $rel;
            if (is_file($abs)) @unlink($abs);
        }
        $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$pid]);
    }
    header('Location: create-portfolio.php?deleted=1');
    exit;
}

// --- Add a project -----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_project') {
    $title  = trim($_POST['title'] ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $github = trim($_POST['github_link'] ?? '');
    $live   = trim($_POST['live_link'] ?? '');

    if ($title === '') {
        $error = 'Project title is required.';
    } else {
        $pdo->prepare("INSERT INTO projects (user_id, title, description, github_link, live_link) VALUES (?,?,?,?,?)")
            ->execute([$userId, $title, $desc, $github, $live]);
        $projectId = (int) $pdo->lastInsertId();

        $allowed = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        $maxBytes = 4 * 1024 * 1024;
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $i => $name) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                if ($_FILES['images']['size'][$i] > $maxBytes)        continue;
                $tmp  = $_FILES['images']['tmp_name'][$i];
                $info = @getimagesize($tmp);
                if ($info === false) continue;
                $ext = strtolower(image_type_to_extension($info[2], false));
                if (!in_array($ext, $allowed, true)) continue;
                $fname = 'p' . $projectId . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($tmp, $uploadDir . $fname)) {
                    $pdo->prepare("INSERT INTO project_images (project_id, image_path) VALUES (?,?)")
                        ->execute([$projectId, 'uploads/projects/' . $fname]);
                }
            }
        }
        header('Location: create-portfolio.php?added=1');
        exit;
    }
}

if (isset($_GET['added']))   $notice = 'Project added to your portfolio.';
if (isset($_GET['deleted'])) $notice = 'Project deleted.';

// --- Fetch projects + images -------------------------------------------
$pStmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC");
$pStmt->execute([$userId]);
$projects = $pStmt->fetchAll();
foreach ($projects as &$pr) {
    $im = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ?");
    $im->execute([$pr['id']]);
    $pr['images'] = $im->fetchAll(PDO::FETCH_COLUMN);
}
unset($pr);

// --- Share URL (adapts to host, or uses $APP_BASE_URL) + template ------
$shareUrl   = build_share_url($portfolio['slug']);
$TEMPLATES  = portfolio_templates();
$curTpl     = valid_template($portfolio['template'] ?? 'midnight');
$curTplName = $TEMPLATES[$curTpl]['name'];

$initials = strtoupper(substr($userName, 0, 1) . (strpos($userName, ' ') !== false ? substr($userName, strpos($userName, ' ') + 1, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Portfolio — PortfolioBuilder</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#f4f1ea;--surface:#fff;--surface-2:#efeade;--line:#e4ddcc;--text:#1d211a;--muted:#797f6f;
  --primary:#3a4a23;--primary-soft:#5c7038;--accent:#7d9e58;--good:#5c8a3a;--radius:14px;--shadow:0 8px 24px rgba(40,45,30,.07);
  font-family:"Plus Jakarta Sans",system-ui,Arial,sans-serif}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);min-height:100vh;padding:28px 20px}
a{text-decoration:none;color:inherit}
.wrap{max-width:980px;margin:0 auto}
.top{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:24px}
.top h2{font-size:24px;font-weight:800;letter-spacing:-.3px}
.top p{font-size:13px;color:var(--muted);margin-top:2px}
.back{font-size:13px;color:var(--primary);font-weight:700}
.who{display:flex;align-items:center;gap:12px}
.avatar{width:42px;height:42px;border-radius:50%;background:var(--primary);display:grid;place-items:center;font-weight:700;color:#fff}
.notice,.error{border-radius:12px;padding:12px 16px;font-size:14px;margin-bottom:20px}
.notice{background:#e7efd9;border:1px solid #cfe0b6;color:#4a6a2c}
.error{background:#f6e2dd;border:1px solid #e9c4ba;color:#a8442f}
.panel{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:24px;margin-bottom:24px;box-shadow:var(--shadow)}
.panel h3{font-size:16px;font-weight:800;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.hint{font-size:11px;color:var(--muted)}
.share{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.share input{flex:1;min-width:220px;background:var(--surface);border:1px solid var(--line);color:var(--text);padding:12px 14px;border-radius:10px;font-size:14px}
.btn{cursor:pointer;border:none;font-weight:700;font-size:14px;padding:12px 18px;border-radius:11px;transition:.18s;font-family:inherit}
.btn-primary{background:var(--primary);color:#fff}
.btn-primary:hover{background:#2f3d1c;transform:translateY(-1px);box-shadow:0 8px 18px rgba(40,45,30,.18)}
.btn-ghost{background:transparent;border:1px solid var(--line);color:var(--text)}
.btn-ghost:hover{border-color:var(--primary);background:var(--surface-2)}
/* Live preview */
.previewbox{border:1px solid var(--line);border-radius:14px;overflow:hidden;background:var(--surface-2)}
.previewbar{display:flex;align-items:center;gap:8px;padding:10px 14px;background:#eae4d6;border-bottom:1px solid var(--line)}
.previewbar .dot{width:11px;height:11px;border-radius:50%}
.previewbar .dot.r{background:#e0726b}.previewbar .dot.y{background:#e3b14a}.previewbar .dot.g{background:#7d9e58}
.previewbar .url{flex:1;margin-left:8px;font-size:12px;color:var(--muted);background:var(--surface);border:1px solid var(--line);border-radius:8px;padding:6px 12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.previewbar a{font-size:12px;font-weight:700;color:var(--primary)}
.refresh{cursor:pointer;border:none;background:transparent;color:var(--primary);font-size:14px;font-family:inherit}
.previewframe{width:100%;height:520px;border:0;display:block;background:#fff}
/* Form */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.field{display:flex;flex-direction:column;gap:6px}
.field.full{grid-column:1 / -1}
.field label{font-size:13px;color:var(--muted);font-weight:600}
.field input,.field textarea{background:var(--surface);border:1px solid var(--line);color:var(--text);padding:11px 13px;border-radius:10px;font-size:14px;font-family:inherit;width:100%}
.field input:focus,.field textarea:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(125,158,88,.18)}
.field textarea{min-height:90px;resize:vertical}
.field input[type=file]{padding:9px;cursor:pointer}
.form-actions{margin-top:18px;display:flex;justify-content:flex-end}
/* Project list */
.proj{display:flex;gap:16px;padding:18px;border:1px solid var(--line);border-radius:var(--radius);background:var(--surface);margin-bottom:14px}
.proj .thumb{width:120px;height:90px;border-radius:10px;background:var(--surface-2);overflow:hidden;flex-shrink:0;display:grid;place-items:center;color:var(--muted);font-size:24px}
.proj .thumb img{width:100%;height:100%;object-fit:cover}
.proj .body{flex:1;min-width:0}
.proj h4{font-size:16px;margin-bottom:4px}
.proj .desc{font-size:13px;color:var(--muted);line-height:1.5;margin-bottom:10px}
.proj .links{display:flex;gap:8px;flex-wrap:wrap}
.tag{font-size:12px;font-weight:600;padding:5px 11px;border-radius:20px;border:1px solid var(--line);background:var(--surface-2);color:var(--primary)}
.proj .meta{display:flex;flex-direction:column;align-items:flex-end;gap:8px;flex-shrink:0}
.count{font-size:12px;color:var(--muted)}
.del{font-size:12px;font-weight:700;color:#a8442f}
.empty{color:var(--muted);font-size:14px;text-align:center;padding:24px}
.tpl-note{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;font-size:14px}
.tpl-note b{color:var(--primary)}
/* Done bar */
.done-bar{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;
  background:linear-gradient(120deg,#3a4a23,#6b8540);border:1px solid var(--line);border-radius:var(--radius);padding:22px 24px;margin-bottom:24px}
.done-bar .txt b{font-size:16px;display:block;margin-bottom:3px;color:#fff}
.done-bar .txt span{font-size:13px;color:#dde5cd}
.btn-done{background:#fff;color:#3a4a23;font-weight:700;font-size:14px;padding:12px 24px;border-radius:30px;border:none;cursor:pointer;transition:.18s;white-space:nowrap;text-decoration:none;display:inline-grid;place-items:center}
.btn-done:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.18)}
@media(max-width:720px){.form-grid{grid-template-columns:1fr}.proj{flex-direction:column}.proj .thumb{width:100%;height:160px}.proj .meta{flex-direction:row;justify-content:space-between;align-items:center;width:100%}.previewframe{height:420px}}
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

  <?php if ($notice): ?><div class="notice">✅ <?= htmlspecialchars($notice) ?></div><?php endif; ?>
  <?php if ($error):  ?><div class="error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

  <!-- Live preview + shareable URL -->
  <section class="panel" id="shareSection">
    <h3>👁 Live Preview of Your Portfolio</h3>
    <div class="previewbox">
      <div class="previewbar">
        <span class="dot r"></span><span class="dot y"></span><span class="dot g"></span>
        <span class="url"><?= htmlspecialchars($shareUrl) ?></span>
        <button class="refresh" onclick="document.getElementById('previewFrame').contentWindow.location.reload()">⟳ Refresh</button>
        <a href="<?= htmlspecialchars($shareUrl) ?>" target="_blank">Open ↗</a>
      </div>
      <iframe id="previewFrame" class="previewframe" src="<?= htmlspecialchars($shareUrl) ?>" title="Portfolio preview"></iframe>
    </div>
   
    
  </section>



  <!-- Done -->
  <section class="done-bar">
    <div class="txt">
      <b>✅ Portfolio ready!</b>
      <span>Preview your live link above, then build your downloadable CV.</span>
    </div>
    <a class="btn-done" href="create-resume.php">Build my Resume →</a>
  </section>

</div>
<script>
function copyUrl(){
  var f = document.getElementById('shareUrl');
  f.select(); f.setSelectionRange(0, 99999);
  navigator.clipboard.writeText(f.value).then(function(){
    var b = event.target, t = b.textContent;
    b.textContent = 'Copied!'; setTimeout(function(){ b.textContent = t; }, 1400);
  });
}
</script>
</body>
</html>