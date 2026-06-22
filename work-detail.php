<?php
$active = 'work-detail';
$page_title = "Designing Dashboards — T.";
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
        <h1 class="detail-title">Designing Dashboards with usability in mind</h1>
        <div class="detail-tagrow">
            <span class="tag-pill">2020</span>
            <span class="tag-cat">Dashboard, User Experience Design</span>
        </div>
        <p class="detail-intro">A look at how I approached information density, hierarchy and real-time data for a dashboard used daily by support and operations teams.</p>

        <figure class="detail-figure">
            <img src="images/detail-dashboard.svg" alt="Dashboard interface screenshot">
        </figure>

        <h2 class="detail-h1">Heading 1</h2>
        <h3 class="detail-h2">Heading 2</h3>
        <p class="detail-body">The brief asked for a single dashboard that could surface team performance, individual workload and live ticket status without overwhelming the people using it every day. The starting point was a card-based grid that could flex between summary and detail views depending on the role logged in.</p>

        <figure class="detail-figure">
            <img src="images/detail-car.svg" alt="Illustration of a red retro car at dusk">
        </figure>

        <figure class="detail-figure" style="max-width:360px;">
            <img src="images/detail-checkout.svg" alt="Checkout order summary interface">
        </figure>
    </main>

    <?php include 'footer.php'; ?>

</div>
</body>
</html>