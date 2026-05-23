<?php
// includes/student_sidebar.php
$activePage = $activePage ?? '';
$initials = strtoupper(substr($_SESSION['full_name']??'S',0,1).(strpos($_SESSION['full_name']??'','')!==false?substr($_SESSION['full_name'],1,1):''));
?>
<nav class="sidebar">
  <div class="sidebar-brand">
    <div class="emblem">E</div>
    <h1>EVSU – OC <span>INC Form Portal</span></h1>
  </div>

  <span class="nav-section-label">My Portal</span>
  <a href="/student/dashboard.php" class="nav-item <?= $activePage==='dashboard'?'active':'' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
    Dashboard
  </a>
  <a href="/student/apply.php" class="nav-item <?= $activePage==='apply'?'active':'' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    New Application
  </a>
  <a href="/student/applications.php" class="nav-item <?= $activePage==='applications'?'active':'' ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    My Applications
  </a>

  <div class="sidebar-footer">
    <div class="user-chip">
      <div class="user-avatar"><?= h($initials) ?></div>
      <div class="user-info">
        <p><?= h($_SESSION['full_name']??'') ?></p>
        <span>Student</span>
      </div>
    </div>
    <a href="/logout.php" style="display:flex;align-items:center;gap:6px;font-size:12px;color:rgba(255,255,255,0.4);margin-top:0.75rem;text-decoration:none;">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
      Sign Out
    </a>
  </div>
</nav>
