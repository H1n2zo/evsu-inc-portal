<?php
require_once 'includes/auth.php';
startSession();
if (isLoggedIn()) {
    redirectToDashboard($_SESSION['role']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVSU-OC INC Form System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="landing-bg">
    <div class="landing-card">
        <div class="landing-logo">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
        </div>
        <div class="landing-title">EVSU-OC INC Form System</div>
        <div class="landing-sub">Eastern Visayas State University – Ormoc Campus</div>

        <div id="roleSelect">
            <p style="font-size:0.92rem;color:#444;margin-bottom:20px;">Who are you?</p>
            <div class="role-cards">
                <div class="role-card" onclick="showLogin('student')">
                    <div class="role-card-icon">
                        <svg viewBox="0 0 24 24" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12 12 0 0112 21a12 12 0 01-6.16-10.422L12 14z"/>
                        </svg>
                    </div>
                    <div class="role-card-label">Student</div>
                    <div class="role-card-desc">File INC applications</div>
                </div>
                <div class="role-card" onclick="showLogin('employee')">
                    <div class="role-card-icon">
                        <svg viewBox="0 0 24 24" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5.916-3.517M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a4 4 0 015.916-3.517M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="role-card-label">Employee</div>
                    <div class="role-card-desc">Admin, Registrar, Instructor, Dept. Head</div>
                </div>
            </div>
        </div>

        <div id="studentLogin" style="display:none;">
            <button class="back-link" onclick="showRoleSelect()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </button>
            <div class="landing-title" style="font-size:1.1rem;margin-bottom:4px;">Student Login</div>
            <div class="landing-sub" style="margin-bottom:20px;">Enter your student credentials</div>
            <?php if (isset($_GET['error']) && $_GET['login'] === 'student'): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['timeout'])): ?>
                <div class="alert alert-info">Session expired. Please log in again.</div>
            <?php endif; ?>
            <form class="login-form" method="POST" action="login.php">
                <input type="hidden" name="login_type" value="student">
                <div class="form-group">
                    <label for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id" class="form-control" placeholder="e.g. 2024-0001" required autofocus>
                </div>
                <div class="form-group">
                    <label for="student_pass">Password</label>
                    <input type="password" id="student_pass" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Log In</button>
            </form>
        </div>

        <div id="employeeLogin" style="display:none;">
            <button class="back-link" onclick="showRoleSelect()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </button>
            <div class="landing-title" style="font-size:1.1rem;margin-bottom:4px;">Employee Login</div>
            <div class="landing-sub" style="margin-bottom:20px;">Enter your employee credentials</div>
            <?php if (isset($_GET['error']) && $_GET['login'] === 'employee'): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            <form class="login-form" method="POST" action="login.php">
                <input type="hidden" name="login_type" value="employee">
                <div class="form-group">
                    <label for="emp_username">Username</label>
                    <input type="text" id="emp_username" name="username" class="form-control" placeholder="Enter your username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="emp_pass">Password</label>
                    <input type="password" id="emp_pass" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Log In</button>
            </form>
        </div>
    </div>
</div>
<script>
function showRoleSelect() {
    document.getElementById('roleSelect').style.display = '';
    document.getElementById('studentLogin').style.display = 'none';
    document.getElementById('employeeLogin').style.display = 'none';
}
function showLogin(type) {
    document.getElementById('roleSelect').style.display = 'none';
    document.getElementById('studentLogin').style.display = type === 'student' ? '' : 'none';
    document.getElementById('employeeLogin').style.display = type === 'employee' ? '' : 'none';
}
<?php if (isset($_GET['login'])): ?>
showLogin('<?= htmlspecialchars($_GET['login']) ?>');
<?php endif; ?>
</script>
</body>
</html>
