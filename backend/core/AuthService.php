<?php
// core/AuthService.php
// Logic Layer — login, logout, and role-switching
// OOP Concepts:
//   - Encapsulation: session writing is a private method
//   - Composition: UserModel and AuditLogModel injected as instances

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';

class AuthService
{
    private UserModel     $users;
    private AuditLogModel $logs;

    public function __construct()
    {
        $this->users = new UserModel();
        $this->logs  = new AuditLogModel();
    }

    public function login(string $username, string $password): array
    {
        $ip   = $_SERVER['REMOTE_ADDR'] ?? '';
        $user = $this->users->authenticate($username, $password);

        if (!$user) {
            $this->logs->write(null, $username, null, 'Login failed', 'Invalid credentials', $ip);
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }
        if ($user['status'] !== 'active') {
            return ['success' => false, 'error' => 'Account is ' . $user['status'] . '. Contact the administrator.'];
        }

        $roles = $user['account_type'] === 'employee'
            ? $this->users->getUserRoles($user['id'])
            : [];

        $this->writeSession($user, $roles);

        $this->logs->write($user['id'], $user['username'], $_SESSION['active_role'], 'Login', 'Authenticated', $ip);
        return ['success' => true];
    }

    public function logout(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->logs->write(
                $_SESSION['user_id'],
                $_SESSION['username'] ?? '',
                $_SESSION['active_role'] ?? '',
                'Logout', 'Session ended', $_SERVER['REMOTE_ADDR'] ?? ''
            );
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public function switchRole(string $role): bool
    {
        if ($_SESSION['account_type'] !== 'employee') return false;
        if (!in_array($role, $_SESSION['roles']))      return false;

        $old = $_SESSION['active_role'];
        $_SESSION['active_role'] = $role;

        $this->logs->write(
            $_SESSION['user_id'], $_SESSION['username'], $role,
            'Role Switch', "Switched from $old to $role", $_SERVER['REMOTE_ADDR'] ?? ''
        );
        return true;
    }

    // Private: encapsulate all session writes in one place
    private function writeSession(array $user, array $roles): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']       = $user['id'];
        $_SESSION['username']      = $user['username'];
        $_SESSION['full_name']     = $user['full_name'];
        $_SESSION['account_type']  = $user['account_type'];
        $_SESSION['status']        = $user['status'];
        $_SESSION['roles']         = $roles;
        $_SESSION['active_role']   = $user['account_type'] === 'employee'
            ? ($roles[0] ?? 'instructor')
            : $user['account_type'];
        $_SESSION['last_activity'] = time();
    }
}
