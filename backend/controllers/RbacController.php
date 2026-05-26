<?php
// controllers/RbacController.php
// Single Responsibility: Admin RBAC overview page (READ only)
// OOP Concepts:
//   - Inheritance: extends Controller
//   - Composition: RbacModel provides all data

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/RbacModel.php';

class RbacController extends Controller
{
    private AuthGuard $guard;
    private RbacModel $rbac;

    public function __construct()
    {
        parent::__construct();
        $this->guard = new AuthGuard();
        $this->rbac  = new RbacModel();
    }

    public function run(): void
    {
        $this->guard->requireAdmin();

        $this->render('admin/rbac', [
            'pageTitle'  => 'RBAC Configuration',
            'activePage' => 'rbac',
            'csrf'       => $this->guard->csrfToken(),
            'roleCounts' => $this->rbac->getRoleCounts(),
            'multiRole'  => $this->rbac->getMultiRoleUsers(),
            'empRoles'   => $this->rbac->getAllEmployeeRoles(),
        ]);
    }
}
