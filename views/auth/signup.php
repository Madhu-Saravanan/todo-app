<?php
// ============================================================
// View: Signup Page
// ============================================================
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
redirectIfAuth();

$pageTitle = 'Create Account';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up | Todo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Apply saved theme before CSS renders to prevent flash -->
  <script>document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('ag_theme') || 'dark');</script>
  <link href="<?= APP_URL ?>/assets/css/app.css"  rel="stylesheet">
  <link href="<?= APP_URL ?>/assets/css/auth.css" rel="stylesheet">
</head>
<body>
<div class="auth-bg"></div>

<div class="auth-wrapper">
  <div class="auth-card fade-in-up">

    <div class="auth-logo">
      <span class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></span>
      <span class="brand-text">Todo</span>
    </div>

    <h1 class="auth-title">Create account 🚀</h1>
    <p class="auth-subtitle">Start organizing your tasks in style.</p>

    <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> alert-dismissible fade show py-2 small" role="alert">
      <?= $_SESSION['flash']['msg'] ?>
      <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/controllers/AuthController.php?action=signup"
          method="POST" novalidate id="signupForm">
      <?= csrfField() ?>

      <div class="mb-3">
        <label class="form-label" for="name">Full Name</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
          <input type="text" id="name" name="name" class="form-control"
                 placeholder="John Doe" required autocomplete="name">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
          <input type="email" id="email" name="email" class="form-control"
                 placeholder="you@example.com" required autocomplete="email">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="password">Password <span class="text-muted">(min. 8 chars)</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="••••••••" required autocomplete="new-password"
                 oninput="checkStrength(this.value)">
          <button type="button" class="btn btn-ghost px-3" id="togglePwd">
            <i class="bi bi-eye-fill" id="eyeIcon"></i>
          </button>
        </div>
        <!-- Strength bar -->
        <div class="progress mt-2" style="height:4px">
          <div id="strengthBar" class="progress-bar" style="width:0%;transition:width .3s"></div>
        </div>
        <div id="strengthLabel" class="text-muted small mt-1"></div>
      </div>

      <div class="mb-4">
        <label class="form-label" for="confirm">Confirm Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
          <input type="password" id="confirm" name="confirm" class="form-control"
                 placeholder="••••••••" required autocomplete="new-password">
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-person-plus-fill me-1"></i> Create Account
      </button>
    </form>

    <p class="text-center text-muted small mt-3 mb-0">
      Already have an account?
      <a href="<?= APP_URL ?>/views/auth/login.php" class="text-decoration-none fw-semibold" style="color:var(--primary)">
        Sign in
      </a>
    </p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Password visibility toggle
  document.getElementById('togglePwd')?.addEventListener('click', () => {
    const pwd  = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    pwd.type   = pwd.type === 'password' ? 'text' : 'password';
    icon.className = pwd.type === 'text' ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
  });

  // Password strength checker
  function checkStrength(pwd) {
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    let score = 0;
    if (pwd.length >= 8)          score++;
    if (/[A-Z]/.test(pwd))        score++;
    if (/[0-9]/.test(pwd))        score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;

    const map = [
      { w:'0%',  cls:'bg-danger',  text:'' },
      { w:'25%', cls:'bg-danger',  text:'Weak' },
      { w:'50%', cls:'bg-warning', text:'Fair' },
      { w:'75%', cls:'bg-info',    text:'Good' },
      { w:'100%',cls:'bg-success', text:'Strong 💪' },
    ];
    bar.style.width  = map[score].w;
    bar.className    = 'progress-bar ' + map[score].cls;
    label.textContent = map[score].text;
  }

  // Client-side confirm password validation
  document.getElementById('signupForm')?.addEventListener('submit', function(e) {
    const pwd = document.getElementById('password').value;
    const con = document.getElementById('confirm').value;
    if (pwd !== con) {
      e.preventDefault();
      document.getElementById('confirm').classList.add('is-invalid');
      alert('Passwords do not match!');
    }
  });
</script>
</body>
</html>
