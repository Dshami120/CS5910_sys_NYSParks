<?php
// Load project setup.
require 'bootstrap.php';
$user = require_role($db, 'admin');
// Employee and news mutations were moved to the dedicated admin pages.
// Employee changes: admin-employee-accounts.php
// News changes: admin-news.php
$parksCount = (int) $db->query("SELECT COUNT(*) FROM parks")->fetchColumn();
$publishedEventsCount = (int) $db->query("SELECT COUNT(*) FROM events WHERE event_status='published'")->fetchColumn();
$approvedBookingsCount = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE booking_status IN ('approved','confirmed')")->fetchColumn();
$pendingBookingsCount = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE booking_status='pending'")->fetchColumn();
$pendingBookings = $pendingBookingsCount;
$pendingPto = (int) $db->query("SELECT COUNT(*) FROM pto_requests WHERE pto_status='pending'")->fetchColumn();
$registeredAttendanceCount = (int) $db->query("SELECT COUNT(*) FROM attendance WHERE attendance_status='registered'")->fetchColumn();
$attendedAttendanceCount = (int) $db->query("SELECT COUNT(*) FROM attendance WHERE attendance_status='attended'")->fetchColumn();
$upcomingRsvpGuests = (int) $db->query("SELECT COALESCE(SUM(a.guest_count),0) FROM attendance a JOIN events e ON e.id=a.event_id WHERE a.attendance_status='registered' AND e.end_datetime >= NOW()")->fetchColumn();
$totalAttendanceGuests = (int) $db->query("SELECT COALESCE(SUM(guest_count),0) FROM attendance WHERE attendance_status IN ('registered','attended')")->fetchColumn();
$recentAttendance = $db->query("SELECT a.*, e.title, e.start_datetime, p.name AS park_name FROM attendance a JOIN events e ON e.id=a.event_id JOIN parks p ON p.id=e.park_id ORDER BY a.registered_at DESC LIMIT 8")->fetchAll();
$employeeActions = (int) $db->query("SELECT COUNT(*) FROM users WHERE role='employee' AND account_status='active'")->fetchColumn();
$openAlerts = $pendingBookings + $pendingPto;
$adminChartRows = [];
foreach ($db->query("SELECT DATE(b.start_datetime) AS date, p.name AS park, 'bookings' AS metric, 1 AS value FROM bookings b JOIN parks p ON p.id=b.park_id WHERE b.booking_status IN ('approved','confirmed') ORDER BY b.start_datetime")->fetchAll() as $row) { $adminChartRows[] = $row; }
foreach ($db->query("SELECT DATE(e.start_datetime) AS date, p.name AS park, 'events' AS metric, 1 AS value FROM events e JOIN parks p ON p.id=e.park_id WHERE e.event_status='published' ORDER BY e.start_datetime")->fetchAll() as $row) { $adminChartRows[] = $row; }
foreach ($db->query("SELECT DATE(a.registered_at) AS date, p.name AS park, 'attendance' AS metric, a.guest_count AS value FROM attendance a JOIN events e ON e.id=a.event_id JOIN parks p ON p.id=e.park_id WHERE a.attendance_status IN ('registered','attended') ORDER BY a.registered_at")->fetchAll() as $row) { $adminChartRows[] = $row; }
foreach ($db->query("SELECT DATE(py.created_at) AS date, COALESCE(p.name, 'No park') AS park, 'donations' AS metric, py.amount AS value FROM payments py LEFT JOIN bookings b ON b.id=py.booking_id LEFT JOIN parks p ON p.id=b.park_id WHERE py.payment_type='donation' AND py.payment_status='completed' ORDER BY py.created_at")->fetchAll() as $row) { $adminChartRows[] = $row; }
$trafficAvailable = false;
$newsTopics = NEWS_TOPICS;
$newsStatuses = NEWS_STATUSES;
$newsSearch = get('news_q');
$newsParams = [];
$newsSql = "SELECT * FROM news WHERE 1=1";
if ($newsSearch !== '') {
    $newsSql .= " AND (title LIKE ? OR summary LIKE ? OR content LIKE ? OR region LIKE ? OR tag LIKE ?)";
    $like = "%{$newsSearch}%";
    array_push($newsParams, $like, $like, $like, $like, $like);
}
$newsSql .= " ORDER BY published_date DESC, updated_at DESC LIMIT 20";
$newsStmt = $db->prepare($newsSql);
$newsStmt->execute($newsParams);
$newsItems = $newsStmt->fetchAll();
$selectedNewsId = (int) get('news_id');
$selectedNews = null;
if ($selectedNewsId) {
    $stmt = $db->prepare("SELECT * FROM news WHERE id=?");
    $stmt->execute([$selectedNewsId]);
    $selectedNews = $stmt->fetch();
}
if (!$selectedNews && $newsItems) { $selectedNews = $newsItems[0]; }
$employees = user_options($db, 'employee');
$parks = park_options($db);
$selectedId = (int) get('employee_id');
$selected = null;
if ($selectedId) { $stmt=$db->prepare("SELECT * FROM users WHERE id=? AND role='employee'"); $stmt->execute([$selectedId]); $selected=$stmt->fetch(); }
if (!$selected && $employees) { $selected = $employees[0]; }
?><?php
// Set page metadata.
$pageTitle = 'NYS Parks - Admin Dashboard';
$bodyPage = 'admin-dashboard';
$extraHead = '';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container py-5">
    <?php if ($flash): ?><div class="alert alert-<?= $flash['type']==='error'?'danger':'success' ?> mb-4"><?= e($flash['message']) ?></div><?php endif; ?>
    <section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-2">Admin Workspace</p>
            <h1 class="display-6 fw-bold mb-2">Admin Dashboard</h1>
            <p class="text-muted mb-0">Analytics, approvals, notifications, and employee account management.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-success" href="admin-dashboard.php"><i class="bi bi-speedometer2"></i> Admin Dash</a>
            <a class="btn btn-outline-dark" href="admin-employee-schedule.php"><i class="bi bi-calendar3"></i> Employee Schedules</a>
            <a class="btn btn-outline-dark" href="admin-pto.php"><i class="bi bi-briefcase"></i> PTO Requests</a>
            <a class="btn btn-outline-dark" href="admin-bookings.php"><i class="bi bi-journal-check"></i> Client Bookings</a>
            <a class="btn btn-outline-dark" href="admin-news.php"><i class="bi bi-journal-check"></i> News Manager</a>
            <a class="btn btn-outline-dark" href="admin-employee-accounts.php"><i class="bi bi-journal-check"></i> Employee Accounts</a>
            <a class="btn btn-outline-dark" href="admin-csv.php"><i class="bi bi-journal-check"></i> CSV</a>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <article class="admin-stat-card">
                <span class="admin-stat-icon icon-blue">☑</span>
                <p class="admin-stat-label mb-1">Parks</p>
                <h2 class="admin-stat-value mb-0"><?= $parksCount ?></h2>
            </article>
        </div>
        <div class="col-md-6 col-xl-3">
            <article class="admin-stat-card">
                <span class="admin-stat-icon icon-green">↗</span>
                <p class="admin-stat-label mb-1">Published events</p>
                <h2 class="admin-stat-value mb-0"><?= $publishedEventsCount ?></h2>
            </article>
        </div>
        <div class="col-md-6 col-xl-3">
            <article class="admin-stat-card">
                <span class="admin-stat-icon icon-purple">◫</span>
                <p class="admin-stat-label mb-1">Approved/Confirmed bookings</p>
                <h2 class="admin-stat-value mb-0"><?= $approvedBookingsCount ?></h2>
            </article>
        </div>
        <div class="col-md-6 col-xl-3">
            <article class="admin-stat-card">
                <span class="admin-stat-icon icon-orange">🔔</span>
                <p class="admin-stat-label mb-1">Pending bookings</p>
                <h2 class="admin-stat-value mb-0"><?= $pendingBookingsCount ?></h2>
            </article>
        </div>
    </section>

    <section class="row g-4 mb-4" id="attendance-summary">
        <div class="col-md-6 col-xl-3">
            <article class="admin-stat-card">
                <span class="admin-stat-icon icon-green"><i class="bi bi-person-check"></i></span>
                <p class="admin-stat-label mb-1">Registered RSVPs</p>
                <h2 class="admin-stat-value mb-0"><?= $registeredAttendanceCount ?></h2>
                <p class="text-muted small mb-0">Active attendance records.</p>
            </article>
        </div>
        <div class="col-md-6 col-xl-3">
            <article class="admin-stat-card">
                <span class="admin-stat-icon icon-blue"><i class="bi bi-people"></i></span>
                <p class="admin-stat-label mb-1">Upcoming RSVP Guests</p>
                <h2 class="admin-stat-value mb-0"><?= $upcomingRsvpGuests ?></h2>
                <p class="text-muted small mb-0">Guest count for future/current events.</p>
            </article>
        </div>
        <div class="col-md-6 col-xl-3">
            <article class="admin-stat-card">
                <span class="admin-stat-icon icon-purple"><i class="bi bi-clipboard-check"></i></span>
                <p class="admin-stat-label mb-1">Attended</p>
                <h2 class="admin-stat-value mb-0"><?= $attendedAttendanceCount ?></h2>
                <p class="text-muted small mb-0">Manually completed attendance.</p>
            </article>
        </div>
        <div class="col-md-6 col-xl-3">
            <article class="admin-stat-card">
                <span class="admin-stat-icon icon-orange"><i class="bi bi-people-fill"></i></span>
                <p class="admin-stat-label mb-1">Total RSVP Guests (Registered & Attended)</p>
                <h2 class="admin-stat-value mb-0"><?= $totalAttendanceGuests ?></h2>
                <p class="text-muted small mb-0">Guests across active and completed attendance records.</p>
            </article>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-lg-7">
            <article class="admin-panel h-100">
                <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-4">
                    <div>
                        <h2 class="h3 fw-bold mb-1">Dashboard metrics</h2>
                        <p class="text-muted mb-0">Database-backed bookings, events, attendance, and donation dollars.</p>
                    </div>
                    <div class="admin-chart-filters">
                        <div>
                            <label class="form-label small text-uppercase text-muted mb-1">Metric</label>
                            <select class="form-select admin-filter-select" id="adminChartMetric">
                                <option value="bookings" selected>Bookings</option>
                                <option value="events">Events</option>
                                <option value="attendance">Attendance</option>
                                <option value="donations">Donation dollars</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small text-uppercase text-muted mb-1">View By</label>
                            <select class="form-select admin-filter-select" id="adminChartRange">
                                <option value="day">Day</option>
                                <option value="week">Week</option>
                                <option value="month" selected>Month</option>
                                <option value="year">Year</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small text-uppercase text-muted mb-1">Park</label>
                            <select class="form-select admin-filter-select" id="adminChartPark">
                                <option value="all" selected>All Parks</option>
                                <?php foreach ($parks as $park): ?><option value="<?= e($park['name']) ?>"><?= e($park['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="admin-chart bg-white rounded-4 border p-3">
                    <canvas id="adminBookingsChart" height="150"></canvas>
                </div>
                <?php if (!$trafficAvailable): ?><p class="text-muted small mt-3 mb-0">Traffic is not available yet because there is no site_visits or page_views table.</p><?php endif; ?>
            </article>
        </div>
        <div class="col-lg-5">
            <article class="admin-panel h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h3 fw-bold mb-0">Notifications</h2>
                    <span class="admin-badge"><?= $openAlerts ?> new</span>
                </div>

                <div class="vstack gap-3">
                    <article class="admin-note-card">
                        <h3 class="h5 fw-bold mb-2"><?= $pendingBookings ?> booking requests</h3>
                        <p class="text-muted mb-3">Open the bookings page to approve or deny client event requests.</p>
                        <a href="admin-bookings.php" class="btn btn-sm btn-outline-dark">Review bookings</a>
                    </article>

                    <article class="admin-note-card">
                        <h3 class="h5 fw-bold mb-2"><?= $pendingPto ?> PTO requests</h3>
                        <p class="text-muted mb-3">Review employee time-off submissions waiting in the PTO queue.</p>
                        <a href="admin-pto.php" class="btn btn-sm btn-outline-dark">Review PTO</a>
                    </article>

                    <article class="admin-note-card">
                        <h3 class="h5 fw-bold mb-2"><?= $employeeActions ?> employee actions</h3>
                        <p class="text-muted mb-3">New employee account creation and one schedule conflict to resolve.</p>
                        <a href="admin-employee-schedule.php" class="btn btn-sm btn-outline-dark">Open employee tools</a>
                    </article>
                </div>
            </article>
        </div>
    </section>

    <section class="card shadow-sm border-0 rounded-4 mb-4" id="attendance-records">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                <div>
                    <p class="eyebrow mb-1">Attendance</p>
                    <h2 class="h3 fw-bold mb-1">Recent RSVP / Attendance Records</h2>
                    <p class="text-muted mb-0">Read-only overview from the existing attendance table. Manual attendance updates can stay in your existing workflow.</p>
                </div>
                <div class="pill-note">Admin only · attendance table</div>
            </div>

            <section class="list-shell mb-0">
                <section class="p-4 border-bottom">
                    <section class="row g-3">
                        <article class="col-lg-3 fw-bold">Event</article>
                        <article class="col-lg-2 fw-bold">Park</article>
                        <article class="col-lg-2 fw-bold">Event Date</article>
                        <article class="col-lg-2 fw-bold">Attendee</article>
                        <article class="col-lg-1 fw-bold">Guests</article>
                        <article class="col-lg-2 fw-bold">Status</article>
                    </section>
                </section>
                <?php foreach ($recentAttendance as $record): ?>
                    <section class="list-row p-4">
                        <section class="row g-3 align-items-start">
                            <article class="col-lg-3 fw-semibold"><?= e($record['title']) ?></article>
                            <article class="col-lg-2 text-muted"><?= e($record['park_name']) ?></article>
                            <article class="col-lg-2 text-muted"><?= e(format_datetime($record['start_datetime'])) ?></article>
                            <article class="col-lg-2 text-muted"><?= e($record['attendee_email']) ?></article>
                            <article class="col-lg-1 text-muted"><?= (int)$record['guest_count'] ?></article>
                            <article class="col-lg-2"><span class="status-pill <?= booking_status_class($record['attendance_status']) ?>"><?= e(ucfirst(str_replace('_',' ', $record['attendance_status']))) ?></span></article>
                        </section>
                    </section>
                <?php endforeach; ?>
                <?php if (!$recentAttendance): ?><section class="p-4 text-muted">No attendance records yet.</section><?php endif; ?>
            </section>
        </div>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="js/dashboard-charts.js?v=3"></script>
<script>
    window.initAdminDashboardChart(<?= json_encode($adminChartRows, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>);
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
