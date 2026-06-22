<?php
/** templates/midnight.php — classic dark hero + project grid.
 *  Vars: $owner (array), $projects (array), $visitCount (int) */
function h($s){ return htmlspecialchars((string)$s); }
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($owner['name']) ?> — Portfolio</title>
<style>
:root{--bg:#0f1117;--surface:#171a23;--surface-2:#1f2430;--line:#2a3040;--text:#e8eaf0;--muted:#9aa3b5;--primary:#6c5ce7;--soft:#8b7cf0;--accent:#ff7a59;--radius:16px;font-family:"Segoe UI",system-ui,Arial,sans-serif}
*{box-sizing:border-box;margin:0;padding:0}a{text-decoration:none;color:inherit}
body{background:var(--bg);color:var(--text);min-height:100vh}
.wrap{max-width:1040px;margin:0 auto;padding:30px 22px 60px}
.head{position:relative;overflow:hidden;border:1px solid var(--line);border-radius:var(--radius);background:linear-gradient(120deg,#241b4a,#3a2a5e 45%,#5a2f4d);padding:38px;margin-bottom:26px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
.head::after{content:"";position:absolute;right:-60px;top:-60px;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(255,122,89,.45),transparent 70%)}
.id{display:flex;align-items:center;gap:18px;position:relative;z-index:1}
.av{width:84px;height:84px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--soft));display:grid;place-items:center;font-weight:700;font-size:30px;color:#fff;overflow:hidden;flex-shrink:0}
.av img{width:100%;height:100%;object-fit:cover}
.head h1{font-size:30px}.role{color:#cfc8e8;font-size:15px;margin-top:3px}
.meta{display:flex;gap:14px;flex-wrap:wrap;margin-top:10px;font-size:13px;color:#cfc8e8;position:relative;z-index:1}
.meta a:hover{color:#fff}
.bio{color:#e7e2f5;max-width:640px;margin-top:14px;line-height:1.6;font-size:14px;position:relative;z-index:1}
.visits{position:absolute;right:30px;bottom:26px;z-index:1;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:30px;padding:8px 16px;font-size:13px;font-weight:600}
.chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:26px}
.chip{font-size:12px;font-weight:600;background:var(--surface);border:1px solid var(--line);color:var(--soft);padding:6px 13px;border-radius:20px}
.section-title{font-size:16px;margin:0 0 16px}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);overflow:hidden;display:flex;flex-direction:column;transition:.2s}
.card:hover{transform:translateY(-4px);border-color:var(--primary)}
.cover{height:170px;background:var(--surface-2);display:grid;place-items:center;color:var(--muted);font-size:30px;overflow:hidden}
.cover img{width:100%;height:100%;object-fit:cover}
.pad{padding:18px;display:flex;flex-direction:column;flex:1}
.pad h3{font-size:16px;margin-bottom:6px}.desc{font-size:13px;color:var(--muted);line-height:1.5;margin-bottom:14px;flex:1}
.links{display:flex;gap:8px;flex-wrap:wrap}
.links a{font-size:12px;font-weight:600;padding:7px 13px;border-radius:20px;border:1px solid var(--line);background:var(--surface-2);color:var(--soft)}
.links a:hover{border-color:var(--primary);color:#fff}
.empty{grid-column:1/-1;text-align:center;color:var(--muted);padding:50px}
.foot{text-align:center;color:var(--muted);font-size:12px;margin-top:40px}
@media(max-width:900px){.grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.grid{grid-template-columns:1fr}.visits{position:static;display:inline-block;margin-top:14px}}
</style></head><body>
<div class="wrap">
  <header class="head">
    <div class="id">
      <div class="av"><?php if($owner['image']):?><img src="<?=h($owner['image'])?>" alt=""><?php else:?><?=h($owner['initials'])?><?php endif;?></div>
      <div>
        <h1><?= h($owner['name']) ?></h1>
        <?php if($owner['role']):?><div class="role"><?= h($owner['role']) ?></div><?php endif;?>
      </div>
    </div>
    <div class="meta">
      <?php if($owner['location']):?><span>📍 <?=h($owner['location'])?></span><?php endif;?>
      <?php if($owner['email']):?><a href="mailto:<?=h($owner['email'])?>">✉ <?=h($owner['email'])?></a><?php endif;?>
      <?php if($owner['github']):?><a href="<?=h($owner['github'])?>" target="_blank">⌥ GitHub</a><?php endif;?>
      <?php if($owner['website']):?><a href="<?=h($owner['website'])?>" target="_blank">🔗 Website</a><?php endif;?>
    </div>
    <?php if($owner['summary']):?><p class="bio"><?= nl2br(h($owner['summary'])) ?></p><?php endif;?>
    <div class="visits">👁 <?= number_format($visitCount) ?> visits</div>
  </header>

  <?php if($owner['skills']):?>
  <div class="chips"><?php foreach($owner['skills'] as $s):?><span class="chip"><?=h($s)?></span><?php endforeach;?></div>
  <?php endif;?>

  <h2 class="section-title">📁 Projects</h2>
  <div class="grid">
    <?php if(!$projects):?><div class="empty">No projects yet.</div><?php else: foreach($projects as $p):?>
    <article class="card">
      <div class="cover"><?php if($p['cover']):?><img src="<?=h($p['cover'])?>" alt=""><?php else:?>🖼<?php endif;?></div>
      <div class="pad">
        <h3><?=h($p['title'])?></h3>
        <?php if(trim($p['description'])):?><p class="desc"><?=nl2br(h($p['description']))?></p><?php else:?><div class="desc"></div><?php endif;?>
        <div class="links">
          <?php if($p['github_link']):?><a href="<?=h($p['github_link'])?>" target="_blank" rel="noopener">⌥ GitHub</a><?php endif;?>
          <?php if($p['live_link']):?><a href="<?=h($p['live_link'])?>" target="_blank" rel="noopener">↗ Live</a><?php endif;?>
        </div>
      </div>
    </article>
    <?php endforeach; endif;?>
  </div>
  <p class="foot">Built with Portfolio Builder</p>
</div>
</body></html>
