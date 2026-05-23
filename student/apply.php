<?php
// student/apply.php — File new INC application
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
if ($_SESSION['account_type'] !== 'student') { header('Location: /index.php'); exit; }

// Check module enabled
$pdo = getDB();
$mod = $pdo->prepare("SELECT is_enabled FROM modules WHERE module_key='inc_filing'");
$mod->execute(); $mod = $mod->fetch();
if ($mod && !$mod['is_enabled']) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#6B0F1A;">INC Form Filing is currently disabled by the administrator.</div>');
}

$uid = $_SESSION['user_id'];
$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $subject_name = trim($_POST['subject_name'] ?? '');
    $subject_code = trim($_POST['subject_code'] ?? '');
    $units        = (int)($_POST['units'] ?? 3);
    $semester     = $_POST['semester'] ?? '2nd';
    $school_year  = getSetting('school_year') ?? date('Y').'-'.(date('Y')+1);
    $instructor_id= (int)($_POST['instructor_id'] ?? 0);
    $dept_head_id = (int)($_POST['dept_head_id'] ?? 0);

    if (!$subject_name || !$subject_code || $units < 1) {
        $error = 'Please fill in all required fields.';
    } elseif ($units < 1 || $units > 6) {
        $error = 'Units must be between 1 and 6.';
    } else {
        // Prevent duplicate submissions
        $dup = $pdo->prepare("SELECT id FROM inc_applications WHERE student_id=? AND subject_code=? AND semester=? AND school_year=? AND status != 'rejected'");
        $dup->execute([$uid, $subject_code, $semester, $school_year]);
        if ($dup->fetch()) {
            $error = "An active application for $subject_code this semester already exists.";
        } else {
            // Generate app code
            $count    = (int)$pdo->query("SELECT COUNT(*)+1 FROM inc_applications")->fetchColumn();
            $app_code = 'INC-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $ins = $pdo->prepare("INSERT INTO inc_applications
                (app_code, student_id, subject_name, subject_code, units, semester, school_year,
                 instructor_id, dept_head_id, current_step, status)
                VALUES (?,?,?,?,?,?,?,?,?,2,'in_progress')");
            $ins->execute([$app_code, $uid, $subject_name, $subject_code, $units,
                           $semester, $school_year,
                           $instructor_id ?: null, $dept_head_id ?: null]);
            $newId = $pdo->lastInsertId();
            auditLog($uid, $_SESSION['username'], 'student', 'Application Filed', "App $app_code — $subject_code", $_SERVER['REMOTE_ADDR']??'');
            header("Location: /student/application_view.php?id=$newId&filed=1");
            exit;
        }
    }
}

// Fetch instructors and dept heads for assignment
$instructors = $pdo->query("SELECT u.id, u.full_name, u.department FROM users u
    JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON ur.role_id=r.id
    WHERE r.role_name='instructor' AND u.status='active' ORDER BY u.full_name")->fetchAll();
$deptHeads = $pdo->query("SELECT u.id, u.full_name, u.department FROM users u
    JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON ur.role_id=r.id
    WHERE r.role_name='dept_head' AND u.status='active' ORDER BY u.full_name")->fetchAll();

$activePage = 'apply'; $pageTitle = 'New Application';
include __DIR__ . '/../includes/head.php';
?>
<body>
<div class="layout">
<?php include __DIR__ . '/../includes/student_sidebar.php'; ?>
<main class="main-content">
  <div class="top-bar">
    <div><h2>New INC Application</h2><p>File an incomplete grade completion request</p></div>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

  <div class="content-card" style="max-width:680px;">
    <div class="card-head"><h3>INC Form Details</h3></div>
    <div class="card-body">
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <p class="form-section-title">Subject Information</p>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Subject Name <span style="color:var(--danger)">*</span></label>
            <input class="form-input" type="text" name="subject_name" placeholder="e.g. Mathematics 201" required>
          </div>
          <div class="form-group">
            <label class="form-label">Subject Code <span style="color:var(--danger)">*</span></label>
            <input class="form-input" type="text" name="subject_code" placeholder="e.g. MATH 201" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Units <span style="color:var(--danger)">*</span></label>
            <select class="form-input" name="units" id="unitsSelect" onchange="updateFee()">
              <option value="1">1 unit</option>
              <option value="2">2 units</option>
              <option value="3" selected>3 units</option>
              <option value="4">4 units</option>
              <option value="5">5 units</option>
              <option value="6">6 units</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Semester</label>
            <select class="form-input" name="semester">
              <option value="1st">1st Semester</option>
              <option value="2nd" selected>2nd Semester</option>
              <option value="Summer">Summer</option>
            </select>
          </div>
        </div>

        <!-- Auto fee display -->
        <div style="background:var(--gold-pale);border:1px solid #FDE68A;border-radius:var(--radius-sm);padding:1rem 1.25rem;margin-bottom:1.25rem;">
          <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:13.5px;color:var(--gray-600);">Computed Processing Fee</span>
            <span style="font-family:'Playfair Display',serif;font-size:22px;color:var(--maroon);font-weight:700;" id="feeDisplay">₱150</span>
          </div>
          <p style="font-size:12px;color:var(--gray-400);margin-top:3px;">Formula: Units × ₱50.00</p>
        </div>

        <p class="form-section-title">Assign Faculty</p>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Instructor</label>
            <select class="form-input" name="instructor_id">
              <option value="">— Select Instructor —</option>
              <?php foreach ($instructors as $i): ?>
              <option value="<?= $i['id'] ?>"><?= h($i['full_name']) ?><?= $i['department']?' ('.$i['department'].')':'' ?></option>
              <?php endforeach; ?>
            </select>
            <p class="form-hint">The instructor who assigned your INC grade.</p>
          </div>
          <div class="form-group">
            <label class="form-label">Department Head</label>
            <select class="form-input" name="dept_head_id">
              <option value="">— Select Dept. Head —</option>
              <?php foreach ($deptHeads as $d): ?>
              <option value="<?= $d['id'] ?>"><?= h($d['full_name']) ?><?= $d['department']?' ('.$d['department'].')':'' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="alert alert-info" style="margin-bottom:1rem;">
          After submission, your instructor will be notified to enter the resolved final grade and sign the form.
        </div>

        <div style="display:flex;gap:0.75rem;">
          <button type="submit" class="btn-primary">Submit Application</button>
          <a href="/student/dashboard.php" class="btn-sm" style="height:42px;padding:0 1.25rem;">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</main>
</div>
<script>
function updateFee() {
  const units = parseInt(document.getElementById('unitsSelect').value) || 3;
  document.getElementById('feeDisplay').textContent = '₱' + (units * 50).toLocaleString();
}
</script>
</body>
</html>
