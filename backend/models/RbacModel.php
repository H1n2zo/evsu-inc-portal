<?php
// models/RbacModel.php
// Single Responsibility: Query role and permission data for the RBAC overview

require_once __DIR__ . '/../core/Model.php';

class RbacModel extends Model
{
    public function getRoleCounts(): array
    {
        return $this->getDb()->query("SELECT r.role_name, COUNT(ur.id) as cnt
            FROM roles r LEFT JOIN user_roles ur ON r.id = ur.role_id
            GROUP BY r.role_name")->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function getMultiRoleUsers(): array
    {
        return $this->getDb()->query("SELECT u.id, u.full_name, u.username, u.status,
            GROUP_CONCAT(r.role_name ORDER BY r.role_name SEPARATOR ',') as roles,
            COUNT(ur.id) as role_count
            FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            JOIN roles r ON ur.role_id = r.id
            WHERE u.account_type = 'employee'
            GROUP BY u.id HAVING role_count > 1 ORDER BY role_count DESC")->fetchAll();
    }

    public function getAllEmployeeRoles(): array
    {
        return $this->getDb()->query("SELECT u.id, u.full_name, u.username, u.department, u.status,
            GROUP_CONCAT(r.role_name ORDER BY r.role_name SEPARATOR ',') as roles
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id AND u.account_type='employee'
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE u.account_type = 'employee'
            GROUP BY u.id ORDER BY u.full_name")->fetchAll();
    }

    public function getAllRoles(): array
    {
        return $this->getDb()->query("SELECT * FROM roles")->fetchAll();
    }
}
