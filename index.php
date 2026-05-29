<?php

require_once __DIR__ . '/backend/config/app.php';
require_once __DIR__ . '/backend/core/View.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// Redirect already-logged-in users to their dashboard
if (!empty($_SESSION['user_id'])) {
    $type = $_SESSION['account_type'];
    if ($type === 'admin')        { header('Location: ' . url('admin/dashboard.php')); exit; }
    elseif ($type === 'employee') { header('Location: ' . url('employee/dashboard.php')); exit; }
    else                          { header('Location: ' . url('student/dashboard.php')); exit; }
}

// Show landing page to guests
$view = new View();
$view->render('index', ['pageTitle' => 'Welcome']);
