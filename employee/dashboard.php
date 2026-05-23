<?php
// employee/dashboard.php
require_once __DIR__ . '/../includes/auth.php';
requireEmployee();

$pdo        = getDB();
$uid        = $_SESSION['user_id'];
$activeRole = $_SESSION['active_role'];

// Stats depend on role
$pendingForMe = 0;
$resolvedAll  = (int)$pdo->query("SELECT COUNT(*) FROM inc_applications WHERE status='resolved'")->fetchColumn();
$totalAll     = (int)$pdo->query("SELECT COUNT(*) FROM inc_applications")->fetchColumn();

if ($activeRole === 'instructor') {
    $pendingForMe = (int)$pdo->prepare("SELECT COUNT(*) FROM inc_applications WHERE instructor_id=? AND current_step=2 AND status='in_progress'")->execute([$uid]) ? $pdo->prepare("SELECT COUNT(*) FROM inc_applications WHERE instructor_id=? AND current_step=2 AND status='in_progress'")->execute([$uid]) : 0;
    // Re-query properly
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inc_applications WHERE instructor_id=? AND current_step=2");
    $stmt->execute([$uid]); $pendingForMe = (int)$stmt->fetchColumn();
} elseif ($activeRole === 'dept_head') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inc_applications WHERE dept_head_id=? AND current_step=3");
    $stmt->execute([$uid]); $pendingForMe = (int)$stmt->fetchColumn();
} elseif ($activeRole === 'registrar') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inc_applications WHERE current_step IN (5,6)");
    $stmt->execute(); $pendingForMe = (int)$stmt->fetchColumn();
}

// Recent apps for this employee
$recentSQL = "SELECT a.*, u.full_name FROM inc_applications a JOIN users u ON a.student_id=u.id ";
$params = [];
if ($activeRole === 'instructor') {
    $recentSQL .= "WHERE a.instructor_id=? ORDER BY a.updated_at DESC LIMIT 6";
    $params = [$uid];
} elseif ($activeRole === 'dept_head') {
    $recentSQL .= "WHERE a.dept_head_id=? ORDER BY a.updated_at DESC LIMIT 6";
    $params = [$uid];
} else {
    $recentSQL .= "WHERE a.current_step IN (5,6) OR a.registrar_id=? ORDER BY a.updated_at DESC LIMIT 6";
    $params = [$uid];
}
$stmt = $pdo->prepare($recentSQL); $stmt->execute($params);
$recent = $stmt->fetchAll();

$roleLabels = ['instructor'=>'Instructor','dept_head'=>'Department Head','registrar'=>'Registrar'];
$roleBadge  = ['instructor'=>'badge-info','dept_head'=>'badge-gold','registrar'=>'badge-success'];
$activePage = 'dashboard';
$pageTitle  = 'Dashboard';
include __DIR__ . '/../includes/head.php';
?>
<body>
<div class="layout">
<?php include __DIR__ . '/../includes/employee_sidebar.php'; ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2>Dashboard</h2>
      <p>Welcome back, <?= h($_SESSION['full_name']) ?></p>
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
      <a href="/employee/applications.php" class="btn-sm">View all</a>
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
          <td style="color:var(--gray-400);font-size:12px;"><?= h($r['app_code']) ?></td>
          <td><?= h($r['full_name']) ?></td>
          <td><?= h($r['subject_name']) ?></td>
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
