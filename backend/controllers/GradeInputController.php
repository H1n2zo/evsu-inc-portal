<?php
// controllers/GradeInputController.php
// Logic Layer — Instructor: applications at Step 2 awaiting grade entry
// OOP Concepts:
//   - Inheritance: extends Controller
//   - Encapsulation: role guard + filtered data fetch are private concerns

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/ApplicationModel.php';

class GradeInputController extends Controller
{
    private AuthGuard        $guard;
    private ApplicationModel $apps;

    public function __construct()
    {
        parent::__construct();
        $this->guard = new AuthGuard();
        $this->apps  = new ApplicationModel();
    }

    public function run(): void
    {
        $this->guard->requireEmployee();

        if ($_SESSION['active_role'] !== 'instructor') {
            $this->redirect('employee/dashboard.php');
        }

        $uid    = $_SESSION['user_id'];
        $search = trim($_GET['q'] ?? '');

        // READ: Step-2 in_progress apps assigned to this instructor
        $apps = $this->apps->getForEmployee('instructor', $uid, [
            'step'   => 2,
            'status' => 'in_progress',
            'search' => $search,
        ]);

        $this->render('employee/grade_input', [
            'pageTitle'  => 'Grade Input',
            'activePage' => 'grade_input',
            'csrf'       => $this->guard->csrfToken(),
            'apps'       => $apps,
            'search'     => $search,
            'total'      => count($apps),
        ]);
    }
}
