<?php
// controllers/LogController.php
// Logic Layer — admin audit log viewer: READ with pagination and filters
// OOP Concepts:
//   - Inheritance: extends Controller
//   - Encapsulation: pagination logic is a private instance property

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/AuditLogModel.php';

class LogController extends Controller
{
    private AuthGuard     $guard;
    private AuditLogModel $logs;
    private int           $perPage = 30;

    public function __construct()
    {
        parent::__construct();
        $this->guard = new AuthGuard();
        $this->logs  = new AuditLogModel();
    }

    public function run(): void
    {
        $this->guard->requireAdmin();

        $filters = [
            'search' => trim($_GET['q'] ?? ''),
            'role'   => $_GET['role'] ?? '',
        ];
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $total = $this->logs->count($filters);
        $pages = (int)ceil($total / $this->perPage);

        $this->render('admin/logs', [
            'pageTitle'  => 'Audit Logs',
            'activePage' => 'logs',
            'csrf'       => $this->guard->csrfToken(),
            'logs'       => $this->logs->getFiltered($filters, $this->perPage, ($page - 1) * $this->perPage),
            'filters'    => $filters,
            'page'       => $page,
            'pages'      => $pages,
            'total'      => $total,
        ]);
    }
}
