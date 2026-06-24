<?php
/**
 * templates/_portfolio-base.php
 * Shared markup for the public portfolio page. Driven by $PALETTE (set by the
 * individual template files: midnight.php / aurora.php / classic.php).
 *
 * Available: $owner (array), $projects (array), $visitCount (int)
 */
if (!isset($PALETTE)) {
    $PALETTE = [];
}
$P = array_merge([
    'bg'      => '#0f1117',
    'surface' => '#171a23',
    'surface2'=> '#1f2430',
    'line'    => '#2a3040',
    'text'    => '#e8eaf0',
    'muted'   => '#9aa3b5',
    'primary' => '#6c5ce7',
    'accent'  => '#ff7a59',
    'heroText'=> '#ffffff',
    'font'    => '"Segoe UI",system-ui,-apple-system,Roboto,Helvetica,Arial,sans-serif',
], $PALETTE);

$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

// Normalise project fields so portfolios built with either form still render.
$projList = [];
foreach (($projects ?? []) as $pr) {
    $imgs = $pr['images'] ?? [];
    if (!$imgs && !empty($pr['image_path'])) {
        $imgs = [$pr['image_path']];
    }
    $projList[] = [
        'title'  => $pr['title'] ?? 'Untitled project',
        'desc'   => $pr['description'] ?? '',
        'github' => ($pr['github_link'] ?? '') ?: ($pr['github_url'] ?? ''),
        'live'   => ($pr['live_link'] ?? '')   ?: ($pr['demo_url']   ?? ''),
        'cover'  => $imgs[0] ?? null,
        'count'  => count($imgs),
    ];
}

$skills = $owner['skills'] ?? [];
$exp    = $owner['experience'] ?? [];
$edu    = $owner['education'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $e($owner['name'] ?? 'Portfolio') ?> — Portfolio</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --bg:<?= $P['bg'] ?>;--surface:<?= $P['surface'] ?>;--surface2:<?= $P['surface2'] ?>;
  --line:<?= $P['line'] ?>;--text:<?= $P['text'] ?>;--muted:<?= $P['muted'] ?>;
  --primary:<?= $P['primary'] ?>;--accent:<?= $P['accent'] ?>;--heroText:<?= $P['heroText'] ?>;
  --radius:16px;--shadow:0 10px 30px rgba(0,0,0,.18);
  font-family:<?= $P['font'] ?>;
}
*{box-sizing:border-box;margin:0;padding:0}
a{text-decoration:none;color:inherit}
body{background:var(--bg);color:var(--text);line-height:1.6}
.wrap{max-width:1040px;margin:0 auto;padding:0 20px}
.hero{background:linear-gradient(135deg,var(--primary),var(--accent));color:var(--heroText);padding:64px 0 72px}
.hero .wrap{display:flex;gap:28px;align-items:center;flex-wrap:wrap}
.avatar{width:120px;height:120px;border-radius:50%;overflow:hidden;flex-shrink:0;
  background:rgba(255,255,255,.18);display:grid;place-items:center;font-size:42px;font-weight:800;
  border:4px solid rgba(255,255,255,.35)}
.avatar img{width:100%;height:100%;object-fit:cover}
.hero h1{font-size:34px;font-weight:800;line-height:1.15}
.hero .role{font-size:17px;opacity:.95;margin-top:4px;font-weight:600}
.hero .meta{display:flex;gap:16px;flex-wrap:wrap;margin-top:12px;font-size:14px;opacity:.95}
.hero .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
.btn{display:inline-flex;align-items:center;gap:7px;padding:10px 18px;border-radius:30px;font-weight:700;
  font-size:14px;background:rgba(255,255,255,.16);color:var(--heroText);border:1px solid rgba(255,255,255,.3);
  transition:.18s}
.btn:hover{background:rgba(255,255,255,.28);transform:translateY(-1px)}
.btn.solid{background:#fff;color:var(--primary);border-color:#fff}
section.block{padding:46px 0}
.section-title{font-size:13px;text-transform:uppercase;letter-spacing:2px;color:var(--muted);
  font-weight:700;margin-bottom:18px}
.summary{font-size:16px;color:var(--text);max-width:760px}
.chips{display:flex;flex-wrap:wrap;gap:10px}
.chip{background:var(--surface);border:1px solid var(--line);color:var(--text);font-weight:600;
  font-size:13px;padding:8px 14px;border-radius:30px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px}
.card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);overflow:hidden;
  box-shadow:var(--shadow);display:flex;flex-direction:column;transition:.2s}
.card:hover{transform:translateY(-4px)}
.card .thumb{height:170px;background:var(--surface2);display:grid;place-items:center;color:var(--muted);font-size:34px}
.card .thumb img{width:100%;height:100%;object-fit:cover}
.card .body{padding:18px;flex:1;display:flex;flex-direction:column;gap:8px}
.card h3{font-size:18px;font-weight:700}
.card p{font-size:14px;color:var(--muted)}
.card .links{display:flex;gap:8px;flex-wrap:wrap;margin-top:auto;padding-top:6px}
.tag{font-size:12px;font-weight:700;padding:6px 12px;border-radius:20px;border:1px solid var(--line);
  background:var(--surface2);color:var(--primary)}
.timeline{display:flex;flex-direction:column;gap:16px}
.item{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:18px 20px}
.item h4{font-size:16px;font-weight:700}
.item .period{font-size:13px;color:var(--muted);margin:2px 0 6px}
.item .detail{font-size:14px;color:var(--text);white-space:pre-line}
.two{display:grid;grid-template-columns:1fr 1fr;gap:24px}
.empty{color:var(--muted);font-size:14px}
footer{border-top:1px solid var(--line);padding:26px 0;color:var(--muted);font-size:13px;text-align:center}
footer .views{color:var(--text);font-weight:700}
@media(max-width:760px){
  .hero{padding:48px 0 56px}.hero h1{font-size:27px}
  .two{grid-template-columns:1fr}
  .avatar{width:96px;height:96px;font-size:34px}
}
</style>
</head>
<body>

<header class="hero">
  <div class="wrap">
    <div class="avatar">
      <?php if (!empty($owner['image'])): ?>
        <img src="<?= $e($owner['image']) ?>" alt="<?= $e($owner['name']) ?>">
      <?php else: ?>
        <?= $e($owner['initials'] ?? 'U') ?>
      <?php endif; ?>
    </div>
    <div style="flex:1;min-width:240px">
      <h1><?= $e($owner['name'] ?? 'Portfolio') ?></h1>
      <?php if (!empty($owner['role'])): ?><div class="role"><?= $e($owner['role']) ?></div><?php endif; ?>
      <div class="meta">
        <?php if (!empty($owner['location'])): ?><span>📍 <?= $e($owner['location']) ?></span><?php endif; ?>
        <?php if (!empty($owner['email'])): ?><span>✉ <?= $e($owner['email']) ?></span><?php endif; ?>
      </div>
      <div class="actions">
        <?php if (!empty($owner['email'])): ?><a class="btn solid" href="mailto:<?= $e($owner['email']) ?>">Contact</a><?php endif; ?>
        <?php if (!empty($owner['github'])): ?><a class="btn" href="<?= $e($owner['github']) ?>" target="_blank" rel="noopener">GitHub</a><?php endif; ?>
        <?php if (!empty($owner['website'])): ?><a class="btn" href="<?= $e($owner['website']) ?>" target="_blank" rel="noopener">Website</a><?php endif; ?>
      </div>
    </div>
  </div>
</header>

<?php if (!empty($owner['summary'])): ?>
<section class="block"><div class="wrap">
  <div class="section-title">About</div>
  <p class="summary"><?= nl2br($e($owner['summary'])) ?></p>
</div></section>
<?php endif; ?>

<?php if ($skills): ?>
<section class="block" style="padding-top:0"><div class="wrap">
  <div class="section-title">Skills</div>
  <div class="chips">
    <?php foreach ($skills as $s): ?><span class="chip"><?= $e($s) ?></span><?php endforeach; ?>
  </div>
</div></section>
<?php endif; ?>

<section class="block" style="padding-top:0"><div class="wrap">
  <div class="section-title">Projects</div>
  <?php if (!$projList): ?>
    <p class="empty">No projects published yet.</p>
  <?php else: ?>
  <div class="grid">
    <?php foreach ($projList as $pr): ?>
    <article class="card">
      <div class="thumb">
        <?php if ($pr['cover']): ?><img src="<?= $e($pr['cover']) ?>" alt=""><?php else: ?>🖼<?php endif; ?>
      </div>
      <div class="body">
        <h3><?= $e($pr['title']) ?></h3>
        <?php if ($pr['desc'] !== ''): ?><p><?= nl2br($e($pr['desc'])) ?></p><?php endif; ?>
        <div class="links">
          <?php if ($pr['github']): ?><a class="tag" href="<?= $e($pr['github']) ?>" target="_blank" rel="noopener">GitHub ↗</a><?php endif; ?>
          <?php if ($pr['live']): ?><a class="tag" href="<?= $e($pr['live']) ?>" target="_blank" rel="noopener">Live Demo ↗</a><?php endif; ?>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div></section>

<?php if ($exp || $edu): ?>
<section class="block" style="padding-top:0"><div class="wrap">
  <div class="two">
    <?php if ($exp): ?>
    <div>
      <div class="section-title">Experience</div>
      <div class="timeline">
        <?php foreach ($exp as $it): ?>
        <div class="item">
          <h4><?= $e($it['title']) ?></h4>
          <?php if ($it['period']): ?><div class="period"><?= $e($it['period']) ?></div><?php endif; ?>
          <?php if ($it['detail']): ?><div class="detail"><?= $e($it['detail']) ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($edu): ?>
    <div>
      <div class="section-title">Education</div>
      <div class="timeline">
        <?php foreach ($edu as $it): ?>
        <div class="item">
          <h4><?= $e($it['title']) ?></h4>
          <?php if ($it['period']): ?><div class="period"><?= $e($it['period']) ?></div><?php endif; ?>
          <?php if ($it['detail']): ?><div class="detail"><?= $e($it['detail']) ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div></section>
<?php endif; ?>

<footer>
  <div class="wrap">
    <span class="views"><?= number_format((int) ($visitCount ?? 0)) ?></span> visits ·
    Built with PortfolioBuilder
  </div>
</footer>

</body>
</html>
