<?php
// views/index.php
// PRESENTATION LAYER — Pure HTML template. No business logic, no DB, no auth.
// Receives: $pageTitle, $view
$expired = isset($_GET['expired']);
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="login-wrap">
  <!-- Header -->
  <header class="login-header">
    <div class="logo-emblem">E</div>
    <div class="logo-text">EVSU – Ormoc Campus
      <span>INC Form Portal</span>
    </div>
  </header>

  <!-- Hero -->
  <section class="landing-hero">
    <div class="hero-pattern"></div>
    <?php if ($expired): ?>
    <div style="background:rgba(201,168,76,0.15);border:1px solid rgba(201,168,76,0.4);color:var(--gold-light);padding:10px 20px;border-radius:8px;font-size:13px;display:inline-block;margin-bottom:1.5rem;">
      ⚠ Your session expired. Please sign in again.
    </div>
    <?php endif; ?>
    <div class="hero-badge">Academic Year 2025–2026</div>
    <h1 class="hero-title">Incomplete Grade<br>Completion System</h1>
    <p class="hero-sub">Digital processing for INC form submissions — faster, paperless, and fully trackable.</p>

    <div class="role-cards">
      <a href="<?= $view->url('login.php?type=student') ?>" class="role-card student">
        <div class="icon-wrap">🎓</div>
        <h3>Student</h3>
        <p>File INC applications, upload receipts, and track your progress.</p>
        <span class="cta">Sign in as Student →</span>
      </a>
      <a href="<?= $view->url('login.php?type=employee') ?>" class="role-card employee">
        <div class="icon-wrap">💼</div>
        <h3>Employee / Admin</h3>
        <p>Instructor · Department Head · Registrar · Administrator</p>
        <span class="cta">Employee Sign In →</span>
      </a>
    </div>
  </section>

  <!-- Footer note -->
  <div style="text-align:center;padding:2rem;font-size:12px;color:var(--gray-400);">
    Eastern Visayas State University – Ormoc Campus &nbsp;|&nbsp; INC Form Portal &nbsp;|&nbsp; Secured System
  </div>
</div>
</body>
</html>
