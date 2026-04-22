<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Employee PTO | ' . SITE_NAME;
$activePage = '';
$employeeSection = 'pto';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin(['employee']);

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = createEmployeePtoRequest($user, $_POST);
    setFlashMessage($result['success'] ? 'success' : 'danger', $result['message']);
    redirectTo('employee-pto.php');
}

$flash = getFlashMessage();
$ptoRequests = getEmployeePtoRequests($user);

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
                        <h1 class="h2 fw-bold mb-2">Apply for PTO</h1>
                        <p class="text-muted mb-0">Use this simple form to submit time-off dates. Admins can review the request later from their PTO screen.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="employee-dashboard.php" class="btn btn-outline-success btn-lg">
                            <i class="bi bi-arrow-left me-2"></i>Back to Schedule
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4 align-items-start">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm sticky-lg-top" style="top: 110px;">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Submit PTO request</h2>
                        <form method="post" class="row g-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label fw-semibold">Start date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" min="<?php echo e(date('Y-m-d')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label fw-semibold">End date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" min="<?php echo e(date('Y-m-d')); ?>" required>
                            </div>
                            <div class="col-12">
                                <label for="reason" class="form-label fw-semibold">Reason or notes</label>
                                <textarea class="form-control" id="reason" name="reason" rows="5" placeholder="Optional details for the admin reviewer."></textarea>
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-success">Submit PTO Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 fw-bold mb-0">My PTO requests</h2>
                            <span class="badge text-bg-light"><?php echo e((string) count($ptoRequests)); ?> request<?php echo count($ptoRequests) === 1 ? '' : 's'; ?></span>
                        </div>

                        <?php if (empty($ptoRequests)): ?>
                            <div class="alert alert-light border mb-0">You have not submitted any PTO requests yet.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle employee-data-table">
                                    <thead>
                                        <tr>
                                            <th>Dates</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th>Submitted</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ptoRequests as $request): ?>
                                            <?php $status = strtolower($request['pto_status'] ?? 'pending'); ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold"><?php echo e(date('M d, Y', strtotime($request['start_date']))); ?></div>
                                                    <div class="small text-muted">to <?php echo e(date('M d, Y', strtotime($request['end_date']))); ?></div>
                                                </td>
                                                <td class="small text-muted"><?php echo e($request['reason'] ?? 'No reason provided.'); ?></td>
                                                <td><span class="badge text-bg-<?php echo e(getPtoStatusBadgeClass($status)); ?>"><?php echo e(ucfirst($status)); ?></span></td>
                                                <td><?php echo e(date('M d, Y', strtotime($request['created_at'] ?? 'now'))); ?></td>
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
