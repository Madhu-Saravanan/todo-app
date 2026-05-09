<?php
// ============================================================
// View: Manage Groups (list + create + edit + delete modals)
// ============================================================
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/GroupModel.php';
require_once __DIR__ . '/../../includes/auth.php';

requireAuth();

$uid       = currentUserId();
$groups    = GroupModel::getAllByUser($uid);
$pageTitle = 'Manage Groups';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-0">My Groups</h4>
    <p class="text-muted small mb-0"><?= count($groups) ?> group<?= count($groups) !== 1 ? 's' : '' ?></p>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGroupModal">
    <i class="bi bi-folder-plus me-1"></i> New Group
  </button>
</div>

<!-- Groups Grid -->
<?php if (empty($groups)): ?>
<div class="empty-state">
  <i class="bi bi-folder2"></i>
  <h5>No groups yet</h5>
  <p class="small">Organise your todos into groups for better productivity!</p>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createGroupModal">
    <i class="bi bi-plus me-1"></i> Create First Group
  </button>
</div>
<?php else: ?>

<div class="row g-3 stagger-children">
  <?php foreach ($groups as $g): ?>
  <div class="col-sm-6 col-lg-4 col-xl-3">
    <div class="app-card h-100" style="border-top: 3px solid <?= e($g['color']) ?>">
      <div class="app-card-body">
        <div class="d-flex align-items-start justify-content-between mb-2">
          <div class="d-flex align-items-center gap-2">
            <span style="width:32px;height:32px;background:<?= e($g['color']) ?>22;border-radius:8px;display:flex;align-items:center;justify-content:center">
              <i class="bi bi-folder-fill" style="color:<?= e($g['color']) ?>"></i>
            </span>
            <div>
              <div class="fw-semibold" style="font-size:.9rem"><?= e($g['name']) ?></div>
              <div class="text-muted" style="font-size:.72rem"><?= (int)$g['todo_count'] ?> todos</div>
            </div>
          </div>

          <!-- Kebab menu -->
          <div class="dropdown">
            <button class="btn btn-sm btn-ghost py-0 px-1" data-bs-toggle="dropdown">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
              <li>
                <a class="dropdown-item small" href="<?= APP_URL ?>/views/groups/view.php?id=<?= $g['id'] ?>">
                  <i class="bi bi-eye me-2"></i>View Todos
                </a>
              </li>
              <li>
                <button class="dropdown-item small" onclick="openEditModal(<?= htmlspecialchars(json_encode($g), ENT_QUOTES) ?>)">
                  <i class="bi bi-pencil me-2"></i>Edit
                </button>
              </li>
              <li><hr class="dropdown-divider my-1"></li>
              <li>
                <button class="dropdown-item small text-danger"
                        onclick="deleteGroup(<?= $g['id'] ?>, '<?= e($g['name']) ?>')">
                  <i class="bi bi-trash me-2"></i>Delete
                </button>
              </li>
            </ul>
          </div>
        </div>

        <?php if ($g['description']): ?>
        <p class="small text-muted mb-3" style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">
          <?= e($g['description']) ?>
        </p>
        <?php endif; ?>

        <a href="<?= APP_URL ?>/views/groups/view.php?id=<?= $g['id'] ?>"
           class="btn btn-sm w-100"
           style="background:<?= e($g['color']) ?>22;color:<?= e($g['color']) ?>;border:1px solid <?= e($g['color']) ?>44">
          <i class="bi bi-arrow-right me-1"></i> View Todos
        </a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>


<!-- ===== CREATE GROUP MODAL ===== -->
<div class="modal fade" id="createGroupModal" tabindex="-1" aria-labelledby="createGroupLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:var(--card-bg);border:1px solid var(--card-border)">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="createGroupLabel">
          <i class="bi bi-folder-plus me-2" style="color:var(--primary)"></i>Create Group
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="<?= APP_URL ?>/controllers/TodoController.php" method="POST">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="group_create">

          <div class="mb-3">
            <label class="form-label" for="cg_name">Group Name <span class="text-danger">*</span></label>
            <input type="text" id="cg_name" name="name" class="form-control"
                   placeholder="e.g. Work, Personal…" required maxlength="150">
          </div>
          <div class="mb-3">
            <label class="form-label" for="cg_desc">Description</label>
            <textarea id="cg_desc" name="description" class="form-control" rows="2"
                      placeholder="Optional description…"></textarea>
          </div>
          <div class="mb-4">
            <label class="form-label" for="cg_color">Color</label>
            <div class="d-flex align-items-center gap-3">
              <input type="color" id="cg_color" name="color" value="#6366f1"
                     class="form-control form-control-color" style="width:48px;height:38px">
              <div class="d-flex gap-2 flex-wrap" id="colorSwatches">
                <?php
                $presets = ['#6366f1','#10b981','#f59e0b','#ef4444','#06b6d4','#8b5cf6','#ec4899','#f97316'];
                foreach ($presets as $c): ?>
                <button type="button" class="border-0 rounded-circle"
                        style="width:22px;height:22px;background:<?= $c ?>;cursor:pointer"
                        onclick="document.getElementById('cg_color').value='<?= $c ?>'"></button>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary fw-semibold">
              <i class="bi bi-folder-plus me-1"></i> Create Group
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ===== EDIT GROUP MODAL ===== -->
<div class="modal fade" id="editGroupModal" tabindex="-1" aria-labelledby="editGroupLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:var(--card-bg);border:1px solid var(--card-border)">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="editGroupLabel">
          <i class="bi bi-pencil-fill me-2" style="color:var(--warning)"></i>Edit Group
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="<?= APP_URL ?>/controllers/TodoController.php" method="POST">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="group_update">
          <input type="hidden" name="id" id="eg_id">

          <div class="mb-3">
            <label class="form-label" for="eg_name">Group Name</label>
            <input type="text" id="eg_name" name="name" class="form-control" required maxlength="150">
          </div>
          <div class="mb-3">
            <label class="form-label" for="eg_desc">Description</label>
            <textarea id="eg_desc" name="description" class="form-control" rows="2"></textarea>
          </div>
          <div class="mb-4">
            <label class="form-label" for="eg_color">Color</label>
            <input type="color" id="eg_color" name="color" class="form-control form-control-color"
                   style="width:48px;height:38px">
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-warning fw-semibold text-dark">
              <i class="bi bi-save me-1"></i> Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Hidden delete form -->
<form id="deleteGroupForm" action="<?= APP_URL ?>/controllers/TodoController.php" method="POST" style="display:none">
  <?= csrfField() ?>
  <input type="hidden" name="action" value="group_delete">
  <input type="hidden" name="id" id="deleteGroupId">
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
function openEditModal(group) {
  document.getElementById('eg_id').value          = group.id;
  document.getElementById('eg_name').value        = group.name;
  document.getElementById('eg_desc').value        = group.description || '';
  document.getElementById('eg_color').value       = group.color;
  new bootstrap.Modal(document.getElementById('editGroupModal')).show();
}

function deleteGroup(id, name) {
  if (!confirm(`Delete group "${name}"? Todos in this group will be unassigned.`)) return;
  document.getElementById('deleteGroupId').value = id;
  document.getElementById('deleteGroupForm').submit();
}
</script>
