<?php
require_once '../includes/auth.php';
requireRole('student');
$user = getCurrentUser();
$db   = getDB();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectId    = (int)($_POST['subject_id'] ?? 0);
    $semester     = trim($_POST['semester'] ?? '');
    $academicYear = trim($_POST['academic_year'] ?? '');
    $orNumber     = trim($_POST['or_number'] ?? '');

    if (!$subjectId || !$semester || !$academicYear || !$orNumber) {
        $error = 'All fields are required.';
    } elseif (empty($_FILES['or_receipt']['name'])) {
        $error = 'O.R. receipt file is required.';
    } else {
        $check = $db->prepare('SELECT app_id FROM inc_applications WHERE student_id=? AND subject_id=? AND semester=? AND academic_year=?');
        $check->execute([$user['user_id'], $subjectId, $semester, $academicYear]);
        if ($check->fetch()) {
            $error = 'Active INC application already exists for this subject.';
        } else {
            $file     = $_FILES['or_receipt'];
            $allowed  = ['image/jpeg','image/png','application/pdf'];
            $maxSize  = 5 * 1024 * 1024;
            if (!in_array($file['type'], $allowed)) {
                $error = 'Only JPG, PNG, or PDF files are allowed.';
            } elseif ($file['size'] > $maxSize) {
                $error = 'File too large. Maximum allowed size is 5MB.';
            } else {
                $uploadDir = '../uploads/receipts/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'or_' . $user['user_id'] . '_' . time() . '.' . $ext;
                $dest     = $uploadDir . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $subStmt = $db->prepare('SELECT units FROM subjects WHERE subject_id=?');
                    $subStmt->execute([$subjectId]);
                    $subject = $subStmt->fetch();
                    $fee     = $subject['units'] * 50;

                    $ins = $db->prepare("
                        INSERT INTO inc_applications (student_id,subject_id,semester,academic_year,status,fee_computed,or_number,or_receipt_path)
                        VALUES (?,?,?,?,'Pending Instructor Evaluation',?,?,?)
                    ");
                    $ins->execute([$user['user_id'],$subjectId,$semester,$academicYear,$fee,$orNumber,'uploads/receipts/'.$filename]);
                    $appId = $db->lastInsertId();

                    $wf = $db->prepare('INSERT INTO workflow_steps (app_id,step_number,acting_user_id,action) VALUES (?,1,?,?)');
                    $wf->execute([$appId,$user['user_id'],'Student submitted application']);

                    auditLog('INC application submitted', 'inc_applications', $appId);
                    $success = 'Application submitted successfully! Fee: ₱' . number_format($fee, 2);
                } else {
                    $error = 'File upload failed. Please try again.';
                }
            }
        }
    }
}

$subjects = $db->query('SELECT * FROM subjects ORDER BY subject_name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for INC – EVSU-OC</title>
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
    <a class="sidebar-item active" href="student_apply.php">Apply for INC</a>
    <a class="sidebar-item" href="student_applications.php">My Applications</a>
</aside>
<div class="main-content">
    <div class="page-title">Apply for INC</div>
    <div class="card" style="max-width:560px;">
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="subject_id">Subject</label>
                <select id="subject_id" name="subject_id" class="form-control" required onchange="updateFee(this)">
                    <option value="">— Select subject —</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['subject_id'] ?>" data-units="<?= $s['units'] ?>">
                            <?= htmlspecialchars($s['subject_code']) ?> – <?= htmlspecialchars($s['subject_name']) ?> (<?= $s['units'] ?> units)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="alert alert-info" id="feeDisplay" style="display:none;">
                Processing fee: <strong id="feeAmount"></strong>
            </div>
            <div class="form-group">
                <label for="semester">Semester</label>
                <select id="semester" name="semester" class="form-control" required>
                    <option value="">— Select —</option>
                    <option>1st Semester</option>
                    <option>2nd Semester</option>
                    <option>Summer</option>
                </select>
            </div>
            <div class="form-group">
                <label for="academic_year">Academic Year</label>
                <input type="text" id="academic_year" name="academic_year" class="form-control" placeholder="e.g. 2024-2025" required>
            </div>
            <div class="form-group">
                <label for="or_number">O.R. Number</label>
                <input type="text" id="or_number" name="or_number" class="form-control" placeholder="Official Receipt number" required>
            </div>
            <div class="form-group">
                <label for="or_receipt">O.R. Receipt (JPG/PNG/PDF, max 5MB)</label>
                <input type="file" id="or_receipt" name="or_receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Submit Application</button>
        </form>
    </div>
</div>
</div>
<script>
function updateFee(sel) {
    const opt = sel.options[sel.selectedIndex];
    const units = parseInt(opt.dataset.units || 0);
    if (units) {
        document.getElementById('feeDisplay').style.display = '';
        document.getElementById('feeAmount').textContent = '₱' + (units * 50).toLocaleString('en-PH', {minimumFractionDigits:2});
    } else {
        document.getElementById('feeDisplay').style.display = 'none';
    }
}
</script>
</body>
</html>
