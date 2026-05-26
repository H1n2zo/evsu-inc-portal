<?php
// models/AuditLogModel.php
// Single Responsibility: Write and query the audit_logs table

require_once __DIR__ . '/../core/Model.php';

class AuditLogModel extends Model
{
    public function write(?int $userId, string $username, ?string $role, string $action, ?string $desc, string $ip): void
    {
        try {
            $this->getDb()->prepare(
                "INSERT INTO audit_logs (user_id, username, active_role, action, description, ip_address) VALUES (?,?,?,?,?,?)"
            )->execute([$userId, $username, $role, $action, $desc, $ip]);
        } catch (Throwable) {
            // Never let logging break the app
        }
    }

    public function getFiltered(array $filters, int $perPage, int $offset): array
    {
        [$where, $params] = $this->buildWhere($filters);
        $stmt = $this->getDb()->prepare(
            "SELECT * FROM audit_logs WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);
        $stmt = $this->getDb()->prepare("SELECT COUNT(*) FROM audit_logs WHERE $where");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Private: encapsulate WHERE builder
    private function buildWhere(array $filters): array
    {
        $where = ['1=1']; $params = [];
        if (!empty($filters['search'])) {
            $where[] = "(username LIKE ? OR action LIKE ? OR description LIKE ? OR ip_address LIKE ?)";
            $s = "%{$filters['search']}%";
            array_push($params, $s, $s, $s, $s);
        }
        if (!empty($filters['role'])) { $where[] = "active_role = ?"; $params[] = $filters['role']; }
        return [implode(' AND ', $where), $params];
    }
}
