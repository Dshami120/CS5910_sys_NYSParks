<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Log In | ' . SITE_NAME;
$activePage = '';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (isLoggedIn()) {
    redirectTo('account.php');
}

$errors = [];
$email = '';
$usedDemoMode = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Please enter your password.';
    }

    if (empty($errors)) {
        $user = findUserByEmail($email);

        if (!$user) {
            $errors[] = 'No account was found with that email address.';
        } elseif (($user['account_status'] ?? 'active') !== 'active') {
            $errors[] = 'This account is not active right now. Please contact an administrator.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            $errors[] = 'Incorrect password. Please try again.';
        } else {
            loginUserSession($user);

            if (!databaseSupportsLiveUsers()) {
                $usedDemoMode = true;
                setFlashMessage('info', 'You are using a demo account because the live Users table is not connected yet.');
            } else {
                setFlashMessage('success', 'Welcome back, ' . ($user['first_name'] ?? 'User') . '!');
            }

            redirectTo('account.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
$flash = getFlashMessage();
?>
<main class="auth-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-xl-5">
                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Please fix the following:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow auth-card">
                    <div class="card-body p-4 p-md-5">
                        <span class="section-kicker">Account Access</span>
                        <h1 class="section-title mb-3">Log in to your NYS Parks account</h1>
                        <p class="text-muted mb-4">
                            Clients, employees, and administrators all sign in here. The full portals will be added next, but the login system is ready now.
                        </p>

                        <form method="post" action="login.php" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email address</label>
                                <input
                                    type="email"
                                    class="form-control form-control-lg"
                                    id="email"
                                    name="email"
                                    value="<?php echo e($email); ?>"
                                    placeholder="name@example.com"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <input
                                    type="password"
                                    class="form-control form-control-lg"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    required
                                >
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100">Log In</button>
                        </form>

                        <div class="auth-divider my-4"><span>Capstone Demo Accounts</span></div>
                        <div class="small text-muted auth-demo-box">
                            <p class="mb-2"><strong>Use these only if your Users table is not ready yet:</strong></p>
                            <ul class="mb-0">
                                <li>Client: <code>client.demo@nysparks.local</code> / <code>Password123!</code></li>
                                <li>Employee: <code>employee.demo@nysparks.local</code> / <code>Password123!</code></li>
                                <li>Admin: <code>admin.demo@nysparks.local</code> / <code>Password123!</code></li>
                            </ul>
                        </div>

                        <p class="mt-4 mb-0 text-muted">
                            Need a client account?
                            <a href="register.php" class="fw-semibold text-success text-decoration-none">Create one here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
