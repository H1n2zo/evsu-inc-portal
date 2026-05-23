<?php
// admin/application_view.php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDB();
$appId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT a.*, u.full_name, u.username, u.email, u.student_id as stu_id,
    inst.full_name as instructor_name, dh.full_name as depthead_name, reg.full_name as registrar_name
    FROM inc_applications a JOIN users u ON a.student_id=u.id
    LEFT JOIN users inst ON a.instructor_id=inst.id
    LEFT JOIN users dh ON a.dept_head_id=dh.id
    LEFT JOIN users reg ON a.registrar_id=reg.id
    WHERE a.id=?");
$stmt->execute([$appId]);
$app = $stmt->fetch();
if (!$app) { header('Location: /admin/applications.php'); exit; }

$stepLabels=[1=>'Student Filing',2=>'Instructor Input',3=>'Dept. Head Review',4=>'Payment Upload',5=>'Registrar Verify',6=>'Grade Posting',7=>'Resolved'];
$activePage='applications'; $pageTitle='Application '.$app['app_code'];
include __DIR__ . '/../includes/head.php';
?>
<body>
<div class="layout">
<?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2><?= h($app['app_code']) ?></h2>
      <p><?= h($app['full_name']) ?> — <?= h($app['subject_name']) ?></p>
    </div>
    <?php
    $bm=['in_progress'=>'badge-info','pending_payment'=>'badge-gold','verification'=>'badge-gold','resolved'=>'badge-success','rejected'=>'badge-danger','draft'=>'badge-gray'];
    $lm=['in_progress'=>'In Progress','pending_payment'=>'Pending Payment','verification'=>'Verification','resolved'=>'Resolved','rejected'=>'Rejected'];
    ?>
    <div style="display:flex;align-items:center;gap:0.75rem;">
      <span class="badge <?= $bm[$app['status']]??'badge-gray' ?>"><?= $lm[$app['status']]??ucfirst($app['status']) ?></span>
      <a href="/admin/applications.php" class="btn-sm">← Back</a>
    </div>
  </div>

  <!-- Step Tracker -->
  <div class="content-card" style="padding:1.25rem;margin-bottom:1.25rem;">
    <div class="step-tracker">
      <?php for($s=1;$s<=7;$s++):
        $done=$app['current_step']>$s||$app['status']==='resolved';
        $active=$app['current_step']==$s&&$app['status']!=='rejected';
      ?>
      <div class="step-item">
        <div class="step-dot <?= $done?'done':($active?'active':'') ?>"><?= $done?'✓':$s ?></div>
        <div class="step-label <?= $active?'active':'' ?>" style="font-size:10px;"><?= $stepLabels[$s] ?></div>
      </div>
      <?php if($s<7): ?><div class="step-line <?= $done?'done':'' ?>"></div><?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
    <div class="content-card">
      <div class="card-head"><h3>Application Details</h3></div>
      <div class="card-body" style="font-size:13.5px;">
        <table style="width:100%;border-collapse:collapse;">
          <?php foreach([['App Code',$app['app_code']],['Student',$app['full_name'].' ('.$app['username'].')'],['Student ID',$app['stu_id']??'—'],['Subject',$app['subject_name']],['Subject Code',$app['subject_code']],['Units',$app['units'].' units'],['Processing Fee','₱'.number_format($app['processing_fee'],0)],['Semester',$app['semester'].' Sem'],['School Year',$app['school_year']],['Instructor',$app['instructor_name']??'—'],['Dept. Head',$app['depthead_name']??'—'],['Registrar',$app['registrar_name']??'—'],['Filed',date('M d, Y H:i',strtotime($app['created_at']))],['Last Updated',date('M d, Y H:i',strtotime($app['updated_at']))]] as [$l,$v]): ?>
          <tr><td style="color:var(--gray-400);padding:5px 0;width:40%;"><?= h($l) ?></td><td style="padding:5px 0;font-weight:500;"><?= h($v) ?></td></tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>

    <div class="content-card">
      <div class="card-head"><h3>Full Workflow Record</h3></div>
      <div class="card-body" style="font-size:13px;">
        <!-- Instructor -->
        <div style="margin-bottom:0.875rem;padding-bottom:0.875rem;border-bottom:1px solid var(--gray-100);">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;"><span class="role-chip rc-instructor">Instructor</span>
          <?php if($app['instructor_signed_at']): ?><span style="font-size:11px;color:var(--gray-400);"><?= date('M d, Y H:i',strtotime($app['instructor_signed_at'])) ?></span><?php endif; ?></div>
          <?php if($app['instructor_grade']): ?><div>Grade: <strong><?= h($app['instructor_grade']) ?></strong></div><?php endif; ?>
          <?php if($app['instructor_remarks']): ?><div style="color:var(--gray-400);font-size:12px;"><?= h($app['instructor_remarks']) ?></div><?php endif; ?>
          <?php if($app['instructor_signature']): ?><img src="<?= $app['instructor_signature'] ?>" style="max-height:40px;border:1px solid var(--gray-200);border-radius:4px;margin-top:4px;"><?php endif; ?>
          <?php if(!$app['instructor_signed_at']): ?><span style="color:var(--gray-400);font-size:12px;">Pending</span><?php endif; ?>
        </div>
        <!-- Dept Head -->
        <div style="margin-bottom:0.875rem;padding-bottom:0.875rem;border-bottom:1px solid var(--gray-100);">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;"><span class="role-chip rc-dept_head">Dept. Head</span>
          <?php if($app['dept_head_signed_at']): ?><span style="font-size:11px;color:var(--gray-400);"><?= date('M d, Y H:i',strtotime($app['dept_head_signed_at'])) ?></span><?php endif; ?></div>
          <?php if($app['dept_head_action']): ?><div>Decision: <strong style="color:<?= $app['dept_head_action']==='approved'?'var(--success)':'var(--danger)' ?>"><?= ucfirst($app['dept_head_action']) ?></strong></div><?php endif; ?>
          <?php if($app['dept_head_remarks']): ?><div style="color:var(--gray-400);font-size:12px;"><?= h($app['dept_head_remarks']) ?></div><?php endif; ?>
          <?php if($app['dept_head_signature']): ?><img src="<?= $app['dept_head_signature'] ?>" style="max-height:40px;border:1px solid var(--gray-200);border-radius:4px;margin-top:4px;"><?php endif; ?>
          <?php if(!$app['dept_head_signed_at']): ?><span style="color:var(--gray-400);font-size:12px;">Pending</span><?php endif; ?>
        </div>
        <!-- Payment -->
        <div style="margin-bottom:0.875rem;padding-bottom:0.875rem;border-bottom:1px solid var(--gray-100);">
          <div style="margin-bottom:4px;"><span class="role-chip rc-student">Student Payment</span></div>
          <?php if($app['or_number']): ?>
          <div>O.R. No.: <strong><?= h($app['or_number']) ?></strong></div>
          <?php if($app['receipt_filename']): ?><a href="/assets/uploads/<?= h($app['receipt_filename']) ?>" target="_blank" class="btn-sm" style="margin-top:6px;">View Receipt</a><?php endif; ?>
          <?php else: ?><span style="color:var(--gray-400);font-size:12px;">No payment uploaded yet</span><?php endif; ?>
        </div>
        <!-- Registrar -->
        <div>
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;"><span class="role-chip rc-registrar">Registrar</span>
          <?php if($app['registrar_signed_at']): ?><span style="font-size:11px;color:var(--gray-400);"><?= date('M d, Y H:i',strtotime($app['registrar_signed_at'])) ?></span><?php endif; ?></div>
          <?php if($app['registrar_action']): ?><div>Action: <strong><?= ucfirst($app['registrar_action']) ?></strong></div><?php endif; ?>
          <?php if($app['registrar_remarks']): ?><div style="color:var(--gray-400);font-size:12px;"><?= h($app['registrar_remarks']) ?></div><?php endif; ?>
          <?php if($app['registrar_signature']): ?><img src="<?= $app['registrar_signature'] ?>" style="max-height:40px;border:1px solid var(--gray-200);border-radius:4px;margin-top:4px;"><?php endif; ?>
          <?php if(!$app['registrar_signed_at']): ?><span style="color:var(--gray-400);font-size:12px;">Pending</span><?php endif; ?>
        </div>

        <?php if($app['rejection_reason']): ?>
        <div class="alert alert-danger" style="margin-top:0.75rem;"><strong>Rejection Reason:</strong> <?= h($app['rejection_reason']) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
</div>
</body>
</html>
