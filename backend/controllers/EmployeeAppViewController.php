<?php
// controllers/EmployeeAppViewController.php
// Fix: fetchApp() now also allows access when instructor_id matches even if
//      the app is still at step 1 (just filed). instructorSign() correctly
//      advances current_step from 2 → 3.

require_once __DIR__ . '/ApplicationViewerController.php';

class EmployeeAppViewController extends ApplicationViewerController
{
    protected function authorize(): void
    {
        $this->guard->requireEmployee();
    }

    protected function fetchApp(int $id): array|false
    {
        $app  = $this->apps->findById($id);
        if (!$app) { $this->redirect('employee/applications.php'); }

        $uid  = $_SESSION['user_id'];
        $role = $_SESSION['active_role'];

        // Grant access if this employee is assigned to this app in their role
        $ok = ($role === 'instructor' && (int)$app['instructor_id'] === $uid)
           || ($role === 'dept_head'  && (int)$app['dept_head_id']  === $uid)
           ||  $role === 'registrar';

        if (!$ok) {
            // Redirect instead of hard 403 so the instructor sees a clear message
            $this->redirect('employee/applications.php');
        }
        return $app;
    }

    protected function getViewTemplate(): string
    {
        return 'employee/application_view';
    }

    protected function handlePost(array &$app): string
    {
        $this->guard->verifyCsrf();
        $action = $_POST['action'] ?? '';
        $uid    = $_SESSION['user_id'];
        $role   = $_SESSION['active_role'];

        // Step 2: instructor enters grade and signs
        if ($action === 'instructor_sign' && $role === 'instructor' && (int)$app['current_step'] === 2) {
            return $this->instructorSign($app, $uid);
        }
        // Step 3: dept head approves or rejects
        if (in_array($action, ['depthead_approve', 'depthead_reject']) && $role === 'dept_head' && (int)$app['current_step'] === 3) {
            return $this->deptHeadReview($app, $action, $uid);
        }
        // Step 5: registrar verifies OR
        if (in_array($action, ['registrar_verify', 'registrar_reject_or']) && $role === 'registrar' && (int)$app['current_step'] === 5) {
            return $this->registrarVerify($app, $action, $uid);
        }
        // Step 6: registrar posts grade
        if ($action === 'post_grade' && $role === 'registrar' && (int)$app['current_step'] === 6) {
            return $this->postGrade($app, $uid);
        }
        return '';
    }

    // ── Step 2 → 3: Instructor enters grade and signs ──────────────

    private function instructorSign(array $app, int $uid): string
    {
        $grade   = trim($_POST['grade'] ?? '');
        $remarks = trim($_POST['remarks'] ?? '');
        $sig     = $_POST['signature_data'] ?? '';

        if (!$grade)                    return 'error:Please enter the resolved final grade.';
        if (!$sig || strlen($sig) < 50) return 'error:Please provide your e-signature.';

        $this->apps->updateStep($app['id'], [
            'instructor_grade'     => $grade,
            'instructor_remarks'   => $remarks,
            'instructor_signature' => $sig,
            'instructor_signed_at' => date('Y-m-d H:i:s'),
            'current_step'         => 3,   // advance to dept head review
            'status'               => 'in_progress',
        ]);
        $this->logs->write($uid, $_SESSION['username'], 'instructor',
            'Grade Input + E-Sign', "App {$app['app_code']} — Grade: $grade", $_SERVER['REMOTE_ADDR'] ?? '');
        return 'Grade entered and signed. Application forwarded to Department Head.';
    }

    // ── Step 3 → 4 or rejected: Dept Head review ───────────────────

    private function deptHeadReview(array $app, string $action, int $uid): string
    {
        $remarks = trim($_POST['remarks'] ?? '');
        $sig     = $_POST['signature_data'] ?? '';

        if ($action === 'depthead_approve') {
            if (!$sig || strlen($sig) < 50) return 'error:Please provide your e-signature.';
            $this->apps->updateStep($app['id'], [
                'dept_head_remarks'   => $remarks,
                'dept_head_signature' => $sig,
                'dept_head_signed_at' => date('Y-m-d H:i:s'),
                'dept_head_action'    => 'approved',
                'current_step'        => 4,
                'status'              => 'pending_payment',
            ]);
            $this->logs->write($uid, $_SESSION['username'], 'dept_head',
                'Dept Head Approved', "App {$app['app_code']}", $_SERVER['REMOTE_ADDR'] ?? '');
            return 'Application approved. Student notified to submit payment.';
        } else {
            if (!$remarks) return 'error:Please provide a rejection reason.';
            $this->apps->updateStep($app['id'], [
                'dept_head_remarks'   => $remarks,
                'dept_head_action'    => 'rejected',
                'dept_head_signed_at' => date('Y-m-d H:i:s'),
                'status'              => 'rejected',
                'rejection_reason'    => $remarks,
                'rejected_at'         => date('Y-m-d H:i:s'),
                'rejected_by'         => $uid,
            ]);
            $this->logs->write($uid, $_SESSION['username'], 'dept_head',
                'Dept Head Rejected', "App {$app['app_code']}: $remarks", $_SERVER['REMOTE_ADDR'] ?? '');
            return 'Application rejected. Student notified.';
        }
    }

    // ── Step 5 → 6 or rejected: Registrar verifies OR ──────────────

    private function registrarVerify(array $app, string $action, int $uid): string
    {
        $remarks = trim($_POST['remarks'] ?? '');
        if ($action === 'registrar_verify') {
            $this->apps->updateStep($app['id'], [
                'registrar_id'      => $uid,
                'registrar_remarks' => $remarks,
                'registrar_action'  => 'approved',
                'current_step'      => 6,
                'status'            => 'verification',
            ]);
            $this->logs->write($uid, $_SESSION['username'], 'registrar',
                'O.R. Verified', "App {$app['app_code']}", $_SERVER['REMOTE_ADDR'] ?? '');
            return 'O.R. verified. Proceed to grade posting.';
        } else {
            if (!$remarks) return 'error:Please provide a rejection reason.';
            $this->apps->updateStep($app['id'], [
                'registrar_id'     => $uid,
                'registrar_remarks'=> $remarks,
                'registrar_action' => 'rejected',
                'status'           => 'rejected',
                'rejection_reason' => $remarks,
                'rejected_at'      => date('Y-m-d H:i:s'),
                'rejected_by'      => $uid,
            ]);
            $this->logs->write($uid, $_SESSION['username'], 'registrar',
                'O.R. Rejected', "App {$app['app_code']}: $remarks", $_SERVER['REMOTE_ADDR'] ?? '');
            return 'O.R. rejected. Student notified to resubmit.';
        }
    }

    // ── Step 6 → 7 resolved: Registrar posts grade ─────────────────

    private function postGrade(array $app, int $uid): string
    {
        $sig     = $_POST['signature_data'] ?? '';
        $remarks = trim($_POST['remarks'] ?? '');
        if (!$sig || strlen($sig) < 50) return 'error:Please provide your e-signature.';
        $this->apps->updateStep($app['id'], [
            'registrar_id'        => $uid,
            'registrar_signature' => $sig,
            'registrar_signed_at' => date('Y-m-d H:i:s'),
            'registrar_remarks'   => $remarks,
            'current_step'        => 7,
            'status'              => 'resolved',
            'resolved_at'         => date('Y-m-d H:i:s'),
        ]);
        $this->logs->write($uid, $_SESSION['username'], 'registrar',
            'Grade Posted', "App {$app['app_code']} — Final grade posted", $_SERVER['REMOTE_ADDR'] ?? '');
        return 'Grade posted successfully. Application marked as Resolved.';
    }
}