<?php
// controllers/DeptReviewController.php
// Logic Layer — Dept. Head: applications at Step 3 awaiting review
// OOP Concepts:
//   - Inheritance: extends Controller
//   - Encapsulation: role guard + filtered data fetch are private concerns

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/ApplicationModel.php';

class DeptReviewController extends Controller
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

        if ($_SESSION['active_role'] !== 'dept_head') {
            $this->redirect('employee/dashboard.php');
        }

        $uid    = $_SESSION['user_id'];
        $search = trim($_GET['q'] ?? '');

        // READ: Step-3 in_progress apps assigned to this dept_head
        $apps = $this->apps->getForEmployee('dept_head', $uid, [
            'step'   => 3,
            'status' => 'in_progress',
            'search' => $search,
        ]);

        $this->render('employee/dept_review', [
            'pageTitle'  => 'Department Review',
            'activePage' => 'dept_review',
            'csrf'       => $this->guard->csrfToken(),
            'apps'       => $apps,
            'search'     => $search,
            'total'      => count($apps),
        ]);
    }
}
