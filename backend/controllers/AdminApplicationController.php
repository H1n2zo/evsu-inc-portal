<?php
// controllers/AdminApplicationController.php
// Logic Layer — admin application list: READ all with pagination
// OOP Concepts:
//   - Inheritance: extends ApplicationController
//   - Polymorphism: overrides buildViewData() and getViewTemplate()
//   - NO static properties — PER_PAGE is a plain instance property

require_once __DIR__ . '/ApplicationController.php';

class AdminApplicationController extends ApplicationController
{
    private int $perPage = 20;

    protected function authorize(): void
    {
        $this->guard->requireAdmin();
    }

    protected function getViewTemplate(): string
    {
        return 'admin/applications';
    }

    protected function buildViewData(): array
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => trim($_GET['q'] ?? ''),
            'limit'  => $this->perPage,
            'offset' => ($page - 1) * $this->perPage,
        ];

        $total = $this->apps->countAll($filters);

        return [
            'apps'         => $this->apps->getAll($filters),
            'statusCounts' => $this->apps->getStatusCounts(),
            'page'         => $page,
            'pages'        => (int)ceil($total / $this->perPage),
            'total'        => $total,
            'filters'      => $filters,
        ];
    }
}
