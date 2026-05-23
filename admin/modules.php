<?php
// admin/modules.php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $key     = $_POST['module_key'] ?? '';
    $enabled = isset($_POST['enabled']) ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE modules SET is_enabled=?, updated_by=? WHERE module_key=?");
    $stmt->execute([$enabled, $_SESSION['user_id'], $key]);
    $action = $enabled ? 'Module enabled' : 'Module disabled';
    auditLog($_SESSION['user_id'], $_SESSION['username'], 'admin', $action, "Module: $key", $_SERVER['REMOTE_ADDR']??'');
    $msg = 'Module updated successfully.';
}

$modules = $pdo->query("SELECT * FROM modules ORDER BY id")->fetchAll();

$activePage = 'modules';
$pageTitle  = 'Module Control';
include __DIR__ . '/../includes/head.php';
?>
<body>
<div class="layout">
<?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2>Module Control</h2>
      <p>Enable or disable portal features per role</p>
    </div>
    <span class="badge badge-danger">Admin Only</span>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>

  <div class="content-card">
    <?php
    $tabs = ['all'=>'All Modules','student'=>'Student','instructor'=>'Instructor','dept_head'=>'Dept. Head','registrar'=>'Registrar','auto'=>'Automatic'];
    $activeTab = $_GET['tab'] ?? 'all';
    if (!array_key_exists($activeTab, $tabs)) $activeTab = 'all';
    ?>
    <div class="tab-row" style="padding:0 1.375rem;">
      <?php foreach ($tabs as $tk => $tl): ?>
      <a href="?tab=<?= $tk ?>" class="tab <?= $activeTab===$tk?'active':'' ?>"><?= $tl ?></a>
      <?php endforeach; ?>
    </div>

    <?php foreach ($modules as $m):
      if ($activeTab !== 'all' && $m['target_role'] !== $activeTab) continue;
      $roleChipMap = ['student'=>'rc-student','instructor'=>'rc-instructor','dept_head'=>'rc-dept_head','registrar'=>'rc-registrar','auto'=>''];
      $roleLabel = ['student'=>'Student','instructor'=>'Instructor','dept_head'=>'Dept. Head','registrar'=>'Registrar','auto'=>'Auto'];
    ?>
    <form method="POST" action="" class="module-row">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="module_key" value="<?= h($m['module_key']) ?>">
      <div class="module-info">
        <h4>
          <?= h($m['module_name']) ?>
          <?php if ($m['target_role']): ?>
          <span class="role-chip <?= $roleChipMap[$m['target_role']] ?? 'badge-gray' ?>" style="font-size:10px;">
            <?= $roleLabel[$m['target_role']] ?? $m['target_role'] ?>
          </span>
          <?php endif; ?>
        </h4>
        <p><?= h($m['description']) ?></p>
      </div>
      <label class="toggle" title="<?= $m['is_enabled'] ? 'Click to disable' : 'Click to enable' ?>">
        <input type="checkbox" name="enabled" <?= $m['is_enabled'] ? 'checked' : '' ?> onchange="this.form.submit()">
        <div class="toggle-track"></div>
        <div class="toggle-thumb" style="left:<?= $m['is_enabled']?'21':'3' ?>px"></div>
      </label>
    </form>
    <?php endforeach; ?>
  </div>

  <div class="content-card" style="padding:1.25rem;">
    <p style="font-size:12.5px;color:var(--gray-400);line-height:1.7;">
      <strong style="color:var(--gray-600);">Note:</strong> Disabling a module prevents users from accessing that feature. Active INC applications already in that step will not be affected — only new actions will be blocked. Modules marked <strong>Auto</strong> are triggered automatically by the system workflow.
    </p>
  </div>
</main>
</div>
</body>
</html>
