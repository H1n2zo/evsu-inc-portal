<?php
// views/admin/logs.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $activePage, $csrf, $logs, $filters, $page, $pages, $total, $view
$view->partial('layouts/head', get_defined_vars());
$search = $filters['search'];
$role   = $filters['role'];
?>
<body>
<div class="layout">
<?php $view->partial('layouts/admin_sidebar', get_defined_vars()); ?>
<main class="main-content">
  <div class="top-bar">
    <div><h2>Audit Logs</h2><p>Immutable activity records — read-only</p></div>
    <span class="badge badge-danger">Read-Only</span>
  </div>

  <!-- READ: filter audit log entries -->
  <form method="GET" action="<?= $view->url('admin/logs.php') ?>" class="content-card" style="padding:0.875rem 1.375rem;margin-bottom:1rem;">
    <div class="filter-bar">
      <input class="form-input" type="text" name="q" placeholder="Search user, action, IP…" value="<?= $view->e($search) ?>">
      <select class="form-input" name="role" style="max-width:160px;height:36px;font-size:13px;">
        <option value="">All roles</option>
        <?php foreach (['admin','instructor','dept_head','registrar','student'] as $r): ?>
        <option value="<?= $r ?>" <?= $role === $r ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $r)) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-sm maroon">Filter</button>
      <a href="<?= $view->url('admin/logs.php') ?>" class="btn-sm">Clear</a>
      <span style="margin-left:auto;font-size:12px;color:var(--gray-400);"><?= number_format($total) ?> entries</span>
    </div>
  </form>

  <div class="content-card">
    <div class="card-body" style="padding:0;">
      <?php if (empty($logs)): ?>
        <div class="empty-state"><p>No log entries found.</p></div>
      <?php else: ?>
      <table class="data-table">
        <thead><tr>
          <th>Time</th><th>User</th><th>Role</th><th>Action</th><th>Description</th><th>IP</th>
        </tr></thead>
        <tbody>
        <?php foreach ($logs as $l): ?>
        <tr>
          <td style="font-size:11.5px;color:var(--gray-400);white-space:nowrap;"><?= $view->date($l['created_at'], 'M d, H:i') ?></td>
          <td style="font-weight:500;"><?= $view->e($l['username']) ?></td>
          <td><span class="role-chip rc-<?= $view->e($l['active_role'] ?? 'guest') ?>" style="font-size:10px;"><?= $view->e(ucfirst($l['active_role'] ?? '—')) ?></span></td>
          <td style="font-weight:500;"><?= $view->e($l['action']) ?></td>
          <td style="color:var(--gray-400);font-size:12.5px;"><?= $view->e($l['description'] ?? '') ?></td>
          <td style="font-size:11.5px;color:var(--gray-400);"><?= $view->e($l['ip_address']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($pages > 1): ?>
  <div class="filter-bar" style="justify-content:center;margin-top:1rem;">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
    <a href="<?= $view->url('admin/logs.php') ?>?page=<?= $i ?>&q=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>"
       class="btn-sm <?= $i === $page ? 'maroon' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</main>
</div>
</body>
</html>
