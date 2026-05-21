<?php
require_once '../includes/auth.php';
requireRole('instructor');
$user = getCurrentUser();
$db   = getDB();

$apps = $db->query("
    SELECT ia.*, s.subject_name, s.subject_code, u.name AS student_name
    FROM inc_applications ia
    JOIN subjects s ON ia.subject_id = s.subject_id
    JOIN users u ON ia.student_id = u.user_id
    WHERE ia.status = 'Pending Instructor Evaluation'
    ORDER BY ia.submitted_at ASC
")->fetchAll();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appId  = (int)($_POST['app_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $grade  = trim($_POST['resolved_grade'] ?? '');
    $remark = trim($_POST['remarks'] ?? '');

    if ($action === 'submit_grade') {
        if (!$grade) { $error = 'Grade is required.'; }
        else {
            $ins = $db->prepare('INSERT INTO grades (app_id, instructor_id, resolved_grade) VALUES (?,?,?)');
            $ins->execute([$appId,$user['user_id'],$grade]);
            $db->prepare("UPDATE inc_applications SET status='Pending Department Head Approval' WHERE app_id=?")->execute([$appId]);
            $wf = $db->prepare('INSERT INTO workflow_steps (app_id,step_number,acting_user_id,action,remarks) VALUES (?,2,?,?,?)');
            $wf->execute([$appId,2,$user['user_id'],'Instructor submitted grade',$remark]);
            auditLog('Instructor submitted grade', 'inc_applications', $appId);
            $success = 'Grade submitted. Application forwarded to Department Head.';
            header('Location: instructor.php');
            exit;
        }
    } elseif ($action === 'return') {
        if (!$remark) { $error = 'Return reason is required.'; }
        else {
            $db->prepare("UPDATE inc_applications SET status='Returned by Instructor' WHERE app_id=?")->execute([$appId]);
            $wf = $db->prepare('INSERT INTO workflow_steps (app_id,step_number,acting_user_id,action,remarks) VALUES (?,2,?,?,?)');
            $wf->execute([$appId,2,$user['user_id'],'Instructor returned application',$remark]);
            auditLog('Instructor returned application', 'inc_applications', $appId);
            $success = 'Application returned to student.';
            header('Location: instructor.php');
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
    <title>Instructor Dashboard – EVSU-OC</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-brand">EVSU-OC <span>INC Form System</span></div>
    <div class="navbar-user">
        <?= htmlspecialchars($user['name']) ?> &mdash; Instructor
        <a href="../logout.php">Logout</a>
    </div>
</nav>
<div class="layout">
<aside class="sidebar">
    <a class="sidebar-item active" href="instructor.php">Applications</a>
</aside>
<div class="main-content">
    <div class="page-title">Instructor – Pending Grade Entry</div>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <?php if (empty($apps)): ?>
        <div class="card"><p style="color:var(--gray-text);">No applications pending your evaluation.</p></div>
    <?php else: ?>
    <?php foreach ($apps as $app): ?>
    <div class="card" style="margin-bottom:20px;">
        <div style="margin-bottom:14px;">
            <strong style="color:var(--maroon);"><?= htmlspecialchars($app['student_name']) ?></strong><br>
            <span style="color:var(--gray-text);font-size:0.88rem;"><?= htmlspecialchars($app['subject_code']) ?> – <?= htmlspecialchars($app['subject_name']) ?></span><br>
            <span style="font-size:0.82rem;color:var(--gray-text);"><?= htmlspecialchars($app['semester']) ?> <?= htmlspecialchars($app['academic_year']) ?></span>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <form method="POST" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <input type="hidden" name="app_id" value="<?= $app['app_id'] ?>">
                <input type="hidden" name="action" value="submit_grade">
                <input type="text" name="resolved_grade" class="form-control" style="width:120px;" placeholder="Grade (e.g. 1.5)" required>
                <input type="text" name="remarks" class="form-control" style="width:200px;" placeholder="Remarks (optional)">
                <button class="btn btn-primary btn-sm">Submit Grade</button>
            </form>
            <form method="POST" style="display:flex;gap:8px;align-items:center;">
                <input type="hidden" name="app_id" value="<?= $app['app_id'] ?>">
                <input type="hidden" name="action" value="return">
                <input type="text" name="remarks" class="form-control" style="width:200px;" placeholder="Return reason (required)" required>
                <button class="btn btn-outline btn-sm">Return</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
</div>
</body>
</html>
