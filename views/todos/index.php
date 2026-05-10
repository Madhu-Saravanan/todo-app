<?php
// ============================================================
// View: All Todos (with filter, search, pagination)
// ============================================================
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/TodoModel.php';
require_once __DIR__ . '/../../models/GroupModel.php';
require_once __DIR__ . '/../../includes/auth.php';

requireAuth();

$uid    = currentUserId();
$groups = GroupModel::getAllByUser($uid);

// --- Filters from GET ---
$filters = [
    'status'   => $_GET['status']   ?? '',
    'priority' => $_GET['priority'] ?? '',
    'group_id' => (int)($_GET['group_id'] ?? 0),
    'search'   => trim($_GET['search'] ?? ''),
];
$page   = max(1, (int)($_GET['page'] ?? 1));
$result = TodoModel::getAll($uid, $filters, $page);

$todos     = $result['data'];
$totalPages = $result['pages'];
$total      = $result['total'];

$pageTitle = 'All Todos';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-0">All Todos</h4>
    <p class="text-muted small mb-0"><?= $total ?> task<?= $total !== 1 ? 's' : '' ?> found</p>
  </div>
  <a href="<?= APP_URL ?>/views/todos/create" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i> New Todo
  </a>
</div>

<!-- Filter Bar -->
<form method="GET" id="filterForm">
  <div class="filter-bar">

    <!-- Search -->
    <div class="flex-grow-1" style="min-width:180px">
      <div class="input-group input-group-sm">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" id="searchInput" name="search" class="form-control"
               placeholder="Search todos…" value="<?= e($filters['search']) ?>">
      </div>
    </div>

    <!-- Status -->
    <select name="status" class="form-select form-select-sm" style="max-width:140px"
            onchange="this.form.submit()">
      <option value="">All Status</option>
      <option value="pending"     <?= $filters['status'] === 'pending'     ? 'selected' : '' ?>>Pending</option>
      <option value="in_progress" <?= $filters['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
      <option value="completed"   <?= $filters['status'] === 'completed'   ? 'selected' : '' ?>>Completed</option>
    </select>

    <!-- Priority -->
    <select name="priority" class="form-select form-select-sm" style="max-width:130px"
            onchange="this.form.submit()">
      <option value="">All Priority</option>
      <option value="high"   <?= $filters['priority'] === 'high'   ? 'selected' : '' ?>>🔴 High</option>
      <option value="medium" <?= $filters['priority'] === 'medium' ? 'selected' : '' ?>>🟡 Medium</option>
      <option value="low"    <?= $filters['priority'] === 'low'    ? 'selected' : '' ?>>🟢 Low</option>
    </select>

    <!-- Group -->
    <select name="group_id" class="form-select form-select-sm" style="max-width:150px"
            onchange="this.form.submit()">
      <option value="">All Groups</option>
      <?php foreach ($groups as $g): ?>
      <option value="<?= $g['id'] ?>" <?= $filters['group_id'] == $g['id'] ? 'selected' : '' ?>>
        <?= e($g['name']) ?>
      </option>
      <?php endforeach; ?>
    </select>

    <!-- Clear filters -->
    <?php if (array_filter($filters)): ?>
    <a href="<?= APP_URL ?>/views/todos/index" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-x-circle me-1"></i>Clear
    </a>
    <?php endif; ?>
  </div>
</form>

<!-- Todo Grid -->
<?php if (empty($todos)): ?>
<div class="empty-state">
  <i class="bi bi-inbox"></i>
  <h5>No todos found</h5>
  <p class="small">Try adjusting your filters or <a href="<?= APP_URL ?>/views/todos/create">create a new todo</a>.</p>
</div>
<?php else: ?>

<div class="row g-3 stagger-children" id="todoGrid">
  <?php foreach ($todos as $t): ?>
  <div class="col-12 col-md-6 col-xl-4">
    <div class="todo-card priority-<?= e($t['priority']) ?> status-<?= e($t['status']) ?>"
         data-id="<?= $t['id'] ?>">

      <!-- Collapsed header (always visible) — click to expand -->
      <div class="todo-card-top d-flex align-items-center gap-2"
           data-bs-toggle="collapse"
           data-bs-target="#todo-body-<?= $t['id'] ?>"
           style="cursor:pointer">

        <!-- Checkbox for quick complete — stop propagation so it doesn't toggle collapse -->
        <input type="checkbox"
               class="form-check-input flex-shrink-0"
               style="width:1.15em;height:1.15em;cursor:pointer"
               data-status-toggle
               data-id="<?= $t['id'] ?>"
               value="completed"
               onclick="event.stopPropagation()"
               <?= $t['status'] === 'completed' ? 'checked' : '' ?>>

        <div class="todo-title flex-grow-1"><?= e($t['title']) ?></div>

        <!-- Badges -->
        <span class="badge badge-priority-<?= e($t['priority']) ?> text-capitalize flex-shrink-0"><?= e($t['priority']) ?></span>
        <span class="badge badge-status-<?= e($t['status']) ?> text-capitalize flex-shrink-0">
          <?= str_replace('_', ' ', e($t['status'])) ?>
        </span>

        <!-- Chevron -->
        <i class="bi bi-chevron-down todo-chevron flex-shrink-0"></i>
      </div>

      <!-- Expandable body -->
      <div class="collapse" id="todo-body-<?= $t['id'] ?>">
        <hr class="my-2" style="border-color:var(--card-border)">

        <?php if ($t['description']): ?>
        <p class="small mb-2" style="color:var(--text-secondary)"><?= nl2br(e($t['description'])) ?></p>
        <?php else: ?>
        <p class="small mb-2 fst-italic" style="color:var(--text-muted)">No description.</p>
        <?php endif; ?>

        <!-- Meta + actions -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <?php if ($t['group_name']): ?>
          <span class="badge" style="background:rgba(99,102,241,.15);color:#818cf8;font-size:.7rem">
            <i class="bi bi-folder-fill me-1" style="color:<?= e($t['group_color'] ?? '#6366f1') ?>"></i>
            <?= e($t['group_name']) ?>
          </span>
          <?php endif; ?>
          <?php if ($t['due_date']): ?>
          <span class="badge" style="background:rgba(255,255,255,.06);color:var(--text-muted);font-size:.67rem">
            <i class="bi bi-calendar2 me-1"></i><?= date('M j, Y', strtotime($t['due_date'])) ?>
          </span>
          <?php endif; ?>

          <div class="ms-auto d-flex align-items-center gap-2">
            <!-- Inline status changer -->
            <select class="form-select form-select-sm py-0"
                    style="font-size:.7rem;height:auto;padding:.15rem .4rem;width:auto;background:var(--card-bg);border-color:rgba(255,255,255,.15);color:var(--text-primary)"
                    data-status-toggle data-id="<?= $t['id'] ?>"
                    onclick="event.stopPropagation()">
              <option value="pending"     <?= $t['status'] === 'pending'     ? 'selected' : '' ?>>Pending</option>
              <option value="in_progress" <?= $t['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
              <option value="completed"   <?= $t['status'] === 'completed'   ? 'selected' : '' ?>>Completed</option>
            </select>

            <a href="<?= APP_URL ?>/views/todos/edit?id=<?= $t['id'] ?>"
               class="btn btn-sm btn-ghost py-0 px-2"
               title="Edit"
               onclick="event.stopPropagation()">
              <i class="bi bi-pencil-fill"></i>
            </a>

            <a href="#" class="btn btn-sm btn-ghost py-0 px-2 text-danger"
               title="Delete"
               data-delete-id="<?= $t['id'] ?>"
               onclick="event.stopPropagation()">
              <i class="bi bi-trash-fill"></i>
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="mt-4" aria-label="Todos pagination">
  <ul class="pagination pagination-sm justify-content-center">
    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
      <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
        <i class="bi bi-chevron-left"></i>
      </a>
    </li>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
      <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
        <?= $i ?>
      </a>
    </li>
    <?php endfor; ?>
    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
      <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
        <i class="bi bi-chevron-right"></i>
      </a>
    </li>
  </ul>
</nav>
<?php endif; ?>

<?php endif; ?>

<script>
// Rotate chevron when Bootstrap collapse opens/closes
document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(trigger => {
  const target = document.querySelector(trigger.dataset.bsTarget);
  if (!target) return;
  target.addEventListener('show.bs.collapse',  () => trigger.querySelector('.todo-chevron').style.transform = 'rotate(180deg)');
  target.addEventListener('hide.bs.collapse',  () => trigger.querySelector('.todo-chevron').style.transform = '');
});
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
