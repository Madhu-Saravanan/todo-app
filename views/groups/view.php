<?php
// ============================================================
// View: View Todos by Group
// ============================================================
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/TodoModel.php';
require_once __DIR__ . '/../../models/GroupModel.php';
require_once __DIR__ . '/../../includes/auth.php';

requireAuth();

$uid     = currentUserId();
$groupId = (int)($_GET['id'] ?? 0);
$group   = GroupModel::getById($groupId, $uid);

if (!$group) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Group not found.'];
    header('Location: ' . APP_URL . '/views/groups/index');
    exit;
}

// Filters within the group
$filters = [
    'group_id' => $groupId,
    'status'   => $_GET['status'] ?? '',
    'priority' => $_GET['priority'] ?? '',
    'search'   => trim($_GET['search'] ?? ''),
];
$page   = max(1, (int)($_GET['page'] ?? 1));
$result = TodoModel::getAll($uid, $filters, $page);

$todos      = $result['data'];
$totalPages = $result['pages'];
$total      = $result['total'];

$pageTitle = e($group['name']) . ' – Todos';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/views/groups/index" class="btn btn-sm btn-ghost">
    <i class="bi bi-arrow-left"></i>
  </a>
  <div class="d-flex align-items-center gap-3 flex-grow-1">
    <span style="width:42px;height:42px;background:<?= e($group['color']) ?>22;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <i class="bi bi-folder-fill fs-5" style="color:<?= e($group['color']) ?>"></i>
    </span>
    <div>
      <h4 class="fw-bold mb-0"><?= e($group['name']) ?></h4>
      <?php if ($group['description']): ?>
      <p class="text-muted small mb-0"><?= e($group['description']) ?></p>
      <?php endif; ?>
    </div>
  </div>
  <a href="<?= APP_URL ?>/views/todos/create" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> Add Todo
  </a>
</div>

<!-- Quick stats for this group (all todos, not just current page) -->
<div class="row g-2 mb-4">
  <?php
  $gs   = TodoModel::getGroupStats($uid, $groupId);
  $gpct = $gs['total'] > 0 ? round(($gs['completed'] / $gs['total']) * 100) : 0;
  ?>
  <div class="col-4 col-md-2">
    <div class="app-card text-center p-2">
      <div class="fw-bold fs-5"><?= (int)$gs['total'] ?></div>
      <div class="text-muted" style="font-size:.7rem">Total</div>
    </div>
  </div>
  <div class="col-4 col-md-2">
    <div class="app-card text-center p-2">
      <div class="fw-bold fs-5" style="color:#34d399"><?= (int)$gs['completed'] ?></div>
      <div class="text-muted" style="font-size:.7rem">Done</div>
    </div>
  </div>
  <div class="col-4 col-md-2">
    <div class="app-card text-center p-2">
      <div class="fw-bold fs-5" style="color:#fbbf24"><?= (int)$gs['pending'] ?></div>
      <div class="text-muted" style="font-size:.7rem">Pending</div>
    </div>
  </div>
  <div class="col-12 col-md-6 d-flex align-items-center gap-2">
    <div class="progress flex-grow-1" style="height:8px">
      <div class="progress-bar" style="width:<?= $gpct ?>%;background:<?= e($group['color']) ?>" role="progressbar"></div>
    </div>
    <span class="small fw-semibold" style="color:<?= e($group['color']) ?>"><?= $gpct ?>%</span>
  </div>
</div>

<!-- Filter bar -->
<form method="GET">
  <input type="hidden" name="id" value="<?= $groupId ?>">
  <div class="filter-bar">
    <div class="flex-grow-1" style="min-width:160px">
      <div class="input-group input-group-sm">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" id="searchInput" name="search" class="form-control"
               placeholder="Search…" value="<?= e($filters['search']) ?>">
      </div>
    </div>
    <select name="status" class="form-select form-select-sm" style="max-width:140px" onchange="this.form.submit()">
      <option value="">All Status</option>
      <option value="pending"     <?= $filters['status'] === 'pending'     ? 'selected' : '' ?>>Pending</option>
      <option value="in_progress" <?= $filters['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
      <option value="completed"   <?= $filters['status'] === 'completed'   ? 'selected' : '' ?>>Completed</option>
    </select>
    <select name="priority" class="form-select form-select-sm" style="max-width:130px" onchange="this.form.submit()">
      <option value="">All Priority</option>
      <option value="high"   <?= $filters['priority'] === 'high'   ? 'selected' : '' ?>>🔴 High</option>
      <option value="medium" <?= $filters['priority'] === 'medium' ? 'selected' : '' ?>>🟡 Medium</option>
      <option value="low"    <?= $filters['priority'] === 'low'    ? 'selected' : '' ?>>🟢 Low</option>
    </select>
  </div>
</form>

<!-- Todos -->
<?php if (empty($todos)): ?>
<div class="empty-state">
  <i class="bi bi-inbox"></i>
  <h5>No todos in this group</h5>
  <a href="<?= APP_URL ?>/views/todos/create" class="btn btn-sm btn-primary mt-2">
    <i class="bi bi-plus me-1"></i> Add Todo
  </a>
</div>
<?php else: ?>

<div class="row g-3 stagger-children" id="todoGrid">
  <?php foreach ($todos as $t): ?>
  <div class="col-12 col-md-6 col-xl-4">
    <div class="todo-card priority-<?= e($t['priority']) ?> status-<?= e($t['status']) ?>"
         data-id="<?= $t['id'] ?>">
      <div class="d-flex align-items-start gap-2">
        <input type="checkbox" class="form-check-input mt-1"
               style="width:1.15em;height:1.15em;cursor:pointer"
               data-status-toggle data-id="<?= $t['id'] ?>"
               value="completed" <?= $t['status'] === 'completed' ? 'checked' : '' ?>>
        <div class="flex-grow-1 overflow-hidden">
          <div class="todo-title"><?= e($t['title']) ?></div>
          <?php if ($t['description']): ?>
          <div class="todo-desc"><?= e($t['description']) ?></div>
          <?php endif; ?>
        </div>
        <div class="dropdown flex-shrink-0">
          <button class="btn btn-sm btn-ghost py-0 px-1" data-bs-toggle="dropdown">
            <i class="bi bi-three-dots-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li>
              <a class="dropdown-item small" href="<?= APP_URL ?>/views/todos/edit?id=<?= $t['id'] ?>">
                <i class="bi bi-pencil me-2"></i>Edit
              </a>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <li>
              <a class="dropdown-item small text-danger" href="#" data-delete-id="<?= $t['id'] ?>">
                <i class="bi bi-trash me-2"></i>Delete
              </a>
            </li>
          </ul>
        </div>
      </div>
      <div class="todo-meta">
        <span class="badge badge-priority-<?= e($t['priority']) ?> text-capitalize"><?= e($t['priority']) ?></span>
        <span class="badge badge-status-<?= e($t['status']) ?> text-capitalize">
          <?= str_replace('_', ' ', e($t['status'])) ?>
        </span>
        <?php if ($t['due_date']): ?>
        <span class="badge" style="background:rgba(255,255,255,.06);color:var(--text-muted);font-size:.67rem">
          <i class="bi bi-calendar2 me-1"></i><?= date('M j', strtotime($t['due_date'])) ?>
        </span>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="mt-4">
  <ul class="pagination pagination-sm justify-content-center">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
      <a class="page-link" href="?id=<?= $groupId ?>&page=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['page'=>1,'id'=>1])) ?>">
        <?= $i ?>
      </a>
    </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
