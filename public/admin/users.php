<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';

require_role(['admin'], '../login.php');

$page_title = "Manage Accounts";
$page_desc  = "Admin can view, edit and delete staff and student accounts.";
$base_path  = '../';

$q = trim($_GET['q'] ?? '');

$users = [];

if ($q !== '') {
  $stmt = $pdo->prepare("
    SELECT id, full_name, email, role, created_at
    FROM users
    WHERE role IN ('staff','student')
      AND (full_name LIKE ? OR email LIKE ?)
    ORDER BY created_at DESC
  ");
  $like = "%{$q}%";
  $stmt->execute([$like, $like]);
  $users = $stmt->fetchAll();
} else {
  $stmt = $pdo->prepare("
    SELECT id, full_name, email, role, created_at
    FROM users
    WHERE role IN ('staff','student')
    ORDER BY created_at DESC
  ");
  $stmt->execute();
  $users = $stmt->fetchAll();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section class="card">
  <h2>Staff & Student Accounts</h2>
  <p class="hint">Search, edit or delete accounts created in the system.</p>

  <form method="get" action="users.php" style="margin-bottom:14px;">
    <div class="form-row">
      <div>
        <label for="q">Search User</label>
        <input id="q" name="q" value="<?= e($q) ?>" placeholder="Search by name or email">
      </div>
      <div style="display:flex;align-items:end;gap:10px;">
        <button class="btn primary" type="submit">Search</button>
        <a class="btn ghost" href="users.php">Reset</a>
      </div>
    </div>
  </form>

  <div class="table-wrap">
    <table aria-label="Accounts list">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Created</th>
          <th style="width:220px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
          <tr>
            <td colspan="5">No accounts found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= e($u['full_name']) ?></td>
              <td><?= e($u['email']) ?></td>
              <td>
                <span class="badge <?= $u['role']==='staff' ? 'warn' : 'ok' ?>">
                  <?= e(strtoupper($u['role'])) ?>
                </span>
              </td>
              <td><?= e(date('d M Y', strtotime((string)$u['created_at']))) ?></td>
              <td>
                <a class="btn" href="user_edit.php?id=<?= (int)$u['id'] ?>">Edit</a>
                <a class="btn danger" data-confirm="Delete this account permanently?" href="user_delete.php?id=<?= (int)$u['id'] ?>">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="actions right" style="margin-top:16px;">
    <a class="btn primary" href="create_user.php">Create New Account</a>
  </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>