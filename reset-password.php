<?php
require 'bootstrap.php';

// Load verified reset user.
$resetUserId = $_SESSION['reset_user_id'] ?? null;

if (!$resetUserId) {
    flash_set('error', 'Verify your account first.');
    redirect('forgot-password.php');
}

$stmt = $db->prepare("SELECT id, first_name, last_name, email FROM users WHERE id = ? AND account_status='active' LIMIT 1");
$stmt->execute([$resetUserId]);
$resetUser = $stmt->fetch();

if (!$resetUser) {
    unset($_SESSION['reset_user_id']);
    flash_set('error', 'We could not find an active account to reset. Please verify your account again.');
    redirect('forgot-password.php');
}

// Handle password reset submit.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = post('password');
    $confirm = post('confirm_password');

    if (strlen($password) < 8) {
        flash_set('error', 'Password must be at least 8 characters.');
        redirect('reset-password.php');
    }

    if ($password !== $confirm) {
        flash_set('error', 'Passwords do not match.');
        redirect('reset-password.php');
    }

    $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
        ->execute([password_hash($password, PASSWORD_DEFAULT), $resetUserId]);

    unset($_SESSION['reset_user_id']);

    flash_set('success', 'Password changed. Please log in with your new password.');
    redirect('login.php');
}
?>

<?php
$pageTitle = 'NYS Parks - Reset Password';
$bodyPage = 'reset-password';
$extraHead = '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

    <main class="auth-shell d-flex align-items-center py-5">
        <section class="container">
            <section class="auth-panel p-4 p-lg-5">

                <!-- Flash message -->
                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> mb-4">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <!-- Reset heading -->
                <p class="section-kicker mb-2">Account recovery</p>
                <h1 class="h3 fw-bold mb-3">Reset your password</h1>
                <p class="text-muted mb-3">Create a new password for <?= e(trim($resetUser['first_name'] . ' ' . $resetUser['last_name'])) ?>.</p>
                <p class="small text-muted mb-4">Verified email: <strong><?= e($resetUser['email']) ?></strong></p>

                <!-- Reset password form -->
                <form method="post">

                    <!-- New password field -->
                    <label class="form-label">New password</label>
                    <input type="password" name="password" class="form-control form-control-lg mb-3" placeholder="New password" required />

                    <!-- Confirm password field -->
                    <label class="form-label">Confirm password</label>
                    <input type="password" name="confirm_password" class="form-control form-control-lg mb-4" placeholder="Confirm new password" required />

                    <button type="submit" class="btn btn-success rounded-pill w-100 py-3 fw-semibold">Reset Password</button>
                </form>

                <!-- Recovery links -->
                <section class="d-flex flex-column flex-sm-row justify-content-between gap-2 mt-4">
                    <a href="login.php" class="text-decoration-none fw-semibold">Back to login</a>
                    <a href="register.php" class="text-decoration-none fw-semibold">Create a new account</a>
                </section>

            </section>
        </section>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>