<?php
// views/employee/dashboard.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $activePage, $csrf, $pendingForMe, $totalAll, $resolvedAll, $recent, $activeRole, $roleLabels, $roleBadge, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="layout">
<?php $view->partial('layouts/employee_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2>Dashboard</h2>
      <p>Welcome back, <?= $view->e($_SESSION['full_name']) ?></p>
    </div>
    <span class="badge <?= $roleBadge[$activeRole] ?? 'badge-gray' ?>"><?= $roleLabels[$activeRole] ?? ucfirst($activeRole) ?></span>
  </div>

  <?php if (count($_SESSION['roles'] ?? []) > 1): ?>
  <div class="alert alert-info" style="margin-bottom:1.25rem;">
    You have <strong><?= count($_SESSION['roles']) ?> roles</strong> assigned. Currently acting as <strong><?= $roleLabels[$activeRole] ?></strong>. Use the role switcher in the sidebar to change your active role.
  </div>
  <?php endif; ?>

  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-label">Pending My Action</div>
      <div class="stat-value" style="color:var(--maroon)"><?= $pendingForMe ?></div>
      <div class="stat-sub">Awaiting my review</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Total Applications</div>
      <div class="stat-value"><?= $totalAll ?></div>
      <div class="stat-sub">System-wide</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Resolved</div>
      <div class="stat-value" style="color:var(--success)"><?= $resolvedAll ?></div>
      <div class="stat-sub">Grades posted</div>
    </div>
  </div>

  <div class="content-card">
    <div class="card-head">
      <h3>Recent Applications <?= $activeRole !== 'registrar' ? 'Assigned to Me' : 'Pending Action' ?></h3>
      <a href="<?= $view->url('employee/applications.php') ?>" class="btn-sm">View all</a>
    </div>
    <div class="card-body" style="padding:0;">
      <?php if (empty($recent)): ?>
        <div class="empty-state"><p>No applications assigned to you yet.</p></div>
      <?php else: ?>
      <table class="data-table">
        <thead><tr><th>App. ID</th><th>Student</th><th>Subject</th><th>Fee</th><th>Step</th><th>Status</th><th>Updated</th></tr></thead>
        <tbody>
        <?php
        $badgeMap = ['in_progress'=>'badge-info','pending_payment'=>'badge-gold','verification'=>'badge-gold','resolved'=>'badge-success','rejected'=>'badge-danger','draft'=>'badge-gray'];
        $labelMap = ['in_progress'=>'In Progress','pending_payment'=>'Pending Payment','verification'=>'Verification','resolved'=>'Resolved','rejected'=>'Rejected','draft'=>'Draft'];
        foreach ($recent as $r): ?>
        <tr>
          <td style="color:var(--gray-400);font-size:12px;"><?= $view->e($r['app_code']) ?></td>
          <td><?= $view->e($r['full_name']) ?></td>
          <td><?= $view->e($r['subject_name']) ?></td>
          <td>₱<?= number_format($r['processing_fee'],0) ?></td>
          <td style="font-size:12px;">Step <?= $r['current_step'] ?></td>
          <td><span class="badge <?= $badgeMap[$r['status']]??'badge-gray' ?>"><?= $labelMap[$r['status']]??ucfirst($r['status']) ?></span></td>
          <td style="color:var(--gray-400);font-size:12px;"><?= date('M d, Y', strtotime($r['updated_at'])) ?></td>
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
