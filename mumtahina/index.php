<?php
/** index.php — public landing page (PortfolioBuilder light theme). */
session_start();
if (isset($_SESSION['user_id'])) { header('Location: Dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PortfolioBuilder — Your work deserves to be seen</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#f4f1ea;--surface:#fff;--line:#e4ddcc;--text:#1d211a;--muted:#797f6f;
  --primary:#3a4a23;--accent:#7d9e58;--radius:14px;
  font-family:"Plus Jakarta Sans",system-ui,Arial,sans-serif}
*{box-sizing:border-box;margin:0;padding:0}a{text-decoration:none;color:inherit}
body{background:var(--bg);color:var(--text)}
.nav{display:flex;justify-content:space-between;align-items:center;padding:22px 7vw;border-bottom:1px solid var(--line)}
.brand{font-size:22px;font-weight:800;letter-spacing:-.5px}
.brand .g{color:var(--accent)}
.nav .right{display:flex;align-items:center;gap:20px}
.nav .login{font-weight:600;font-size:15px;color:var(--text)}
.nav .login:hover{color:var(--primary)}
.btn{font-weight:700;font-size:15px;padding:13px 24px;border-radius:13px;transition:.18s;display:inline-block}
.btn-primary{background:var(--primary);color:#fff}
.btn-primary:hover{background:#2f3d1c;transform:translateY(-1px);box-shadow:0 10px 22px rgba(40,45,30,.18)}
.btn-ghost{background:transparent;border:1px solid #ccc4b0;color:var(--text)}
.btn-ghost:hover{border-color:var(--primary);background:#fff}

.hero{padding:90px 7vw 50px;max-width:1100px}
.eyebrow{color:var(--accent);font-weight:700;font-size:14px;letter-spacing:2.5px;margin-bottom:22px}
.hero h1{font-size:clamp(40px,7vw,76px);font-weight:800;line-height:1.04;letter-spacing:-1.5px;max-width:760px}
.hero h1 .g{color:var(--accent)}
.hero p{color:var(--muted);font-size:clamp(17px,2.2vw,21px);line-height:1.6;max-width:560px;margin:26px 0 36px}
.cta{display:flex;gap:14px;flex-wrap:wrap}
.cta .btn{padding:15px 30px;font-size:16px}

.divider{max-width:1100px;margin:0 auto;border-top:1px solid var(--line)}
.stats{display:flex;gap:60px;flex-wrap:wrap;padding:40px 7vw 10px;max-width:1100px}
.stat .num{font-size:40px;font-weight:800;letter-spacing:-1px}
.stat .num .g{color:var(--accent)}
.stat .lbl{color:var(--muted);font-size:14px;margin-top:4px}

.features{max-width:1100px;margin:0 auto;padding:60px 7vw 80px;display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.feat{background:var(--surface);border:1px solid var(--line);border-radius:18px;padding:28px}
.feat .ic{width:48px;height:48px;border-radius:12px;background:#e7efd9;color:var(--primary);
  display:grid;place-items:center;font-size:22px;margin-bottom:16px}
.feat h3{font-size:18px;font-weight:800;margin-bottom:8px}
.feat p{color:var(--muted);font-size:14px;line-height:1.6}
.foot{text-align:center;color:var(--muted);font-size:13px;padding:30px;border-top:1px solid var(--line)}
@media(max-width:780px){.features{grid-template-columns:1fr}.stats{gap:36px}}
</style>
</head>
<body>

<nav class="nav">
  <div class="brand">Portfolio<span class="g">Builder</span></div>
  <div class="right">
    <a href="login.php" class="login">Log in</a>
    <a href="register.php" class="btn btn-primary">Sign up free</a>
  </div>
</nav>

<header class="hero">
  <div class="eyebrow">PORTFOLIO PLATFORM</div>
  <h1>Your work deserves to be <span class="g">seen.</span></h1>
  <p>Build a beautiful portfolio in minutes. No code, no clutter — just your work, perfectly presented.</p>
  <div class="cta">
    <a href="register.php" class="btn btn-primary">Start for free</a>
    <a href="login.php" class="btn btn-ghost">I already have an account</a>
  </div>
</header>

<div class="divider"></div>
<section class="stats">
  <div class="stat"><div class="num">5<span class="g">+</span></div><div class="lbl">Portfolio templates</div></div>
  <div class="stat"><div class="num">1<span class="g">-click</span></div><div class="lbl">Shareable link</div></div>
  <div class="stat"><div class="num">PDF<span class="g">.</span></div><div class="lbl">Downloadable CV</div></div>
  <div class="stat"><div class="num">Free<span class="g">.</span></div><div class="lbl">Forever, no card</div></div>
</section>

<section class="features">
  <div class="feat"><div class="ic">📁</div><h3>Add your projects</h3>
    <p>Upload images, write descriptions and attach GitHub &amp; live demo links — all in one place.</p></div>
  <div class="feat"><div class="ic">🎨</div><h3>Pick a template</h3>
    <p>Choose from 5 ready-made designs and switch the whole look of your portfolio in one click.</p></div>
  <div class="feat"><div class="ic">🔗</div><h3>Share one link</h3>
    <p>Get a clean, shareable URL. Anyone can view your live portfolio — no account needed.</p></div>
</section>

<p class="foot">PortfolioBuilder · PHP · MySQL · Built with care</p>
</body>
</html>
