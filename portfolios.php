<?php

include 'admin_auth.php';
include 'config.php';

$portfolios = mysqli_query($con, "SELECT * FROM portfolios ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Portfolios - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<div class="header">
    <div class="header-title"><b>Portfolios</b></div>
    <div class="logout"><a href="admin_logout.php">Logout</a></div>
</div>

<div class="simple-page">
    <div class="simple-card">
        <h2>All Portfolios</h2>

        <div class="admin_dashboard">
            <a href="admin_logout.php">Back to Dashboard</a>
        </div>
        <br>
        <table>
            <tr>
                <th>SL</th>
                <th>Title</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>

            <?php
            if ($portfolios && mysqli_num_rows($portfolios) > 0) {
                $sl = 1;
                while ($portfolio = mysqli_fetch_assoc($portfolios)) {
            ?>
                    <tr>
                        <td><?php echo $sl++; ?></td>
                        <td><?php echo $portfolio['title']; ?></td>
                        <td><?php echo $portfolio['status']; ?></td>
                        <td><?php echo $portfolio['created_at']; ?></td>
                    </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='4'>No portfolios found</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>