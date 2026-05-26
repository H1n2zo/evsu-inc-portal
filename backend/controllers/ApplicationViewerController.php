<?php
// controllers/ApplicationViewerController.php
// Logic Layer — base for single-application detail + workflow action pages
// OOP Concepts:
//   - Polymorphism: role subclasses override fetchApp(), handlePost(), getViewTemplate()
//   - Template Method: run() is shared, hooks are implemented by each subclass

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/ApplicationModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';
require_once __DIR__ . '/../models/SettingsModel.php';

abstract class ApplicationViewerController extends Controller
{
    protected AuthGuard        $guard;
    protected ApplicationModel $apps;
    protected AuditLogModel    $logs;
    protected SettingsModel    $settings;

    protected array $stepLabels = [
        1 => 'Student Filing',    2 => 'Instructor Input',
        3 => 'Dept. Head Review', 4 => 'Payment Upload',
        5 => 'Registrar Verify',  6 => 'Grade Posting', 7 => 'Resolved',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->guard    = new AuthGuard();
        $this->apps     = new ApplicationModel();
        $this->logs     = new AuditLogModel();
        $this->settings = new SettingsModel();
    }

    abstract protected function authorize(): void;
    abstract protected function fetchApp(int $id): array|false;
    abstract protected function getViewTemplate(): string;

    // Default: no-op; subclasses override for workflow actions
    protected function handlePost(array &$app): string { return ''; }

    public function run(): void
    {
        $this->authorize();
        $appId = (int)($_GET['id'] ?? 0);
        $app   = $this->fetchApp($appId);

        $msg   = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->handlePost($app);
            if (str_starts_with($result, 'error:')) {
                $error = substr($result, 6);
            } else {
                $msg = $result;
                $app = $this->fetchApp($appId); // Refresh after action
            }
        }

        $this->render($this->getViewTemplate(), [
            'pageTitle'    => 'Application ' . ($app['app_code'] ?? ''),
            'activePage'   => 'applications',
            'csrf'         => $this->guard->csrfToken(),
            'app'          => $app,
            'msg'          => $msg,
            'error'        => $error,
            'stepLabels'   => $this->stepLabels,
            'activeRole'   => $_SESSION['active_role'] ?? '',
            'filed'        => isset($_GET['filed']) ? (int)$_GET['filed'] : 0,
            // settingsData for upload size hint in student view
            'settingsData' => ['max_upload_mb' => $this->settings->get('max_upload_mb') ?? 5],
        ]);
    }
}
