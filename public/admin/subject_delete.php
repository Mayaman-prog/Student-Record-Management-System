<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';
require_role(['admin'], '../login.php');

$id = (int)get('id');

$stmt = $pdo->prepare("
  SELECT sub.id, sub.code, sub.title, f.name AS faculty, sem.name AS semester
  FROM subjects sub
  JOIN faculties f ON f.id=sub.faculty_id
  JOIN semesters sem ON sem.id=sub.semester_id
  WHERE sub.id=?
  LIMIT 1
");
$stmt->execute([$id]);
$sub = $stmt->fetch();

if (!$sub) {
  http_response_code(404);
  echo "Subject not found.";
  exit;
}

$page_title = "Delete Subject";
$page_desc  = "Confirm subject deletion.";
$base_path  = '../';

$error = '';

if (is_post()) {
  if (!csrf_verify()) {
    $error = "Invalid request.";
  } else {
    $del = $pdo->prepare("DELETE FROM subjects WHERE id=?");
    $del->execute([$id]);
    redirect('subjects.php');
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h2>Delete Subject</h2>
  <p class="hint">
    You are deleting: <strong><?= e($sub['code']) ?></strong> — <?= e($sub['title']) ?><br>
    <?= e($sub['faculty']) ?> • <?= e($sub['semester']) ?>
  </p>
  <p class="hint">Related grade rows for students will also be removed.</p>

  <?php if ($error): ?><div class="notice bad"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="subject_delete.php?id=<?= (int)$id ?>">
    <?= csrf_input() ?>
    <div class="actions">
      <button class="btn danger" type="submit">Confirm delete</button>
      <a class="btn" href="subjects.php">Cancel</a>
    </div>
  </form>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>