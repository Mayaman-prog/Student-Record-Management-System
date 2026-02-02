<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$page_title = "Login | SRMS";
$page_desc  = "Login to access student records and grades.";
$base_path  = './';

$error = '';
if (is_post()) {
  if (!csrf_verify()) {
    $error = "Invalid request. Please refresh and try again.";
  } else {
    $email = post('email');
    $password = post('password');

    $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email=? LIMIT 1");
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if ($u && password_verify($password, $u['password_hash'])) {
      $_SESSION['user'] = [
        'id' => (int)$u['id'],
        'full_name' => (string)$u['full_name'],
        'email' => (string)$u['email'],
        'role' => (string)$u['role']
      ];
      redirect('index.php');
    } else {
      $error = "Incorrect email or password.";
    }
  }
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card">
  <h1>Login</h1>
  <p>Enter your credentials to continue.</p>

  <?php if ($error): ?>
    <div class="notice bad" role="alert"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post" action="login.php">
    <?= csrf_input() ?>

    <div class="form-row">
      <div>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required maxlength="160" placeholder="name@example.com">
      </div>
      <div>
        <label for="password">Password</label>
        <div style="display:flex;gap:10px;align-items:center;">
          <input id="password" name="password" type="password" required minlength="6" placeholder="Your password">
          <button class="btn" type="button" data-toggle-password="password">Show</button>
        </div>
      </div>
    </div>

    <div class="actions">
      <button class="btn primary" type="submit">Sign in</button>
    </div>
  </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>