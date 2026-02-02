<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

require_login('login.php');
$u = current_user();

$page_title="Profile";
$page_desc="View your profile and update password.";
$base_path='./';

$flash='';
$error='';

if (is_post()) {
  if (!csrf_verify()) $error="Invalid request.";
  else {
    $new = post('new_password');
    if (strlen($new) < 6) $error="Password must be at least 6 characters.";
    else {
      $hash = password_hash($new, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
      $stmt->execute([$hash, (int)$u['id']]);
      $flash="Password updated.";
    }
  }
}

$studentInfo = null;
if ($u['role'] === 'student') {
  $stmt = $pdo->prepare("
    SELECT s.student_uid, f.name AS faculty, sem.name AS semester, s.attendance_percent
    FROM students s
    JOIN faculties f ON f.id=s.faculty_id
    JOIN semesters sem ON sem.id=s.semester_id
    WHERE s.user_id=?
    LIMIT 1
  ");
  $stmt->execute([(int)$u['id']]);
  $studentInfo = $stmt->fetch() ?: null;
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card">
  <h1>My Profile</h1>

  <?php if ($flash): ?><div class="notice ok" data-autohide="1"><?= e($flash) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="notice bad"><?= e($error) ?></div><?php endif; ?>

  <div class="table-wrap" style="margin-top:12px;">
    <table style="min-width:520px;">
      <tbody>
        <tr><th>Full name</th><td><?= e($u['full_name']) ?></td></tr>
        <tr><th>Email</th><td><?= e($u['email']) ?></td></tr>
        <tr><th>Role</th><td><?= e($u['role']) ?></td></tr>
        <?php if ($studentInfo): ?>
          <tr><th>Student ID</th><td><?= e($studentInfo['student_uid']) ?></td></tr>
          <tr><th>Faculty</th><td><?= e($studentInfo['faculty']) ?></td></tr>
          <tr><th>Semester</th><td><?= e($studentInfo['semester']) ?></td></tr>
          <tr><th>Attendance</th><td><?= e((string)$studentInfo['attendance_percent']) ?>%</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <h2>Change Password</h2>
  <form method="post" action="profile.php">
    <?= csrf_input() ?>
    <label for="new_password">New password</label>
    <input id="new_password" name="new_password" type="password" minlength="6" required>
    <div class="actions">
      <button class="btn primary" type="submit">Update</button>
    </div>
  </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>