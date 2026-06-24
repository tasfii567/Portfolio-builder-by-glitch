<?php
include 'admin_auth.php';
include 'config.php';

function js_arg($value): string
{
  return htmlspecialchars(
    json_encode($value, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE),
    ENT_QUOTES,
    'UTF-8'
  );
}

$currentUserId = (int) ($_SESSION['user_id'] ?? 0);
$hasRoleColumn = admin_has_column($pdo, 'users', 'role');
$adminUserCount = $hasRoleColumn
  ? (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn()
  : 0;

// ── Handle Delete ──
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);

  if ($id === $currentUserId) {
    header("Location: users.php?msg=cannot_delete_self");
    exit;
  }

  if ($hasRoleColumn) {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $targetRole = $stmt->fetchColumn();

    if ($targetRole === 'admin' && $adminUserCount <= 1) {
      header("Location: users.php?msg=cannot_delete_last_admin");
      exit;
    }
  }

  $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
  $stmt->execute([$id]);
  header("Location: users.php?msg=deleted");
  exit;
}

// ── Handle Edit Submit ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
  $id     = intval($_POST['edit_id']);
  $name   = trim($_POST['name'] ?? '');
  $email  = trim($_POST['email'] ?? '');

  $sets = ['name = ?', 'email = ?'];
  $params = [$name, $email];

  $params[] = $id;
  $stmt = $pdo->prepare("UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?");
  $stmt->execute($params);
  header("Location: users.php?msg=updated");
  exit;
}

$order = admin_has_column($pdo, 'users', 'created_at') ? 'created_at DESC, id DESC' : 'id DESC';
$users = admin_fetch_all($pdo, "SELECT * FROM users ORDER BY $order");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users — Admin Panel</title>
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

    /* ── Toast ── */
    .toast {
      display: flex; align-items: center; gap: 10px;
      padding: 12px 18px;
      border-radius: 10px;
      font-size: 13px; font-weight: 600;
      margin-bottom: 18px;
      animation: fadeIn .3s ease;
    }
    .toast.success { background: #e8efe0; color: #4f6b43; border: 1px solid #cfe0b6; }
    .toast.error   { background: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger-line); }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }

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

    .user-count {
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
      vertical-align: middle;
    }

    tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: var(--surface-2); }

    .sl-cell { color: var(--muted); font-size: 12px; font-weight: 600; }

    /* ── Action Buttons ── */
    .actions { display: flex; gap: 7px; }

    .btn-edit, .btn-delete {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 5px 12px; border-radius: 8px;
      font-size: 12px; font-weight: 600;
      border: none; cursor: pointer; transition: .18s;
    }

    .btn-edit {
      background: #eef3e8; color: #4f6b43;
      border: 1px solid #cfe0b6;
    }
    .btn-edit:hover { background: #ddecd2; }

    .btn-delete {
      background: var(--danger-bg); color: var(--danger);
      border: 1px solid var(--danger-line);
    }
    .btn-delete:hover { background: #eed6d0; }
    .btn-delete:disabled { opacity: .45; cursor: not-allowed; }
    .btn-delete:disabled:hover { background: var(--danger-bg); }

    .empty-row td {
      text-align: center; color: var(--muted);
      padding: 32px; font-size: 13px;
    }

    /* ── Modal ── */
    .modal-overlay {
      display: none;
      position: fixed; inset: 0;
      background: rgba(43,41,38,.45);
      z-index: 200;
      align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }

    .modal {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      box-shadow: 0 20px 50px rgba(43,41,38,.18);
      padding: 28px;
      width: 100%; max-width: 460px;
      animation: modalIn .22s ease;
    }
    @keyframes modalIn { from { opacity: 0; transform: scale(.96) translateY(8px); } to { opacity: 1; transform: none; } }

    .modal-head {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 20px; padding-bottom: 12px;
      border-bottom: 1px solid var(--line);
    }
    .modal-head h3 {
      font-size: 15px; font-weight: 800;
      display: flex; align-items: center; gap: 8px;
    }
    .modal-head h3::before {
      content: '';
      display: inline-block;
      width: 3px; height: 15px;
      background: var(--accent); border-radius: 2px;
    }

    .modal-close {
      width: 30px; height: 30px;
      border-radius: 8px; border: 1px solid var(--line);
      background: var(--surface-2);
      color: var(--muted); cursor: pointer; font-size: 14px;
      display: grid; place-items: center; transition: .18s;
    }
    .modal-close:hover { background: var(--line); color: var(--text); }

    .form-group { margin-bottom: 14px; }

    .form-group label {
      display: block; font-size: 12px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .4px;
      color: var(--muted); margin-bottom: 6px;
    }

    .form-group input,
    .form-group select {
      width: 100%; padding: 9px 12px;
      border: 1px solid var(--line);
      border-radius: 9px; font-size: 13px;
      font-family: 'Inter', sans-serif;
      color: var(--text); background: var(--surface);
      outline: none; transition: border .18s;
    }
    .form-group input:focus,
    .form-group select:focus { border-color: var(--accent); }

    .modal-footer {
      display: flex; justify-content: flex-end; gap: 10px;
      margin-top: 20px; padding-top: 14px;
      border-top: 1px solid var(--line);
    }

    .btn-cancel {
      padding: 9px 20px; border-radius: 9px;
      border: 1px solid var(--line);
      background: var(--surface-2);
      font-size: 13px; font-weight: 600; color: var(--muted);
      cursor: pointer; transition: .18s;
    }
    .btn-cancel:hover { background: var(--line); color: var(--text); }

    .btn-save {
      padding: 9px 22px; border-radius: 9px;
      border: none;
      background: var(--primary); color: #fff;
      font-size: 13px; font-weight: 700;
      cursor: pointer; transition: background .18s;
      display: inline-flex; align-items: center; gap: 7px;
    }
    .btn-save:hover { background: var(--primary-soft); }

    /* ── Delete Confirm Modal ── */
    .del-modal {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      box-shadow: 0 20px 50px rgba(43,41,38,.18);
      padding: 28px;
      width: 100%; max-width: 380px;
      text-align: center;
      animation: modalIn .22s ease;
    }

    .del-icon {
      width: 54px; height: 54px;
      border-radius: 50%;
      background: var(--danger-bg);
      border: 1px solid var(--danger-line);
      display: grid; place-items: center;
      margin: 0 auto 14px;
      font-size: 20px; color: var(--danger);
    }

    .del-modal h3 { font-size: 16px; font-weight: 800; margin-bottom: 7px; }
    .del-modal p  { font-size: 13px; color: var(--muted); margin-bottom: 22px; line-height: 1.5; }

    .del-modal-btns { display: flex; gap: 10px; justify-content: center; }

    .btn-confirm-del {
      padding: 9px 22px; border-radius: 9px; border: none;
      background: var(--danger); color: #fff;
      font-size: 13px; font-weight: 700; cursor: pointer;
      transition: opacity .18s;
    }
    .btn-confirm-del:hover { opacity: .88; }

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
      .modal, .del-modal { margin: 16px; }
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
      <a href="../nahin/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
    <div class="sidebar-item active">
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
      <i class="fas fa-envelope"></i>
      <a href="contact_messages.php">Contact Messages</a>
    </div>

    <div class="sidebar-logout-item">
      <div class="sidebar-item">
        <i class="fas fa-sign-out-alt"></i>
        <a href="../nahin/logout.php">Logout</a>
      </div>
    </div>
  </aside>

  <!-- ── Content ── -->
  <main class="content">

    <div class="topbar">
      <div>
        <h2>Users</h2>
        <p>Manage all registered users.</p>
      </div>
      <a class="back-btn" href="admin_dashboard.php">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </a>
    </div>

    <!-- Toast -->
    <?php if (isset($_GET['msg'])): ?>
      <?php if ($_GET['msg'] === 'updated'): ?>
        <div class="toast success"><i class="fas fa-check-circle"></i> User updated successfully.</div>
      <?php elseif ($_GET['msg'] === 'deleted'): ?>
        <div class="toast success"><i class="fas fa-check-circle"></i> User deleted successfully.</div>
      <?php elseif ($_GET['msg'] === 'cannot_delete_self'): ?>
        <div class="toast error"><i class="fas fa-exclamation-circle"></i> You cannot delete your own admin account.</div>
      <?php elseif ($_GET['msg'] === 'cannot_delete_last_admin'): ?>
        <div class="toast error"><i class="fas fa-exclamation-circle"></i> You cannot delete the last admin account.</div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="card">
      <div class="card-head">
        <h3>All Users</h3>
        <span class="user-count">
          <?php echo count($users); ?> total
        </span>
      </div>

      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>SL</th>
              <th>Name</th>
              <th>Email</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (!empty($users)):
              $sl = 1;
              foreach ($users as $user):
                $isCurrentUser = (int) $user['id'] === $currentUserId;
                $isLastAdminUser = $hasRoleColumn && (($user['role'] ?? '') === 'admin') && $adminUserCount <= 1;
                $deleteDisabledReason = $isCurrentUser
                  ? 'You cannot delete your own account'
                  : ($isLastAdminUser ? 'You cannot delete the last admin account' : '');
            ?>
              <tr>
                <td class="sl-cell"><?php echo $sl++; ?></td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                  <div class="actions">
                    <button class="btn-edit"
                      onclick="openEdit(
                        <?php echo (int) $user['id']; ?>,
                        <?php echo js_arg($user['name'] ?? ''); ?>,
                        <?php echo js_arg($user['email'] ?? ''); ?>
                      )">
                      <i class="fas fa-pen"></i> Edit
                    </button>
                    <?php if ($deleteDisabledReason): ?>
                      <button class="btn-delete" type="button" disabled title="<?php echo htmlspecialchars($deleteDisabledReason); ?>">
                        <i class="fas fa-trash"></i> Delete
                      </button>
                    <?php else: ?>
                      <button class="btn-delete" type="button"
                        onclick="openDelete(<?php echo (int) $user['id']; ?>, <?php echo js_arg($user['name'] ?? ''); ?>)">
                        <i class="fas fa-trash"></i> Delete
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php
              endforeach;
            else:
            ?>
              <tr class="empty-row">
                  <td colspan="4">
                  <i class="fas fa-users" style="font-size:24px;color:var(--muted-2);display:block;margin-bottom:8px"></i>
                  No users found
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<!-- ── Edit Modal ── -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-head">
      <h3>Edit User</h3>
      <button class="modal-close" onclick="closeEdit()"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="users.php">
      <input type="hidden" name="edit_id" id="edit_id">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" id="edit_name" required placeholder="Enter full name">
      </div>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" id="edit_email" required placeholder="Enter email">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeEdit()">Cancel</button>
        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- ── Delete Confirm Modal ── -->
<div class="modal-overlay" id="deleteModal">
  <div class="del-modal">
    <div class="del-icon"><i class="fas fa-trash-alt"></i></div>
    <h3>Delete User?</h3>
    <p>You are about to delete <strong id="del_name"></strong>. This action cannot be undone.</p>
    <div class="del-modal-btns">
      <button class="btn-cancel" onclick="closeDelete()">Cancel</button>
      <a id="del_link" href="#" class="btn-confirm-del"><i class="fas fa-trash-alt"></i> Yes, Delete</a>
    </div>
  </div>
</div>

<script>
  // ── Edit Modal ──
  function openEdit(id, name, email) {
    document.getElementById('edit_id').value    = id;
    document.getElementById('edit_name').value  = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('editModal').classList.add('open');
  }
  function closeEdit() {
    document.getElementById('editModal').classList.remove('open');
  }

  // ── Delete Modal ──
  function openDelete(id, name) {
    document.getElementById('del_name').textContent = name;
    document.getElementById('del_link').href = 'users.php?delete=' + id;
    document.getElementById('deleteModal').classList.add('open');
  }
  function closeDelete() {
    document.getElementById('deleteModal').classList.remove('open');
  }

  // Close modals on overlay click
  document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
  });
  document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDelete();
  });

  // Auto-hide toast after 4s
  const toast = document.querySelector('.toast');
  if (toast) setTimeout(() => toast.style.display = 'none', 4000);
</script>

</body>
</html>
