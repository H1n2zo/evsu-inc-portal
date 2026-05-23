<?php
// includes/admin_sidebar.php
// Requires: $activePage variable set before include
$activePage = $activePage ?? '';
$initials = strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 2));
?>
<nav class="sidebar">
  <div class="sidebar-brand">
    <div class="emblem">E</div>
    <h1>EVSU – OC <span>INC Form Portal</span></h1>
  </div>

  <span class="nav-section-label">Overview</span>
  <a href="/admin/dashboard.php" class="nav-item <?= $activePage==='dashboard'?'active':'' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
    Dashboard
  </a>
  <a href="/admin/applications.php" class="nav-item <?= $activePage==='applications'?'active':'' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    Applications
  </a>

  <span class="nav-section-label">Administration</span>
  <a href="/admin/users.php" class="nav-item <?= $activePage==='users'?'active':'' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
    Users &amp; Roles
  </a>
  <a href="/admin/rbac.php" class="nav-item <?= $activePage==='rbac'?'active':'' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    RBAC Config
  </a>
  <a href="/admin/modules.php" class="nav-item <?= $activePage==='modules'?'active':'' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 2l9 4.9V17L12 22 3 17V6.9L12 2z"/><path d="M12 22V12M12 12L3 7M12 12l9-5"/></svg>
    Module Control
  </a>
  <a href="/admin/settings.php" class="nav-item <?= $activePage==='settings'?'active':'' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
    Settings
  </a>
  <a href="/admin/logs.php" class="nav-item <?= $activePage==='logs'?'active':'' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
    Audit Logs
  </a>

  <div class="sidebar-footer">
    <div class="user-chip">
      <div class="user-avatar"><?= h($initials) ?></div>
      <div class="user-info">
        <p><?= h($_SESSION['full_name'] ?? 'Admin') ?></p>
        <span>System Administrator</span>
      </div>
    </div>
    <a href="/logout.php" style="display:flex;align-items:center;gap:6px;font-size:12px;color:rgba(255,255,255,0.4);margin-top:0.75rem;text-decoration:none;">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
      Sign Out
    </a>
  </div>
</nav>
