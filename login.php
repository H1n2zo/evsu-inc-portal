<?php
// login.php — Login entry point
require_once __DIR__ . '/backend/controllers/LoginController.php';
(new LoginController())->run();
