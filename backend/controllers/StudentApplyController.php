<?php
// controllers/StudentApplyController.php
// Logic Layer — handle the new INC application form: CREATE
// Fix: instructor_id and dept_head_id are now REQUIRED fields

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/ApplicationModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/ModuleModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';
require_once __DIR__ . '/../models/SettingsModel.php';

class StudentApplyController extends Controller
{
    private AuthGuard        $guard;
    private ApplicationModel $apps;
    private UserModel        $users;
    private AuditLogModel    $logs;
    private ModuleModel      $modules;
    private SettingsModel    $settings;

    public function __construct()
    {
        parent::__construct();
        $this->guard    = new AuthGuard();
        $this->apps     = new ApplicationModel();
        $this->users    = new UserModel();
        $this->logs     = new AuditLogModel();
        $this->modules  = new ModuleModel();
        $this->settings = new SettingsModel();
    }

    public function run(): void
    {
        $this->guard->requireStudent();

        if (!$this->modules->isEnabled('inc_filing')) {
            die('<div style="font-family:sans-serif;padding:2rem;color:#6B0F1A;">INC Form Filing is currently disabled.</div>');
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = $this->handleCreate();
        }

        $this->render('student/apply', [
            'pageTitle'   => 'New Application',
            'activePage'  => 'apply',
            'csrf'        => $this->guard->csrfToken(),
            'error'       => $error,
            'instructors' => $this->users->getInstructors(),
            'deptHeads'   => $this->users->getDeptHeads(),
        ]);
    }

    private function handleCreate(): string
    {
        $this->guard->verifyCsrf();
        $uid           = $_SESSION['user_id'];
        $subject_name  = trim($_POST['subject_name'] ?? '');
        $subject_code  = trim($_POST['subject_code'] ?? '');
        $units         = (int)($_POST['units'] ?? 3);
        $semester      = $_POST['semester'] ?? '2nd';
        $school_year   = $this->settings->get('school_year')
                         ?? date('Y') . '-' . (date('Y') + 1);
        $instructor_id = (int)($_POST['instructor_id'] ?? 0);
        $dept_head_id  = (int)($_POST['dept_head_id'] ?? 0);

        if (!$subject_name || !$subject_code || $units < 1) {
            return 'Please fill in all required fields.';
        }
        if ($units < 1 || $units > 6) {
            return 'Units must be between 1 and 6.';
        }
        // Both instructor and dept head are now required so the workflow can proceed
        if (!$instructor_id) {
            return 'Please select an instructor.';
        }
        if (!$dept_head_id) {
            return 'Please select a department head.';
        }
        if ($this->apps->isDuplicate($uid, $subject_code, $semester, $school_year)) {
            return "An active application for $subject_code this semester already exists.";
        }

        $newId = $this->apps->create([
            'student_id'    => $uid,
            'subject_name'  => $subject_name,
            'subject_code'  => $subject_code,
            'units'         => $units,
            'semester'      => $semester,
            'school_year'   => $school_year,
            'instructor_id' => $instructor_id,
            'dept_head_id'  => $dept_head_id,
        ]);

        $this->logs->write($uid, $_SESSION['username'], 'student',
            'Application Filed', "App — $subject_code", $_SERVER['REMOTE_ADDR'] ?? '');

        $this->redirect("student/application_view.php?id={$newId}&filed=1");
    }
}