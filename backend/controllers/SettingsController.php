<?php
// controllers/SettingsController.php
// Logic Layer — admin system settings page: READ + UPDATE
// OOP Concepts:
//   - Inheritance: extends Controller
//   - Encapsulation: allowed keys list is a private instance property (not static)

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/SettingsModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';

class SettingsController extends Controller
{
    private AuthGuard     $guard;
    private SettingsModel $settings;
    private AuditLogModel $logs;

    // Instance property — not static; encapsulates the whitelist of safe keys
    private array $allowedKeys = [
        'school_year', 'active_semester', 'session_timeout',
        'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass',
        'smtp_from_name', 'max_upload_mb',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->guard    = new AuthGuard();
        $this->settings = new SettingsModel();
        $this->logs     = new AuditLogModel();
    }

    public function run(): void
    {
        $this->guard->requireAdmin();
        $msg = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $msg = $this->handleUpdate();
        }

        $this->render('admin/settings', [
            'pageTitle'    => 'System Settings',
            'activePage'   => 'settings',
            'csrf'         => $this->guard->csrfToken(),
            'msg'          => $msg,
            'settingsData' => $this->settings->getAll(),
        ]);
    }

    // UPDATE: save whitelisted settings only
    private function handleUpdate(): string
    {
        $this->guard->verifyCsrf();
        $pairs = array_filter(
            array_intersect_key($_POST, array_flip($this->allowedKeys)),
            fn($v) => isset($v)
        );
        $this->settings->save(array_map('trim', $pairs));
        $this->logs->write(
            $_SESSION['user_id'], $_SESSION['username'], 'admin',
            'Settings Updated', 'System settings changed', $_SERVER['REMOTE_ADDR'] ?? ''
        );
        return 'Settings saved successfully.';
    }
}
