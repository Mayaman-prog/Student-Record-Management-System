<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';

require_role(['student'], '../login.php');

$u = current_user();

$stmt = $pdo->prepare("
  SELECT s.id AS student_id, s.student_uid, f.name AS faculty, sem.name AS semester
  FROM students s
  JOIN faculties f ON f.id=s.faculty_id
  JOIN semesters sem ON sem.id=s.semester_id
  WHERE s.user_id=?
  LIMIT 1
");
$stmt->execute([(int)$u['id']]);
$stu = $stmt->fetch();
if (!$stu) { http_response_code(400); echo "Student profile not linked."; exit; }

$page_title="Student Dashboard";
$page_desc="View your grades for all subjects.";
$base_path='../';

$stmt2 = $pdo->prepare("
  SELECT sub.id AS subject_id, sub.code, sub.title,
         COALESCE(g.marks,0) AS marks,
         COALESCE(g.grade_letter,'F') AS grade_letter
  FROM subjects sub
  LEFT JOIN grades g ON g.subject_id=sub.id AND g.student_id=:sid
  WHERE sub.faculty_id = (SELECT faculty_id FROM students WHERE id=:sid2)
    AND sub.semester_id = (SELECT semester_id FROM students WHERE id=:sid3)
  ORDER BY sub.code
");
$stmt2->execute([
  ':sid' => (int)$stu['student_id'],
  ':sid2' => (int)$stu['student_id'],
  ':sid3' => (int)$stu['student_id'],
]);
$rows = $stmt2->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h1>Welcome, <?= e($u['full_name']) ?></h1>
  <p>
    Student ID: <strong><?= e($stu['student_uid']) ?></strong><br>
    Faculty: <?= e($stu['faculty']) ?> • Semester: <?= e($stu['semester']) ?>
  </p>

  <h2>My Grades</h2>

  <div class="table-wrap" style="margin-top:12px;">
    <table aria-label="My grades">
      <thead>
        <tr><th>Code</th><th>Subject</th><th>Marks</th><th>Grade</th><th>Details</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= e($r['code']) ?></td>
            <td><?= e($r['title']) ?></td>
            <td><?= e((string)$r['marks']) ?></td>
            <td><?= e($r['grade_letter']) ?></td>
            <td><a class="btn" href="grade_details.php?subject_id=<?= (int)$r['subject_id'] ?>">View</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="5">No subjects configured for your faculty/semester.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>