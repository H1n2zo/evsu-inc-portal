<?php
// core/AuthGuard.php
// Logic Layer — enforces session authentication and role-based access control
// OOP Concepts:
//   - Encapsulation: all session/role/timeout checks are private helpers
//   - Composition: injects SettingsModel (no static calls)
//   - NO static properties or methods

require_once __DIR__ . '/../models/SettingsModel.php';

class AuthGuard
{
    private SettingsModel $settings;

    public function __construct()
    {
        // Inject SettingsModel as a regular instance — no static calls
        $this->settings = new SettingsModel();
    }

    // ── Private helpers ───────────────────────────────────────────

    private function checkTimeout(): void
    {
        $timeout = (int)($this->settings->get('session_timeout') ?? 30) * 60;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
            $this->forceLogout();
            header('Location: index.php?expired=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }

    private function forceLogout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    // ── Public guards — called by controllers ─────────────────────

    public function requireLogin(string $redirect = 'index.php'): void
    {
        $this->checkTimeout();
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $redirect);
            exit;
        }
    }

    public function requireAdmin(): void
    {
        $this->requireLogin();
        if ($_SESSION['account_type'] !== 'admin') {
            http_response_code(403);
            die('<div style="font-family:sans-serif;padding:2rem;color:#6B0F1A;">403 — Admin access only.</div>');
        }
    }

    public function requireEmployee(): void
    {
        $this->requireLogin();
        if (!in_array($_SESSION['account_type'], ['admin', 'employee'])) {
            http_response_code(403);
            die('<div style="font-family:sans-serif;padding:2rem;color:#6B0F1A;">403 — Employee access only.</div>');
        }
    }

    public function requireStudent(): void
    {
        $this->requireLogin();
        if ($_SESSION['account_type'] !== 'student') {
            header('Location: index.php');
            exit;
        }
    }

    // ── CSRF helpers ──────────────────────────────────────────────

    public function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('CSRF token mismatch.');
        }
    }
}
