<?php
// views/employee/application_view.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $activePage, $csrf, $app, $msg, $error, $stepLabels, $view
$view->partial('layouts/head', get_defined_vars());
$stepLabels = $stepLabels ?? [1=>'Student Filing',2=>'Instructor Input',3=>'Dept. Head Review',4=>'Payment Upload',5=>'Registrar Verify',6=>'Grade Posting',7=>'Resolved'];
?>
<body>
<div class="layout">
<?php $view->partial('layouts/employee_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2><?= $view->e($app['app_code']) ?></h2>
      <p><?= $view->e($app['full_name']) ?> — <?= $view->e($app['subject_name']) ?> (<?= $app['units'] ?> units)</p>
    </div>
    <?php
    $bm=['in_progress'=>'badge-info','pending_payment'=>'badge-gold','verification'=>'badge-gold','resolved'=>'badge-success','rejected'=>'badge-danger','draft'=>'badge-gray'];
    $lm=['in_progress'=>'In Progress','pending_payment'=>'Pending Payment','verification'=>'Verification','resolved'=>'Resolved','rejected'=>'Rejected','draft'=>'Draft'];
    ?>
    <span class="badge <?= $bm[$app['status']]??'badge-gray' ?>"><?= $lm[$app['status']]??ucfirst($app['status']) ?></span>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= $view->e($msg) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= $view->e($error) ?></div><?php endif; ?>

  <!-- Step tracker -->
  <div class="content-card" style="padding:1.25rem;margin-bottom:1.25rem;">
    <div class="step-tracker">
      <?php for($s=1;$s<=7;$s++):
        $done   = $app['current_step'] > $s;
        $active = $app['current_step'] == $s;
      ?>
      <div class="step-item">
        <div class="step-dot <?= $done?'done':($active?'active':'') ?>"><?= $done ? '✓' : $s ?></div>
        <div class="step-label <?= $active?'active':'' ?>" style="font-size:10px;"><?= $stepLabels[$s] ?></div>
      </div>
      <?php if ($s < 7): ?><div class="step-line <?= $done?'done':'' ?>"></div><?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">
    <!-- Application details -->
    <div class="content-card">
      <div class="card-head"><h3>Application Details</h3></div>
      <div class="card-body" style="font-size:13.5px;">
        <table style="width:100%;border-collapse:collapse;">
          <?php $rows=[['Student',$view->e($app['full_name'])],['Student ID',$view->e($app['stu_id']??'—')],['Subject',$view->e($app['subject_name'])],['Subject Code',$view->e($app['subject_code'])],['Units',$app['units']],['Processing Fee','₱'.number_format($app['processing_fee'],0)],['Semester',$view->e($app['semester'])],['School Year',$view->e($app['school_year'])],['Filed',date('M d, Y H:i',strtotime($app['created_at']))]]; ?>
          <?php foreach($rows as [$label,$val]): ?>
          <tr><td style="color:var(--gray-400);padding:5px 0;width:40%;"><?= $label ?></td><td style="padding:5px 0;font-weight:500;"><?= $val ?></td></tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>

    <!-- Workflow history -->
    <div class="content-card">
      <div class="card-head"><h3>Workflow History</h3></div>
      <div class="card-body" style="font-size:13px;">
        <?php if ($app['instructor_signed_at']): ?>
        <div style="margin-bottom:0.875rem;padding-bottom:0.875rem;border-bottom:1px solid var(--gray-100);">
          <div style="display:flex;justify-content:space-between;"><span class="role-chip rc-instructor">Instructor</span><span style="font-size:11.5px;color:var(--gray-400);"><?= date('M d, Y', strtotime($app['instructor_signed_at'])) ?></span></div>
          <div style="margin-top:4px;color:var(--gray-600);">Grade entered: <strong><?= $view->e($app['instructor_grade']??'—') ?></strong></div>
          <?php if ($app['instructor_remarks']): ?><div style="font-size:12px;color:var(--gray-400);margin-top:2px;"><?= $view->e($app['instructor_remarks']) ?></div><?php endif; ?>
          <?php if ($app['instructor_signature']): ?><div style="margin-top:6px;"><img src="<?= $app['instructor_signature'] ?>" style="max-height:40px;border:1px solid var(--gray-200);border-radius:4px;" title="Instructor signature"></div><?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($app['dept_head_signed_at']): ?>
        <div style="margin-bottom:0.875rem;padding-bottom:0.875rem;border-bottom:1px solid var(--gray-100);">
          <div style="display:flex;justify-content:space-between;"><span class="role-chip rc-dept_head">Dept. Head</span><span style="font-size:11.5px;color:var(--gray-400);"><?= date('M d, Y', strtotime($app['dept_head_signed_at'])) ?></span></div>
          <div style="margin-top:4px;color:var(--gray-600);">Decision: <strong style="color:<?= $app['dept_head_action']==='approved'?'var(--success)':'var(--danger)' ?>"><?= ucfirst($app['dept_head_action']??'—') ?></strong></div>
          <?php if ($app['dept_head_remarks']): ?><div style="font-size:12px;color:var(--gray-400);margin-top:2px;"><?= $view->e($app['dept_head_remarks']) ?></div><?php endif; ?>
          <?php if ($app['dept_head_signature']): ?><div style="margin-top:6px;"><img src="<?= $app['dept_head_signature'] ?>" style="max-height:40px;border:1px solid var(--gray-200);border-radius:4px;" title="Dept. Head signature"></div><?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($app['or_number']): ?>
        <div style="margin-bottom:0.875rem;padding-bottom:0.875rem;border-bottom:1px solid var(--gray-100);">
          <div><span class="role-chip rc-student">Student</span></div>
          <div style="margin-top:4px;color:var(--gray-600);">O.R. No.: <strong><?= $view->e($app['or_number']) ?></strong></div>
          <?php if ($app['receipt_filename']): ?>
          <a href="<?= $view->asset('uploads/') . $view->e($app['receipt_filename']) ?>" target="_blank" class="btn-sm" style="margin-top:6px;">View Receipt</a>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($app['registrar_signed_at']): ?>
        <div>
          <div style="display:flex;justify-content:space-between;"><span class="role-chip rc-registrar">Registrar</span><span style="font-size:11.5px;color:var(--gray-400);"><?= date('M d, Y', strtotime($app['registrar_signed_at'])) ?></span></div>
          <div style="margin-top:4px;color:var(--gray-600);">Grade posted. Application Resolved.</div>
          <?php if ($app['registrar_signature']): ?><div style="margin-top:6px;"><img src="<?= $app['registrar_signature'] ?>" style="max-height:40px;border:1px solid var(--gray-200);border-radius:4px;" title="Registrar signature"></div><?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($app['rejection_reason'] && $app['status']==='rejected'): ?>
        <div class="alert alert-danger" style="margin-top:0.5rem;">
          <strong>Rejection Reason:</strong> <?= $view->e($app['rejection_reason']) ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ── ACTION PANELS ── -->

  <?php if ($activeRole==='instructor' && $app['current_step']==2 && $app['status']==='in_progress'): ?>
  <div class="content-card">
    <div class="card-head"><h3>Step 2 — Enter Final Grade &amp; Sign</h3></div>
    <div class="card-body">
      <form method="POST" action="<?= $view->url('employee/application_view.php') ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="instructor_sign">
        <input type="hidden" name="signature_data" id="sigData">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Resolved Final Grade <span style="color:var(--danger)">*</span></label>
            <input class="form-input" type="text" name="grade" placeholder="e.g. 1.50 or 75" maxlength="10" required>
            <p class="form-hint">Enter the resolved/passed grade for this INC subject.</p>
          </div>
          <div class="form-group">
            <label class="form-label">Remarks (optional)</label>
            <input class="form-input" type="text" name="remarks" placeholder="Additional notes…">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">E-Signature <span style="color:var(--danger)">*</span></label>
          <div class="sig-canvas-wrap" style="max-width:420px;">
            <canvas id="sigCanvas" width="400" height="100"></canvas>
          </div>
          <div class="sig-actions">
            <button type="button" class="btn-sm danger" onclick="clearSig()">Clear</button>
            <span id="sigStatus" style="font-size:12px;color:var(--gray-400);align-self:center;">Draw your signature above</span>
          </div>
        </div>
        <button type="submit" class="btn-primary" onclick="return captureSig()">Submit Grade &amp; Sign</button>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($activeRole==='dept_head' && $app['current_step']==3 && $app['status']==='in_progress'): ?>
  <div class="content-card">
    <div class="card-head"><h3>Step 3 — Department Head Review</h3></div>
    <div class="card-body">
      <div class="alert alert-info" style="margin-bottom:1rem;">
        Instructor entered grade: <strong><?= $view->e($app['instructor_grade']??'—') ?></strong>
      </div>
      <form method="POST" action="<?= $view->url('employee/application_view.php') ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="signature_data" id="sigData">
        <div class="form-group">
          <label class="form-label">Remarks</label>
          <textarea class="form-input" name="remarks" rows="3" placeholder="Optional remarks…"></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">E-Signature (required for approval)</label>
          <div class="sig-canvas-wrap" style="max-width:420px;">
            <canvas id="sigCanvas" width="400" height="100"></canvas>
          </div>
          <div class="sig-actions">
            <button type="button" class="btn-sm danger" onclick="clearSig()">Clear</button>
          </div>
        </div>
        <div style="display:flex;gap:0.75rem;">
          <button type="submit" name="action" value="depthead_approve" class="btn-primary" onclick="return captureSig()">
            ✓ Approve
          </button>
          <button type="submit" name="action" value="depthead_reject" class="btn-sm danger" style="height:42px;padding:0 1.25rem;" onclick="document.getElementById('sigData').value='REJECTED';">
            ✕ Reject
          </button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($activeRole==='registrar' && $app['current_step']==5): ?>
  <!-- Split-view O.R. verification -->
  <div class="content-card">
    <div class="card-head"><h3>Step 5 — O.R. Ledger Verification</h3></div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
        <!-- Left: OR number input -->
        <div>
          <p class="form-section-title">Official Receipt Details</p>
          <form method="POST" action="<?= $view->url('employee/application_view.php') ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div class="form-group">
              <label class="form-label">O.R. Number on Ledger</label>
              <input class="form-input" type="text" value="<?= $view->e($app['or_number']??'') ?>" readonly style="background:var(--gray-100);">
              <p class="form-hint">O.R. number submitted by student</p>
            </div>
            <div class="form-group">
              <label class="form-label">Processing Fee</label>
              <input class="form-input" type="text" value="₱<?= number_format($app['processing_fee'],0) ?>" readonly style="background:var(--gray-100);">
            </div>
            <div class="form-group">
              <label class="form-label">Verification Remarks</label>
              <textarea class="form-input" name="remarks" rows="2" placeholder="Optional remarks…"></textarea>
            </div>
            <div style="display:flex;gap:0.75rem;">
              <button type="submit" name="action" value="registrar_verify" class="btn-primary">✓ O.R. Verified</button>
              <button type="submit" name="action" value="registrar_reject_or" class="btn-sm danger" style="height:42px;padding:0 1rem;">✕ Reject</button>
            </div>
          </form>
        </div>
        <!-- Right: Receipt image -->
        <div>
          <p class="form-section-title">Uploaded Receipt</p>
          <?php if ($app['receipt_filename']): ?>
          <div style="border:1px solid var(--gray-200);border-radius:var(--radius-sm);overflow:hidden;">
            <img src="<?= $view->asset('uploads/') ?><?= $view->e($app['receipt_filename']) ?>"
                 style="width:100%;max-height:340px;object-fit:contain;background:var(--gray-100);"
                 alt="Receipt">
          </div>
          <a href="<?= $view->asset('uploads/') . $view->e($app['receipt_filename']) ?>" target="_blank" class="btn-sm" style="margin-top:8px;">Open Full Size</a>
          <?php else: ?>
          <div style="border:1px dashed var(--gray-200);border-radius:var(--radius-sm);padding:3rem;text-align:center;color:var(--gray-400);">No receipt uploaded yet.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($activeRole==='registrar' && $app['current_step']==6): ?>
  <div class="content-card">
    <div class="card-head"><h3>Step 6 — Post Final Grade</h3></div>
    <div class="card-body">
      <div class="alert alert-gold" style="margin-bottom:1rem;">
        Ready to post final grade: <strong><?= $view->e($app['instructor_grade']??'—') ?></strong> for <strong><?= $view->e($app['subject_name']) ?></strong>
      </div>
      <form method="POST" action="<?= $view->url('employee/application_view.php') ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="post_grade">
        <input type="hidden" name="signature_data" id="sigData">
        <div class="form-group">
          <label class="form-label">Remarks</label>
          <textarea class="form-input" name="remarks" rows="2" placeholder="Optional registrar remarks…"></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Registrar E-Signature <span style="color:var(--danger)">*</span></label>
          <div class="sig-canvas-wrap" style="max-width:420px;">
            <canvas id="sigCanvas" width="400" height="100"></canvas>
          </div>
          <div class="sig-actions">
            <button type="button" class="btn-sm danger" onclick="clearSig()">Clear</button>
          </div>
        </div>
        <button type="submit" class="btn-primary" onclick="return captureSig()">✓ Post Grade &amp; Resolve</button>
      </form>
    </div>
  </div>
  <?php endif; ?>

</main>
</div>

<script>
// ── E-SIGNATURE CANVAS ──────────────────────────────────────────
const canvas = document.getElementById('sigCanvas');
if (canvas) {
  const ctx = canvas.getContext('2d');
  let drawing = false;

  function getPos(e) {
    const r = canvas.getBoundingClientRect();
    const src = e.touches ? e.touches[0] : e;
    return { x: src.clientX - r.left, y: src.clientY - r.top };
  }

  canvas.addEventListener('mousedown',  e => { drawing=true; const p=getPos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); });
  canvas.addEventListener('mousemove',  e => { if(!drawing) return; const p=getPos(e); ctx.lineWidth=1.8; ctx.lineCap='round'; ctx.strokeStyle='#1C1410'; ctx.lineTo(p.x,p.y); ctx.stroke(); updateStatus(); });
  canvas.addEventListener('mouseup',    () => drawing=false);
  canvas.addEventListener('mouseleave', () => drawing=false);
  canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing=true; const p=getPos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); });
  canvas.addEventListener('touchmove',  e => { e.preventDefault(); if(!drawing) return; const p=getPos(e); ctx.lineWidth=1.8; ctx.lineCap='round'; ctx.strokeStyle='#1C1410'; ctx.lineTo(p.x,p.y); ctx.stroke(); updateStatus(); });
  canvas.addEventListener('touchend',   () => drawing=false);
}

function updateStatus() {
  const el = document.getElementById('sigStatus');
  if (el) el.textContent = 'Signature captured ✓';
}

function clearSig() {
  if (!canvas) return;
  canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
  const el = document.getElementById('sigStatus');
  if (el) el.textContent = 'Draw your signature above';
  const sd = document.getElementById('sigData');
  if (sd) sd.value = '';
}

function captureSig() {
  if (!canvas) return true;
  const sd = document.getElementById('sigData');
  if (!sd) return true;
  const data = canvas.toDataURL('image/png');
  // Check if canvas is blank
  const blank = document.createElement('canvas');
  blank.width = canvas.width; blank.height = canvas.height;
  if (data === blank.toDataURL('image/png')) {
    alert('Please draw your e-signature before submitting.');
    return false;
  }
  sd.value = data;
  return true;
}
</script>
</body>
</html>
