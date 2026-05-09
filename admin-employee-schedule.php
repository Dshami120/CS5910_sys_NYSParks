<?php
// Load setup and require admin access.
require 'bootstrap.php';
$user = require_role($db, 'admin');

// Build admin schedule URL with filters.
function schedule_admin_path(array $params = []): string
{
    $scheduleValue = array_key_exists('schedule_id', $params) ? $params['schedule_id'] : get('schedule_id');
    $statusValue = $params['schedule_filter'] ?? post('schedule_filter', get('schedule_filter'));

    $query = array_filter([
        'schedule_id' => $scheduleValue,
        'schedule_filter' => $statusValue,
    ], fn($value) => $value !== null && $value !== '' && $value !== 'all');

    return 'admin-employee-schedule.php' . ($query ? '?' . http_build_query($query) : '');
}

// Load schedule status labels.
$scheduleStatusLabels = SCHEDULE_STATUS_LABELS;

// Handle schedule form actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');

    // Cancel selected schedule.
    if ($action === 'cancel_schedule') {
        $scheduleId = (int) post('schedule_id');

        if ($scheduleId <= 0) {
            flash_set('error', 'Select a shift to cancel.');
            redirect(schedule_admin_path(['schedule_id' => null]));
        }

        $exists = $db->prepare("SELECT s.schedule_status, u.account_status FROM employee_schedules s JOIN users u ON u.id=s.employee_id WHERE s.id=?");
        $exists->execute([$scheduleId]);
        $existingSchedule = $exists->fetch();

        if (!$existingSchedule) {
            flash_set('error', 'Schedule not found.');
            redirect(schedule_admin_path(['schedule_id' => null]));
        }

        if (($existingSchedule['schedule_status'] ?? '') === 'cancelled' || ($existingSchedule['account_status'] ?? '') === 'disabled') {
            flash_set('error', 'Cancelled schedules are view-only. Create a new schedule if needed.');
            redirect(schedule_admin_path(['schedule_id' => $scheduleId]));
        }

        $db->prepare("UPDATE employee_schedules SET schedule_status='cancelled' WHERE id=?")->execute([$scheduleId]);

        flash_set('success', 'Selected shift cancelled.');
        redirect(schedule_admin_path(['schedule_id' => null, 'schedule_filter' => 'cancelled']));
    }

    // Create or update schedule.
    if (in_array($action, ['create_schedule', 'update_schedule'], true)) {
        $scheduleId = (int) post('schedule_id');
        $employeeId = (int) post('employee_id');
        $parkId = (int) post('park_id');
        $date = post('shift_date');
        $start = post('start_time');
        $end = post('end_time');
        $assignment = post('assignment');
        $scheduleStatus = post('schedule_status') ?: 'scheduled';

        if (!isset($scheduleStatusLabels[$scheduleStatus])) {
            $scheduleStatus = 'scheduled';
        }

        $redirectPath = $scheduleId > 0 ? schedule_admin_path(['schedule_id' => $scheduleId]) : schedule_admin_path(['schedule_id' => null]);

        // Validate required schedule fields.
        if (!$employeeId || !$parkId || !$date || !$start || !$end || !$assignment) {
            flash_set('error', 'Complete every schedule field.');
            redirect($redirectPath);
        }

        // Validate shift time order.
        if ($start >= $end) {
            flash_set('error', 'Shift end must be after shift start.');
            redirect($redirectPath);
        }

        // Validate update selection.
        if ($action === 'update_schedule' && $scheduleId <= 0) {
            flash_set('error', 'Select a schedule to update.');
            redirect(schedule_admin_path(['schedule_id' => null]));
        }

        // Check selected schedule before update.
        if ($action === 'update_schedule') {
            $exists = $db->prepare("SELECT s.schedule_status, u.account_status FROM employee_schedules s JOIN users u ON u.id=s.employee_id WHERE s.id=?");
            $exists->execute([$scheduleId]);
            $existingSchedule = $exists->fetch();

            if (!$existingSchedule) {
                flash_set('error', 'Schedule not found.');
                redirect(schedule_admin_path(['schedule_id' => null]));
            }

            if (($existingSchedule['schedule_status'] ?? '') === 'cancelled' || ($existingSchedule['account_status'] ?? '') === 'disabled') {
                flash_set('error', 'Cancelled schedules are view-only. Create a new schedule if needed.');
                redirect(schedule_admin_path(['schedule_id' => $scheduleId]));
            }
        }

        // Confirm employee is active.
        $employeeCheck = $db->prepare("SELECT COUNT(*) FROM users WHERE id=? AND role='employee' AND account_status='active'");
        $employeeCheck->execute([$employeeId]);

        if ((int) $employeeCheck->fetchColumn() === 0) {
            flash_set('error', 'Select an active employee for this schedule.');
            redirect($redirectPath);
        }

        // Prevent overlapping scheduled shifts.
        if ($scheduleStatus === 'scheduled') {
            $overlapSql = "SELECT COUNT(*) FROM employee_schedules WHERE employee_id=? AND shift_date=? AND schedule_status='scheduled' AND NOT (end_time <= ? OR start_time >= ?)";
            $overlapParams = [$employeeId, $date, $start, $end];

            if ($action === 'update_schedule') {
                $overlapSql .= " AND id <> ?";
                $overlapParams[] = $scheduleId;
            }

            $overlap = $db->prepare($overlapSql);
            $overlap->execute($overlapParams);

            if ((int) $overlap->fetchColumn() > 0) {
                flash_set('error', 'That employee already has an overlapping shift.');
                redirect($redirectPath);
            }
        }

        // Insert new schedule.
        if ($action === 'create_schedule') {
            $db->prepare("INSERT INTO employee_schedules (employee_id, park_id, shift_date, start_time, end_time, assignment, schedule_status, notes, created_by) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$employeeId, $parkId, $date, $start, $end, $assignment, $scheduleStatus, post('notes') ?: null, $user['id']]);

            $newId = (int) $db->lastInsertId();

            flash_set('success', 'Schedule created.');
            redirect(schedule_admin_path(['schedule_id' => $newId]));
        }

        // Update existing schedule.
        $db->prepare("UPDATE employee_schedules SET employee_id=?, park_id=?, shift_date=?, start_time=?, end_time=?, assignment=?, schedule_status=?, notes=? WHERE id=?")
            ->execute([$employeeId, $parkId, $date, $start, $end, $assignment, $scheduleStatus, post('notes') ?: null, $scheduleId]);

        flash_set('success', 'Schedule updated.');
        redirect(schedule_admin_path(['schedule_id' => $scheduleId]));
    }
}

// Load selected schedule id.
$selectedScheduleId = (int) get('schedule_id');

// Load employees for schedule form.
$employeeStmt = $db->prepare("SELECT u.*, p.name AS park_name FROM users u LEFT JOIN parks p ON p.id=u.park_id WHERE u.role='employee' AND (u.account_status='active' OR u.id IN (SELECT employee_id FROM employee_schedules WHERE id=?)) ORDER BY u.account_status='disabled', u.first_name, u.last_name");
$employeeStmt->execute([$selectedScheduleId]);
$employees = $employeeStmt->fetchAll();

// Load parks and filter.
$parks = park_options($db);
$scheduleFilter = get('schedule_filter', 'all');

// Build schedule list query.
$scheduleSql = "SELECT s.*, u.account_status, CONCAT(u.first_name, ' ', u.last_name) AS employee_name, p.name AS park_name FROM employee_schedules s JOIN users u ON u.id=s.employee_id JOIN parks p ON p.id=s.park_id";
$scheduleParams = [];

// Apply schedule status filter.
if (isset($scheduleStatusLabels[$scheduleFilter])) {
    $scheduleSql .= " WHERE s.schedule_status=?";
    $scheduleParams[] = $scheduleFilter;
}

$scheduleSql .= " ORDER BY s.schedule_status='cancelled', s.shift_date ASC, s.start_time ASC";

// Load filtered schedules.
$scheduleStmt = $db->prepare($scheduleSql);
$scheduleStmt->execute($scheduleParams);
$schedules = $scheduleStmt->fetchAll();

// Load all schedules for grouped dropdown.
$allSchedules = $db->query("SELECT s.*, u.account_status, CONCAT(u.first_name, ' ', u.last_name) AS employee_name, p.name AS park_name FROM employee_schedules s JOIN users u ON u.id=s.employee_id JOIN parks p ON p.id=s.park_id ORDER BY s.schedule_status='cancelled', s.shift_date ASC, s.start_time ASC")->fetchAll();

// Group schedules by status.
$schedulesByStatus = [];

foreach ($scheduleStatusLabels as $key => $label) {
    $schedulesByStatus[$key] = [];
}

foreach ($allSchedules as $row) {
    $schedulesByStatus[$row['schedule_status']][] = $row;
}

// Load selected schedule details.
$selectedSchedule = null;

if ($selectedScheduleId) {
    $stmt = $db->prepare("SELECT s.*, u.account_status, CONCAT(u.first_name, ' ', u.last_name) AS employee_name, p.name AS park_name FROM employee_schedules s JOIN users u ON u.id=s.employee_id JOIN parks p ON p.id=s.park_id WHERE s.id=?");
    $stmt->execute([$selectedScheduleId]);
    $selectedSchedule = $stmt->fetch();

    if (!$selectedSchedule) {
        flash_set('error', 'Schedule not found.');
        redirect(schedule_admin_path(['schedule_id' => null]));
    }
}

// Determine read-only state.
$scheduleReadOnly = $selectedSchedule && ((($selectedSchedule['schedule_status'] ?? '') === 'cancelled') || (($selectedSchedule['account_status'] ?? '') === 'disabled'));
?>

<?php
// Set page metadata.
$pageTitle = 'NYS Parks - Admin Schedule';
$bodyPage = 'admin-schedule';
$extraHead = '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

    <main class="py-5">
        <section class="container">

            <!-- Flash message -->
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> mb-4">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Page heading and admin navigation -->
            <section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <p class="section-kicker mb-2">Admin portal</p>
                    <h1 class="h2 fw-bold mb-1">Employee Schedule Creation</h1>
                    <p class="text-muted mb-0">Create, review, update, complete, or cancel employee shift assignments by park and date.</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-dark" href="admin-dashboard.php">
                        <i class="bi bi-speedometer2"></i> Admin Dash
                    </a>
                    <a class="btn btn-success" href="admin-employee-schedule.php">
                        <i class="bi bi-calendar3"></i> Employee Schedules
                    </a>
                    <a class="btn btn-outline-dark" href="admin-pto.php">
                        <i class="bi bi-briefcase"></i> PTO Requests
                    </a>
                    <a class="btn btn-outline-dark" href="admin-bookings.php">
                        <i class="bi bi-journal-check"></i> Client Bookings
                    </a>
                    <a class="btn btn-outline-dark" href="admin-news.php">
                        <i class="bi bi-newspaper"></i> News Manager
                    </a>
                    <a class="btn btn-outline-dark" href="admin-employee-accounts.php">
                        <i class="bi bi-people"></i> Employee Accounts
                    </a>
                    <a class="btn btn-outline-dark" href="admin-csv.php">
                        <i class="bi bi-filetype-csv"></i> CSV
                    </a>
                </div>
            </section>

            <!-- Schedule manager layout -->
            <section class="row g-4">

                <!-- Schedule form column -->
                <article class="col-xl-4">
                    <section class="form-panel p-4 h-100">

                        <!-- Form heading -->
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <h2 class="h5 fw-bold mb-0">
                                <?= $scheduleReadOnly ? 'View Schedule' : ($selectedSchedule ? 'Update Current Schedule' : 'Create New Schedule') ?>
                            </h2>
                            <a href="admin-employee-schedule.php" class="btn btn-sm btn-outline-dark">Create New</a>
                        </div>

                        <!-- Schedule filter form -->
                        <form method="get" class="mb-3">
                            <label class="form-label">Filter Schedule List</label>

                            <?php if ($selectedSchedule): ?>
                                <input type="hidden" name="schedule_id" value="<?= (int) $selectedSchedule['id'] ?>" />
                            <?php endif; ?>

                            <select class="form-select" name="schedule_filter" onchange="this.form.submit()">
                                <option value="all" <?= $scheduleFilter === 'all' ? 'selected' : '' ?>>All shifts</option>

                                <?php foreach ($scheduleStatusLabels as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= $scheduleFilter === $key ? 'selected' : '' ?>>
                                        <?= e($label) ?> shifts
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <!-- Existing schedule selector -->
                        <form method="get" class="mb-3">
                            <label class="form-label">Select Existing Schedule</label>
                            <input type="hidden" name="schedule_filter" value="<?= e($scheduleFilter) ?>" />

                            <select class="form-select" name="schedule_id" onchange="this.form.submit()">
                                <option value="">New schedule</option>

                                <?php foreach ($scheduleStatusLabels as $statusKey => $statusLabel): ?>
                                    <?php if (!empty($schedulesByStatus[$statusKey])): ?>
                                        <optgroup label="<?= e($statusLabel) ?> Shifts">
                                            <?php foreach ($schedulesByStatus[$statusKey] as $row): ?>
                                                <option value="<?= (int) $row['id'] ?>" <?= $selectedSchedule && (int) $selectedSchedule['id'] === (int) $row['id'] ? 'selected' : '' ?>>
                                                    <?= e($row['employee_name']) ?> — <?= e(format_date($row['shift_date'])) ?> <?= e(date('g:i A', strtotime($row['start_time']))) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <!-- Read-only warning -->
                        <?php if ($scheduleReadOnly): ?>
                            <div class="alert alert-warning py-2">
                                Cancelled schedules and disabled employee schedules are view-only. Create a new schedule if needed.
                            </div>
                        <?php endif; ?>

                        <!-- Create or update schedule form -->
                        <form method="post">
                            <input type="hidden" name="schedule_id" value="<?= (int) ($selectedSchedule['id'] ?? 0) ?>" />
                            <input type="hidden" name="schedule_filter" value="<?= e($scheduleFilter) ?>" />
                            <input type="hidden" name="action" value="<?= $selectedSchedule ? 'update_schedule' : 'create_schedule' ?>" />

                            <!-- Employee field -->
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select mb-3" required <?= $scheduleReadOnly ? 'disabled' : '' ?>>
                                <option value="">Select employee</option>

                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= (int) $emp['id'] ?>" <?= (int) ($selectedSchedule['employee_id'] ?? 0) === (int) $emp['id'] ? 'selected' : '' ?>>
                                        <?= e(full_name($emp)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Park field -->
                            <label class="form-label">Park</label>
                            <select name="park_id" class="form-select mb-3" required <?= $scheduleReadOnly ? 'disabled' : '' ?>>
                                <option value="">Select park</option>

                                <?php foreach ($parks as $park): ?>
                                    <option value="<?= (int) $park['id'] ?>" <?= (int) ($selectedSchedule['park_id'] ?? 0) === (int) $park['id'] ? 'selected' : '' ?>>
                                        <?= e($park['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Shift date field -->
                            <label class="form-label">Shift date</label>
                            <input type="date" name="shift_date" class="form-control mb-3" value="<?= e((string) ($selectedSchedule['shift_date'] ?? '')) ?>" required <?= $scheduleReadOnly ? 'disabled' : '' ?> />

                            <!-- Shift time fields -->
                            <section class="row g-3">
                                <article class="col-6">
                                    <label class="form-label">Start</label>
                                    <input type="time" name="start_time" class="form-control" value="<?= e(substr((string) ($selectedSchedule['start_time'] ?? ''), 0, 5)) ?>" required <?= $scheduleReadOnly ? 'disabled' : '' ?> />
                                </article>

                                <article class="col-6">
                                    <label class="form-label">End</label>
                                    <input type="time" name="end_time" class="form-control" value="<?= e(substr((string) ($selectedSchedule['end_time'] ?? ''), 0, 5)) ?>" required <?= $scheduleReadOnly ? 'disabled' : '' ?> />
                                </article>
                            </section>

                            <!-- Assignment field -->
                            <label class="form-label mt-3">Role / assignment</label>
                            <input type="text" name="assignment" class="form-control mb-3" placeholder="Events support, ranger duty, maintenance" value="<?= e($selectedSchedule['assignment'] ?? '') ?>" required <?= $scheduleReadOnly ? 'disabled' : '' ?> />

                            <!-- Status field -->
                            <label class="form-label">Schedule Status</label>
                            <select name="schedule_status" class="form-select mb-3" <?= $scheduleReadOnly ? 'disabled' : '' ?>>
                                <?php foreach ($scheduleStatusLabels as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= ($selectedSchedule['schedule_status'] ?? 'scheduled') === $key ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Notes field -->
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control mb-3" rows="3" placeholder="Optional schedule notes" <?= $scheduleReadOnly ? 'disabled' : '' ?>><?= e((string) ($selectedSchedule['notes'] ?? '')) ?></textarea>

                            <!-- Submit schedule form -->
                            <button type="submit" class="btn btn-success rounded-pill w-100 fw-semibold" <?= $scheduleReadOnly ? 'disabled' : '' ?>>
                                <?= $selectedSchedule ? 'Update Current Schedule' : 'Create New Schedule' ?>
                            </button>
                        </form>
                    </section>
                </article>

                <!-- Schedule list column -->
                <article class="col-xl-8">

                    <!-- Schedule list table -->
                    <section class="list-shell mb-4">

                        <!-- List header -->
                        <section class="p-4 border-bottom">
                            <section class="row g-3 align-items-center">
                                <article class="col-md-2 fw-bold">Employee</article>
                                <article class="col-md-2 fw-bold">Park</article>
                                <article class="col-md-2 fw-bold">Date</article>
                                <article class="col-md-2 fw-bold">Hours</article>
                                <article class="col-md-2 fw-bold">Assignment</article>
                                <article class="col-md-1 fw-bold">Status</article>
                                <article class="col-md-1 fw-bold">Actions</article>
                            </section>
                        </section>

                        <!-- Schedule rows -->
                        <?php foreach ($schedules as $i => $row): ?>
                            <section class="<?= $i < count($schedules) - 1 ? 'list-row' : '' ?> p-4">
                                <section class="row g-3 align-items-center">
                                    <article class="col-md-2">
                                        <?= e($row['employee_name']) ?>
                                    </article>

                                    <article class="col-md-2">
                                        <?= e($row['park_name']) ?>
                                    </article>

                                    <article class="col-md-2 text-muted">
                                        <?= e(format_date($row['shift_date'])) ?>
                                    </article>

                                    <article class="col-md-2 text-muted">
                                        <?= e(date('g:i A', strtotime($row['start_time']))) ?>–<?= e(date('g:i A', strtotime($row['end_time']))) ?>
                                    </article>

                                    <article class="col-md-2 text-muted">
                                        <?= e($row['assignment']) ?>
                                    </article>

                                    <article class="col-md-1 text-muted">
                                        <?= e($scheduleStatusLabels[$row['schedule_status']] ?? ucfirst($row['schedule_status'])) ?>
                                    </article>

                                    <article class="col-md-1 d-flex flex-wrap gap-5">
                                        <a class="btn btn-sm btn-outline-dark" href="<?= e(schedule_admin_path(['schedule_id' => (int) $row['id']])) ?>">
                                            <?= (($row['schedule_status'] ?? '') === 'cancelled' || ($row['account_status'] ?? '') === 'disabled') ? 'View' : 'Edit' ?>
                                        </a>

                                        <?php if (($row['schedule_status'] ?? '') !== 'cancelled' && ($row['account_status'] ?? '') !== 'disabled'): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="cancel_schedule" />
                                                <input type="hidden" name="schedule_filter" value="<?= e($scheduleFilter) ?>" />
                                                <input type="hidden" name="schedule_id" value="<?= (int) $row['id'] ?>" />
                                                <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Cancel this shift?">Cancel</button>
                                            </form>
                                        <?php endif; ?>
                                    </article>
                                </section>
                            </section>
                        <?php endforeach; ?>

                        <!-- Empty schedule message -->
                        <?php if (!$schedules): ?>
                            <section class="p-4 text-muted">No schedules have been created yet.</section>
                        <?php endif; ?>
                    </section>

                    <!-- Admin note -->
                    <section class="note-box">
                        Employees can only view this schedule on their side. Editing stays in the admin portal.
                    </section>
                </article>
            </section>
        </section>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>