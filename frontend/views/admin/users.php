<?php
// views/admin/users.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $activePage, $csrf, $msg, $error, $users, $allRoles,
//           $search, $roleFilter, $filterStatus, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="layout">
<?php $view->partial('layouts/admin_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2>Users &amp; Roles</h2>
      <p>Manage accounts and multi-role assignments (RBAC)</p>
    </div>
    <a href="<?= $view->url('register.php?type=employee') ?>" class="btn-sm maroon">+ Add Employee</a>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= $view->e($msg) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= $view->e($error) ?></div><?php endif; ?>

  <!-- Filter Bar -->
  <form method="GET" action="<?= $view->url('admin/users.php') ?>" class="content-card" style="padding:0.875rem 1.375rem;">
    <div class="filter-bar">
      <input class="form-input" type="text" name="q" placeholder="Search users…" value="<?= $view->e($search) ?>">
      <select class="form-input" name="role" style="max-width:170px;height:36px;font-size:13px;">
        <option value="all">All roles</option>
        <?php foreach (['admin'=>'Admin','instructor'=>'Instructor','registrar'=>'Registrar','dept_head'=>'Dept. Head','student'=>'Student'] as $val => $label): ?>
        <option value="<?= $val ?>" <?= $roleFilter === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-sm maroon">Filter</button>
      <a href="<?= $view->url('admin/users.php') ?>" class="btn-sm">Clear</a>
    </div>
  </form>

  <!-- Users Table -->
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
          $roles    = $u['roles'] ? explode(',', $u['roles']) : [];
          $pos      = strpos($u['full_name'], ' ');
          $initials = strtoupper(substr($u['full_name'], 0, 1) . ($pos !== false ? substr($u['full_name'], $pos + 1, 1) : ''));
        ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="width:30px;height:30px;border-radius:50%;background:var(--gray-200);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--gray-600);flex-shrink:0;"><?= $view->e($initials) ?></div>
              <strong><?= $view->e($u['full_name']) ?></strong>
            </div>
          </td>
          <td style="color:var(--gray-400);font-size:12px;"><?= $view->e($u['username']) ?></td>
          <td>
            <span class="badge <?= $u['account_type']==='admin' ? 'badge-danger' : ($u['account_type']==='employee' ? 'badge-info' : 'badge-success') ?>">
              <?= ucfirst($u['account_type']) ?>
            </span>
          </td>
          <td>
            <div class="role-chips">
              <?php if ($u['account_type'] === 'admin'): ?>
                <span class="role-chip rc-admin">Admin</span>
              <?php elseif ($u['account_type'] === 'student'): ?>
                <span class="role-chip rc-student">Student</span>
              <?php else: ?>
                <?php foreach ($roles as $r): ?>
                  <span class="role-chip rc-<?= $view->e($r) ?>"><?= $view->e(str_replace('_', '. ', ucfirst($r))) ?></span>
                <?php endforeach; ?>
                <?php if (empty($roles)): ?><span style="font-size:11.5px;color:var(--gray-400);">No roles</span><?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
          <td><span class="badge <?= $view->statusBadge($u['status']) ?>"><?= ucfirst($u['status']) ?></span></td>
          <td style="color:var(--gray-400);font-size:12px;"><?= $view->date($u['created_at']) ?></td>
          <td>
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
              <?php if ($u['status'] === 'pending'): ?>
                <!-- CRUD: Approve (UPDATE status → active) -->
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= $view->e($csrf) ?>">
                  <input type="hidden" name="action" value="approve">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn-sm success">Approve</button>
                </form>
                <!-- CRUD: Reject (DELETE pending user) -->
                <form method="POST" style="display:inline;" onsubmit="return confirm('Reject and delete this request?')">
                  <input type="hidden" name="csrf_token" value="<?= $view->e($csrf) ?>">
                  <input type="hidden" name="action" value="reject">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn-sm danger">Reject</button>
                </form>
              <?php else: ?>
                <?php if ($u['account_type'] === 'employee'): ?>
                <!-- CRUD: Edit Roles (UPDATE roles) -->
                <button class="btn-sm" onclick="openRoleModal(<?= $u['id'] ?>, '<?= $view->e(addslashes($u['full_name'])) ?>', '<?= $view->e($u['roles'] ?? '') ?>')">Edit Roles</button>
                <?php else: ?>
                <!-- CRUD: Read single user -->
                <a href="<?= $view->url('admin/user_view.php') . '?id=' . $u['id']  ?>" class="btn-sm">View</a>
                <?php endif; ?>
                <?php if ($u['account_type'] !== 'admin'): ?>
                  <?php if ($u['status'] === 'active'): ?>
                  <!-- CRUD: Disable (UPDATE status) -->
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Disable this account?')">
                    <input type="hidden" name="csrf_token" value="<?= $view->e($csrf) ?>">
                    <input type="hidden" name="action" value="disable">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn-sm danger">Disable</button>
                  </form>
                  <?php else: ?>
                  <!-- CRUD: Re-enable (UPDATE status) -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= $view->e($csrf) ?>">
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

<!-- Role Assignment Modal -->
<div class="modal-backdrop hidden" id="roleModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Assign Roles — <span id="modalUserName"></span></h3>
      <button class="modal-close" onclick="document.getElementById('roleModal').classList.add('hidden')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $view->e($csrf) ?>">
      <input type="hidden" name="action" value="save_roles">
      <input type="hidden" name="user_id" id="modalUserId">
      <div class="modal-body">
        <p style="font-size:13px;color:var(--gray-400);margin-bottom:1rem;">
          Select one or more roles for this employee account.
        </p>
        <?php foreach ($allRoles as $role): ?>
        <label style="display:flex;align-items:center;gap:10px;padding:0.5rem 0;cursor:pointer;">
          <input type="checkbox" name="roles[]" value="<?= $view->e($role['role_name']) ?>"
                 class="role-check" data-role="<?= $view->e($role['role_name']) ?>">
          <span class="role-chip rc-<?= $view->e($role['role_name']) ?>"><?= $view->e(ucfirst(str_replace('_', ' ', $role['role_name']))) ?></span>
        </label>
        <?php endforeach; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-sm" onclick="document.getElementById('roleModal').classList.add('hidden')">Cancel</button>
        <button type="submit" class="btn-sm maroon">Save Roles</button>
      </div>
    </form>
  </div>
</div>

<script>
function openRoleModal(userId, name, currentRoles) {
  document.getElementById('modalUserId').value = userId;
  document.getElementById('modalUserName').textContent = name;
  const current = currentRoles ? currentRoles.split(',') : [];
  document.querySelectorAll('.role-check').forEach(cb => {
    cb.checked = current.includes(cb.dataset.role);
  });
  document.getElementById('roleModal').classList.remove('hidden');
}
</script>
</body>
</html>
