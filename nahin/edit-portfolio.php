<?php
require 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$errors = [];
$saved  = isset($_GET['saved']);

// ---------- Helper: handle a single file upload ----------
function handle_upload($file, $destDir, $allowedExt, $maxBytes)
{
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed (error code ' . $file['error'] . ').'];
    }
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'error' => 'File "' . $file['name'] . '" is too large.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return ['success' => false, 'error' => 'File type ".' . $ext . '" is not allowed.'];
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $ext;
    $destPath = rtrim($destDir, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['success' => false, 'error' => 'Could not save uploaded file.'];
    }

    return ['success' => true, 'filename' => $filename];
}

// ---------- Load current user + related rows ----------
$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: login.php");
    exit;
}

// Profile lives in its own table - may not exist yet for this user
$stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch();

if (!$profile) {
    $profile = [
        'avatar' => null,
        'title' => null,
        'bio' => null,
        'location' => null,
        'phone' => null,
        'resume_path' => null,
        'resume_downloadable' => 1,
        'theme' => 'light',
        'visibility' => 'public',
        'contact_email' => null,
        'contact_phone' => null,
        'enable_contact_form' => 1,
        'email_notifications' => 1,
    ];
}

function fetch_all($pdo, $table, $userId)
{
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE user_id = ? ORDER BY id ASC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

$socialLinks   = fetch_all($pdo, 'social_links', $userId);
$skills        = fetch_all($pdo, 'skills', $userId);
$experience    = fetch_all($pdo, 'experience', $userId);
$education     = fetch_all($pdo, 'education', $userId);
$projects      = fetch_all($pdo, 'projects', $userId);
$certifications = fetch_all($pdo, 'certifications', $userId);
$achievements  = fetch_all($pdo, 'achievements', $userId);

// ---------- Handle form submit ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ----- Personal info -----
    $name     = trim($_POST['name'] ?? '');
    $title    = trim($_POST['title'] ?? '');
    $bio      = trim($_POST['bio'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');

    if ($name === '') {
        $errors[] = "Name is required.";
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    } elseif ($email !== $user['email']) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $userId]);
        if ($check->fetch()) {
            $errors[] = "That email is already used by another account.";
        }
    }

    // ----- Avatar upload -----
    $avatarPath = $profile['avatar'];
    if (!empty($_FILES['avatar']['name'])) {
        $res = handle_upload($_FILES['avatar'], __DIR__ . '/uploads/avatars', ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
        if ($res['success']) {
            $avatarPath = 'uploads/avatars/' . $res['filename'];
        } else {
            $errors[] = $res['error'];
        }
    }

    // ----- Resume upload -----
    // $resumePath = $profile['resume_path'];
    // if (!empty($_FILES['resume']['name'])) {
    //     $res = handle_upload($_FILES['resume'], __DIR__ . '/uploads/resumes', ['pdf'], 5 * 1024 * 1024);
    //     if ($res['success']) {
    //         $resumePath = 'uploads/resumes/' . $res['filename'];
    //     } else {
    //         $errors[] = $res['error'];
    //     }
    // }
    // $resumeDownloadable = isset($_POST['resume_downloadable']) ? 1 : 0;

    // ----- Settings -----
    $theme              = ($_POST['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
    $visibility         = ($_POST['visibility'] ?? 'public') === 'private' ? 'private' : 'public';
    $contactEmail       = trim($_POST['contact_email'] ?? '');
    $contactPhone       = trim($_POST['contact_phone'] ?? '');
    $enableContactForm  = isset($_POST['enable_contact_form']) ? 1 : 0;
    $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;

    if (empty($errors)) {
        // ----- Save name/email to users (login table - untouched structure) -----
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $userId]);

        // ----- Upsert profile (separate table, one row per user) -----
        // NOTE: resume_path / resume_downloadable removed from this query
        // because the resume upload section is currently disabled (commented out)
        // above and in the form. Re-add both the columns/placeholders here AND
        // the $resumePath/$resumeDownloadable values below together when you
        // re-enable that feature.
        $stmt = $pdo->prepare("
            INSERT INTO profiles (
                user_id, avatar, title, bio, location, phone,
                theme, visibility,
                contact_email, contact_phone, enable_contact_form, email_notifications
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                avatar = VALUES(avatar),
                title = VALUES(title),
                bio = VALUES(bio),
                location = VALUES(location),
                phone = VALUES(phone),
                theme = VALUES(theme),
                visibility = VALUES(visibility),
                contact_email = VALUES(contact_email),
                contact_phone = VALUES(contact_phone),
                enable_contact_form = VALUES(enable_contact_form),
                email_notifications = VALUES(email_notifications)
        ");
        $stmt->execute([
            $userId,
            $avatarPath,
            $title,
            $bio,
            $location,
            $phone,
            // $resumePath,
            // $resumeDownloadable,
            $theme,
            $visibility,
            $contactEmail,
            $contactPhone,
            $enableContactForm,
            $emailNotifications
        ]);

        // ----- Social links -----
        $pdo->prepare("DELETE FROM social_links WHERE user_id = ?")->execute([$userId]);
        if (!empty($_POST['platform'])) {
            $ins = $pdo->prepare("INSERT INTO social_links (user_id, platform, url) VALUES (?, ?, ?)");
            foreach ($_POST['platform'] as $i => $platform) {
                $url = trim($_POST['social_url'][$i] ?? '');
                if ($platform !== '' && $url !== '') {
                    $ins->execute([$userId, $platform, $url]);
                }
            }
        }

        // ----- Skills -----
        $pdo->prepare("DELETE FROM skills WHERE user_id = ?")->execute([$userId]);
        if (!empty($_POST['skill_name'])) {
            $ins = $pdo->prepare("INSERT INTO skills (user_id, skill_name, level) VALUES (?, ?, ?)");
            foreach ($_POST['skill_name'] as $i => $skillName) {
                $skillName = trim($skillName);
                $level = $_POST['skill_level'][$i] ?? 'Beginner';
                if ($skillName !== '') {
                    $ins->execute([$userId, $skillName, $level]);
                }
            }
        }

        // ----- Work experience -----
        $pdo->prepare("DELETE FROM experience WHERE user_id = ?")->execute([$userId]);
        if (!empty($_POST['company'])) {
            $ins = $pdo->prepare("
                INSERT INTO experience (user_id, company, position, start_date, end_date, currently_working, description)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($_POST['company'] as $i => $company) {
                $company = trim($company);
                if ($company === '') continue;
                $position  = trim($_POST['position'][$i] ?? '');
                $startDate = $_POST['start_date'][$i] ?? '';
                $current   = isset($_POST['currently_working'][$i]) ? 1 : 0;
                $endDate   = $current ? null : ($_POST['end_date'][$i] ?? '');
                $desc      = trim($_POST['exp_description'][$i] ?? '');
                $ins->execute([
                    $userId,
                    $company,
                    $position,
                    $startDate !== '' ? $startDate : null,
                    $endDate !== '' ? $endDate : null,
                    $current,
                    $desc
                ]);
            }
        }

        // ----- Education -----
        $pdo->prepare("DELETE FROM education WHERE user_id = ?")->execute([$userId]);
        if (!empty($_POST['institution'])) {
            $ins = $pdo->prepare("
                INSERT INTO education (user_id, institution, degree, field_of_study, start_year, end_year, gpa)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($_POST['institution'] as $i => $institution) {
                $institution = trim($institution);
                if ($institution === '') continue;
                $degree = trim($_POST['degree'][$i] ?? '');
                $field  = trim($_POST['field_of_study'][$i] ?? '');
                $sy     = $_POST['start_year'][$i] ?? '';
                $ey     = $_POST['end_year'][$i] ?? '';
                $gpa    = trim($_POST['gpa'][$i] ?? '');
                $ins->execute([
                    $userId,
                    $institution,
                    $degree,
                    $field,
                    $sy !== '' ? $sy : null,
                    $ey !== '' ? $ey : null,
                    $gpa
                ]);
            }
        }

        // ----- Projects (each row can have its own image) -----
        $pdo->prepare("DELETE FROM projects WHERE user_id = ?")->execute([$userId]);
        if (!empty($_POST['project_title'])) {
            $ins = $pdo->prepare("
                INSERT INTO projects (user_id, title, description, technologies, image_path, demo_url, github_url, featured)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($_POST['project_title'] as $i => $pTitle) {
                $pTitle = trim($pTitle);
                if ($pTitle === '') continue;

                $pDesc  = trim($_POST['project_description'][$i] ?? '');
                $pTech  = trim($_POST['project_tech'][$i] ?? '');
                $pDemo  = trim($_POST['project_demo'][$i] ?? '');
                $pGit   = trim($_POST['project_github'][$i] ?? '');
                $pFeat  = isset($_POST['project_featured'][$i]) ? 1 : 0;

                // keep existing image unless a new one is uploaded
                $pImage = $_POST['project_existing_image'][$i] ?? null;

                if (!empty($_FILES['project_image']['name'][$i])) {
                    $f = [
                        'name'     => $_FILES['project_image']['name'][$i],
                        'type'     => $_FILES['project_image']['type'][$i],
                        'tmp_name' => $_FILES['project_image']['tmp_name'][$i],
                        'error'    => $_FILES['project_image']['error'][$i],
                        'size'     => $_FILES['project_image']['size'][$i],
                    ];
                    $res = handle_upload($f, __DIR__ . '/uploads/projects', ['jpg', 'jpeg', 'png', 'webp'], 3 * 1024 * 1024);
                    if ($res['success']) {
                        $pImage = 'uploads/projects/' . $res['filename'];
                    }
                }

                $ins->execute([$userId, $pTitle, $pDesc, $pTech, $pImage, $pDemo, $pGit, $pFeat]);
            }
        }

        // ----- Certifications -----
        $pdo->prepare("DELETE FROM certifications WHERE user_id = ?")->execute([$userId]);
        if (!empty($_POST['cert_name'])) {
            $ins = $pdo->prepare("
                INSERT INTO certifications (user_id, name, issuing_org, date_issued, credential_url)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($_POST['cert_name'] as $i => $certName) {
                $certName = trim($certName);
                if ($certName === '') continue;
                $org  = trim($_POST['cert_org'][$i] ?? '');
                $date = $_POST['cert_date'][$i] ?? '';
                $url  = trim($_POST['cert_url'][$i] ?? '');
                $ins->execute([$userId, $certName, $org, $date !== '' ? $date : null, $url]);
            }
        }

        // ----- Achievements -----
        $pdo->prepare("DELETE FROM achievements WHERE user_id = ?")->execute([$userId]);
        if (!empty($_POST['achievement_title'])) {
            $ins = $pdo->prepare("
                INSERT INTO achievements (user_id, title, description, achieved_on)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($_POST['achievement_title'] as $i => $aTitle) {
                $aTitle = trim($aTitle);
                if ($aTitle === '') continue;
                $aDesc = trim($_POST['achievement_description'][$i] ?? '');
                $aDate = $_POST['achievement_date'][$i] ?? '';
                $ins->execute([$userId, $aTitle, $aDesc, $aDate !== '' ? $aDate : null]);
            }
        }

        header("Location: demo.php");
        exit;
    }
}

$initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') !== false ? substr($user['name'], strpos($user['name'], ' ') + 1, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Portfolio — PortfolioBuilder</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f7f3ea;
            --surface: #fff;
            --surface-2: #ede7d6;
            --line: #e8e1d3;
            --text: #2b2926;
            --muted: #8a8270;
            --muted-2: #a39c89;
            --primary: #36402c;
            --primary-soft: #46532f;
            --accent: #6b8c5a;
            --good: #6f9a52;
            --radius: 14px;
            --shadow: 0 8px 24px rgba(43, 41, 38, .07);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif !important;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            margin: 0;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .app {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: var(--surface);
            border-right: 1px solid var(--line);
            padding: 24px 18px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 8px 22px;
        }

        .brand .logo {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            background: var(--primary);
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 18px;
            color: #fff;
            flex-shrink: 0;
        }

        .brand h1 {
            font-size: 16px;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: -.02em;
            margin: 0;
        }

        .brand h1 .g {
            color: var(--accent);
        }

        .brand span {
            font-size: 11px;
            color: var(--muted-2);
            letter-spacing: .5px;
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 6px;
        }

        .nav-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted-2);
            padding: 14px 12px 6px;
        }

        .nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border-radius: 10px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
            transition: .18s;
        }

        .nav a .ic {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .nav a:hover {
            background: var(--surface-2);
            color: var(--text);
        }

        .nav a.active {
            background: var(--primary);
            color: #fff;
        }

        .logout {
            margin-top: auto;
        }

        .logout a {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            background: #f3e3df;
            color: #a8442f;
            border: 1px solid #ecc9c1;
            transition: .18s;
        }

        .logout a:hover {
            background: #eed6d0;
        }

        /* Main */
        .main {
            padding: 28px 34px;
            overflow-x: hidden;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 26px;
            flex-wrap: wrap;
        }

        .topbar h2 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -.02em;
            margin: 0;
            color: var(--text);
        }

        .topbar p {
            font-size: 13px;
            color: var(--muted);
            margin: 2px 0 0;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--primary);
            display: grid;
            place-items: center;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
            font-size: 14px;
        }

        .profile .who {
            text-align: right;
        }

        .profile .who b {
            font-size: 14px;
            display: block;
            color: var(--text);
        }

        .profile .who small {
            font-size: 12px;
            color: var(--muted);
        }

        .menu-btn {
            display: none;
        }

        /* Form sections recolored to match index.php palette */
        .section-card {
            margin-bottom: 1.5rem;
            border: 1px solid var(--line) !important;
            border-radius: var(--radius) !important;
            box-shadow: var(--shadow) !important;
            background: var(--surface) !important;
        }

        .section-card h4 {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -.01em;
            color: var(--text);
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
        }

        .form-control,
        .form-select,
        select.form-control,
        textarea.form-control {
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            color: var(--text);
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(107, 140, 90, .15);
        }

        .form-check-input:checked {
            background-color: var(--accent);
            border-color: var(--accent);
        }

        .repeat-row {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: .75rem;
            position: relative;
            background: var(--surface-2);
        }

        .remove-row-btn {
            position: absolute;
            top: .5rem;
            right: .5rem;
        }

        .avatar-preview {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 50%;
            border: 1px solid var(--line);
        }

        .project-thumb {
            width: 100%;
            max-height: 140px;
            object-fit: cover;
            border-radius: .5rem;
            border: 1px solid var(--line);
        }

        .btn-outline-primary {
            color: var(--primary) !important;
            border-color: var(--accent) !important;
        }

        .btn-outline-primary:hover {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            color: #fff !important;
        }

        .btn-save-theme {
            background: var(--primary);
            border: none;
            color: #fff;
            transition: background .2s, transform .15s;
            border-radius: 10px;
        }

        .btn-save-theme:hover {
            background: var(--primary-soft);
            transform: translateY(-1px);
            color: #fff;
        }

        .alert-success {
            background: #e8efe0;
            color: #4f6b43;
            border: 1px solid #cfe0b6;
        }

        .alert-danger {
            background: #f3e3df;
            color: #a8442f;
            border: 1px solid #ecc9c1;
        }

        @media (max-width: 760px) {
            .app {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: fixed;
                left: -280px;
                z-index: 50;
                transition: .25s;
                width: 260px;
            }

            .sidebar.open {
                left: 0;
            }

            .menu-btn {
                display: grid;
                place-items: center;
                width: 42px;
                height: 42px;
                border-radius: 10px;
                background: var(--surface);
                border: 1px solid var(--line);
                color: var(--text);
                font-size: 20px;
                cursor: pointer;
            }

            .main {
                padding: 20px;
            }

            .profile .who {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="app">

        <aside class="sidebar" id="sidebar">
            <div class="brand">
                <div class="logo">P</div>
                <div>
                    <h1>Portfolio<span class="g">Builder</span></h1>
                    <span>BUILD · SHOWCASE · GET HIRED</span>
                </div>
            </div>
            <nav class="nav">
                <div class="nav-label">Menu</div>
                <a href="Dashboard.php"><span class="ic">🏠</span> Dashboard</a>
                <a href="edit-portfolio.php" class="active"><span class="ic">👤</span> Edit Portfolio</a>
                <a href="demo.php"><span class="ic">🎨</span> Choose Template</a>
                <a href="create-portfolio.php"><span class="ic">📁</span> Create Portfolio</a>
                <a href="create-resume.php"><span class="ic">📄</span> Create Resume</a>
                <a href="job-match.php"><span class="ic">📊</span> Job Match</a>
            </nav>
            <div class="logout">
                <a href="Logout.php"><span>⏻</span> Logout</a>
            </div>
        </aside>

        <main class="main">
            <div class="topbar">
                <div style="display:flex;align-items:center;gap:14px">
                    <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                    <div>
                        <h2>Edit Portfolio</h2>
                        <p>Keep your profile, projects and skills up to date.</p>
                    </div>
                </div>
                <div class="profile">
                    <div class="who"><b><?= htmlspecialchars($user['name']) ?></b><small><?= htmlspecialchars($user['email']) ?></small></div>
                    <div class="avatar"><?= htmlspecialchars($initials) ?></div>
                </div>
            </div>

            <div class="container-fluid px-0">
                <?php if ($saved): ?>
                    <div class="alert alert-success">Your portfolio has been saved.</div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" novalidate>

                    <!-- ===================== 1. PERSONAL INFO ===================== -->
                    <div class="card section-card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Personal Information</h4>

                            <div class="d-flex align-items-center gap-3 mb-3">
                                <?php if (!empty($profile['avatar'])): ?>
                                    <img src="<?= htmlspecialchars($profile['avatar']) ?>" class="avatar-preview" alt="Avatar">
                                <?php endif; ?>
                                <div>
                                    <label class="form-label d-block">Profile Photo</label>
                                    <input type="file" name="avatar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="name" class="form-control" required
                                        value="<?= htmlspecialchars($user['name']) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Professional Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="e.g. Frontend Developer"
                                        value="<?= htmlspecialchars($profile['title'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Short Bio / About Me</label>
                                <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control"
                                        value="<?= htmlspecialchars($profile['location'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-control" required
                                        value="<?= htmlspecialchars($user['email']) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control"
                                        value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===================== 2. SOCIAL LINKS ===================== -->
                    <div class="card section-card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Social Links</h4>
                            <div id="social-container">
                                <?php foreach ($socialLinks as $link): ?>
                                    <div class="repeat-row">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <select name="platform[]" class="form-control">
                                                    <?php foreach (['GitHub', 'LinkedIn', 'Twitter/X', 'Facebook', 'Instagram', 'Behance', 'Dribbble', 'Medium', 'Portfolio Website'] as $p): ?>
                                                        <option value="<?= $p ?>" <?= $link['platform'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-8 mb-2">
                                                <input type="url" name="social_url[]" class="form-control" placeholder="https://..."
                                                    value="<?= htmlspecialchars($link['url']) ?>">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSocialRow()">+ Add Social Link</button>
                        </div>
                    </div>

                    <!-- ===================== 3. SKILLS ===================== -->
                    <div class="card section-card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Skills</h4>
                            <div id="skills-container">
                                <?php foreach ($skills as $skill): ?>
                                    <div class="repeat-row">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
                                        <div class="row">
                                            <div class="col-md-8 mb-2">
                                                <input type="text" name="skill_name[]" class="form-control" placeholder="e.g. React"
                                                    value="<?= htmlspecialchars($skill['skill_name']) ?>">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <select name="skill_level[]" class="form-control">
                                                    <?php foreach (['Beginner', 'Intermediate', 'Expert'] as $lvl): ?>
                                                        <option value="<?= $lvl ?>" <?= $skill['level'] === $lvl ? 'selected' : '' ?>><?= $lvl ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSkillRow()">+ Add Skill</button>
                        </div>
                    </div>

                    <!-- ===================== 4. WORK EXPERIENCE ===================== -->
                    <div class="card section-card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Work Experience</h4>
                            <div id="experience-container">
                                <?php foreach ($experience as $exp): ?>
                                    <div class="repeat-row">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Company Name</label>
                                                <input type="text" name="company[]" class="form-control" value="<?= htmlspecialchars($exp['company']) ?>">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Position</label>
                                                <input type="text" name="position[]" class="form-control" value="<?= htmlspecialchars($exp['position']) ?>">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Start Date</label>
                                                <input type="date" name="start_date[]" class="form-control" value="<?= htmlspecialchars($exp['start_date'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-4 mb-2 end-date-wrap">
                                                <label class="form-label">End Date</label>
                                                <input type="date" name="end_date[]" class="form-control end-date-input"
                                                    value="<?= htmlspecialchars($exp['end_date'] ?? '') ?>"
                                                    <?= $exp['currently_working'] ? 'disabled' : '' ?>>
                                            </div>
                                            <div class="col-md-4 mb-2 d-flex align-items-end">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input current-job-check" name="currently_working[]"
                                                        <?= $exp['currently_working'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label">Currently Working Here</label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <label class="form-label">Description</label>
                                                <textarea name="exp_description[]" class="form-control" rows="2"><?= htmlspecialchars($exp['description'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addExperienceRow()">+ Add Experience</button>
                        </div>
                    </div>

                    <!-- ===================== 5. EDUCATION ===================== -->
                    <div class="card section-card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Education</h4>
                            <div id="education-container">
                                <?php foreach ($education as $edu): ?>
                                    <div class="repeat-row">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Institution Name</label>
                                                <input type="text" name="institution[]" class="form-control" value="<?= htmlspecialchars($edu['institution']) ?>">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Degree</label>
                                                <input type="text" name="degree[]" class="form-control" value="<?= htmlspecialchars($edu['degree']) ?>">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Field of Study</label>
                                                <input type="text" name="field_of_study[]" class="form-control" value="<?= htmlspecialchars($edu['field_of_study'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label class="form-label">Start Year</label>
                                                <input type="number" name="start_year[]" class="form-control" value="<?= htmlspecialchars($edu['start_year'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label class="form-label">End Year</label>
                                                <input type="number" name="end_year[]" class="form-control" value="<?= htmlspecialchars($edu['end_year'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">GPA</label>
                                                <input type="text" name="gpa[]" class="form-control" value="<?= htmlspecialchars($edu['gpa'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addEducationRow()">+ Add Education</button>
                        </div>
                    </div>

                    <!-- ===================== 6. PROJECTS ===================== -->
                    <div class="card section-card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Projects</h4>
                            <div id="projects-container">
                                <?php foreach ($projects as $proj): ?>
                                    <div class="repeat-row">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
                                        <input type="hidden" name="project_existing_image[]" value="<?= htmlspecialchars($proj['image_path'] ?? '') ?>">
                                        <div class="row">
                                            <div class="col-md-8 mb-2">
                                                <label class="form-label">Project Title</label>
                                                <input type="text" name="project_title[]" class="form-control" value="<?= htmlspecialchars($proj['title']) ?>">
                                            </div>
                                            <div class="col-md-4 mb-2 d-flex align-items-end">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="project_featured[]" <?= $proj['featured'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label">Featured Project</label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <label class="form-label">Description</label>
                                                <textarea name="project_description[]" class="form-control" rows="2"><?= htmlspecialchars($proj['description'] ?? '') ?></textarea>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Technologies Used</label>
                                                <input type="text" name="project_tech[]" class="form-control" placeholder="React, Node.js"
                                                    value="<?= htmlspecialchars($proj['technologies'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Project Image</label>
                                                <input type="file" name="project_image[]" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                                                <?php if (!empty($proj['image_path'])): ?>
                                                    <img src="<?= htmlspecialchars($proj['image_path']) ?>" class="project-thumb mt-2" alt="">
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Live Demo URL</label>
                                                <input type="url" name="project_demo[]" class="form-control" value="<?= htmlspecialchars($proj['demo_url'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">GitHub Repository URL</label>
                                                <input type="url" name="project_github[]" class="form-control" value="<?= htmlspecialchars($proj['github_url'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addProjectRow()">+ Add Project</button>
                        </div>
                    </div>

                    <!-- ===================== 7. CERTIFICATIONS ===================== -->
                    <div class="card section-card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Certifications</h4>
                            <div id="certifications-container">
                                <?php foreach ($certifications as $cert): ?>
                                    <div class="repeat-row">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Certificate Name</label>
                                                <input type="text" name="cert_name[]" class="form-control" value="<?= htmlspecialchars($cert['name']) ?>">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Issuing Organization</label>
                                                <input type="text" name="cert_org[]" class="form-control" value="<?= htmlspecialchars($cert['issuing_org'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Date Issued</label>
                                                <input type="date" name="cert_date[]" class="form-control" value="<?= htmlspecialchars($cert['date_issued'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Credential URL</label>
                                                <input type="url" name="cert_url[]" class="form-control" value="<?= htmlspecialchars($cert['credential_url'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addCertificationRow()">+ Add Certification</button>
                        </div>
                    </div>

                    <!-- ===================== 8. ACHIEVEMENTS ===================== -->
                    <div class="card section-card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Achievements / Awards</h4>
                            <div id="achievements-container">
                                <?php foreach ($achievements as $ach): ?>
                                    <div class="repeat-row">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
                                        <div class="row">
                                            <div class="col-md-7 mb-2">
                                                <label class="form-label">Title</label>
                                                <input type="text" name="achievement_title[]" class="form-control" value="<?= htmlspecialchars($ach['title']) ?>">
                                            </div>
                                            <div class="col-md-5 mb-2">
                                                <label class="form-label">Date</label>
                                                <input type="date" name="achievement_date[]" class="form-control" value="<?= htmlspecialchars($ach['achieved_on'] ?? '') ?>">
                                            </div>
                                            <div class="col-12 mb-2">
                                                <label class="form-label">Description</label>
                                                <textarea name="achievement_description[]" class="form-control" rows="2"><?= htmlspecialchars($ach['description'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addAchievementRow()">+ Add Achievement</button>
                        </div>
                    </div>

                    <!-- ===================== 9. RESUME / CV ===================== -->
                    <!-- <div class="card section-card shadow-sm border-0">
            <div class="card-body p-4">
                <h4 class="mb-3">Resume / CV</h4>
                <div class="mb-3">
                    <label class="form-label">Upload PDF Resume</label>
                    <input type="file" name="resume" class="form-control" accept=".pdf">
                    <?php if (!empty($profile['resume_path'])): ?>
                        <div class="form-text">
                            Current file: <a href="<?= htmlspecialchars($profile['resume_path']) ?>" target="_blank">View resume</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="resume_downloadable" <?= $profile['resume_downloadable'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Allow visitors to download my resume</label>
                </div>
            </div>
        </div> -->

                    <!-- ===================== 10. PORTFOLIO SETTINGS ===================== -->
                    <div class="card section-card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Portfolio Settings</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Theme</label>
                                    <select name="theme" class="form-control">
                                        <option value="light" <?= $profile['theme'] === 'light' ? 'selected' : '' ?>>Light Mode</option>
                                        <option value="dark" <?= $profile['theme'] === 'dark' ? 'selected' : '' ?>>Dark Mode</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Visibility</label>
                                    <select name="visibility" class="form-control">
                                        <option value="public" <?= $profile['visibility'] === 'public' ? 'selected' : '' ?>>Public</option>
                                        <option value="private" <?= $profile['visibility'] === 'private' ? 'selected' : '' ?>>Private</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Email (shown to visitors)</label>
                                    <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($profile['contact_email'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Phone (shown to visitors)</label>
                                    <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($profile['contact_phone'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="enable_contact_form" <?= $profile['enable_contact_form'] ? 'checked' : '' ?>>
                                <label class="form-check-label">Enable contact form on my public portfolio</label>
                            </div>

                        </div>
                    </div>

                    <button type="submit" class="btn btn-lg btn-save-theme w-100">
                        Save &amp; Choose Template →
                    </button>
                </form>
            </div>
        </main>
    </div>

    <script>
        let socialIdx = <?= count($socialLinks) ?>;
        let skillIdx = <?= count($skills) ?>;
        let expIdx = <?= count($experience) ?>;
        let eduIdx = <?= count($education) ?>;
        let projIdx = <?= count($projects) ?>;
        let certIdx = <?= count($certifications) ?>;
        let achIdx = <?= count($achievements) ?>;

        function addSocialRow() {
            document.getElementById('social-container').insertAdjacentHTML('beforeend', `
        <div class="repeat-row">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <select name="platform[]" class="form-control">
                        <option>GitHub</option><option>LinkedIn</option><option>Twitter/X</option>
                        <option>Facebook</option><option>Instagram</option><option>Behance</option>
                        <option>Dribbble</option><option>Medium</option><option>Portfolio Website</option>
                    </select>
                </div>
                <div class="col-md-8 mb-2">
                    <input type="url" name="social_url[]" class="form-control" placeholder="https://...">
                </div>
            </div>
        </div>`);
        }

        function addSkillRow() {
            document.getElementById('skills-container').insertAdjacentHTML('beforeend', `
        <div class="repeat-row">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
            <div class="row">
                <div class="col-md-8 mb-2">
                    <input type="text" name="skill_name[]" class="form-control" placeholder="e.g. React">
                </div>
                <div class="col-md-4 mb-2">
                    <select name="skill_level[]" class="form-control">
                        <option>Beginner</option><option>Intermediate</option><option>Expert</option>
                    </select>
                </div>
            </div>
        </div>`);
        }

        function addExperienceRow() {
            document.getElementById('experience-container').insertAdjacentHTML('beforeend', `
        <div class="repeat-row">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
            <div class="row">
                <div class="col-md-6 mb-2"><label class="form-label">Company Name</label><input type="text" name="company[]" class="form-control"></div>
                <div class="col-md-6 mb-2"><label class="form-label">Position</label><input type="text" name="position[]" class="form-control"></div>
                <div class="col-md-4 mb-2"><label class="form-label">Start Date</label><input type="date" name="start_date[]" class="form-control"></div>
                <div class="col-md-4 mb-2"><label class="form-label">End Date</label><input type="date" name="end_date[]" class="form-control end-date-input"></div>
                <div class="col-md-4 mb-2 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input current-job-check" name="currently_working[]">
                        <label class="form-check-label">Currently Working Here</label>
                    </div>
                </div>
                <div class="col-12 mb-2"><label class="form-label">Description</label><textarea name="exp_description[]" class="form-control" rows="2"></textarea></div>
            </div>
        </div>`);
        }

        function addEducationRow() {
            document.getElementById('education-container').insertAdjacentHTML('beforeend', `
        <div class="repeat-row">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
            <div class="row">
                <div class="col-md-6 mb-2"><label class="form-label">Institution Name</label><input type="text" name="institution[]" class="form-control"></div>
                <div class="col-md-6 mb-2"><label class="form-label">Degree</label><input type="text" name="degree[]" class="form-control"></div>
                <div class="col-md-4 mb-2"><label class="form-label">Field of Study</label><input type="text" name="field_of_study[]" class="form-control"></div>
                <div class="col-md-3 mb-2"><label class="form-label">Start Year</label><input type="number" name="start_year[]" class="form-control"></div>
                <div class="col-md-3 mb-2"><label class="form-label">End Year</label><input type="number" name="end_year[]" class="form-control"></div>
                <div class="col-md-2 mb-2"><label class="form-label">GPA</label><input type="text" name="gpa[]" class="form-control"></div>
            </div>
        </div>`);
        }

        function addProjectRow() {
            document.getElementById('projects-container').insertAdjacentHTML('beforeend', `
        <div class="repeat-row">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
            <input type="hidden" name="project_existing_image[]" value="">
            <div class="row">
                <div class="col-md-8 mb-2"><label class="form-label">Project Title</label><input type="text" name="project_title[]" class="form-control"></div>
                <div class="col-md-4 mb-2 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="project_featured[]">
                        <label class="form-check-label">Featured Project</label>
                    </div>
                </div>
                <div class="col-12 mb-2"><label class="form-label">Description</label><textarea name="project_description[]" class="form-control" rows="2"></textarea></div>
                <div class="col-md-6 mb-2"><label class="form-label">Technologies Used</label><input type="text" name="project_tech[]" class="form-control" placeholder="React, Node.js"></div>
                <div class="col-md-6 mb-2"><label class="form-label">Project Image</label><input type="file" name="project_image[]" class="form-control" accept=".jpg,.jpeg,.png,.webp"></div>
                <div class="col-md-6 mb-2"><label class="form-label">Live Demo URL</label><input type="url" name="project_demo[]" class="form-control"></div>
                <div class="col-md-6 mb-2"><label class="form-label">GitHub Repository URL</label><input type="url" name="project_github[]" class="form-control"></div>
            </div>
        </div>`);
        }

        function addCertificationRow() {
            document.getElementById('certifications-container').insertAdjacentHTML('beforeend', `
        <div class="repeat-row">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
            <div class="row">
                <div class="col-md-6 mb-2"><label class="form-label">Certificate Name</label><input type="text" name="cert_name[]" class="form-control"></div>
                <div class="col-md-6 mb-2"><label class="form-label">Issuing Organization</label><input type="text" name="cert_org[]" class="form-control"></div>
                <div class="col-md-6 mb-2"><label class="form-label">Date Issued</label><input type="date" name="cert_date[]" class="form-control"></div>
                <div class="col-md-6 mb-2"><label class="form-label">Credential URL</label><input type="url" name="cert_url[]" class="form-control"></div>
            </div>
        </div>`);
        }

        function addAchievementRow() {
            document.getElementById('achievements-container').insertAdjacentHTML('beforeend', `
        <div class="repeat-row">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" onclick="this.closest('.repeat-row').remove()">&times;</button>
            <div class="row">
                <div class="col-md-7 mb-2"><label class="form-label">Title</label><input type="text" name="achievement_title[]" class="form-control"></div>
                <div class="col-md-5 mb-2"><label class="form-label">Date</label><input type="date" name="achievement_date[]" class="form-control"></div>
                <div class="col-12 mb-2"><label class="form-label">Description</label><textarea name="achievement_description[]" class="form-control" rows="2"></textarea></div>
            </div>
        </div>`);
        }

        // Disable End Date when "Currently Working Here" is checked
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('current-job-check')) {
                const row = e.target.closest('.repeat-row');
                const endDateInput = row.querySelector('.end-date-input');
                if (e.target.checked) {
                    endDateInput.value = '';
                    endDateInput.disabled = true;
                } else {
                    endDateInput.disabled = false;
                }
            }
        });
    </script>
</body>

</html>