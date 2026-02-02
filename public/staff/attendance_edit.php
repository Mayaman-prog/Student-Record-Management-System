<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';

require_role(['staff'], '../login.php');

$id = (int)get('id');

$stmt = $pdo->prepare("
  SELECT s.id, s.student_uid, s.attendance_percent, u.full_name
  FROM students s
  JOIN users u ON u.id=s.user_id
  WHERE s.id=?
  LIMIT 1
");
$stmt->execute([$id]);
$stu = $stmt->fetch();
if (!$stu) { http_response_code(404); echo "Student not found."; exit; }

$page_title="Edit Attendance";
$page_desc="Update student attendance.";
$base_path='../';

$flash=''; $error='';

if (is_post()) {
  if (!csrf_verify()) $error="Invalid request.";
  else {
    $att = post_float('attendance_percent', (float)$stu['attendance_percent']);
    if ($att < 0 || $att > 100) $error="Attendance must be between 0 and 100.";
    else {
      $upd = $pdo->prepare("UPDATE students SET attendance_percent=? WHERE id=?");
      $upd->execute([$att, $id]);
      $stu['attendance_percent'] = $att;
      $flash="Attendance updated.";
    }
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h1>Edit Attendance</h1>
  <p><strong><?= e($stu['full_name']) ?></strong> (<?= e($stu['student_uid']) ?>)</p>

  <?php if ($flash): ?><div class="notice ok" data-autohide="1"><?= e($flash) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="notice bad"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="attendance_edit.php?id=<?= (int)$id ?>">
    <?= csrf_input() ?>
    <label for="attendance_percent">Attendance (%)</label>
    <input id="attendance_percent" name="attendance_percent" type="number" step="0.01" min="0" max="100"
           value="<?= e((string)$stu['attendance_percent']) ?>" required>

    <div class="actions">
      <button class="btn primary" type="submit">Save</button>
      <a class="btn" href="students.php">Back</a>
    </div>
  </form>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>