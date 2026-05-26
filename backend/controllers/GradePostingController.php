<?php
// controllers/GradePostingController.php
// Logic Layer — Registrar: applications at Step 6 ready for final grade posting
// OOP Concepts:
//   - Inheritance: extends Controller
//   - Encapsulation: role guard + filtered data fetch are private concerns

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/ApplicationModel.php';

class GradePostingController extends Controller
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

        if ($_SESSION['active_role'] !== 'registrar') {
            $this->redirect('employee/dashboard.php');
        }

        $search = trim($_GET['q'] ?? '');

        // READ: Step-6 apps ready for grade posting (registrar sees all)
        $apps = $this->apps->getForEmployee('registrar', 0, [
            'step'   => 6,
            'search' => $search,
        ]);

        $this->render('employee/grade_posting', [
            'pageTitle'  => 'Grade Posting',
            'activePage' => 'grade_posting',
            'csrf'       => $this->guard->csrfToken(),
            'apps'       => $apps,
            'search'     => $search,
            'total'      => count($apps),
        ]);
    }
}
