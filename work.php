<?php
$active = 'work';
$page_title = "Work — T.";

$projects = [
    [
        'slug' => 'designing-dashboards',
        'title' => 'Designing Dashboards',
        'year' => '2020',
        'cat' => 'Dashboard',
        'thumb' => 'thumb-dashboard',
        'desc' => 'A reusable component library and layout system built for data-heavy admin dashboards used across three internal products.'
    ],
    [
        'slug' => 'vibrant-portraits',
        'title' => 'Vibrant Portraits of 2020',
        'year' => '2020',
        'cat' => 'Illustration',
        'thumb' => 'thumb-portrait',
        'desc' => 'A personal series of bold, color-saturated portraits exploring texture and light through digital illustration.'
    ],
    [
        'slug' => 'malayalam-type',
        'title' => '36 Days of Malayalam type',
        'year' => '2019',
        'cat' => 'Typography',
        'thumb' => 'thumb-type',
        'desc' => 'A 36 day type challenge reinterpreting the Malayalam script with hand-drawn letterforms and seasonal color palettes.'
    ],
    [
        'slug' => 'components',
        'title' => 'Components',
        'year' => '2019',
        'cat' => 'Components, Design',
        'thumb' => 'thumb-components',
        'desc' => 'A token-based component kit covering buttons, inputs, cards and navigation, built for fast, consistent product design.'
    ],
];
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
        <h1 class="page-title">Work</h1>

        <?php foreach ($projects as $p): ?>
            <a href="work-detail.php?project=<?php echo urlencode($p['slug']); ?>" class="work-item" style="cursor:pointer;">
                <div class="work-thumb <?php echo $p['thumb']; ?>">
                    <img src="images/<?php echo $p['thumb']; ?>.svg" alt="<?php echo htmlspecialchars($p['title']); ?>">
                </div>
                <div class="work-info">
                    <h3><?php echo htmlspecialchars($p['title']); ?></h3>
                    <div class="tag-row">
                        <span class="tag-pill"><?php echo htmlspecialchars($p['year']); ?></span>
                        <span class="tag-cat"><?php echo htmlspecialchars($p['cat']); ?></span>
                    </div>
                    <p><?php echo htmlspecialchars($p['desc']); ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </main>

    <?php include 'footer.php'; ?>

</div>
</body>
</html>