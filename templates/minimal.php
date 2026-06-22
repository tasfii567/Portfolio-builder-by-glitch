<?php
/** templates/minimal.php — clean light, centered single column. */
function h($s){ return htmlspecialchars((string)$s); }
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($owner['name']) ?> — Portfolio</title>
<style>
:root{--bg:#f4f6fb;--card:#fff;--line:#e2e6f0;--text:#1f2430;--muted:#6b7385;--accent:#6c5ce7;font-family:"Segoe UI",system-ui,Arial,sans-serif}
*{box-sizing:border-box;margin:0;padding:0}a{text-decoration:none;color:inherit}
body{background:var(--bg);color:var(--text)}
.wrap{max-width:760px;margin:0 auto;padding:64px 22px 80px}
.top{text-align:center;margin-bottom:14px}
.av{width:104px;height:104px;border-radius:50%;margin:0 auto 18px;background:var(--accent);display:grid;place-items:center;font-weight:700;font-size:38px;color:#fff;overflow:hidden}
.av img{width:100%;height:100%;object-fit:cover}
.top h1{font-size:34px;letter-spacing:-.5px}
.role{color:var(--accent);font-weight:700;font-size:15px;margin-top:6px}
.meta{display:flex;gap:18px;justify-content:center;flex-wrap:wrap;margin-top:14px;font-size:13.5px;color:var(--muted)}
.meta a{border-bottom:1px solid var(--line)}.meta a:hover{color:var(--accent);border-color:var(--accent)}
.bio{text-align:center;color:#3b4252;font-size:16px;line-height:1.7;max-width:600px;margin:22px auto 0}
.visits{text-align:center;color:var(--muted);font-size:12.5px;margin-top:14px}
.rule{height:1px;background:var(--line);margin:40px 0}
.lbl{font-size:12px;text-transform:uppercase;letter-spacing:2px;color:var(--muted);font-weight:700;margin-bottom:16px}
.chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px}
.chip{font-size:12.5px;font-weight:600;background:#fff;border:1px solid var(--line);color:#4b5263;padding:6px 13px;border-radius:8px}
.item{padding:20px 0;border-bottom:1px solid var(--line);display:flex;gap:18px;align-items:flex-start}
.item:last-child{border-bottom:none}
.ph{width:64px;height:64px;border-radius:12px;background:#eef1f8;overflow:hidden;flex-shrink:0;display:grid;place-items:center;color:var(--muted);font-size:22px}
.ph img{width:100%;height:100%;object-fit:cover}
.item h3{font-size:17px;margin-bottom:4px}.item .d{color:var(--muted);font-size:14px;line-height:1.6;margin-bottom:8px}
.item .lk a{font-size:13px;font-weight:600;color:var(--accent);margin-right:14px}
.item .lk a:hover{text-decoration:underline}
.empty{color:var(--muted);text-align:center;padding:30px}
.foot{text-align:center;color:var(--muted);font-size:12px;margin-top:46px}
</style></head><body>
<div class="wrap">
  <div class="top">
    <div class="av"><?php if($owner['image']):?><img src="<?=h($owner['image'])?>" alt=""><?php else:?><?=h($owner['initials'])?><?php endif;?></div>
    <h1><?=h($owner['name'])?></h1>
    <?php if($owner['role']):?><div class="role"><?=h($owner['role'])?></div><?php endif;?>
    <div class="meta">
      <?php if($owner['location']):?><span><?=h($owner['location'])?></span><?php endif;?>
      <?php if($owner['email']):?><a href="mailto:<?=h($owner['email'])?>"><?=h($owner['email'])?></a><?php endif;?>
      <?php if($owner['github']):?><a href="<?=h($owner['github'])?>" target="_blank">GitHub</a><?php endif;?>
      <?php if($owner['website']):?><a href="<?=h($owner['website'])?>" target="_blank">Website</a><?php endif;?>
    </div>
    <?php if($owner['summary']):?><p class="bio"><?=nl2br(h($owner['summary']))?></p><?php endif;?>
    <div class="visits">👁 <?=number_format($visitCount)?> visits</div>
  </div>

  <?php if($owner['skills']):?>
  <div class="rule"></div>
  <p class="lbl">Skills</p>
  <div class="chips"><?php foreach($owner['skills'] as $s):?><span class="chip"><?=h($s)?></span><?php endforeach;?></div>
  <?php endif;?>

  <div class="rule"></div>
  <p class="lbl">Projects</p>
  <?php if(!$projects):?><div class="empty">No projects yet.</div><?php else: foreach($projects as $p):?>
  <div class="item">
    <div class="ph"><?php if($p['cover']):?><img src="<?=h($p['cover'])?>" alt=""><?php else:?>🖼<?php endif;?></div>
    <div>
      <h3><?=h($p['title'])?></h3>
      <?php if(trim($p['description'])):?><p class="d"><?=nl2br(h($p['description']))?></p><?php endif;?>
      <div class="lk">
        <?php if($p['github_link']):?><a href="<?=h($p['github_link'])?>" target="_blank">GitHub →</a><?php endif;?>
        <?php if($p['live_link']):?><a href="<?=h($p['live_link'])?>" target="_blank">Live →</a><?php endif;?>
      </div>
    </div>
  </div>
  <?php endforeach; endif;?>
  <p class="foot">Built with Portfolio Builder</p>
</div>
</body></html>
