<?php
// backend/config/app.php
// Central application configuration for InfinityFree hosting.

define('BASE_URL', 'https://evsuinc.infinityfreeapp.com');  // no trailing slash

function url(string $path = ''): string
{
    $rootFiles = ['index.php', 'login.php', 'logout.php', 'register.php', 'forgot_password.php'];

    $clean = ltrim($path, '/');
    $base  = explode('?', $clean)[0];

    if ($clean === '') {
        return BASE_URL . '/index.php';
    }

    foreach ($rootFiles as $rf) {
        if ($base === $rf || str_starts_with($clean, $rf . '?')) {
            return BASE_URL . '/' . $clean;
        }
    }

    return BASE_URL . '/backend/routes/' . $clean;
}

function asset(string $path = ''): string
{
    return BASE_URL . '/frontend/assets/' . ltrim($path, '/');
}