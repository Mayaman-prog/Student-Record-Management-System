<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';

require_role(['admin'], '../login.php');

$page_title="Admin Dashboard";
$page_desc="Create and manage staff/student accounts.";
$base_path='../';

$staffCount = (int)$pdo->query("SELECT COUNT(*) c FROM users WHERE role='staff'")->fetch()['c'];
$studCount  = (int)$pdo->query("SELECT COUNT(*) c FROM users WHERE role='student'")->fetch()['c'];

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <div class="grid cols-3">
    <div class="stat">
      <p class="label">Staff Accounts</p>
      <p class="value"><?= $staffCount ?></p>
    </div>
    <div class="stat">
      <p class="label">Student Accounts</p>
      <p class="value"><?= $studCount ?></p>
    </div>
    <div class="stat">
      <p class="label">System Status</p>
      <p class="value"><span class="badge ok">Online</span></p>
    </div>
  </div>

  <div class="actions">
    <a class="btn primary" href="create_user.php">Create Account</a>
    <a class="btn" href="users.php">Manage Accounts</a>
    <a class="btn" href="subjects.php">Manage Subjects</a>
    <a class="btn" href="grades_bulk_generate.php">Bulk Generate Grades</a>
    <a class="btn" href="cohort_overview.php">Cohort Overview</a>
  </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>