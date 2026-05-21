<?php
require_once 'includes/auth.php';
startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$loginType = $_POST['login_type'] ?? '';
$password  = $_POST['password'] ?? '';
$db        = getDB();

if ($loginType === 'student') {
    $studentId = trim($_POST['student_id'] ?? '');
    if (empty($studentId) || empty($password)) {
        header('Location: index.php?login=student&error=Please+fill+in+all+fields.');
        exit;
    }
    $stmt = $db->prepare('SELECT * FROM users WHERE student_number = ? AND role = ? AND is_active = 1');
    $stmt->execute([$studentId, 'student']);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        header('Location: index.php?login=student&error=Invalid+Student+ID+or+password.');
        exit;
    }
} elseif ($loginType === 'employee') {
    $username = trim($_POST['username'] ?? '');
    if (empty($username) || empty($password)) {
        header('Location: index.php?login=employee&error=Please+fill+in+all+fields.');
        exit;
    }
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND role IN ('admin','registrar','department_head','instructor') AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        header('Location: index.php?login=employee&error=Invalid+username+or+password.');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id']       = $user['user_id'];
$_SESSION['name']          = $user['name'];
$_SESSION['role']          = $user['role'];
$_SESSION['dept_id']       = $user['dept_id'];
$_SESSION['last_activity'] = time();

auditLog('Login', 'users', $user['user_id']);

redirectToDashboard($user['role']);
