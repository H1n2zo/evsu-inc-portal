<?php

define('DB_HOST', 'sql200.infinityfree.com'); 
define('DB_NAME', 'if0_41988594_evsu_inc_portal'); 
define('DB_USER', 'if0_41988594');            
define('DB_PASS', 'Intern0908');    
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Cleaned up the error message slightly to reflect the live environment
            die('<div style="font-family:sans-serif;padding:2rem;color:#6B0F1A;background:#FEF2F2;border-left:4px solid #6B0F1A;margin:2rem;">
                <strong>Database Connection Failed</strong><br>' 
                . htmlspecialchars($e->getMessage()) . '<br><br>
                Please verify your InfinityFree MySQL credentials and ensure your database schema has been imported via phpMyAdmin.
            </div>');
        }
    }
    return $pdo;
}