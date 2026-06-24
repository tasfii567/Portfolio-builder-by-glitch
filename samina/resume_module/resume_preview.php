<?php include 'resume_data.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="color-scheme" content="light only">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($user['name'] ?? 'Resume'); ?> — Resume</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    /* ── App shell (matches edit-portfolio.php exactly) ── */
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
      --radius: 14px;
      --shadow: 0 8px 24px rgba(43, 41, 38, .07);
    }

    *,
    *::before,
    *::after {
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
      font-weight: 600;
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

    /* Export button */
    .export-btn-wrap {
      margin-bottom: 20px;
    }

    .btn-export {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      padding: 10px 22px;
      cursor: pointer;
      text-decoration: none;
      transition: background .2s;
    }

    .btn-export:hover {
      background: var(--primary-soft);
      color: #fff;
    }

    /* Resume paper */
    .resume-outer {
      display: flex;
      justify-content: center;
      padding-bottom: 40px;
    }

    /* ── Resume styles (original, untouched) ── */
    .resume {
      width: 794px;
      min-height: 1123px;
      background: #fff;
      color: #1a1a1a;
      box-shadow: 0 2px 16px rgba(0, 0, 0, 0.2);
      padding: 50px 55px;
      font-family: 'Times New Roman', Georgia, serif;
      font-size: 12px;
      color-scheme: light only;
    }

    .resume * {
      box-sizing: border-box;
    }

    .name {
      font-size: 30px;
      font-weight: normal;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
      color: #1a1a1a;
    }

    .contact-line {
      font-size: 11.5px;
      color: #333;
      margin-bottom: 6px;
    }

    .links-line {
      font-size: 11.5px;
      margin-bottom: 16px;
      color: #1a1a1a;
    }

    .links-line a {
      color: #1a1a1a;
      font-weight: bold;
      text-decoration: underline;
    }

    .links-line a:visited {
      color: #1a1a1a;
    }

    .links-line .sep {
      margin: 0 6px;
      color: #555;
      font-weight: normal;
    }

    .header-rule {
      border: none;
      border-top: 1px solid #1a1a1a;
      margin-bottom: 18px;
    }

    .sec {
      margin-bottom: 18px;
    }

    .sec:last-child {
      margin-bottom: 0;
    }

    .sec-title {
      font-size: 13px;
      font-weight: bold;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      border-bottom: 1.4px solid #1a1a1a;
      padding-bottom: 4px;
      margin-bottom: 10px;
      color: #1a1a1a;
    }

    .summary-text {
      font-size: 11.5px;
      line-height: 1.6;
      color: #222;
    }

    .edu-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      column-gap: 24px;
      row-gap: 14px;
    }

    .edu-item.has-divider {
      border-right: 1px solid #ccc;
      padding-right: 20px;
    }

    .edu-item.full-span {
      grid-column: 1 / -1;
    }

    .edu-line {
      font-size: 11.5px;
      color: #1a1a1a;
    }

    .edu-line.inst-line {
      margin-top: 2px;
      color: #333;
    }

    .edu-notes {
      margin-top: 5px;
      padding-left: 16px;
    }

    .edu-notes li {
      margin-bottom: 3px;
      font-size: 11px;
      color: #333;
    }

    .skills-list {
      list-style: disc;
      padding-left: 18px;
    }

    .skills-list li {
      margin-bottom: 5px;
      font-size: 11.5px;
      line-height: 1.5;
      color: #1a1a1a;
    }

    .skill-cat {
      font-weight: bold;
      color: #1a1a1a;
    }

    .proj-entry {
      margin-bottom: 14px;
    }

    .proj-entry:last-child {
      margin-bottom: 0;
    }

    .proj-head {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      flex-wrap: wrap;
      gap: 4px 10px;
    }

    .proj-title {
      font-weight: bold;
      font-size: 12px;
      color: #1a1a1a;
    }

    .proj-links {
      font-size: 11.5px;
      white-space: nowrap;
      color: #1a1a1a;
    }

    .proj-links a {
      color: #1a1a1a;
      font-weight: bold;
      text-decoration: underline;
    }

    .proj-links a:visited {
      color: #1a1a1a;
    }

    .proj-links .sep {
      margin: 0 5px;
      color: #555;
      font-weight: normal;
    }

    .proj-bullets {
      padding-left: 18px;
      margin-top: 4px;
    }

    .proj-bullets li {
      margin-bottom: 3px;
      font-size: 11.5px;
      line-height: 1.5;
      color: #1a1a1a;
    }

    .cert-list {
      list-style: disc;
      padding-left: 18px;
    }

    .cert-list li {
      margin-bottom: 8px;
      color: #1a1a1a;
    }

    .cert-list li:last-child {
      margin-bottom: 0;
    }

    .cert-title {
      font-weight: bold;
      font-size: 12px;
      color: #1a1a1a;
    }

    .cert-desc {
      font-size: 11.5px;
      color: #333;
      margin-top: 1px;
    }

    .vol-list {
      padding-left: 18px;
    }

    .vol-list li {
      margin-bottom: 10px;
      color: #1a1a1a;
    }

    .vol-list li:last-child {
      margin-bottom: 0;
    }

    .vol-header {
      font-size: 11.5px;
      margin-bottom: 3px;
      color: #1a1a1a;
    }

    .vol-bullets {
      padding-left: 18px;
    }

    .vol-bullets li {
      font-size: 11.5px;
      line-height: 1.5;
      color: #1a1a1a;
    }

    /* Mobile */
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

      .resume-outer {
        overflow-x: auto;
      }

      .resume {
        transform-origin: top left;
        transform: scale(0.45);
        width: 794px;
        margin-bottom: -600px;
      }
    }
  </style>
</head>

<body>

  <?php
  function filterNonEmpty(array $items): array
  {
    return array_values(array_filter($items, fn($v) => trim((string)$v) !== ''));
  }

  // Build initials for avatar
  $name     = $user['name'] ?? 'User';
  $email    = $user['email'] ?? '';
  $initials = strtoupper(
    substr($name, 0, 1) .
      (strpos($name, ' ') !== false ? substr($name, strpos($name, ' ') + 1, 1) : '')
  );

  // Sidebar nav paths — two levels up from samina/resume_module/
  $base = '../../nahin/';
  ?>

  <div class="app">

    <!-- ── Sidebar ── -->
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
        <a href="<?= $base ?>dashboard.php"><span class="ic">🏠</span> Dashboard</a>
        <a href="<?= $base ?>edit-portfolio.php"><span class="ic">👤</span> Edit Profile</a>
        <a href="<?= $base ?>choose-template.php"><span class="ic">🎨</span> Choose Template</a>
        <a href="<?= $base ?>create-portfolio.php"><span class="ic">📁</span> Create Portfolio</a>
        <a href="resume_preview.php" class="active"><span class="ic">📄</span> Create Resume</a>
        <a href="<?= $base ?>../job-match-ai/job-match.php"><span class="ic">📊</span> Job Match</a>
      </nav>
      <div class="logout">
        <a href="<?= $base ?>logout.php"><span>⏻</span> Logout</a>
      </div>
    </aside>

    <!-- ── Main ── -->
    <main class="main">

      <!-- Topbar -->
      <div class="topbar">
        <div style="display:flex;align-items:center;gap:14px">
          <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
          <div>
            <h2>Resume Preview</h2>
            <p>Your resume, built from your portfolio data.</p>
          </div>
        </div>
        <div class="profile">
          <div class="who">
            <b><?= htmlspecialchars($name) ?></b>
            <small><?= htmlspecialchars($email) ?></small>
          </div>
          <div class="avatar"><?= htmlspecialchars($initials) ?></div>
        </div>
      </div>

      <!-- Export button -->
      <div class="export-btn-wrap">
        <a class="btn-export" href="export_pdf.php">
          <i class="bi bi-file-earmark-arrow-down"></i> Export PDF
        </a>
      </div>

      <!-- Resume paper -->
      <div class="resume-outer">
        <div class="resume">

          <!-- Header -->
          <div class="header">
            <h1 class="name"><?= htmlspecialchars($user['name'] ?? '') ?></h1>

            <?php
            $contactParts = array_filter([
              $user['location'] ?? '',
              $user['phone']   ?? '',
              $user['email']   ?? '',
            ], fn($v) => $v !== '');
            ?>
            <?php if (!empty($contactParts)): ?>
              <div class="contact-line"><?= htmlspecialchars(implode(' | ', $contactParts)) ?></div>
            <?php endif; ?>

            <?php
            $linkDefs = [
              ['key' => 'linkedin',  'label' => 'LinkedIn'],
              ['key' => 'github',    'label' => 'GitHub'],
              ['key' => 'portfolio', 'label' => 'Portfolio'],
            ];
            $activeLinks = array_filter($linkDefs, fn($ld) => !empty($user[$ld['key']]));
            ?>
            <?php if (!empty($activeLinks)): ?>
              <div class="links-line">
                <?php $first = true;
                foreach ($activeLinks as $ld): ?>
                  <?php if (!$first): ?><span class="sep">|</span><?php endif; ?>
                  <a href="<?= htmlspecialchars($user[$ld['key']]) ?>"><?= htmlspecialchars($ld['label']) ?></a>
                  <?php $first = false; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <hr class="header-rule">
          </div>

          <!-- Summary -->
          <?php if (!empty($user['summary'])): ?>
            <div class="sec">
              <div class="sec-title">Summary</div>
              <div class="summary-text"><?= htmlspecialchars($user['summary']) ?></div>
            </div>
          <?php endif; ?>

          <!-- Education -->
          <?php if (!empty($education)): ?>
            <?php $eduCount = count($education); ?>
            <div class="sec">
              <div class="sec-title">Education</div>
              <div class="edu-grid">
                <?php foreach ($education as $i => $edu): ?>
                  <?php
                  $isLastOdd  = ($eduCount % 2 === 1) && ($i === $eduCount - 1);
                  $hasDivider = !$isLastOdd && ($i % 2 === 0) && isset($education[$i + 1]);
                  $classes    = 'edu-item';
                  if ($isLastOdd)  $classes .= ' full-span';
                  if ($hasDivider) $classes .= ' has-divider';
                  $degreeDur = array_filter([$edu['degree'] ?? '', $edu['duration'] ?? ''], fn($v) => $v !== '');
                  $instScore = array_filter([$edu['institution'] ?? '', $edu['score'] ?? ''], fn($v) => $v !== '');
                  $notes = filterNonEmpty($edu['notes'] ?? []);
                  ?>
                  <div class="<?= $classes ?>">
                    <?php if (!empty($degreeDur)): ?>
                      <div class="edu-line"><?= htmlspecialchars(implode(' | ', $degreeDur)) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($instScore)): ?>
                      <div class="edu-line inst-line"><?= htmlspecialchars(implode(' | ', $instScore)) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($notes)): ?>
                      <ul class="edu-notes">
                        <?php foreach ($notes as $note): ?>
                          <li><?= htmlspecialchars($note) ?></li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Skills -->
          <?php if (!empty($skills)): ?>
            <div class="sec">
              <div class="sec-title">Skills</div>
              <ul class="skills-list">
                <?php foreach ($skills as $skill): ?>
                  <?php if (trim($skill['items'] ?? '') === '') continue; ?>
                  <li>
                    <?php if (!empty($skill['category'])): ?><span class="skill-cat"><?= htmlspecialchars($skill['category']) ?>:</span> <?php endif; ?>
                    <?= htmlspecialchars($skill['items']) ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <!-- Projects -->
          <?php if (!empty($projects)): ?>
            <div class="sec">
              <div class="sec-title">Projects</div>
              <?php foreach ($projects as $project): ?>
                <?php if (trim($project['title'] ?? '') === '') continue; ?>
                <div class="proj-entry">
                  <div class="proj-head">
                    <span class="proj-title">
                      <?= htmlspecialchars($project['title']) ?><?php if (!empty($project['tech'])): ?> (<?= htmlspecialchars($project['tech']) ?>)<?php endif; ?>
                    </span>
                    <?php
                    $validLinks = array_filter(
                      $project['links'] ?? [],
                      fn($l) => !empty($l['label']) && !empty($l['url'])
                    );
                    ?>
                    <?php if (!empty($validLinks)): ?>
                      <span class="proj-links">
                        <?php $idx = 0;
                        foreach ($validLinks as $link): ?>
                          <?php if ($idx > 0): ?><span class="sep">|</span><?php endif; ?>
                          <a href="<?= htmlspecialchars($link['url']) ?>"><?= htmlspecialchars($link['label']) ?></a>
                          <?php $idx++; ?>
                        <?php endforeach; ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <?php $bullets = filterNonEmpty($project['bullets'] ?? []); ?>
                  <?php if (!empty($bullets)): ?>
                    <ul class="proj-bullets">
                      <?php foreach ($bullets as $bullet): ?>
                        <li><?= htmlspecialchars($bullet) ?></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Certifications -->
          <?php if (!empty($certifications)): ?>
            <div class="sec">
              <div class="sec-title">Certifications and Training</div>
              <ul class="cert-list">
                <?php foreach ($certifications as $cert): ?>
                  <?php if (trim($cert['title'] ?? '') === '') continue; ?>
                  <li>
                    <div class="cert-title"><?= htmlspecialchars($cert['title']) ?></div>
                    <?php if (!empty($cert['description'])): ?>
                      <div class="cert-desc"><?= htmlspecialchars($cert['description']) ?></div>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <!-- Volunteering -->
          <?php if (!empty($volunteering)): ?>
            <div class="sec">
              <div class="sec-title">Volunteering Experience</div>
              <ol class="vol-list">
                <?php foreach ($volunteering as $vol): ?>
                  <?php if (trim($vol['role'] ?? '') === '') continue; ?>
                  <li>
                    <div class="vol-header">
                      <strong><?= htmlspecialchars($vol['role']) ?></strong>
                      <?php if (!empty($vol['organization'])): ?> | <?= htmlspecialchars($vol['organization']) ?><?php endif; ?>
                        <?php if (!empty($vol['year'])): ?> (<?= htmlspecialchars($vol['year']) ?>)<?php endif; ?>
                    </div>
                    <?php $volBullets = filterNonEmpty($vol['bullets'] ?? []); ?>
                    <?php if (!empty($volBullets)): ?>
                      <ul class="vol-bullets">
                        <?php foreach ($volBullets as $bullet): ?>
                          <li><?= htmlspecialchars($bullet) ?></li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ol>
            </div>
          <?php endif; ?>

        </div><!-- /.resume -->
      </div><!-- /.resume-outer -->

    </main>
  </div><!-- /.app -->

  <script>
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
      const sidebar = document.getElementById('sidebar');
      if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !e.target.classList.contains('menu-btn')) {
        sidebar.classList.remove('open');
      }
    });
  </script>

</body>

</html>
