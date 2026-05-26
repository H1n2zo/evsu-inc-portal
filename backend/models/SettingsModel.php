<?php
// models/SettingsModel.php
// Data Layer — reads and writes system settings from the DB
// OOP: Extends Model. NO static methods — instantiate and call as a regular object.

require_once __DIR__ . '/../core/Model.php';

class SettingsModel extends Model
{
    /**
     * Get a single setting value by key.
     * Returns null if the key does not exist.
     */
    public function get(string $key): ?string
    {
        try {
            $stmt = $this->getDb()->prepare(
                "SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1"
            );
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            return $row ? $row['setting_value'] : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Get all settings as an associative array [key => value].
     */
    public function getAll(): array
    {
        $map = [];
        foreach (
            $this->getDb()
                 ->query("SELECT setting_key, setting_value FROM settings")
                 ->fetchAll() as $row
        ) {
            $map[$row['setting_key']] = $row['setting_value'];
        }
        return $map;
    }

    /**
     * Save (upsert) an array of [key => value] pairs.
     */
    public function save(array $pairs): void
    {
        $stmt = $this->getDb()->prepare(
            "INSERT INTO settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = ?"
        );
        foreach ($pairs as $key => $val) {
            $stmt->execute([$key, $val, $val]);
        }
    }
}
