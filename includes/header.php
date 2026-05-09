<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Todo App – Organize your tasks by priority and group, track progress, set due dates, and stay on top of everything — all in one place.">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Todo</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="<?= APP_URL ?>/assets/css/app.css" rel="stylesheet">
  <!-- CSRF token for AJAX requests -->
  <meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES) ?>">
  <!-- APP_URL for JS -->
  <script>const APP_URL = '<?= APP_URL ?>';</script>
</head>
<body>
