<?php
// views/employee/dept_review.php
// PRESENTATION LAYER — Dept. Head: applications at Step 3 waiting for approval
// Receives: $pageTitle, $activePage, $csrf, $apps, $search, $total, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="layout">
<?php $view->partial('layouts/employee_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2>Review &amp; Approve</h2>
      <p>Applications waiting for your review — verify the instructor's grade entry and approve or reject</p>
    </div>
    <span class="badge badge-gold"><?= $total ?> for review</span>
  </div>

  <!-- Search -->
  <form method="GET" action="<?= $view->url('employee/dept_review.php') ?>" class="content-card" style="padding:0.875rem 1.375rem;margin-bottom:1rem;">
    <div class="filter-bar">
      <input class="form-input" type="text" name="q"
             placeholder="Search student name, app code, subject…"
             value="<?= $view->e($search) ?>">
      <button type="submit" class="btn-sm maroon">Search</button>
      <a href="<?= $view->url('employee/dept_review.php') ?>" class="btn-sm">Clear</a>
    </div>
  </form>

  <div class="content-card">
    <div class="card-body" style="padding:0;">
      <?php if (empty($apps)): ?>
        <div class="empty-state">
          <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
          <p>No applications waiting for your review.</p>
          <span style="font-size:12.5px;color:var(--gray-400);">Applications appear here once the instructor has entered and signed the resolved grade.</span>
        </div>
      <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>App. ID</th>
            <th>Student</th>
            <th>Subject</th>
            <th>Units</th>
            <th>Fee</th>
            <th>Instructor Grade</th>
            <th>Instructor</th>
            <th>Filed</th>
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
          <td><?= $a['units'] ?> <?= $a['units'] == 1 ? 'unit' : 'units' ?></td>
          <td>₱<?= number_format($a['processing_fee'], 0) ?></td>
          <td>
            <?php if (!empty($a['instructor_grade'])): ?>
              <strong style="font-size:15px;color:var(--gray-900);"><?= $view->e($a['instructor_grade']) ?></strong>
            <?php else: ?>
              <span style="color:var(--gray-400);font-size:12px;">Pending</span>
            <?php endif; ?>
          </td>
          <td style="font-size:12.5px;"><?= $view->e($a['instructor_name'] ?? '—') ?></td>
          <td style="color:var(--gray-400);font-size:12px;"><?= date('M d, Y', strtotime($a['created_at'])) ?></td>
          <td>
            <a href="<?= $view->url('employee/application_view.php') ?>?id=<?= $a['id'] ?>"
               class="btn-sm maroon">
              Review →
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
      Open each application to verify the instructor's entered grade. If correct, draw your e-signature and click
      <em>Approve</em> — the student will be notified to submit their payment receipt.
      If there is an issue, click <em>Reject</em> and provide a reason.
    </p>
  </div>
  <?php endif; ?>

</main>
</div>
</body>
</html>
