<?php
// ============================================================
// AJAX – Soft Delete Todo
// Expects POST: id, csrf_token
// Returns JSON
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/TodoModel.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();
verifyCsrf();

$id  = (int)($_POST['id'] ?? 0);
$uid = currentUserId();

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
    exit;
}

$rows = TodoModel::softDelete($id, $uid);
echo json_encode([
    'success' => $rows > 0,
    'message' => $rows > 0 ? 'Todo deleted.' : 'Delete failed.'
]);
