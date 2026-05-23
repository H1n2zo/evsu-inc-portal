<?php
// register.php
require_once __DIR__ . '/includes/auth.php';
if (!empty($_SESSION['user_id'])) { header('Location: /index.php'); exit; }

$type = ($_GET['type'] ?? 'student') === 'employee' ? 'employee' : 'student';
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $pdo       = getDB();
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $acct_type = $_POST['account_type'] === 'employee' ? 'employee' : 'student';
    $student_id= trim($_POST['student_id'] ?? '');
    $dept      = trim($_POST['department'] ?? '');

    if (!$full_name || !$email || !$username || !$password) {
        $error = 'All required fields must be filled.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check unique
        $chk = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
        $chk->execute([$username, $email]);
        if ($chk->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $status = $acct_type === 'admin' ? 'active' : 'pending'; // employees need admin approval
            if ($acct_type === 'student') $status = 'active'; // students auto-active
            $ins = $pdo->prepare("INSERT INTO users (full_name, username, email, password_hash, account_type, status, student_id, department) VALUES (?,?,?,?,?,?,?,?)");
            $ins->execute([$full_name, $username, $email, $hash, $acct_type, $status, $student_id ?: null, $dept ?: null]);
            auditLog(null, $username, null, 'Registration', "New $acct_type account created", $_SERVER['REMOTE_ADDR'] ?? '');
            $success = $acct_type === 'student'
                ? 'Account created! You can now sign in.'
                : 'Account request submitted. An administrator will review and activate your account.';
        }
    }
}

$pageTitle = 'Register';
include __DIR__ . '/includes/head.php';
?>
<body>
<div class="login-wrap">
  <header class="login-header">
    <div class="logo-emblem">E</div>
    <div class="logo-text">EVSU – Ormoc Campus <span>INC Form Portal</span></div>
  </header>
  <div class="login-body">
    <div style="width:100%;max-width:460px;">
      <a href="/login.php?type=<?= $type ?>" class="back-link">← Back to login</a>
      <div class="login-card">
        <div class="login-card-header">
          <div class="login-emblem">E</div>
          <h2>Create Account</h2>
          <p>Register as a student or employee</p>
        </div>

        <?php if ($success): ?>
          <div class="alert alert-success"><?= h($success) ?></div>
          <a href="/login.php?type=<?= $type ?>" class="btn-primary full">Go to Sign In</a>
        <?php else: ?>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="account_type" value="<?= h($type) ?>">

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
