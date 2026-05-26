<?php
// views/employee/applications.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $activePage, $csrf, $apps, $page, $pages, $total, $stepLabels, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="layout">
<?php $view->partial('layouts/employee_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div><h2>My Applications</h2><p>INC forms assigned to you as <?= ucfirst(str_replace('_',' ',$activeRole)) ?></p></div>
    <span class="badge badge-info"><?= $total ?> total</span>
  </div>

  <form method="GET" action="<?= $view->url('employee/applications.php') ?>" class="content-card" style="padding:0.875rem 1.375rem;margin-bottom:1rem;">
    <div class="filter-bar">
      <input class="form-input" type="text" name="q" placeholder="Search student, app code…" value="<?= $view->e($search) ?>">
      <select class="form-input" name="status" style="max-width:170px;height:36px;font-size:13px;">
        <option value="">All statuses</option>
        <option value="in_progress" <?= $status==='in_progress'?'selected':'' ?>>In Progress</option>
        <option value="pending_payment" <?= $status==='pending_payment'?'selected':'' ?>>Pending Payment</option>
        <option value="verification" <?= $status==='verification'?'selected':'' ?>>Verification</option>
        <option value="resolved" <?= $status==='resolved'?'selected':'' ?>>Resolved</option>
        <option value="rejected" <?= $status==='rejected'?'selected':'' ?>>Rejected</option>
      </select>
      <button type="submit" class="btn-sm maroon">Filter</button>
      <a href="<?= $view->url('employee/applications.php') ?>" class="btn-sm">Clear</a>
    </div>
  </form>

  <div class="content-card">
    <div class="card-body" style="padding:0;">
      <?php if (empty($apps)): ?>
        <div class="empty-state"><p>No applications found.</p></div>
      <?php else: ?>
      <table class="data-table">
        <thead><tr>
          <th>App. ID</th><th>Student</th><th>Subject</th><th>Units</th><th>Fee</th><th>Step</th><th>Status</th><th>Updated</th><th>Action</th>
        </tr></thead>
        <tbody>
        <?php
        $stepLabels=[1=>'Student Filing',2=>'Instructor Input',3=>'Dept. Head Review',4=>'Payment Upload',5=>'Registrar Verify',6=>'Grade Posting',7=>'Resolved'];
        $badgeMap=['in_progress'=>'badge-info','pending_payment'=>'badge-gold','verification'=>'badge-gold','resolved'=>'badge-success','rejected'=>'badge-danger','draft'=>'badge-gray'];
        $labelMap=['in_progress'=>'In Progress','pending_payment'=>'Pending Payment','verification'=>'Verification','resolved'=>'Resolved','rejected'=>'Rejected','draft'=>'Draft'];
        foreach ($apps as $a): ?>
        <tr>
          <td style="color:var(--gray-400);font-size:12px;"><?= $view->e($a['app_code']) ?></td>
          <td><?= $view->e($a['full_name']) ?></td>
          <td><?= $view->e($a['subject_name']) ?></td>
          <td><?= $a['units'] ?></td>
          <td>₱<?= number_format($a['processing_fee'],0) ?></td>
          <td style="font-size:12px;">Step <?= $a['current_step'] ?> — <?= $stepLabels[$a['current_step']]??'' ?></td>
          <td><span class="badge <?= $badgeMap[$a['status']]??'badge-gray' ?>"><?= $labelMap[$a['status']]??ucfirst($a['status']) ?></span></td>
          <td style="color:var(--gray-400);font-size:12px;"><?= date('M d, Y', strtotime($a['updated_at'])) ?></td>
          <td>
            <?php
            $canAct = false;
            if ($activeRole==='instructor' && $a['current_step']==2 && $a['status']==='in_progress') $canAct=true;
            if ($activeRole==='dept_head'  && $a['current_step']==3 && $a['status']==='in_progress') $canAct=true;
            if ($activeRole==='registrar'  && in_array($a['current_step'],[5,6])) $canAct=true;
            $link = $view->url('employee/application_view.php') . '?id=' . $a['id'];
            ?>
            <a href="<?= $link ?>" class="btn-sm <?= $canAct?'maroon':'' ?>"><?= $canAct?'Act Now':'View' ?></a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
    <?php if ($pages>1): ?>
    <div style="padding:0.875rem 1.375rem;border-top:1px solid var(--gray-100);display:flex;gap:6px;">
      <?php for($p=1;$p<=$pages;$p++): ?>
      <a href="<?= $view->url('employee/applications.php') ?>?q=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&page=<?= $p ?>"
         class="btn-sm <?= $p===$page?'maroon':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</main>
</div>
</body>
</html>
