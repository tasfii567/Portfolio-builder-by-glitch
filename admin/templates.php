<?php
include 'admin_auth.php';
include 'config.php';

$templates = mysqli_query($con, "SELECT * FROM templates ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Templates - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<div class="header">
    <div class="header-title"><b>Templates</b></div>
    <div class="logout"><a href="admin_logout.php">Logout</a></div>
</div>

<div class="simple-page">
    <div class="simple-card">
        <h2>All Templates</h2>
        <div class="admin_dashboard">
            <a href="admin_logout.php">Back to Dashboard</a>
        </div>
        <br>


        <table>
            <tr>
                <th>SL</th>
                <th>Template Name</th>
                <th>Status</th>
            </tr>

            <?php
            if ($templates && mysqli_num_rows($templates) > 0) {
                $sl = 1;
                while ($template = mysqli_fetch_assoc($templates)) {
            ?>
                    <tr>
                        <td><?php echo $sl++; ?></td>
                        <td><?php echo $template['name']; ?></td>
                        <td><?php echo $template['status']; ?></td>
                    </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='3'>No templates found</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>