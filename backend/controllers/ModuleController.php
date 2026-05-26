<?php
// controllers/ModuleController.php
// Single Responsibility: Admin module enable/disable (UPDATE)
// OOP Concepts:
//   - Inheritance: extends Controller
//   - Encapsulation: update logic is private

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/ModuleModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';

class ModuleController extends Controller
{
    private AuthGuard     $guard;
    private ModuleModel   $modules;
    private AuditLogModel $logs;

    public function __construct()
    {
        parent::__construct();
        $this->guard   = new AuthGuard();
        $this->modules = new ModuleModel();
        $this->logs    = new AuditLogModel();
    }

    public function run(): void
    {
        $this->guard->requireAdmin();
        $msg = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $msg = $this->handleToggle();
        }

        $this->render('admin/modules', [
            'pageTitle'  => 'Module Control',
            'activePage' => 'modules',
            'csrf'       => $this->guard->csrfToken(),
            'msg'        => $msg,
            'modules'    => $this->modules->getAll(),
            'activeTab'  => $this->resolveTab(),
        ]);
    }

    // UPDATE: toggle a module on/off
    private function handleToggle(): string
    {
        $this->guard->verifyCsrf();
        $key     = $_POST['module_key'] ?? '';
        $enabled = isset($_POST['enabled']);
        $this->modules->setEnabled($key, $enabled, $_SESSION['user_id']);
        $action = $enabled ? 'Module enabled' : 'Module disabled';
        $this->logs->write(
            $_SESSION['user_id'], $_SESSION['username'], 'admin',
            $action, "Module: $key", $_SERVER['REMOTE_ADDR'] ?? ''
        );
        return 'Module updated successfully.';
    }

    private function resolveTab(): string
    {
        $valid = ['all', 'student', 'instructor', 'dept_head', 'registrar', 'auto'];
        $tab   = $_GET['tab'] ?? 'all';
        return in_array($tab, $valid) ? $tab : 'all';
    }
}
