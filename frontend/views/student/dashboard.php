<?php
// views/student/dashboard.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $activePage, $csrf, $total, $active, $resolved, $rejected, $recent, $needsPayment, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="layout">
<?php $view->partial('layouts/student_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div><h2>My Dashboard</h2><p>Welcome, <?= $view->e($_SESSION['full_name']) ?></p></div>
    <a href="<?= $view->url('student/apply.php') ?>" class="btn-primary" style="height:36px;">+ New Application</a>
  </div>

  <?php foreach ($needsPayment as $np): ?>
  <div class="alert alert-gold">
    ⚠ <strong>Action Required:</strong> Application <strong><?= $view->e($np['app_code']) ?></strong> is waiting for your payment receipt upload.
    <a href="<?= $view->url('student/application_view.php') . '?id=' . $np['id']  ?>" style="color:inherit;font-weight:700;margin-left:8px;">Upload Now →</a>
  </div>
  <?php endforeach; ?>

  <div class="stat-grid">
    <div class="stat-card"><div class="stat-label">Total Applications</div><div class="stat-value"><?= $total ?></div><div class="stat-sub">All time</div></div>
    <div class="stat-card"><div class="stat-label">Active</div><div class="stat-value" style="color:var(--maroon)"><?= $active ?></div><div class="stat-sub">In progress</div></div>
    <div class="stat-card"><div class="stat-label">Resolved</div><div class="stat-value" style="color:var(--success)"><?= $resolved ?></div><div class="stat-sub">Grades posted</div></div>
    <div class="stat-card"><div class="stat-label">Rejected</div><div class="stat-value" style="color:var(--danger)"><?= $rejected ?></div><div class="stat-sub">Needs resubmission</div></div>
  </div>

  <div class="content-card">
    <div class="card-head"><h3>My Applications</h3><a href="<?= $view->url('student/applications.php') ?>" class="btn-sm">View all</a></div>
    <div class="card-body" style="padding:0;">
      <?php if (empty($recent)): ?>
        <div class="empty-state">
          <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          <p>No applications yet. <a href="<?= $view->url('student/apply.php') ?>" style="color:var(--maroon);">File one now →</a></p>
        </div>
      <?php else: ?>
      <table class="data-table">
        <thead><tr><th>App. ID</th><th>Subject</th><th>Fee</th><th>Step</th><th>Status</th><th>Updated</th><th></th></tr></thead>
        <tbody>
        <?php
        $stepLabels=[1=>'Filing',2=>'Instructor',3=>'Dept. Head',4=>'Payment',5=>'Registrar',6=>'Posting',7=>'Resolved'];
        $bm=['in_progress'=>'badge-info','pending_payment'=>'badge-gold','verification'=>'badge-gold','resolved'=>'badge-success','rejected'=>'badge-danger','draft'=>'badge-gray'];
        $lm=['in_progress'=>'In Progress','pending_payment'=>'Pay Now','verification'=>'Verification','resolved'=>'Resolved','rejected'=>'Rejected','draft'=>'Draft'];
        foreach ($recent as $r): ?>
        <tr>
          <td style="color:var(--gray-400);font-size:12px;"><?= $view->e($r['app_code']) ?></td>
          <td><?= $view->e($r['subject_name']) ?></td>
          <td>₱<?= number_format($r['processing_fee'],0) ?></td>
          <td style="font-size:12px;">Step <?= $r['current_step'] ?> — <?= $stepLabels[$r['current_step']]??'' ?></td>
          <td><span class="badge <?= $bm[$r['status']]??'badge-gray' ?>"><?= $lm[$r['status']]??ucfirst($r['status']) ?></span></td>
          <td style="color:var(--gray-400);font-size:12px;"><?= date('M d, Y', strtotime($r['updated_at'])) ?></td>
          <td>
            <?php if ($r['current_step']==4 && $r['status']==='pending_payment'): ?>
            <a href="<?= $view->url('student/application_view.php') . '?id=' . $r['id']  ?>" class="btn-sm maroon">Upload Receipt</a>
            <?php else: ?>
            <a href="<?= $view->url('student/application_view.php') . '?id=' . $r['id']  ?>" class="btn-sm">View</a>
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
