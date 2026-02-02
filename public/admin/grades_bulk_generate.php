<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';
require_role(['admin'], '../login.php');

$page_title = "Bulk Generate Grades";
$page_desc  = "Generate missing grade rows for all students in a faculty and semester.";
$base_path  = '../';

$faculties = $pdo->query("SELECT id, name FROM faculties ORDER BY name")->fetchAll();
$semesters = $pdo->query("SELECT id, name FROM semesters ORDER BY id")->fetchAll();

$flash = '';
$error = '';

$faculty_id  = (int)($_POST['faculty_id'] ?? ($_GET['faculty_id'] ?? 0));
$semester_id = (int)($_POST['semester_id'] ?? ($_GET['semester_id'] ?? 0));

if (is_post()) {
  if (!csrf_verify()) {
    $error = "Invalid request. Please refresh and try again.";
  } elseif ($faculty_id <= 0 || $semester_id <= 0) {
    $error = "Please select both faculty and semester.";
  } else {
    try {
      $pdo->beginTransaction();

      $stuStmt = $pdo->prepare("
        SELECT id
        FROM students
        WHERE faculty_id = ? AND semester_id = ?
      ");
      $stuStmt->execute([$faculty_id, $semester_id]);
      $studentIds = array_map(static fn($r) => (int)$r['id'], $stuStmt->fetchAll());

      $subStmt = $pdo->prepare("
        SELECT id
        FROM subjects
        WHERE faculty_id = ? AND semester_id = ?
      ");
      $subStmt->execute([$faculty_id, $semester_id]);
      $subjectIds = array_map(static fn($r) => (int)$r['id'], $subStmt->fetchAll());

      if (empty($studentIds)) {
        $pdo->rollBack();
        $error = "No students found for the selected faculty and semester.";
      } elseif (empty($subjectIds)) {
        $pdo->rollBack();
        $error = "No subjects found for the selected faculty and semester. Add subjects first.";
      } else {
        $ins = $pdo->prepare("
          INSERT IGNORE INTO grades (student_id, subject_id, marks, grade_letter, remarks)
          VALUES (?, ?, 0, 'F', NULL)
        ");

        $insertAttempts = 0;
        foreach ($studentIds as $sid) {
          foreach ($subjectIds as $subId) {
            $ins->execute([$sid, $subId]);
            $insertAttempts++;
          }
        }

        $countStmt = $pdo->prepare("
          SELECT COUNT(*) AS c
          FROM grades g
          JOIN students s ON s.id = g.student_id
          WHERE s.faculty_id = ? AND s.semester_id = ?
        ");
        $countStmt->execute([$faculty_id, $semester_id]);
        $totalGrades = (int)$countStmt->fetch()['c'];

        $pdo->commit();

        $flash = "Bulk grade generation complete. Insert checks performed: {$insertAttempts}. Total grade rows for this cohort now: {$totalGrades}.";
      }
    } catch (Throwable $t) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $error = "Bulk generation failed. Please check your database constraints and try again.";
    }
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h2>Bulk Generate Grades</h2>
  <p class="hint">
    This will create missing grade rows for every student in a selected faculty and semester.
    It will not duplicate existing grades.
  </p>

  <?php if ($flash): ?><div class="notice ok" data-autohide="1"><?= e($flash) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="notice bad"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="grades_bulk_generate.php">
    <?= csrf_input() ?>

    <div class="form-row">
      <div>
        <label for="faculty_id">Faculty</label>
        <select id="faculty_id" name="faculty_id" required>
          <option value="">Select faculty</option>
          <?php foreach ($faculties as $f): ?>
            <option value="<?= (int)$f['id'] ?>" <?= $faculty_id === (int)$f['id'] ? 'selected':'' ?>>
              <?= e($f['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="semester_id">Semester</label>
        <select id="semester_id" name="semester_id" required>
          <option value="">Select semester</option>
          <?php foreach ($semesters as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= $semester_id === (int)$s['id'] ? 'selected':'' ?>>
              <?= e($s['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="actions" style="margin-top:14px;">
      <button class="btn primary" type="submit"
        data-confirm="Generate grades for all students in this faculty and semester?">
        Generate for Cohort
      </button>
      <a class="btn" href="subjects.php">Manage Subjects</a>
      <a class="btn" href="dashboard.php">Back</a>
    </div>
  </form>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>