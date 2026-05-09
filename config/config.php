<?php
// ============================================================
// AntiGravity Todo App – Global Configuration
// ============================================================

// --- App Settings ---
define('APP_NAME',    'AntiGravity Todo');
define('APP_VERSION', '1.0.0');
// APP_URL: set via env var on Vercel; falls back to XAMPP local default
define('APP_URL',     rtrim(getenv('APP_URL') ?: 'http://localhost/ToDo', '/'));
define('TIMEZONE',    'Asia/Kolkata');

// --- Database Credentials (env vars on Vercel, hardcoded fallbacks for XAMPP) ---
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_PORT',    getenv('DB_PORT')    ?: '3306');
define('DB_NAME',    getenv('DB_NAME')    ?: 'antigravity_todo_db');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// --- Security ---
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 86400);   // 24 hours in seconds

// --- Pagination ---
define('ITEMS_PER_PAGE', 10);

// Set timezone
date_default_timezone_set(TIMEZONE);

// Start session securely if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');

    // Use DB-backed sessions when the DB layer is available (required on Vercel
    // because serverless instances don't share a filesystem for file sessions).
    if (!class_exists('Database') && file_exists(__DIR__ . '/database.php')) {
        require_once __DIR__ . '/database.php';
    }
    if (class_exists('Database') && !class_exists('DbSessionHandler') && file_exists(__DIR__ . '/session.php')) {
        require_once __DIR__ . '/session.php';
    }

    session_start();
}
