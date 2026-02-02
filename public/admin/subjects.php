<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';
require_role(['admin'], '../login.php');

$page_title = "Manage Subjects";
$page_desc  = "Filter by faculty/semester and manage subject list.";
$base_path  = '../';

$faculty_id  = (int)($_GET['faculty_id'] ?? 0);
$semester_id = (int)($_GET['semester_id'] ?? 0);
$q = trim((string)($_GET['q'] ?? ''));

$faculties = $pdo->query("SELECT id, name FROM faculties ORDER BY name")->fetchAll();
$semesters = $pdo->query("SELECT id, name FROM semesters ORDER BY id")->fetchAll();

$where = [];
$params = [];

if ($faculty_id > 0) {
  $where[] = "sub.faculty_id = ?";
  $params[] = $faculty_id;
}

if ($semester_id > 0) {
  $where[] = "sub.semester_id = ?";
  $params[] = $semester_id;
}

if ($q !== '') {
  $where[] = "(sub.code LIKE ? OR sub.title LIKE ? OR f.name LIKE ? OR sem.name LIKE ?)";
  $like = "%{$q}%";
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
}

$whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

$sql = "
  SELECT sub.id, sub.code, sub.title, f.name AS faculty, sem.name AS semester
  FROM subjects sub
  JOIN faculties f ON f.id = sub.faculty_id
  JOIN semesters sem ON sem.id = sub.semester_id
  $whereSql
  ORDER BY f.name, sem.id, sub.code
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$subjects = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h2>Subjects</h2>
  <p class="hint">Filter by faculty/semester and manage subject list.</p>

  <form method="get" action="subjects.php" style="margin-bottom:14px;">
    <div class="form-row">
      <div>
        <label for="faculty_id">Faculty</label>
        <select id="faculty_id" name="faculty_id">
          <option value="0">All faculties</option>
          <?php foreach ($faculties as $f): ?>
            <option value="<?= (int)$f['id'] ?>" <?= $faculty_id === (int)$f['id'] ? 'selected' : '' ?>>
              <?= e($f['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="semester_id">Semester</label>
        <select id="semester_id" name="semester_id">
          <option value="0">All semesters</option>
          <?php foreach ($semesters as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= $semester_id === (int)$s['id'] ? 'selected' : '' ?>>
              <?= e($s['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-row" style="margin-top:12px;">
      <div>
        <label for="q">Search</label>
        <input id="q" name="q" value="<?= e($q) ?>" placeholder="Search by code, title, faculty, semester">
      </div>

      <div style="display:flex;align-items:end;gap:10px;">
        <button class="btn primary" type="submit">Apply</button>
        <a class="btn ghost" href="subjects.php">Reset</a>
        <a class="btn" href="subjects_seed_all.php">Seed All Subjects</a>
        <a class="btn" href="subject_create.php">Add Subject</a>
      </div>
    </div>
  </form>

  <div class="table-wrap">
    <table aria-label="Subjects table">
      <thead>
        <tr>
          <th>Code</th>
          <th>Title</th>
          <th>Faculty</th>
          <th>Semester</th>
          <th style="width:220px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($subjects)): ?>
          <tr><td colspan="5">No subjects found.</td></tr>
        <?php else: ?>
          <?php foreach ($subjects as $s): ?>
            <tr>
              <td><?= e($s['code']) ?></td>
              <td><?= e($s['title']) ?></td>
              <td><?= e($s['faculty']) ?></td>
              <td><?= e($s['semester']) ?></td>
              <td>
                <a class="btn" href="subject_edit.php?id=<?= (int)$s['id'] ?>">Edit</a>
                <a class="btn danger" data-confirm="Delete this subject? Related grades will be removed too."
                   href="subject_delete.php?id=<?= (int)$s['id'] ?>">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="actions right" style="margin-top:16px;">
    <a class="btn primary" href="subject_create.php">Add Subject</a>
  </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>