<?php
// controllers/EmployeeDashboardController.php
// Logic Layer — assemble employee/role-aware dashboard data
// OOP Concepts:
//   - Inheritance: extends DashboardController
//   - Polymorphism: overrides getData() for employee role context

require_once __DIR__ . '/DashboardController.php';

class EmployeeDashboardController extends DashboardController
{
    protected function authorize(): void
    {
        $this->guard->requireEmployee();
    }

    protected function getViewTemplate(): string
    {
        return 'employee/dashboard';
    }

    protected function getData(): array
    {
        $uid        = $_SESSION['user_id'];
        $activeRole = $_SESSION['active_role'];
        $global     = $this->apps->globalStats();

        // Flat variables — view uses them directly (no $stats['key'] nesting)
        return [
            'pageTitle'    => 'Dashboard',
            'activePage'   => 'dashboard',
            'activeRole'   => $activeRole,
            'roleLabels'   => ['instructor' => 'Instructor', 'dept_head' => 'Department Head', 'registrar' => 'Registrar'],
            'roleBadge'    => ['instructor' => 'badge-info', 'dept_head' => 'badge-gold', 'registrar' => 'badge-success'],
            'pendingForMe' => $this->apps->countForEmployee($activeRole, $uid, ['status' => 'in_progress']),
            'totalAll'     => $global['total'],
            'resolvedAll'  => $global['resolved'],
            'recent'       => $this->apps->getForEmployee($activeRole, $uid, ['limit' => 6]),
        ];
    }
}
