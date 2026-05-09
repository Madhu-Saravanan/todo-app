<?php
// ============================================================
// AJAX – Update Todo Status
// Expects POST: id, status, csrf_token
// Returns JSON
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/TodoModel.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();
verifyCsrf();

$id     = (int)($_POST['id']     ?? 0);
$status = $_POST['status'] ?? '';
$uid    = currentUserId();

if (!$id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

$rows = TodoModel::updateStatus($id, $uid, $status);

echo json_encode([
    'success' => $rows > 0,
    'message' => $rows > 0 ? 'Status updated.' : 'Update failed or no changes.'
]);
