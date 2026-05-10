<?php
// ============================================================
// Entry Point – redirect to login or dashboard
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/views/dashboard');
} else {
    header('Location: ' . APP_URL . '/views/auth/login');
}
exit;
