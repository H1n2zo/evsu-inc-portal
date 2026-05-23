<?php
// admin/users.php — User management with role assignment
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo    = getDB();
$msg    = '';
$error  = '';
$filter = $_GET['filter'] ?? 'all';

// ─── POST ACTIONS ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'approve') {
        $uid = (int)$_POST['user_id'];
        $pdo->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$uid]);
        $uname = $pdo->prepare("SELECT username FROM users WHERE id=?");
        $uname->execute([$uid]); $row = $uname->fetch();
        auditLog($_SESSION['user_id'], $_SESSION['username'], 'admin', 'User Approved', "Activated account: ".($row['username']??''), $_SERVER['REMOTE_ADDR']??'');
        $msg = 'Account approved and activated.';

    } elseif ($action === 'disable') {
        $uid = (int)$_POST['user_id'];
        $pdo->prepare("UPDATE users SET status='disabled' WHERE id=? AND account_type != 'admin'")->execute([$uid]);
        $msg = 'Account disabled.';

    } elseif ($action === 'enable') {
        $uid = (int)$_POST['user_id'];
        $pdo->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$uid]);
        $msg = 'Account re-enabled.';

    } elseif ($action === 'reject') {
        $uid = (int)$_POST['user_id'];
        $pdo->prepare("DELETE FROM users WHERE id=? AND status='pending'")->execute([$uid]);
        $msg = 'Account request rejected and removed.';

    } elseif ($action === 'save_roles') {
        // RBAC: save multi-role assignment for an employee
        $uid       = (int)$_POST['user_id'];
        $newRoles  = $_POST['roles'] ?? [];
        $validRoles = ['instructor', 'dept_head', 'registrar'];
        $newRoles   = array_intersect($newRoles, $validRoles);

        // Check user is employee
        $chk = $pdo->prepare("SELECT account_type FROM users WHERE id=?");
        $chk->execute([$uid]); $chkRow = $chk->fetch();
        if ($chkRow && $chkRow['account_type'] === 'employee') {
            // Delete old roles
            $pdo->prepare("DELETE FROM user_roles WHERE user_id=?")->execute([$uid]);
            // Insert new roles
            $ins = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, assigned_by) SELECT ?, id, ? FROM roles WHERE role_name=?");
            foreach ($newRoles as $r) {
                $ins->execute([$uid, $_SESSION['user_id'], $r]);
            }
            auditLog($_SESSION['user_id'], $_SESSION['username'], 'admin', 'Roles Updated', "User ID $uid roles: " . implode(',', $newRoles), $_SERVER['REMOTE_ADDR']??'');
            $msg = 'Roles updated successfully.';
        } else {
            $error = 'Can only assign roles to employee accounts.';
        }
    }
}

// ─── FETCH USERS ─────────────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
$roleFilter = $_GET['role'] ?? '';
$params = [];
$where  = ['1=1'];

if ($filter === 'pending') { $where[] = "u.status='pending'"; }
if ($search) { $where[] = "(u.full_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }
if ($roleFilter && $roleFilter !== 'all') {
    if (in_array($roleFilter, ['instructor','dept_head','registrar'])) {
        $where[] = "EXISTS (SELECT 1 FROM user_roles ur2 JOIN roles r2 ON ur2.role_id=r2.id WHERE ur2.user_id=u.id AND r2.role_name=?)";
        $params[] = $roleFilter;
    } else {
        $where[] = "u.account_type=?"; $params[] = $roleFilter;
    }
}

$whereSQL = implode(' AND ', $where);
$stmt = $pdo->prepare("SELECT u.*, GROUP_CONCAT(r.role_name ORDER BY r.role_name SEPARATOR ',') as roles
    FROM users u
    LEFT JOIN user_roles ur ON ur.user_id = u.id AND u.account_type='employee'
    LEFT JOIN roles r ON ur.role_id = r.id
    WHERE $whereSQL
    GROUP BY u.id
    ORDER BY u.status='pending' DESC, u.created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

// For role edit modal: pre-fetch all roles
$allRoles = $pdo->query("SELECT * FROM roles")->fetchAll();

$activePage = 'users';
$pageTitle  = 'Users & Roles';
include __DIR__ . '/../includes/head.php';
?>
<body>
<div class="layout">
<?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2>Users &amp; Roles</h2>
      <p>Manage accounts and multi-role assignments (RBAC)</p>
    </div>
    <a href="/evsu_inc_portal/register.php?type=employee" class="btn-sm maroon">+ Add Employee</a>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

  <!-- Filters -->
  <form method="GET" action="" class="content-card" style="padding:0.875rem 1.375rem;">
    <div class="filter-bar">
      <input class="form-input" type="text" name="q" placeholder="Search users…" value="<?= h($search) ?>">
      <select class="form-input" name="role" style="max-width:170px;height:36px;font-size:13px;">
        <option value="all">All roles</option>
        <option value="admin" <?= $roleFilter==='admin'?'selected':'' ?>>Admin</option>
        <option value="instructor" <?= $roleFilter==='instructor'?'selected':'' ?>>Instructor</option>
        <option value="registrar" <?= $roleFilter==='registrar'?'selected':'' ?>>Registrar</option>
        <option value="dept_head" <?= $roleFilter==='dept_head'?'selected':'' ?>>Dept. Head</option>
        <option value="student" <?= $roleFilter==='student'?'selected':'' ?>>Student</option>
      </select>
      <button type="submit" class="btn-sm maroon">Filter</button>
      <a href="/evsu_inc_portal/admin/users.php" class="btn-sm">Clear</a>
    </div>
  </form>

  <div class="content-card">
    <div class="card-body" style="padding:0;">
      <?php if (empty($users)): ?>
        <div class="empty-state"><p>No users found matching your criteria.</p></div>
      <?php else: ?>
      <table class="data-table">
        <thead><tr>
          <th>Name</th><th>Username / ID</th><th>Type</th><th>Roles Assigned</th><th>Status</th><th>Joined</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($users as $u):
          $roles = $u['roles'] ? explode(',', $u['roles']) : [];
          $initials = strtoupper(substr($u['full_name'], 0, 1) . (strpos($u['full_name'], ' ') !== false ? substr($u['full_name'], strpos($u['full_name'],' ')+1, 1) : ''));
        ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="width:30px;height:30px;border-radius:50%;background:var(--gray-200);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--gray-600);flex-shrink:0;"><?= h($initials) ?></div>
              <strong><?= h($u['full_name']) ?></strong>
            </div>
          </td>
          <td style="color:var(--gray-400);font-size:12px;"><?= h($u['username']) ?></td>
          <td><span class="badge <?= $u['account_type']==='admin'?'badge-danger':($u['account_type']==='employee'?'badge-info':'badge-success') ?>"><?= ucfirst($u['account_type']) ?></span></td>
          <td>
            <div class="role-chips">
              <?php if ($u['account_type'] === 'admin'): ?>
                <span class="role-chip rc-admin">Admin</span>
              <?php elseif ($u['account_type'] === 'student'): ?>
                <span class="role-chip rc-student">Student</span>
              <?php else: ?>
                <?php foreach ($roles as $r): ?>
                  <span class="role-chip rc-<?= h($r) ?>"><?= h(str_replace('_', '. ', ucfirst($r))) ?></span>
                <?php endforeach; ?>
                <?php if (empty($roles)): ?><span style="font-size:11.5px;color:var(--gray-400);">No roles</span><?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
          <td>
            <?php
              $sb = ['active'=>'badge-success','pending'=>'badge-gold','disabled'=>'badge-gray'];
              echo '<span class="badge '.($sb[$u['status']]??'badge-gray').'">'.ucfirst($u['status']).'</span>';
            ?>
          </td>
          <td style="color:var(--gray-400);font-size:12px;"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
          <td>
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
              <?php if ($u['status'] === 'pending'): ?>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                  <input type="hidden" name="action" value="approve">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn-sm success">Approve</button>
                </form>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Reject and delete this request?')">
                  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                  <input type="hidden" name="action" value="reject">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn-sm danger">Reject</button>
                </form>
              <?php else: ?>
                <?php if ($u['account_type'] === 'employee'): ?>
                <button class="btn-sm" onclick="openRoleModal(<?= $u['id'] ?>, '<?= h(addslashes($u['full_name'])) ?>', '<?= h($u['roles'] ?? '') ?>')">Edit Roles</button>
                <?php else: ?>
                <a href="/evsu_inc_portal/admin/user_view.php?id=<?= $u['id'] ?>" class="btn-sm">View</a>
                <?php endif; ?>
                <?php if ($u['account_type'] !== 'admin'): ?>
                  <?php if ($u['status'] === 'active'): ?>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Disable this account?')">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="disable">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn-sm danger">Disable</button>
                  </form>
                  <?php else: ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="enable">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn-sm success">Enable</button>
                  </form>
                  <?php endif; ?>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</main>
</div>

<!-- ─── ROLE EDIT MODAL ──────────────────────────────────────────── -->
<div class="modal-backdrop hidden" id="roleModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Edit Roles — <span id="modalUserName"></span></h3>
      <button class="modal-close" onclick="closeRoleModal()">✕</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="action" value="save_roles">
      <input type="hidden" name="user_id" id="modalUserId">
      <div class="modal-body">
        <p style="font-size:13px;color:var(--gray-600);margin-bottom:1rem;">
          Select the roles assigned to this employee. They will be able to switch between their assigned roles after login. <strong>Admin is separate — admin accounts cannot be assigned employee roles.</strong>
        </p>
        <div style="display:flex;flex-direction:column;gap:0.75rem;">
          <?php foreach ($allRoles as $role): ?>
          <label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:0.75rem 1rem;border:1px solid var(--gray-200);border-radius:var(--radius-sm);transition:border-color 0.15s;">
            <input type="checkbox" name="roles[]" value="<?= h($role['role_name']) ?>" class="role-check"
              data-role="<?= h($role['role_name']) ?>"
              style="width:16px;height:16px;accent-color:var(--maroon);cursor:pointer;">
            <div>
              <div style="font-size:13.5px;font-weight:600;color:var(--gray-900);"><?php
                $roleLabels = ['instructor'=>'Instructor','dept_head'=>'Department Head','registrar'=>'Registrar'];
                echo $roleLabels[$role['role_name']] ?? ucfirst($role['role_name']);
              ?></div>
              <div style="font-size:12px;color:var(--gray-400);"><?php
                $roleDescs = ['instructor'=>'Can input final grades and sign INC forms','dept_head'=>'Can approve/reject instructor-submitted grades','registrar'=>'Can verify receipts and post final grades'];
                echo $roleDescs[$role['role_name']] ?? '';
              ?></div>
            </div>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-sm" onclick="closeRoleModal()">Cancel</button>
        <button type="submit" class="btn-primary" style="height:36px;">Save Roles</button>
      </div>
    </form>
  </div>
</div>

<script>
function openRoleModal(uid, name, currentRoles) {
  document.getElementById('modalUserId').value = uid;
  document.getElementById('modalUserName').textContent = name;
  const rolesArr = currentRoles ? currentRoles.split(',') : [];
  document.querySelectorAll('.role-check').forEach(cb => {
    cb.checked = rolesArr.includes(cb.dataset.role);
  });
  document.getElementById('roleModal').classList.remove('hidden');
}
function closeRoleModal() {
  document.getElementById('roleModal').classList.add('hidden');
}
document.getElementById('roleModal').addEventListener('click', function(e) {
  if (e.target === this) closeRoleModal();
});
</script>
</body>
</html>
