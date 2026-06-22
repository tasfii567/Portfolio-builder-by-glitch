<?php
/** templates/terminal.php — developer / code theme. */
function h($s){ return htmlspecialchars((string)$s); }
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($owner['name']) ?> — Portfolio</title>
<style>
:root{--bg:#0b1a12;--card:#0f2418;--line:#1d3a28;--text:#cfeede;--muted:#6f9c84;--accent:#39d353;--cyan:#56d4dd;font-family:"JetBrains Mono",ui-monospace,"Cascadia Code","Courier New",monospace}
*{box-sizing:border-box;margin:0;padding:0}a{text-decoration:none;color:inherit}
body{background:var(--bg);color:var(--text);min-height:100vh;background-image:radial-gradient(rgba(57,211,83,.05) 1px,transparent 1px);background-size:22px 22px}
.wrap{max-width:920px;margin:0 auto;padding:34px 22px 70px}
.win{background:var(--card);border:1px solid var(--line);border-radius:12px;overflow:hidden;margin-bottom:26px;box-shadow:0 10px 30px rgba(0,0,0,.4)}
.bar{display:flex;align-items:center;gap:8px;padding:11px 14px;background:#0a1d12;border-bottom:1px solid var(--line)}
.dot{width:12px;height:12px;border-radius:50%}.r{background:#ff5f56}.y{background:#ffbd2e}.g{background:#27c93f}
.bar span{margin-left:8px;font-size:12px;color:var(--muted)}
.win .body{padding:22px}
.prompt{color:var(--accent)}.prompt::before{content:"$ ";color:var(--cyan)}
.idline{display:flex;align-items:center;gap:18px;margin:14px 0}
.av{width:80px;height:80px;border-radius:10px;background:#0a1d12;border:1px solid var(--line);display:grid;place-items:center;font-weight:700;font-size:28px;color:var(--accent);overflow:hidden}
.av img{width:100%;height:100%;object-fit:cover}
.idline h1{font-size:24px;color:#eafff2}
.idline .role{color:var(--cyan);font-size:14px;margin-top:3px}
.kv{font-size:13px;line-height:1.9;color:var(--text)}
.kv b{color:var(--muted);font-weight:400}
.kv a{color:var(--accent)}.kv a:hover{text-decoration:underline}
.bio{color:var(--muted);font-size:13.5px;line-height:1.7;margin-top:10px}
.tag{font-size:12px;color:var(--accent)}
.chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}
.chip{font-size:12px;background:#0a1d12;border:1px solid var(--line);color:var(--cyan);padding:5px 11px;border-radius:6px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.card{background:var(--card);border:1px solid var(--line);border-radius:10px;overflow:hidden;transition:.18s}
.card:hover{border-color:var(--accent);transform:translateY(-3px)}
.cv{height:140px;background:#0a1d12;display:grid;place-items:center;color:var(--muted);font-size:26px;overflow:hidden}
.cv img{width:100%;height:100%;object-fit:cover}
.card .p{padding:15px}
.card h3{font-size:15px;color:#eafff2;margin-bottom:6px}.card h3::before{content:"> ";color:var(--accent)}
.card .d{font-size:12.5px;color:var(--muted);line-height:1.5;margin-bottom:10px}
.card .lk a{font-size:12px;color:var(--accent);margin-right:12px}
.card .lk a:hover{text-decoration:underline}
.empty{color:var(--muted);padding:30px;text-align:center}
.visits{color:var(--muted);font-size:12px;margin-top:10px}
.foot{text-align:center;color:var(--muted);font-size:12px;margin-top:34px}
@media(max-width:680px){.grid{grid-template-columns:1fr}}
</style></head><body>
<div class="wrap">
  <div class="win">
    <div class="bar"><span class="dot r"></span><span class="dot y"></span><span class="dot g"></span><span>whoami — <?=h($owner['name'])?></span></div>
    <div class="body">
      <p class="prompt">whoami</p>
      <div class="idline">
        <div class="av"><?php if($owner['image']):?><img src="<?=h($owner['image'])?>" alt=""><?php else:?><?=h($owner['initials'])?><?php endif;?></div>
        <div>
          <h1><?=h($owner['name'])?></h1>
          <?php if($owner['role']):?><div class="role"><?=h($owner['role'])?></div><?php endif;?>
        </div>
      </div>
      <div class="kv">
        <?php if($owner['location']):?><div><b>location:</b> <?=h($owner['location'])?></div><?php endif;?>
        <?php if($owner['email']):?><div><b>email:</b> <a href="mailto:<?=h($owner['email'])?>"><?=h($owner['email'])?></a></div><?php endif;?>
        <?php if($owner['github']):?><div><b>github:</b> <a href="<?=h($owner['github'])?>" target="_blank"><?=h($owner['github'])?></a></div><?php endif;?>
        <?php if($owner['website']):?><div><b>web:</b> <a href="<?=h($owner['website'])?>" target="_blank"><?=h($owner['website'])?></a></div><?php endif;?>
      </div>
      <?php if($owner['summary']):?><p class="bio"># <?=nl2br(h($owner['summary']))?></p><?php endif;?>
      <?php if($owner['skills']):?>
        <p class="prompt" style="margin-top:16px">cat skills.txt</p>
        <div class="chips"><?php foreach($owner['skills'] as $s):?><span class="chip"><?=h($s)?></span><?php endforeach;?></div>
      <?php endif;?>
      <p class="visits">// <?=number_format($visitCount)?> visits logged</p>
    </div>
  </div>

  <p class="prompt" style="margin:0 0 14px 4px">ls ./projects</p>
  <div class="grid">
    <?php if(!$projects):?><div class="empty" style="grid-column:1/-1">// no projects yet</div><?php else: foreach($projects as $p):?>
    <div class="card">
      <div class="cv"><?php if($p['cover']):?><img src="<?=h($p['cover'])?>" alt=""><?php else:?>{ }<?php endif;?></div>
      <div class="p">
        <h3><?=h($p['title'])?></h3>
        <?php if(trim($p['description'])):?><p class="d"><?=nl2br(h($p['description']))?></p><?php endif;?>
        <div class="lk">
          <?php if($p['github_link']):?><a href="<?=h($p['github_link'])?>" target="_blank">[github]</a><?php endif;?>
          <?php if($p['live_link']):?><a href="<?=h($p['live_link'])?>" target="_blank">[live]</a><?php endif;?>
        </div>
      </div>
    </div>
    <?php endforeach; endif;?>
  </div>
  <p class="foot">// built with Portfolio Builder</p>
</div>
</body></html>
