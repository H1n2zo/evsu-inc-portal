<?php
// views/student/apply.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $activePage, $csrf, $error, $instructors, $deptHeads, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="layout">
<?php $view->partial('layouts/student_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div><h2>New INC Application</h2><p>File an incomplete grade completion request</p></div>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= $view->e($error) ?></div><?php endif; ?>

  <div class="content-card" style="max-width:680px;">
    <div class="card-head"><h3>INC Form Details</h3></div>
    <div class="card-body">
      <form method="POST" action="<?= $view->url('student/apply.php') ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <p class="form-section-title">Subject Information</p>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Subject Name <span style="color:var(--danger)">*</span></label>
            <input class="form-input" type="text" name="subject_name" placeholder="e.g. Mathematics 201" required>
          </div>
          <div class="form-group">
            <label class="form-label">Subject Code <span style="color:var(--danger)">*</span></label>
            <input class="form-input" type="text" name="subject_code" placeholder="e.g. MATH 201" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Units <span style="color:var(--danger)">*</span></label>
            <select class="form-input" name="units" id="unitsSelect" onchange="updateFee()">
              <option value="1">1 unit</option>
              <option value="2">2 units</option>
              <option value="3" selected>3 units</option>
              <option value="4">4 units</option>
              <option value="5">5 units</option>
              <option value="6">6 units</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Semester</label>
            <select class="form-input" name="semester">
              <option value="1st">1st Semester</option>
              <option value="2nd" selected>2nd Semester</option>
              <option value="Summer">Summer</option>
            </select>
          </div>
        </div>

        <!-- Auto fee display -->
        <div style="background:var(--gold-pale);border:1px solid #FDE68A;border-radius:var(--radius-sm);padding:1rem 1.25rem;margin-bottom:1.25rem;">
          <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:13.5px;color:var(--gray-600);">Computed Processing Fee</span>
            <span style="font-family:'Playfair Display',serif;font-size:22px;color:var(--maroon);font-weight:700;" id="feeDisplay">₱150</span>
          </div>
          <p style="font-size:12px;color:var(--gray-400);margin-top:3px;">Formula: Units × ₱50.00</p>
        </div>

        <p class="form-section-title">Assign Faculty</p>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Instructor</label>
            <select class="form-input" name="instructor_id" required>
              <option value="">— Select Instructor —</option>
              <?php foreach ($instructors as $i): ?>
              <option value="<?= $i['id'] ?>"><?= $view->e($i['full_name']) ?><?= $i['department']?' ('.$i['department'].')':'' ?></option>
              <?php endforeach; ?>
            </select>
            <p class="form-hint">The instructor who assigned your INC grade.</p>
          </div>
          <div class="form-group">
            <label class="form-label">Department Head</label>
            <select class="form-input" name="dept_head_id" required>
              <option value="">— Select Dept. Head —</option>
              <?php foreach ($deptHeads as $d): ?>
              <option value="<?= $d['id'] ?>"><?= $view->e($d['full_name']) ?><?= $d['department']?' ('.$d['department'].')':'' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="alert alert-info" style="margin-bottom:1rem;">
          After submission, your instructor will be notified to enter the resolved final grade and sign the form.
        </div>

        <div style="display:flex;gap:0.75rem;">
          <button type="submit" class="btn-primary">Submit Application</button>
          <a href="<?= $view->url('student/dashboard.php') ?>" class="btn-sm" style="height:42px;padding:0 1.25rem;">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</main>
</div>
<script>
function updateFee() {
  const units = parseInt(document.getElementById('unitsSelect').value) || 3;
  document.getElementById('feeDisplay').textContent = '₱' + (units * 50).toLocaleString();
}
</script>
</body>
</html>
