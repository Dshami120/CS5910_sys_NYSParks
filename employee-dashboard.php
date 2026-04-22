<?php
require 'bootstrap.php';
$user = require_role($db, 'employee');
$stmt = $db->prepare("SELECT s.*, p.name AS park_name FROM employee_schedules s JOIN parks p ON p.id=s.park_id WHERE s.employee_id=? AND s.shift_date >= CURDATE() AND s.schedule_status <> 'cancelled' ORDER BY s.shift_date, s.start_time LIMIT 1");
$stmt->execute([$user['id']]); $nextShift = $stmt->fetch();
$stmt = $db->prepare("SELECT COALESCE(SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)),0) FROM employee_schedules WHERE employee_id=? AND YEARWEEK(shift_date,1)=YEARWEEK(CURDATE(),1) AND schedule_status <> 'cancelled'");
$stmt->execute([$user['id']]); $hoursWeek = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM pto_requests WHERE employee_id=? AND pto_status='pending'");
$stmt->execute([$user['id']]); $ptoPending = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT s.*, p.name AS park_name FROM employee_schedules s JOIN parks p ON p.id=s.park_id WHERE s.employee_id=? ORDER BY s.shift_date ASC, s.start_time ASC LIMIT 5");
$stmt->execute([$user['id']]); $shifts = $stmt->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Employee Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="employee-dashboard">
  <header class="site-header">
    <nav class="container py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
      <section class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 gap-lg-4">
        <a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2">
          <span class="brand-badge">NY</span>
          <span class="brand-mark text-dark">
            NYS Parks<br />
            <small>&amp; RECREATION</small>
          </span>
        </a>
        <ul class="list-unstyled d-flex flex-wrap gap-3 gap-lg-4 m-0 align-items-center">
          <li><a href="parks.php" class="nav-link-custom" data-page-link="parks"><i class="bi bi-tree"></i>Parks</a></li>
          <li><a href="events.php" class="nav-link-custom" data-page-link="events"><i class="bi bi-calendar-event"></i>Events</a></li>
          <li><a href="map.php" class="nav-link-custom" data-page-link="map"><i class="bi bi-geo-alt"></i>Map</a></li>
          <li><a href="ai.php" class="nav-link-custom" data-page-link="ai"><i class="bi bi-stars"></i>AI</a></li>
          <li><a href="news.php" class="nav-link-custom" data-page-link="news"><i class="bi bi-newspaper"></i>News</a></li>
          <li><a href="employee-dashboard.php" class="nav-link-custom active" data-page-link="employee-dashboard"><i class="bi bi-speedometer2"></i>Employee Dash</a></li>
          <li><a href="employee-schedule.php" class="nav-link-custom" data-page-link="employee-schedule"><i class="bi bi-calendar3"></i>Schedule</a></li>
          <li><a href="employee-pto.php" class="nav-link-custom" data-page-link="employee-pto"><i class="bi bi-briefcase"></i>PTO</a></li>
        </ul>
      </section>
      <ul class="list-unstyled d-flex flex-wrap gap-3 m-0 align-items-center">
        <li><a href="logout.php" class="nav-link-custom" data-page-link="logout"><i class="bi bi-box-arrow-right"></i>Logout</a></li>
      </ul>
    </nav>
  </header>
  <main class="py-5">
    <section class="container">
      <section class="row g-4 mb-4">
        <article class="col-xl-8">
          <section class="soft-card p-4 p-lg-5 h-100">
            <p class="section-kicker mb-2">Employee portal</p>
            <h1 class="h2 fw-bold mb-1">Employee Dashboard</h1>
            <p class="text-muted mb-0">Employees can only view their schedule and submit PTO requests.</p>

            <section class="row g-3 mt-4">
              <article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Next shift</p><h2 class="h5 fw-bold mb-2"><?= $nextShift ? date('M d', strtotime($nextShift['shift_date'])) : 'No shift' ?></h2><p class="text-muted mb-0"><?= $nextShift ? e($nextShift['park_name']) . ' · ' . date('g:i A', strtotime($nextShift['start_time'])) . '–' . date('g:i A', strtotime($nextShift['end_time'])) : 'No upcoming shift assigned.' ?></p></section></article>
              <article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Hours this week</p><h2 class="h3 fw-bold mb-2"><?= $hoursWeek ?></h2><p class="text-muted mb-0">Based on assigned schedule blocks.</p></section></article>
              <article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">PTO status</p><h2 class="h5 fw-bold mb-2"><?= $ptoPending ?> pending</h2><p class="text-muted mb-0">One request currently in review.</p></section></article>
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
        <?php foreach ($shifts as $shift): ?>
<section class="list-row p-4"><section class="row g-3"><article class="col-md-4 fw-semibold"><?= e($shift['park_name']) ?></article><article class="col-md-4 text-muted"><?= date('m/d/Y', strtotime($shift['shift_date'])) ?></article><article class="col-md-4 text-muted"><?= date('g:i A', strtotime($shift['start_time'])) ?> – <?= date('g:i A', strtotime($shift['end_time'])) ?></article></section></section>
<?php endforeach; ?></section>
        <section class="p-4"><section class="row g-3"><article class="col-md-4 fw-semibold">Letchworth State Park</article><article class="col-md-4 text-muted">06/18/2026</article><article class="col-md-4 text-muted">7:30 AM – 3:30 PM</article></section></section>
      </section>
    </section>
  </main>
  <footer class="footer-shell py-5 mt-5">
    <section class="container">
      <section class="footer-five">
        <article class="footer-block footer-brand">
          <a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2 mb-3">
            <span class="brand-badge">NY</span>
            <span class="brand-mark text-dark">
              NYS Parks<br />
              <small>&amp; RECREATION</small>
            </span>
          </a>
          <p class="text-muted mb-0">
            A modern gateway to New York State parks, events, maps, news, and role-based operations.
          </p>
        </article>
        <article class="footer-block">
          <h2 class="h6 fw-bold mb-3">Explore</h2>
          <ul class="list-unstyled m-0">
            <li class="mb-2"><a href="parks.php" class="text-muted text-decoration-none">Parks</a></li>
            <li class="mb-2"><a href="events.php" class="text-muted text-decoration-none">Events</a></li>
            <li class="mb-2"><a href="map.php" class="text-muted text-decoration-none">Map</a></li>
            <li class="mb-2"><a href="ai.php" class="text-muted text-decoration-none">AI</a></li>
            <li class="mb-2"><a href="news.php" class="text-muted text-decoration-none">News</a></li>
          </ul>
        </article>
        <article class="footer-block">
          <h2 class="h6 fw-bold mb-3">Account</h2>
          <ul class="list-unstyled m-0">
            <li class="mb-2"><a href="about.php" class="text-muted text-decoration-none">About</a></li>
            <li class="mb-2"><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li>
            <li class="mb-2"><a href="donate.php" class="text-muted text-decoration-none">Donate</a></li>
            <li class="mb-2"><a href="login.php" class="text-muted text-decoration-none">Log In</a></li>
            <li class="mb-2"><a href="register.php" class="text-muted text-decoration-none">Register</a></li>
            <li class="mb-2"><a href="account.php" class="text-muted text-decoration-none">Account</a></li>
          </ul>
        </article>
        <article class="footer-block">
          <h2 class="h6 fw-bold mb-3">Portals</h2>
          <ul class="list-unstyled m-0">
            <li class="mb-2"><a href="client-dashboard.php" class="text-muted text-decoration-none">Client Dashboard</a></li>
            <li class="mb-2"><a href="admin-dashboard.php" class="text-muted text-decoration-none">Admin Dashboard</a></li>
            <li class="mb-2"><a href="employee-dashboard.php" class="text-muted text-decoration-none">Employee Dashboard</a></li>
          </ul>
        </article>
        <article class="footer-block">
          <h2 class="h6 fw-bold mb-3">Contact</h2>
          <p class="text-muted mb-2">info@nysparks.gov</p>
          <p class="text-muted mb-2">(555) 123-4567</p>
          <p class="text-muted mb-0">Albany, New York</p>
        </article>
      </section>
    </section>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="app.js"></script>
</body>
</html>