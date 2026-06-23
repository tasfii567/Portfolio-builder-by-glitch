<?php
include 'admin_auth.php';
include 'config.php';

function getCount($con, $sql) {
    $result = mysqli_query($con, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

$total_users        = getCount($con, "SELECT COUNT(id) AS total FROM users");
$new_users          = getCount($con, "SELECT COUNT(id) AS total FROM users WHERE DATE(created_at) = CURDATE()");
$active_users       = getCount($con, "SELECT COUNT(id) AS total FROM users WHERE status = 'active'");
$inactive_users     = getCount($con, "SELECT COUNT(id) AS total FROM users WHERE status = 'inactive'");
$total_portfolios   = getCount($con, "SELECT COUNT(id) AS total FROM portfolios");
$draft_portfolios   = getCount($con, "SELECT COUNT(id) AS total FROM portfolios WHERE status = 'draft'");
$published_portfolios = getCount($con, "SELECT COUNT(id) AS total FROM portfolios WHERE status = 'published'");
$total_templates    = getCount($con, "SELECT COUNT(id) AS total FROM templates");
$total_categories   = getCount($con, "SELECT COUNT(id) AS total FROM categories");
$total_messages     = getCount($con, "SELECT COUNT(id) AS total FROM contact_messages");

$latest_users = mysqli_query($con, "SELECT id, name, email, status, created_at FROM users ORDER BY id DESC LIMIT 5");
$latest_messages = mysqli_query($con, "SELECT id, name, email, subject, created_at FROM contact_messages ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portfolio Builder — Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg:           #f7f3ea;
      --surface:      #fff;
      --surface-2:    #ede7d6;
      --line:         #e8e1d3;
      --text:         #2b2926;
      --muted:        #8a8270;
      --muted-2:      #a39c89;
      --primary:      #36402c;
      --primary-soft: #46532f;
      --accent:       #6b8c5a;
      --good:         #6f9a52;
      --danger:       #a8442f;
      --danger-bg:    #f3e3df;
      --danger-line:  #ecc9c1;
      --radius:       14px;
      --shadow:       0 8px 24px rgba(43,41,38,.07);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
    }

    a { text-decoration: none; color: inherit; }

    /* ── Layout ── */
    .app {
      display: grid;
      grid-template-columns: 260px 1fr;
      grid-template-rows: 60px 1fr;
      min-height: 100vh;
    }

    /* ── Header ── */
    .header {
      grid-column: 1 / -1;
      background: var(--surface);
      border-bottom: 1px solid var(--line);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 28px;
      position: sticky;
      top: 0;
      z-index: 100;
      height: 60px;
    }

    .header-title {
      display: flex;
      align-items: center;
      gap: 14px;
      font-size: 15px;
      font-weight: 800;
      letter-spacing: -.02em;
      color: var(--text);
    }

    .header-title .brand-g { color: var(--accent); }

    .menu-btn {
      width: 36px; height: 36px;
      border-radius: 9px;
      border: 1px solid var(--line);
      background: var(--surface-2);
      color: var(--text);
      cursor: pointer;
      display: grid; place-items: center;
      font-size: 15px;
      display: none; /* shown on mobile */
    }

    .header-logout a {
      display: inline-flex; align-items: center; gap: 7px;
      padding: 8px 18px;
      background: var(--danger-bg);
      color: var(--danger);
      border: 1px solid var(--danger-line);
      border-radius: 9px;
      font-size: 13px;
      font-weight: 600;
      transition: background .18s;
    }
    .header-logout a:hover { background: #eed6d0; }

    /* ── Sidebar ── */
    .sidebar {
      background: var(--surface);
      border-right: 1px solid var(--line);
      padding: 20px 16px;
      display: flex;
      flex-direction: column;
      gap: 4px;
      position: sticky;
      top: 60px;
      height: calc(100vh - 60px);
      overflow-y: auto;
    }

    .sidebar-header {
      text-align: center;
      padding: 10px 8px 22px;
      border-bottom: 1px solid var(--line);
      margin-bottom: 10px;
    }

    .admin-avatar {
      width: 52px; height: 52px;
      border-radius: 50%;
      background: var(--primary);
      color: #fff;
      font-size: 20px;
      font-weight: 800;
      display: grid; place-items: center;
      margin: 0 auto 10px;
    }

    .sidebar-header h3 {
      font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 3px;
    }
    .sidebar-header p { font-size: 12px; color: var(--muted); }

    .nav-label {
      font-size: 10px; text-transform: uppercase;
      letter-spacing: 1px; color: var(--muted-2);
      padding: 12px 10px 5px;
    }

    .sidebar-item {
      display: flex;
      align-items: center;
      gap: 11px;
      padding: 10px 12px;
      border-radius: 10px;
      color: var(--muted);
      font-size: 13.5px;
      font-weight: 500;
      cursor: pointer;
      transition: .18s;
    }
    .sidebar-item i { width: 18px; text-align: center; font-size: 14px; }
    .sidebar-item a { color: inherit; }
    .sidebar-item:hover { background: var(--surface-2); color: var(--text); }
    .sidebar-item.active { background: var(--primary); color: #fff; }
    .sidebar-item.active a { color: #fff; }

    .sidebar-logout-item {
      margin-top: auto;
      padding-top: 12px;
      border-top: 1px solid var(--line);
    }
    .sidebar-logout-item .sidebar-item {
      background: var(--danger-bg);
      color: var(--danger);
      border: 1px solid var(--danger-line);
      justify-content: center;
    }
    .sidebar-logout-item .sidebar-item:hover { background: #eed6d0; }
    .sidebar-logout-item .sidebar-item a { color: var(--danger); }

    /* ── Content ── */
    .content {
      padding: 28px 32px;
      overflow-x: hidden;
    }

    .page-title {
      font-size: 22px;
      font-weight: 800;
      letter-spacing: -.02em;
      color: var(--text);
      margin-bottom: 22px;
    }

    /* ── Stat grid ── */
    .dashboard-boxes {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 28px;
    }

    .count-box {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 20px 18px;
      display: flex;
      align-items: center;
      gap: 14px;
      transition: transform .18s, box-shadow .18s;
    }
    .count-box:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(43,41,38,.11);
    }

    .count-box i {
      width: 44px; height: 44px;
      border-radius: 11px;
      background: var(--surface-2);
      color: var(--accent);
      display: grid; place-items: center;
      font-size: 18px;
      flex-shrink: 0;
    }

    .count-box h3 {
      font-size: 11.5px;
      font-weight: 600;
      color: var(--muted);
      margin-bottom: 4px;
      text-transform: uppercase;
      letter-spacing: .4px;
    }

    .count-box p {
      font-size: 24px;
      font-weight: 800;
      color: var(--text);
      letter-spacing: -.02em;
      line-height: 1;
    }

    /* ── Tables section ── */
    .table-section {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .card {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 20px 20px 16px;
    }

    .card h3 {
      font-size: 14px;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 14px;
      padding-bottom: 10px;
      border-bottom: 1px solid var(--line);
      display: flex; align-items: center; gap: 8px;
    }
    .card h3::before {
      content: '';
      display: inline-block;
      width: 3px; height: 14px;
      background: var(--accent);
      border-radius: 2px;
    }

    .table-responsive { overflow-x: auto; }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    th {
      text-align: left;
      padding: 8px 10px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .5px;
      color: var(--muted);
      border-bottom: 1px solid var(--line);
    }

    td {
      padding: 9px 10px;
      color: var(--text);
      border-bottom: 1px solid var(--line);
      font-size: 13px;
    }

    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--surface-2); }

    .status-active {
      display: inline-block;
      padding: 2px 10px;
      background: #e8efe0;
      color: #4f6b43;
      border: 1px solid #cfe0b6;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
    }

    .status-inactive {
      display: inline-block;
      padding: 2px 10px;
      background: var(--danger-bg);
      color: var(--danger);
      border: 1px solid var(--danger-line);
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
    }

    /* ── Mobile ── */
    @media (max-width: 860px) {
      .app { grid-template-columns: 1fr; }
      .sidebar {
        position: fixed;
        left: -280px;
        top: 60px;
        height: calc(100vh - 60px);
        z-index: 90;
        width: 260px;
        transition: left .25s;
      }
      .sidebar.open { left: 0; }
      .menu-btn { display: grid; }
      .content { padding: 20px; }
      .table-section { grid-template-columns: 1fr; }
      .dashboard-boxes { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
    }
  </style>
</head>
<body>

<div class="app">

  <!-- ── Header ── -->
  <header class="header">
    <div class="header-title">
      <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">
        <i class="fas fa-bars"></i>
      </button>
      Portfolio<span class="brand-g">Builder</span>&nbsp;Admin
    </div>
    <div class="header-logout">
      <a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </header>

  <!-- ── Sidebar ── -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="admin-avatar">
        <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
      </div>
      <h3><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h3>
      <p><?php echo htmlspecialchars($_SESSION['admin_email']); ?></p>
    </div>

    <div class="nav-label">Menu</div>

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

    <div class="sidebar-logout-item">
      <div class="sidebar-item">
        <i class="fas fa-sign-out-alt"></i>
        <a href="admin_logout.php">Logout</a>
      </div>
    </div>
  </aside>

  <!-- ── Content ── -->
  <main class="content">
    <h2 class="page-title">Welcome to Admin Dashboard</h2>

    <!-- Stat boxes -->
    <div class="dashboard-boxes">

      <div class="count-box">
        <i class="fas fa-users"></i>
        <div><h3>Total Users</h3><p><?php echo $total_users; ?></p></div>
      </div>

      <div class="count-box">
        <i class="fas fa-user-plus"></i>
        <div><h3>New Users Today</h3><p><?php echo $new_users; ?></p></div>
      </div>

      <div class="count-box">
        <i class="fas fa-user-check"></i>
        <div><h3>Active Users</h3><p><?php echo $active_users; ?></p></div>
      </div>

      <div class="count-box">
        <i class="fas fa-user-times"></i>
        <div><h3>Inactive Users</h3><p><?php echo $inactive_users; ?></p></div>
      </div>

      <div class="count-box">
        <i class="fas fa-briefcase"></i>
        <div><h3>Total Portfolios</h3><p><?php echo $total_portfolios; ?></p></div>
      </div>

      <div class="count-box">
        <i class="fas fa-file-alt"></i>
        <div><h3>Draft Portfolios</h3><p><?php echo $draft_portfolios; ?></p></div>
      </div>

      <div class="count-box">
        <i class="fas fa-globe"></i>
        <div><h3>Published Portfolios</h3><p><?php echo $published_portfolios; ?></p></div>
      </div>

      <div class="count-box">
        <i class="fas fa-paint-brush"></i>
        <div><h3>Total Templates</h3><p><?php echo $total_templates; ?></p></div>
      </div>

      <div class="count-box">
        <i class="fas fa-list"></i>
        <div><h3>Total Categories</h3><p><?php echo $total_categories; ?></p></div>
      </div>

      <div class="count-box">
        <i class="fas fa-envelope"></i>
        <div><h3>Contact Messages</h3><p><?php echo $total_messages; ?></p></div>
      </div>

    </div>

    <!-- Tables -->
    <div class="table-section">

      <div class="card">
        <h3>Latest Users</h3>
        <div class="table-responsive">
          <table>
            <tr><th>Name</th><th>Email</th><th>Status</th></tr>
            <?php
            if ($latest_users && mysqli_num_rows($latest_users) > 0) {
                while ($u = mysqli_fetch_assoc($latest_users)) { ?>
                  <tr>
                    <td><?php echo htmlspecialchars($u['name']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                      <?php if ($u['status'] == 'active'): ?>
                        <span class="status-active">Active</span>
                      <?php else: ?>
                        <span class="status-inactive">Inactive</span>
                      <?php endif; ?>
                    </td>
                  </tr>
            <?php }
            } else { echo "<tr><td colspan='3'>No users found</td></tr>"; } ?>
          </table>
        </div>
      </div>

      <div class="card">
        <h3>Latest Contact Messages</h3>
        <div class="table-responsive">
          <table>
            <tr><th>Name</th><th>Email</th><th>Subject</th></tr>
            <?php
            if ($latest_messages && mysqli_num_rows($latest_messages) > 0) {
                while ($msg = mysqli_fetch_assoc($latest_messages)) { ?>
                  <tr>
                    <td><?php echo htmlspecialchars($msg['name']); ?></td>
                    <td><?php echo htmlspecialchars($msg['email']); ?></td>
                    <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                  </tr>
            <?php }
            } else { echo "<tr><td colspan='3'>No messages found</td></tr>"; } ?>
          </table>
        </div>
      </div>

    </div>
  </main>

</div>

<?php mysqli_close($con); ?>
</body>
</html>
