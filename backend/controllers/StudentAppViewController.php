<?php
// controllers/StudentAppViewController.php
// Single Responsibility: Student view of their own application + payment upload
// OOP: Inherits from ApplicationViewerController; overrides handlePost() for payment

require_once __DIR__ . '/ApplicationViewerController.php';

class StudentAppViewController extends ApplicationViewerController
{
    protected function authorize(): void
    {
        $this->guard->requireStudent();
    }

    protected function fetchApp(int $id): array|false
    {
        $app = $this->apps->findByIdForStudent($id, $_SESSION['user_id']);
        if (!$app) { $this->redirect('student/applications.php'); }
        return $app;
    }

    protected function getViewTemplate(): string
    {
        return 'student/application_view';
    }

    // UPDATE: student uploads payment receipt (step 4 → step 5)
    protected function handlePost(array &$app): string
    {
        $this->guard->verifyCsrf();
        $uid = $_SESSION['user_id'];

        if ($app['current_step'] != 4 || $app['student_id'] != $uid) {
            return 'error:Action not allowed at this stage.';
        }

        if (empty($_FILES['payment_receipt']['tmp_name'])) {
            return 'error:Please upload your payment receipt.';
        }

        $file    = $_FILES['payment_receipt'];
        $allowed = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($file['type'], $allowed)) {
            return 'error:Only JPG, PNG, or PDF files are accepted.';
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            return 'error:File must be under 5MB.';
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'receipt_' . $app['app_code'] . '_' . time() . '.' . $ext;
        $dest     = __DIR__ . '/../assets/uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return 'error:Upload failed. Please try again.';
        }

        $this->apps->updateStep($app['id'], [
            'payment_receipt' => $filename,
            'current_step'    => 5,
            'status'          => 'verification',
        ]);
        $this->logs->write($uid, $_SESSION['username'], 'student',
            'Payment Uploaded', "App {$app['app_code']}", $_SERVER['REMOTE_ADDR'] ?? '');
        return 'Payment receipt uploaded. Your application is now under verification.';
    }
}
