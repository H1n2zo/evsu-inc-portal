<?php
// models/UserModel.php
// Single Responsibility: All database operations related to users
// OOP Concepts:
//   - Encapsulation: private query helpers hide SQL from controllers
//   - Inheritance: extends Model (gets DB access)

require_once __DIR__ . '/../core/Model.php';

class UserModel extends Model
{
    // Private: find by username OR student_id
    private function findByUsername(string $username): array|false
    {
        $stmt = $this->getDb()->prepare(
            "SELECT * FROM users WHERE username = ? OR student_id = ? LIMIT 1"
        );
        $stmt->execute([$username, $username]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->getDb()->prepare("SELECT u.*, GROUP_CONCAT(r.role_name SEPARATOR ',') as roles
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id AND u.account_type = 'employee'
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE u.id = ? GROUP BY u.id");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAll(array $filters = []): array
    {
        $where = ['1=1']; $params = [];

        if (!empty($filters['status'])) {
            $where[] = "u.status = ?"; $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(u.full_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
            $s = "%{$filters['search']}%";
            array_push($params, $s, $s, $s);
        }
        if (!empty($filters['role']) && in_array($filters['role'], ['instructor','dept_head','registrar'])) {
            $where[] = "EXISTS (SELECT 1 FROM user_roles ur2 JOIN roles r2 ON ur2.role_id=r2.id WHERE ur2.user_id=u.id AND r2.role_name=?)";
            $params[] = $filters['role'];
        } elseif (!empty($filters['role']) && in_array($filters['role'], ['admin','student','employee'])) {
            $where[] = "u.account_type = ?"; $params[] = $filters['role'];
        }

        $sql = "SELECT u.*, GROUP_CONCAT(r.role_name ORDER BY r.role_name SEPARATOR ',') as roles
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id AND u.account_type = 'employee'
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY u.id ORDER BY u.status='pending' DESC, u.created_at DESC";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function usernameOrEmailExists(string $username, string $email): bool
    {
        $stmt = $this->getDb()->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
        $stmt->execute([$username, $email]);
        return (bool)$stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->getDb()->prepare("INSERT INTO users
            (full_name, username, email, password_hash, account_type, status, student_id, department)
            VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['full_name'], $data['username'], $data['email'],
            $data['password_hash'], $data['account_type'], $data['status'],
            $data['student_id'] ?? null, $data['department'] ?? null,
        ]);
        return (int)$this->getDb()->lastInsertId();
    }

    public function setStatus(int $id, string $status): void
    {
        $this->getDb()->prepare("UPDATE users SET status=? WHERE id=?")->execute([$status, $id]);
    }

    public function delete(int $id): void
    {
        $this->getDb()->prepare("DELETE FROM users WHERE id=? AND status='pending'")->execute([$id]);
    }

    public function getUserRoles(int $id): array
    {
        $stmt = $this->getDb()->prepare("SELECT r.role_name FROM user_roles ur JOIN roles r ON ur.role_id=r.id WHERE ur.user_id=?");
        $stmt->execute([$id]);
        return array_column($stmt->fetchAll(), 'role_name');
    }

    public function saveRoles(int $userId, array $roles, int $assignedBy): void
    {
        $valid = ['instructor', 'dept_head', 'registrar'];
        $roles = array_intersect($roles, $valid);
        $this->getDb()->prepare("DELETE FROM user_roles WHERE user_id=?")->execute([$userId]);
        $ins = $this->getDb()->prepare("INSERT INTO user_roles (user_id, role_id, assigned_by)
            SELECT ?, id, ? FROM roles WHERE role_name=?");
        foreach ($roles as $role) {
            $ins->execute([$userId, $assignedBy, $role]);
        }
    }

    public function getInstructors(): array
    {
        return $this->getDb()->query("SELECT u.id, u.full_name, u.department FROM users u
            JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON ur.role_id=r.id
            WHERE r.role_name='instructor' AND u.status='active' ORDER BY u.full_name")->fetchAll();
    }

    public function getDeptHeads(): array
    {
        return $this->getDb()->query("SELECT u.id, u.full_name, u.department FROM users u
            JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON ur.role_id=r.id
            WHERE r.role_name='dept_head' AND u.status='active' ORDER BY u.full_name")->fetchAll();
    }

    // Used by AuthService — checks username OR student_id, then verifies password
    public function authenticate(string $username, string $password): array|false
    {
        $user = $this->findByUsername($username);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        return $user;
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->getDb()->prepare(
            "SELECT * FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function clearPasswordResets(string $email): void
    {
        $this->getDb()->prepare(
            "DELETE FROM password_resets WHERE email = ?"
        )->execute([$email]);
    }

    public function createPasswordReset(string $email, string $otpHash, string $expiresAt): void
    {
        $this->getDb()->prepare(
            "INSERT INTO password_resets (email, otp_hash, expires_at) VALUES (?, ?, ?)"
        )->execute([$email, $otpHash, $expiresAt]);
    }

    public function findValidPasswordReset(string $email): array|false
    {
        $stmt = $this->getDb()->prepare(
            "SELECT * FROM password_resets
             WHERE email = ? AND used = 0 AND expires_at > NOW()
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function updatePassword(int $id, string $hash): void
    {
        $this->getDb()->prepare(
            "UPDATE users SET password_hash = ? WHERE id = ?"
        )->execute([$hash, $id]);
    }

    public function markResetUsed(int $id): void
    {
        $this->getDb()->prepare(
            "UPDATE password_resets SET used = 1 WHERE id = ?"
        )->execute([$id]);
    }
}