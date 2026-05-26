<?php
// core/Model.php
// Data Layer — base model providing shared DB access to all child models
// OOP Concepts:
//   - Encapsulation: $pdo is private, accessed only via getDb()
//   - Inheritance: All models extend this class
//   - NO static properties or methods — Database is injected via constructor

require_once __DIR__ . '/../config/Database.php';

abstract class Model
{
    private PDO $pdo;

    public function __construct()
    {
        // Instantiate a new Database connection and retrieve its PDO object
        $db        = new Database();
        $this->pdo = $db->getConnection();
    }

    // Protected: child models access DB only through this method
    protected function getDb(): PDO
    {
        return $this->pdo;
    }

    // Shared helper: HTML-escape output
    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
