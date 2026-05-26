<?php
// views/student/applications.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $activePage, $csrf, $apps, $stepLabels, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="layout">
<?php $view->partial('layouts/student_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div><h2>My Applications</h2><p>All your INC completion requests</p></div>
    <a href="<?= $view->url('student/apply.php') ?>" class="btn-primary" style="height:36px;">+ New Application</a>
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
          <p>No applications found. <a href="<?= $view->url('student/apply.php') ?>" style="color:var(--maroon);">File your first application →</a></p>
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
          <td style="color:var(--gray-400);font-size:12px;"><?= $view->e($a['app_code']) ?></td>
          <td><?= $view->e($a['subject_name']) ?></td>
          <td style="font-size:12px;color:var(--gray-400);"><?= $view->e($a['subject_code']) ?></td>
          <td><?= $a['units'] ?></td>
          <td>₱<?= number_format($a['processing_fee'],0) ?></td>
          <td style="font-size:12px;">Step <?= $a['current_step'] ?> — <?= $sl[$a['current_step']]??'' ?></td>
          <td><span class="badge <?= $bm[$a['status']]??'badge-gray' ?>"><?= $lm[$a['status']]??ucfirst($a['status']) ?></span></td>
          <td style="color:var(--gray-400);font-size:12px;"><?= date('M d, Y', strtotime($a['created_at'])) ?></td>
          <td>
            <?php if ($a['current_step']==4 && $a['status']==='pending_payment'): ?>
            <a href="<?= $view->url('student/application_view.php') . '?id=' . $a['id']  ?>" class="btn-sm maroon">Upload Receipt</a>
            <?php else: ?>
            <a href="<?= $view->url('student/application_view.php') . '?id=' . $a['id']  ?>" class="btn-sm">View</a>
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
