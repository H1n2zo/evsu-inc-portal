<?php
// student/applications.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
if ($_SESSION['account_type'] !== 'student') { header('Location: /index.php'); exit; }

$pdo = getDB();
$uid = $_SESSION['user_id'];
$status = $_GET['status'] ?? '';

$where = ['a.student_id=?']; $params = [$uid];
if ($status) { $where[] = "a.status=?"; $params[] = $status; }
$whereSQL = implode(' AND ', $where);

$stmt = $pdo->prepare("SELECT * FROM inc_applications a WHERE $whereSQL ORDER BY a.updated_at DESC");
$stmt->execute($params);
$apps = $stmt->fetchAll();

$activePage = 'applications'; $pageTitle = 'My Applications';
include __DIR__ . '/../includes/head.php';
?>
<body>
<div class="layout">
<?php include __DIR__ . '/../includes/student_sidebar.php'; ?>
<main class="main-content">
  <div class="top-bar">
    <div><h2>My Applications</h2><p>All your INC completion requests</p></div>
    <a href="/student/apply.php" class="btn-primary" style="height:36px;">+ New Application</a>
  </div>

  <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:1rem;">
    <?php foreach([''=> 'All','in_progress'=>'In Progress','pending_payment'=>'Pay Now','verification'=>'Verification','resolved'=>'Resolved','rejected'=>'Rejected'] as $sv=>$sl): ?>
    <a href="?status=<?= urlencode($sv) ?>" class="btn-sm <?= $status===$sv?'maroon':'' ?>"><?= $sl ?></a>
    <?php endforeach; ?>
  </div>

  <div class="content-card">
    <div class="card-body" style="padding:0;">
      <?php if (empty($apps)): ?>
        <div class="empty-state">
          <p>No applications found. <a href="/student/apply.php" style="color:var(--maroon);">File your first application →</a></p>
        </div>
      <?php else: ?>
      <table class="data-table">
        <thead><tr><th>App. ID</th><th>Subject</th><th>Code</th><th>Units</th><th>Fee</th><th>Step</th><th>Status</th><th>Filed</th><th></th></tr></thead>
        <tbody>
        <?php
        $sl=[1=>'Filing',2=>'Instructor',3=>'Dept. Head',4=>'Payment',5=>'Registrar',6=>'Posting',7=>'Resolved'];
        $bm=['in_progress'=>'badge-info','pending_payment'=>'badge-gold','verification'=>'badge-gold','resolved'=>'badge-success','rejected'=>'badge-danger','draft'=>'badge-gray'];
        $lm=['in_progress'=>'In Progress','pending_payment'=>'Pay Now','verification'=>'Verification','resolved'=>'Resolved','rejected'=>'Rejected','draft'=>'Draft'];
        foreach ($apps as $a): ?>
        <tr>
          <td style="color:var(--gray-400);font-size:12px;"><?= h($a['app_code']) ?></td>
          <td><?= h($a['subject_name']) ?></td>
          <td style="font-size:12px;color:var(--gray-400);"><?= h($a['subject_code']) ?></td>
          <td><?= $a['units'] ?></td>
          <td>₱<?= number_format($a['processing_fee'],0) ?></td>
          <td style="font-size:12px;">Step <?= $a['current_step'] ?> — <?= $sl[$a['current_step']]??'' ?></td>
          <td><span class="badge <?= $bm[$a['status']]??'badge-gray' ?>"><?= $lm[$a['status']]??ucfirst($a['status']) ?></span></td>
          <td style="color:var(--gray-400);font-size:12px;"><?= date('M d, Y', strtotime($a['created_at'])) ?></td>
          <td>
            <?php if ($a['current_step']==4 && $a['status']==='pending_payment'): ?>
            <a href="/student/application_view.php?id=<?= $a['id'] ?>" class="btn-sm maroon">Upload Receipt</a>
            <?php else: ?>
            <a href="/student/application_view.php?id=<?= $a['id'] ?>" class="btn-sm">View</a>
            <?php endif; ?>
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
</body>
</html>
