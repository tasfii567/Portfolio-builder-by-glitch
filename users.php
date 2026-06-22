<?php
include 'admin_auth.php';
include 'config.php';

$users = mysqli_query($con, "SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<div class="header">
    <div class="header-title"><b>Users</b></div>
    <div class="logout"><a href="admin_logout.php">Logout</a></div>
</div>

<div class="simple-page">
    <div class="simple-card">
        <h2>All Users</h2>

        <div class="admin_dashboard">
            <a href="admin_dashboard.php">Back to Dashboard</a>
        </div>
        <br>

        <table>
            <tr>
                <th>SL</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
            </tr>

            <?php
            if ($users && mysqli_num_rows($users) > 0) {
                $sl = 1;
                while ($user = mysqli_fetch_assoc($users)) {
            ?>
                    <tr>
                        <td><?php echo $sl++; ?></td>
                        <td><?php echo $user['name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['phone']; ?></td>
                        <td><?php echo $user['status']; ?></td>
                    </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='5'>No users found</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>