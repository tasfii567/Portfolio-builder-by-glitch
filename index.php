<?php
/**
 * index.php — public landing page.
 * Describes the product, then sends people to register or log in.
 * If already logged in, jump straight to the dashboard.
 */
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: Dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portfolio Builder — Build · Showcase · Get Hired</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{
  --bg:#0f1117;--surface:#171a23;--surface-2:#1f2430;--line:#2a3040;
  --text:#e8eaf0;--muted:#9aa3b5;--primary:#6c5ce7;--primary-soft:#8b7cf0;--accent:#ff7a59;
}
body{background:var(--bg);color:var(--text);
  font-family:"Segoe UI",system-ui,-apple-system,Roboto,Arial,sans-serif}
a{text-decoration:none}
.nav-top{display:flex;justify-content:space-between;align-items:center;padding:20px 6vw}
.brand{display:flex;align-items:center;gap:12px}
.brand .logo{width:42px;height:42px;border-radius:12px;
  background:linear-gradient(135deg,var(--primary),var(--accent));
  display:grid;place-items:center;font-weight:800;font-size:18px;color:#fff}
.brand h1{font-size:16px;margin:0}
.brand span{font-size:11px;color:var(--muted);letter-spacing:.5px}
.nav-top .links{display:flex;gap:10px}
.btn-ghost{color:var(--text);border:1px solid var(--line);background:var(--surface);
  padding:10px 18px;border-radius:10px;font-weight:600;font-size:14px}
.btn-ghost:hover{border-color:var(--primary);color:#fff}
.btn-grad{background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;
  padding:10px 18px;border-radius:10px;font-weight:600;font-size:14px}
.btn-grad:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.3);color:#fff}

.hero{text-align:center;padding:60px 6vw 40px;position:relative;overflow:hidden}
.hero .pill{display:inline-block;font-size:12px;font-weight:700;letter-spacing:.5px;color:var(--primary-soft);
  background:rgba(108,92,231,.14);border:1px solid rgba(108,92,231,.3);padding:7px 16px;border-radius:30px;margin-bottom:22px}
.hero h2{font-size:clamp(30px,6vw,52px);line-height:1.1;margin-bottom:18px;font-weight:800}
.hero h2 .grad{background:linear-gradient(120deg,var(--primary-soft),var(--accent));
  -webkit-background-clip:text;background-clip:text;color:transparent}
.hero p{color:var(--muted);font-size:17px;max-width:620px;margin:0 auto 30px;line-height:1.6}
.hero .cta-row{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.cta-big{font-size:16px;padding:14px 30px;border-radius:30px}

.features{max-width:1040px;margin:30px auto 70px;padding:0 6vw}
.feat{background:var(--surface);border:1px solid var(--line);border-radius:16px;padding:26px;height:100%}
.feat .ic{width:50px;height:50px;border-radius:14px;display:grid;place-items:center;font-size:24px;margin-bottom:16px;
  background:linear-gradient(135deg,rgba(108,92,231,.25),rgba(255,122,89,.18))}
.feat h3{font-size:17px;margin-bottom:8px}
.feat p{color:var(--muted);font-size:14px;line-height:1.6;margin:0}
.foot{text-align:center;color:var(--muted);font-size:13px;padding:24px}
</style>
</head>
<body>

<div class="nav-top">
  <div class="brand">
    <div class="logo">P</div>
    <div><h1>Portfolio Builder</h1><span>BUILD · SHOWCASE · GET HIRED</span></div>
  </div>
  <div class="links">
    <a href="login.php" class="btn-ghost">Log in</a>
    <a href="register.php" class="btn-grad">Get started</a>
  </div>
</div>

<section class="hero">
  <span class="pill">✨ YOUR PORTFOLIO, ONLINE IN MINUTES</span>
  <h2>Build a stunning <span class="grad">developer portfolio</span><br>and share it with one link.</h2>
  <p>Create an account, add your projects with images and links, pick a ready-made template,
     and instantly get a shareable URL you can send to recruiters — no coding, no login needed for viewers.</p>
  <div class="cta-row">
    <a href="register.php" class="btn-grad cta-big">Create your portfolio →</a>
    <a href="login.php" class="btn-ghost cta-big">I already have an account</a>
  </div>
</section>

<section class="features">
  <div class="row g-4">
    <div class="col-12 col-md-4">
      <div class="feat">
        <div class="ic">📁</div>
        <h3>Add your projects</h3>
        <p>Upload project images, write descriptions, and attach GitHub & live demo links — all in one place.</p>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="feat">
        <div class="ic">🎨</div>
        <h3>Pick a template</h3>
        <p>Choose from ready-made, editable templates. Switch the whole look of your portfolio in one click.</p>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="feat">
        <div class="ic">🔗</div>
        <h3>Share one link</h3>
        <p>Get a clean, shareable URL. Anyone can view your live portfolio — they don't need an account.</p>
      </div>
    </div>
  </div>
</section>

<p class="foot">Built with Portfolio Builder · PHP · MySQL · Bootstrap</p>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
