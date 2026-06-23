<?php
include 'admin_auth.php';
include 'config.php';

$templates = mysqli_query($con, "SELECT * FROM templates ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Templates — Admin Panel</title>
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
      display: flex; align-items: center;
      justify-content: space-between;
      padding: 0 28px;
      position: sticky; top: 0; z-index: 100;
      height: 60px;
    }

    .header-title {
      display: flex; align-items: center; gap: 14px;
      font-size: 15px; font-weight: 800;
      letter-spacing: -.02em; color: var(--text);
    }

    .brand-g { color: var(--accent); }

    .menu-btn {
      width: 36px; height: 36px;
      border-radius: 9px;
      border: 1px solid var(--line);
      background: var(--surface-2);
      color: var(--text); cursor: pointer;
      display: none; place-items: center; font-size: 15px;
    }

    .header-logout a {
      display: inline-flex; align-items: center; gap: 7px;
      padding: 8px 18px;
      background: var(--danger-bg); color: var(--danger);
      border: 1px solid var(--danger-line);
      border-radius: 9px; font-size: 13px; font-weight: 600;
      transition: background .18s;
    }
    .header-logout a:hover { background: #eed6d0; }

    /* ── Sidebar ── */
    .sidebar {
      background: var(--surface);
      border-right: 1px solid var(--line);
      padding: 20px 16px;
      display: flex; flex-direction: column; gap: 4px;
      position: sticky; top: 60px;
      height: calc(100vh - 60px); overflow-y: auto;
    }

    .sidebar-header {
      text-align: center;
      padding: 10px 8px 22px;
      border-bottom: 1px solid var(--line);
      margin-bottom: 10px;
    }

    .admin-avatar {
      width: 52px; height: 52px; border-radius: 50%;
      background: var(--primary); color: #fff;
      font-size: 20px; font-weight: 800;
      display: grid; place-items: center;
      margin: 0 auto 10px;
    }

    .sidebar-header h3 { font-size: 14px; font-weight: 700; margin-bottom: 3px; }
    .sidebar-header p  { font-size: 12px; color: var(--muted); }

    .nav-label {
      font-size: 10px; text-transform: uppercase;
      letter-spacing: 1px; color: var(--muted-2);
      padding: 12px 10px 5px;
    }

    .sidebar-item {
      display: flex; align-items: center; gap: 11px;
      padding: 10px 12px; border-radius: 10px;
      color: var(--muted); font-size: 13.5px; font-weight: 500;
      transition: .18s; cursor: pointer;
    }
    .sidebar-item i { width: 18px; text-align: center; font-size: 14px; }
    .sidebar-item a { color: inherit; }
    .sidebar-item:hover { background: var(--surface-2); color: var(--text); }
    .sidebar-item.active { background: var(--primary); color: #fff; }
    .sidebar-item.active a { color: #fff; }

    .sidebar-logout-item {
      margin-top: auto; padding-top: 12px;
      border-top: 1px solid var(--line);
    }
    .sidebar-logout-item .sidebar-item {
      background: var(--danger-bg); color: var(--danger);
      border: 1px solid var(--danger-line); justify-content: center;
    }
    .sidebar-logout-item .sidebar-item:hover { background: #eed6d0; }
    .sidebar-logout-item .sidebar-item a { color: var(--danger); }

    /* ── Content ── */
    .content { padding: 28px 32px; overflow-x: hidden; }

    .topbar {
      display: flex; align-items: center;
      justify-content: space-between;
      flex-wrap: wrap; gap: 12px;
      margin-bottom: 22px;
    }

    .topbar h2 {
      font-size: 22px; font-weight: 800;
      letter-spacing: -.02em; color: var(--text);
    }
    .topbar p { font-size: 13px; color: var(--muted); margin-top: 2px; }

    .back-btn {
      display: inline-flex; align-items: center; gap: 7px;
      padding: 9px 18px;
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 10px;
      font-size: 13px; font-weight: 600; color: var(--text);
      transition: background .18s, transform .15s;
    }
    .back-btn:hover { background: var(--surface-2); transform: translateY(-1px); }

    /* ── Card ── */
    .card {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 22px 22px 18px;
    }

    .card-head {
      display: flex; align-items: center;
      justify-content: space-between;
      margin-bottom: 16px; padding-bottom: 12px;
      border-bottom: 1px solid var(--line);
    }

    .card-head h3 {
      font-size: 14px; font-weight: 700;
      display: flex; align-items: center; gap: 8px;
    }
    .card-head h3::before {
      content: '';
      display: inline-block;
      width: 3px; height: 14px;
      background: var(--accent); border-radius: 2px;
    }

    .count-pill {
      font-size: 12px; font-weight: 600; color: var(--muted);
      background: var(--surface-2); border: 1px solid var(--line);
      border-radius: 20px; padding: 3px 12px;
    }

    /* ── Table ── */
    .table-responsive { overflow-x: auto; }

    table { width: 100%; border-collapse: collapse; font-size: 13px; }

    th {
      text-align: left; padding: 9px 12px;
      font-size: 11px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .5px;
      color: var(--muted); border-bottom: 1px solid var(--line);
      white-space: nowrap;
    }

    td {
      padding: 10px 12px; color: var(--text);
      border-bottom: 1px solid var(--line); font-size: 13px;
    }

    tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: var(--surface-2); }

    .sl-cell { color: var(--muted); font-size: 12px; font-weight: 600; }

    .status-active {
      display: inline-block; padding: 2px 10px;
      background: #e8efe0; color: #4f6b43;
      border: 1px solid #cfe0b6;
      border-radius: 20px; font-size: 11px; font-weight: 600;
    }

    .status-inactive {
      display: inline-block; padding: 2px 10px;
      background: var(--danger-bg); color: var(--danger);
      border: 1px solid var(--danger-line);
      border-radius: 20px; font-size: 11px; font-weight: 600;
    }

    .status-other {
      display: inline-block; padding: 2px 10px;
      background: var(--surface-2); color: var(--muted);
      border: 1px solid var(--line);
      border-radius: 20px; font-size: 11px; font-weight: 600;
    }

    .empty-row td {
      text-align: center; color: var(--muted);
      padding: 32px; font-size: 13px;
    }

    /* ── Mobile ── */
    @media (max-width: 860px) {
      .app { grid-template-columns: 1fr; }
      .sidebar {
        position: fixed; left: -280px; top: 60px;
        height: calc(100vh - 60px);
        z-index: 90; width: 260px; transition: left .25s;
      }
      .sidebar.open { left: 0; }
      .menu-btn { display: grid; }
      .content { padding: 20px; }
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

    <div class="sidebar-item">
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
    <div class="sidebar-item active">
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

    <div class="topbar">
      <div>
        <h2>Templates</h2>
        <p>View and manage all available templates.</p>
      </div>
      <a class="back-btn" href="admin_dashboard.php">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </a>
    </div>

    <div class="card">
      <div class="card-head">
        <h3>All Templates</h3>
        <span class="count-pill">
          <?php echo mysqli_num_rows($templates); ?> total
        </span>
      </div>

      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>SL</th>
              <th>Template Name</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($templates && mysqli_num_rows($templates) > 0):
              $sl = 1;
              while ($template = mysqli_fetch_assoc($templates)):
                $status = strtolower($template['status'] ?? '');
            ?>
              <tr>
                <td class="sl-cell"><?php echo $sl++; ?></td>
                <td><?php echo htmlspecialchars($template['name'] ?? '—'); ?></td>
                <td>
                  <?php if ($status === 'active'): ?>
                    <span class="status-active">Active</span>
                  <?php elseif ($status === 'inactive'): ?>
                    <span class="status-inactive">Inactive</span>
                  <?php else: ?>
                    <span class="status-other"><?php echo htmlspecialchars($template['status'] ?? '—'); ?></span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php
              endwhile;
            else:
            ?>
              <tr class="empty-row">
                <td colspan="3">
                  <i class="fas fa-paint-brush" style="font-size:24px;color:var(--muted-2);display:block;margin-bottom:8px"></i>
                  No templates found
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<?php mysqli_close($con); ?>
</body>
</html>
