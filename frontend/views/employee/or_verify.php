<?php
// views/employee/or_verify.php
// PRESENTATION LAYER — Registrar: applications at Step 5 waiting for O.R. verification
// Receives: $pageTitle, $activePage, $csrf, $apps, $search, $total, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="layout">
<?php $view->partial('layouts/employee_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2>O.R. Verification</h2>
      <p>Verify payment receipts against the official ledger before grade posting</p>
    </div>
    <span class="badge badge-gold"><?= $total ?> pending</span>
  </div>

  <!-- Search -->
  <form method="GET" action="<?= $view->url('employee/or_verify.php') ?>" class="content-card" style="padding:0.875rem 1.375rem;margin-bottom:1rem;">
    <div class="filter-bar">
      <input class="form-input" type="text" name="q"
             placeholder="Search student name, app code, O.R. number…"
             value="<?= $view->e($search) ?>">
      <button type="submit" class="btn-sm maroon">Search</button>
      <a href="<?= $view->url('employee/or_verify.php') ?>" class="btn-sm">Clear</a>
    </div>
  </form>

  <div class="content-card">
    <div class="card-body" style="padding:0;">
      <?php if (empty($apps)): ?>
        <div class="empty-state">
          <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
          <p>No receipts waiting for verification.</p>
          <span style="font-size:12.5px;color:var(--gray-400);">Applications appear here after the student uploads their payment receipt.</span>
        </div>
      <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>App. ID</th>
            <th>Student</th>
            <th>Subject</th>
            <th>Fee</th>
            <th>O.R. Number</th>
            <th>Receipt</th>
            <th>Dept. Head</th>
            <th>Grade</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($apps as $a): ?>
        <tr>
          <td style="color:var(--gray-400);font-size:12px;font-family:monospace;"><?= $view->e($a['app_code']) ?></td>
          <td>
            <div style="font-weight:500;"><?= $view->e($a['full_name']) ?></div>
            <?php if (!empty($a['stu_id'])): ?>
            <div style="font-size:11.5px;color:var(--gray-400);"><?= $view->e($a['stu_id']) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <div><?= $view->e($a['subject_name']) ?></div>
            <div style="font-size:11.5px;color:var(--gray-400);"><?= $view->e($a['subject_code']) ?></div>
          </td>
          <td>₱<?= number_format($a['processing_fee'], 0) ?></td>
          <td style="font-weight:500;font-family:monospace;"><?= $view->e($a['or_number'] ?? '—') ?></td>
          <td>
            <?php if (!empty($a['receipt_filename'])): ?>
              <a href="<?= $view->asset('uploads/') . $view->e($a['receipt_filename']) ?>"
                 target="_blank" class="btn-sm" style="padding:3px 10px;font-size:11.5px;">
                View
              </a>
            <?php else: ?>
              <span style="color:var(--danger);font-size:12px;">Not uploaded</span>
            <?php endif; ?>
          </td>
          <td style="font-size:12.5px;"><?= $view->e($a['depthead_name'] ?? '—') ?></td>
          <td>
            <?php if (!empty($a['instructor_grade'])): ?>
              <strong><?= $view->e($a['instructor_grade']) ?></strong>
            <?php else: ?>
              <span style="color:var(--gray-400);">—</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="<?= $view->url('employee/application_view.php') ?>?id=<?= $a['id'] ?>"
               class="btn-sm maroon">
              Verify →
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($apps)): ?>
  <div class="content-card" style="padding:1.25rem;margin-top:0;">
    <p style="font-size:12.5px;color:var(--gray-400);line-height:1.7;margin:0;">
      <strong style="color:var(--gray-600);">Instructions:</strong>
      Open each application to view the uploaded receipt and compare the O.R. number against the official
      ledger. Click <em>O.R. Verified</em> once confirmed to advance to grade posting, or <em>Reject</em>
      with a reason to return the application.
    </p>
  </div>
  <?php endif; ?>

</main>
</div>
</body>
</html>
