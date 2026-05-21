<?php
require_once '../includes/auth.php';
requireRole('department_head');
$user = getCurrentUser();
$db   = getDB();

$apps = $db->query("
    SELECT ia.*, s.subject_name, s.subject_code, u.name AS student_name, g.resolved_grade
    FROM inc_applications ia
    JOIN subjects s ON ia.subject_id = s.subject_id
    JOIN users u ON ia.student_id = u.user_id
    LEFT JOIN grades g ON g.app_id = ia.app_id
    WHERE ia.status = 'Pending Department Head Approval'
    ORDER BY ia.submitted_at ASC
")->fetchAll();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appId  = (int)($_POST['app_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $remark = trim($_POST['remarks'] ?? '');

    if ($action === 'approve') {
        $db->prepare("UPDATE inc_applications SET status='Pending Registrar Verification' WHERE app_id=?")->execute([$appId]);
        $wf = $db->prepare('INSERT INTO workflow_steps (app_id,step_number,acting_user_id,action,remarks) VALUES (?,3,?,?,?)');
        $wf->execute([$appId,3,$user['user_id'],'Department Head approved',$remark]);
        auditLog('Dept Head approved application', 'inc_applications', $appId);
        $success = 'Application approved and forwarded to Registrar.';
        header('Location: department_head.php');
        exit;
    } elseif ($action === 'reject') {
        if (!$remark) { $error = 'Rejection reason is required.'; }
        else {
            $db->prepare("UPDATE inc_applications SET status='Returned by Dept Head' WHERE app_id=?")->execute([$appId]);
            $wf = $db->prepare('INSERT INTO workflow_steps (app_id,step_number,acting_user_id,action,remarks) VALUES (?,3,?,?,?)');
            $wf->execute([$appId,3,$user['user_id'],'Department Head rejected',$remark]);
            auditLog('Dept Head rejected application', 'inc_applications', $appId);
            $success = 'Application returned to Instructor.';
            header('Location: department_head.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Head Dashboard – EVSU-OC</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-brand">EVSU-OC <span>INC Form System</span></div>
    <div class="navbar-user">
        <?= htmlspecialchars($user['name']) ?> &mdash; Department Head
        <a href="../logout.php">Logout</a>
    </div>
</nav>
<div class="layout">
<aside class="sidebar">
    <a class="sidebar-item active" href="department_head.php">Applications</a>
</aside>
<div class="main-content">
    <div class="page-title">Department Head – Approval Queue</div>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <?php if (empty($apps)): ?>
        <div class="card"><p style="color:var(--gray-text);">No applications pending your approval.</p></div>
    <?php else: ?>
    <?php foreach ($apps as $app): ?>
    <div class="card" style="margin-bottom:20px;">
        <div style="margin-bottom:14px;">
            <strong style="color:var(--maroon);"><?= htmlspecialchars($app['student_name']) ?></strong><br>
            <span style="color:var(--gray-text);font-size:0.88rem;"><?= htmlspecialchars($app['subject_code']) ?> – <?= htmlspecialchars($app['subject_name']) ?></span><br>
            <span style="font-size:0.82rem;color:var(--gray-text);"><?= htmlspecialchars($app['semester']) ?> <?= htmlspecialchars($app['academic_year']) ?></span>
        </div>
        <?php if ($app['resolved_grade']): ?>
        <div class="alert alert-info" style="margin-bottom:14px;">
            Resolved Grade entered by Instructor: <strong><?= htmlspecialchars($app['resolved_grade']) ?></strong>
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <form method="POST" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <input type="hidden" name="app_id" value="<?= $app['app_id'] ?>">
                <input type="hidden" name="action" value="approve">
                <input type="text" name="remarks" class="form-control" style="width:200px;" placeholder="Remarks (optional)">
                <button class="btn btn-primary btn-sm">Approve</button>
            </form>
            <form method="POST" style="display:flex;gap:8px;align-items:center;">
                <input type="hidden" name="app_id" value="<?= $app['app_id'] ?>">
                <input type="hidden" name="action" value="reject">
                <input type="text" name="remarks" class="form-control" style="width:200px;" placeholder="Rejection reason (required)" required>
                <button class="btn btn-outline btn-sm">Return to Instructor</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
</div>
</body>
</html>
