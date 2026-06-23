<?php
include 'admin_auth.php';
include 'config.php';

$messages = mysqli_query($con, "SELECT * FROM contact_messages ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Messages - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<div class="header">
    <div class="header-title"><b>Contact Messages</b></div>
    <div class="logout"><a href="admin_logout.php">Logout</a></div>
</div>

<div class="simple-page">
    <div class="simple-card">
        <h2>Contact Messages</h2>

        <div class="admin_dashboard">
            <a href="admin_logout.php">Back to Dashboard</a>
        </div>
        <br>
        <table>
            <tr>
                <th>SL</th>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Message</th>
            </tr>

            <?php
            if ($messages && mysqli_num_rows($messages) > 0) {
                $sl = 1;
                while ($message = mysqli_fetch_assoc($messages)) {
            ?>
                    <tr>
                        <td><?php echo $sl++; ?></td>
                        <td><?php echo $message['name']; ?></td>
                        <td><?php echo $message['email']; ?></td>
                        <td><?php echo $message['subject']; ?></td>
                        <td><?php echo $message['message']; ?></td>
                    </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='5'>No messages found</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>