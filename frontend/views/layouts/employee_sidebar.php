<?php
// views/layouts/employee_sidebar.php
// PRESENTATION LAYER — employee navigation sidebar
// Relies on $_SESSION and $view (injected by View::partial())
$activePage    = $activePage ?? '';
$activeRole    = $_SESSION['active_role']  ?? 'instructor';
$assignedRoles = $_SESSION['roles']        ?? [];

$nameParts = explode(' ', trim($_SESSION['full_name'] ?? 'E'));
$initials  = strtoupper(substr($nameParts[0], 0, 1));
if (isset($nameParts[1])) { $initials .= strtoupper(substr($nameParts[1], 0, 1)); }

$roleLabels = [
    'instructor' => 'Instructor',
    'dept_head'  => 'Department Head',
    'registrar'  => 'Registrar',
];
?>
<nav class="sidebar">
  <div class="sidebar-brand">
    <div class="emblem">E</div>
    <h1>EVSU – OC <span>INC Form Portal</span></h1>
  </div>

  <!-- Role switcher — only shown when the user holds multiple roles -->
  <?php if (count($assignedRoles) > 1): ?>
  <div class="role-switcher">
    <label>Active Role</label>
    <form method="POST" action="<?= $view->url('employee/switch_role.php') ?>">
      <input type="hidden" name="csrf_token" value="<?= $view->e($csrf ?? '') ?>">
      <select name="role" onchange="this.form.submit()">
        <?php foreach ($assignedRoles as $r): ?>
        <option value="<?= $view->e($r) ?>" <?= $activeRole === $r ? 'selected' : '' ?>>
          <?= $view->e($roleLabels[$r] ?? ucfirst($r)) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
  <?php else: ?>
  <div style="padding:0 1.25rem 0.5rem;">
    <span style="font-size:10.5px;color:rgba(255,255,255,0.35);">Role:</span>
    <span style="font-size:12px;color:var(--gold-light);font-weight:500;margin-left:5px;">
      <?= $view->e($roleLabels[$activeRole] ?? ucfirst($activeRole)) ?>
    </span>
  </div>
  <?php endif; ?>

  <span class="nav-section-label">Overview</span>
  <a href="<?= $view->url('employee/dashboard.php') ?>"
     class="nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
      <rect x="3" y="3" width="7" height="7" rx="1"/>
      <rect x="14" y="3" width="7" height="7" rx="1"/>
      <rect x="3" y="14" width="7" height="7" rx="1"/>
      <rect x="14" y="14" width="7" height="7" rx="1"/>
    </svg>
    Dashboard
  </a>
  <a href="<?= $view->url('employee/applications.php') ?>"
     class="nav-item <?= $activePage === 'applications' ? 'active' : '' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
      <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    All Applications
  </a>

  <!-- Role-specific nav items -->
  <?php if ($activeRole === 'instructor'): ?>
  <span class="nav-section-label">Instructor Actions</span>
  <a href="<?= $view->url('employee/grade_input.php') ?>"
     class="nav-item <?= $activePage === 'grade_input' ? 'active' : '' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
      <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
      <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
    </svg>
    Grade Input
  </a>

  <?php elseif ($activeRole === 'dept_head'): ?>
  <span class="nav-section-label">Dept. Head Actions</span>
  <a href="<?= $view->url('employee/dept_review.php') ?>"
     class="nav-item <?= $activePage === 'dept_review' ? 'active' : '' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
      <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
      <polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
    Review &amp; Approve
  </a>

  <?php elseif ($activeRole === 'registrar'): ?>
  <span class="nav-section-label">Registrar Actions</span>
  <a href="<?= $view->url('employee/or_verify.php') ?>"
     class="nav-item <?= $activePage === 'or_verify' ? 'active' : '' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
      <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
      <polyline points="14 2 14 8 20 8"/>
      <line x1="16" y1="13" x2="8" y2="13"/>
      <line x1="16" y1="17" x2="8" y2="17"/>
    </svg>
    O.R. Verification
  </a>
  <a href="<?= $view->url('employee/grade_posting.php') ?>"
     class="nav-item <?= $activePage === 'grade_posting' ? 'active' : '' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
      <polyline points="9 11 12 14 22 4"/>
      <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
    </svg>
    Grade Posting
  </a>
  <?php endif; ?>

  <div class="sidebar-footer">
    <div class="user-chip">
      <div class="user-avatar"><?= $view->e($initials) ?></div>
      <div class="user-info">
        <p><?= $view->e($_SESSION['full_name'] ?? '') ?></p>
        <span><?= $view->e($roleLabels[$activeRole] ?? ucfirst($activeRole)) ?></span>
      </div>
    </div>
    <a href="<?= $view->url('logout.php') ?>"
       style="display:flex;align-items:center;gap:6px;font-size:12px;color:rgba(255,255,255,0.4);margin-top:0.75rem;text-decoration:none;">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
      </svg>
      Sign Out
    </a>
  </div>
</nav>
