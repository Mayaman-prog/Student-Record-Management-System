<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';

require_role(['staff'], '../login.php');

$page_title = "Students";
$page_desc  = "Search students and manage their attendance and grades.";
$base_path  = '../';

$q = trim($_GET['q'] ?? '');

$students = [];

if ($q !== '') {
  $stmt = $pdo->prepare("
    SELECT
      s.id,
      s.student_uid,
      s.attendance_percent,
      u.full_name,
      u.email,
      f.name AS faculty,
      sem.name AS semester,
      s.created_at
    FROM students s
    JOIN users u ON u.id = s.user_id
    JOIN faculties f ON f.id = s.faculty_id
    JOIN semesters sem ON sem.id = s.semester_id
    WHERE
      (s.student_uid LIKE ? OR
       u.full_name LIKE ? OR
       u.email LIKE ? OR
       f.name LIKE ? OR
       sem.name LIKE ?)
    ORDER BY s.created_at DESC
  ");

  $like = "%{$q}%";
  $stmt->execute([$like, $like, $like, $like, $like]);
  $students = $stmt->fetchAll();
} else {
  $stmt = $pdo->prepare("
    SELECT
      s.id,
      s.student_uid,
      s.attendance_percent,
      u.full_name,
      u.email,
      f.name AS faculty,
      sem.name AS semester,
      s.created_at
    FROM students s
    JOIN users u ON u.id = s.user_id
    JOIN faculties f ON f.id = s.faculty_id
    JOIN semesters sem ON sem.id = s.semester_id
    ORDER BY s.created_at DESC
  ");
  $stmt->execute();
  $students = $stmt->fetchAll();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h2>Student List</h2>
  <p class="hint">Search by student ID, name, email, faculty, or semester.</p>

  <form method="get" action="students.php" style="margin-bottom:14px;">
    <div class="form-row">
      <div>
        <label for="q">Search</label>
        <input id="q" name="q" value="<?= e($q) ?>" placeholder="e.g., SRMS-2026-00001, BSc Computing, Semester 1">
      </div>
      <div style="display:flex;align-items:end;gap:10px;">
        <button class="btn primary" type="submit">Search</button>
        <a class="btn ghost" href="students.php">Reset</a>
      </div>
    </div>
  </form>

  <div class="table-wrap">
    <table aria-label="Students">
      <thead>
        <tr>
          <th>Student ID</th>
          <th>Name</th>
          <th>Faculty</th>
          <th>Semester</th>
          <th>Attendance</th>
          <th style="width:280px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($students)): ?>
          <tr><td colspan="6">No students found.</td></tr>
        <?php else: ?>
          <?php foreach ($students as $s): ?>
            <tr>
              <td><?= e($s['student_uid']) ?></td>
              <td>
                <?= e($s['full_name']) ?>
                <div class="hint" style="margin:6px 0 0;"><?= e($s['email']) ?></div>
              </td>
              <td><?= e($s['faculty']) ?></td>
              <td><?= e($s['semester']) ?></td>
              <td>
                <?php
                  $att = (float)$s['attendance_percent'];
                  $badgeClass = $att >= 75 ? 'ok' : ($att >= 50 ? 'warn' : 'bad');
                ?>
                <span class="badge <?= $badgeClass ?>"><?= e(number_format($att, 2)) ?>%</span>
              </td>
              <td>
                <a class="btn" href="student_view.php?id=<?= (int)$s['id'] ?>">View</a>
                <a class="btn" href="attendance_edit.php?id=<?= (int)$s['id'] ?>">Attendance</a>
                <a class="btn primary" href="grades_edit.php?id=<?= (int)$s['id'] ?>">Grades</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>