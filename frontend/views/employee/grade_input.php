<?php
// views/employee/grade_input.php
// PRESENTATION LAYER — Instructor: applications at Step 2 waiting for grade input
// Receives: $pageTitle, $activePage, $csrf, $apps, $search, $total, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="layout">
<?php $view->partial('layouts/employee_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2>Grade Input</h2>
      <p>INC applications assigned to you awaiting your grade entry and e-signature</p>
    </div>
    <span class="badge badge-info"><?= $total ?> pending</span>
  </div>

  <!-- Search -->
  <form method="GET" action="<?= $view->url('employee/grade_input.php') ?>" class="content-card" style="padding:0.875rem 1.375rem;margin-bottom:1rem;">
    <div class="filter-bar">
      <input class="form-input" type="text" name="q"
             placeholder="Search student name, app code, subject…"
             value="<?= $view->e($search) ?>">
      <button type="submit" class="btn-sm maroon">Search</button>
      <a href="<?= $view->url('employee/grade_input.php') ?>" class="btn-sm">Clear</a>
    </div>
  </form>

  <div class="content-card">
    <div class="card-body" style="padding:0;">
      <?php if (empty($apps)): ?>
        <div class="empty-state">
          <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            <path d="M9 12h6M9 16h4"/>
          </svg>
          <p>No applications waiting for grade input.</p>
          <span style="font-size:12.5px;color:var(--gray-400);">Applications appear here when a student has filed and reached Step 2.</span>
        </div>
      <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>App. ID</th>
            <th>Student</th>
            <th>Subject</th>
            <th>Units</th>
            <th>Processing Fee</th>
            <th>Semester</th>
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
          <td style="font-size:12.5px;"><?= $view->e($a['semester']) ?> Sem, <?= $view->e($a['school_year']) ?></td>
          <td style="color:var(--gray-400);font-size:12px;"><?= date('M d, Y', strtotime($a['created_at'])) ?></td>
          <td>
            <a href="<?= $view->url('employee/application_view.php') ?>?id=<?= $a['id'] ?>"
               class="btn-sm maroon">
              Enter Grade →
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
      <strong style="color:var(--gray-600);">Reminder:</strong>
      Open each application, enter the resolved final grade, then draw your e-signature and click
      <em>Submit Grade &amp; Sign</em>. The form will automatically advance to the Department Head for review.
    </p>
  </div>
  <?php endif; ?>

</main>
</div>
</body>
</html>
