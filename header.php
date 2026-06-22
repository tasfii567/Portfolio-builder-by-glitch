<?php
/**
 * Shared header / nav
 * Expects $active to be set by the calling page: 'home' | 'blog' | 'work' | 'work-detail'
 */
if (!isset($active)) { $active = ''; }

function nav_class($key, $active) {
    return $key === $active ? 'active' : '';
}

$is_work_active = ($active === 'work' || $active === 'work-detail') ? 'active' : '';
?>
<header class="site-header">
    <div class="wrap">
        <nav class="site-nav">
            <?php if ($active === 'home'): ?>
                <a href="work.php">Works</a>
                <a href="blog.php">Blog</a>
                <a href="contact.php">Contact</a>
            <?php else: ?>
                <a href="blog.php" class="<?php echo nav_class('blog', $active); ?>">Blog</a>
                <a href="work.php" class="<?php echo $is_work_active; ?>">Works</a>
                <a href="contact.php" class="<?php echo nav_class('contact', $active); ?>">Contact</a>
            <?php endif; ?>
        </nav>
    </div>
</header>