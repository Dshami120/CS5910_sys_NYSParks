<?php
// Load project setup.
require 'bootstrap.php';

$registrationSuccess = false;

// Handle submitted form actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = post('full_name');
    [$first, $last] = split_name($fullName);
    $email = strtolower(post('email'));
    $phone = post('phone');
    $birthdate = post('birthdate');
    $organization = post('organization');
    $password = post('password');

    if (!$first || !$last) {
        flash_set('error', 'Please enter your full name.');
        redirect('register.php');
    }

    if (!valid_email($email)) {
        flash_set('error', 'Enter a valid email address.');
        redirect('register.php');
    }

    if (strlen($password) < 8) {
        flash_set('error', 'Password must be at least 8 characters.');
        redirect('register.php');
    }

    $exists = $db->prepare("SELECT id FROM users WHERE email = ?");
    $exists->execute([$email]);

    if ($exists->fetch()) {
        flash_set('error', 'That email is already registered.');
        redirect('register.php');
    }

    $db->prepare("INSERT INTO users (first_name,last_name,email,password_hash,role,phone,birthdate,organization,account_status) VALUES (?,?,?,?, 'client',?,?,?, 'active')")
        ->execute([$first,$last,$email,password_hash($password,PASSWORD_DEFAULT),$phone ?: null,$birthdate ?: null,$organization ?: null]);

    $newUserId = (int) $db->lastInsertId();

    $newUserStmt = $db->prepare("SELECT * FROM users WHERE id = ? AND account_status='active' LIMIT 1");
    $newUserStmt->execute([$newUserId]);
    $newUser = $newUserStmt->fetch();

    if ($newUser) {
        login_user($newUser);
        $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$newUser['id']]);

        flash_set('success', 'Account created successfully.');
        $registrationSuccess = true;
    } else {
        flash_set('error', 'Account was created, but login could not be completed. Please try logging in.');
        redirect('login.php');
    }
}
?>
<?php
// Set page metadata.
$pageTitle = 'NYS Parks - Register';
$bodyPage = 'register';
$extraHead = $registrationSuccess ? '<meta http-equiv="refresh" content="2;url=client-dashboard.php">' : '';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="auth-shell d-flex align-items-center">
    <section class="container py-5">
        <section class="auth-panel p-4 p-lg-5">

            <?php if ($flash && !$registrationSuccess): ?>
                <div class="alert alert-<?= $flash['type']==='error'?'danger':'success' ?> mb-4">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($registrationSuccess): ?>
                <div class="alert alert-success mb-4">
                    Account created successfully. Redirecting to your dashboard...
                </div>

                <div class="text-center">
                    <a href="client-dashboard.php" class="btn btn-success rounded-pill px-4">
                        Continue to Client Dashboard
                    </a>
                </div>
            <?php else: ?>

                <header class="text-center mb-4">
                    <p class="section-kicker mb-2">Client registration</p>
                    <h1 class="h2 fw-bold mb-2">Create your account</h1>
                    <p class="text-muted mb-0">Simple starter form for the future client registration workflow.</p>
                </header>

                <form method="post">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control form-control-lg mb-3" placeholder="Your full name" required />

                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control form-control-lg mb-3" placeholder="you@example.com" required />

                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control form-control-lg mb-3" placeholder="(555) 123-4567" />

                    <label class="form-label">Birthdate</label>
                    <input type="date" name="birthdate" class="form-control form-control-lg mb-3" />

                    <label class="form-label">Organization</label>
                    <input type="text" name="organization" class="form-control form-control-lg mb-3" placeholder="Community group, company, or school" />

                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control form-control-lg mb-4" placeholder="Create password" required />

                    <button type="submit" class="btn btn-success w-100 rounded-pill py-3 fw-semibold mb-3">
                        Register
                    </button>

                    <p class="text-center mb-0">Already have an account? <a href="login.php" class="map-link">Log in</a></p>
                </form>

            <?php endif; ?>
        </section>
    </section>
</main>
<!-- Bootstrap JavaScript bundle -->
<!-- Shared site JavaScript -->
<?php include __DIR__ . '/includes/footer.php'; ?>
