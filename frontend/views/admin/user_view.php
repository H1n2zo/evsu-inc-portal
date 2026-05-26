<?php
// views/admin/user_view.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $activePage, $csrf, $user, $userApps, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="layout">
<?php $view->partial('layouts/admin_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div><h2><?= $view->e($user['full_name']) ?></h2><p><?= $view->e($user['username']) ?> · <?= ucfirst($user['account_type']) ?></p></div>
    <a href="<?= $view->url('admin/users.php') ?>" class="btn-sm">← Back to Users</a>
  </div>

  <div style="display:grid;grid-template-columns:320px 1fr;gap:1.25rem;align-items:start;">
    <div class="content-card">
      <div class="card-head"><h3>Account Info</h3></div>
      <div class="card-body" style="font-size:13.5px;">
        <div style="text-align:center;margin-bottom:1.25rem;">
          <div style="width:60px;height:60px;border-radius:50%;background:var(--maroon);color:var(--gold);display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:22px;font-weight:700;margin:0 auto 0.75rem;">
            <?= strtoupper(substr($user['full_name'],0,1)) ?>
          </div>
          <div style="font-weight:600;font-size:15px;"><?= $view->e($user['full_name']) ?></div>
          <div style="color:var(--gray-400);font-size:12.5px;margin-top:2px;"><?= $view->e($user['email']) ?></div>
        </div>
        <table style="width:100%;border-collapse:collapse;">
          <?php foreach([['Username',$user['username']],['Account Type',ucfirst($user['account_type'])],['Status',ucfirst($user['status'])],['Department',$user['department']??'—'],['Student ID',$user['student_id']??'—'],['Joined',date('M d, Y',strtotime($user['created_at']))]] as [$l,$v]): ?>
          <tr><td style="color:var(--gray-400);padding:5px 0;width:45%;"><?= $l ?></td><td style="padding:5px 0;font-weight:500;"><?= $view->e($v) ?></td></tr>
          <?php endforeach; ?>
          <?php if ($user['roles']): ?>
          <tr><td style="color:var(--gray-400);padding:5px 0;">Roles</td><td style="padding:5px 0;">
            <div class="role-chips"><?php foreach(explode(',',$user['roles']) as $r): ?><span class="role-chip rc-<?= $view->e($r) ?>"><?= $view->e(str_replace('_','. ',ucfirst($r))) ?></span><?php endforeach; ?></div>
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
            <td style="color:var(--gray-400);font-size:12px;"><?= $view->e($a['app_code']) ?></td>
            <td><?= $view->e($a['subject_name']) ?></td>
            <td>₱<?= number_format($a['processing_fee'],0) ?></td>
            <td style="font-size:12px;">Step <?= $a['current_step'] ?></td>
            <td><span class="badge <?= $bm[$a['status']]??'badge-gray' ?>"><?= $lm[$a['status']]??ucfirst($a['status']) ?></span></td>
            <td style="color:var(--gray-400);font-size:12px;"><?= date('M d, Y',strtotime($a['created_at'])) ?></td>
            <td><a href="<?= $view->url('admin/application_view.php') . '?id=' . $a['id']  ?>" class="btn-sm">View</a></td>
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
