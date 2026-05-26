<?php
// controllers/StudentDashboardController.php
// Logic Layer — assemble student dashboard data
// OOP Concepts:
//   - Inheritance: extends DashboardController
//   - Polymorphism: overrides getData() for student context

require_once __DIR__ . '/DashboardController.php';

class StudentDashboardController extends DashboardController
{
    protected function authorize(): void
    {
        $this->guard->requireStudent();
    }

    protected function getViewTemplate(): string
    {
        return 'student/dashboard';
    }

    protected function getData(): array
    {
        $uid = $_SESSION['user_id'];

        // Flat stat variables — view uses $total, $active, $resolved, $rejected directly
        return [
            'pageTitle'    => 'My Dashboard',
            'activePage'   => 'dashboard',
            'total'        => $this->apps->countForStudent($uid),
            'active'       => $this->apps->countForStudent($uid, 'in_progress')
                            + $this->apps->countForStudent($uid, 'pending_payment')
                            + $this->apps->countForStudent($uid, 'verification'),
            'resolved'     => $this->apps->countForStudent($uid, 'resolved'),
            'rejected'     => $this->apps->countForStudent($uid, 'rejected'),
            'recent'       => $this->apps->getForStudent($uid),
            'needsPayment' => $this->apps->pendingPaymentForStudent($uid),
        ];
    }
}
