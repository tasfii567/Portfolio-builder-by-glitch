<?php
/**
 * edit-profile.php — Step 1 of the builder.
 * Collects name, photo, email, GitHub, education, experience and skills.
 * On submit it sends you to choose a portfolio template.
 */
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/inc/profile.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = (int) $_SESSION['user_id'];

$u = $pdo->prepare("SELECT * FROM users WHERE id = ?"); $u->execute([$userId]); $user = $u->fetch();
$prof = get_profile($pdo, $userId);

$avatarDir = __DIR__ . '/uploads/avatars/';
if (!is_dir($avatarDir)) @mkdir($avatarDir, 0775, true);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_profile') {
    $name       = trim($_POST['name'] ?? '');
    $role       = trim($_POST['role'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $github     = trim($_POST['github'] ?? '');
    $website    = trim($_POST['website'] ?? '');
    $location   = trim($_POST['location'] ?? '');
    $skills     = trim($_POST['skills'] ?? '');
    $education  = trim($_POST['education'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $summary    = trim($_POST['summary'] ?? '');

    if ($name === '' || $email === '') {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } else {
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $chk->execute([$email, $userId]);
        if ($chk->fetch()) {
            $error = 'That email is already used by another account.';
        } else {
            // ---- profile image upload (optional) ----
            $imagePath = $prof['image'] ?? '';
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $info = @getimagesize($_FILES['image']['tmp_name']);
                $allowed = ['jpeg','jpg','png','gif','webp'];
                if ($info !== false) {
                    $ext = strtolower(image_type_to_extension($info[2], false));
                    if (in_array($ext, $allowed, true) && $_FILES['image']['size'] <= 4*1024*1024) {
                        $fname = 'u' . $userId . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $avatarDir . $fname)) {
                            // remove old file
                            if ($imagePath && is_file(__DIR__ . '/' . $imagePath)) @unlink(__DIR__ . '/' . $imagePath);
                            $imagePath = 'uploads/avatars/' . $fname;
                        }
                    } else { $error = 'Image must be JPG/PNG/GIF/WEBP and under 4 MB.'; }
                } else { $error = 'That file is not a valid image.'; }
            }

            if ($error === '') {
                $pdo->prepare("UPDATE users SET name=?, role=?, email=? WHERE id=?")
                    ->execute([$name, $role !== '' ? $role : 'Member', $email, $userId]);
                $pdo->prepare("UPDATE profiles SET image=?, github=?, website=?, location=?, summary=?, skills=?, education=?, experience=? WHERE user_id=?")
                    ->execute([$imagePath, $github, $website, $location, $summary, $skills, $education, $experience, $userId]);
                $pdo->prepare("UPDATE portfolios SET owner_name=?, owner_role=? WHERE user_id=?")
                    ->execute([$name, $role !== '' ? $role : 'Member', $userId]);
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role !== '' ? $role : 'Member';
                // Step 1 done -> go choose a template (Step 2)
                header('Location: templates.php?from=profile');
                exit;
            }
        }
    }
    // keep typed values on error
    $user['name']=$name; $user['role']=$role; $user['email']=$email;
    $prof['github']=$github; $prof['website']=$website; $prof['location']=$location;
    $prof['skills']=$skills; $prof['education']=$education; $prof['experience']=$experience; $prof['summary']=$summary;
}

$userName = $user['name']; $userRole = $user['role'];
$initials = strtoupper(substr($userName,0,1) . (strpos($userName,' ')!==false ? substr($userName,strpos($userName,' ')+1,1) : ''));
$ACTIVE = 'profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile — Portfolio Builder</title>
<link rel="stylesheet" href="assets/app.css">
<style>
.steps{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:22px}
.step{font-size:12px;font-weight:600;color:var(--muted);background:var(--surface);border:1px solid var(--line);
  padding:8px 14px;border-radius:20px}
.step.on{color:#fff;background:linear-gradient(135deg,var(--primary),var(--accent));border-color:transparent}
.avatar-row{display:flex;align-items:center;gap:18px;margin-bottom:6px}
.avatar-prev{width:84px;height:84px;border-radius:50%;background:var(--surface-2);border:1px solid var(--line);
  overflow:hidden;display:grid;place-items:center;font-weight:700;font-size:26px;color:var(--primary-soft);flex-shrink:0}
.avatar-prev img{width:100%;height:100%;object-fit:cover}
</style>
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/inc/sidebar.php'; ?>
  <main class="main">
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:14px">
        <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
        <div><h2>Edit Profile</h2><p>Step 1 — tell us about yourself.</p></div>
      </div>
      <div class="profile">
        <div class="who"><b><?= htmlspecialchars($userName) ?></b><small><?= htmlspecialchars($userRole) ?></small></div>
        <div class="avatar"><?= htmlspecialchars($initials) ?></div>
      </div>
    </div>

    <div class="steps">
      <span class="step on">1 · Profile</span>
      <span class="step">2 · Choose Template</span>
      <span class="step">3 · Create Portfolio</span>
      <span class="step">4 · Resume / CV</span>
    </div>

    <?php if ($error): ?><div class="error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <section class="panel">
      <h3>👤 Your Details</h3>
      <p class="lead">Fill this in once — it powers your portfolio, your CV and your job matches.</p>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save_profile">

        <div class="avatar-row">
          <div class="avatar-prev" id="avatarPrev">
            <?php if (!empty($prof['image'])): ?><img src="<?= htmlspecialchars($prof['image']) ?>" alt=""><?php else: ?><?= htmlspecialchars($initials) ?><?php endif; ?>
          </div>
          <div class="field" style="flex:1">
            <label>Profile image</label>
            <input type="file" name="image" accept="image/*" onchange="previewAvatar(event)">
            <span class="hint">JPG, PNG, GIF or WEBP · up to 4 MB.</span>
          </div>
        </div>

        <div class="form-grid" style="margin-top:8px">
          <div class="field"><label>Full name *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required></div>
          <div class="field"><label>Professional title</label>
            <input type="text" name="role" value="<?= htmlspecialchars($user['role']) ?>" placeholder="e.g. AI Engineer"></div>
          <div class="field"><label>Email *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></div>
          <div class="field"><label>GitHub</label>
            <input type="url" name="github" value="<?= htmlspecialchars($prof['github']) ?>" placeholder="https://github.com/you"></div>
          <div class="field"><label>Website / portfolio</label>
            <input type="url" name="website" value="<?= htmlspecialchars($prof['website']) ?>" placeholder="https://…"></div>
          <div class="field"><label>Location</label>
            <input type="text" name="location" value="<?= htmlspecialchars($prof['location']) ?>" placeholder="City, Country"></div>
          <div class="field full"><label>Short bio / summary</label>
            <textarea name="summary" placeholder="A sentence or two about you…"><?= htmlspecialchars($prof['summary'] ?? '') ?></textarea></div>
          <div class="field full"><label>Skills</label>
            <textarea name="skills" placeholder="Comma separated, e.g. Python, Machine Learning, HTML, CSS, JavaScript, SQL"><?= htmlspecialchars($prof['skills'] ?? '') ?></textarea>
            <span class="hint">Used to build your CV and calculate job matches.</span></div>
          <div class="field full"><label>Education</label>
            <textarea name="education" placeholder="B.Sc in Computer Science — University (2020–2024)"><?= htmlspecialchars($prof['education'] ?? '') ?></textarea></div>
          <div class="field full"><label>Experience</label>
            <textarea name="experience" style="min-height:110px" placeholder="Job title — Company (2023–2024)&#10;• What you did…"><?= htmlspecialchars($prof['experience'] ?? '') ?></textarea></div>
        </div>

        <div class="form-actions">
          <button class="btn btn-primary" type="submit">Save & Choose Template →</button>
        </div>
      </form>
    </section>
  </main>
</div>
<script>
function previewAvatar(e){
  var file = e.target.files[0]; if(!file) return;
  var url = URL.createObjectURL(file);
  document.getElementById('avatarPrev').innerHTML = '<img src="'+url+'" alt="">';
}
</script>
</body>
</html>
