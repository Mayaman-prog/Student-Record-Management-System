<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';

require_role(['student'], '../login.php');

$u = current_user();
$subject_id = (int)get('subject_id');

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id=? LIMIT 1");
$stmt->execute([(int)$u['id']]);
$stu = $stmt->fetch();
if (!$stu) { http_response_code(400); echo "Student profile not found."; exit; }

$stmt = $pdo->prepare("
  SELECT sub.code, sub.title,
         COALESCE(g.marks,0) AS marks,
         COALESCE(g.grade_letter,'F') AS grade_letter,
         COALESCE(g.remarks,'') AS remarks,
         COALESCE(g.updated_at,'') AS updated_at
  FROM subjects sub
  LEFT JOIN grades g ON g.subject_id=sub.id AND g.student_id=?
  WHERE sub.id=?
  LIMIT 1
");
$stmt->execute([(int)$stu['id'], $subject_id]);
$row = $stmt->fetch();

$page_title="Grade Details";
$page_desc="Detailed grade view.";
$base_path='../';

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h1>Grade Details</h1>

  <?php if (!$row): ?>
    <p>Subject not found.</p>
    <div class="actions"><a class="btn" href="dashboard.php">Back</a></div>
  <?php else: ?>
    <p><strong><?= e($row['code']) ?></strong> — <?= e($row['title']) ?></p>

    <div class="table-wrap" style="margin-top:12px;">
      <table style="min-width:520px;">
        <tbody>
          <tr><th>Marks</th><td><?= e((string)$row['marks']) ?></td></tr>
          <tr><th>Grade</th><td><?= e($row['grade_letter']) ?></td></tr>
          <tr><th>Remarks</th><td><?= e((string)$row['remarks']) ?></td></tr>
          <tr><th>Last updated</th><td><?= e((string)$row['updated_at']) ?></td></tr>
        </tbody>
      </table>
    </div>

    <div class="actions" style="margin-top:12px;">
      <a class="btn" href="dashboard.php">Back</a>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>