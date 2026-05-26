<?php
// views/admin/modules.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $activePage, $csrf, $msg, $modules, $activeTab, $view
$view->partial('layouts/head', get_defined_vars());
$tabs = ['all'=>'All Modules','student'=>'Student','instructor'=>'Instructor','dept_head'=>'Dept. Head','registrar'=>'Registrar','auto'=>'Automatic'];
?>
<body>
<div class="layout">
<?php $view->partial('layouts/admin_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div><h2>Module Control</h2><p>Enable or disable portal features per role</p></div>
    <span class="badge badge-danger">Admin Only</span>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= $view->e($msg) ?></div><?php endif; ?>

  <div class="content-card">
    <div class="tab-row" style="padding:0 1.375rem;">
      <?php foreach ($tabs as $tk => $tl): ?>
      <a href="?tab=<?= $tk ?>" class="tab <?= $activeTab === $tk ? 'active' : '' ?>"><?= $tl ?></a>
      <?php endforeach; ?>
    </div>

    <?php
    $roleChipMap  = ['student'=>'rc-student','instructor'=>'rc-instructor','dept_head'=>'rc-dept_head','registrar'=>'rc-registrar','auto'=>''];
    $roleLabel    = ['student'=>'Student','instructor'=>'Instructor','dept_head'=>'Dept. Head','registrar'=>'Registrar','auto'=>'Auto'];
    foreach ($modules as $m):
      if ($activeTab !== 'all' && $m['target_role'] !== $activeTab) continue;
    ?>
    <!-- CRUD: UPDATE module enabled state -->
    <form method="POST" action="<?= $view->url('admin/modules.php') ?>" class="module-row">
      <input type="hidden" name="csrf_token" value="<?= $view->e($csrf) ?>">
      <input type="hidden" name="module_key" value="<?= $view->e($m['module_key']) ?>">
      <div class="module-info">
        <h4>
          <?= $view->e($m['module_name']) ?>
          <?php if ($m['target_role']): ?>
          <span class="role-chip <?= $roleChipMap[$m['target_role']] ?? 'badge-gray' ?>" style="font-size:10px;">
            <?= $roleLabel[$m['target_role']] ?? $view->e($m['target_role']) ?>
          </span>
          <?php endif; ?>
        </h4>
        <p><?= $view->e($m['description']) ?></p>
      </div>
      <label class="toggle" title="<?= $m['is_enabled'] ? 'Click to disable' : 'Click to enable' ?>">
        <input type="checkbox" name="enabled" <?= $m['is_enabled'] ? 'checked' : '' ?> onchange="this.form.submit()">
        <div class="toggle-track"></div>
        <div class="toggle-thumb" style="left:<?= $m['is_enabled'] ? '21' : '3' ?>px"></div>
      </label>
    </form>
    <?php endforeach; ?>
  </div>

  <div class="content-card" style="padding:1.25rem;">
    <p style="font-size:12.5px;color:var(--gray-400);line-height:1.7;">
      <strong style="color:var(--gray-600);">Note:</strong> Disabling a module prevents users from accessing that feature.
      Active INC applications already in that step will not be affected — only new actions will be blocked.
      Modules marked <strong>Auto</strong> are triggered automatically by the system workflow.
    </p>
  </div>
</main>
</div>
</body>
</html>
