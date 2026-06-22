<?php
/**
 * inc/sidebar.php — shared sidebar.
 * Expects $ACTIVE, $userName, $userRole, $initials from the including page.
 */
$ACTIVE   = $ACTIVE   ?? '';
$userName = $userName ?? 'User';
$userRole = $userRole ?? 'Member';
$initials = $initials ?? 'U';
function navActive($k, $cur){ return $k === $cur ? 'active' : ''; }
?>
<aside class="sidebar" id="sidebar">
  <div class="brand">
    <div class="logo">P</div>
    <div>
      <h1>Portfolio Builder</h1>
      <span>BUILD · SHOWCASE · GET HIRED</span>
    </div>
  </div>
  <nav class="nav">
    <div class="nav-label">Menu</div>
    <a href="Dashboard.php"        class="<?= navActive('dashboard',$ACTIVE) ?>"><span class="ic">🏠</span> Dashboard</a>
    <a href="edit-profile.php"     class="<?= navActive('profile',$ACTIVE) ?>"><span class="ic">👤</span> Edit Profile</a>
    <a href="templates.php"        class="<?= navActive('templates',$ACTIVE) ?>"><span class="ic">🎨</span> Choose Template</a>
    <a href="create-portfolio.php" class="<?= navActive('portfolio',$ACTIVE) ?>"><span class="ic">📁</span> Create Portfolio</a>
    <a href="create-resume.php"    class="<?= navActive('resume',$ACTIVE) ?>"><span class="ic">📄</span> Create Resume</a>
    <a href="job-match.php"        class="<?= navActive('jobmatch',$ACTIVE) ?>"><span class="ic">📊</span> Job Match</a>
  </nav>
  <div class="logout">
    <a href="Logout.php"><span>⏻</span> Logout</a>
  </div>
</aside>
