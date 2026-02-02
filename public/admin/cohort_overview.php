<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';
require_role(['admin'], '../login.php');

$page_title = "Cohort Overview";
$page_desc  = "Monitor students, subjects, and missing grade rows across faculty and semester cohorts.";
$base_path  = '../';

$sql = "
  SELECT
    f.id AS faculty_id,
    f.name AS faculty_name,
    sem.id AS semester_id,
    sem.name AS semester_name,

    -- Students in cohort
    (SELECT COUNT(*)
     FROM students s
     WHERE s.faculty_id = f.id AND s.semester_id = sem.id) AS student_count,

    -- Subjects in cohort
    (SELECT COUNT(*)
     FROM subjects sub
     WHERE sub.faculty_id = f.id AND sub.semester_id = sem.id) AS subject_count,

    -- Grade rows currently stored
    (SELECT COUNT(*)
     FROM grades g
     JOIN students s2 ON s2.id = g.student_id
     JOIN subjects sub2 ON sub2.id = g.subject_id
     WHERE s2.faculty_id = f.id AND s2.semester_id = sem.id
       AND sub2.faculty_id = f.id AND sub2.semester_id = sem.id) AS grade_rows
  FROM faculties f
  CROSS JOIN semesters sem
  ORDER BY f.name, sem.id
";

$rows = $pdo->query($sql)->fetchAll();

$cohorts = [];
foreach ($rows as $r) {
  $students = (int)$r['student_count'];
  $subjects = (int)$r['subject_count'];
  if ($students === 0 && $subjects === 0) continue;

  $expected = $students * $subjects;
  $existing = (int)$r['grade_rows'];
  $missing  = max(0, $expected - $existing);

  $cohorts[] = [
    'faculty_id' => (int)$r['faculty_id'],
    'faculty'    => (string)$r['faculty_name'],
    'semester_id'=> (int)$r['semester_id'],
    'semester'   => (string)$r['semester_name'],
    'students'   => $students,
    'subjects'   => $subjects,
    'expected'   => $expected,
    'existing'   => $existing,
    'missing'    => $missing,
  ];
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h2>Cohort Overview</h2>
  <p class="hint">
    A cohort is defined by Faculty + Semester. Use this page to detect missing grade rows and fix them quickly.
  </p>

  <div class="grid cols-3" style="margin-top:10px;">
    <?php
      $totalCohorts  = count($cohorts);
      $totalStudents = array_sum(array_column($cohorts, 'students'));
      $totalMissing  = array_sum(array_column($cohorts, 'missing'));
    ?>
    <div class="stat">
      <p class="label">Cohorts Tracked</p>
      <p class="value"><?= (int)$totalCohorts ?></p>
    </div>
    <div class="stat">
      <p class="label">Total Students</p>
      <p class="value"><?= (int)$totalStudents ?></p>
    </div>
    <div class="stat">
      <p class="label">Missing Grade Rows</p>
      <p class="value">
        <span class="badge <?= $totalMissing > 0 ? 'warn' : 'ok' ?>">
          <?= (int)$totalMissing ?>
        </span>
      </p>
    </div>
  </div>

  <div class="actions" style="margin-top:14px;">
    <a class="btn" href="subjects.php">Manage Subjects</a>
    <a class="btn" href="grades_bulk_generate.php">Bulk Generate Grades</a>
  </div>

  <div class="table-wrap" style="margin-top:14px;">
    <table aria-label="Cohort overview table">
      <thead>
        <tr>
          <th>Faculty</th>
          <th>Semester</th>
          <th>Students</th>
          <th>Subjects</th>
          <th>Expected Grades</th>
          <th>Existing Grades</th>
          <th>Missing</th>
          <th style="width:260px;">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($cohorts)): ?>
        <tr><td colspan="8">No cohorts found.</td></tr>
      <?php else: ?>
        <?php foreach ($cohorts as $c): ?>
          <?php
            $missingBadge = $c['missing'] > 0 ? 'warn' : 'ok';
          ?>
          <tr>
            <td><?= e($c['faculty']) ?></td>
            <td><?= e($c['semester']) ?></td>
            <td><?= (int)$c['students'] ?></td>
            <td><?= (int)$c['subjects'] ?></td>
            <td><?= (int)$c['expected'] ?></td>
            <td><?= (int)$c['existing'] ?></td>
            <td>
              <span class="badge <?= $missingBadge ?>"><?= (int)$c['missing'] ?></span>
            </td>
            <td>
              <a class="btn"
                 href="subjects.php?faculty_id=<?= (int)$c['faculty_id'] ?>&semester_id=<?= (int)$c['semester_id'] ?>">
                View Subjects
              </a>

              <a class="btn primary"
                 href="grades_bulk_generate.php?faculty_id=<?= (int)$c['faculty_id'] ?>&semester_id=<?= (int)$c['semester_id'] ?>">
                Fix Missing Grades
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>