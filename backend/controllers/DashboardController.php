<?php
// controllers/DashboardController.php
// Single Responsibility: Base dashboard — defines shared dashboard contract
// OOP Concepts:
//   - Polymorphism: each role's dashboard overrides getStats() and getRecentItems()
//   - Template Method pattern: run() calls abstract methods implemented by subclasses
//   - Inheritance: Admin/Employee/StudentDashboardController all extend this

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/ApplicationModel.php';

abstract class DashboardController extends Controller
{
    protected AuthGuard        $guard;
    protected ApplicationModel $apps;

    public function __construct()
    {
        parent::__construct();
        $this->guard = new AuthGuard();
        $this->apps  = new ApplicationModel();
    }

    // Template Method: shared run() calls the polymorphic methods
    public function run(): void
    {
        $this->authorize();

        // Collect all data from subclass implementations
        $viewData = array_merge(
            ['csrf' => $this->guard->csrfToken()],
            $this->getData()
        );

        $this->render($this->getViewTemplate(), $viewData);
    }

    // Subclasses must define which view template to render
    abstract protected function getViewTemplate(): string;

    // Subclasses must define what data to pass to the view
    abstract protected function getData(): array;

    // Subclasses must define auth enforcement
    abstract protected function authorize(): void;
}
