<?php
/** templates/sunset.php — magazine cover + alternating project rows. */
function h($s){ return htmlspecialchars((string)$s); }
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($owner['name']) ?> — Portfolio</title>
<style>
:root{--bg:#18100f;--panel:#221614;--panel2:#30201c;--line:#3d2a25;--text:#f5e9e7;--muted:#c2a39b;--accent:#ff7a59;--soft:#ff8aa3;font-family:"Segoe UI",system-ui,Arial,sans-serif}
*{box-sizing:border-box;margin:0;padding:0}a{text-decoration:none;color:inherit}
body{background:var(--bg);color:var(--text)}
.cover{min-height:340px;background:linear-gradient(120deg,#4a1f23,#642a2a 45%,#7a3a2f);display:flex;align-items:center;padding:60px 8vw;position:relative;overflow:hidden}
.cover::after{content:"";position:absolute;left:-80px;bottom:-80px;width:320px;height:320px;border-radius:50%;background:radial-gradient(circle,rgba(255,122,89,.5),transparent 70%)}
.cv-in{position:relative;z-index:1;display:flex;align-items:center;gap:28px;flex-wrap:wrap}
.av{width:130px;height:130px;border-radius:50%;border:4px solid rgba(255,255,255,.25);background:rgba(255,255,255,.12);display:grid;place-items:center;font-weight:800;font-size:46px;color:#fff;overflow:hidden}
.av img{width:100%;height:100%;object-fit:cover}
.cover h1{font-size:clamp(34px,6vw,56px);line-height:1.05}
.cover .role{color:#ffd9cf;font-weight:700;font-size:17px;margin-top:6px;letter-spacing:.5px}
.cover .meta{display:flex;gap:16px;flex-wrap:wrap;margin-top:14px;font-size:13.5px;color:#ffd9cf}
.cover .meta a:hover{color:#fff}
.wrap{max-width:1000px;margin:0 auto;padding:44px 22px 70px}
.bio{font-size:16px;line-height:1.7;color:#ecd8d3;max-width:720px;margin-bottom:28px}
.chips{display:flex;flex-wrap:wrap;gap:9px;margin-bottom:40px}
.chip{font-size:12.5px;font-weight:700;background:var(--panel);border:1px solid var(--line);color:var(--soft);padding:7px 14px;border-radius:20px}
.h2{font-size:13px;text-transform:uppercase;letter-spacing:2px;color:var(--accent);font-weight:800;margin-bottom:22px}
.row{display:grid;grid-template-columns:1fr 1fr;gap:26px;align-items:center;margin-bottom:32px}
.row:nth-child(even) .img{order:2}
.img{height:230px;border-radius:18px;background:var(--panel2);overflow:hidden;display:grid;place-items:center;color:var(--muted);font-size:34px;border:1px solid var(--line)}
.img img{width:100%;height:100%;object-fit:cover}
.row h3{font-size:23px;margin-bottom:10px}
.row .d{color:var(--muted);line-height:1.6;font-size:14px;margin-bottom:14px}
.links a{font-size:13px;font-weight:700;margin-right:8px;color:var(--soft);border-bottom:2px solid var(--accent);padding-bottom:2px}
.empty{color:var(--muted);padding:40px;text-align:center}
.foot{text-align:center;color:var(--muted);font-size:12px;margin-top:30px}
@media(max-width:780px){.row{grid-template-columns:1fr}.row:nth-child(even) .img{order:0}}
</style></head><body>
<header class="cover">
  <div class="cv-in">
    <div class="av"><?php if($owner['image']):?><img src="<?=h($owner['image'])?>" alt=""><?php else:?><?=h($owner['initials'])?><?php endif;?></div>
    <div>
      <h1><?=h($owner['name'])?></h1>
      <?php if($owner['role']):?><div class="role"><?=h($owner['role'])?></div><?php endif;?>
      <div class="meta">
        <span>👁 <?=number_format($visitCount)?> visits</span>
        <?php if($owner['location']):?><span>📍 <?=h($owner['location'])?></span><?php endif;?>
        <?php if($owner['email']):?><a href="mailto:<?=h($owner['email'])?>">✉ Email</a><?php endif;?>
        <?php if($owner['github']):?><a href="<?=h($owner['github'])?>" target="_blank">⌥ GitHub</a><?php endif;?>
        <?php if($owner['website']):?><a href="<?=h($owner['website'])?>" target="_blank">🔗 Website</a><?php endif;?>
      </div>
    </div>
  </div>
</header>
<div class="wrap">
  <?php if($owner['summary']):?><p class="bio"><?=nl2br(h($owner['summary']))?></p><?php endif;?>
  <?php if($owner['skills']):?>
  <div class="chips"><?php foreach($owner['skills'] as $s):?><span class="chip"><?=h($s)?></span><?php endforeach;?></div>
  <?php endif;?>
  <p class="h2">Selected Work</p>
  <?php if(!$projects):?><div class="empty">No projects yet.</div><?php else: foreach($projects as $p):?>
  <div class="row">
    <div class="img"><?php if($p['cover']):?><img src="<?=h($p['cover'])?>" alt=""><?php else:?>🖼<?php endif;?></div>
    <div>
      <h3><?=h($p['title'])?></h3>
      <?php if(trim($p['description'])):?><p class="d"><?=nl2br(h($p['description']))?></p><?php endif;?>
      <div class="links">
        <?php if($p['github_link']):?><a href="<?=h($p['github_link'])?>" target="_blank">GitHub ↗</a><?php endif;?>
        <?php if($p['live_link']):?><a href="<?=h($p['live_link'])?>" target="_blank">Live Demo ↗</a><?php endif;?>
      </div>
    </div>
  </div>
  <?php endforeach; endif;?>
  <p class="foot">Built with Portfolio Builder</p>
</div>
</body></html>
