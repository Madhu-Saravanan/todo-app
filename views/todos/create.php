<?php
// ============================================================
// View: Create Todo
// ============================================================
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/GroupModel.php';
require_once __DIR__ . '/../../includes/auth.php';

requireAuth();

$groups    = GroupModel::getAllByUser(currentUserId());
$pageTitle = 'New Todo';

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
        <h4 class="fw-bold mb-0">Create New Todo</h4>
        <p class="text-muted small mb-0">Fill in the details below</p>
      </div>
    </div>

    <div class="app-card fade-in-up">
      <div class="app-card-body">
        <form action="<?= APP_URL ?>/controllers/TodoController" method="POST" novalidate>
          <?= csrfField() ?>
          <input type="hidden" name="action" value="create">

          <!-- Title -->
          <div class="mb-3">
            <label class="form-label" for="title">
              <i class="bi bi-pencil-square me-1"></i>Title <span class="text-danger">*</span>
            </label>
            <input type="text" id="title" name="title" class="form-control"
                   placeholder="e.g. Finish the project report" required maxlength="255">
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label class="form-label" for="description">
              <i class="bi bi-text-paragraph me-1"></i>Description
            </label>
            <textarea id="description" name="description" class="form-control" rows="3"
                      placeholder="Add more details… (optional)"></textarea>
          </div>

          <!-- Priority + Status (2-col) -->
          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label class="form-label" for="priority">
                <i class="bi bi-flag-fill me-1"></i>Priority
              </label>
              <select id="priority" name="priority" class="form-select">
                <option value="low">🟢 Low</option>
                <option value="medium" selected>🟡 Medium</option>
                <option value="high">🔴 High</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="status">
                <i class="bi bi-toggle-on me-1"></i>Status
              </label>
              <select id="status" name="status" class="form-select">
                <option value="pending" selected>Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
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
                     min="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="group_id">
                <i class="bi bi-folder-fill me-1"></i>Group
              </label>
              <select id="group_id" name="group_id" class="form-select">
                <option value="">— No Group —</option>
                <?php foreach ($groups as $g): ?>
                <option value="<?= $g['id'] ?>"><?= e($g['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary py-2 fw-semibold">
              <i class="bi bi-plus-circle-fill me-1"></i> Create Todo
            </button>
            <a href="<?= APP_URL ?>/views/todos/index" class="btn btn-outline-secondary">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
