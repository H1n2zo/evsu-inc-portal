<?php
require_once __DIR__ . '/db.php';

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_unset();
        session_destroy();
        header('Location: index.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

function requireRole($roles) {
    requireLogin();
    if (!in_array($_SESSION['role'], (array)$roles)) {
        header('Location: dashboard.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM users WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function auditLog($action, $targetTable = null, $targetId = null) {
    if (!isLoggedIn()) return;
    $db = getDB();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $db->prepare('INSERT INTO audit_logs (user_id, action, target_table, target_id, ip_address) VALUES (?,?,?,?,?)');
    $stmt->execute([$_SESSION['user_id'], $action, $targetTable, $targetId, $ip]);
}

function redirectToDashboard($role) {
    $map = [
        'admin'           => 'dashboard/admin.php',
        'registrar'       => 'dashboard/registrar.php',
        'department_head' => 'dashboard/department_head.php',
        'instructor'      => 'dashboard/instructor.php',
        'student'         => 'dashboard/student.php',
    ];
    header('Location: ' . ($map[$role] ?? 'index.php'));
    exit;
}
