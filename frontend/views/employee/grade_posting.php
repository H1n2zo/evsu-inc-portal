<?php
// views/employee/grade_posting.php
// PRESENTATION LAYER — Registrar: applications at Step 6 ready for final grade posting
// Receives: $pageTitle, $activePage, $csrf, $apps, $search, $total, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="layout">
<?php $view->partial('layouts/employee_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2>Grade Posting</h2>
      <p>Post the final grade to the transcript — last step before resolution</p>
    </div>
    <span class="badge badge-info"><?= $total ?> ready</span>
  </div>

  <!-- Search -->
  <form method="GET" action="<?= $view->url('employee/grade_posting.php') ?>" class="content-card" style="padding:0.875rem 1.375rem;margin-bottom:1rem;">
    <div class="filter-bar">
      <input class="form-input" type="text" name="q"
             placeholder="Search student name, app code, subject…"
             value="<?= $view->e($search) ?>">
      <button type="submit" class="btn-sm maroon">Search</button>
      <a href="<?= $view->url('employee/grade_posting.php') ?>" class="btn-sm">Clear</a>
    </div>
  </form>

  <div class="content-card">
    <div class="card-body" style="padding:0;">
      <?php if (empty($apps)): ?>
        <div class="empty-state">
          <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <polyline points="9 11 12 14 22 4"/>
            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
          </svg>
          <p>No applications ready for grade posting.</p>
          <span style="font-size:12.5px;color:var(--gray-400);">Applications appear here after the O.R. has been verified in Step 5.</span>
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
            <th>Resolved Grade</th>
            <th>O.R. No.</th>
            <th>Instructor</th>
            <th>Dept. Head</th>
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
            <strong style="font-size:16px;color:var(--maroon);">
              <?= $view->e($a['instructor_grade'] ?? '—') ?>
            </strong>
          </td>
          <td style="font-family:monospace;font-size:12.5px;"><?= $view->e($a['or_number'] ?? '—') ?></td>
          <td style="font-size:12.5px;"><?= $view->e($a['instructor_name'] ?? '—') ?></td>
          <td style="font-size:12.5px;"><?= $view->e($a['depthead_name'] ?? '—') ?></td>
          <td>
            <a href="<?= $view->url('employee/application_view.php') ?>?id=<?= $a['id'] ?>"
               class="btn-sm maroon">
              Post Grade →
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
      <strong style="color:var(--gray-600);">Final Step:</strong>
      Open each application, confirm the resolved grade shown above, draw your e-signature, and click
      <em>Post Grade &amp; Resolve</em>. The INC form will be marked as <strong>Resolved</strong>
      and the student will be notified.
    </p>
  </div>
  <?php endif; ?>

</main>
</div>
</body>
</html>
