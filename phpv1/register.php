<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Create Account | ' . SITE_NAME;
$activePage = '';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (isLoggedIn()) {
    redirectTo('account.php');
}

$errors = [];
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['first_name'] = trim($_POST['first_name'] ?? '');
    $formData['last_name'] = trim($_POST['last_name'] ?? '');
    $formData['email'] = strtolower(trim($_POST['email'] ?? ''));
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($formData['first_name'] === '') {
        $errors[] = 'First name is required.';
    }
    if ($formData['last_name'] === '') {
        $errors[] = 'Last name is required.';
    }
    if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }

    if (empty($errors)) {
        $result = createClientUser(
            $formData['first_name'],
            $formData['last_name'],
            $formData['email'],
            $password,
            $formData['phone'] !== '' ? $formData['phone'] : null
        );

        if ($result['success']) {
            setFlashMessage('success', $result['message']);
            redirectTo('login.php');
        }

        $errors[] = $result['message'];
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
$flash = getFlashMessage();
?>
<main class="auth-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">
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
                        <span class="section-kicker">Client Registration</span>
                        <h1 class="section-title mb-3">Create a public client account</h1>
                        <p class="text-muted mb-4">
                            Guests can register here to prepare for future booking, payment, and dashboard features. Employee and admin accounts should still be created by administrators later.
                        </p>

                        <form method="post" action="register.php" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label fw-semibold">First name</label>
                                    <input type="text" class="form-control form-control-lg" id="first_name" name="first_name" value="<?php echo e($formData['first_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label fw-semibold">Last name</label>
                                    <input type="text" class="form-control form-control-lg" id="last_name" name="last_name" value="<?php echo e($formData['last_name']); ?>" required>
                                </div>
                                <div class="col-md-8">
                                    <label for="email" class="form-label fw-semibold">Email address</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" value="<?php echo e($formData['email']); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="phone" class="form-label fw-semibold">Phone</label>
                                    <input type="text" class="form-control form-control-lg" id="phone" name="phone" value="<?php echo e($formData['phone']); ?>" placeholder="Optional">
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-semibold">Password</label>
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label fw-semibold">Confirm password</label>
                                    <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>

                            <div class="form-text mt-3 mb-4">
                                For this capstone starter build, registration creates only <strong>client</strong> accounts. Employee and admin accounts will be handled inside the staff portal later.
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100">Create Account</button>
                        </form>

                        <p class="mt-4 mb-0 text-muted">
                            Already have an account?
                            <a href="login.php" class="fw-semibold text-success text-decoration-none">Log in here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
