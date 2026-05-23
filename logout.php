<?php
require_once __DIR__ . '/includes/auth.php';
logout();
header('Location: /evsu_inc_portal/index.php');
exit;
