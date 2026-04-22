<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Employee Dashboard | ' . SITE_NAME;
$activePage = '';
$employeeSection = 'dashboard';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin(['employee']);

$user = currentUser();
$flash = getFlashMessage();
$schedules = getEmployeeSchedules($user);
$ptoRequests = getEmployeePtoRequests($user);

$today = date('Y-m-d');
$upcomingSchedules = array_values(array_filter($schedules, function ($schedule) use ($today) {
    return ($schedule['shift_date'] ?? '9999-12-31') >= $today;
}));

$nextShift = $upcomingSchedules[0] ?? null;
$pendingPtoCount = count(array_filter($ptoRequests, fn($request) => strtolower($request['pto_status'] ?? 'pending') === 'pending'));
$approvedPtoCount = count(array_filter($ptoRequests, fn($request) => strtolower($request['pto_status'] ?? '') === 'approved'));

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="employee-portal-page py-5">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo e($flash['type']); ?> mb-4"><?php echo e($flash['message']); ?></div>
        <?php endif; ?>

        <?php require __DIR__ . '/includes/employee-nav.php'; ?>

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <span class="section-kicker">Employee portal</span>
                        <h1 class="h2 fw-bold mb-2">Welcome back, <?php echo e($user['first_name'] ?? 'Employee'); ?></h1>
                        <p class="text-muted mb-0">This page only covers the two employee features you chose for the capstone: viewing your schedule and submitting PTO requests.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="employee-pto.php" class="btn btn-success btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>Apply for PTO
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="mini-stat-card h-100">
                    <div class="mini-stat-label">Upcoming shifts</div>
                    <div class="mini-stat-number"><?php echo e((string) count($upcomingSchedules)); ?></div>
                    <p class="text-muted small mb-0">All future schedule assignments currently available.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mini-stat-card h-100">
                    <div class="mini-stat-label">Pending PTO</div>
                    <div class="mini-stat-number"><?php echo e((string) $pendingPtoCount); ?></div>
                    <p class="text-muted small mb-0">Requests still waiting for admin review.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mini-stat-card h-100">
                    <div class="mini-stat-label">Approved PTO</div>
                    <div class="mini-stat-number"><?php echo e((string) $approvedPtoCount); ?></div>
                    <p class="text-muted small mb-0">Approved time-off requests in your record.</p>
                </div>
            </div>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm employee-summary-card">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Next scheduled shift</h2>
                        <?php if ($nextShift): ?>
                            <div class="employee-next-shift">
                                <div class="employee-next-shift-date mb-3"><?php echo e(date('l, M d, Y', strtotime($nextShift['shift_date']))); ?></div>
                                <div class="fw-semibold mb-1"><?php echo e($nextShift['park_name'] ?? 'NYS Park'); ?></div>
                                <div class="text-muted mb-2"><?php echo e(date('g:i A', strtotime($nextShift['start_time']))); ?> - <?php echo e(date('g:i A', strtotime($nextShift['end_time']))); ?></div>
                                <span class="badge text-bg-<?php echo e(getAdminScheduleStatusBadgeClass($nextShift['schedule_status'] ?? 'scheduled')); ?> mb-3"><?php echo e(ucfirst($nextShift['schedule_status'] ?? 'scheduled')); ?></span>
                                <p class="small text-muted mb-0"><?php echo e($nextShift['notes'] ?? 'No shift notes were provided.'); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light border mb-0">No future shifts are currently assigned.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 fw-bold mb-0">My schedule</h2>
                            <span class="badge text-bg-light"><?php echo e((string) count($schedules)); ?> shift<?php echo count($schedules) === 1 ? '' : 's'; ?></span>
                        </div>

                        <?php if (empty($schedules)): ?>
                            <div class="alert alert-light border mb-0">No shift assignments are available yet.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle employee-data-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Park</th>
                                            <th>Shift</th>
                                            <th>Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($schedules as $schedule): ?>
                                            <tr>
                                                <td class="fw-semibold"><?php echo e(date('M d, Y', strtotime($schedule['shift_date']))); ?></td>
                                                <td><?php echo e($schedule['park_name'] ?? 'NYS Park'); ?></td>
                                                <td><?php echo e(date('g:i A', strtotime($schedule['start_time']))); ?> - <?php echo e(date('g:i A', strtotime($schedule['end_time']))); ?></td>
                                                <td><span class="badge text-bg-<?php echo e(getAdminScheduleStatusBadgeClass($schedule['schedule_status'] ?? 'scheduled')); ?>"><?php echo e(ucfirst($schedule['schedule_status'] ?? 'scheduled')); ?></span></td>
                                                <td class="small text-muted"><?php echo e($schedule['notes'] ?? ''); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
