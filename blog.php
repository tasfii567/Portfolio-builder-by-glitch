<?php
$active = 'blog';
$page_title = "Blog — T.";

$posts = [
    ['title' => 'UI interactions of the week', 'date' => '13 Feb 2019', 'cat' => 'Express, Tendencies'],
    ['title' => 'UI interactions of the week', 'date' => '12 Feb 2019', 'cat' => 'Express, Tendencies'],
    ['title' => 'UI interactions of the week', 'date' => '12 Feb 2019', 'cat' => 'Express, Tendencies'],
    ['title' => 'UI interactions of the week', 'date' => '11 Feb 2019', 'cat' => 'Express, Tendencies'],
];
$excerpt = "A short round-up of the small interaction details that caught my eye this week, from button states to scroll-triggered transitions.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $page_title; ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-shell">

    <?php include 'header.php'; ?>

    <main class="wrap">
        <h1 class="page-title">Blog</h1>

        <?php foreach ($posts as $post): ?>
            <article class="blog-item">
                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                <div class="blog-meta">
                    <?php echo htmlspecialchars($post['date']); ?> &nbsp;|&nbsp;
                    <span class="cat"><?php echo htmlspecialchars($post['cat']); ?></span>
                </div>
                <p><?php echo $excerpt; ?></p>
            </article>
        <?php endforeach; ?>
    </main>

    <?php include 'footer.php'; ?>

</div>
</body>
</html>