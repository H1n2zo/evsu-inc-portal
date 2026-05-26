<?php
// views/admin/settings.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $activePage, $csrf, $msg, $settingsData, $view
$view->partial('layouts/head', get_defined_vars());
$s = fn(string $k): string => $view->e($settingsData[$k] ?? '');
?>
<body>
<div class="layout">
<?php $view->partial('layouts/admin_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div><h2>System Settings</h2><p>Configure academic period, session, and email settings</p></div>
    <span class="badge badge-danger">Admin Only</span>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= $view->e($msg) ?></div><?php endif; ?>

  <!-- CRUD: UPDATE system settings -->
  <form method="POST" action="<?= $view->url('admin/settings.php') ?>">
    <input type="hidden" name="csrf_token" value="<?= $view->e($csrf) ?>">

    <div class="content-card" style="margin-bottom:1.25rem;">
      <div class="card-head"><h3>Academic Period</h3></div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Current School Year</label>
            <input class="form-input" type="text" name="school_year" value="<?= $s('school_year') ?>" placeholder="2025-2026">
          </div>
          <div class="form-group">
            <label class="form-label">Active Semester</label>
            <select class="form-input" name="active_semester">
              <option <?= ($settingsData['active_semester']??'')==='1st'?'selected':'' ?>>1st</option>
              <option <?= ($settingsData['active_semester']??'')==='2nd'?'selected':'' ?>>2nd</option>
              <option <?= ($settingsData['active_semester']??'')==='Summer'?'selected':'' ?>>Summer</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="content-card" style="margin-bottom:1.25rem;">
      <div class="card-head"><h3>Session &amp; Security</h3></div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Session Timeout (minutes)</label>
            <input class="form-input" type="number" name="session_timeout" value="<?= $s('session_timeout') ?>" min="5" max="240">
            <p class="form-hint">Idle sessions expire after this duration. Minimum: 5 mins.</p>
          </div>
          <div class="form-group">
            <label class="form-label">Max Upload Size (MB)</label>
            <input class="form-input" type="number" name="max_upload_mb" value="<?= $s('max_upload_mb') ?>" min="1" max="20">
            <p class="form-hint">Maximum file size for receipt uploads.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="content-card" style="margin-bottom:1.25rem;">
      <div class="card-head">
        <h3>Email Notifications (SMTP / PHPMailer)</h3>
        <span class="badge badge-gray">Requires PHPMailer library</span>
      </div>
      <div class="card-body">
        <div class="alert alert-info" style="margin-bottom:1rem;">
          Configure with your institutional Google Workspace SMTP. Install PHPMailer via Composer:
          <code>composer require phpmailer/phpmailer</code>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">SMTP Host</label>
            <input class="form-input" type="text" name="smtp_host" value="<?= $s('smtp_host') ?>" placeholder="smtp.gmail.com">
          </div>
          <div class="form-group">
            <label class="form-label">SMTP Port</label>
            <input class="form-input" type="number" name="smtp_port" value="<?= $s('smtp_port') ?>" placeholder="587">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">SMTP Username</label>
            <input class="form-input" type="email" name="smtp_user" value="<?= $s('smtp_user') ?>" placeholder="noreply@evsu.edu.ph">
          </div>
          <div class="form-group">
            <label class="form-label">SMTP App Password</label>
            <input class="form-input" type="password" name="smtp_pass" value="<?= $s('smtp_pass') ?>" placeholder="Google App Password">
          </div>
        </div>
        <div class="form-group" style="max-width:320px;">
          <label class="form-label">From Name</label>
          <input class="form-input" type="text" name="smtp_from_name" value="<?= $s('smtp_from_name') ?>" placeholder="EVSU-OC INC Portal">
        </div>
      </div>
    </div>

    <div style="display:flex;gap:0.75rem;">
      <button type="submit" class="btn-primary">Save Settings</button>
      <a href="<?= $view->url('admin/dashboard.php') ?>" class="btn-sm" style="height:42px;padding:0 1.25rem;">Cancel</a>
    </div>
  </form>
</main>
</div>
</body>
</html>
