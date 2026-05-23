<?php
// admin/logs.php — Immutable audit trail
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDB();

$search = trim($_GET['q'] ?? '');
$role   = $_GET['role'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 30;
$offset  = ($page - 1) * $perPage;

$where = ['1=1']; $params = [];
if ($search) {
    $where[] = "(username LIKE ? OR action LIKE ? OR description LIKE ? OR ip_address LIKE ?)";
    $params = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]);
}
if ($role) { $where[] = "active_role = ?"; $params[] = $role; }
$whereSQL = implode(' AND ', $where);

$total = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE $whereSQL");
$total->execute($params); $total = (int)$total->fetchColumn();
$pages = ceil($total / $perPage);

$stmt = $pdo->prepare("SELECT * FROM audit_logs WHERE $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$logs = $stmt->fetchAll();

$activePage = 'logs';
$pageTitle  = 'Audit Logs';
include __DIR__ . '/../includes/head.php';
?>
<body>
<div class="layout">
<?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
<main class="main-content">
  <div class="top-bar">
    <div>
      <h2>Audit Logs</h2>
      <p>Immutable activity records — read-only</p>
    </div>
    <span class="badge badge-danger">Read-Only</span>
  </div>

  <form method="GET" action="" class="content-card" style="padding:0.875rem 1.375rem;margin-bottom:1rem;">
    <div class="filter-bar">
      <input class="form-input" type="text" name="q" placeholder="Search user, action, IP…" value="<?= h($search) ?>">
      <select class="form-input" name="role" style="max-width:160px;height:36px;font-size:13px;">
        <option value="">All roles</option>
        <option value="admin" <?= $role==='admin'?'selected':'' ?>>Admin</option>
        <option value="instructor" <?= $role==='instructor'?'selected':'' ?>>Instructor</option>
        <option value="dept_head" <?= $role==='dept_head'?'selected':'' ?>>Dept. Head</option>
        <option value="registrar" <?= $role==='registrar'?'selected':'' ?>>Registrar</option>
        <option value="student" <?= $role==='student'?'selected':'' ?>>Student</option>
      </select>
      <button type="submit" class="btn-sm maroon">Filter</button>
      <a href="/evsu_inc_portal/admin/logs.php" class="btn-sm">Clear</a>
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
          <th>Timestamp</th><th>User</th><th>Active Role</th><th>Action</th><th>Description</th><th>IP Address</th>
        </tr></thead>
        <tbody>
        <?php foreach ($logs as $log):
          $roleChipMap = ['admin'=>'rc-admin','instructor'=>'rc-instructor','dept_head'=>'rc-dept_head','registrar'=>'rc-registrar','student'=>'rc-student'];
          $rc = $roleChipMap[$log['active_role']] ?? 'badge-gray';
        ?>
        <tr>
          <td style="font-size:11.5px;color:var(--gray-400);white-space:nowrap;"><?= h($log['created_at']) ?></td>
          <td style="font-size:12.5px;"><?= h($log['username'] ?? '—') ?></td>
          <td>
            <?php if ($log['active_role']): ?>
              <span class="role-chip <?= $rc ?>"><?= h(str_replace('_','. ',ucfirst($log['active_role']))) ?></span>
            <?php else: ?>
              <span style="color:var(--gray-400);font-size:12px;">—</span>
            <?php endif; ?>
          </td>
          <td style="font-size:12.5px;font-weight:500;"><?= h($log['action']) ?></td>
          <td style="font-size:12px;color:var(--gray-600);"><?= h($log['description'] ?? '—') ?></td>
          <td style="font-size:11.5px;color:var(--gray-400);"><?= h($log['ip_address'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <?php if ($pages > 1): ?>
    <div style="padding:0.875rem 1.375rem;border-top:1px solid var(--gray-100);display:flex;align-items:center;gap:6px;">
      <?php for ($p=1; $p<=$pages; $p++): ?>
      <a href="?q=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>&page=<?= $p ?>"
         class="btn-sm <?= $p===$page?'maroon':'' ?>" style="min-width:32px;justify-content:center;"><?= $p ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</main>
</div>
</body>
</html>
