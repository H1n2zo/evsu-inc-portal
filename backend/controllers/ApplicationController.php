<?php
// controllers/ApplicationController.php
// Single Responsibility: Base for application-listing pages
// OOP Concepts:
//   - Polymorphism: subclasses override authorize(), loadApps(), getViewTemplate()
//   - Template Method: run() calls abstract hooks implemented by subclasses

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/ApplicationModel.php';

abstract class ApplicationController extends Controller
{
    protected AuthGuard        $guard;
    protected ApplicationModel $apps;

    // Shared lookup maps — passed to views as data, never computed in view
    protected array $stepLabels = [
        1 => 'Student Filing',   2 => 'Instructor Input',
        3 => 'Dept. Head Review', 4 => 'Payment Upload',
        5 => 'Registrar Verify', 6 => 'Grade Posting', 7 => 'Resolved',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->guard = new AuthGuard();
        $this->apps  = new ApplicationModel();
    }

    abstract protected function authorize(): void;
    abstract protected function buildViewData(): array;
    abstract protected function getViewTemplate(): string;

    // Template Method: same run() for all role app-list pages
    public function run(): void
    {
        $this->authorize();
        $data = array_merge(
            [
                'pageTitle'  => 'Applications',
                'activePage' => 'applications',
                'csrf'       => $this->guard->csrfToken(),
                'stepLabels' => $this->stepLabels,
            ],
            $this->buildViewData()
        );
        $this->render($this->getViewTemplate(), $data);
    }
}
