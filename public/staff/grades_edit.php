<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';
require_role(['staff'], '../login.php');

$studentId = (int)get('id'); // students.id

// Student details
$stmt = $pdo->prepare("
  SELECT s.id, s.student_uid, s.faculty_id, s.semester_id,
         u.full_name, f.name AS faculty, sem.name AS semester
  FROM students s
  JOIN users u ON u.id = s.user_id
  JOIN faculties f ON f.id = s.faculty_id
  JOIN semesters sem ON sem.id = s.semester_id
  WHERE s.id = ?
  LIMIT 1
");
$stmt->execute([$studentId]);
$stu = $stmt->fetch();

if (!$stu) {
  http_response_code(404);
  echo "Student not found.";
  exit;
}

$page_title = "Edit Grades";
$page_desc  = "Update grades for each subject.";
$base_path  = '../';

$flash = '';
$error = '';

// Load all subjects for this faculty+semester, with grades if already present
$gstmt = $pdo->prepare("
  SELECT
    sub.id AS subject_id,
    sub.code,
    sub.title,
    g.marks,
    g.grade_letter,
    g.remarks
  FROM subjects sub
  LEFT JOIN grades g
    ON g.subject_id = sub.id AND g.student_id = ?
  WHERE sub.faculty_id = ? AND sub.semester_id = ?
  ORDER BY sub.code
");
$gstmt->execute([(int)$stu['id'], (int)$stu['faculty_id'], (int)$stu['semester_id']]);
$rows = $gstmt->fetchAll();

// Save grades (create or update)
if (is_post()) {
  if (!csrf_verify()) {
    $error = "Invalid request. Please refresh and try again.";
  } else {
    $marksArr   = $_POST['marks'] ?? [];
    $letterArr  = $_POST['letter'] ?? [];
    $remarksArr = $_POST['remarks'] ?? [];

    try {
      $pdo->beginTransaction();

      $up = $pdo->prepare("
        INSERT INTO grades (student_id, subject_id, marks, grade_letter, remarks)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
          marks = VALUES(marks),
          grade_letter = VALUES(grade_letter),
          remarks = VALUES(remarks)
      ");

      foreach ($rows as $r) {
        $sid = (int)$r['subject_id'];

        $marks = isset($marksArr[$sid]) ? (float)$marksArr[$sid] : 0.0;
        $marks = max(0.0, min(100.0, $marks)); // clamp 0..100

        $letter = isset($letterArr[$sid]) ? strtoupper(trim((string)$letterArr[$sid])) : 'F';
        $allowed = ['A','B','C','D','E','F'];
        if (!in_array($letter, $allowed, true)) $letter = 'F';

        $remarks = isset($remarksArr[$sid]) ? trim((string)$remarksArr[$sid]) : null;
        if ($remarks === '') $remarks = null;
        if ($remarks !== null && strlen($remarks) > 255) $remarks = substr($remarks, 0, 255);

        $up->execute([(int)$stu['id'], $sid, $marks, $letter, $remarks]);
      }

      $pdo->commit();
      $flash = "Grades saved successfully.";

      // Reload rows to show updated values
      $gstmt->execute([(int)$stu['id'], (int)$stu['faculty_id'], (int)$stu['semester_id']]);
      $rows = $gstmt->fetchAll();
    } catch (Throwable $t) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $error = "Failed to save grades. Please try again.";
    }
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h2>Edit Grades</h2>
  <p class="hint">
    <strong><?= e($stu['full_name']) ?></strong> (<?= e($stu['student_uid']) ?>)
    — <?= e($stu['faculty']) ?>, <?= e($stu['semester']) ?>
  </p>

  <?php if ($flash): ?><div class="notice ok" data-autohide="1"><?= e($flash) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="notice bad"><?= e($error) ?></div><?php endif; ?>

  <?php if (empty($rows)): ?>
    <div class="notice bad">
      No subjects found for this faculty/semester. Admin must add subjects first.
    </div>
    <div class="actions" style="margin-top:12px;">
      <a class="btn" href="students.php">Back</a>
    </div>
  <?php else: ?>

    <form method="post" action="grades_edit.php?id=<?= (int)$studentId ?>">
      <?= csrf_input() ?>

      <div class="table-wrap">
        <table aria-label="Edit grades table">
          <thead>
            <tr>
              <th style="width:140px;">Code</th>
              <th>Subject</th>
              <th style="width:160px;">Marks</th>
              <th style="width:170px;">Grade</th>
              <th style="width:320px;">Remarks</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <?php $sid = (int)$r['subject_id']; ?>
              <tr>
                <td><?= e($r['code']) ?></td>
                <td><?= e($r['title']) ?></td>

                <td>
                  <label class="sr-only" for="marks_<?= $sid ?>">Marks</label>
                  <input
                    id="marks_<?= $sid ?>"
                    name="marks[<?= $sid ?>]"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    value="<?= e((string)($r['marks'] ?? 0)) ?>"
                    placeholder="0 - 100"
                  >
                </td>

                <td>
                  <label class="sr-only" for="grade_<?= $sid ?>">Grade</label>
                  <div class="select">
                    <select id="grade_<?= $sid ?>" name="letter[<?= $sid ?>]">
                      <?php
                        $current = $r['grade_letter'] ?: 'F';
                        foreach (['A','B','C','D','E','F'] as $g):
                      ?>
                        <option value="<?= $g ?>" <?= $current === $g ? 'selected' : '' ?>><?= $g ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </td>

                <td>
                  <label class="sr-only" for="rem_<?= $sid ?>">Remarks</label>
                  <input
                    id="rem_<?= $sid ?>"
                    name="remarks[<?= $sid ?>]"
                    type="text"
                    maxlength="255"
                    value="<?= e((string)($r['remarks'] ?? '')) ?>"
                    placeholder="Optional remarks"
                  >
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="actions" style="margin-top:14px;">
        <button class="btn primary" type="submit">Save grades</button>
        <a class="btn" href="student_view.php?id=<?= (int)$studentId ?>">Back</a>
      </div>
    </form>

  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>