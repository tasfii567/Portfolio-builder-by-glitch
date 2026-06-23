<?php
include 'admin_auth.php';
include 'config.php';

/*
  Beginner friendly count function
  Ei function diye jekono table theke count ana jabe
*/
function getCount($con, $sql)
{
    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    } else {
        return 0;
    }
}

/* Dashboard counts */
$total_users = getCount($con, "SELECT COUNT(id) AS total FROM users");

$new_users = getCount($con, "SELECT COUNT(id) AS total FROM users WHERE DATE(created_at) = CURDATE()");

$active_users = getCount($con, "SELECT COUNT(id) AS total FROM users WHERE status = 'active'");

$inactive_users = getCount($con, "SELECT COUNT(id) AS total FROM users WHERE status = 'inactive'");

$total_portfolios = getCount($con, "SELECT COUNT(id) AS total FROM portfolios");

$draft_portfolios = getCount($con, "SELECT COUNT(id) AS total FROM portfolios WHERE status = 'draft'");

$published_portfolios = getCount($con, "SELECT COUNT(id) AS total FROM portfolios WHERE status = 'published'");

$total_templates = getCount($con, "SELECT COUNT(id) AS total FROM templates");

$total_categories = getCount($con, "SELECT COUNT(id) AS total FROM categories");

$total_messages = getCount($con, "SELECT COUNT(id) AS total FROM contact_messages");

/* Latest users */
$latest_users_sql = "SELECT id, name, email, status, created_at 
                     FROM users 
                     ORDER BY id DESC 
                     LIMIT 5";
$latest_users = mysqli_query($con, $latest_users_sql);

/* Latest contact messages */
$latest_messages_sql = "SELECT id, name, email, subject, created_at 
                        FROM contact_messages 
                        ORDER BY id DESC 
                        LIMIT 5";
$latest_messages = mysqli_query($con, $latest_messages_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Portfolio Builder Admin Dashboard</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>

    <div class="header">
        <div class="header-title">
            <button class="menu-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>

            <b>Portfolio Builder Admin</b>
        </div>

        <div class="logout">


            <a href="admin_logout.php">Logout</a>
        </div>
    </div>

    <div class="container">

        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="admin-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
                </div>

                <h3><?php echo $_SESSION['admin_name']; ?></h3>
                <p><?php echo $_SESSION['admin_email']; ?></p>
            </div>

            <div class="sidebar-item active">
                <i class="fas fa-tachometer-alt"></i>
                <a href="admin_dashboard.php">Dashboard</a>
            </div>

            <div class="sidebar-item">
                <i class="fas fa-users"></i>
                <a href="users.php">Users</a>
            </div>

            <div class="sidebar-item">
                <i class="fas fa-briefcase"></i>
                <a href="portfolios.php">Portfolios</a>
            </div>

            <div class="sidebar-item">
                <i class="fas fa-paint-brush"></i>
                <a href="templates.php">Templates</a>
            </div>

            <div class="sidebar-item">
                <i class="fas fa-list"></i>
                <a href="categories.php">Categories</a>
            </div>

            <div class="sidebar-item">
                <i class="fas fa-envelope"></i>
                <a href="contact_messages.php">Contact Messages</a>
            </div>

            <div class="sidebar-item">
                <i class="fas fa-sign-out-alt"></i>
                <a href="admin_logout.php">Logout</a>
            </div>
        </div>

        <div class="content">

            <h2 class="page-title">Welcome to Admin Dashboard</h2>

            <div class="dashboard-boxes">

                <div class="count-box">
                    <i class="fas fa-users"></i>
                    <div>
                        <h3>Total Users</h3>
                        <p><?php echo $total_users; ?></p>
                    </div>
                </div>

                <div class="count-box">
                    <i class="fas fa-user-plus"></i>
                    <div>
                        <h3>New Users Today</h3>
                        <p><?php echo $new_users; ?></p>
                    </div>
                </div>

                <div class="count-box">
                    <i class="fas fa-user-check"></i>
                    <div>
                        <h3>Active Users</h3>
                        <p><?php echo $active_users; ?></p>
                    </div>
                </div>

               <div class="count-box">
                    <i class="fas fa-user-times"></i>
                    <div>
                        <h3>Inactive Users</h3>
                        <p><?php echo $inactive_users; ?></p>
                    </div>
                </div>

                <div class="count-box">
                    <i class="fas fa-briefcase"></i>
                    <div>
                        <h3>Total Portfolios</h3>
                        <p><?php echo $total_portfolios; ?></p>
                    </div>
                </div>

                <div class="count-box">
                    <i class="fas fa-file-alt"></i>
                    <div>
                        <h3>Draft Portfolios</h3>
                        <p><?php echo $draft_portfolios; ?></p>
                    </div>
                </div>

                <div class="count-box">
                    <i class="fas fa-globe"></i>
                    <div>
                        <h3>Published Portfolios</h3>
                        <p><?php echo $published_portfolios; ?></p>
                    </div>
                </div>

                <div class="count-box">
                    <i class="fas fa-paint-brush"></i>
                    <div>
                        <h3>Total Templates</h3>
                        <p><?php echo $total_templates; ?></p>
                    </div>
                </div>

                <div class="count-box">
                    <i class="fas fa-list"></i>
                    <div>
                        <h3>Total Categories</h3>
                        <p><?php echo $total_categories; ?></p>
                    </div>
                </div>

                <div class="count-box">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h3>Contact Messages</h3>
                        <p><?php echo $total_messages; ?></p>
                    </div>
                </div>

            </div>

            <div class="table-section">

                <div class="card">
                    <h3>Latest Users</h3>

                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                            </tr>

                            <?php
                            if ($latest_users && mysqli_num_rows($latest_users) > 0) {
                                while ($user = mysqli_fetch_assoc($latest_users)) {
                            ?>
                                    <tr>
                                        <td><?php echo $user['name']; ?></td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td>
                                            <?php
                                            if ($user['status'] == 'active') {
                                                echo "<span class='status-active'>Active</span>";
                                            } else {
                                                echo "<span class='status-inactive'>Inactive</span>";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='3'>No users found</td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <h3>Latest Contact Messages</h3>

                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                            </tr>

                            <?php
                            if ($latest_messages && mysqli_num_rows($latest_messages) > 0) {
                                while ($message = mysqli_fetch_assoc($latest_messages)) {
                            ?>
                                    <tr>
                                        <td><?php echo $message['name']; ?></td>
                                        <td><?php echo $message['email']; ?></td>
                                        <td><?php echo $message['subject']; ?></td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='3'>No messages found</td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>

<script src="assets/js/admin_script.js"></script>

</body>
</html>

<?php
mysqli_close($con);
?>