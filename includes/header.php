<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

/** @var string $page_title */
/** @var string $page_desc */
/** @var string $base_path */
$page_title = $page_title ?? 'Student Record Management System';
$page_desc  = $page_desc ?? 'Manage student records securely: grades, attendance, and profiles.';
$base_path  = $base_path ?? './';

$u = current_user();
$role = $u['role'] ?? null;

$links = [];
$links[] = ['label' => 'Home', 'href' => $base_path . 'index.php'];

if ($role === 'admin') {
  $links[] = ['label' => 'Admin Dashboard', 'href' => $base_path . 'admin/dashboard.php'];
  $links[] = ['label' => 'Manage Accounts', 'href' => $base_path . 'admin/users.php'];
  $links[] = ['label' => 'Create Account', 'href' => $base_path . 'admin/create_user.php'];
}
if ($role === 'staff') {
  $links[] = ['label' => 'Staff Dashboard', 'href' => $base_path . 'staff/dashboard.php'];
  $links[] = ['label' => 'Students', 'href' => $base_path . 'staff/students.php'];
}
if ($role === 'student') {
  $links[] = ['label' => 'Student Dashboard', 'href' => $base_path . 'student/dashboard.php'];
}

$links[] = ['label' => 'Profile', 'href' => $base_path . 'profile.php'];
$links[] = ['label' => 'Logout', 'href' => $base_path . 'logout.php'];

function is_active(string $href): bool {
  $current = $_SERVER['PHP_SELF'] ?? '';
  return str_ends_with($current, basename($href));
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($page_title) ?></title>
  <meta name="description" content="<?= e($page_desc) ?>">
  <link rel="stylesheet" href="<?= e($base_path) ?>../assets/css/style.css">
  <script src="<?= e($base_path) ?>../assets/js/app.js" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<div class="shell">
  <aside class="sidebar" aria-label="Sidebar navigation">
    <div class="brand">
      <div class="logo" aria-hidden="true">SR</div>
      <div>
        <p class="brand-title">SRMS</p>
        <p class="brand-subtitle"><?= $role ? e(strtoupper((string)$role)) : 'GUEST' ?></p>
      </div>
    </div>

    <nav class="side-nav" aria-label="Primary">
      <?php foreach ($links as $ln): ?>
        <a href="<?= e($ln['href']) ?>" class="<?= is_active($ln['href']) ? 'active' : '' ?>">
          <span aria-hidden="true">•</span>
          <span><?= e($ln['label']) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <?php if ($u): ?>
      <div class="side-foot" role="note">
        <div class="kv"><span>User</span><span><?= e($u['full_name']) ?></span></div>
        <div class="kv"><span>Email</span><span><?= e($u['email']) ?></span></div>
      </div>
    <?php else: ?>
      <div class="side-foot" role="note">
        Login to access dashboards.
      </div>
    <?php endif; ?>
  </aside>

  <main class="main" id="main" role="main">
    <div class="topbar">
      <div>
        <h1 class="page-title"><?= e($page_title) ?></h1>
        <p class="page-desc"><?= e($page_desc) ?></p>
      </div>
    </div>