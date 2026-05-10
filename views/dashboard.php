<?php
// ============================================================
// View: Dashboard
// ============================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/TodoModel.php';
require_once __DIR__ . '/../models/GroupModel.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();

$uid    = currentUserId();
$stats  = TodoModel::getDashboardStats($uid);
$groups = GroupModel::getAllByUser($uid);
$due    = TodoModel::getDueToday($uid);

// Recent todos (last 5)
$recent = TodoModel::getAll($uid, [], 1, 5)['data'];

$pageTitle = 'Dashboard';

// Completion percentage
$total     = (int)($stats['total']     ?? 0);
$completed = (int)($stats['completed'] ?? 0);
$pct       = $total > 0 ? round(($completed / $total) * 100) : 0;

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<!-- ===== DASHBOARD CONTENT ===== -->

<!-- Stat Cards -->
<div class="row g-3 mb-2 stagger-children">

  <div class="col-6 col-xl-3">
    <div class="stat-card stat-card-clickable" data-filter="" data-label="All Todos" style="cursor:pointer">
      <div class="stat-icon primary"><i class="bi bi-list-task"></i></div>
      <div class="flex-grow-1">
        <div class="stat-label">Total Todos</div>
        <div class="stat-value"><?= $total ?></div>
      </div>
      <i class="bi bi-chevron-down stat-chevron text-muted"></i>
    </div>
  </div>

  <div class="col-6 col-xl-3">
    <div class="stat-card stat-card-clickable" data-filter="completed" data-label="Completed Tasks" style="cursor:pointer">
      <div class="stat-icon success"><i class="bi bi-check2-all"></i></div>
      <div class="flex-grow-1">
        <div class="stat-label">Completed</div>
        <div class="stat-value"><?= (int)($stats['completed'] ?? 0) ?></div>
      </div>
      <i class="bi bi-chevron-down stat-chevron text-muted"></i>
    </div>
  </div>

  <div class="col-6 col-xl-3">
    <div class="stat-card stat-card-clickable" data-filter="pending" data-label="Pending Tasks" style="cursor:pointer">
      <div class="stat-icon warning"><i class="bi bi-hourglass-split"></i></div>
      <div class="flex-grow-1">
        <div class="stat-label">Pending</div>
        <div class="stat-value"><?= (int)($stats['pending'] ?? 0) ?></div>
      </div>
      <i class="bi bi-chevron-down stat-chevron text-muted"></i>
    </div>
  </div>

  <div class="col-6 col-xl-3">
    <div class="stat-card stat-card-clickable" data-filter="high_priority" data-label="High Priority Tasks" style="cursor:pointer">
      <div class="stat-icon danger"><i class="bi bi-exclamation-triangle-fill"></i></div>
      <div class="flex-grow-1">
        <div class="stat-label">High Priority</div>
        <div class="stat-value"><?= (int)($stats['high_priority'] ?? 0) ?></div>
      </div>
      <i class="bi bi-chevron-down stat-chevron text-muted"></i>
    </div>
  </div>
</div>

<!-- Inline task panel (shown when a stat card is clicked) -->
<div id="statPanel" class="mb-4" style="display:none">
  <div class="app-card">
    <div class="app-card-header">
      <h6 class="mb-0 fw-semibold" id="statPanelLabel"><i class="bi bi-list-task me-2"></i></h6>
      <button class="btn btn-sm btn-ghost py-0" id="statPanelClose"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="app-card-body p-0" id="statPanelBody">
      <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>
    </div>
  </div>
</div>

<!-- Progress + Due Today -->
<div class="row g-3 mb-4">

  <!-- Overall Progress -->
  <div class="col-lg-5">
    <div class="app-card h-100">
      <div class="app-card-header">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-bar-chart-fill me-2" style="color:var(--primary)"></i>Overall Progress</h6>
        <span class="badge" style="background:var(--primary-glow);color:var(--primary)"><?= $pct ?>%</span>
      </div>
      <div class="app-card-body">
        <!-- Big donut-style number -->
        <div class="text-center mb-3">
          <div style="font-size:3.5rem;font-weight:800;background:linear-gradient(135deg,#818cf8,#c4b5fd);-webkit-background-clip:text;-webkit-text-fill-color:transparent;line-height:1">
            <?= $pct ?><span style="font-size:1.5rem">%</span>
          </div>
          <div class="text-muted small"><?= $completed ?> of <?= $total ?> tasks completed</div>
        </div>
        <div class="progress mb-2" style="height:10px">
          <div class="progress-bar" style="width:<?= $pct ?>%;background:linear-gradient(90deg,#6366f1,#8b5cf6)" role="progressbar"></div>
        </div>

        <!-- Status breakdown -->
        <div class="row g-2 mt-2 text-center">
          <div class="col-4">
            <div class="p-2 rounded" style="background:rgba(100,116,139,.12)">
              <div class="fw-bold"><?= (int)($stats['pending'] ?? 0) ?></div>
              <div class="small text-muted">Pending</div>
            </div>
          </div>
          <div class="col-4">
            <div class="p-2 rounded" style="background:rgba(6,182,212,.1)">
              <div class="fw-bold" style="color:#22d3ee"><?= (int)($stats['in_progress'] ?? 0) ?></div>
              <div class="small text-muted">In Progress</div>
            </div>
          </div>
          <div class="col-4">
            <div class="p-2 rounded" style="background:rgba(16,185,129,.1)">
              <div class="fw-bold" style="color:#34d399"><?= $completed ?></div>
              <div class="small text-muted">Done</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Due Today -->
  <div class="col-lg-7">
    <div class="app-card h-100">
      <div class="app-card-header">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-calendar-event-fill me-2" style="color:var(--warning)"></i>Due Today</h6>
        <span class="badge bg-warning text-dark"><?= count($due) ?></span>
      </div>
      <div class="app-card-body p-0">
        <?php if (empty($due)): ?>
        <div class="empty-state py-4">
          <i class="bi bi-sun"></i>
          <h5>All clear today!</h5>
          <p class="small">Nothing due today. Enjoy your day 😊</p>
        </div>
        <?php else: ?>
        <ul class="list-group list-group-flush">
          <?php foreach ($due as $t): ?>
          <li class="list-group-item d-flex align-items-center gap-3 py-2 px-3"
              style="background:transparent;border-color:var(--card-border);color:var(--text-primary)">
            <span class="badge badge-priority-<?= e($t['priority']) ?> text-capitalize">
              <?= e($t['priority']) ?>
            </span>
            <span class="flex-grow-1 small fw-semibold"><?= e($t['title']) ?></span>
            <?php if ($t['group_name']): ?>
            <span class="badge" style="background:rgba(99,102,241,.15);color:#818cf8;font-size:.65rem">
              <?= e($t['group_name']) ?>
            </span>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/views/todos/edit?id=<?= $t['id'] ?>"
               class="btn btn-sm btn-ghost py-0 px-2"><i class="bi bi-pencil-fill"></i></a>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Groups Summary + Recent Todos -->
<div class="row g-3">

  <!-- Groups -->
  <div class="col-lg-4">
    <div class="app-card h-100">
      <div class="app-card-header">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-folder2-open me-2" style="color:var(--info)"></i>My Groups</h6>
        <a href="<?= APP_URL ?>/views/groups/index" class="btn btn-sm btn-ghost py-0 small">Manage</a>
      </div>
      <div class="app-card-body p-0">
        <?php if (empty($groups)): ?>
        <div class="empty-state py-3">
          <i class="bi bi-folder-x" style="font-size:2rem"></i>
          <p class="small mt-2">No groups yet.</p>
        </div>
        <?php else: ?>
        <ul class="list-group list-group-flush">
          <?php foreach ($groups as $g): ?>
          <li class="list-group-item d-flex align-items-center gap-2 px-3"
              style="background:transparent;border-color:var(--card-border);color:var(--text-primary)">
            <span class="color-swatch" style="background:<?= e($g['color']) ?>"></span>
            <span class="flex-grow-1 small fw-semibold"><?= e($g['name']) ?></span>
            <span class="badge" style="background:rgba(255,255,255,.07);color:var(--text-muted)">
              <?= (int)$g['todo_count'] ?>
            </span>
            <a href="<?= APP_URL ?>/views/groups/view?id=<?= $g['id'] ?>"
               class="btn btn-sm btn-ghost py-0 px-1"><i class="bi bi-arrow-right"></i></a>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent Todos -->
  <div class="col-lg-8">
    <div class="app-card h-100">
      <div class="app-card-header">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2" style="color:var(--success)"></i>Recent Todos</h6>
        <a href="<?= APP_URL ?>/views/todos/index" class="btn btn-sm btn-ghost py-0 small">View All</a>
      </div>
      <div class="app-card-body p-0">
        <?php if (empty($recent)): ?>
        <div class="empty-state py-3">
          <i class="bi bi-check2-square" style="font-size:2rem"></i>
          <p class="small mt-2">No todos yet. <a href="<?= APP_URL ?>/views/todos/create">Create one!</a></p>
        </div>
        <?php else: ?>
        <ul class="list-group list-group-flush">
          <?php foreach ($recent as $t): ?>
          <li class="list-group-item px-3 py-2"
              style="background:transparent;border-color:var(--card-border);color:var(--text-primary)">
            <div class="d-flex align-items-center gap-2">
              <!-- Quick status toggle -->
              <input type="checkbox"
                     class="form-check-input mt-0"
                     style="width:1.1em;height:1.1em;cursor:pointer"
                     data-status-toggle
                     data-id="<?= $t['id'] ?>"
                     value="completed"
                     <?= $t['status'] === 'completed' ? 'checked' : '' ?>>
              <div class="flex-grow-1">
                <div class="small fw-semibold <?= $t['status'] === 'completed' ? 'text-decoration-line-through text-muted' : '' ?>">
                  <?= e($t['title']) ?>
                </div>
                <?php if ($t['due_date']): ?>
                <div class="text-muted" style="font-size:.7rem">
                  <i class="bi bi-calendar2 me-1"></i><?= date('M j, Y', strtotime($t['due_date'])) ?>
                </div>
                <?php endif; ?>
              </div>
              <span class="badge badge-priority-<?= e($t['priority']) ?> text-capitalize">
                <?= e($t['priority']) ?>
              </span>
              <span class="badge badge-status-<?= e($t['status']) ?> text-capitalize">
                <?= str_replace('_', ' ', e($t['status'])) ?>
              </span>
              <a href="<?= APP_URL ?>/views/todos/edit?id=<?= $t['id'] ?>"
                 class="btn btn-sm btn-ghost py-0 px-1"><i class="bi bi-pencil"></i></a>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const panel      = document.getElementById('statPanel');
  const panelLabel = document.getElementById('statPanelLabel');
  const panelBody  = document.getElementById('statPanelBody');
  const panelClose = document.getElementById('statPanelClose');
  let activeCard   = null;

  function priorityColor(p) {
    return p === 'high' ? 'var(--priority-high)' : p === 'medium' ? 'var(--priority-medium)' : 'var(--priority-low)';
  }

  function statusLabel(s) {
    return s.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
  }

  function renderTodos(todos, label) {
    panelLabel.innerHTML = `<i class="bi bi-list-task me-2"></i>${label}`;
    if (!todos.length) {
      panelBody.innerHTML = '<div class="empty-state py-3"><i class="bi bi-inbox" style="font-size:2rem"></i><p class="small mt-2 mb-0">No tasks found.</p></div>';
      return;
    }
    const rows = todos.map(t => `
      <li class="list-group-item px-3 py-2" style="background:transparent;border-color:var(--card-border);color:var(--text-primary)">
        <div class="d-flex align-items-start gap-2">
          <div class="flex-grow-1">
            <div class="fw-semibold small ${t.status === 'completed' ? 'text-decoration-line-through text-muted' : ''}">${t.title}</div>
            ${t.description ? `<div class="text-muted" style="font-size:.78rem;margin-top:.2rem">${t.description}</div>` : ''}
          </div>
          <div class="d-flex align-items-center gap-1 flex-shrink-0">
            <span class="badge" style="background:rgba(255,255,255,.07);color:var(--text-secondary);font-size:.68rem;border-left:3px solid ${priorityColor(t.priority)}">${t.priority}</span>
            <span class="badge badge-status-${t.status}" style="font-size:.68rem">${statusLabel(t.status)}</span>
            ${t.group_name ? `<span class="badge" style="background:rgba(99,102,241,.15);color:#818cf8;font-size:.68rem">${t.group_name}</span>` : ''}
            <a href="<?= APP_URL ?>/views/todos/edit?id=${t.id}" class="btn btn-sm btn-ghost py-0 px-1"><i class="bi bi-pencil"></i></a>
          </div>
        </div>
      </li>`).join('');
    panelBody.innerHTML = `<ul class="list-group list-group-flush">${rows}</ul>`;
  }

  function fetchAndShow(card) {
    const filter = card.dataset.filter;
    const label  = card.dataset.label;

    // Toggle off if same card clicked again
    if (activeCard === card) {
      panel.style.display = 'none';
      card.querySelector('.stat-chevron').style.transform = '';
      activeCard = null;
      return;
    }

    // Reset previous chevron
    if (activeCard) {
      activeCard.querySelector('.stat-chevron').style.transform = '';
    }
    activeCard = card;
    card.querySelector('.stat-chevron').style.transform = 'rotate(180deg)';

    panel.style.display = 'block';
    panelBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
    panelLabel.innerHTML = `<i class="bi bi-list-task me-2"></i>${label}`;

    // Build query params
    const params = new URLSearchParams();
    if (filter === 'pending' || filter === 'completed' || filter === 'in_progress') {
      params.set('status', filter);
    } else if (filter === 'high_priority') {
      params.set('priority', 'high');
    }

    fetch(`<?= APP_URL ?>/api/todos?${params}`)
      .then(r => r.json())
      .then(json => renderTodos(json.data, label))
      .catch(() => { panelBody.innerHTML = '<p class="text-danger small p-3">Failed to load tasks.</p>'; });
  }

  document.querySelectorAll('.stat-card-clickable').forEach(card => {
    card.addEventListener('click', () => fetchAndShow(card));
  });

  panelClose.addEventListener('click', () => {
    panel.style.display = 'none';
    if (activeCard) {
      activeCard.querySelector('.stat-chevron').style.transform = '';
      activeCard = null;
    }
  });
})();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
