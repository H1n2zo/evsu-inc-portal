<?php
// controllers/AdminDashboardController.php
// Logic Layer — assemble admin dashboard data
// OOP Concepts:
//   - Inheritance: extends DashboardController
//   - Polymorphism: overrides getData() for admin-wide context

require_once __DIR__ . '/DashboardController.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/SettingsModel.php';

class AdminDashboardController extends DashboardController
{
    private UserModel     $users;
    private SettingsModel $settings;

    public function __construct()
    {
        parent::__construct();
        $this->users    = new UserModel();
        $this->settings = new SettingsModel();
    }

    protected function authorize(): void
    {
        $this->guard->requireAdmin();
    }

    protected function getViewTemplate(): string
    {
        return 'admin/dashboard';
    }

    protected function getData(): array
    {
        $global = $this->apps->globalStats();
        $all    = $this->users->getAll();

        // Flat variables — view accesses $stats['key'], $recentApps, $schoolYear, $activeSem
        return [
            'pageTitle'    => 'Dashboard',
            'activePage'   => 'dashboard',
            'schoolYear'   => $this->settings->get('school_year')     ?? '2025-2026',
            'activeSem'    => $this->settings->get('active_semester') ?? '2nd',
            'stats'        => [
                'total'         => $global['total'],
                'pending_apps'  => $global['pending'],
                'resolved'      => $global['resolved'],
                'active_users'  => count(array_filter($all, fn($u) => $u['status'] === 'active')),
                'pending_users' => count(array_filter($all, fn($u) => $u['status'] === 'pending')),
            ],
            'recentApps'   => $this->apps->getAll(['limit' => 8]),
        ];
    }
}
