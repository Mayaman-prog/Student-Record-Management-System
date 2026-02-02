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

$page_title="Delete Account";
$page_desc="Confirm deletion of an account.";
$base_path='../';

$error='';

if (is_post()) {
  if (!csrf_verify()) {
    $error = "Invalid request.";
  } else {
    $del = $pdo->prepare("DELETE FROM users WHERE id=?");
    $del->execute([$id]);
    redirect('users.php');
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h1>Delete Account</h1>
  <p>You are deleting: <strong><?= e($user['full_name']) ?></strong> (<?= e($user['email']) ?>).</p>
  <p class="muted">This action cannot be undone.</p>

  <?php if ($error): ?><div class="notice bad"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="user_delete.php?id=<?= (int)$id ?>">
    <?= csrf_input() ?>
    <div class="actions">
      <button class="btn danger" type="submit">Confirm delete</button>
      <a class="btn" href="users.php">Cancel</a>
    </div>
  </form>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>