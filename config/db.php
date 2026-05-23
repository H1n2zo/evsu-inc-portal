<?php
// config/db.php — PDO connection for XAMPP localhost

define('DB_HOST', 'localhost');
define('DB_NAME', 'evsu_inc_portal');
define('DB_USER', 'root');
define('DB_PASS', '');          // Default XAMPP password is empty
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
            die('<div style="font-family:sans-serif;padding:2rem;color:#6B0F1A;background:#FEF2F2;border-left:4px solid #6B0F1A;margin:2rem;">
                <strong>Database Connection Failed</strong><br>
                ' . htmlspecialchars($e->getMessage()) . '<br><br>
                Make sure XAMPP MySQL is running and <code>config/database.sql</code> has been imported.
            </div>');
        }
    }
    return $pdo;
}
