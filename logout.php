<?php
// logout.php — Destroys session and redirects to landing page
require_once __DIR__ . '/backend/config/app.php';
require_once __DIR__ . '/backend/config/Database.php';
require_once __DIR__ . '/backend/core/Model.php';
require_once __DIR__ . '/backend/models/UserModel.php';
require_once __DIR__ . '/backend/models/AuditLogModel.php';
require_once __DIR__ . '/backend/core/AuthService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
(new AuthService())->logout();
header('Location: ' . url('index.php'));
exit;
