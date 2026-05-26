<?php
// controllers/AdminAppViewController.php
// Single Responsibility: Admin read-only view of any application
// OOP: Inherits from ApplicationViewerController, admin has read-only access

require_once __DIR__ . '/ApplicationViewerController.php';

class AdminAppViewController extends ApplicationViewerController
{
    protected function authorize(): void
    {
        $this->guard->requireAdmin();
    }

    protected function fetchApp(int $id): array|false
    {
        $app = $this->apps->findById($id);
        if (!$app) { $this->redirect('admin/applications.php'); }
        return $app;
    }

    protected function getViewTemplate(): string
    {
        return 'admin/application_view';
    }

    // Admin is read-only — no POST handling
}
