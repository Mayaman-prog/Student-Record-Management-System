<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';
require_role(['admin'], '../login.php');

$page_title = "Add Subject";
$page_desc  = "Create a subject for a specific faculty and semester.";
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
    } elseif (strlen($code) > 30) {
      $error = "Subject code is too long (max 30).";
    } elseif (strlen($title) > 160) {
      $error = "Subject title is too long (max 160).";
    } else {
      try {
        $stmt = $pdo->prepare("
          INSERT INTO subjects (faculty_id, semester_id, code, title)
          VALUES (?,?,?,?)
        ");
        $stmt->execute([$faculty_id, $semester_id, $code, $title]);
        $flash = "Subject created successfully.";
      } catch (Throwable $t) {
        $error = "Could not create subject. Code must be unique within the same faculty and semester.";
      }
    }
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h2>Add Subject</h2>
  <p class="hint">After adding subjects, staff can generate grades for students.</p>

  <?php if ($flash): ?><div class="notice ok" data-autohide="1"><?= e($flash) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="notice bad"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="subject_create.php">
    <?= csrf_input() ?>

    <div class="form-row">
      <div>
        <label for="faculty_id">Faculty</label>
        <select id="faculty_id" name="faculty_id" required>
          <option value="">Select faculty</option>
          <?php foreach ($faculties as $f): ?>
            <option value="<?= (int)$f['id'] ?>"><?= e($f['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="semester_id">Semester</label>
        <select id="semester_id" name="semester_id" required>
          <option value="">Select semester</option>
          <?php foreach ($semesters as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-row" style="margin-top:12px;">
      <div>
        <label for="code">Subject Code</label>
        <input id="code" name="code" required maxlength="30" placeholder="e.g., CS202">
      </div>
      <div>
        <label for="title">Subject Title</label>
        <input id="title" name="title" required maxlength="160" placeholder="e.g., Database Systems">
      </div>
    </div>

    <div class="actions" style="margin-top:14px;">
      <button class="btn primary" type="submit">Create</button>
      <a class="btn" href="subjects.php">Back</a>
    </div>
  </form>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>