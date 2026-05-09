<?php
// Load project setup.
require 'bootstrap.php';

// Verify account before reset.
// Handle submitted form actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(post('email'));
    $first = post('first_name');
    $last = post('last_name');
    $stmt = $db->prepare("SELECT id, email FROM users WHERE email=? AND first_name=? AND last_name=? AND account_status='active' LIMIT 1");
    $stmt->execute([$email, $first, $last]);
    $user = $stmt->fetch();
    if (!$user) {
        flash_set('error', 'We could not verify that user. Try the exact account name and email.');
        redirect('forgot-password.php');
    }
    $_SESSION['reset_user_id'] = (int) $user['id'];
    flash_set('success', 'Account verified for ' . $user['email'] . '. Create a new password below.');
    redirect('reset-password.php');
}
?><?php
// Set page metadata.
$pageTitle = 'NYS Parks - Forgot Password';
$bodyPage = 'forgot-password';
$extraHead = '';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="auth-shell d-flex align-items-center py-5">
    <section class="container">
      <section class="auth-panel p-4 p-lg-5">
        <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> mb-4"><?= e($flash['message']) ?></div><?php endif; ?>
        <p class="section-kicker mb-2">Account recovery</p>
        <h1 class="h3 fw-bold mb-3">Forgot your password?</h1>
        <p class="text-muted mb-4">Verify your account with your email and name. For security, existing passwords are never displayed; you will create a new one after verification.</p>

        <form method="post">
          <label class="form-label">Email address</label>
          <input type="email" name="email" class="form-control form-control-lg mb-3" placeholder="you@example.com" required /><label class="form-label">First name</label><input type="text" name="first_name" class="form-control form-control-lg mb-3" placeholder="First name" required /><label class="form-label">Last name</label><input type="text" name="last_name" class="form-control form-control-lg mb-4" placeholder="Last name" required />
          <button type="submit" class="btn btn-success rounded-pill w-100 py-3 fw-semibold">Verify Account</button>
        </form>

        <section class="d-flex flex-column flex-sm-row justify-content-between gap-2 mt-4">
          <a href="login.php" class="text-decoration-none fw-semibold">Back to login</a>
          <a href="register.php" class="text-decoration-none fw-semibold">Create a new account</a>
        </section>
      </section>
    </section>
  </main>
<?php include __DIR__ . '/includes/footer.php'; ?>
