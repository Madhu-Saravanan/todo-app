<?php
// ============================================================
// View: Reset Password
// ============================================================
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../models/UserModel.php';
redirectIfAuth();

$token = $_GET['token'] ?? '';
$user  = $token ? UserModel::findByResetToken($token) : null;

if (!$user) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'This reset link is invalid or has expired.'];
    header('Location: ' . APP_URL . '/views/auth/forgot_password.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password | Todo</title>
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

    <h1 class="auth-title">Set new password 🔑</h1>
    <p class="auth-subtitle">Choose a strong password for your account.</p>

    <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> py-2 small" role="alert">
      <?= $_SESSION['flash']['msg'] ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/controllers/AuthController.php?action=reset" method="POST">
      <?= csrfField() ?>
      <input type="hidden" name="token" value="<?= e($token) ?>">

      <div class="mb-3">
        <label class="form-label" for="password">New Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Min 8 characters" required minlength="8">
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label" for="confirm">Confirm Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
          <input type="password" id="confirm" name="confirm" class="form-control"
                 placeholder="Repeat password" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-check-circle-fill me-1"></i> Update Password
      </button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
