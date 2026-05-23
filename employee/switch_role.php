<?php
// employee/switch_role.php
require_once __DIR__ . '/../includes/auth.php';
requireEmployee();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $role = trim($_POST['role'] ?? '');
    if (!switchRole($role)) {
        // Role not assigned — silently redirect
    }
}
header('Location: /employee/dashboard.php');
exit;
