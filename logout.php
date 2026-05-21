<?php
require_once 'includes/auth.php';
startSession();
if (isLoggedIn()) {
    auditLog('Logout', 'users', $_SESSION['user_id']);
}
session_unset();
session_destroy();
header('Location: ../index.php');
exit;
