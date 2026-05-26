<?php
// controllers/OrVerifyController.php
// Logic Layer — Registrar: applications at Step 5 awaiting O.R. verification
// OOP Concepts:
//   - Inheritance: extends Controller
//   - Encapsulation: role guard + filtered data fetch are private concerns

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/ApplicationModel.php';

class OrVerifyController extends Controller
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

        // READ: Step-5 verification apps (registrar sees all — uid not needed)
        $apps = $this->apps->getForEmployee('registrar', 0, [
            'step'   => 5,
            'status' => 'verification',
            'search' => $search,
        ]);

        $this->render('employee/or_verify', [
            'pageTitle'  => 'O.R. Verification',
            'activePage' => 'or_verify',
            'csrf'       => $this->guard->csrfToken(),
            'apps'       => $apps,
            'search'     => $search,
            'total'      => count($apps),
        ]);
    }
}
