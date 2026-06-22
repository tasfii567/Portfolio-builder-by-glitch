<?php
include 'admin_auth.php';
include 'config.php';

$categories = mysqli_query($con, "SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Categories - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<div class="header">
    <div class="header-title"><b>Categories</b></div>
    <div class="logout"><a href="admin_logout.php">Logout</a></div>
</div>

<div class="simple-page">
    <div class="simple-card">
        <h2>All Categories</h2>

        <div class="admin_dashboard">
            <a href="admin_logout.php">Back to Dashboard</a>
        </div>
        <br>
        <table>
            <tr>
                <th>SL</th>
                <th>Category Name</th>
                <th>Type</th>
            </tr>

            <?php
            if ($categories && mysqli_num_rows($categories) > 0) {
                $sl = 1;
                while ($category = mysqli_fetch_assoc($categories)) {
            ?>
                    <tr>
                        <td><?php echo $sl++; ?></td>
                        <td><?php echo $category['name']; ?></td>
                        <td><?php echo $category['type']; ?></td>
                    </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='3'>No categories found</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>