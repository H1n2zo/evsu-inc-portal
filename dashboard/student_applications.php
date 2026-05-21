<?php
require_once '../includes/auth.php';
requireRole('student');
$user = getCurrentUser();
$db   = getDB();

$stmt = $db->prepare("
    SELECT ia.*, s.subject_name, s.subject_code, s.units
    FROM inc_applications ia
    JOIN subjects s ON ia.subject_id = s.subject_id
    WHERE ia.student_id = ?
    ORDER BY ia.submitted_at DESC
");
$stmt->execute([$user['user_id']]);
$apps = $stmt->fetchAll();

$badgeClass = function($status) {
    if (strpos($status,'Pending') !== false) return 'badge-pending';
    if (in_array($status,['Approved','Resolved','Grade Posted'])) return 'badge-approved';
    if ($status === 'Rejected') return 'badge-rejected';
    return 'badge-pending';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications – EVSU-OC</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-brand">EVSU-OC <span>INC Form System</span></div>
    <div class="navbar-user">
        <?= htmlspecialchars($user['name']) ?> &mdash; Student
        <a href="../logout.php">Logout</a>
    </div>
</nav>
<div class="layout">
<aside class="sidebar">
    <a class="sidebar-item" href="student.php">Dashboard</a>
    <a class="sidebar-item" href="student_apply.php">Apply for INC</a>
    <a class="sidebar-item active" href="student_applications.php">My Applications</a>
</aside>
<div class="main-content">
    <div class="page-title">My Applications</div>
    <div class="card">
        <?php if (empty($apps)): ?>
            <p style="color:var(--gray-text);">No applications found.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>Units</th>
                        <th>Fee</th>
                        <th>O.R. No.</th>
                        <th>Semester</th>
                        <th>A.Y.</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($apps as $i => $app): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($app['subject_code']) ?><br><small><?= htmlspecialchars($app['subject_name']) ?></small></td>
                        <td><?= $app['units'] ?></td>
                        <td>₱<?= number_format($app['fee_computed'], 2) ?></td>
                        <td><?= htmlspecialchars($app['or_number'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($app['semester']) ?></td>
                        <td><?= htmlspecialchars($app['academic_year']) ?></td>
                        <td><span class="badge <?= $badgeClass($app['status']) ?>"><?= htmlspecialchars($app['status']) ?></span></td>
                        <td><?= date('M d, Y', strtotime($app['submitted_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
</body>
</html>
