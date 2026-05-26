<?php
// views/admin/dashboard.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $activePage, $stats, $recentApps, $schoolYear, $activeSem, $view, $csrf
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="layout">
<?php $view->partial('layouts/admin_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2>Dashboard</h2>
      <p>Academic Year <?= $view->e($schoolYear) ?>, <?= $view->e($activeSem) ?> Semester</p>
    </div>
    <span class="badge badge-gold">Admin Access</span>
  </div>

  <?php if ($stats['pending_users'] > 0): ?>
  <div class="alert alert-gold">
    ⚠ <strong><?= $stats['pending_users'] ?> pending account<?= $stats['pending_users'] > 1 ? 's' : '' ?></strong>
    require your approval.
    <a href="<?= $view->url('admin/users.php?filter=pending') ?>" style="color:inherit;font-weight:600;">Review now →</a>
  </div>
  <?php endif; ?>

  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-label">Total Applications</div>
      <div class="stat-value"><?= $stats['total'] ?></div>
      <div class="stat-sub">This semester</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Pending Review</div>
      <div class="stat-value" style="color:var(--maroon)"><?= $stats['pending_apps'] ?></div>
      <div class="stat-sub">Awaiting action</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Resolved</div>
      <div class="stat-value" style="color:var(--success)"><?= $stats['resolved'] ?></div>
      <div class="stat-sub">Grades posted</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Active Users</div>
      <div class="stat-value"><?= $stats['active_users'] ?></div>
      <div class="stat-sub">All roles</div>
    </div>
  </div>

  <div class="content-card">
    <div class="card-head">
      <h3>Recent Applications</h3>
      <a href="<?= $view->url('admin/applications.php') ?>" class="btn-sm">View all</a>
    </div>
    <div class="card-body" style="padding:0">
      <?php if (empty($recentApps)): ?>
        <div class="empty-state"><p>No applications yet.</p></div>
      <?php else: ?>
      <table class="data-table">
        <thead><tr>
          <th>App. ID</th><th>Student</th><th>Subject</th><th>Fee</th><th>Step</th><th>Status</th><th>Date Filed</th>
        </tr></thead>
        <tbody>
        <?php foreach ($recentApps as $r): ?>
        <tr>
          <td style="color:var(--gray-400);font-size:12px;"><?= $view->e($r['app_code']) ?></td>
          <td><?= $view->e($r['full_name']) ?></td>
          <td><?= $view->e($r['subject_name']) ?> (<?= $r['units'] ?> units)</td>
          <td>₱<?= number_format($r['processing_fee'] ?? 0, 0) ?></td>
          <td style="font-size:12.5px;">Step <?= $r['current_step'] ?></td>
          <td><span class="badge <?= $view->statusBadge($r['status']) ?>"><?= $view->statusLabel($r['status']) ?></span></td>
          <td style="color:var(--gray-400);font-size:12px;"><?= $view->date($r['created_at']) ?></td>
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
