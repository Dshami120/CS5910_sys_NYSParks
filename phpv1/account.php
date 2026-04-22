<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'My Account | ' . SITE_NAME;
$activePage = '';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin();

$user = currentUser();
$flash = getFlashMessage();
$role = $user['role'] ?? 'client';

$roleMeta = [
    'client' => [
        'title' => 'Client Account Hub',
        'description' => 'Your booking dashboard, event request tools, and payment pages will connect here next.',
        'icon' => 'bi-person-heart',
        'badge' => 'Client',
        'next_steps' => ['View future bookings', 'Create an event request', 'Pay for approved reservations'],
    ],
    'employee' => [
        'title' => 'Employee Portal Hub',
        'description' => 'Your work schedule, PTO page, and staff tools will connect here next.',
        'icon' => 'bi-briefcase-fill',
        'badge' => 'Employee',
        'next_steps' => ['View assigned schedule', 'Submit PTO request', 'See employee updates'],
    ],
    'admin' => [
        'title' => 'Admin Portal Hub',
        'description' => 'Your admin dashboard, employee tools, event review queue, and approval features will connect here next.',
        'icon' => 'bi-shield-lock-fill',
        'badge' => 'Admin',
        'next_steps' => ['Review booking requests', 'Manage employee accounts', 'Approve PTO and event workflows'],
    ],
];

$currentRoleMeta = $roleMeta[$role] ?? $roleMeta['client'];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="account-page py-5">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
        <?php endif; ?>

        <section class="account-hero rounded-4 p-4 p-md-5 mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="badge rounded-pill text-bg-light text-success px-3 py-2 mb-3"><?php echo e($currentRoleMeta['badge']); ?></span>
                    <h1 class="display-6 fw-bold mb-3"><?php echo e($currentRoleMeta['title']); ?></h1>
                    <p class="lead mb-3"><?php echo e($currentRoleMeta['description']); ?></p>
                    <p class="mb-0 text-white-50">
                        Signed in as <strong class="text-white"><?php echo e(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></strong>
                        (<?php echo e($user['email'] ?? ''); ?>)
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="account-icon-circle ms-lg-auto">
                        <i class="bi <?php echo e($currentRoleMeta['icon']); ?>"></i>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Account Summary</h2>
                        <ul class="list-unstyled mb-0 account-summary-list">
                            <li><strong>Name:</strong> <?php echo e(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></li>
                            <li><strong>Email:</strong> <?php echo e($user['email'] ?? ''); ?></li>
                            <li><strong>Role:</strong> <?php echo e(ucfirst($role)); ?></li>
                            <li><strong>Status:</strong> Signed In</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Next Portal Features</h2>
                        <div class="row g-3">
                            <?php foreach ($currentRoleMeta['next_steps'] as $step): ?>
                                <div class="col-md-4">
                                    <div class="mini-feature-card h-100">
                                        <i class="bi bi-check2-square"></i>
                                        <p class="mb-0"><?php echo e($step); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="alert alert-info mt-4 mb-0">
                            This page acts as a temporary landing spot until the dedicated client, employee, and admin portals are built in the next phase.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
