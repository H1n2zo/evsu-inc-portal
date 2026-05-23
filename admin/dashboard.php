<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDB();

// Stats
$totalApps   = $pdo->query("SELECT COUNT(*) FROM inc_applications")->fetchColumn();
$pendingApps = $pdo->query("SELECT COUNT(*) FROM inc_applications WHERE status IN ('in_progress','pending_payment','verification')")->fetchColumn();
$resolved    = $pdo->query("SELECT COUNT(*) FROM inc_applications WHERE status='resolved'")->fetchColumn();
$activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
$pendingUsers= $pdo->query("SELECT COUNT(*) FROM users WHERE status='pending'")->fetchColumn();

// Recent applications
$recent = $pdo->query("
    SELECT a.app_code, u.full_name, a.subject_name, a.units, a.processing_fee,
           a.current_step, a.status, a.created_at
    FROM inc_applications a
    JOIN users u ON a.student_id = u.id
    ORDER BY a.created_at DESC LIMIT 8
")->fetchAll();

$activePage = 'dashboard';
$pageTitle  = 'Dashboard';
include __DIR__ . '/../includes/head.php';
?>
<body>
<div class="layout">
<?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2>Dashboard</h2>
      <p>Academic Year <?= h(getSetting('school_year') ?? '2025–2026') ?>, <?= h(getSetting('active_semester') ?? '2nd') ?> Semester</p>
    </div>
    <span class="badge badge-gold">Admin Access</span>
  </div>

  <?php if ($pendingUsers > 0): ?>
  <div class="alert alert-gold">
    ⚠ <strong><?= $pendingUsers ?> pending account<?= $pendingUsers > 1 ? 's' : '' ?></strong> require your approval. <a href="/evsu_inc_portal/admin/users.php?filter=pending" style="color:inherit;font-weight:600;">Review now →</a>
  </div>
  <?php endif; ?>

  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-label">Total Applications</div>
      <div class="stat-value"><?= $totalApps ?></div>
      <div class="stat-sub">This semester</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Pending Review</div>
      <div class="stat-value" style="color:var(--maroon)"><?= $pendingApps ?></div>
      <div class="stat-sub">Awaiting action</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Resolved</div>
      <div class="stat-value" style="color:var(--success)"><?= $resolved ?></div>
      <div class="stat-sub">Grades posted</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Active Users</div>
      <div class="stat-value"><?= $activeUsers ?></div>
      <div class="stat-sub">All roles</div>
    </div>
  </div>

  <div class="content-card">
    <div class="card-head">
      <h3>Recent Applications</h3>
      <a href="/evsu_inc_portal/admin/applications.php" class="btn-sm">View all</a>
    </div>
    <div class="card-body" style="padding:0">
      <?php if (empty($recent)): ?>
        <div class="empty-state"><p>No applications yet.</p></div>
      <?php else: ?>
      <table class="data-table">
        <thead><tr>
          <th>App. ID</th><th>Student</th><th>Subject</th><th>Fee</th><th>Step</th><th>Status</th><th>Date Filed</th>
        </tr></thead>
        <tbody>
        <?php foreach ($recent as $r): ?>
        <tr>
          <td style="color:var(--gray-400);font-size:12px;"><?= h($r['app_code']) ?></td>
          <td><?= h($r['full_name']) ?></td>
          <td><?= h($r['subject_name']) ?> (<?= $r['units'] ?> units)</td>
          <td>₱<?= number_format($r['processing_fee'], 0) ?></td>
          <td style="font-size:12.5px;">Step <?= $r['current_step'] ?></td>
          <td><?php
            $badgeMap = ['in_progress'=>'badge-info','pending_payment'=>'badge-gold','verification'=>'badge-gold','resolved'=>'badge-success','rejected'=>'badge-danger','draft'=>'badge-gray'];
            $labelMap = ['in_progress'=>'In Progress','pending_payment'=>'Pending Payment','verification'=>'Verification','resolved'=>'Resolved','rejected'=>'Rejected','draft'=>'Draft'];
            $b = $badgeMap[$r['status']] ?? 'badge-gray';
            $l = $labelMap[$r['status']] ?? ucfirst($r['status']);
          ?><span class="badge <?= $b ?>"><?= $l ?></span></td>
          <td style="color:var(--gray-400);font-size:12px;"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
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
