<?php
/**
 * create-portfolio.php — add/list/delete projects, upload images,
 * live preview + shareable link.
 */
require __DIR__ . '/includes/auth-user.php';
require __DIR__ . '/inc/templates.php';
require __DIR__ . '/inc/portfolio-helpers.php';

$activeNav = 'create-portfolio';

$uStmt = $pdo->prepare('SELECT name, role FROM users WHERE id = ?');
$uStmt->execute([$userId]);
$dbUser = $uStmt->fetch() ?: ['name' => $userName, 'role' => 'Member'];
$ownerName = $dbUser['name'];
$ownerRole = $dbUser['role'] ?? 'Member';

$stmt = $pdo->prepare('SELECT * FROM portfolios WHERE user_id = ?');
$stmt->execute([$userId]);
$portfolio = $stmt->fetch();
if (!$portfolio) {
    $base = trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $ownerName)), '-') ?: 'portfolio';
    $slug = $base . '-' . bin2hex(random_bytes(3));
    $pdo->prepare('INSERT INTO portfolios (user_id, slug, title, owner_name, owner_role) VALUES (?,?,?,?,?)')
        ->execute([$userId, $slug, $ownerName . "'s Portfolio", $ownerName, $ownerRole]);
    $stmt->execute([$userId]);
    $portfolio = $stmt->fetch();
} else {
    $pdo->prepare('UPDATE portfolios SET owner_name=?, owner_role=? WHERE user_id=?')
        ->execute([$ownerName, $ownerRole, $userId]);
}

$uploadDir = __DIR__ . '/uploads/projects/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0775, true);
}

$error = '';
$notice = '';

if (isset($_GET['delete'])) {
    $pid = (int) $_GET['delete'];
    $own = $pdo->prepare('SELECT id FROM projects WHERE id = ? AND user_id = ?');
    $own->execute([$pid, $userId]);
    if ($own->fetch()) {
        $imgs = $pdo->prepare('SELECT image_path FROM project_images WHERE project_id = ?');
        $imgs->execute([$pid]);
        foreach ($imgs->fetchAll(PDO::FETCH_COLUMN) as $rel) {
            $abs = __DIR__ . '/' . ltrim($rel, '/');
            if (is_file($abs)) {
                @unlink($abs);
            }
        }
        $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([$pid]);
    }
    header('Location: create-portfolio.php?deleted=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_project') {
    $title  = trim($_POST['title'] ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $github = trim($_POST['github_link'] ?? '');
    $live   = trim($_POST['live_link'] ?? '');

    if ($title === '') {
        $error = 'Project title is required.';
    } else {
        $pdo->prepare('INSERT INTO projects (user_id, title, description, github_link, live_link) VALUES (?,?,?,?,?)')
            ->execute([$userId, $title, $desc, $github, $live]);
        $projectId = (int) $pdo->lastInsertId();

        $allowed  = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        $maxBytes = 4 * 1024 * 1024;
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $i => $name) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                if ($_FILES['images']['size'][$i] > $maxBytes) {
                    continue;
                }
                $tmp  = $_FILES['images']['tmp_name'][$i];
                $info = @getimagesize($tmp);
                if ($info === false) {
                    continue;
                }
                $ext = strtolower(image_type_to_extension($info[2], false));
                if (!in_array($ext, $allowed, true)) {
                    continue;
                }
                $fname = 'p' . $projectId . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($tmp, $uploadDir . $fname)) {
                    $pdo->prepare('INSERT INTO project_images (project_id, image_path) VALUES (?,?)')
                        ->execute([$projectId, 'uploads/projects/' . $fname]);
                }
            }
        }
        header('Location: create-portfolio.php?added=1');
        exit;
    }
}

if (isset($_GET['added'])) {
    $notice = 'Project added to your portfolio.';
}
if (isset($_GET['deleted'])) {
    $notice = 'Project deleted.';
}

$pStmt = $pdo->prepare('SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC');
$pStmt->execute([$userId]);
$projects = $pStmt->fetchAll();
foreach ($projects as &$pr) {
    $im = $pdo->prepare('SELECT image_path FROM project_images WHERE project_id = ?');
    $im->execute([$pr['id']]);
    $pr['images'] = $im->fetchAll(PDO::FETCH_COLUMN);
}
unset($pr);

$shareUrl   = build_share_url($portfolio['slug']);
$TEMPLATES  = portfolio_templates();
$curTpl     = valid_template($portfolio['template'] ?? 'midnight');
$curTplName = $TEMPLATES[$curTpl]['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Portfolio — Portfolio Builder</title>
<style>
:root{--bg:#0f1117;--surface:#171a23;--surface-2:#1f2430;--line:#2a3040;--text:#e8eaf0;--muted:#9aa3b5;
  --primary:#6c5ce7;--primary-soft:#8b7cf0;--accent:#ff7a59;--good:#2ecc71;--radius:16px;--shadow:0 10px 30px rgba(0,0,0,.35);
  font-family:"Segoe UI",system-ui,-apple-system,Roboto,Helvetica,Arial,sans-serif}
*{box-sizing:border-box;margin:0;padding:0}a{text-decoration:none;color:inherit}
body{background:var(--bg);color:var(--text);min-height:100vh}
.app{display:grid;grid-template-columns:260px 1fr;min-height:100vh}
.sidebar{background:var(--surface);border-right:1px solid var(--line);padding:24px 18px;display:flex;flex-direction:column;gap:8px;position:sticky;top:0;height:100vh}
.brand{display:flex;align-items:center;gap:12px;padding:6px 8px 22px}
.brand .logo{width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--primary),var(--accent));display:grid;place-items:center;font-weight:800;font-size:18px;color:#fff}
.brand h1{font-size:16px;line-height:1.2}.brand span{font-size:11px;color:var(--muted);letter-spacing:.5px}
.nav{display:flex;flex-direction:column;gap:4px;margin-top:6px}
.nav-label{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);padding:14px 12px 6px}
.nav a{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:10px;color:var(--muted);font-size:14px;font-weight:500;transition:.18s}
.nav a .ic{width:20px;text-align:center;font-size:16px}
.nav a:hover{background:var(--surface-2);color:var(--text)}
.nav a.active{background:linear-gradient(135deg,rgba(108,92,231,.22),rgba(255,122,89,.12));color:#fff}
.logout{margin-top:auto}
.logout a{display:flex;align-items:center;gap:12px;justify-content:center;padding:12px;border-radius:10px;font-weight:600;font-size:14px;background:rgba(255,99,99,.12);color:#ff8585;border:1px solid rgba(255,99,99,.25);transition:.18s}
.logout a:hover{background:rgba(255,99,99,.22)}
.main{padding:28px 34px;overflow-x:hidden}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:26px}
.topbar h2{font-size:22px}.topbar p{font-size:13px;color:var(--muted);margin-top:2px}
.profile{display:flex;align-items:center;gap:12px}
.profile .avatar{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-soft));display:grid;place-items:center;font-weight:700;color:#fff;overflow:hidden}
.profile .avatar img{width:100%;height:100%;object-fit:cover}
.profile .who{text-align:right}.profile .who b{font-size:14px;display:block}.profile .who small{font-size:12px;color:var(--muted)}
.menu-btn{display:none}
.notice,.error{border-radius:12px;padding:12px 16px;font-size:14px;margin-bottom:20px}
.notice{background:rgba(46,204,113,.12);border:1px solid rgba(46,204,113,.3);color:var(--good)}
.error{background:rgba(255,99,99,.12);border:1px solid rgba(255,99,99,.3);color:#ff8585}
.panel{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:24px;margin-bottom:24px;box-shadow:var(--shadow)}
.panel h3{font-size:16px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.hint{font-size:11px;color:var(--muted)}
.share{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.share input{flex:1;min-width:220px;background:var(--surface-2);border:1px solid var(--line);color:var(--text);padding:12px 14px;border-radius:10px;font-size:14px}
.btn{cursor:pointer;border:none;font-weight:700;font-size:14px;padding:12px 18px;border-radius:11px;transition:.18s;font-family:inherit;display:inline-grid;place-items:center}
.btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-soft));color:#fff}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 8px 18px rgba(108,92,231,.35)}
.btn-ghost{background:transparent;border:1px solid var(--line);color:var(--text)}
.btn-ghost:hover{border-color:var(--primary);background:var(--surface-2)}
.previewbox{border:1px solid var(--line);border-radius:14px;overflow:hidden;background:var(--surface-2)}
.previewbar{display:flex;align-items:center;gap:8px;padding:10px 14px;background:var(--surface);border-bottom:1px solid var(--line)}
.previewbar .dot{width:11px;height:11px;border-radius:50%}
.previewbar .dot.r{background:#e0726b}.previewbar .dot.y{background:#e3b14a}.previewbar .dot.g{background:var(--good)}
.previewbar .url{flex:1;margin-left:8px;font-size:12px;color:var(--muted);background:var(--surface-2);border:1px solid var(--line);border-radius:8px;padding:6px 12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.previewbar a,.refresh{font-size:12px;font-weight:700;color:var(--primary-soft)}
.refresh{cursor:pointer;border:none;background:transparent;font-family:inherit}
.previewframe{width:100%;height:520px;border:0;display:block;background:#fff}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.field{display:flex;flex-direction:column;gap:6px}
.field.full{grid-column:1/-1}
.field label{font-size:13px;color:var(--muted);font-weight:600}
.field input,.field textarea{background:var(--surface-2);border:1px solid var(--line);color:var(--text);padding:11px 13px;border-radius:10px;font-size:14px;font-family:inherit;width:100%}
.field input:focus,.field textarea:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(108,92,231,.2)}
.field textarea{min-height:90px;resize:vertical}
.field input[type=file]{padding:9px;cursor:pointer}
.form-actions{margin-top:18px;display:flex;justify-content:flex-end}
.proj{display:flex;gap:16px;padding:18px;border:1px solid var(--line);border-radius:var(--radius);background:var(--surface-2);margin-bottom:14px}
.proj .thumb{width:120px;height:90px;border-radius:10px;background:var(--surface);overflow:hidden;flex-shrink:0;display:grid;place-items:center;color:var(--muted);font-size:24px}
.proj .thumb img{width:100%;height:100%;object-fit:cover}
.proj .body{flex:1;min-width:0}
.proj h4{font-size:16px;margin-bottom:4px}
.proj .desc{font-size:13px;color:var(--muted);line-height:1.5;margin-bottom:10px}
.proj .links{display:flex;gap:8px;flex-wrap:wrap}
.tag{font-size:12px;font-weight:600;padding:5px 11px;border-radius:20px;border:1px solid var(--line);background:var(--surface);color:var(--primary-soft)}
.proj .meta{display:flex;flex-direction:column;align-items:flex-end;gap:8px;flex-shrink:0}
.count{font-size:12px;color:var(--muted)}
.del{font-size:12px;font-weight:700;color:#ff8585}
.empty{color:var(--muted);font-size:14px;text-align:center;padding:24px}
.tpl-note{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;font-size:14px}
.tpl-note b{color:var(--primary-soft)}
.done-bar{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;
  background:linear-gradient(120deg,#241b4a,#3a2a5e 45%,#5a2f4d);border:1px solid var(--line);border-radius:var(--radius);padding:22px 24px;margin-bottom:24px}
.done-bar .txt b{font-size:16px;display:block;margin-bottom:3px;color:#fff}
.done-bar .txt span{font-size:13px;color:#cfc8e8}
.btn-done{background:#fff;color:#241b4a;font-weight:700;font-size:14px;padding:12px 24px;border-radius:30px;border:none;cursor:pointer;transition:.18s;white-space:nowrap;text-decoration:none;display:inline-grid;place-items:center}
.btn-done:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.3)}
@media(max-width:760px){.app{grid-template-columns:1fr}.sidebar{position:fixed;left:-280px;z-index:50;transition:.25s;width:260px}.sidebar.open{left:0}
.menu-btn{display:grid;place-items:center;width:42px;height:42px;border-radius:10px;background:var(--surface);border:1px solid var(--line);color:var(--text);font-size:20px;cursor:pointer}
.main{padding:20px}.form-grid{grid-template-columns:1fr}.proj{flex-direction:column}.proj .thumb{width:100%;height:160px}.proj .meta{flex-direction:row;justify-content:space-between;width:100%}.previewframe{height:420px}.profile .who{display:none}}
</style>
</head>
<body>
<div class="app">
<?php require __DIR__ . '/includes/sidebar.php'; ?>
<main class="main">
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:14px">
      <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
      <div>
        <h2>Create Portfolio</h2>
        <p>Add projects, images and links — then share your live portfolio.</p>
      </div>
    </div>
    <div class="profile">
      <div class="who">
        <b><?= htmlspecialchars($userName) ?></b>
        <small><?= htmlspecialchars($userRole) ?></small>
      </div>
      <div class="avatar">
        <?php if ($userAvatar): ?>
          <img src="<?= htmlspecialchars($userAvatar) ?>" alt="<?= htmlspecialchars($userName) ?>">
        <?php else: ?>
          <?= htmlspecialchars($initials) ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($notice): ?><div class="notice">✅ <?= htmlspecialchars($notice) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

  <section class="panel">
    <h3>👁 Live Preview of Your Portfolio</h3>
    <div class="previewbox">
      <div class="previewbar">
        <span class="dot r"></span><span class="dot y"></span><span class="dot g"></span>
        <span class="url"><?= htmlspecialchars($shareUrl) ?></span>
        <button class="refresh" type="button" onclick="document.getElementById('previewFrame').contentWindow.location.reload()">⟳ Refresh</button>
        <a href="<?= htmlspecialchars($shareUrl) ?>" target="_blank" rel="noopener">Open ↗</a>
      </div>
      <iframe id="previewFrame" class="previewframe" src="<?= htmlspecialchars($shareUrl) ?>" title="Portfolio preview"></iframe>
    </div>
    <p class="hint" style="margin:14px 0 8px">🔗 Your shareable portfolio link:</p>
    <div class="share">
      <input id="shareUrl" type="text" readonly value="<?= htmlspecialchars($shareUrl) ?>">
      <button class="btn btn-ghost" type="button" onclick="copyUrl(event)">Copy</button>
      <a class="btn btn-primary" href="<?= htmlspecialchars($shareUrl) ?>" target="_blank" rel="noopener">Open ↗</a>
    </div>
  </section>

  <section class="panel">
    <div class="tpl-note">
      <div>🎨 Current template: <b><?= htmlspecialchars($curTplName) ?></b></div>
      <a class="btn btn-ghost" href="choose-template.php">Change template</a>
    </div>
  </section>

  <section class="panel">
    <h3>➕ Add a Project</h3>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add_project">
      <div class="form-grid">
        <div class="field full">
          <label>Project title *</label>
          <input type="text" name="title" maxlength="150" placeholder="e.g. Weather Dashboard App" required>
        </div>
        <div class="field full">
          <label>Description</label>
          <textarea name="description" placeholder="What does this project do? Tech used, your role, etc."></textarea>
        </div>
        <div class="field">
          <label>GitHub link</label>
          <input type="url" name="github_link" placeholder="https://github.com/you/project">
        </div>
        <div class="field">
          <label>Live demo link</label>
          <input type="url" name="live_link" placeholder="https://your-demo.com">
        </div>
        <div class="field full">
          <label>Project images</label>
          <input type="file" name="images[]" accept="image/*" multiple>
          <span class="hint">JPG, PNG, GIF or WEBP · up to 4 MB each · multiple allowed.</span>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Project</button>
      </div>
    </form>
  </section>

  <section class="panel">
    <h3>📁 Your Projects (<?= count($projects) ?>)</h3>
    <?php if (!$projects): ?>
      <div class="empty">No projects yet. Add your first one above 👆</div>
    <?php else: ?>
      <?php foreach ($projects as $pr): ?>
      <div class="proj">
        <div class="thumb">
          <?php if (!empty($pr['images'])): ?><img src="<?= htmlspecialchars($pr['images'][0]) ?>" alt=""><?php else: ?>🖼<?php endif; ?>
        </div>
        <div class="body">
          <h4><?= htmlspecialchars($pr['title']) ?></h4>
          <?php if ($pr['description']): ?><p class="desc"><?= nl2br(htmlspecialchars($pr['description'])) ?></p><?php endif; ?>
          <div class="links">
            <?php if ($pr['github_link']): ?><a class="tag" href="<?= htmlspecialchars($pr['github_link']) ?>" target="_blank" rel="noopener">⌥ GitHub</a><?php endif; ?>
            <?php if ($pr['live_link']): ?><a class="tag" href="<?= htmlspecialchars($pr['live_link']) ?>" target="_blank" rel="noopener">↗ Live Demo</a><?php endif; ?>
          </div>
        </div>
        <div class="meta">
          <span class="count"><?= count($pr['images']) ?> image<?= count($pr['images']) === 1 ? '' : 's' ?></span>
          <a class="del" href="create-portfolio.php?delete=<?= (int) $pr['id'] ?>" onclick="return confirm('Delete this project?')">🗑 Delete</a>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <section class="done-bar">
    <div class="txt">
      <b>✅ Portfolio ready!</b>
      <span>Preview your live link above, then continue editing your profile.</span>
    </div>
    <a class="btn-done" href="edit-portfolio.php">Done — Edit Profile →</a>
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
