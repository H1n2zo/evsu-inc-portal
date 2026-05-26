<?php
// controllers/EmployeeApplicationController.php
// Logic Layer — employee application list: READ filtered by active role with pagination
// OOP Concepts:
//   - Inheritance: extends ApplicationController
//   - Polymorphism: overrides buildViewData() for role-scoped view

require_once __DIR__ . '/ApplicationController.php';

class EmployeeApplicationController extends ApplicationController
{
    private int $perPage = 20;

    protected function authorize(): void
    {
        $this->guard->requireEmployee();
    }

    protected function getViewTemplate(): string
    {
        return 'employee/applications';
    }

    protected function buildViewData(): array
    {
        $role    = $_SESSION['active_role'];
        $uid     = $_SESSION['user_id'];
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $search  = trim($_GET['q'] ?? '');
        $status  = $_GET['status'] ?? '';

        $filters = [
            'status' => $status,
            'search' => $search,
            'limit'  => $this->perPage,
            'offset' => ($page - 1) * $this->perPage,
        ];

        $total = $this->apps->countForEmployee($role, $uid, $filters);

        return [
            'apps'        => $this->apps->getForEmployee($role, $uid, $filters),
            'activeRole'  => $role,
            'search'      => $search,
            'status'      => $status,
            'page'        => $page,
            'pages'       => (int)ceil($total / $this->perPage),
            'total'       => $total,
        ];
    }
}
