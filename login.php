<?php
// login.php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: /index.php'); exit;
}

$type  = ($_GET['type'] ?? 'employee') === 'student' ? 'student' : 'employee';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        $result = attemptLogin($username, $password);
        if ($result['success']) {
            $at = $_SESSION['account_type'];
            if ($at === 'admin') header('Location: /admin/dashboard.php');
            elseif ($at === 'employee') header('Location: /employee/dashboard.php');
            else header('Location: /student/dashboard.php');
            exit;
        } else {
            $error = $result['error'];
        }
    } else {
        $error = 'Please enter your username and password.';
    }
}

$pageTitle = $type === 'student' ? 'Student Login' : 'Employee Login';
include __DIR__ . '/includes/head.php';
?>
<body>
<div class="login-wrap">
  <header class="login-header">
    <div class="logo-emblem">E</div>
    <div class="logo-text">EVSU – Ormoc Campus <span>INC Form Portal</span></div>
  </header>

  <div class="login-body">
    <div style="width:100%;max-width:420px;">
      <a href="/index.php" class="back-link">← Back to home</a>

      <div class="login-card">
        <div class="login-card-header">
          <div class="login-emblem">E</div>
          <h2><?= $type === 'student' ? 'Student Login' : 'Employee Login' ?></h2>
          <p><?= $type === 'student' ? 'Access your INC applications' : 'Admin · Instructor · Dept. Head · Registrar' ?></p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
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
          Don't have an account? <a href="/register.php" style="color:var(--maroon);font-weight:500;">Register here</a>
        </p>
        <?php else: ?>
        <p style="font-size:12px;color:var(--gray-400);text-align:center;margin-top:1.25rem;">
          New employee? <a href="/register.php?type=employee" style="color:var(--maroon);font-weight:500;">Request an account</a>
        </p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</body>
</html>
