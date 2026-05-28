<?php
// views/forgot_password.php
// PRESENTATION LAYER — 3-step password reset: email → OTP → new password
// Receives: $pageTitle, $csrf, $step, $error, $msg, $maskedEmail, $view
$view->partial('layouts/head', get_defined_vars());
?>
<body>
<div class="login-wrap">
  <header class="login-header">
    <div class="logo-emblem">E</div>
    <div class="logo-text">EVSU – Ormoc Campus <span>INC Form Portal</span></div>
  </header>

  <div class="login-body">
    <div style="width:100%;max-width:420px;">
      <a href="<?= $view->url('login.php') ?>" class="back-link">← Back to login</a>

      <div class="login-card">
        <div class="login-card-header">
          <div class="login-emblem">🔑</div>
          <h2>Reset Password</h2>

          <!-- Step indicator -->
          <div style="display:flex;justify-content:center;align-items:center;gap:6px;margin-top:1rem;">
            <?php
            $stepLabels = [1 => 'Email', 2 => 'OTP', 3 => 'New Password'];
            for ($i = 1; $i <= 3; $i++):
              $isActive = $step === $i;
              $isDone   = $step > $i;
            ?>
            <div style="width:26px;height:26px;border-radius:50%;
                        display:flex;align-items:center;justify-content:center;
                        font-size:11px;font-weight:700;
                        background:<?= $isDone ? 'var(--success)' : ($isActive ? 'var(--maroon)' : 'var(--gray-200)') ?>;
                        color:<?= ($isDone || $isActive) ? '#fff' : 'var(--gray-400)' ?>;">
              <?= $isDone ? '✓' : $i ?>
            </div>
            <span style="font-size:11px;
                         color:<?= $isActive ? 'var(--maroon)' : 'var(--gray-400)' ?>;
                         font-weight:<?= $isActive ? '600' : '400' ?>;">
              <?= $stepLabels[$i] ?>
            </span>
            <?php if ($i < 3): ?>
            <div style="width:16px;height:1px;background:var(--gray-200);"></div>
            <?php endif; ?>
            <?php endfor; ?>
          </div>
        </div>

        <?php if ($msg === 'PASSWORD_CHANGED'): ?>
          <!-- Success -->
          <div style="text-align:center;padding:1rem 0;">
            <div style="font-size:40px;margin-bottom:0.75rem;">✅</div>
            <div class="alert alert-success">Password changed successfully!</div>
            <p style="font-size:13px;color:var(--gray-400);margin-bottom:1.25rem;">
              You can now sign in with your new password.
            </p>
            <a href="<?= $view->url('login.php') ?>" class="btn-primary full">Go to Sign In</a>
          </div>

        <?php elseif ($step === 1): ?>
          <!-- Step 1: Email -->
          <p style="font-size:13px;color:var(--gray-400);text-align:center;margin:0.75rem 0 1.25rem;">
            Enter the email address linked to your account.
          </p>
          <?php if ($error): ?>
          <div class="alert alert-danger"><?= $view->e($error) ?></div>
          <?php endif; ?>
          <?php if ($msg): ?>
          <div class="alert alert-success"><?= $view->e($msg) ?></div>
          <?php endif; ?>
          <form method="POST" action="<?= $view->url('forgot_password.php') ?>">
            <input type="hidden" name="csrf_token" value="<?= $view->e($csrf) ?>">
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input class="form-input" type="email" name="email"
                     placeholder="your@email.com" autocomplete="email" required>
            </div>
            <button type="submit" class="btn-primary full">Send OTP</button>
          </form>

        <?php elseif ($step === 2): ?>
          <!-- Step 2: OTP -->
          <p style="font-size:13px;color:var(--gray-400);text-align:center;margin:0.75rem 0 1.25rem;">
            A 6-digit code was sent to <strong><?= $view->e($maskedEmail) ?></strong>.
            It expires in 15 minutes.
          </p>
          <?php if ($error): ?>
          <div class="alert alert-danger"><?= $view->e($error) ?></div>
          <?php endif; ?>
          <?php if ($msg): ?>
          <div class="alert alert-success"><?= $view->e($msg) ?></div>
          <?php endif; ?>
          <form method="POST" action="<?= $view->url('forgot_password.php') ?>">
            <input type="hidden" name="csrf_token" value="<?= $view->e($csrf) ?>">
            <div class="form-group">
              <label class="form-label">6-Digit OTP</label>
              <input class="form-input" type="text" name="otp"
                     placeholder="000000" maxlength="6"
                     inputmode="numeric" pattern="\d{6}"
                     autocomplete="one-time-code" required
                     style="text-align:center;font-family:monospace;
                            font-size:26px;letter-spacing:0.4em;">
            </div>
            <button type="submit" class="btn-primary full">Verify OTP</button>
          </form>
          <p style="font-size:12px;color:var(--gray-400);text-align:center;margin-top:1rem;">
            Didn't receive it?
            <a href="<?= $view->url('forgot_password.php') ?>"
               style="color:var(--maroon);font-weight:500;">Start over</a>
          </p>

        <?php elseif ($step === 3): ?>
          <!-- Step 3: New Password -->
          <p style="font-size:13px;color:var(--gray-400);text-align:center;margin:0.75rem 0 1.25rem;">
            OTP verified. Choose a strong new password.
          </p>
          <?php if ($error): ?>
          <div class="alert alert-danger"><?= $view->e($error) ?></div>
          <?php endif; ?>
          <form method="POST" action="<?= $view->url('forgot_password.php') ?>">
            <input type="hidden" name="csrf_token" value="<?= $view->e($csrf) ?>">
            <div class="form-group">
              <label class="form-label">New Password <span style="color:var(--danger)">*</span></label>
              <input class="form-input" type="password" name="password"
                     placeholder="Min. 8 characters"
                     autocomplete="new-password" required>
            </div>
            <div class="form-group">
              <label class="form-label">Confirm Password <span style="color:var(--danger)">*</span></label>
              <input class="form-input" type="password" name="confirm_password"
                     placeholder="Repeat new password"
                     autocomplete="new-password" required>
              <p class="form-hint" id="matchHint"
                 style="display:none;color:var(--danger);">
                Passwords do not match.
              </p>
            </div>
            <button type="submit" class="btn-primary full">Set New Password</button>
          </form>
          <script>
          const p    = document.querySelector('[name="password"]');
          const c    = document.querySelector('[name="confirm_password"]');
          const hint = document.getElementById('matchHint');
          c.addEventListener('input', () => {
            hint.style.display = (c.value && c.value !== p.value) ? 'block' : 'none';
          });
          </script>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>
</body>
</html>