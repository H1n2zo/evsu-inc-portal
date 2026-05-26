<?php
// views/login.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $csrf, $type, $error, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="login-wrap">
  <header class="login-header">
    <div class="logo-emblem">E</div>
    <div class="logo-text">EVSU – Ormoc Campus <span>INC Form Portal</span></div>
  </header>

  <div class="login-body">
    <div style="width:100%;max-width:420px;">
      <a href="<?= $view->url('index.php') ?>" class="back-link">← Back to home</a>

      <div class="login-card">
        <div class="login-card-header">
          <div class="login-emblem">E</div>
          <h2><?= $type === 'student' ? 'Student Login' : 'Employee Login' ?></h2>
          <p><?= $type === 'student' ? 'Access your INC applications' : 'Admin · Instructor · Dept. Head · Registrar' ?></p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $view->e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= $view->url('login.php') ?>">
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
          <div class="form-group">
            <label class="form-label"><?= $type === 'student' ? 'Student ID / Username' : 'Username' ?></label>
            <input class="form-input" type="text" name="username" placeholder="<?= $type === 'student' ? '2021-00001' : 'your.username' ?>" autocomplete="username" required>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input class="form-input" type="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
          </div>
          <button type="submit" class="btn-primary full" style="margin-top:0.5rem;">Sign In</button>
        </form>

        <?php if ($type === 'student'): ?>
        <p style="font-size:12px;color:var(--gray-400);text-align:center;margin-top:1.25rem;">
          Don't have an account? <a href="<?= $view->url('register.php') ?>" style="color:var(--maroon);font-weight:500;">Register here</a>
        </p>
        <?php else: ?>
        <p style="font-size:12px;color:var(--gray-400);text-align:center;margin-top:1.25rem;">
          New employee? <a href="<?= $view->url('register.php?type=employee') ?>" style="color:var(--maroon);font-weight:500;">Request an account</a>
        </p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</body>
</html>
