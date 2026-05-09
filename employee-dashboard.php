<?php
// Load project setup.
require 'bootstrap.php';
$user = require_role($db, 'employee');

// Load employee dashboard metrics.
$stmt = $db->prepare("SELECT s.*, p.name AS park_name FROM employee_schedules s JOIN parks p ON p.id=s.park_id WHERE s.employee_id=? AND s.shift_date >= CURDATE() AND s.schedule_status <> 'cancelled' ORDER BY s.shift_date, s.start_time LIMIT 1");
$stmt->execute([$user['id']]); $nextShift = $stmt->fetch();
$stmt = $db->prepare("SELECT COALESCE(SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)),0) FROM employee_schedules WHERE employee_id=? AND YEARWEEK(shift_date,1)=YEARWEEK(CURDATE(),1) AND schedule_status <> 'cancelled'");
$stmt->execute([$user['id']]); $hoursWeek = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM pto_requests WHERE employee_id=? AND pto_status='pending'");
$stmt->execute([$user['id']]); $ptoPending = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT s.*, p.name AS park_name FROM employee_schedules s JOIN parks p ON p.id=s.park_id WHERE s.employee_id=? AND s.shift_date >= CURDATE() AND s.schedule_status <> 'cancelled' ORDER BY s.shift_date ASC, s.start_time ASC LIMIT 5");
$stmt->execute([$user['id']]); $shifts = $stmt->fetchAll();
$ptoStatusText = $ptoPending === 0 ? 'No requests currently in review.' : ($ptoPending === 1 ? '1 request currently in review.' : $ptoPending . ' requests currently in review.');
?><?php
// Set page metadata.
$pageTitle = 'NYS Parks - Employee Dashboard';
$bodyPage = 'employee-dashboard';
$extraHead = '';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="py-5">
    <section class="container">
<section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <p class="eyebrow mb-2">Employee portal</p>
        <h1 class="display-6 fw-bold mb-2">Employee Dashboard</h1>
        <p class="text-muted mb-0">Employees can only view their schedule and submit PTO requests.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-success" href="employee-dashboard.php"><i class="bi bi-speedometer2"></i> Employee Dash</a>
        <a class="btn btn-outline-dark" href="employee-schedule.php"><i class="bi bi-calendar3"></i> Employee Schedule</a>
        <a class="btn btn-outline-dark" href="employee-pto.php"><i class="bi bi-calendar-event me-1"></i>Employee PTO</a>
    </div>
</section>

        <!-- Employee summary cards. -->
        <section class="row g-4 mb-4">
            <article class="col-xl-8">
                <section class="soft-card p-4 p-lg-5 h-100">
                    <p class="section-kicker mb-2">Employee Notifications</p>
                    <h1 class="h2 fw-bold mb-1">Shift & PTO Cards</h1>
                    <p class="text-muted mb-0">Quick at-a-glance summary for current schedule and PTO statuses.</p>

                    <section class="row g-3 mt-4">
                        <article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Next shift</p><h2 class="h5 fw-bold mb-2"><?= $nextShift ? date('M d', strtotime($nextShift['shift_date'])) : 'No shift' ?></h2><p class="text-muted mb-0"><?= $nextShift ? e($nextShift['park_name']) . ' · ' . date('g:i A', strtotime($nextShift['start_time'])) . '–' . date('g:i A', strtotime($nextShift['end_time'])) : 'No upcoming shift assigned.' ?></p></section></article>
                        <article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Hours this week</p><h2 class="h3 fw-bold mb-2"><?= $hoursWeek ?></h2><p class="text-muted mb-0">Based on assigned schedule blocks.</p></section></article>
                        <article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">PTO status</p><h2 class="h5 fw-bold mb-2"><?= $ptoPending ?> pending</h2><p class="text-muted mb-0"><?= e($ptoStatusText) ?></p></section></article>
                    </section>
                </section>
            </article>
            <article class="col-xl-4">
                <section class="soft-card p-4 h-100">
                    <h2 class="h5 fw-bold mb-3">Employee actions</h2>
                    <a href="employee-schedule.php" class="action-card mb-3 text-decoration-none d-block"><strong>View schedule</strong><span>See your upcoming shifts and park assignments.</span></a>
                    <a href="employee-pto.php" class="action-card text-decoration-none d-block"><strong>Create PTO request</strong><span>Submit time off and track request status.</span></a>
                </section>
            </article>
        </section>

        <section class="list-shell">
            <section class="p-4 border-bottom">
                <h2 class="h5 fw-bold mb-1">Upcoming shifts</h2>
                <p class="text-muted mb-0">Read-only employee schedule preview</p>
            </section>
            <?php if ($shifts): ?>
                <?php foreach ($shifts as $i => $shift): ?>
                    <section class="<?= $i < count($shifts) - 1 ? 'list-row' : '' ?> p-4"><section class="row g-3"><article class="col-md-4 fw-semibold"><?= e($shift['park_name']) ?></article><article class="col-md-4 text-muted"><?= date('m/d/Y', strtotime($shift['shift_date'])) ?></article><article class="col-md-4 text-muted"><?= date('g:i A', strtotime($shift['start_time'])) ?> – <?= date('g:i A', strtotime($shift['end_time'])) ?></article></section></section>
                <?php endforeach; ?>
            <?php else: ?>
                <section class="p-4 text-muted">No upcoming shifts assigned.</section>
            <?php endif; ?>
        </section>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
