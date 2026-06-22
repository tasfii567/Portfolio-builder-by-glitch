<?php
$active = 'home';
$page_title = "T. — Creative Technologist";
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

    <section class="hero">
        <div class="wrap">
            <div class="hero-copy">
                <h1>Hi, I am T,<br>Creative Technologist</h1>
                <p>Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.</p>
                <a href="#" class="btn">Download Resume</a>
            </div>
            <div class="avatar">
                <img src="images/avatar.svg" alt="Illustration of T, creative technologist">
            </div>
        </div>
    </section>

    <section class="recent-band">
        <div class="wrap">
            <div class="recent-head">
                <h2>Recent posts</h2>
                <a href="blog.php">View all</a>
            </div>
            <div class="recent-grid">
                <article class="recent-card">
                    <h3>Making a design system from scratch</h3>
                    <div class="recent-meta">12 Feb 2020 &nbsp;|&nbsp; Design, Pattern</div>
                    <p>Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.</p>
                </article>
                <article class="recent-card">
                    <h3>Creating pixel perfect icons in Figma</h3>
                    <div class="recent-meta">12 Feb 2020 &nbsp;|&nbsp; Figma, Icon Design</div>
                    <p>Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="wrap">
        <div class="section-label">Featured works</div>

        <div class="work-item">
            <div class="work-thumb thumb-dashboard">
                <img src="images/thumb-dashboard.svg" alt="Dashboard UI design">
            </div>
            <div class="work-info">
                <h3>Designing Dashboards</h3>
                <div class="tag-row">
                    <span class="tag-pill">2020</span>
                    <span class="tag-cat">Dashboard</span>
                </div>
                <p>Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.</p>
            </div>
        </div>

        <div class="work-item">
            <div class="work-thumb thumb-portrait">
                <img src="images/thumb-portrait.svg" alt="Vibrant illustrated portrait">
            </div>
            <div class="work-info">
                <h3>Vibrant Portraits of 2020</h3>
                <div class="tag-row">
                    <span class="tag-pill">2018</span>
                    <span class="tag-cat">Illustration</span>
                </div>
                <p>Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.</p>
            </div>
        </div>

        <div class="work-item">
            <div class="work-thumb thumb-type">
                <img src="images/thumb-type.svg" alt="Malayalam type design">
            </div>
            <div class="work-info">
                <h3>36 Days of Malayalam type</h3>
                <div class="tag-row">
                    <span class="tag-pill">2018</span>
                    <span class="tag-cat">Typography</span>
                </div>
                <p>Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.</p>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

</div>
</body>
</html>