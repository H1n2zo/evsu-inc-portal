<?php
// controllers/StudentApplicationController.php
// Logic Layer — student application list: READ filtered by student ID
// OOP Concepts:
//   - Inheritance: extends ApplicationController
//   - Polymorphism: overrides buildViewData() for student scope

require_once __DIR__ . '/ApplicationController.php';

class StudentApplicationController extends ApplicationController
{
    protected function authorize(): void
    {
        $this->guard->requireStudent();
    }

    protected function getViewTemplate(): string
    {
        return 'student/applications';
    }

    protected function buildViewData(): array
    {
        $uid    = $_SESSION['user_id'];
        $status = $_GET['status'] ?? '';

        return [
            'apps'   => $this->apps->getForStudent($uid, $status),
            'status' => $status,
        ];
    }
}
