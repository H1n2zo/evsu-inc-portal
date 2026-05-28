<?php
// controllers/ForgotPasswordController.php
// Single Responsibility: Handle the 3-step forgot password flow
// Steps: 1=enter email, 2=enter OTP, 3=new password

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../core/MailerService.php';
require_once __DIR__ . '/../models/UserModel.php';

class ForgotPasswordController extends Controller
{
    private AuthGuard     $guard;
    private UserModel     $users;
    private MailerService $mailer;

    public function __construct()
    {
        parent::__construct();
        $this->guard  = new AuthGuard();
        $this->users  = new UserModel();
        $this->mailer = new MailerService();
    }

    public function run(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('index.php');
        }

        $step  = (int)($_SESSION['reset_step'] ?? 1);
        $error = '';
        $msg   = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guard->verifyCsrf();

            if ($step === 1) {
                [$error, $msg, $step] = $this->handleEmailStep();
            } elseif ($step === 2) {
                [$error, $msg, $step] = $this->handleOtpStep();
            } elseif ($step === 3) {
                [$error, $msg, $step] = $this->handlePasswordStep();
            }
        }

        $this->render('forgot_password', [
            'pageTitle'   => 'Reset Password',
            'csrf'        => $this->guard->csrfToken(),
            'step'        => $step,
            'error'       => $error,
            'msg'         => $msg,
            'maskedEmail' => $this->maskEmail($_SESSION['reset_email'] ?? ''),
        ]);
    }

    private function handleEmailStep(): array
    {
        $email = trim(strtolower($_POST['email'] ?? ''));

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['Please enter a valid email address.', '', 1];
        }

        $safeMsg = 'If that email is registered, a 6-digit OTP has been sent to it.';
        $user    = $this->users->findByEmail($email);

        if (!$user || $user['status'] !== 'active') {
            $_SESSION['reset_step']  = 2;
            $_SESSION['reset_email'] = $email;
            return ['', $safeMsg, 2];
        }

        $otp     = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $this->users->clearPasswordResets($email);
        $this->users->createPasswordReset(
            $email,
            password_hash($otp, PASSWORD_BCRYPT),
            $expires
        );

        $this->mailer->send(
            to:      $user['email'],
            toName:  $user['full_name'],
            subject: '[INC Portal] Your Password Reset OTP',
            body:    "
                <p>Dear {$user['full_name']},</p>
                <p>You requested a password reset. Use the OTP below.
                   It expires in <strong>15 minutes</strong>.</p>
                <div style='text-align:center;margin:2rem 0;'>
                  <span style='font-family:monospace;font-size:36px;font-weight:700;
                               letter-spacing:0.2em;color:#6B0F1A;background:#FEF3E6;
                               padding:16px 28px;border-radius:8px;display:inline-block;'>
                    {$otp}
                  </span>
                </div>
                <p>If you did not request this, you can safely ignore this email.</p>
            "
        );

        $_SESSION['reset_step']  = 2;
        $_SESSION['reset_email'] = $email;

        return ['', $safeMsg, 2];
    }

    private function handleOtpStep(): array
    {
        $otp   = trim($_POST['otp'] ?? '');
        $email = $_SESSION['reset_email'] ?? '';

        if (!$otp || !$email) {
            return ['Invalid session. Please start over.', '', 1];
        }

        $reset = $this->users->findValidPasswordReset($email);

        if (!$reset) {
            return ['OTP expired or already used. Please request a new one.', '', 1];
        }

        if (!password_verify($otp, $reset['otp_hash'])) {
            return ['Incorrect OTP. Please check your email and try again.', '', 2];
        }

        $_SESSION['reset_step']     = 3;
        $_SESSION['reset_token_id'] = $reset['id'];

        return ['', '', 3];
    }

    private function handlePasswordStep(): array
    {
        $password = $_POST['password']         ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $email    = $_SESSION['reset_email']   ?? '';
        $tokenId  = (int)($_SESSION['reset_token_id'] ?? 0);

        if (!$email || !$tokenId) {
            return ['Session expired. Please start over.', '', 1];
        }

        if (strlen($password) < 8) {
            return ['Password must be at least 8 characters.', '', 3];
        }

        if ($password !== $confirm) {
            return ['Passwords do not match.', '', 3];
        }

        $user = $this->users->findByEmail($email);
        if (!$user) {
            return ['Account not found. Please start over.', '', 1];
        }

        $this->users->updatePassword(
            $user['id'],
            password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])
        );
        $this->users->markResetUsed($tokenId);

        unset(
            $_SESSION['reset_step'],
            $_SESSION['reset_email'],
            $_SESSION['reset_token_id']
        );

        return ['', 'PASSWORD_CHANGED', 0];
    }

    private function maskEmail(string $email): string
    {
        if (!$email || !str_contains($email, '@')) return '';
        [$local, $domain] = explode('@', $email, 2);
        $visible = substr($local, 0, 1);
        return $visible . str_repeat('*', max(3, strlen($local) - 1)) . '@' . $domain;
    }
}