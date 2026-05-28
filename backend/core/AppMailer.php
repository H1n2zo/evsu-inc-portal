<?php
// core/AppMailer.php
// Service Layer — named notification methods for every INC workflow step.
// Each method corresponds to one state transition in the workflow.
// Controllers call these after a successful updateStep().

require_once __DIR__ . '/MailerService.php';

class AppMailer
{
    private MailerService $mailer;

    public function __construct()
    {
        $this->mailer = new MailerService();
    }

    // ── Step 1 → 2: Student filed application ────────────────────
    // Notify the assigned instructor that a new INC has been filed.
    public function notifyApplicationFiled(array $app, string $instructorEmail, string $instructorName): bool
    {
        return $this->mailer->send(
            to:      $instructorEmail,
            toName:  $instructorName,
            subject: "[INC Portal] New INC Application Assigned — {$app['app_code']}",
            body:    "
                <p>Dear {$instructorName},</p>
                <p>A student has filed a new INC completion application and assigned it to you for grade entry.</p>
                <table style='width:100%;border-collapse:collapse;font-size:13px;'>
                  <tr><td style='padding:6px 0;color:#A09080;width:40%'>Application</td><td style='font-weight:600'>{$app['app_code']}</td></tr>
                  <tr><td style='padding:6px 0;color:#A09080'>Student</td><td>{$app['full_name']}</td></tr>
                  <tr><td style='padding:6px 0;color:#A09080'>Subject</td><td>{$app['subject_name']} ({$app['subject_code']})</td></tr>
                  <tr><td style='padding:6px 0;color:#A09080'>Units</td><td>{$app['units']} units</td></tr>
                  <tr><td style='padding:6px 0;color:#A09080'>Semester</td><td>{$app['semester']} Semester, {$app['school_year']}</td></tr>
                </table>
                <p style='margin-top:24px;'>Please log in to the INC Portal to enter the resolved final grade and apply your e-signature.</p>
            "
        );
    }

    // ── Step 2 → 3: Instructor signed ────────────────────────────
    // Notify the dept head that a grade is ready for review.
    public function notifyInstructorSigned(array $app, string $deptHeadEmail, string $deptHeadName): bool
    {
        return $this->mailer->send(
            to:      $deptHeadEmail,
            toName:  $deptHeadName,
            subject: "[INC Portal] Grade Ready for Review — {$app['app_code']}",
            body:    "
                <p>Dear {$deptHeadName},</p>
                <p>The instructor has entered and signed the resolved final grade for the following INC application:</p>
                <table style='width:100%;border-collapse:collapse;font-size:13px;'>
                  <tr><td style='padding:6px 0;color:#A09080;width:40%'>Application</td><td style='font-weight:600'>{$app['app_code']}</td></tr>
                  <tr><td style='padding:6px 0;color:#A09080'>Student</td><td>{$app['full_name']}</td></tr>
                  <tr><td style='padding:6px 0;color:#A09080'>Subject</td><td>{$app['subject_name']} ({$app['subject_code']})</td></tr>
                  <tr><td style='padding:6px 0;color:#A09080'>Resolved Grade</td><td style='font-weight:600'>{$app['instructor_grade']}</td></tr>
                </table>
                <p style='margin-top:24px;'>Please log in to the INC Portal to review and approve or reject this application.</p>
            "
        );
    }

    // ── Step 3 → 4: Dept head approved ───────────────────────────
    // Notify the student to submit payment.
    public function notifyDeptHeadApproved(array $app, string $studentEmail, string $studentName): bool
    {
        $fee = '₱' . number_format($app['processing_fee'], 0);
        return $this->mailer->send(
            to:      $studentEmail,
            toName:  $studentName,
            subject: "[INC Portal] Action Required — Upload Payment Receipt ({$app['app_code']})",
            body:    "
                <p>Dear {$studentName},</p>
                <p>Your INC application <strong>{$app['app_code']}</strong> for <strong>{$app['subject_name']}</strong>
                has been approved by the Department Head.</p>
                <p>Please pay the processing fee of <strong>{$fee}</strong> at the cashier and upload your
                Official Receipt in the portal to continue.</p>
                <p style='margin-top:24px;'>Log in to the INC Portal → My Applications → Upload Receipt.</p>
            "
        );
    }

    // ── Step 3 → rejected: Dept head rejected ────────────────────
    // Notify the student of the rejection reason.
    public function notifyDeptHeadRejected(array $app, string $studentEmail, string $studentName): bool
    {
        $reason = htmlspecialchars($app['dept_head_remarks'] ?? 'No reason provided.', ENT_QUOTES, 'UTF-8');
        return $this->mailer->send(
            to:      $studentEmail,
            toName:  $studentName,
            subject: "[INC Portal] Application Rejected — {$app['app_code']}",
            body:    "
                <p>Dear {$studentName},</p>
                <p>Your INC application <strong>{$app['app_code']}</strong> for <strong>{$app['subject_name']}</strong>
                has been <strong style='color:#C0392B'>rejected</strong> by the Department Head.</p>
                <p><strong>Reason:</strong> {$reason}</p>
                <p>If you believe this is an error, please contact your Department Head directly.</p>
            "
        );
    }

    // ── Step 4 → 5: Student uploaded receipt ─────────────────────
    // Notify the registrar that a receipt is waiting for verification.
    public function notifyReceiptUploaded(array $app, string $registrarEmail, string $registrarName): bool
    {
        return $this->mailer->send(
            to:      $registrarEmail,
            toName:  $registrarName,
            subject: "[INC Portal] Receipt Uploaded — {$app['app_code']}",
            body:    "
                <p>Dear {$registrarName},</p>
                <p>A student has uploaded their payment receipt for the following INC application:</p>
                <table style='width:100%;border-collapse:collapse;font-size:13px;'>
                  <tr><td style='padding:6px 0;color:#A09080;width:40%'>Application</td><td style='font-weight:600'>{$app['app_code']}</td></tr>
                  <tr><td style='padding:6px 0;color:#A09080'>Student</td><td>{$app['full_name']}</td></tr>
                  <tr><td style='padding:6px 0;color:#A09080'>Subject</td><td>{$app['subject_name']}</td></tr>
                  <tr><td style='padding:6px 0;color:#A09080'>O.R. Number</td><td style='font-weight:600'>{$app['or_number']}</td></tr>
                  <tr><td style='padding:6px 0;color:#A09080'>Amount</td><td>₱" . number_format($app['processing_fee'], 0) . "</td></tr>
                </table>
                <p style='margin-top:24px;'>Please log in to verify the receipt against the official ledger.</p>
            "
        );
    }

    // ── Step 5 → 6: Registrar verified O.R. ──────────────────────
    // Notify student that receipt has been verified.
    public function notifyOrVerified(array $app, string $studentEmail, string $studentName): bool
    {
        return $this->mailer->send(
            to:      $studentEmail,
            toName:  $studentName,
            subject: "[INC Portal] Receipt Verified — {$app['app_code']}",
            body:    "
                <p>Dear {$studentName},</p>
                <p>Your payment receipt for <strong>{$app['app_code']}</strong> has been verified.
                The registrar will now post your final grade.</p>
                <p>No further action is required from you at this time.</p>
            "
        );
    }

    // ── Step 5 → rejected: Registrar rejected O.R. ───────────────
    public function notifyOrRejected(array $app, string $studentEmail, string $studentName): bool
    {
        $reason = htmlspecialchars($app['registrar_remarks'] ?? 'No reason provided.', ENT_QUOTES, 'UTF-8');
        return $this->mailer->send(
            to:      $studentEmail,
            toName:  $studentName,
            subject: "[INC Portal] Receipt Rejected — {$app['app_code']}",
            body:    "
                <p>Dear {$studentName},</p>
                <p>Your payment receipt for <strong>{$app['app_code']}</strong> was <strong style='color:#C0392B'>rejected</strong>
                by the Registrar.</p>
                <p><strong>Reason:</strong> {$reason}</p>
                <p>Please resubmit a valid Official Receipt through the portal.</p>
            "
        );
    }

    // ── Step 6 → 7: Grade posted, resolved ───────────────────────
    public function notifyResolved(array $app, string $studentEmail, string $studentName): bool
    {
        return $this->mailer->send(
            to:      $studentEmail,
            toName:  $studentName,
            subject: "[INC Portal] INC Resolved — {$app['app_code']}",
            body:    "
                <p>Dear {$studentName},</p>
                <p>Congratulations! Your INC application <strong>{$app['app_code']}</strong> for
                <strong>{$app['subject_name']}</strong> has been fully resolved.</p>
                <p>Your final grade of <strong style='font-size:18px'>{$app['instructor_grade']}</strong>
                has been officially posted to your academic record.</p>
                <p>No further action is required from you.</p>
            "
        );
    }
}