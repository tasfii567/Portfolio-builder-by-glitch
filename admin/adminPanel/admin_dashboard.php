<?php
session_start();

// Later database/login complete korle ei part use korba
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: admin_login.php");
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Portfolio Builder</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="../assets/css/dashboard1.css">
</head>
<body>

<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-briefcase-fill"></i>
            <span>Portfolio<span class="green">Builder</span></span>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="#" class="active">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="bi bi-people"></i>
                    Manage Users
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="bi bi-window-stack"></i>
                    Templates
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="bi bi-folder2-open"></i>
                    Portfolios
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="bi bi-envelope"></i>
                    Contact Messages
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="bi bi-tags"></i>
                    Categories
                </a>
            </li>
        </ul>

        <a href="admin_logout.php" class="logout-btn">
            <i class="bi bi-box-arrow-right"></i>
            Logout
        </a>
    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <!-- Top Navbar -->
        <header class="topbar">
            <button class="menu-btn" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <div>
                <h2>Admin Dashboard</h2>
                <p>Welcome back, Admin</p>
            </div>

            <div class="admin-profile">
                <div class="admin-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div>
                    <h5>Admin</h5>
                    <span>Super Admin</span>
                </div>
            </div>
        </header>

        <!-- Dashboard Cards -->
        <section class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <p>Total Users</p>
                    <h3>120</h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <div>
                    <p>New Users</p>
                    <h3>18</h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-folder-fill"></i>
                </div>
                <div>
                    <p>Total Portfolios</p>
                    <h3>85</h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-cloud-check-fill"></i>
                </div>
                <div>
                    <p>Published</p>
                    <h3>54</h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </div>
                <div>
                    <p>Draft Portfolios</p>
                    <h3>31</h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-envelope-fill"></i>
                </div>
                <div>
                    <p>Messages</p>
                    <h3>12</h3>
                </div>
            </div>

        </section>

        <!-- Bottom Section -->
        <section class="dashboard-row">

            <div class="panel">
                <div class="panel-header">
                    <h3>Recent Users</h3>
                    <a href="#">View All</a>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>Monoswita Shome</td>
                            <td>monoswita@email.com</td>
                            <td><span class="status active">Active</span></td>
                            <td><button class="small-btn">View</button></td>
                        </tr>

                        <tr>
                            <td>Uma Dhar</td>
                            <td>uma@email.com</td>
                            <td><span class="status inactive">active</span></td>
                            <td><button class="small-btn">View</button></td>
                        </tr>

                      
                    </tbody>
                </table>
            </div>

            <div class="panel quick-panel">
                <h3>Quick Actions</h3>

                <a href="#" class="quick-link">
                    <i class="bi bi-plus-circle"></i>
                    Add Template
                </a>

                <a href="#" class="quick-link">
                    <i class="bi bi-search"></i>
                    Search User
                </a>

                <a href="#" class="quick-link">
                    <i class="bi bi-envelope-open"></i>
                    View Messages
                </a>

                <a href="#" class="quick-link">
                    <i class="bi bi-sliders"></i>
                    Site Settings
                </a>
            </div>

        </section>

    </main>
</div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("show");
}
</script>

</body>
</html>