<?php
// ============================================================
// View: Edit Todo
// ============================================================
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/TodoModel.php';
require_once __DIR__ . '/../../models/GroupModel.php';
require_once __DIR__ . '/../../includes/auth.php';

requireAuth();

$uid  = currentUserId();
$id   = (int)($_GET['id'] ?? 0);
$todo = TodoModel::getById($id, $uid);

if (!$todo) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Todo not found.'];
    header('Location: ' . APP_URL . '/views/todos/index');
    exit;
}

$groups    = GroupModel::getAllByUser($uid);
$pageTitle = 'Edit Todo';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="row justify-content-center">
  <div class="col-lg-7 col-xl-6">

    <div class="d-flex align-items-center gap-3 mb-4">
      <a href="<?= APP_URL ?>/views/todos/index" class="btn btn-sm btn-ghost">
        <i class="bi bi-arrow-left"></i>
      </a>
      <div>
        <h4 class="fw-bold mb-0">Edit Todo</h4>
        <p class="text-muted small mb-0">Update the details below</p>
      </div>
    </div>

    <div class="app-card fade-in-up">
      <div class="app-card-body">
        <form action="<?= APP_URL ?>/controllers/TodoController" method="POST" novalidate>
          <?= csrfField() ?>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id"     value="<?= $todo['id'] ?>">

          <!-- Title -->
          <div class="mb-3">
            <label class="form-label" for="title">
              <i class="bi bi-pencil-square me-1"></i>Title <span class="text-danger">*</span>
            </label>
            <input type="text" id="title" name="title" class="form-control"
                   value="<?= e($todo['title']) ?>" required maxlength="255">
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label class="form-label" for="description">
              <i class="bi bi-text-paragraph me-1"></i>Description
            </label>
            <textarea id="description" name="description" class="form-control" rows="3"><?= e($todo['description'] ?? '') ?></textarea>
          </div>

          <!-- Priority + Status -->
          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label class="form-label" for="priority">
                <i class="bi bi-flag-fill me-1"></i>Priority
              </label>
              <select id="priority" name="priority" class="form-select">
                <option value="low"    <?= $todo['priority'] === 'low'    ? 'selected' : '' ?>>🟢 Low</option>
                <option value="medium" <?= $todo['priority'] === 'medium' ? 'selected' : '' ?>>🟡 Medium</option>
                <option value="high"   <?= $todo['priority'] === 'high'   ? 'selected' : '' ?>>🔴 High</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="status">
                <i class="bi bi-toggle-on me-1"></i>Status
              </label>
              <select id="status" name="status" class="form-select">
                <option value="pending"     <?= $todo['status'] === 'pending'     ? 'selected' : '' ?>>Pending</option>
                <option value="in_progress" <?= $todo['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="completed"   <?= $todo['status'] === 'completed'   ? 'selected' : '' ?>>Completed</option>
              </select>
            </div>
          </div>

          <!-- Due Date + Group -->
          <div class="row g-3 mb-4">
            <div class="col-sm-6">
              <label class="form-label" for="due_date">
                <i class="bi bi-calendar2-check me-1"></i>Due Date
              </label>
              <input type="date" id="due_date" name="due_date" class="form-control"
                     value="<?= e($todo['due_date'] ?? '') ?>">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="group_id">
                <i class="bi bi-folder-fill me-1"></i>Group
              </label>
              <select id="group_id" name="group_id" class="form-select">
                <option value="">— No Group —</option>
                <?php foreach ($groups as $g): ?>
                <option value="<?= $g['id'] ?>" <?= $todo['group_id'] == $g['id'] ? 'selected' : '' ?>>
                  <?= e($g['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Timestamps info -->
          <div class="mb-4 p-2 rounded small text-muted" style="background:rgba(255,255,255,.04)">
            <i class="bi bi-clock me-1"></i>
            Created: <?= date('M j, Y g:i A', strtotime($todo['created_at'])) ?>
            &nbsp;|&nbsp;
            Updated: <?= date('M j, Y g:i A', strtotime($todo['updated_at'])) ?>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary py-2 fw-semibold">
              <i class="bi bi-save-fill me-1"></i> Save Changes
            </button>
            <a href="<?= APP_URL ?>/views/todos/index" class="btn btn-outline-secondary">
              Cancel
            </a>
          </div>
        </form>

        <!-- Danger Zone: Delete -->
        <hr style="border-color:rgba(239,68,68,.2);margin:1.5rem 0">
        <div class="text-center">
          <p class="small text-muted mb-2">Soft-delete this todo (can be recovered from DB).</p>
          <button class="btn btn-sm btn-outline-danger" data-delete-id="<?= $todo['id'] ?>" data-redirect="<?= APP_URL ?>/views/todos/index">
            <i class="bi bi-trash me-1"></i> Delete Todo
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
