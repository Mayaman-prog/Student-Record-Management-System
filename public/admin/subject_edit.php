<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';
require_role(['admin'], '../login.php');

$id = (int)get('id');

$stmt = $pdo->prepare("
  SELECT id, faculty_id, semester_id, code, title
  FROM subjects
  WHERE id=?
  LIMIT 1
");
$stmt->execute([$id]);
$sub = $stmt->fetch();

if (!$sub) {
  http_response_code(404);
  echo "Subject not found.";
  exit;
}

$page_title = "Edit Subject";
$page_desc  = "Update subject information.";
$base_path  = '../';

$faculties = $pdo->query("SELECT id, name FROM faculties ORDER BY name")->fetchAll();
$semesters = $pdo->query("SELECT id, name FROM semesters ORDER BY id")->fetchAll();

$error = '';
$flash = '';

if (is_post()) {
  if (!csrf_verify()) {
    $error = "Invalid request.";
  } else {
    $faculty_id  = post_int('faculty_id', 0);
    $semester_id = post_int('semester_id', 0);
    $code  = strtoupper(trim(post('code')));
    $title = trim(post('title'));

    if ($faculty_id <= 0 || $semester_id <= 0 || $code === '' || $title === '') {
      $error = "All fields are required.";
    } else {
      try {
        $upd = $pdo->prepare("
          UPDATE subjects
          SET faculty_id=?, semester_id=?, code=?, title=?
          WHERE id=?
        ");
        $upd->execute([$faculty_id, $semester_id, $code, $title, $id]);

        $sub['faculty_id'] = $faculty_id;
        $sub['semester_id'] = $semester_id;
        $sub['code'] = $code;
        $sub['title'] = $title;

        $flash = "Subject updated.";
      } catch (Throwable $t) {
        $error = "Update failed. Code must be unique within the same faculty and semester.";
      }
    }
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h2>Edit Subject</h2>

  <?php if ($flash): ?><div class="notice ok" data-autohide="1"><?= e($flash) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="notice bad"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="subject_edit.php?id=<?= (int)$id ?>">
    <?= csrf_input() ?>

    <div class="form-row">
      <div>
        <label for="faculty_id">Faculty</label>
        <select id="faculty_id" name="faculty_id" required>
          <?php foreach ($faculties as $f): ?>
            <option value="<?= (int)$f['id'] ?>" <?= (int)$sub['faculty_id'] === (int)$f['id'] ? 'selected':'' ?>>
              <?= e($f['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="semester_id">Semester</label>
        <select id="semester_id" name="semester_id" required>
          <?php foreach ($semesters as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= (int)$sub['semester_id'] === (int)$s['id'] ? 'selected':'' ?>>
              <?= e($s['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-row" style="margin-top:12px;">
      <div>
        <label for="code">Subject Code</label>
        <input id="code" name="code" required maxlength="30" value="<?= e($sub['code']) ?>">
      </div>
      <div>
        <label for="title">Subject Title</label>
        <input id="title" name="title" required maxlength="160" value="<?= e($sub['title']) ?>">
      </div>
    </div>

    <div class="actions" style="margin-top:14px;">
      <button class="btn primary" type="submit">Save</button>
      <a class="btn" href="subjects.php">Back</a>
    </div>
  </form>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>