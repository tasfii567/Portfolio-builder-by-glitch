<?php
/**
 * includes/sidebar.php — left navigation for the logged-in builder pages.
 * Set $activeNav before requiring this file to highlight the current item.
 * Relies on the CSS already defined by the including page (.sidebar, .nav, etc.).
 */
$nav = $activeNav ?? '';

// Public portfolio slug (so the sidebar "View Portfolio" link can be live).
$__slug = '';
if (isset($pdo, $userId)) {
    try {
        $__s = $pdo->prepare('SELECT slug FROM portfolios WHERE user_id = ?');
        $__s->execute([$userId]);
        $__slug = $__s->fetchColumn() ?: '';
    } catch (Throwable $e) {
        $__slug = '';
    }
}
$__viewHref   = $__slug ? ('view-portfolio.php?u=' . urlencode($__slug)) : 'create-portfolio.php';
$__viewAttr   = $__slug ? ' target="_blank" rel="noopener"' : '';
?>
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
        <a href="dashboard.php" class="<?= $nav === 'dashboard' ? 'active' : '' ?>"><span class="ic">🏠</span> Dashboard</a>
        <a href="edit-portfolio.php" class="<?= $nav === 'edit-portfolio' ? 'active' : '' ?>"><span class="ic">👤</span> Edit Profile</a>
        <a href="choose-template.php" class="<?= $nav === 'choose-template' ? 'active' : '' ?>"><span class="ic">🎨</span> Choose Template</a>
        <a href="create-portfolio.php" class="<?= $nav === 'create-portfolio' ? 'active' : '' ?>"><span class="ic">📁</span> Create Portfolio</a>
        <a href="<?= htmlspecialchars($__viewHref) ?>"<?= $__viewAttr ?>><span class="ic">👁</span> View Portfolio</a>
    </nav>
    <div class="logout">
        <a href="logout.php"><span>⏻</span> Logout</a>
    </div>
</aside>
