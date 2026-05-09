<?php
require 'bootstrap.php';

// Read safe return path.
$next = (string)($_GET['next'] ?? $_POST['next'] ?? '');

// Block external redirect targets.
if ($next !== '' && (preg_match('/^[a-z][a-z0-9+.-]*:/i', $next) || str_starts_with($next, '//'))) {
    $next = '';
}

// Redirect already logged-in users.
if ($currentUser) {
    if ($next !== '') {
        redirect($next);
    }

    redirect($currentUser['role'] === 'admin' ? 'admin-dashboard.php' : ($currentUser['role'] === 'employee' ? 'employee-dashboard.php' : 'client-dashboard.php'));
}

// Handle login form submit.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(post('email'));
    $password = post('password');

    // Load active user by email.
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND account_status='active' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify credentials and start session.
    if ($user && password_verify($password, $user['password_hash'])) {
        login_user($user);

        $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$user['id']]);

        if ($next !== '') {
            redirect($next);
        }

        redirect($user['role'] === 'admin' ? 'admin-dashboard.php' : ($user['role'] === 'employee' ? 'employee-dashboard.php' : 'client-dashboard.php'));
    }

    flash_set('error', 'Invalid email or password.');
    redirect('login.php');
}
?>

<?php
$pageTitle = 'NYS Parks - Login';
$bodyPage = 'login';
$extraHead = '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

    <main class="auth-shell d-flex align-items-center">
        <section class="container py-5">
            <section class="auth-panel p-4 p-lg-5">

                <!-- Flash message -->
                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> mb-4">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <!-- Login heading -->
                <header class="text-center mb-4">
                    <p class="section-kicker mb-2">Secure access</p>
                    <h1 class="h2 fw-bold mb-2">Log in to your account</h1>
                    <p class="text-muted mb-0">Clients, employees, and admins can all start here.</p>
                </header>

                <!-- Login form -->
                <form method="post">

                    <!-- Return path field -->
                    <?php if ($next !== ''): ?>
                        <input type="hidden" name="next" value="<?= e($next) ?>">
                    <?php endif; ?>

                    <label class="form-label">Email / Username</label>
                    <input type="email" name="email" class="form-control form-control-lg mb-3" placeholder="you@example.com" required />

                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control form-control-lg mb-4" placeholder="Enter password" required />

                    <button type="submit" class="btn btn-success w-100 rounded-pill py-3 fw-semibold mb-3">
                        Sign In
                    </button>

                    <p class="text-center mb-2">
                        <a href="forgot-password.php" class="map-link">Forgot Password?</a>
                    </p>

                    <p class="text-center mb-0">
                        New here? <a href="register.php" class="map-link">Create an account</a>
                    </p>
                </form>
            </section>
        </section>
    </main>

    <!-- Bootstrap JavaScript bundle -->
    <!-- Shared site JavaScript -->
<?php include __DIR__ . '/includes/footer.php'; ?>