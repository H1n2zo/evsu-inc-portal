<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$user = getCurrentUser();
$roleLabel = [
    'admin'           => 'Administrator',
    'registrar'       => 'Registrar',
    'department_head' => 'Department Head',
    'instructor'      => 'Instructor',
    'student'         => 'Student',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVSU-OC INC System</title>
    <link rel="stylesheet" href="<?= str_repeat('../', $depth ?? 1) ?>css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-brand">EVSU-OC <span>INC Form System</span></div>
    <div class="navbar-user">
        <?= htmlspecialchars($user['name']) ?> &mdash; <?= $roleLabel[$user['role']] ?>
        <a href="<?= str_repeat('../', $depth ?? 1) ?>logout.php">Logout</a>
    </div>
</nav>
<div class="layout">
