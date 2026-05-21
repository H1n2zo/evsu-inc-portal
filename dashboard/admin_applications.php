<?php
require_once '../includes/auth.php';
requireRole('admin');
$user = getCurrentUser();
$db   = getDB();

$apps = $db->query("
    SELECT ia.*, s.subject_name, s.subject_code, u.name AS student_name
    FROM inc_applications ia
    JOIN subjects s ON ia.subject_id = s.subject_id
    JOIN users u ON ia.student_id = u.user_id
    ORDER BY ia.submitted_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Applications – EVSU-OC</title>
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
    <a class="sidebar-item active" href="admin_applications.php">All Applications</a>
    <a class="sidebar-item" href="admin_audit.php">Audit Logs</a>
</aside>
<div class="main-content">
    <div class="page-title">All INC Applications</div>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Student</th><th>Subject</th><th>Semester</th><th>A.Y.</th><th>Fee</th><th>Status</th><th>Submitted</th></tr>
                </thead>
                <tbody>
                <?php foreach ($apps as $app): ?>
                    <tr>
                        <td><?= htmlspecialchars($app['student_name']) ?></td>
                        <td><?= htmlspecialchars($app['subject_code']) ?> – <?= htmlspecialchars($app['subject_name']) ?></td>
                        <td><?= htmlspecialchars($app['semester']) ?></td>
                        <td><?= htmlspecialchars($app['academic_year']) ?></td>
                        <td>₱<?= number_format($app['fee_computed'],2) ?></td>
                        <td><span class="badge badge-pending"><?= htmlspecialchars($app['status']) ?></span></td>
                        <td><?= date('M d, Y', strtotime($app['submitted_at'])) ?></td>
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
