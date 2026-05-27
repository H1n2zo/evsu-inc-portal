<?php
// core/MailerService.php
// Service Layer — sends transactional emails via PHPMailer
// Credentials are read from the settings table, never hardcoded.
// OOP: Composition — depends on SettingsModel; no static methods.
//
// Usage:
//   $mailer = new MailerService();
//   $mailer->send(
//       to:      'student@evsu.edu.ph',
//       toName:  'Juan Dela Cruz',
//       subject: 'Your INC Application Has Been Approved',
//       body:    '<p>Dear Juan, your application INC-0001 has been approved.</p>',
//   );

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/SettingsModel.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailerService
{
    private SettingsModel $settings;

    public function __construct()
    {
        $this->settings = new SettingsModel();
    }

    /**
     * Send a single transactional email.
     *
     * @param string $to       Recipient email address
     * @param string $toName   Recipient display name
     * @param string $subject  Email subject line
     * @param string $body     HTML email body
     * @param string $altBody  Plain-text fallback (auto-stripped from $body if empty)
     * @return bool            true on success, false on failure
     * @throws Exception       only if you call with $throw = true
     */
    public function send(
        string $to,
        string $toName,
        string $subject,
        string $body,
        string $altBody = ''
    ): bool {
        $s = $this->settings->getAll();

        // Bail early if SMTP is not configured
        if (empty($s['smtp_host']) || empty($s['smtp_user']) || empty($s['smtp_pass'])) {
            error_log('[MailerService] SMTP not configured — email not sent to ' . $to);
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            // ── Server settings ──────────────────────────────────
            $mail->isSMTP();
            $mail->Host       = $s['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $s['smtp_user'];
            $mail->Password   = $s['smtp_pass'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)($s['smtp_port'] ?? 587);

            // ── From ─────────────────────────────────────────────
            $fromName = $s['smtp_from_name'] ?? 'EVSU-OC INC Portal';
            $mail->setFrom($s['smtp_user'], $fromName);
            $mail->addReplyTo($s['smtp_user'], $fromName);

            // ── Recipient ─────────────────────────────────────────
            $mail->addAddress($to, $toName);

            // ── Content ───────────────────────────────────────────
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $this->wrapTemplate($body, $fromName);
            $mail->AltBody = $altBody ?: strip_tags($body);

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log('[MailerService] Failed to send to ' . $to . ': ' . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Wraps the raw HTML body in a clean, branded email template.
     */
    private function wrapTemplate(string $content, string $fromName): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width,initial-scale=1">
          <style>
            body { margin:0; padding:0; background:#F8F5EE; font-family:'Helvetica Neue',Arial,sans-serif; }
            .wrap { max-width:580px; margin:32px auto; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #DDD6CA; }
            .header { background:#4A0A12; padding:24px 32px; text-align:center; }
            .header-title { color:#E8C97A; font-size:18px; font-weight:700; margin:0; letter-spacing:0.04em; }
            .header-sub { color:rgba(255,255,255,0.5); font-size:11px; margin:4px 0 0; }
            .body { padding:32px; color:#1C1410; font-size:14px; line-height:1.7; }
            .body p { margin:0 0 16px; }
            .footer { background:#F1EDE4; padding:16px 32px; text-align:center; font-size:11px; color:#A09080; border-top:1px solid #DDD6CA; }
          </style>
        </head>
        <body>
          <div class="wrap">
            <div class="header">
              <p class="header-title">EVSU – Ormoc Campus</p>
              <p class="header-sub">INC Form Portal</p>
            </div>
            <div class="body">
              {$content}
            </div>
            <div class="footer">
              This is an automated message from {$fromName}. Please do not reply directly to this email.
            </div>
          </div>
        </body>
        </html>
        HTML;
    }
}