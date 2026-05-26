<?php
// models/ModuleModel.php
// Single Responsibility: Manage feature flags (modules) in the DB

require_once __DIR__ . '/../core/Model.php';

class ModuleModel extends Model
{
    public function getAll(): array
    {
        return $this->getDb()->query("SELECT * FROM modules ORDER BY id")->fetchAll();
    }

    public function isEnabled(string $key): bool
    {
        $stmt = $this->getDb()->prepare("SELECT is_enabled FROM modules WHERE module_key=?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? (bool)$row['is_enabled'] : true;
    }

    public function setEnabled(string $key, bool $enabled, int $updatedBy): void
    {
        $this->getDb()->prepare("UPDATE modules SET is_enabled=?, updated_by=? WHERE module_key=?")
                      ->execute([(int)$enabled, $updatedBy, $key]);
    }
}
