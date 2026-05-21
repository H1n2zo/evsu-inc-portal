<?php
$depth = 1;
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

$statusBadge = function($status) {
    $map = [
        'Pending Instructor Evaluation' => 'badge-pending',
        'Instructor Grade Entry'         => 'badge-pending',
        'Pending Department Head Approval' => 'badge-pending',
        'Pending Registrar Verification' => 'badge-pending',
        'Pending Final Registrar Confirmation' => 'badge-pending',
        'Approved'   => 'badge-approved',
        'Resolved'   => 'badge-resolved',
        'Grade Posted'=> 'badge-resolved',
        'Rejected'   => 'badge-rejected',
    ];
    $cls = $map[$status] ?? 'badge-pending';
    return '<span class="badge ' . $cls . '">' . htmlspecialchars($status) . '</span>';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard – EVSU-OC</title>
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
    <a class="sidebar-item active" href="student.php">Dashboard</a>
    <a class="sidebar-item" href="student_apply.php">Apply for INC</a>
    <a class="sidebar-item" href="student_applications.php">My Applications</a>
</aside>
<div class="main-content">
    <div class="page-title">Student Dashboard</div>

    <div class="stats-row">
        <?php
        $total   = count($apps);
        $pending = count(array_filter($apps, fn($a) => strpos($a['status'], 'Pending') !== false));
        $resolved= count(array_filter($apps, fn($a) => in_array($a['status'], ['Resolved','Grade Posted'])));
        ?>
        <div class="stat-box"><div class="stat-num"><?= $total ?></div><div class="stat-label">Total Applications</div></div>
        <div class="stat-box"><div class="stat-num"><?= $pending ?></div><div class="stat-label">In Progress</div></div>
        <div class="stat-box gold"><div class="stat-num"><?= $resolved ?></div><div class="stat-label">Resolved</div></div>
    </div>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <strong style="color:var(--maroon);">My INC Applications</strong>
            <a href="student_apply.php" class="btn btn-primary btn-sm">+ New Application</a>
        </div>
        <?php if (empty($apps)): ?>
            <p style="color:var(--gray-text);font-size:0.92rem;">No applications yet. Click <strong>+ New Application</strong> to get started.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Units</th>
                        <th>Fee</th>
                        <th>Semester</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($apps as $app): ?>
                    <tr>
                        <td><?= htmlspecialchars($app['subject_code']) ?> – <?= htmlspecialchars($app['subject_name']) ?></td>
                        <td><?= $app['units'] ?></td>
                        <td>₱<?= number_format($app['fee_computed'], 2) ?></td>
                        <td><?= htmlspecialchars($app['semester']) ?> <?= htmlspecialchars($app['academic_year']) ?></td>
                        <td><?= $statusBadge($app['status']) ?></td>
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
