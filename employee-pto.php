<?php
// Load setup and require employee access.
require 'bootstrap.php';
$user = require_role($db, 'employee');

// Handle PTO form actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Route PTO create/cancel actions.
    $action = post('action', 'create');

    // Cancel pending PTO request.
    if ($action === 'cancel') {
        // Cancel only this employee's pending PTO request.
        $requestId = (int) post('pto_id');

        $cancelStmt = $db->prepare("
            UPDATE pto_requests
            SET pto_status='cancelled'
            WHERE id=?
              AND employee_id=?
              AND pto_status='pending'
        ");
        $cancelStmt->execute([$requestId, $user['id']]);

        if ($cancelStmt->rowCount() > 0) {
            flash_set('success', 'Pending PTO request cancelled.');
        } else {
            flash_set('error', 'PTO request could not be cancelled. It may have already been reviewed.');
        }

        redirect('employee-pto.php');
    }

    // Read PTO form values.
    $leave = post('leave_type');
    $start = post('start_date');
    $end = post('end_date');
    $reason = post('reason');

    // Validate required PTO fields.
    if (!$leave || !$start || !$end) {
        flash_set('error', 'Complete the PTO form.');
        redirect('employee-pto.php');
    }

    // Prevent past PTO requests.
    if ($start < date('Y-m-d')) {
        flash_set('error', 'PTO start date cannot be in the past.');
        redirect('employee-pto.php');
    }

    // Validate date order.
    if ($start > $end) {
        flash_set('error', 'End date must be on or after start date.');
        redirect('employee-pto.php');
    }

    // Validate leave type against expected options.
    $validLeaveTypes = ['Vacation', 'Sick Leave', 'Personal Leave', 'Bereavement'];

    if (!in_array($leave, $validLeaveTypes, true)) {
        flash_set('error', 'Please choose a valid leave type.');
        redirect('employee-pto.php');
    }

    // Prevent overlapping pending or approved PTO.
    $dup = $db->prepare("
        SELECT COUNT(*)
        FROM pto_requests
        WHERE employee_id=?
          AND pto_status IN ('pending','approved')
          AND NOT (end_date < ? OR start_date > ?)
    ");
    $dup->execute([$user['id'], $start, $end]);

    if ((int) $dup->fetchColumn() > 0) {
        flash_set('error', 'That PTO range overlaps an existing request.');
        redirect('employee-pto.php');
    }

    // Save new PTO request.
    $db->prepare("
        INSERT INTO pto_requests (
            employee_id,
            leave_type,
            start_date,
            end_date,
            reason,
            pto_status
        )
        VALUES (?,?,?,?,?, 'pending')
    ")->execute([
        $user['id'],
        $leave,
        $start,
        $end,
        $reason ?: null
    ]);

    flash_set('success', 'PTO request submitted.');
    redirect('employee-pto.php');
}

// Load employee PTO requests.
$stmt = $db->prepare("SELECT * FROM pto_requests WHERE employee_id=? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$requests = $stmt->fetchAll();
?>

<?php
// Set page metadata.
$pageTitle = 'NYS Parks - Employee PTO';
$bodyPage = 'employee-pto';
$extraHead = '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

    <main class="py-5">
        <section class="container">

            <!-- Page heading and employee navigation -->
            <section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <p class="eyebrow mb-2">Employee portal</p>
                    <h1 class="display-6 fw-bold mb-2">Create PTO Request</h1>
                    <p class="text-muted mb-0">Submit new time-off requests and check the status of recent submissions.</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-dark" href="employee-dashboard.php">
                        <i class="bi bi-speedometer2"></i> Employee Dash
                    </a>
                    <a class="btn btn-outline-dark" href="employee-schedule.php">
                        <i class="bi bi-calendar3"></i> Employee Schedule
                    </a>
                    <a class="btn btn-success" href="employee-pto.php">
                        <i class="bi bi-calendar-event me-1"></i>Employee PTO
                    </a>
                </div>
            </section>

            <!-- PTO form and request list -->
            <section class="row g-4">

                <!-- New PTO request form -->
                <article class="col-xl-5">
                    <section class="form-panel p-4 h-100">
                        <h2 class="h5 fw-bold mb-3">New PTO Request</h2>

                        <!-- Flash message -->
                        <?php if ($flash): ?>
                            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>">
                                <?= e($flash['message']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- PTO create form -->
                        <form method="post">
                            <input type="hidden" name="action" value="create">

                            <label class="form-label">Leave type</label>
                            <select name="leave_type" class="form-select mb-3">
                                <option>Vacation</option>
                                <option>Sick Leave</option>
                                <option>Personal Leave</option>
                                <option>Bereavement</option>
                            </select>

                            <label class="form-label">Start date</label>
                            <input type="date" name="start_date" class="form-control mb-3" required />

                            <label class="form-label">End date</label>
                            <input type="date" name="end_date" class="form-control mb-3" required />

                            <label class="form-label">Reason</label>
                            <textarea rows="4" name="reason" class="form-control mb-4" placeholder="Optional comments"></textarea>

                            <button type="submit" class="btn btn-success rounded-pill w-100 fw-semibold">Submit PTO Request</button>
                        </form>
                    </section>
                </article>

                <!-- PTO request list -->
                <article class="col-xl-7">
                    <section class="list-shell">

                        <!-- PTO list heading -->
                        <section class="p-4 border-bottom">
                            <section class="row g-3">
                                <article class="col-md-2 fw-bold">Dates</article>
                                <article class="col-md-2 fw-bold">Type</article>
                                <article class="col-md-2 fw-bold">Submitted</article>
                                <article class="col-md-2 fw-bold">Status</article>
                                <article class="col-md-4 fw-bold">Actions</article>
                            </section>
                        </section>

                        <!-- PTO request rows -->
                        <?php foreach ($requests as $i => $row): ?>
                            <section class="<?= $i < count($requests) - 1 ? 'list-row' : '' ?> p-4">
                                <section class="row g-3 align-items-center">

                                    <article class="col-md-2">
                                        <?= format_date($row['start_date']) ?>
                                        <?= $row['start_date'] !== $row['end_date'] ? '–' . format_date($row['end_date']) : '' ?>
                                    </article>

                                    <article class="col-md-2 text-muted">
                                        <?= e($row['leave_type']) ?>
                                    </article>

                                    <article class="col-md-2 text-muted">
                                        <?= format_date($row['created_at']) ?>
                                    </article>

                                    <article class="col-md-2">
                                    <span class="status-pill <?= booking_status_class($row['pto_status']) ?>">
                                        <?= e(ucfirst($row['pto_status'])) ?>
                                    </span>
                                    </article>

                                    <article class="col-md-4">
                                        <?php if ($row['pto_status'] === 'pending'): ?>
                                            <form method="post" class="mb-0">
                                                <input type="hidden" name="action" value="cancel">
                                                <input type="hidden" name="pto_id" value="<?= (int) $row['id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger rounded-pill" type="submit">
                                                    Cancel request
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small">No action</span>
                                        <?php endif; ?>
                                    </article>
                                </section>
                            </section>
                        <?php endforeach; ?>

                        <!-- Empty PTO message -->
                        <?php if (!$requests): ?>
                            <section class="p-4">
                                <section class="row g-3 align-items-center">
                                    <article class="col-12 text-muted">No PTO requests yet.</article>
                                </section>
                            </section>
                        <?php endif; ?>
                    </section>
                </article>
            </section>
        </section>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>