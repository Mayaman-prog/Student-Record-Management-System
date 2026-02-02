<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';

require_role(['staff'], '../login.php');

$id = (int)get('id');

$stmt = $pdo->prepare("
  SELECT s.id, s.student_uid, s.attendance_percent, u.full_name, u.email,
         f.name AS faculty, sem.name AS semester
  FROM students s
  JOIN users u ON u.id=s.user_id
  JOIN faculties f ON f.id=s.faculty_id
  JOIN semesters sem ON sem.id=s.semester_id
  WHERE s.id=?
  LIMIT 1
");
$stmt->execute([$id]);
$stu = $stmt->fetch();
if (!$stu) { http_response_code(404); echo "Student not found."; exit; }

$page_title="Student Details";
$page_desc="View student details.";
$base_path='../';

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h1><?= e($stu['full_name']) ?></h1>
  <p>
    Student ID: <strong><?= e($stu['student_uid']) ?></strong><br>
    Email: <?= e($stu['email']) ?><br>
    Faculty: <?= e($stu['faculty']) ?> • Semester: <?= e($stu['semester']) ?><br>
    Attendance: <?= e((string)$stu['attendance_percent']) ?>%
  </p>

  <div class="actions">
    <a class="btn" href="attendance_edit.php?id=<?= (int)$stu['id'] ?>">Edit attendance</a>
    <a class="btn primary" href="grades_edit.php?id=<?= (int)$stu['id'] ?>">Edit grades</a>
    <a class="btn" href="grades_generate.php?id=<?= (int)$stu['id'] ?>">Create grades</a>
    <a class="btn" href="students.php">Back</a>
  </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>