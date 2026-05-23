<?php
// student/application_view.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
if ($_SESSION['account_type'] !== 'student') { header('Location: /index.php'); exit; }

$pdo   = getDB();
$uid   = $_SESSION['user_id'];
$appId = (int)($_GET['id'] ?? 0);
$filed = isset($_GET['filed']);
$msg = ''; $error = '';

$stmt = $pdo->prepare("SELECT a.*, u.full_name, inst.full_name as instructor_name, dh.full_name as depthead_name, reg.full_name as registrar_name
    FROM inc_applications a JOIN users u ON a.student_id=u.id
    LEFT JOIN users inst ON a.instructor_id=inst.id
    LEFT JOIN users dh ON a.dept_head_id=dh.id
    LEFT JOIN users reg ON a.registrar_id=reg.id
    WHERE a.id=? AND a.student_id=?");
$stmt->execute([$appId, $uid]);
$app = $stmt->fetch();
if (!$app) { header('Location: /student/applications.php'); exit; }

// Handle receipt upload (Step 4)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if ($_POST['action'] === 'upload_receipt' && $app['current_step'] == 4) {
        $orNum = trim($_POST['or_number'] ?? '');
        $maxMb = (int)(getSetting('max_upload_mb') ?? 5);

        if (!$orNum) { $error = 'Please enter the Official Receipt number.'; }
        elseif (empty($_FILES['receipt']['name'])) { $error = 'Please upload your receipt file.'; }
        elseif ($_FILES['receipt']['size'] > $maxMb * 1024 * 1024) { $error = "File exceeds {$maxMb}MB limit."; }
        else {
            $ext = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','pdf'])) {
                $error = 'Only JPG, PNG, and PDF files are allowed.';
            } else {
                $uploadDir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = 'receipt_' . $appId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['receipt']['tmp_name'], $uploadDir . $filename)) {
                    $pdo->prepare("UPDATE inc_applications SET or_number=?, receipt_filename=?,
                        receipt_uploaded_at=NOW(), current_step=5, status='verification', updated_at=NOW()
                        WHERE id=?")->execute([$orNum, $filename, $appId]);
                    auditLog($uid, $_SESSION['username'], 'student', 'Receipt Uploaded', "App $app[app_code] — O.R.: $orNum", $_SERVER['REMOTE_ADDR']??'');
                    $msg = 'Receipt uploaded. The Registrar will now verify your O.R.';
                    $stmt->execute([$appId, $uid]); $app = $stmt->fetch();
                } else {
                    $error = 'File upload failed. Please try again.';
                }
            }
        }
    }
}

$stepLabels=[1=>'Student Filing',2=>'Instructor Input',3=>'Dept. Head Review',4=>'Payment Upload',5=>'Registrar Verify',6=>'Grade Posting',7=>'Resolved'];
$activePage = 'applications'; $pageTitle = 'Application '.$app['app_code'];
include __DIR__ . '/../includes/head.php';
?>
<body>
<div class="layout">
<?php include __DIR__ . '/../includes/student_sidebar.php'; ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2><?= h($app['app_code']) ?></h2>
      <p><?= h($app['subject_name']) ?> — <?= $app['units'] ?> units — ₱<?= number_format($app['processing_fee'],0) ?></p>
    </div>
    <?php
    $bm=['in_progress'=>'badge-info','pending_payment'=>'badge-gold','verification'=>'badge-gold','resolved'=>'badge-success','rejected'=>'badge-danger','draft'=>'badge-gray'];
    $lm=['in_progress'=>'In Progress','pending_payment'=>'Pending Payment','verification'=>'Verification','resolved'=>'Resolved','rejected'=>'Rejected'];
    ?>
    <span class="badge <?= $bm[$app['status']]??'badge-gray' ?>"><?= $lm[$app['status']]??ucfirst($app['status']) ?></span>
  </div>

  <?php if ($filed): ?><div class="alert alert-success">✓ Application submitted successfully! Your instructor has been notified.</div><?php endif; ?>
  <?php if ($msg): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

  <?php if ($app['status']==='rejected' && $app['rejection_reason']): ?>
  <div class="alert alert-danger">
    <strong>Rejected:</strong> <?= h($app['rejection_reason']) ?>
  </div>
  <?php endif; ?>

  <!-- Step tracker -->
  <div class="content-card" style="padding:1.25rem;margin-bottom:1.25rem;">
    <div class="step-tracker">
      <?php for($s=1;$s<=7;$s++):
        $done   = $app['current_step'] > $s || $app['status']==='resolved';
        $active = $app['current_step'] == $s && $app['status']!=='rejected';
      ?>
      <div class="step-item">
        <div class="step-dot <?= $done?'done':($active?'active':'') ?>"><?= $done?'✓':$s ?></div>
        <div class="step-label <?= $active?'active':'' ?>" style="font-size:10px;"><?= $stepLabels[$s] ?></div>
      </div>
      <?php if($s<7): ?><div class="step-line <?= $done?'done':'' ?>"></div><?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">
    <!-- Application details -->
    <div class="content-card">
      <div class="card-head"><h3>Application Details</h3></div>
      <div class="card-body" style="font-size:13.5px;">
        <table style="width:100%;border-collapse:collapse;">
          <?php foreach([['Subject',$app['subject_name']],['Subject Code',$app['subject_code']],['Units',$app['units']],['Processing Fee','₱'.number_format($app['processing_fee'],0)],['Semester',$app['semester']],['School Year',$app['school_year']],['Instructor',$app['instructor_name']??'—'],['Dept. Head',$app['depthead_name']??'—'],['Filed',date('M d, Y',strtotime($app['created_at']))]] as [$l,$v]): ?>
          <tr><td style="color:var(--gray-400);padding:5px 0;width:45%;"><?= $l ?></td><td style="padding:5px 0;font-weight:500;"><?= h($v) ?></td></tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>

    <!-- Progress details -->
    <div class="content-card">
      <div class="card-head"><h3>Progress</h3></div>
      <div class="card-body" style="font-size:13px;">
        <?php if ($app['instructor_signed_at']): ?>
        <div style="margin-bottom:0.875rem;padding-bottom:0.875rem;border-bottom:1px solid var(--gray-100);">
          <span class="role-chip rc-instructor">Instructor</span>
          <div style="margin-top:4px;">Grade entered: <strong><?= h($app['instructor_grade']??'—') ?></strong></div>
          <div style="font-size:11.5px;color:var(--gray-400);margin-top:2px;"><?= date('M d, Y', strtotime($app['instructor_signed_at'])) ?></div>
          <?php if ($app['instructor_signature']): ?><img src="<?= $app['instructor_signature'] ?>" style="max-height:35px;border:1px solid var(--gray-200);border-radius:4px;margin-top:4px;"><?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($app['dept_head_signed_at']): ?>
        <div style="margin-bottom:0.875rem;padding-bottom:0.875rem;border-bottom:1px solid var(--gray-100);">
          <span class="role-chip rc-dept_head">Dept. Head</span>
          <div style="margin-top:4px;">Decision: <strong style="color:<?= $app['dept_head_action']==='approved'?'var(--success)':'var(--danger)' ?>"><?= ucfirst($app['dept_head_action']??'—') ?></strong></div>
          <div style="font-size:11.5px;color:var(--gray-400);margin-top:2px;"><?= date('M d, Y', strtotime($app['dept_head_signed_at'])) ?></div>
          <?php if ($app['dept_head_signature']): ?><img src="<?= $app['dept_head_signature'] ?>" style="max-height:35px;border:1px solid var(--gray-200);border-radius:4px;margin-top:4px;"><?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($app['registrar_signed_at']): ?>
        <div>
          <span class="role-chip rc-registrar">Registrar</span>
          <div style="margin-top:4px;color:var(--success);font-weight:600;">✓ Grade Posted — Application Resolved</div>
          <div style="font-size:11.5px;color:var(--gray-400);margin-top:2px;"><?= date('M d, Y', strtotime($app['registrar_signed_at'])) ?></div>
          <?php if ($app['registrar_signature']): ?><img src="<?= $app['registrar_signature'] ?>" style="max-height:35px;border:1px solid var(--gray-200);border-radius:4px;margin-top:4px;"><?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($app['current_step'] <= 2 && !$app['instructor_signed_at']): ?>
        <div style="color:var(--gray-400);font-size:13px;">⏳ Waiting for instructor to enter grade…</div>
        <?php endif; ?>
        <?php if ($app['current_step'] == 3 && !$app['dept_head_signed_at']): ?>
        <div style="color:var(--gray-400);font-size:13px;">⏳ Waiting for department head review…</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Payment upload (Step 4) -->
  <?php if ($app['current_step'] == 4 && $app['status'] === 'pending_payment'): ?>
  <div class="content-card" style="max-width:600px;">
    <div class="card-head"><h3>Step 4 — Upload Payment Receipt</h3></div>
    <div class="card-body">
      <div class="alert alert-gold" style="margin-bottom:1rem;">
        Please pay <strong>₱<?= number_format($app['processing_fee'],0) ?></strong> at the cashier and upload your Official Receipt below.
      </div>
      <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="action" value="upload_receipt">
        <div class="form-group">
          <label class="form-label">Official Receipt (O.R.) Number <span style="color:var(--danger)">*</span></label>
          <input class="form-input" type="text" name="or_number" placeholder="Enter the O.R. number on your receipt" required>
        </div>
        <div class="form-group">
          <label class="form-label">Upload Receipt File <span style="color:var(--danger)">*</span></label>
          <input class="form-input" type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" required style="height:auto;padding:8px 14px;">
          <p class="form-hint">Accepted: JPG, PNG, PDF — Max <?= getSetting('max_upload_mb')??5 ?>MB</p>
        </div>
        <button type="submit" class="btn-primary">Upload Receipt</button>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- Already uploaded receipt -->
  <?php if ($app['receipt_filename'] && $app['current_step'] >= 5): ?>
  <div class="content-card" style="max-width:500px;">
    <div class="card-head"><h3>Payment Receipt</h3><span class="badge badge-success">Uploaded</span></div>
    <div class="card-body">
      <p style="font-size:13.5px;margin-bottom:0.75rem;">O.R. No.: <strong><?= h($app['or_number']??'—') ?></strong></p>
      <a href="/assets/uploads/<?= h($app['receipt_filename']) ?>" target="_blank" class="btn-sm">View Receipt</a>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($app['status'] === 'resolved'): ?>
  <div class="alert alert-success" style="font-size:14px;">
    🎉 <strong>Congratulations!</strong> Your INC application has been resolved. Final grade <strong><?= h($app['instructor_grade']??'') ?></strong> has been posted to your record.
  </div>
  <?php endif; ?>

</main>
</div>
</body>
</html>
