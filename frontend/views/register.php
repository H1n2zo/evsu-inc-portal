<?php
// views/register.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $csrf, $type, $error, $success, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="login-wrap">
  <header class="login-header">
    <div class="logo-emblem">E</div>
    <div class="logo-text">EVSU – Ormoc Campus <span>INC Form Portal</span></div>
  </header>
  <div class="login-body">
    <div style="width:100%;max-width:460px;">
      <a href="<?= $view->url('login.php') . '?type=' . $view->e($type)  ?>" class="back-link">← Back to login</a>
      <div class="login-card">
        <div class="login-card-header">
          <div class="login-emblem">E</div>
          <h2>Create Account</h2>
          <p>Register as a student or employee</p>
        </div>

        <?php if ($success): ?>
          <div class="alert alert-success"><?= $view->e($success) ?></div>
          <a href="<?= $view->url('login.php') . '?type=' . $view->e($type)  ?>" class="btn-primary full">Go to Sign In</a>
        <?php else: ?>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= $view->e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= $view->url('register.php') ?>">
          <input type="hidden" name="csrf_token" value="<?= $view->e($csrf) ?>">
          <input type="hidden" name="account_type" value="<?= $view->e($type) ?>">

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Full Name <span style="color:var(--danger)">*</span></label>
              <input class="form-input" type="text" name="full_name" placeholder="Juan Dela Cruz" required>
            </div>
            <div class="form-group">
              <label class="form-label">Email <span style="color:var(--danger)">*</span></label>
              <input class="form-input" type="email" name="email" placeholder="juan@evsu.edu.ph" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Username <span style="color:var(--danger)">*</span></label>
            <input class="form-input" type="text" name="username"
              placeholder="<?= $type === 'student' ? '2021-00001' : 'j.delacruz' ?>" required>
          </div>

          <?php if ($type === 'student'): ?>
          <div class="form-group">
            <label class="form-label">Student ID</label>
            <input class="form-input" type="text" name="student_id" placeholder="2021-00001">
          </div>
          <?php else: ?>
          <div class="form-group">
            <label class="form-label">Department</label>
            <input class="form-input" type="text" name="department" placeholder="e.g. College of Engineering">
          </div>
          <?php endif; ?>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Password <span style="color:var(--danger)">*</span></label>
              <input class="form-input" type="password" name="password" placeholder="Min. 8 characters" required>
            </div>
            <div class="form-group">
              <label class="form-label">Confirm Password <span style="color:var(--danger)">*</span></label>
              <input class="form-input" type="password" name="confirm_password" placeholder="Repeat password" required>
            </div>
          </div>

          <button type="submit" class="btn-primary full">Create Account</button>

          <?php if ($type === 'employee'): ?>
          <p class="form-hint" style="text-align:center;margin-top:0.875rem;">
            Employee accounts require admin approval before activation.
          </p>
          <?php endif; ?>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</body>
</html>
