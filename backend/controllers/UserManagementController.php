<?php
// controllers/UserManagementController.php
// Single Responsibility: Admin user list, approval, role assignment (CRUD)
// OOP Concepts:
//   - Inheritance: extends Controller
//   - Encapsulation: each CRUD action is a private method; run() is the single entry point
//   - Composition: UserModel, RbacModel, AuditLogModel injected via constructor

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/RbacModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';

class UserManagementController extends Controller
{
    private AuthGuard     $guard;
    private UserModel     $users;
    private RbacModel     $rbac;
    private AuditLogModel $logs;

    public function __construct()
    {
        parent::__construct();
        $this->guard = new AuthGuard();
        $this->users = new UserModel();
        $this->rbac  = new RbacModel();
        $this->logs  = new AuditLogModel();
    }

    public function run(): void
    {
        $this->guard->requireAdmin();
        $msg   = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$msg, $error] = $this->handlePost();
        }

        // Read filter params
        $filters = [
            'status' => ($_GET['filter'] ?? '') === 'pending' ? 'pending' : '',
            'search' => trim($_GET['q'] ?? ''),
            'role'   => $_GET['role'] ?? '',
        ];

        // Fetch data in the controller — view receives ready-made arrays
        $users    = $this->users->getAll($filters);
        $allRoles = $this->rbac->getAllRoles();

        $this->render('admin/users', [
            'pageTitle'  => 'Users & Roles',
            'activePage' => 'users',
            'csrf'       => $this->guard->csrfToken(),
            'msg'        => $msg,
            'error'      => $error,
            'users'      => $users,
            'allRoles'   => $allRoles,
            'search'     => $filters['search'],
            'roleFilter' => $filters['role'],
            'filterStatus' => $_GET['filter'] ?? 'all',
        ]);
    }

    // ── POST dispatcher ────────────────────────────────────────────
    private function handlePost(): array
    {
        $this->guard->verifyCsrf();
        $action = $_POST['action'] ?? '';
        $uid    = (int)($_POST['user_id'] ?? 0);
        $ip     = $_SERVER['REMOTE_ADDR'] ?? '';

        return match($action) {
            'approve'    => $this->approveUser($uid, $ip),
            'disable'    => $this->disableUser($uid),
            'enable'     => $this->enableUser($uid),
            'reject'     => $this->rejectUser($uid),
            'save_roles' => $this->saveRoles($uid, $ip),
            default      => ['', ''],
        };
    }

    // ── CRUD operations — each action is its own private method ────

    // CREATE-adjacent: approve a pending account
    private function approveUser(int $uid, string $ip): array
    {
        $this->users->setStatus($uid, 'active');
        $user = $this->users->findById($uid);
        $this->logs->write(
            $_SESSION['user_id'], $_SESSION['username'], 'admin',
            'User Approved', 'Activated: ' . ($user['username'] ?? ''), $ip
        );
        return ['Account approved and activated.', ''];
    }

    // UPDATE: disable account
    private function disableUser(int $uid): array
    {
        $this->users->setStatus($uid, 'disabled');
        return ['Account disabled.', ''];
    }

    // UPDATE: re-enable account
    private function enableUser(int $uid): array
    {
        $this->users->setStatus($uid, 'active');
        return ['Account re-enabled.', ''];
    }

    // DELETE: reject and remove pending account
    private function rejectUser(int $uid): array
    {
        $this->users->delete($uid);
        return ['Account request rejected and removed.', ''];
    }

    // UPDATE: assign roles (RBAC)
    private function saveRoles(int $uid, string $ip): array
    {
        $user = $this->users->findById($uid);
        if (!$user || $user['account_type'] !== 'employee') {
            return ['', 'Can only assign roles to employee accounts.'];
        }
        $roles = $_POST['roles'] ?? [];
        $this->users->saveRoles($uid, $roles, $_SESSION['user_id']);
        $this->logs->write(
            $_SESSION['user_id'], $_SESSION['username'], 'admin',
            'Roles Updated', "User ID $uid roles: " . implode(',', $roles), $ip
        );
        return ['Roles updated successfully.', ''];
    }
}
