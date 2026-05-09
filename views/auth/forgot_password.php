<?php
// ============================================================
// View: Forgot Password
// ============================================================
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
redirectIfAuth();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password | Todo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    <p class="text-center small mb-1" style="color:var(--text-muted);letter-spacing:.3px">
      Organize tasks &middot; Track progress &middot; Get things done
    </p>

    <h1 class="auth-title">Forgot password? 🔐</h1>
    <p class="auth-subtitle">Enter your email and we'll send a reset link.</p>

    <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> py-2 small" role="alert">
      <?= $_SESSION['flash']['msg'] ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/controllers/AuthController.php?action=forgot" method="POST">
      <?= csrfField() ?>
      <div class="mb-4">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
          <input type="email" id="email" name="email" class="form-control"
                 placeholder="you@example.com" required autocomplete="email">
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-send-fill me-1"></i> Send Reset Link
      </button>
    </form>

    <p class="text-center text-muted small mt-3 mb-0">
      Remembered it?
      <a href="<?= APP_URL ?>/views/auth/login.php" class="text-decoration-none fw-semibold" style="color:var(--primary)">
        Back to login
      </a>
    </p>

    <div class="mt-3 p-2 rounded small text-center" style="background:rgba(6,182,212,.08);color:var(--text-secondary)">
      <i class="bi bi-info-circle me-1"></i>
      In this demo, the reset URL is printed to <code>php_error.log</code>.
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
