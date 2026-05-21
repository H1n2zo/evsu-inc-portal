<?php
require_once '../includes/auth.php';
requireRole('registrar');
$user = getCurrentUser();
$db   = getDB();

$pending = $db->query("
    SELECT ia.*, s.subject_name, s.subject_code, u.name AS student_name, u.email AS student_email
    FROM inc_applications ia
    JOIN subjects s ON ia.subject_id = s.subject_id
    JOIN users u ON ia.student_id = u.user_id
    WHERE ia.status IN ('Pending Registrar Verification','Pending Final Registrar Confirmation')
    ORDER BY ia.submitted_at ASC
")->fetchAll();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appId  = (int)($_POST['app_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $remark = trim($_POST['remarks'] ?? '');

    if ($action === 'approve_payment') {
        $db->prepare("UPDATE inc_applications SET status='Pending Department Head Approval' WHERE app_id=?")->execute([$appId]);
        $wf = $db->prepare('INSERT INTO workflow_steps (app_id,step_number,acting_user_id,action,remarks) VALUES (?,5,?,?,?)');
        $wf->execute([$appId,5,$user['user_id'],'Registrar verified O.R. payment',$remark]);
        auditLog('Registrar approved payment', 'inc_applications', $appId);
        $success = 'Payment verified. Application forwarded.';
    } elseif ($action === 'reject') {
        if (!$remark) { $error = 'Rejection reason is required.'; }
        else {
            $db->prepare("UPDATE inc_applications SET status='Rejected' WHERE app_id=?")->execute([$appId]);
            $wf = $db->prepare('INSERT INTO workflow_steps (app_id,step_number,acting_user_id,action,remarks) VALUES (?,5,?,?,?)');
            $wf->execute([$appId,5,$user['user_id'],'Registrar rejected application',$remark]);
            auditLog('Registrar rejected application', 'inc_applications', $appId);
            $success = 'Application rejected.';
        }
    } elseif ($action === 'post_grade') {
        $db->prepare("UPDATE inc_applications SET status='Grade Posted' WHERE app_id=?")->execute([$appId]);
        $wf = $db->prepare('INSERT INTO workflow_steps (app_id,step_number,acting_user_id,action) VALUES (?,7,?,?)');
        $wf->execute([$appId,7,$user['user_id'],'Registrar posted final grade']);
        auditLog('Registrar posted grade', 'inc_applications', $appId);
        $success = 'Grade posted. Application resolved.';
    }
    header('Location: registrar.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Dashboard – EVSU-OC</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-brand">EVSU-OC <span>INC Form System</span></div>
    <div class="navbar-user">
        <?= htmlspecialchars($user['name']) ?> &mdash; Registrar
        <a href="../logout.php">Logout</a>
    </div>
</nav>
<div class="layout">
<aside class="sidebar">
    <a class="sidebar-item active" href="registrar.php">Pending Applications</a>
</aside>
<div class="main-content">
    <div class="page-title">Registrar – Pending Applications</div>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <?php if (empty($pending)): ?>
        <div class="card"><p style="color:var(--gray-text);">No pending applications for your review.</p></div>
    <?php else: ?>
    <?php foreach ($pending as $app): ?>
    <div class="card" style="margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
            <div>
                <strong style="color:var(--maroon);font-size:1rem;"><?= htmlspecialchars($app['student_name']) ?></strong><br>
                <span style="color:var(--gray-text);font-size:0.88rem;"><?= htmlspecialchars($app['subject_code']) ?> – <?= htmlspecialchars($app['subject_name']) ?></span>
            </div>
            <span class="badge badge-pending"><?= htmlspecialchars($app['status']) ?></span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:18px;">
            <div>
                <p style="font-size:0.85rem;color:var(--gray-text);margin-bottom:4px;">O.R. Number</p>
                <p style="font-weight:700;color:var(--maroon);font-size:1.1rem;"><?= htmlspecialchars($app['or_number'] ?? '—') ?></p>
                <p style="font-size:0.85rem;color:var(--gray-text);margin-top:10px;">Fee Computed</p>
                <p style="font-weight:600;">₱<?= number_format($app['fee_computed'],2) ?></p>
            </div>
            <div>
                <p style="font-size:0.85rem;color:var(--gray-text);margin-bottom:4px;">Uploaded Receipt</p>
                <?php if ($app['or_receipt_path']): ?>
                    <?php $ext = strtolower(pathinfo($app['or_receipt_path'], PATHINFO_EXTENSION)); ?>
                    <?php if (in_array($ext,['jpg','jpeg','png'])): ?>
                        <img src="../<?= htmlspecialchars($app['or_receipt_path']) ?>" style="max-width:100%;max-height:180px;border-radius:6px;border:1px solid var(--gray);">
                    <?php else: ?>
                        <a href="../<?= htmlspecialchars($app['or_receipt_path']) ?>" target="_blank" class="btn btn-outline btn-sm">View PDF</a>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="color:var(--gray-text);">No receipt uploaded.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($app['status'] === 'Pending Registrar Verification'): ?>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <form method="POST">
                <input type="hidden" name="app_id" value="<?= $app['app_id'] ?>">
                <input type="hidden" name="action" value="approve_payment">
                <button class="btn btn-primary btn-sm">Verify &amp; Approve Payment</button>
            </form>
            <form method="POST" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <input type="hidden" name="app_id" value="<?= $app['app_id'] ?>">
                <input type="hidden" name="action" value="reject">
                <input type="text" name="remarks" class="form-control" style="width:220px;" placeholder="Rejection reason (required)" required>
                <button class="btn btn-outline btn-sm">Reject</button>
            </form>
        </div>
        <?php elseif ($app['status'] === 'Pending Final Registrar Confirmation'): ?>
        <form method="POST">
            <input type="hidden" name="app_id" value="<?= $app['app_id'] ?>">
            <input type="hidden" name="action" value="post_grade">
            <button class="btn btn-gold btn-sm">Post Final Grade</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
</div>
</body>
</html>
