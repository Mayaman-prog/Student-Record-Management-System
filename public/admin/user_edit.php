<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';

require_role(['admin'], '../login.php');

$id = (int)get('id');

$stmt = $pdo->prepare("SELECT id, full_name, email, role FROM users WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user || $user['role'] === 'admin') {
  http_response_code(404);
  echo "Account not found.";
  exit;
}

$faculties = $pdo->query("SELECT id, name FROM faculties ORDER BY name")->fetchAll();
$semesters = $pdo->query("SELECT id, name FROM semesters ORDER BY id")->fetchAll();

$studentRow = null;
if ($user['role'] === 'student') {
  $st = $pdo->prepare("SELECT id, student_uid, faculty_id, semester_id FROM students WHERE user_id=? LIMIT 1");
  $st->execute([$id]);
  $studentRow = $st->fetch();
}

$page_title = "Edit Account";
$page_desc  = "Update staff/student account details.";
$base_path  = '../';

$error = '';
$flash = '';

if (is_post()) {
  if (!csrf_verify()) {
    $error = "Invalid request.";
  } else {
    $full_name = post('full_name');
    $email = post('email');
    $new_password = post('new_password');

    $faculty_id = post_int('faculty_id', 0);
    $semester_id = post_int('semester_id', 0);

    if ($full_name === '' || $email === '') {
      $error = "Name and email are required.";
    } else {
      try {
        $pdo->beginTransaction();

        $upd = $pdo->prepare("UPDATE users SET full_name=?, email=? WHERE id=?");
        $upd->execute([$full_name, $email, $id]);

        if ($new_password !== '') {
          if (strlen($new_password) < 6) throw new RuntimeException("Password must be at least 6 characters.");
          $hash = password_hash($new_password, PASSWORD_DEFAULT);
          $pupd = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
          $pupd->execute([$hash, $id]);
        }

        if ($user['role'] === 'student') {
          if ($faculty_id <= 0 || $semester_id <= 0) throw new RuntimeException("Faculty and semester are required for student.");

          $supd = $pdo->prepare("UPDATE students SET faculty_id=?, semester_id=? WHERE user_id=?");
          $supd->execute([$faculty_id, $semester_id, $id]);

          $sidStmt = $pdo->prepare("SELECT id FROM students WHERE user_id=? LIMIT 1");
          $sidStmt->execute([$id]);
          $sid = (int)$sidStmt->fetch()['id'];

          $subStmt = $pdo->prepare("SELECT id FROM subjects WHERE faculty_id=? AND semester_id=?");
          $subStmt->execute([$faculty_id, $semester_id]);
          $subs = $subStmt->fetchAll();

          $ins = $pdo->prepare("INSERT IGNORE INTO grades (student_id, subject_id, marks, grade_letter) VALUES (?,?,0,'F')");
          foreach ($subs as $s) $ins->execute([$sid, (int)$s['id']]);
        }

        $pdo->commit();
        $flash = "Account updated.";
      } catch (Throwable $t) {
        $pdo->rollBack();
        $error = "Update failed. Email may already exist.";
      }
    }
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h1>Edit Account</h1>

  <?php if ($flash): ?><div class="notice ok" data-autohide="1"><?= e($flash) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="notice bad"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="user_edit.php?id=<?= (int)$id ?>">
    <?= csrf_input() ?>

    <div class="form-row">
      <div>
        <label for="full_name">Full name</label>
        <input id="full_name" name="full_name" required value="<?= e($user['full_name']) ?>">
      </div>
      <div>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required value="<?= e($user['email']) ?>">
      </div>
    </div>

    <div>
      <label for="new_password">Reset password (optional)</label>
      <input id="new_password" name="new_password" type="password" minlength="6" placeholder="Leave blank to keep unchanged">
    </div>

    <?php if ($user['role'] === 'student'): ?>
      <h2>Student Settings</h2>
      <p class="muted">Student ID is generated automatically and cannot be changed.</p>

      <div class="form-row">
        <div>
          <label for="faculty_id">Faculty</label>
          <select id="faculty_id" name="faculty_id" required>
            <?php foreach ($faculties as $f): ?>
              <option value="<?= (int)$f['id'] ?>" <?= ($studentRow && (int)$studentRow['faculty_id']===(int)$f['id']) ? 'selected':'' ?>>
                <?= e($f['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="semester_id">Semester</label>
          <select id="semester_id" name="semester_id" required>
            <?php foreach ($semesters as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= ($studentRow && (int)$studentRow['semester_id']===(int)$s['id']) ? 'selected':'' ?>>
                <?= e($s['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    <?php endif; ?>

    <div class="actions">
      <button class="btn primary" type="submit">Save changes</button>
      <a class="btn" href="users.php">Back</a>
    </div>
  </form>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>