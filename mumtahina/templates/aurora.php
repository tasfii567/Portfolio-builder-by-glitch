<?php
/** templates/aurora.php — split layout, profile left + projects right. */
function h($s){ return htmlspecialchars((string)$s); }
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($owner['name']) ?> — Portfolio</title>
<style>
:root{--bg:#0b1513;--panel:#10201d;--panel2:#16302b;--line:#1f3d37;--text:#e6f1ee;--muted:#8fb3aa;--accent:#2ecc71;--soft:#3fd6b0;font-family:"Segoe UI",system-ui,Arial,sans-serif}
*{box-sizing:border-box;margin:0;padding:0}a{text-decoration:none;color:inherit}
body{background:var(--bg);color:var(--text);min-height:100vh}
.shell{display:grid;grid-template-columns:330px 1fr;min-height:100vh}
.side{background:linear-gradient(160deg,#08332e,#0f5d54);padding:40px 30px;position:sticky;top:0;height:100vh;display:flex;flex-direction:column;gap:18px;border-right:1px solid var(--line)}
.av{width:120px;height:120px;border-radius:24px;background:rgba(255,255,255,.15);display:grid;place-items:center;font-weight:700;font-size:42px;color:#fff;overflow:hidden}
.av img{width:100%;height:100%;object-fit:cover}
.side h1{font-size:26px}.side .role{color:#bdebdd;font-weight:600;margin-top:2px}
.side .bio{color:#cdeee2;font-size:13.5px;line-height:1.6}
.side .links{display:flex;flex-direction:column;gap:9px;margin-top:4px;font-size:13.5px}
.side .links a,.side .links span{display:flex;gap:9px;align-items:center;color:#d7f3ea}
.side .links a:hover{color:#fff}
.side .visits{margin-top:auto;font-size:12.5px;color:#bdebdd;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);padding:8px 14px;border-radius:20px;align-self:flex-start}
.main{padding:44px 46px}
.sk-title{font-size:13px;text-transform:uppercase;letter-spacing:1.5px;color:var(--soft);margin:0 0 14px;font-weight:700}
.chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:34px}
.chip{font-size:12px;font-weight:600;background:var(--panel);border:1px solid var(--line);color:var(--soft);padding:6px 13px;border-radius:8px}
.proj{display:flex;gap:18px;background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:18px;margin-bottom:16px;transition:.18s}
.proj:hover{border-color:var(--accent);transform:translateX(4px)}
.thumb{width:150px;height:104px;border-radius:10px;background:var(--panel2);overflow:hidden;flex-shrink:0;display:grid;place-items:center;color:var(--muted);font-size:26px}
.thumb img{width:100%;height:100%;object-fit:cover}
.proj h3{font-size:17px;margin-bottom:6px}.proj .d{font-size:13px;color:var(--muted);line-height:1.5;margin-bottom:10px}
.links2{display:flex;gap:8px;flex-wrap:wrap}
.links2 a{font-size:12px;font-weight:600;color:var(--soft);border:1px solid var(--line);padding:6px 12px;border-radius:20px}
.links2 a:hover{border-color:var(--accent);color:#fff}
.empty{color:var(--muted);padding:40px;text-align:center}
@media(max-width:820px){.shell{grid-template-columns:1fr}.side{position:static;height:auto}.main{padding:30px 22px}.proj{flex-direction:column}.thumb{width:100%;height:170px}}
</style></head><body>
<div class="shell">
  <aside class="side">
    <div class="av"><?php if($owner['image']):?><img src="<?=h($owner['image'])?>" alt=""><?php else:?><?=h($owner['initials'])?><?php endif;?></div>
    <div>
      <h1><?=h($owner['name'])?></h1>
      <?php if($owner['role']):?><div class="role"><?=h($owner['role'])?></div><?php endif;?>
    </div>
    <?php if($owner['summary']):?><p class="bio"><?=nl2br(h($owner['summary']))?></p><?php endif;?>
    <div class="links">
      <?php if($owner['location']):?><span>📍 <?=h($owner['location'])?></span><?php endif;?>
      <?php if($owner['email']):?><a href="mailto:<?=h($owner['email'])?>">✉ <?=h($owner['email'])?></a><?php endif;?>
      <?php if($owner['github']):?><a href="<?=h($owner['github'])?>" target="_blank">⌥ GitHub</a><?php endif;?>
      <?php if($owner['website']):?><a href="<?=h($owner['website'])?>" target="_blank">🔗 Website</a><?php endif;?>
    </div>
    <div class="visits">👁 <?=number_format($visitCount)?> visits</div>
  </aside>
  <main class="main">
    <?php if($owner['skills']):?>
    <p class="sk-title">Skills</p>
    <div class="chips"><?php foreach($owner['skills'] as $s):?><span class="chip"><?=h($s)?></span><?php endforeach;?></div>
    <?php endif;?>
    <p class="sk-title">Projects</p>
    <?php if(!$projects):?><div class="empty">No projects yet.</div><?php else: foreach($projects as $p):?>
    <div class="proj">
      <div class="thumb"><?php if($p['cover']):?><img src="<?=h($p['cover'])?>" alt=""><?php else:?>🖼<?php endif;?></div>
      <div>
        <h3><?=h($p['title'])?></h3>
        <?php if(trim($p['description'])):?><p class="d"><?=nl2br(h($p['description']))?></p><?php endif;?>
        <div class="links2">
          <?php if($p['github_link']):?><a href="<?=h($p['github_link'])?>" target="_blank">⌥ GitHub</a><?php endif;?>
          <?php if($p['live_link']):?><a href="<?=h($p['live_link'])?>" target="_blank">↗ Live</a><?php endif;?>
        </div>
      </div>
    </div>
    <?php endforeach; endif;?>
  </main>
</div>
</body></html>
