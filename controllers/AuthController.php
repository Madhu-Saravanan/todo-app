<?php
// ============================================================
// AntiGravity Todo App – Auth Controller
// Handles: signup, login, logout, forgot/reset password
// ============================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../includes/auth.php';

$action = $_GET['action'] ?? '';

// ----- LOGOUT -----------------------------------------------
if ($action === 'logout') {
    session_unset();
    session_destroy();
    header('Location: ' . APP_URL . '/views/auth/login');
    exit;
}

// ----- SIGNUP -----------------------------------------------
if ($action === 'signup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');
    $errors   = [];

    // Validation
    if (empty($name))                          $errors[] = 'Name is required.';
    if (strlen($name) < 2)                     $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 8)                 $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)                $errors[] = 'Passwords do not match.';
    if (UserModel::findByEmail($email))        $errors[] = 'Email is already registered.';

    if ($errors) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => implode('<br>', $errors)];
        header('Location: ' . APP_URL . '/views/auth/signup');
        exit;
    }

    $userId = UserModel::create($name, $email, $password);
    if ($userId) {
        $_SESSION['user_id']   = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['flash']     = ['type' => 'success', 'msg' => 'Welcome aboard, ' . htmlspecialchars($name) . '! 🎉'];
        header('Location: ' . APP_URL . '/views/dashboard');
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Registration failed. Please try again.'];
        header('Location: ' . APP_URL . '/views/auth/signup');
    }
    exit;
}

// ----- LOGIN ------------------------------------------------
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);

    $user = UserModel::authenticate($email, $password);

    if ($user) {
        // Regenerate session ID on privilege escalation
        session_regenerate_id(true);

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        if ($remember) {
            // Extend session cookie lifetime
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() + SESSION_LIFETIME,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']);
        }

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Welcome back, ' . htmlspecialchars($user['name']) . '!'];
        header('Location: ' . APP_URL . '/views/dashboard');
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid email or password.'];
        header('Location: ' . APP_URL . '/views/auth/login');
    }
    exit;
}

// ----- FORGOT PASSWORD --------------------------------------
if ($action === 'forgot' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = trim($_POST['email'] ?? '');
    $user  = UserModel::findByEmail($email);

    // Always show success to prevent user enumeration
    $_SESSION['flash'] = [
        'type' => 'info',
        'msg'  => 'If that email exists, a reset link has been sent.'
    ];

    if ($user) {
        $token = bin2hex(random_bytes(32));
        UserModel::setResetToken((int)$user['id'], $token);
        $resetUrl = APP_URL . '/views/auth/reset_password?token=' . urlencode($token);
        // In a real app you would send an email. We log it here for demo.
        error_log("[Password Reset] URL: $resetUrl");
    }

    header('Location: ' . APP_URL . '/views/auth/forgot_password');
    exit;
}

// ----- RESET PASSWORD ---------------------------------------
if ($action === 'reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $token    = $_POST['token']    ?? '';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    $user = UserModel::findByResetToken($token);
    if (!$user) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid or expired reset link.'];
        header('Location: ' . APP_URL . '/views/auth/forgot_password');
        exit;
    }

    if (strlen($password) < 8 || $password !== $confirm) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Passwords do not match or are too short.'];
        header('Location: ' . APP_URL . '/views/auth/reset_password?token=' . urlencode($token));
        exit;
    }

    UserModel::updatePassword((int)$user['id'], $password);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Password updated! You can now log in.'];
    header('Location: ' . APP_URL . '/views/auth/login');
    exit;
}
