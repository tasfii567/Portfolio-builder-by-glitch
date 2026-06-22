<?php
/**
 * templates.php — Step 2. Pick one of 5 pre-built portfolio templates.
 * Applying is instant; "Continue" moves on to adding projects.
 */
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/inc/templates.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId   = (int) $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'Member';

// Ensure a portfolio + slug exists.
$pf = $pdo->prepare("SELECT * FROM portfolios WHERE user_id = ?");
$pf->execute([$userId]); $portfolio = $pf->fetch();
if (!$portfolio) {
    $base = trim(strtolower(preg_replace('/[^a-z0-9]+/i','-',$userName)), '-') ?: 'portfolio';
    $slug = $base . '-' . bin2hex(random_bytes(3));
    $pdo->prepare("INSERT INTO portfolios (user_id, slug, title, owner_name, owner_role) VALUES (?,?,?,?,?)")
        ->execute([$userId, $slug, $userName."'s Portfolio", $userName, $userRole]);
    $pf->execute([$userId]); $portfolio = $pf->fetch();
}

$TEMPLATES = portfolio_templates();
$current   = valid_template($portfolio['template'] ?? 'midnight');

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_template') {
    $tpl = valid_template($_POST['template'] ?? '');
    $pdo->prepare("UPDATE portfolios SET template=? WHERE user_id=?")->execute([$tpl, $userId]);
    header('Location: templates.php?applied=1'); exit;
}
if (isset($_GET['applied'])) $notice = 'Template applied! Continue to add your projects.';
if (isset($_GET['from']))    $notice = 'Profile saved! Now pick a template for your portfolio.';

$viewUrl = build_share_url($portfolio['slug']);

$initials = strtoupper(substr($userName,0,1) . (strpos($userName,' ')!==false ? substr($userName,strpos($userName,' ')+1,1) : ''));
$ACTIVE = 'templates';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Choose Template — Portfolio Builder</title>
<link rel="stylesheet" href="assets/app.css">
<style>
.steps{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:22px}
.step{font-size:12px;font-weight:600;color:var(--muted);background:var(--surface);border:1px solid var(--line);padding:8px 14px;border-radius:20px}
.step.on{color:#fff;background:linear-gradient(135deg,var(--primary),var(--accent));border-color:transparent}
.tpl-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px}
@media(max-width:760px){.tpl-grid{grid-template-columns:1fr}}
.tplcard{border:2px solid var(--line);border-radius:var(--radius);overflow:hidden;background:var(--surface);transition:.18s}
.tplcard:hover{transform:translateY(-3px);border-color:var(--primary)}
.tplcard.sel{border-color:var(--primary);box-shadow:0 0 0 3px rgba(108,92,231,.25)}
.tplcard .preview{height:148px;position:relative;display:flex;align-items:center;padding:0 24px;gap:14px}
.tplcard .preview .pv-av{width:46px;height:46px;border-radius:50%;background:rgba(255,255,255,.25);display:grid;place-items:center;font-weight:700;color:#fff;overflow:hidden}
.tplcard .preview .pv-av img{width:100%;height:100%;object-fit:cover}
.tplcard .preview .pv-tx b{display:block;color:#fff;font-size:16px}
.tplcard .preview .pv-tx span{font-size:12px;color:rgba(255,255,255,.82)}
.tplcard .preview .pv-dot{position:absolute;right:18px;bottom:16px;width:20px;height:20px;border-radius:50%;border:2px solid rgba(255,255,255,.55)}
.tplcard .body{padding:18px 20px;display:flex;justify-content:space-between;align-items:flex-end;gap:12px}
.tplcard .body b{font-size:16px;display:block;margin-bottom:4px}
.tplcard .body small{font-size:12.5px;color:var(--muted)}
.badge-cur{display:inline-block;margin-top:8px;font-size:10px;font-weight:700;letter-spacing:.4px;color:var(--good);
  background:rgba(46,204,113,.15);border:1px solid rgba(46,204,113,.3);padding:3px 8px;border-radius:20px}
.preview-bar{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;background:var(--surface);
  border:1px solid var(--line);border-radius:var(--radius);padding:18px 22px;margin-bottom:24px}
.preview-bar .u{font-size:13px;color:var(--muted);word-break:break-all}
.continue{display:flex;justify-content:flex-end;gap:12px;margin-top:6px}
</style>
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/inc/sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:14px">
        <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
        <div><h2>Choose Template</h2><p>Step 2 — pick a look for your portfolio.</p></div>
      </div>
      <div class="profile">
        <div class="who"><b><?= htmlspecialchars($userName) ?></b><small><?= htmlspecialchars($userRole) ?></small></div>
        <div class="avatar"><?= htmlspecialchars($initials) ?></div>
      </div>
    </div>

    <div class="steps">
      <a href="edit-profile.php" class="step">1 · Profile</a>
      <span class="step on">2 · Choose Template</span>
      <span class="step">3 · Create Portfolio</span>
      <span class="step">4 · Resume / CV</span>
    </div>

    <?php if ($notice): ?><div class="notice">✅ <?= htmlspecialchars($notice) ?></div><?php endif; ?>

    <section class="preview-bar">
      <div><b>Live portfolio link</b><div class="u"><?= htmlspecialchars($viewUrl) ?></div></div>
      <a class="btn btn-ghost" href="<?= htmlspecialchars($viewUrl) ?>" target="_blank">Preview current ↗</a>
    </section>

    <section class="panel">
      <h3>🎨 5 Templates</h3>
      <p class="lead">Click <b>Apply</b> to switch your portfolio's design. You can change it any time.</p>
      <div class="tpl-grid">
        <?php foreach ($TEMPLATES as $key => $t): ?>
        <div class="tplcard <?= $current === $key ? 'sel' : '' ?>">
          <div class="preview" style="background:linear-gradient(120deg,<?= $t['c1'] ?>,<?= $t['c2'] ?>)">
            <div class="pv-av"><?= htmlspecialchars($initials) ?></div>
            <div class="pv-tx"><b><?= htmlspecialchars($userName) ?></b><span><?= htmlspecialchars($userRole) ?></span></div>
            <span class="pv-dot" style="background:<?= $t['dot'] ?>"></span>
          </div>
          <div class="body">
            <div>
              <b><?= htmlspecialchars($t['name']) ?></b>
              <small><?= htmlspecialchars($t['desc']) ?></small>
              <?php if ($current === $key): ?><div><span class="badge-cur">✓ CURRENT</span></div><?php endif; ?>
            </div>
            <div style="display:flex;gap:8px">
              <a class="btn btn-ghost" href="<?= htmlspecialchars($viewUrl) ?>" target="_blank" style="padding:9px 14px">Preview</a>
              <?php if ($current !== $key): ?>
              <form method="post" style="margin:0">
                <input type="hidden" name="action" value="set_template">
                <input type="hidden" name="template" value="<?= htmlspecialchars($key) ?>">
                <button class="btn btn-primary" type="submit" style="padding:9px 16px">Apply</button>
              </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="continue">
        <a class="btn btn-primary" href="create-portfolio.php">Continue to Create Portfolio →</a>
      </div>
    </section>
  </main>
</div>
</body>
</html>