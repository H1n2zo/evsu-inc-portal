<?php
// includes/auth.php

require_once __DIR__ . '/../config/db.php';

// ─────────────────────────────────────────────
// Session bootstrap
// ─────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// Session timeout check
function checkSessionTimeout(): void {
    $timeout = (int)(getSetting('session_timeout') ?? 30) * 60;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        logout();
        header('Location: /evsu_inc_portal/index.php?expired=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

// ─────────────────────────────────────────────
// Auth guards
// ─────────────────────────────────────────────
function requireLogin(string $redirect = '/evsu_inc_portal/index.php'): void {
    checkSessionTimeout();
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . $redirect);
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['account_type'] !== 'admin') {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:2rem;color:#6B0F1A;">403 — Access Denied. Admin only.</div>');
    }
}

function requireEmployee(): void {
    requireLogin();
    if (!in_array($_SESSION['account_type'], ['admin', 'employee'])) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:2rem;color:#6B0F1A;">403 — Access Denied.</div>');
    }
}

// ─────────────────────────────────────────────
// Login / Logout
// ─────────────────────────────────────────────
function attemptLogin(string $username, string $password): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        auditLog(null, $username, null, 'Login failed', 'Invalid credentials', $_SERVER['REMOTE_ADDR'] ?? '');
        return ['success' => false, 'error' => 'Invalid username or password.'];
    }
    if ($user['status'] !== 'active') {
        return ['success' => false, 'error' => 'Account is ' . $user['status'] . '. Contact the administrator.'];
    }

    // Fetch employee roles
    $roles = [];
    if ($user['account_type'] === 'employee') {
        $rs = $pdo->prepare("SELECT r.role_name FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ?");
        $rs->execute([$user['id']]);
        $roles = array_column($rs->fetchAll(), 'role_name');
    }

    // Regenerate session
    session_regenerate_id(true);
    $_SESSION['user_id']      = $user['id'];
    $_SESSION['username']     = $user['username'];
    $_SESSION['full_name']    = $user['full_name'];
    $_SESSION['account_type'] = $user['account_type'];
    $_SESSION['status']       = $user['status'];
    $_SESSION['roles']        = $roles;
    // Active role: first assigned role, or account type for admin/student
    $_SESSION['active_role']  = $user['account_type'] === 'employee'
        ? ($roles[0] ?? 'instructor')
        : $user['account_type'];
    $_SESSION['last_activity'] = time();

    auditLog($user['id'], $user['username'], $_SESSION['active_role'], 'Login', 'Authenticated successfully', $_SERVER['REMOTE_ADDR'] ?? '');
    return ['success' => true, 'user' => $user, 'roles' => $roles];
}

function logout(): void {
    if (!empty($_SESSION['user_id'])) {
        auditLog($_SESSION['user_id'], $_SESSION['username'] ?? '', $_SESSION['active_role'] ?? '', 'Logout', 'Session ended', $_SERVER['REMOTE_ADDR'] ?? '');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ─────────────────────────────────────────────
// Role switching (employees only)
// ─────────────────────────────────────────────
function switchRole(string $role): bool {
    if ($_SESSION['account_type'] !== 'employee') return false;
    if (!in_array($role, $_SESSION['roles'])) return false;
    $old = $_SESSION['active_role'];
    $_SESSION['active_role'] = $role;
    auditLog($_SESSION['user_id'], $_SESSION['username'], $role, 'Role Switch', "Switched from $old to $role", $_SERVER['REMOTE_ADDR'] ?? '');
    return true;
}

// ─────────────────────────────────────────────
// CSRF helpers
// ─────────────────────────────────────────────
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF token mismatch.');
    }
}

// ─────────────────────────────────────────────
// Audit log writer
// ─────────────────────────────────────────────
function auditLog(?int $userId, string $username, ?string $role, string $action, ?string $desc, string $ip): void {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, username, active_role, action, description, ip_address) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$userId, $username, $role, $action, $desc, $ip]);
    } catch (Throwable $e) {
        // Silently fail — never let logging break the app
    }
}

// ─────────────────────────────────────────────
// Settings helper
// ─────────────────────────────────────────────
function getSetting(string $key): ?string {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : null;
    } catch (Throwable $e) {
        return null;
    }
}

// ─────────────────────────────────────────────
// Input sanitization
// ─────────────────────────────────────────────
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
