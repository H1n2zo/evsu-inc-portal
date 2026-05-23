<?php
// admin/user_view.php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$pdo = getDB();
$uid = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT u.*, GROUP_CONCAT(r.role_name SEPARATOR ',') as roles
    FROM users u LEFT JOIN user_roles ur ON ur.user_id=u.id AND u.account_type='employee'
    LEFT JOIN roles r ON ur.role_id=r.id WHERE u.id=? GROUP BY u.id");
$stmt->execute([$uid]); $user = $stmt->fetch();
if (!$user) { header('Location: /admin/users.php'); exit; }

// Applications for this user (if student)
$apps = [];
if ($user['account_type'] === 'student') {
    $s = $pdo->prepare("SELECT * FROM inc_applications WHERE student_id=? ORDER BY created_at DESC");
    $s->execute([$uid]); $apps = $s->fetchAll();
}

$activePage='users'; $pageTitle='User — '.$user['full_name'];
include __DIR__ . '/../includes/head.php';
?>
<body>
<div class="layout">
<?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
<main class="main-content">
  <div class="top-bar">
    <div><h2><?= h($user['full_name']) ?></h2><p><?= h($user['username']) ?> · <?= ucfirst($user['account_type']) ?></p></div>
    <a href="/admin/users.php" class="btn-sm">← Back to Users</a>
  </div>

  <div style="display:grid;grid-template-columns:320px 1fr;gap:1.25rem;align-items:start;">
    <div class="content-card">
      <div class="card-head"><h3>Account Info</h3></div>
      <div class="card-body" style="font-size:13.5px;">
        <div style="text-align:center;margin-bottom:1.25rem;">
          <div style="width:60px;height:60px;border-radius:50%;background:var(--maroon);color:var(--gold);display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:22px;font-weight:700;margin:0 auto 0.75rem;">
            <?= strtoupper(substr($user['full_name'],0,1)) ?>
          </div>
          <div style="font-weight:600;font-size:15px;"><?= h($user['full_name']) ?></div>
          <div style="color:var(--gray-400);font-size:12.5px;margin-top:2px;"><?= h($user['email']) ?></div>
        </div>
        <table style="width:100%;border-collapse:collapse;">
          <?php foreach([['Username',$user['username']],['Account Type',ucfirst($user['account_type'])],['Status',ucfirst($user['status'])],['Department',$user['department']??'—'],['Student ID',$user['student_id']??'—'],['Joined',date('M d, Y',strtotime($user['created_at']))]] as [$l,$v]): ?>
          <tr><td style="color:var(--gray-400);padding:5px 0;width:45%;"><?= $l ?></td><td style="padding:5px 0;font-weight:500;"><?= h($v) ?></td></tr>
          <?php endforeach; ?>
          <?php if ($user['roles']): ?>
          <tr><td style="color:var(--gray-400);padding:5px 0;">Roles</td><td style="padding:5px 0;">
            <div class="role-chips"><?php foreach(explode(',',$user['roles']) as $r): ?><span class="role-chip rc-<?= h($r) ?>"><?= h(str_replace('_','. ',ucfirst($r))) ?></span><?php endforeach; ?></div>
          </td></tr>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <?php if ($user['account_type']==='student'): ?>
    <div class="content-card">
      <div class="card-head"><h3>Applications (<?= count($apps) ?>)</h3></div>
      <div class="card-body" style="padding:0;">
        <?php if (empty($apps)): ?><div class="empty-state"><p>No applications filed yet.</p></div>
        <?php else: ?>
        <table class="data-table">
          <thead><tr><th>App. ID</th><th>Subject</th><th>Fee</th><th>Step</th><th>Status</th><th>Filed</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($apps as $a):
            $bm=['in_progress'=>'badge-info','pending_payment'=>'badge-gold','verification'=>'badge-gold','resolved'=>'badge-success','rejected'=>'badge-danger'];
            $lm=['in_progress'=>'In Progress','pending_payment'=>'Pending Payment','verification'=>'Verification','resolved'=>'Resolved','rejected'=>'Rejected'];
          ?>
          <tr>
            <td style="color:var(--gray-400);font-size:12px;"><?= h($a['app_code']) ?></td>
            <td><?= h($a['subject_name']) ?></td>
            <td>₱<?= number_format($a['processing_fee'],0) ?></td>
            <td style="font-size:12px;">Step <?= $a['current_step'] ?></td>
            <td><span class="badge <?= $bm[$a['status']]??'badge-gray' ?>"><?= $lm[$a['status']]??ucfirst($a['status']) ?></span></td>
            <td style="color:var(--gray-400);font-size:12px;"><?= date('M d, Y',strtotime($a['created_at'])) ?></td>
            <td><a href="/admin/application_view.php?id=<?= $a['id'] ?>" class="btn-sm">View</a></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</main>
</div>
</body>
</html>
