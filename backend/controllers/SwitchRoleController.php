<?php
// controllers/SwitchRoleController.php
// Single Responsibility: Handle employee role switching

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../core/AuthService.php';

class SwitchRoleController extends Controller
{
    private AuthGuard   $guard;
    private AuthService $auth;

    public function __construct()
    {
        parent::__construct();
        $this->guard = new AuthGuard();
        $this->auth  = new AuthService();
    }

    public function run(): void
    {
        $this->guard->requireEmployee();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guard->verifyCsrf();
            $this->auth->switchRole(trim($_POST['role'] ?? ''));
        }
        $this->redirect('employee/dashboard.php');
    }
}
