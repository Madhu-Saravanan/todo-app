<?php
// ============================================================
// Sidebar + Topbar partial – included in all protected pages
// ============================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/GroupModel.php';
require_once __DIR__ . '/../models/TodoModel.php';

$groups     = GroupModel::getAllByUser(currentUserId());
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$csrf       = generateCsrfToken();
?>

<!-- Mobile sidebar backdrop -->
<div id="sidebarBackdrop" class="sidebar-backdrop"></div>

<!-- ============ SIDEBAR ============ -->
<nav id="sidebar" class="sidebar d-flex flex-column">
  <!-- Brand -->
  <div class="sidebar-brand">
    <span class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></span>
    <span class="brand-text">AntiGravity</span>
  </div>

  <!-- Navigation links -->
  <ul class="sidebar-nav flex-grow-1 mt-3">
    <li class="nav-label">MAIN</li>
    <li>
      <a href="<?= APP_URL ?>/views/dashboard.php"
         class="sidebar-link <?= str_contains($currentUri, 'dashboard') ? 'active' : '' ?>">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
      </a>
    </li>
    <li>
      <a href="<?= APP_URL ?>/views/todos/index.php"
         class="sidebar-link <?= str_contains($currentUri, 'todos/index') ? 'active' : '' ?>">
        <i class="bi bi-check2-square"></i> All Todos
      </a>
    </li>
    <li>
      <a href="<?= APP_URL ?>/views/todos/create.php"
         class="sidebar-link <?= str_contains($currentUri, 'todos/create') ? 'active' : '' ?>">
        <i class="bi bi-plus-circle-fill"></i> New Todo
      </a>
    </li>

    <li class="nav-label mt-3">GROUPS</li>
    <?php foreach ($groups as $g): ?>
    <li>
      <a href="<?= APP_URL ?>/views/groups/view.php?id=<?= $g['id'] ?>"
         class="sidebar-link <?= str_contains($currentUri, 'groups/view') && ($_GET['id'] ?? 0) == $g['id'] ? 'active' : '' ?>"
         style="border-left: 3px solid <?= e($g['color']) ?>">
        <i class="bi bi-folder-fill" style="color:<?= e($g['color']) ?>"></i>
        <?= e($g['name']) ?>
        <span class="badge ms-auto"><?= (int)$g['todo_count'] ?></span>
      </a>
    </li>
    <?php endforeach; ?>

    <li>
      <a href="<?= APP_URL ?>/views/groups/index.php"
         class="sidebar-link <?= str_contains($currentUri, 'groups/index') ? 'active' : '' ?>">
        <i class="bi bi-folder-plus"></i> Manage Groups
      </a>
    </li>
  </ul>

  <!-- User info + logout at bottom -->
  <div class="sidebar-footer">
    <div class="user-info">
      <span class="user-avatar"><i class="bi bi-person-circle"></i></span>
      <div>
        <div class="user-name"><?= e(currentUserName()) ?></div>
        <div class="user-role">Member</div>
      </div>
    </div>
    <a href="<?= APP_URL ?>/controllers/AuthController.php?action=logout"
       class="btn btn-sm btn-outline-danger w-100 mt-2">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</nav>

<!-- ============ MAIN WRAPPER ============ -->
<div id="content-wrapper">
  <!-- TOPBAR -->
  <header class="topbar">
    <!-- Hamburger toggle -->
    <button id="sidebarToggle" class="btn btn-sm btn-ghost me-2" title="Toggle sidebar">
      <i class="bi bi-list fs-5"></i>
    </button>

    <!-- Page title -->
    <h6 class="topbar-title mb-0"><?= isset($pageTitle) ? e($pageTitle) : 'Dashboard' ?></h6>

    <div class="topbar-actions ms-auto">
      <!-- Dark/Light Mode Toggle -->
      <button id="themeToggle" class="btn btn-sm btn-ghost" title="Toggle theme">
        <i class="bi bi-sun-fill" id="themeIcon"></i>
      </button>

      <!-- Notification: Due Today badge -->
      <?php
      $dueToday = TodoModel::getDueToday(currentUserId());
      $dueCnt   = count($dueToday);
      ?>
      <div class="dropdown">
        <button class="btn btn-sm btn-ghost position-relative" data-bs-toggle="dropdown">
          <i class="bi bi-bell-fill"></i>
          <?php if ($dueCnt > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?= $dueCnt ?>
          </span>
          <?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:260px">
          <li><h6 class="dropdown-header">Due Today (<?= $dueCnt ?>)</h6></li>
          <?php if ($dueCnt === 0): ?>
          <li><span class="dropdown-item text-muted small">All caught up! 🎉</span></li>
          <?php endif; ?>
          <?php foreach ($dueToday as $dt): ?>
          <li>
            <a class="dropdown-item small" href="<?= APP_URL ?>/views/todos/edit.php?id=<?= $dt['id'] ?>">
              <span class="badge bg-<?= $dt['priority'] === 'high' ? 'danger' : ($dt['priority'] === 'medium' ? 'warning' : 'secondary') ?> me-1">
                <?= e($dt['priority']) ?>
              </span>
              <?= e($dt['title']) ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </header>

  <!-- MAIN CONTENT AREA -->
  <main class="main-content">
    <!-- Flash messages -->
    <?php if (!empty($_SESSION['flash'])): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999">
      <div id="flashToast" class="toast align-items-center text-bg-<?= e($_SESSION['flash']['type']) ?> border-0 show"
           role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
        <div class="d-flex">
          <div class="toast-body"><?= $_SESSION['flash']['msg'] ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    </div>
    <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
