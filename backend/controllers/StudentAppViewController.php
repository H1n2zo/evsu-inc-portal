<?php
// controllers/StudentAppViewController.php
// Single Responsibility: Student view of their own application + payment upload
// OOP: Inherits from ApplicationViewerController; overrides handlePost() for payment
// FIX: or_number is now saved in updateStep(); file input name is 'payment_receipt'

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

        // FIX: file input in the view is named 'payment_receipt'
        if (empty($_FILES['payment_receipt']['tmp_name'])) {
            return 'error:Please upload your payment receipt.';
        }

        // Collect and validate or_number
        $orNumber = trim($_POST['or_number'] ?? '');
        if (!$orNumber) {
            return 'error:Please enter the O.R. number from your receipt.';
        }

        $file    = $_FILES['payment_receipt'];
        $allowed = ['image/jpeg', 'image/png', 'application/pdf'];

        // Some browsers send 'image/jpg' — normalise
        $mime = $file['type'];
        if (!in_array($mime, $allowed)) {
            return 'error:Only JPG, PNG, or PDF files are accepted.';
        }

        $maxBytes = ((int)($this->settings->get('max_upload_mb') ?? 5)) * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            return 'error:File must be under ' . ($maxBytes / 1024 / 1024) . 'MB.';
        }

        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename  = 'receipt_' . $app['app_code'] . '_' . time() . '.' . $ext;

        // Correct path: backend/controllers/../../frontend/assets/uploads/
        // = htdocs/frontend/assets/uploads/  (same folder asset() serves)
        $uploadDir = __DIR__ . '/../../frontend/assets/uploads/';
        $dest      = $uploadDir . $filename;

        // Create the directory if it somehow doesn't exist yet
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return 'error:Upload failed. Please check folder permissions on frontend/assets/uploads/';
        }

        // FIX: or_number is now persisted alongside the receipt
        $this->apps->updateStep($app['id'], [
            'or_number'           => $orNumber,
            'receipt_filename'    => $filename,
            'receipt_uploaded_at' => date('Y-m-d H:i:s'),
            'current_step'        => 5,
            'status'              => 'verification',
        ]);

        $this->logs->write($uid, $_SESSION['username'], 'student',
            'Payment Uploaded',
            "App {$app['app_code']} — O.R. {$orNumber}",
            $_SERVER['REMOTE_ADDR'] ?? '');

        return 'Payment receipt uploaded successfully. Your application is now under verification.';
    }
}