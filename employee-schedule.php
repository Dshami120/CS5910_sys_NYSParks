<?php
// Load setup and require employee access.
require 'bootstrap.php';
$user = require_role($db, 'employee');

// Load employee schedule list.
$stmt = $db->prepare("SELECT s.*, p.name AS park_name FROM employee_schedules s JOIN parks p ON p.id=s.park_id WHERE s.employee_id=? ORDER BY s.shift_date ASC, s.start_time ASC");
$stmt->execute([$user['id']]);
$schedules = $stmt->fetchAll();
?>

<?php
// Set page metadata.
$pageTitle = 'NYS Parks - Employee Schedule';
$bodyPage = 'employee-schedule';
$extraHead = '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

    <main class="py-5">
        <section class="container">

            <!-- Page heading and employee navigation -->
            <section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <p class="eyebrow mb-2">Employee portal</p>
                    <h1 class="display-6 fw-bold mb-2">My Schedule</h1>
                    <p class="text-muted mb-0">Employees can view their schedule here, but only admins can make schedule changes.</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-dark" href="employee-dashboard.php">
                        <i class="bi bi-speedometer2"></i> Employee Dash
                    </a>
                    <a class="btn btn-success" href="employee-schedule.php">
                        <i class="bi bi-calendar3"></i> Employee Schedule
                    </a>
                    <a class="btn btn-outline-dark" href="employee-pto.php">
                        <i class="bi bi-calendar-event me-1"></i>Employee PTO
                    </a>
                </div>
            </section>

            <!-- Schedule and PTO layout -->
            <section class="row g-4">

                <!-- Employee schedule list -->
                <article class="col-xl-8">
                    <section class="list-shell">

                        <!-- Schedule list heading -->
                        <section class="p-4 border-bottom">
                            <section class="row g-3">
                                <article class="col-md-4 fw-bold">Park</article>
                                <article class="col-md-3 fw-bold">Date</article>
                                <article class="col-md-3 fw-bold">Shift</article>
                                <article class="col-md-2 fw-bold">Role</article>
                            </section>
                        </section>

                        <!-- Schedule rows -->
                        <?php foreach ($schedules as $shift): ?>
                            <section class="list-row p-4">
                                <section class="row g-3">
                                    <article class="col-md-4 fw-semibold">
                                        <?= e($shift['park_name']) ?>
                                    </article>

                                    <article class="col-md-3 text-muted">
                                        <?= date('m/d/Y', strtotime($shift['shift_date'])) ?>
                                    </article>

                                    <article class="col-md-3 text-muted">
                                        <?= date('g:i A', strtotime($shift['start_time'])) ?>
                                        –
                                        <?= date('g:i A', strtotime($shift['end_time'])) ?>
                                    </article>

                                    <article class="col-md-2 text-muted">
                                        <?= e($shift['assignment']) ?>
                                    </article>
                                </section>
                            </section>
                        <?php endforeach; ?>
                    </section>
                </article>

                <!-- PTO reminder card -->
                <article class="col-xl-4">
                    <section class="soft-card p-4 h-100">
                        <h2 class="h5 fw-bold mb-3">Need time off?</h2>
                        <p class="text-muted">Employees cannot edit schedule assignments here. Submit PTO so an admin can review coverage and update the schedule if approved.</p>
                        <a href="employee-pto.php" class="btn btn-success rounded-pill w-100">Open PTO Request Page</a>
                    </section>
                </article>
            </section>
        </section>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>