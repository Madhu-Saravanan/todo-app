<?php
// ============================================================
// API: Return todos as JSON (used by dashboard inline expand)
// ============================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/TodoModel.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();
header('Content-Type: application/json');

$uid = currentUserId();
$filters = [
    'status'   => $_GET['status']   ?? '',
    'priority' => $_GET['priority'] ?? '',
    'group_id' => (int)($_GET['group_id'] ?? 0),
    'search'   => '',
];

$result = TodoModel::getAll($uid, $filters, 1, 50);
echo json_encode(['success' => true, 'data' => $result['data']]);
