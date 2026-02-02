<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';

require_role(['staff'], '../login.php');

$page_title="Staff Dashboard";
$page_desc="Staff can edit attendance and grades.";
$base_path='../';

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h1>Staff Dashboard</h1>
  <p>Manage student attendance and grades.</p>

  <div class="actions">
    <a class="btn primary" href="students.php">Students</a>
  </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>