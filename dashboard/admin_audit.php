<?php
require_once '../includes/auth.php';
requireRole('admin');
$user = getCurrentUser();
$db   = getDB();

$logs = $db->query("
    SELECT al.*, u.name, u.role FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.user_id
    ORDER BY al.logged_at DESC LIMIT 200
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs – EVSU-OC</title>
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
    <a class="sidebar-item" href="admin.php">Dashboard</a>
    <a class="sidebar-item" href="admin_users.php">User Management</a>
    <a class="sidebar-item" href="admin_applications.php">All Applications</a>
    <a class="sidebar-item active" href="admin_audit.php">Audit Logs</a>
</aside>
<div class="main-content">
    <div class="page-title">Audit Logs</div>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Timestamp</th><th>User</th><th>Role</th><th>Action</th><th>Table</th><th>Record</th><th>IP Address</th></tr>
                </thead>
                <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= date('M d Y H:i:s', strtotime($log['logged_at'])) ?></td>
                        <td><?= htmlspecialchars($log['name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($log['role'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['target_table'] ?? '—') ?></td>
                        <td><?= $log['target_id'] ?? '—' ?></td>
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
