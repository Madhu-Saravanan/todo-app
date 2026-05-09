<?php
// ============================================================
// View: Login Page
// ============================================================
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
redirectIfAuth();

$pageTitle = 'Login';
$csrf      = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | AntiGravity Todo</title>
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

    <!-- Logo -->
    <div class="auth-logo">
      <span class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></span>
      <span class="brand-text">AntiGravity Todo</span>
    </div>

    <h1 class="auth-title">Welcome back 👋</h1>
    <p class="auth-subtitle">Sign in to manage your tasks.</p>

    <!-- Flash Message -->
    <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> alert-dismissible fade show py-2 small" role="alert">
      <?= $_SESSION['flash']['msg'] ?>
      <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="<?= APP_URL ?>/controllers/AuthController.php?action=login" method="POST" novalidate>
      <?= csrfField() ?>

      <div class="mb-3">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
          <input type="email" id="email" name="email" class="form-control"
                 placeholder="you@example.com" required autocomplete="email">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="••••••••" required autocomplete="current-password">
          <button type="button" class="btn btn-ghost px-3" id="togglePwd" title="Show/hide password">
            <i class="bi bi-eye-fill" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
          <label class="form-check-label small text-muted" for="remember">Remember me</label>
        </div>
        <a href="<?= APP_URL ?>/views/auth/forgot_password.php" class="small text-decoration-none" style="color:var(--primary)">
          Forgot password?
        </a>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
      </button>
    </form>

    <p class="text-center text-muted small mt-3 mb-0">
      Don't have an account?
      <a href="<?= APP_URL ?>/views/auth/signup.php" class="text-decoration-none fw-semibold" style="color:var(--primary)">
        Create one
      </a>
    </p>

    <!-- Demo credentials hint -->
    <div class="mt-3 p-2 rounded small text-center" style="background:rgba(99,102,241,.1);color:var(--text-secondary)">
      <i class="bi bi-info-circle me-1"></i>
      Demo: <strong>test@example.com</strong> / <strong>Test@1234</strong>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('togglePwd')?.addEventListener('click', () => {
    const pwd  = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    pwd.type       = pwd.type === 'password' ? 'text' : 'password';
    icon.className = pwd.type === 'text' ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
  });
</script>
</body>
</html>
