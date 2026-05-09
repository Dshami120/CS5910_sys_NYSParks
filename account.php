<?php
require 'bootstrap.php';
$user = require_login($db);

// Handle profile updates.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full = post('full_name');
    [$first, $last] = split_name($full);

    if (!$first || !$last) {
        flash_set('error', 'Enter first and last name.');
        redirect('account.php');
    }

    $profileImageUrl = post('profile_image_url') ?: null;
    $profileImageScheme = strtolower((string) parse_url((string) $profileImageUrl, PHP_URL_SCHEME));

    if ($profileImageUrl && (!filter_var($profileImageUrl, FILTER_VALIDATE_URL) || !in_array($profileImageScheme, ['http', 'https'], true))) {
        flash_set('error', 'Enter a valid profile image URL or leave it blank.');
        redirect('account.php');
    }

    // Save editable profile fields only.
    $db->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, organization=?, profile_image_url=?, notes=? WHERE id=?")
        ->execute([$first, $last, post('phone') ?: null, post('organization') ?: null, $profileImageUrl, post('notes') ?: null, $user['id']]);

    flash_set('success', 'Profile updated.');
    redirect('account.php');
}

// Reload user after save.
$user = current_user($db);

// Set dashboard target by role.
$dashboardPath = match ($user['role']) {
    'admin' => 'admin-dashboard.php',
    'employee' => 'employee-dashboard.php',
    default => 'client-dashboard.php',
};

// Set dashboard button label by role.
$dashboardLabel = match ($user['role']) {
    'admin' => 'Open Admin Dashboard',
    'employee' => 'Open Employee Dashboard',
    default => 'Open Client Dashboard',
};

// Set primary action target by role.
$actionPath = match ($user['role']) {
    'admin' => 'admin-dashboard.php',
    'employee' => 'employee-pto.php',
    default => 'client-create-event.php',
};

// Set primary action label by role.
$actionLabel = match ($user['role']) {
    'admin' => 'Open Admin Tools',
    'employee' => 'Create PTO Request',
    default => 'Create Event Request',
};

// Build client account stats.
if ($user['role'] === 'client') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE client_id=? AND booking_status='pending'");
    $stmt->execute([$user['id']]);
    $stat1 = ['Open requests', (int) $stmt->fetchColumn(), 'Pending approval from the admin booking queue.'];

    $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE client_id=? AND booking_status IN ('approved','confirmed','completed')");
    $stmt->execute([$user['id']]);
    $stat2 = ['Booking history', (int) $stmt->fetchColumn(), 'Approved, confirmed, and completed bookings tied to this account.'];

    $stat3 = ['Saved parks', (int) $db->query("SELECT COUNT(*) FROM parks WHERE is_featured=1")->fetchColumn(), 'Quick access to favorite parks and event venues.'];

    $detailsTitle = 'Client account details';
    $detailsText = 'Editable front-end profile fields for the future users table binding.';
    $roleText = 'Client account';

// Build employee account stats.
} elseif ($user['role'] === 'employee') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM employee_schedules WHERE employee_id=? AND shift_date >= CURDATE() AND schedule_status <> 'cancelled'");
    $stmt->execute([$user['id']]);
    $stat1 = ['Upcoming shifts', (int) $stmt->fetchColumn(), 'Future scheduled shifts assigned from the admin portal.'];

    $stmt = $db->prepare("SELECT COUNT(*) FROM pto_requests WHERE employee_id=? AND pto_status='pending'");
    $stmt->execute([$user['id']]);
    $stat2 = ['Pending PTO', (int) $stmt->fetchColumn(), 'Time-off requests waiting for admin review.'];

    $stat3 = ['Assigned park', $user['park_name'] ? 1 : 0, 'Primary park or department assignment on file.'];

    $detailsTitle = 'Employee account details';
    $detailsText = 'Editable profile information tied to the shared users table.';
    $roleText = 'Employee account';

// Build admin account stats.
} else {
    $stat1 = ['Managed employees', (int) $db->query("SELECT COUNT(*) FROM users WHERE role='employee' AND account_status='active'")->fetchColumn(), 'Active employee accounts currently managed by admin.'];
    $stat2 = ['Pending approvals', (int) $db->query("SELECT (SELECT COUNT(*) FROM bookings WHERE booking_status='pending') + (SELECT COUNT(*) FROM pto_requests WHERE pto_status='pending')")->fetchColumn(), 'Combined booking and PTO requests waiting in queues.'];
    $stat3 = ['Managed parks', (int) $db->query("SELECT COUNT(*) FROM parks")->fetchColumn(), 'Park records currently available across the site.'];

    $detailsTitle = 'Admin account details';
    $detailsText = 'Shared profile fields for the admin users table record.';
    $roleText = 'Admin account';
}
?>

<?php
$pageTitle = 'NYS Parks - Account';
$bodyPage = 'account';
$extraHead = '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

    <main class="py-5">
        <section class="container">
            <section class="row g-4">

                <!-- Profile summary card -->
                <article class="col-xl-4">
                    <section class="soft-card overflow-hidden h-100">
                        <section class="profile-banner"></section>

                        <section class="p-4">
                            <img src="<?= e(profile_image_url($user)) ?>" alt="<?= e(full_name($user)) ?> profile photo" class="profile-photo-lg" />

                            <h1 class="h3 fw-bold mt-3 mb-1"><?= e(full_name($user)) ?></h1>

                            <p class="text-muted mb-3">
                                <?= e($roleText) ?><?= $user['park_name'] ? ' · ' . e($user['park_name']) : '' ?>
                            </p>

                            <section class="d-grid gap-2">
                                <a href="<?= e($dashboardPath) ?>" class="btn btn-success rounded-pill">
                                    <?= e($dashboardLabel) ?>
                                </a>

                                <a href="<?= e($actionPath) ?>" class="btn btn-outline-dark rounded-pill">
                                    <?= e($actionLabel) ?>
                                </a>
                            </section>
                        </section>
                    </section>
                </article>

                <!-- Account details column -->
                <article class="col-xl-8">

                    <!-- Account stat cards -->
                    <section class="row g-4 mb-4">

                        <!-- First account stat -->
                        <article class="col-md-4">
                            <section class="stats-card h-100">
                                <p class="small text-uppercase text-muted mb-1"><?= e($stat1[0]) ?></p>
                                <h2 class="h3 fw-bold mb-2"><?= e((string) $stat1[1]) ?></h2>
                                <p class="text-muted mb-0"><?= e($stat1[2]) ?></p>
                            </section>
                        </article>

                        <!-- Second account stat -->
                        <article class="col-md-4">
                            <section class="stats-card h-100">
                                <p class="small text-uppercase text-muted mb-1"><?= e($stat2[0]) ?></p>
                                <h2 class="h3 fw-bold mb-2"><?= e((string) $stat2[1]) ?></h2>
                                <p class="text-muted mb-0"><?= e($stat2[2]) ?></p>
                            </section>
                        </article>

                        <!-- Third account stat -->
                        <article class="col-md-4">
                            <section class="stats-card h-100">
                                <p class="small text-uppercase text-muted mb-1"><?= e($stat3[0]) ?></p>
                                <h2 class="h3 fw-bold mb-2"><?= e((string) $stat3[1]) ?></h2>
                                <p class="text-muted mb-0"><?= e($stat3[2]) ?></p>
                            </section>
                        </article>
                    </section>

                    <!-- Editable account details -->
                    <section class="list-shell">

                        <!-- Details heading -->
                        <section class="p-4 border-bottom">
                            <h2 class="h5 fw-bold mb-1"><?= e($detailsTitle) ?></h2>
                            <p class="text-muted mb-0"><?= e($detailsText) ?></p>
                        </section>

                        <!-- Profile form -->
                        <section class="p-4">

                            <!-- Flash message -->
                            <?php if ($flash): ?>
                                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>">
                                    <?= e($flash['message']) ?>
                                </div>
                            <?php endif; ?>

                            <form method="post">
                                <section class="row g-3">

                                    <!-- Full name field -->
                                    <article class="col-md-6">
                                        <label class="form-label">Full name</label>
                                        <input class="form-control" name="full_name" value="<?= e(full_name($user)) ?>" />
                                    </article>

                                    <!-- Email field -->
                                    <article class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input class="form-control" value="<?= e($user['email']) ?>" readonly />
                                    </article>

                                    <!-- Phone field -->
                                    <article class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input class="form-control" name="phone" value="<?= e((string) $user['phone']) ?>" />
                                    </article>

                                    <!-- Organization field -->
                                    <article class="col-md-6">
                                        <label class="form-label">Organization</label>
                                        <input class="form-control" name="organization" value="<?= e((string) $user['organization']) ?>" />
                                    </article>

                                    <!-- Profile photo URL field -->
                                    <article class="col-12">
                                        <label class="form-label">Profile photo URL</label>

                                        <section class="input-group">
                                            <input class="form-control" name="profile_image_url" value="<?= e((string) ($user['profile_image_url'] ?? '')) ?>" placeholder="https://example.com/profile-photo.jpg" />
                                            <a href="<?= e(profile_image_url($user)) ?>" class="btn btn-outline-dark" target="_blank" rel="noopener">View Photo</a>
                                        </section>

                                        <p class="small text-muted mb-0 mt-1">Paste an online image URL, or leave blank to use the default avatar.</p>
                                    </article>

                                    <!-- Notes field -->
                                    <article class="col-12">
                                        <label class="form-label">Notes</label>
                                        <textarea rows="4" class="form-control" name="notes"><?= e((string) $user['notes']) ?></textarea>
                                    </article>
                                </section>

                                <!-- Form buttons -->
                                <section class="d-flex flex-wrap gap-2 mt-4">
                                    <button type="submit" class="btn btn-success rounded-pill px-4">Save Changes</button>
                                    <a href="<?= e($actionPath) ?>" class="btn btn-outline-dark rounded-pill px-4"><?= e($actionLabel) ?></a>
                                </section>
                            </form>
                        </section>
                    </section>
                </article>
            </section>
        </section>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>