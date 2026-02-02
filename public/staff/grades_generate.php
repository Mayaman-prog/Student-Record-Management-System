<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';

require_role(['staff'], '../login.php');

$id = (int)get('id');

$stmt = $pdo->prepare("
  SELECT s.id, s.faculty_id, s.semester_id, u.full_name, s.student_uid
  FROM students s
  JOIN users u ON u.id = s.user_id
  WHERE s.id=?
  LIMIT 1
");
$stmt->execute([$id]);
$stu = $stmt->fetch();

if (!$stu) {
  http_response_code(404);
  echo "Student not found.";
  exit;
}

$page_title = "Create Grades | Staff";
$page_desc  = "Generate grade rows for all subjects in the student's faculty and semester.";
$base_path  = '../';

$flash = '';
$error = '';

if (is_post()) {
  if (!csrf_verify()) {
    $error = "Invalid request.";
  } else {
    try {
      $pdo->beginTransaction();

      $sub = $pdo->prepare("SELECT id FROM subjects WHERE faculty_id=? AND semester_id=?");
      $sub->execute([(int)$stu['faculty_id'], (int)$stu['semester_id']]);
      $subjects = $sub->fetchAll();

      $ins = $pdo->prepare("
        INSERT IGNORE INTO grades (student_id, subject_id, marks, grade_letter, remarks)
        VALUES (?, ?, 0, 'F', NULL)
      ");

      $created = 0;
      foreach ($subjects as $s) {
        $before = $pdo->query("SELECT ROW_COUNT() AS c")->fetch();
        $ins->execute([(int)$stu['id'], (int)$s['id']]);
      }

      $cnt = $pdo->prepare("SELECT COUNT(*) AS c FROM grades WHERE student_id=?");
      $cnt->execute([(int)$stu['id']]);
      $total = (int)$cnt->fetch()['c'];

      $pdo->commit();
      $flash = "Grades created/updated. Total grade rows for this student: {$total}.";
    } catch (Throwable $t) {
      $pdo->rollBack();
      $error = "Failed to create grades.";
    }
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h2>Create Grades</h2>
  <p class="hint">
    Student: <strong><?= e($stu['full_name']) ?></strong> (<?= e($stu['student_uid']) ?>)
  </p>

  <?php if ($flash): ?><div class="notice ok" data-autohide="1"><?= e($flash) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="notice bad"><?= e($error) ?></div><?php endif; ?>

  <p class="hint">
    This will generate grade rows for every subject in the student's faculty and semester.
    If a grade row already exists, it will not be duplicated.
  </p>

  <form method="post" action="grades_generate.php?id=<?= (int)$id ?>">
    <?= csrf_input() ?>
    <div class="actions">
      <button class="btn primary" type="submit">Generate grades now</button>
      <a class="btn" href="grades_edit.php?id=<?= (int)$id ?>">Go to grade editor</a>
      <a class="btn" href="student_view.php?id=<?= (int)$id ?>">Back</a>
    </div>
  </form>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>