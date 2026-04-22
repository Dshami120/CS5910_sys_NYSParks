<?php
require 'bootstrap.php';
$user = require_role($db, 'employee');
$stmt = $db->prepare("SELECT s.*, p.name AS park_name FROM employee_schedules s JOIN parks p ON p.id=s.park_id WHERE s.employee_id=? ORDER BY s.shift_date ASC, s.start_time ASC");
$stmt->execute([$user['id']]); $schedules = $stmt->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Employee Schedule</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="employee-schedule">
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
          <li><a href="employee-dashboard.php" class="nav-link-custom" data-page-link="employee-dashboard"><i class="bi bi-speedometer2"></i>Employee Dash</a></li>
          <li><a href="employee-schedule.php" class="nav-link-custom active" data-page-link="employee-schedule"><i class="bi bi-calendar3"></i>Schedule</a></li>
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
      <header class="mb-4">
        <p class="section-kicker mb-2">Employee portal</p>
        <h1 class="h2 fw-bold mb-1">My Schedule</h1>
        <p class="text-muted mb-0">Employees can view their schedule here, but only admins can make schedule changes.</p>
      </header>

      <section class="row g-4">
        <article class="col-xl-8">
          <section class="list-shell">
            <section class="p-4 border-bottom">
              <section class="row g-3">
                <article class="col-md-4 fw-bold">Park</article>
                <article class="col-md-3 fw-bold">Date</article>
                <article class="col-md-3 fw-bold">Shift</article>
                <article class="col-md-2 fw-bold">Role</article>
              </section>
            </section>
            <?php foreach ($schedules as $shift): ?>
<section class="list-row p-4"><section class="row g-3"><article class="col-md-4 fw-semibold"><?= e($shift['park_name']) ?></article><article class="col-md-3 text-muted"><?= date('m/d/Y', strtotime($shift['shift_date'])) ?></article><article class="col-md-3 text-muted"><?= date('g:i A', strtotime($shift['start_time'])) ?> – <?= date('g:i A', strtotime($shift['end_time'])) ?></article><article class="col-md-2 text-muted"><?= e($shift['assignment']) ?></article></section></section>
<?php endforeach; ?></section>
          </section>
        </article>

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