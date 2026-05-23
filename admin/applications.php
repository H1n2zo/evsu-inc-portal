<?php
// admin/applications.php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo    = getDB();
$status = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$page   = max(1,(int)($_GET['page']??1));
$perPage = 25; $offset = ($page-1)*$perPage;

$where = ['1=1']; $params = [];
if ($status) { $where[] = "a.status=?"; $params[] = $status; }
if ($search) {
    $where[] = "(u.full_name LIKE ? OR a.app_code LIKE ? OR a.subject_name LIKE ?)";
    $params = array_merge($params,["%$search%","%$search%","%$search%"]);
}
$whereSQL = implode(' AND ', $where);

$total = $pdo->prepare("SELECT COUNT(*) FROM inc_applications a JOIN users u ON a.student_id=u.id WHERE $whereSQL");
$total->execute($params); $total=(int)$total->fetchColumn();
$pages = ceil($total/$perPage);

$stmt = $pdo->prepare("
    SELECT a.*, u.full_name, u.username
    FROM inc_applications a JOIN users u ON a.student_id=u.id
    WHERE $whereSQL ORDER BY a.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$apps = $stmt->fetchAll();

$statusCounts = $pdo->query("SELECT status, COUNT(*) as cnt FROM inc_applications GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

$activePage = 'applications';
$pageTitle  = 'All Applications';
include __DIR__ . '/../includes/head.php';
?>
<body>
<div class="layout">
<?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
<main class="main-content">
  <div class="top-bar">
    <div><h2>All Applications</h2><p>Track every INC completion request in the system</p></div>
    <span class="badge badge-info"><?= number_format($total) ?> total</span>
  </div>

  <!-- Status tab filters -->
  <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:1rem;">
    <?php
    $statuses = [''=> 'All', 'in_progress'=>'In Progress','pending_payment'=>'Pending Payment','verification'=>'Verification','resolved'=>'Resolved','rejected'=>'Rejected'];
    $badgeMap = [''=> 'badge-gray','in_progress'=>'badge-info','pending_payment'=>'badge-gold','verification'=>'badge-gold','resolved'=>'badge-success','rejected'=>'badge-danger'];
    foreach ($statuses as $sv => $sl):
      $cnt = $sv ? ($statusCounts[$sv] ?? 0) : $total;
    ?>
    <a href="?status=<?= urlencode($sv) ?>&q=<?= urlencode($search) ?>"
       class="btn-sm <?= $status===$sv?'maroon':'' ?>"><?= $sl ?> <span class="badge <?= $badgeMap[$sv] ?>" style="margin-left:4px;"><?= $cnt ?></span></a>
    <?php endforeach; ?>
  </div>

  <form method="GET" class="content-card" style="padding:0.875rem 1.375rem;margin-bottom:1rem;">
    <input type="hidden" name="status" value="<?= h($status) ?>">
    <div class="filter-bar">
      <input class="form-input" type="text" name="q" placeholder="Search student, app code, subject…" value="<?= h($search) ?>">
      <button type="submit" class="btn-sm maroon">Search</button>
      <a href="/admin/applications.php" class="btn-sm">Clear</a>
    </div>
  </form>

  <div class="content-card">
    <div class="card-body" style="padding:0;">
      <?php if (empty($apps)): ?>
        <div class="empty-state"><p>No applications found.</p></div>
      <?php else: ?>
      <table class="data-table">
        <thead><tr>
          <th>App. ID</th><th>Student</th><th>Subject</th><th>Fee</th><th>Current Step</th><th>Status</th><th>Filed</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php
        $stepLabels = [1=>'Student Filing',2=>'Instructor Input',3=>'Dept. Head Review',4=>'Payment Upload',5=>'Registrar Verify',6=>'Grade Posting',7=>'Resolved'];
        $badgeMap2 = ['in_progress'=>'badge-info','pending_payment'=>'badge-gold','verification'=>'badge-gold','resolved'=>'badge-success','rejected'=>'badge-danger','draft'=>'badge-gray'];
        $labelMap2 = ['in_progress'=>'In Progress','pending_payment'=>'Pending Payment','verification'=>'Verification','resolved'=>'Resolved','rejected'=>'Rejected','draft'=>'Draft'];
        foreach ($apps as $a):
        ?>
        <tr>
          <td style="color:var(--gray-400);font-size:12px;"><?= h($a['app_code']) ?></td>
          <td><?= h($a['full_name']) ?></td>
          <td style="font-size:12.5px;"><?= h($a['subject_name']) ?> (<?= $a['units'] ?> units)</td>
          <td>₱<?= number_format($a['processing_fee'],0) ?></td>
          <td style="font-size:12px;">Step <?= $a['current_step'] ?> — <?= $stepLabels[$a['current_step']]??'' ?></td>
          <td><span class="badge <?= $badgeMap2[$a['status']]??'badge-gray' ?>"><?= $labelMap2[$a['status']]??ucfirst($a['status']) ?></span></td>
          <td style="color:var(--gray-400);font-size:12px;"><?= date('M d, Y', strtotime($a['created_at'])) ?></td>
          <td><a href="/admin/application_view.php?id=<?= $a['id'] ?>" class="btn-sm">View</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <?php if ($pages > 1): ?>
    <div style="padding:0.875rem 1.375rem;border-top:1px solid var(--gray-100);display:flex;gap:6px;">
      <?php for ($p=1;$p<=$pages;$p++): ?>
      <a href="?status=<?= urlencode($status) ?>&q=<?= urlencode($search) ?>&page=<?= $p ?>"
         class="btn-sm <?= $p===$page?'maroon':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</main>
</div>
</body>
</html>
