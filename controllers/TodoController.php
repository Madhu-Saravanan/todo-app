<?php
// ============================================================
// AntiGravity Todo App – Todo Controller
// Handles Create / Update / Delete (form submissions)
// AJAX-based status update is handled in /ajax/todo_status.php
// ============================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/TodoModel.php';
require_once __DIR__ . '/../models/GroupModel.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();
verifyCsrf();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$uid    = currentUserId();

// Whitelists for enum fields
$validPriority = ['low', 'medium', 'high'];
$validStatus   = ['pending', 'in_progress', 'completed'];

// ----- CREATE TODO ------------------------------------------
if ($action === 'create') {
    $rawPriority = $_POST['priority'] ?? 'medium';
    $rawStatus   = $_POST['status']   ?? 'pending';
    $data = [
        'group_id'    => !empty($_POST['group_id']) ? (int)$_POST['group_id'] : null,
        'title'       => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'priority'    => in_array($rawPriority, $validPriority, true) ? $rawPriority : 'medium',
        'status'      => in_array($rawStatus,   $validStatus,   true) ? $rawStatus   : 'pending',
        'due_date'    => $_POST['due_date']  ?? null,
    ];

    if (empty($data['title'])) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Todo title is required.'];
        header('Location: ' . APP_URL . '/views/todos/create');
        exit;
    }

    $id = TodoModel::create($uid, $data);
    if ($id) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Todo created successfully! ✅'];
        header('Location: ' . APP_URL . '/views/todos/index');
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Failed to create todo.'];
        header('Location: ' . APP_URL . '/views/todos/create');
    }
    exit;
}

// ----- UPDATE TODO ------------------------------------------
if ($action === 'update') {
    $id          = (int)($_POST['id'] ?? 0);
    $rawPriority = $_POST['priority'] ?? 'medium';
    $rawStatus   = $_POST['status']   ?? 'pending';
    $data = [
        'group_id'    => !empty($_POST['group_id']) ? (int)$_POST['group_id'] : null,
        'title'       => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'priority'    => in_array($rawPriority, $validPriority, true) ? $rawPriority : 'medium',
        'status'      => in_array($rawStatus,   $validStatus,   true) ? $rawStatus   : 'pending',
        'due_date'    => $_POST['due_date']  ?? null,
    ];

    if (empty($data['title'])) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Todo title is required.'];
        header('Location: ' . APP_URL . '/views/todos/edit?id=' . $id);
        exit;
    }

    $rows = TodoModel::update($id, $uid, $data);
    if ($rows > 0) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Todo updated successfully! ✏️'];
    } else {
        $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'No changes detected.'];
    }
    header('Location: ' . APP_URL . '/views/todos/index');
    exit;
}

// ----- SOFT DELETE ------------------------------------------
if ($action === 'delete') {
    $id   = (int)($_POST['id'] ?? 0);
    $rows = TodoModel::softDelete($id, $uid);
    if ($rows > 0) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Todo deleted. 🗑️'];
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Could not delete todo.'];
    }
    header('Location: ' . APP_URL . '/views/todos/index');
    exit;
}

// ----- GROUP CREATE -----------------------------------------
if ($action === 'group_create') {
    $rawColor = trim($_POST['color'] ?? '#6366f1');
    $data = [
        'name'        => trim($_POST['name']        ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'color'       => preg_match('/^#[0-9a-fA-F]{6}$/', $rawColor) ? $rawColor : '#6366f1',
    ];

    if (empty($data['name'])) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Group name is required.'];
        header('Location: ' . APP_URL . '/views/groups/index');
        exit;
    }

    $id = GroupModel::create($uid, $data);
    $_SESSION['flash'] = $id
        ? ['type' => 'success', 'msg' => 'Group created! 📁']
        : ['type' => 'danger',  'msg' => 'Failed to create group.'];
    header('Location: ' . APP_URL . '/views/groups/index');
    exit;
}

// ----- GROUP UPDATE -----------------------------------------
if ($action === 'group_update') {
    $id       = (int)($_POST['id'] ?? 0);
    $rawColor = trim($_POST['color'] ?? '#6366f1');
    $data = [
        'name'        => trim($_POST['name']        ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'color'       => preg_match('/^#[0-9a-fA-F]{6}$/', $rawColor) ? $rawColor : '#6366f1',
    ];

    GroupModel::update($id, $uid, $data);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Group updated! ✏️'];
    header('Location: ' . APP_URL . '/views/groups/index');
    exit;
}

// ----- GROUP DELETE -----------------------------------------
if ($action === 'group_delete') {
    $id = (int)($_POST['id'] ?? 0);
    GroupModel::delete($id, $uid);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Group deleted. 🗑️'];
    header('Location: ' . APP_URL . '/views/groups/index');
    exit;
}

// Fallback
header('Location: ' . APP_URL . '/views/todos/index');
exit;
