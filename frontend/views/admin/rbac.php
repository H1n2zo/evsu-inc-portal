<?php
// views/admin/rbac.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $activePage, $csrf, $roleCounts, $multiRole, $empRoles, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="layout">
<?php $view->partial('layouts/admin_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div><h2>RBAC Configuration</h2><p>Role-Based Access Control — overview and management</p></div>
    <span class="badge badge-danger">Admin Only</span>
  </div>

  <!-- Role stat cards -->
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="content-card" style="padding:1.25rem;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:0.5rem;">
        <span class="role-chip rc-admin">Admin</span>
      </div>
      <div style="font-size:26px;font-family:'Playfair Display',serif;color:var(--gray-900);">1</div>
      <div style="font-size:12px;color:var(--gray-400);">System administrator. Cannot be assigned employee roles. Always has full access.</div>
    </div>
    <?php
    $roleInfo = [
      'instructor' => ['label'=>'Instructor', 'class'=>'rc-instructor', 'desc'=>'Enter resolved final grades, e-sign forms'],
      'dept_head'  => ['label'=>'Dept. Head',  'class'=>'rc-dept_head',  'desc'=>'Review & approve/reject instructor submissions'],
      'registrar'  => ['label'=>'Registrar',   'class'=>'rc-registrar',  'desc'=>'Verify receipts, post final grades to transcripts'],
    ];
    foreach ($roleInfo as $rkey => $ri):
      $cnt = $roleCounts[$rkey] ?? 0;
    ?>
    <div class="content-card" style="padding:1.25rem;">
      <div style="margin-bottom:0.5rem;"><span class="role-chip <?= $ri['class'] ?>"><?= $ri['label'] ?></span></div>
      <div style="font-size:26px;font-family:'Playfair Display',serif;color:var(--gray-900);"><?= $cnt ?></div>
      <div style="font-size:12px;color:var(--gray-400);"><?= $ri['desc'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Permission Matrix (READ only) -->
  <div class="content-card" style="margin-bottom:1.25rem;">
    <div class="card-head"><h3>Permission Matrix</h3></div>
    <div class="card-body" style="padding:0;">
      <table class="data-table">
        <thead><tr>
          <th>Permission / Action</th>
          <th>Admin</th><th>Instructor</th><th>Dept. Head</th><th>Registrar</th><th>Student</th>
        </tr></thead>
        <tbody>
          <?php
          $matrix = [
            ['Full system administration','✓','','','',''],
            ['View all users & audit logs','✓','','','',''],
            ['RBAC & module configuration','✓','','','',''],
            ['Approve/disable accounts','✓','','','',''],
            ['View all INC applications','✓','','','',''],
            ['Initiate INC application','','','','','✓'],
            ['Upload payment receipt','','','','','✓'],
            ['Enter resolved final grade','','✓','','',''],
            ['E-sign INC form','','✓','✓','✓',''],
            ['Review & approve/reject (Step 3)','','','✓','',''],
            ['Verify O.R. & ledger (Step 5)','','','','✓',''],
            ['Post final grade to transcript','','','','✓',''],
            ['Generate/download INC PDF','','✓','✓','✓','✓'],
          ];
          foreach ($matrix as $row): ?>
          <tr>
            <td style="font-size:12.5px;"><?= $view->e($row[0]) ?></td>
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <td style="text-align:center;">
              <?php if ($row[$i] === '✓'): ?>
                <span style="color:var(--success);font-weight:700;">✓</span>
              <?php else: ?>
                <span style="color:var(--gray-200);">–</span>
              <?php endif; ?>
            </td>
            <?php endfor; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Multi-role users -->
  <?php if (!empty($multiRole)): ?>
  <div class="content-card" style="margin-bottom:1.25rem;">
    <div class="card-head">
      <h3>Multi-Role Employees</h3>
      <span class="badge badge-info"><?= count($multiRole) ?> users</span>
    </div>
    <div class="card-body" style="padding:0;">
      <table class="data-table">
        <thead><tr><th>Name</th><th>Username</th><th>Assigned Roles</th><th>Role Count</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($multiRole as $u): $roles = explode(',', $u['roles']); ?>
        <tr>
          <td><strong><?= $view->e($u['full_name']) ?></strong></td>
          <td style="color:var(--gray-400);font-size:12px;"><?= $view->e($u['username']) ?></td>
          <td>
            <div class="role-chips">
              <?php foreach ($roles as $r): ?>
              <span class="role-chip rc-<?= $view->e($r) ?>"><?= $view->e(str_replace('_', '. ', ucfirst($r))) ?></span>
              <?php endforeach; ?>
            </div>
          </td>
          <td><strong><?= $u['role_count'] ?></strong> roles</td>
          <td><a href="<?= $view->url('admin/users.php') ?>" class="btn-sm">Edit Roles</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- All employee roles -->
  <div class="content-card">
    <div class="card-head">
      <h3>All Employee Role Assignments</h3>
      <a href="<?= $view->url('admin/users.php') ?>" class="btn-sm maroon">Manage Roles</a>
    </div>
    <div class="card-body" style="padding:0;">
      <?php if (empty($empRoles)): ?>
        <div class="empty-state"><p>No employee accounts found.</p></div>
      <?php else: ?>
      <table class="data-table">
        <thead><tr><th>Name</th><th>Username</th><th>Department</th><th>Roles</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($empRoles as $u): $roles = $u['roles'] ? explode(',', $u['roles']) : []; ?>
        <tr>
          <td><?= $view->e($u['full_name']) ?></td>
          <td style="color:var(--gray-400);font-size:12px;"><?= $view->e($u['username']) ?></td>
          <td style="font-size:12px;color:var(--gray-600);"><?= $view->e($u['department'] ?? '—') ?></td>
          <td>
            <div class="role-chips">
              <?php foreach ($roles as $r): ?>
              <span class="role-chip rc-<?= $view->e($r) ?>"><?= $view->e(str_replace('_', '. ', ucfirst($r))) ?></span>
              <?php endforeach; ?>
              <?php if (empty($roles)): ?><span style="font-size:11.5px;color:var(--gray-400);">No roles assigned</span><?php endif; ?>
            </div>
          </td>
          <td><span class="badge <?= $view->statusBadge($u['status']) ?>"><?= ucfirst($u['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</main>
</div>
</body>
</html>
