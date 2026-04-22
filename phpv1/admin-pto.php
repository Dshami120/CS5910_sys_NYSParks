<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Admin PTO Queue | ' . SITE_NAME;
$activePage = '';
$adminSection = 'pto';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin(['admin']);

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ptoId = (int) ($_POST['pto_id'] ?? 0);
    $action = trim((string) ($_POST['pto_action'] ?? ''));

    $result = updateAdminPtoStatus($ptoId, $action, (int) ($user['user_id'] ?? 0));
    setFlashMessage($result['success'] ? 'success' : 'danger', $result['message']);
    redirectTo('admin-pto.php');
}

$flash = getFlashMessage();
$ptoRequests = getAdminPtoRequests();
$statusFilter = strtolower(trim((string) ($_GET['status'] ?? 'all')));
$allowedStatuses = ['all', 'pending', 'approved', 'denied'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

$filteredRequests = array_values(array_filter($ptoRequests, function ($request) use ($statusFilter) {
    $status = strtolower($request['pto_status'] ?? 'pending');
    return $statusFilter === 'all' || $status === $statusFilter;
}));

$pendingCount = count(array_filter($ptoRequests, fn($item) => strtolower($item['pto_status'] ?? '') === 'pending'));
$approvedCount = count(array_filter($ptoRequests, fn($item) => strtolower($item['pto_status'] ?? '') === 'approved'));
$deniedCount = count(array_filter($ptoRequests, fn($item) => strtolower($item['pto_status'] ?? '') === 'denied'));

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="admin-portal-page py-5">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo e($flash['type']); ?> mb-4"><?php echo e($flash['message']); ?></div>
        <?php endif; ?>

        <?php require __DIR__ . '/includes/admin-nav.php'; ?>

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-8">
                        <span class="section-kicker">Employee management</span>
                        <h1 class="h2 fw-bold mb-2">Review PTO requests</h1>
                        <p class="text-muted mb-0">This screen keeps PTO approvals separate from the main dashboard while still giving you the full queue in one place.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="row g-3">
                            <div class="col-4"><div class="mini-stat-card text-center"><div class="mini-stat-number"><?php echo e((string) $pendingCount); ?></div><div class="mini-stat-label">Pending</div></div></div>
                            <div class="col-4"><div class="mini-stat-card text-center"><div class="mini-stat-number"><?php echo e((string) $approvedCount); ?></div><div class="mini-stat-label">Approved</div></div></div>
                            <div class="col-4"><div class="mini-stat-card text-center"><div class="mini-stat-number"><?php echo e((string) $deniedCount); ?></div><div class="mini-stat-label">Denied</div></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="status" class="form-label fw-semibold">Filter by PTO status</label>
                        <select class="form-select" id="status" name="status">
                            <?php foreach ($allowedStatuses as $statusOption): ?>
                                <option value="<?php echo e($statusOption); ?>" <?php echo $statusFilter === $statusOption ? 'selected' : ''; ?>><?php echo e(ucfirst($statusOption)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-success w-100">Apply</button>
                        <a href="admin-pto.php" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </section>

        <section class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 fw-bold mb-0">PTO approval queue</h2>
                    <span class="badge text-bg-light"><?php echo e((string) count($filteredRequests)); ?> request<?php echo count($filteredRequests) === 1 ? '' : 's'; ?></span>
                </div>

                <?php if (empty($filteredRequests)): ?>
                    <div class="alert alert-light border mb-0">No PTO requests match the current filter.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle admin-data-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Dates</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($filteredRequests as $request): ?>
                                    <?php $status = strtolower($request['pto_status'] ?? 'pending'); ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo e($request['employee_name'] ?? 'Employee'); ?></td>
                                        <td>
                                            <div><?php echo e(date('M d, Y', strtotime($request['start_date']))); ?></div>
                                            <div class="small text-muted">to <?php echo e(date('M d, Y', strtotime($request['end_date']))); ?></div>
                                        </td>
                                        <td class="small text-muted"><?php echo e($request['reason'] ?? 'No reason provided.'); ?></td>
                                        <td><span class="badge text-bg-<?php echo e(getPtoStatusBadgeClass($status)); ?>"><?php echo e(ucfirst($status)); ?></span></td>
                                        <td><?php echo e(date('M d, Y', strtotime($request['created_at'] ?? 'now'))); ?></td>
                                        <td>
                                            <?php if ($status === 'pending'): ?>
                                                <div class="d-flex flex-column gap-2">
                                                    <form method="post">
                                                        <input type="hidden" name="pto_id" value="<?php echo e((string) $request['pto_id']); ?>">
                                                        <input type="hidden" name="pto_action" value="approved">
                                                        <button type="submit" class="btn btn-sm btn-success w-100">Approve</button>
                                                    </form>
                                                    <form method="post">
                                                        <input type="hidden" name="pto_id" value="<?php echo e((string) $request['pto_id']); ?>">
                                                        <input type="hidden" name="pto_action" value="denied">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Deny</button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <div class="small text-muted">Reviewed <?php echo e(!empty($request['decision_date']) ? date('M d, Y', strtotime($request['decision_date'])) : 'previously'); ?></div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
