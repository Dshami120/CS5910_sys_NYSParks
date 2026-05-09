<?php
// Load setup and require admin access.
require 'bootstrap.php';
$user = require_role($db, 'admin');

// Handle PTO approval actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate PTO approval action.
    $id = (int) post('pto_id');
    $decision = post('decision');

    if (!in_array($decision, ['approved', 'denied'], true)) {
        flash_set('error', 'Invalid PTO action.');
        redirect('admin-pto.php');
    }

    // Only pending PTO requests can be approved or denied.
    $updateStmt = $db->prepare("
        UPDATE pto_requests
        SET pto_status=?,
            reviewed_by=?,
            reviewed_at=NOW(),
            admin_notes=?
        WHERE id=?
          AND pto_status='pending'
    ");

    $updateStmt->execute([
        $decision,
        $user['id'],
        post('admin_notes') ?: null,
        $id
    ]);

    // Confirm PTO update result.
    if ($updateStmt->rowCount() > 0) {
        flash_set('success', 'PTO request updated.');
    } else {
        flash_set('error', 'PTO request could not be updated. It may have already been reviewed or cancelled.');
    }

    redirect('admin-pto.php');
}

// Load PTO summary counts.
$pending = (int) $db->query("SELECT COUNT(*) FROM pto_requests WHERE pto_status='pending'")->fetchColumn();
$approvedMonth = (int) $db->query("SELECT COUNT(*) FROM pto_requests WHERE pto_status='approved' AND YEAR(start_date)=YEAR(CURDATE()) AND MONTH(start_date)=MONTH(CURDATE())")->fetchColumn();
$conflicts = (int) $db->query("SELECT COUNT(DISTINCT p.id) FROM pto_requests p JOIN employee_schedules s ON s.employee_id=p.employee_id AND s.shift_date BETWEEN p.start_date AND p.end_date AND s.schedule_status <> 'cancelled' WHERE p.pto_status='pending'")->fetchColumn();

// Load PTO request rows.
$rows = $db->query("SELECT p.*, CONCAT(u.first_name,' ',u.last_name) AS employee_name, EXISTS(SELECT 1 FROM employee_schedules s WHERE s.employee_id=p.employee_id AND s.shift_date BETWEEN p.start_date AND p.end_date AND s.schedule_status <> 'cancelled') AS has_schedule_conflict FROM pto_requests p JOIN users u ON u.id=p.employee_id ORDER BY p.created_at DESC")->fetchAll();
?>

<?php
// Set page metadata.
$pageTitle = 'NYS Parks - Admin PTO';
$bodyPage = 'admin-pto';
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
            <header class="mb-4">
                <p class="section-kicker mb-2">Admin portal</p>
                <h1 class="display-6 fw-bold mb-2">PTO Approval Queue</h1>
                <p class="text-muted mb-0">Approve or deny employee PTO requests submitted from the employee portal.</p>

                <section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                    <div></div>

                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-outline-dark" href="admin-dashboard.php">
                            <i class="bi bi-speedometer2"></i> Admin Dash
                        </a>

                        <a class="btn btn-outline-dark" href="admin-employee-schedule.php">
                            <i class="bi bi-calendar3"></i> Employee Schedules
                        </a>

                        <a class="btn btn-success" href="admin-pto.php">
                            <i class="bi bi-briefcase"></i> PTO Requests
                        </a>

                        <a class="btn btn-outline-dark" href="admin-bookings.php">
                            <i class="bi bi-journal-check"></i> Client Bookings
                        </a>

                        <a class="btn btn-outline-dark" href="admin-news.php">
                            <i class="bi bi-journal-check"></i> News Manager
                        </a>

                        <a class="btn btn-outline-dark" href="admin-employee-accounts.php">
                            <i class="bi bi-journal-check"></i> Employee Accounts
                        </a>

                        <a class="btn btn-outline-dark" href="admin-csv.php">
                            <i class="bi bi-journal-check"></i> CSV
                        </a>
                    </div>
                </section>
            </header>

            <!-- PTO summary cards -->
            <section class="row g-4 mb-4">

                <!-- Pending requests card -->
                <article class="col-md-4">
                    <section class="stats-card h-100">
                        <p class="small text-uppercase text-muted mb-1">Pending requests</p>
                        <h2 class="h3 fw-bold mb-2"><?= $pending ?></h2>
                        <p class="text-muted mb-0">Waiting for decision.</p>
                    </section>
                </article>

                <!-- Approved this month card -->
                <article class="col-md-4">
                    <section class="stats-card h-100">
                        <p class="small text-uppercase text-muted mb-1">Approved this month</p>
                        <h2 class="h3 fw-bold mb-2"><?= $approvedMonth ?></h2>
                        <p class="text-muted mb-0">Time off already granted.</p>
                    </section>
                </article>

                <!-- Coverage conflicts card -->
                <article class="col-md-4">
                    <section class="stats-card h-100">
                        <p class="small text-uppercase text-muted mb-1">Coverage conflicts</p>
                        <h2 class="h3 fw-bold mb-2"><?= $conflicts ?></h2>
                        <p class="text-muted mb-0">Needs schedule adjustment.</p>
                    </section>
                </article>
            </section>

            <!-- PTO request list -->
            <section class="list-shell">

                <!-- PTO table header -->
                <section class="p-4 border-bottom">
                    <section class="row g-3">
                        <article class="col-md-3 fw-bold">Employee</article>
                        <article class="col-md-2 fw-bold">Dates</article>
                        <article class="col-md-2 fw-bold">Type</article>
                        <article class="col-md-2 fw-bold">Coverage</article>
                        <article class="col-md-3 fw-bold">Actions</article>
                    </section>
                </section>

                <!-- PTO request rows -->
                <?php foreach ($rows as $i => $row): ?>
                    <section class="<?= $i < count($rows) - 1 ? 'list-row' : '' ?> p-4">
                        <section class="row g-3 align-items-center">

                            <!-- Employee and status -->
                            <article class="col-md-3">
                                <?= e($row['employee_name']) ?><br>

                                <span class="status-pill <?= booking_status_class($row['pto_status']) ?>">
                                <?= e(ucfirst($row['pto_status'])) ?>
                            </span>
                            </article>

                            <!-- PTO date range -->
                            <article class="col-md-2 text-muted">
                                <?= format_date($row['start_date']) ?>
                                <?= $row['start_date'] !== $row['end_date'] ? '–' . format_date($row['end_date']) : '' ?>
                            </article>

                            <!-- Leave type -->
                            <article class="col-md-2 text-muted">
                                <?= e($row['leave_type']) ?>
                            </article>

                            <!-- Coverage conflict status -->
                            <article class="col-md-2">
                            <span class="status-pill <?= (int) $row['has_schedule_conflict'] ? 'status-pending' : 'status-approved' ?>">
                                <?= (int) $row['has_schedule_conflict'] ? 'Conflict' : 'No conflict' ?>
                            </span>
                            </article>

                            <!-- PTO action controls -->
                            <article class="col-md-3">
                                <?php if ($row['pto_status'] === 'pending'): ?>
                                    <form method="post" class="d-flex flex-column gap-2 mb-0">
                                        <input type="hidden" name="pto_id" value="<?= (int) $row['id'] ?>">

                                        <input
                                                type="text"
                                                name="admin_notes"
                                                class="form-control form-control-sm"
                                                placeholder="Optional admin note"
                                        >

                                        <section class="d-flex flex-wrap gap-2">
                                            <button
                                                    type="submit"
                                                    name="decision"
                                                    value="approved"
                                                    class="btn btn-sm btn-success rounded-pill"
                                            >
                                                Approve
                                            </button>

                                            <button
                                                    type="submit"
                                                    name="decision"
                                                    value="denied"
                                                    class="btn btn-sm btn-outline-danger rounded-pill"
                                            >
                                                Deny
                                            </button>
                                        </section>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">Already reviewed</span>
                                <?php endif; ?>
                            </article>
                        </section>
                    </section>
                <?php endforeach; ?>

                <!-- Empty PTO message -->
                <?php if (!$rows): ?>
                    <section class="p-4">
                        <p class="text-muted mb-0">No PTO requests found.</p>
                    </section>
                <?php endif; ?>
            </section>
        </section>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>