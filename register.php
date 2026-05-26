<?php
// register.php — Account registration entry point
require_once __DIR__ . '/backend/controllers/RegisterController.php';
(new RegisterController())->run();
