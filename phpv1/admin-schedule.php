<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Admin Employee Schedule | ' . SITE_NAME;
$activePage = '';
$adminSection = 'schedule';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = createAdminScheduleEntry(currentUser(), $_POST);
    setFlashMessage($result['success'] ? 'success' : 'danger', $result['message']);
    redirectTo('admin-schedule.php');
}

$flash = getFlashMessage();
$employees = getAdminEmployeeDirectory();
$parks = getBookableParks();
$schedules = getAdminSchedules();

$parkFilter = trim((string) ($_GET['park'] ?? 'all'));
$filteredSchedules = array_values(array_filter($schedules, function ($schedule) use ($parkFilter) {
    if ($parkFilter === 'all') {
        return true;
    }
    return ($schedule['park_name'] ?? '') === $parkFilter;
}));

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
                <span class="section-kicker">Employee management</span>
                <h1 class="h2 fw-bold mb-2">Schedule employees</h1>
                <p class="text-muted mb-0">Keep shift creation on its own screen so the admin dashboard stays readable while staff scheduling remains easy to manage.</p>
            </div>
        </section>

        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-lg-top" style="top: 110px;">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Create schedule assignment</h2>
                        <form method="post" class="row g-3">
                            <div class="col-12">
                                <label for="employee_id" class="form-label fw-semibold">Employee</label>
                                <select class="form-select" id="employee_id" name="employee_id" required>
                                    <option value="">Choose employee</option>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?php echo e((string) $employee['user_id']); ?>">
                                            <?php echo e(trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''))); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="park_id" class="form-label fw-semibold">Park</label>
                                <select class="form-select" id="park_id" name="park_id" required>
                                    <option value="">Choose park</option>
                                    <?php foreach ($parks as $park): ?>
                                        <option value="<?php echo e((string) $park['park_id']); ?>"><?php echo e($park['park_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="shift_date" class="form-label fw-semibold">Shift date</label>
                                <input type="date" class="form-control" id="shift_date" name="shift_date" min="<?php echo e(date('Y-m-d')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="start_time" class="form-label fw-semibold">Start time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_time" class="form-label fw-semibold">End time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                            <div class="col-12">
                                <label for="notes" class="form-label fw-semibold">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Optional shift notes or responsibilities."></textarea>
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-success">Save Schedule</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <form method="get" class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <label for="park" class="form-label fw-semibold">Filter by park</label>
                                <select class="form-select" id="park" name="park">
                                    <option value="all">All parks</option>
                                    <?php foreach ($parks as $park): ?>
                                        <option value="<?php echo e($park['park_name']); ?>" <?php echo $parkFilter === ($park['park_name'] ?? '') ? 'selected' : ''; ?>>
                                            <?php echo e($park['park_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-success w-100">Apply</button>
                                <a href="admin-schedule.php" class="btn btn-outline-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h5 fw-bold mb-0">Upcoming employee schedule</h2>
                            <span class="badge text-bg-light"><?php echo e((string) count($filteredSchedules)); ?> shift<?php echo count($filteredSchedules) === 1 ? '' : 's'; ?></span>
                        </div>

                        <?php if (empty($filteredSchedules)): ?>
                            <div class="alert alert-light border mb-0">No schedule entries match the current filter.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle admin-data-table">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Park</th>
                                            <th>Date</th>
                                            <th>Shift</th>
                                            <th>Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($filteredSchedules as $schedule): ?>
                                            <tr>
                                                <td class="fw-semibold"><?php echo e($schedule['employee_name'] ?? 'Employee'); ?></td>
                                                <td><?php echo e($schedule['park_name'] ?? 'NYS Park'); ?></td>
                                                <td><?php echo e(date('M d, Y', strtotime($schedule['shift_date']))); ?></td>
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
