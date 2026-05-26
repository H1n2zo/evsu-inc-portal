<?php
// config/Database.php
// Data Layer — manages the PDO database connection for InfinityFree
// OOP: Encapsulates connection credentials and options.
// NO static methods or properties — instantiate and inject as a dependency.

class Database
{
    private PDO $connection;
    private string $host    = 'sql200.infinityfree.com';   
    private string $name    = 'if0_41988594_evsu_inc_portal';   
    private string $user    = 'if0_41988594';  
    private string $pass    = 'Intern0908'; 
    private string $charset = 'utf8mb4';

    public function __construct()
    {
        $dsn  = "mysql:host={$this->host};dbname={$this->name};charset={$this->charset}";
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->connection = new PDO($dsn, $this->user, $this->pass, $opts);
        } catch (PDOException $e) {
            die(
                '<div style="font-family:sans-serif;padding:2rem;color:#6B0F1A;background:#FEF2F2;border-left:4px solid #6B0F1A;margin:2rem;">'
                . '<strong>Database Connection Failed</strong><br>'
                . htmlspecialchars($e->getMessage())
                . '</div>'
            );
        }
    }

    // Returns the live PDO connection — injected into Model instances
    public function getConnection(): PDO
    {
        return $this->connection;
    }
}