<?php
require_once '../includes/auth.php';
requireRole('admin');
$user = getCurrentUser();
$db   = getDB();

$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();
$totalApps  = $db->query("SELECT COUNT(*) FROM inc_applications")->fetchColumn();
$pendingApps= $db->query("SELECT COUNT(*) FROM inc_applications WHERE status LIKE 'Pending%'")->fetchColumn();
$resolvedApps=$db->query("SELECT COUNT(*) FROM inc_applications WHERE status IN ('Resolved','Grade Posted')")->fetchColumn();

$recentLogs = $db->query("
    SELECT al.*, u.name, u.role FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.user_id
    ORDER BY al.logged_at DESC LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – EVSU-OC</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-brand">EVSU-OC <span>INC Form System</span></div>
    <div class="navbar-user">
        <?= htmlspecialchars($user['name']) ?> &mdash; Administrator
        <a href="../logout.php">Logout</a>
    </div>
</nav>
<div class="layout">
<aside class="sidebar">
    <a class="sidebar-item active" href="admin.php">Dashboard</a>
    <a class="sidebar-item" href="admin_users.php">User Management</a>
    <a class="sidebar-item" href="admin_applications.php">All Applications</a>
    <a class="sidebar-item" href="admin_audit.php">Audit Logs</a>
</aside>
<div class="main-content">
    <div class="page-title">Admin Dashboard</div>

    <div class="stats-row">
        <div class="stat-box"><div class="stat-num"><?= $totalUsers ?></div><div class="stat-label">Active Users</div></div>
        <div class="stat-box"><div class="stat-num"><?= $totalApps ?></div><div class="stat-label">Total Applications</div></div>
        <div class="stat-box"><div class="stat-num"><?= $pendingApps ?></div><div class="stat-label">Pending</div></div>
        <div class="stat-box gold"><div class="stat-num"><?= $resolvedApps ?></div><div class="stat-label">Resolved</div></div>
    </div>

    <div class="card">
        <strong style="color:var(--maroon);display:block;margin-bottom:14px;">Recent Audit Logs</strong>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Time</th><th>User</th><th>Role</th><th>Action</th><th>IP</th></tr>
                </thead>
                <tbody>
                <?php foreach ($recentLogs as $log): ?>
                    <tr>
                        <td><?= date('M d Y H:i', strtotime($log['logged_at'])) ?></td>
                        <td><?= htmlspecialchars($log['name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($log['role'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['ip_address']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
</body>
</html>
