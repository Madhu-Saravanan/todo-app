<?php
// ============================================================
// AntiGravity Todo App – Auth Middleware
// Include this file at the top of every protected page.
// Redirects unauthenticated users to the login page.
// ============================================================
require_once __DIR__ . '/../config/config.php';

/**
 * Enforce authentication.
 * Redirect to login if the user is not logged in.
 */
function requireAuth(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . APP_URL . '/views/auth/login');
        exit;
    }
}

/**
 * Redirect already-logged-in users away from auth pages.
 */
function redirectIfAuth(): void {
    if (!empty($_SESSION['user_id'])) {
        header('Location: ' . APP_URL . '/views/dashboard');
        exit;
    }
}

/**
 * Generate (or reuse) a CSRF token and store it in the session.
 */
function generateCsrfToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Return an HTML hidden input containing the CSRF token.
 */
function csrfField(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES) . '">';
}

/**
 * Validate the submitted CSRF token against the session token.
 * Kills the request if invalid.
 */
function verifyCsrf(): void {
    $submitted = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $stored    = $_SESSION[CSRF_TOKEN_NAME] ?? '';

    if (!hash_equals($stored, $submitted)) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'Invalid CSRF token. Please refresh and try again.']));
    }
}

/**
 * Sanitise output to prevent XSS.
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Return the currently logged-in user's session ID.
 */
function currentUserId(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

/**
 * Return the currently logged-in user's name.
 */
function currentUserName(): string {
    return $_SESSION['user_name'] ?? 'User';
}
