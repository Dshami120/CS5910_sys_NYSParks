<?php
require 'bootstrap.php';
$user = require_role($db, 'client');
$stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE client_id=? AND booking_status IN ('approved','confirmed')");
$stmt->execute([$user['id']]); $confirmedCount = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE client_id=? AND booking_status='pending'");
$stmt->execute([$user['id']]); $pendingCount = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT * FROM bookings WHERE client_id=? AND booking_status IN ('approved','confirmed') ORDER BY start_datetime ASC LIMIT 1");
$stmt->execute([$user['id']]); $nextBooking = $stmt->fetch();
$stmt = $db->prepare("SELECT b.*, p.name AS park_name FROM bookings b JOIN parks p ON p.id=b.park_id WHERE b.client_id=? ORDER BY b.created_at DESC");
$stmt->execute([$user['id']]); $bookings = $stmt->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Client Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="client-dashboard">
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
          <li><a href="client-dashboard.php" class="nav-link-custom active" data-page-link="client-dashboard"><i class="bi bi-speedometer2"></i>Client Dash</a></li>
          <li><a href="client-create-event.php" class="nav-link-custom" data-page-link="client-create-event"><i class="bi bi-plus-circle"></i>Create Event</a></li>
        </ul>
      </section>
      <ul class="list-unstyled d-flex flex-wrap gap-3 m-0 align-items-center">
        <li><a href="account.php" class="nav-link-custom" data-page-link="account"><i class="bi bi-person-circle"></i>Account</a></li>
          <li><a href="logout.php" class="nav-link-custom" data-page-link="logout"><i class="bi bi-box-arrow-right"></i>Logout</a></li>
      </ul>
    </nav>
  </header>
  <main class="py-5">
    <section class="container">
      <section class="row g-4 mb-4">
        <article class="col-xl-8">
          <section class="soft-card p-4 p-lg-5 h-100">
            <section class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-start">
              <section>
                <p class="section-kicker mb-2">Client portal</p>
                <h1 class="h2 fw-bold mb-1">Client Dashboard</h1>
                <p class="text-muted mb-0">Booked events, pending booking requests, and account shortcuts in one place.</p>
              </section>
              <a href="client-create-event.php" class="btn btn-success rounded-pill">
                <i class="bi bi-plus-circle me-1"></i>Create Event Request
              </a>
            </section>

            <section class="row g-3 mt-4">
              <article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Confirmed events</p><h2 class="h3 fw-bold mb-2"><?= $confirmedCount ?></h2><p class="text-muted mb-0">Pulled from approved booking rows.</p></section></article>
              <article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Pending requests</p><h2 class="h3 fw-bold mb-2"><?= $pendingCount ?></h2><p class="text-muted mb-0">Awaiting admin review.</p></section></article>
              <article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Next event</p><h2 class="h5 fw-bold mb-2"><?= $nextBooking ? date('M d', strtotime($nextBooking['start_datetime'])) : '—' ?></h2><p class="text-muted mb-0"><?= $nextBooking ? e($nextBooking['title']) . ' at ' . e($nextBooking['park_name'] ?? '') : 'No confirmed events yet.' ?></p></section></article>
            </section>
          </section>
        </article>
        <article class="col-xl-4">
          <section class="soft-card p-4 h-100">
            <h2 class="h5 fw-bold mb-3">Quick actions</h2>
            <a href="client-create-event.php" class="action-card mb-3 text-decoration-none d-block"><strong>New booking request</strong><span>Start a new event request for review.</span></a>
            <a href="account.php" class="action-card mb-3 text-decoration-none d-block"><strong>Update account</strong><span>Edit contact details and preferred parks.</span></a>
            <a href="events.php" class="action-card text-decoration-none d-block"><strong>Browse events</strong><span>See public events and inspiration for new requests.</span></a>
          </section>
        </article>
      
      <section class="client-chart-panel mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
          <div>
            <p class="client-section-kicker mb-1">Bookings Overview</p>
            <h2 class="client-section-title mb-0">My Booking Activity</h2>
          </div>
          <div class="client-chart-filters">
            <div>
              <label class="form-label small text-uppercase text-muted mb-1">View By</label>
              <select class="form-select client-filter-select">
                <option>Week</option>
                <option selected>Month</option>
                <option>Quarter</option>
                <option>Year</option>
              </select>
            </div>
            <div>
              <label class="form-label small text-uppercase text-muted mb-1">Status</label>
              <select class="form-select client-filter-select">
                <option selected>All Statuses</option>
                <option>Pending</option>
                <option>Approved</option>
                <option>Declined</option>
              </select>
            </div>
          </div>
        </div>

        <div class="client-chart-box mb-4">
          <div class="client-chart-grid">
            <div class="client-chart-item">
              <span class="client-chart-label">Jan</span>
              <div class="client-chart-track"><div class="client-chart-fill" style="height: 36%;"></div></div>
            </div>
            <div class="client-chart-item">
              <span class="client-chart-label">Feb</span>
              <div class="client-chart-track"><div class="client-chart-fill" style="height: 54%;"></div></div>
            </div>
            <div class="client-chart-item">
              <span class="client-chart-label">Mar</span>
              <div class="client-chart-track"><div class="client-chart-fill" style="height: 74%;"></div></div>
            </div>
            <div class="client-chart-item">
              <span class="client-chart-label">Apr</span>
              <div class="client-chart-track"><div class="client-chart-fill" style="height: 61%;"></div></div>
            </div>
            <div class="client-chart-item">
              <span class="client-chart-label">May</span>
              <div class="client-chart-track"><div class="client-chart-fill" style="height: 82%;"></div></div>
            </div>
            <div class="client-chart-item">
              <span class="client-chart-label">Jun</span>
              <div class="client-chart-track"><div class="client-chart-fill" style="height: 46%;"></div></div>
            </div>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-4">
            <article class="client-metric-card">
              <span class="client-metric-label">Pending</span>
              <strong class="client-metric-value">2</strong>
            </article>
          </div>
          <div class="col-md-4">
            <article class="client-metric-card">
              <span class="client-metric-label">Approved</span>
              <strong class="client-metric-value">6</strong>
            </article>
          </div>
          <div class="col-md-4">
            <article class="client-metric-card">
              <span class="client-metric-label">Declined</span>
              <strong class="client-metric-value">1</strong>
            </article>
          </div>
        </div>
      </section>

      <section class="list-shell mb-4">
        <section class="p-4 border-bottom">
          <section class="row g-3">
            <article class="col-lg-2 fw-bold">Event</article>
            <article class="col-lg-2 fw-bold">Park</article>
            <article class="col-lg-2 fw-bold">Date</article>
            <article class="col-lg-2 fw-bold">Attendees</article>
            <article class="col-lg-2 fw-bold">Fee</article>
            <article class="col-lg-2 fw-bold">Status</article>
          </section>
        </section>
        <?php foreach ($bookings as $booking): ?>
<section class="list-row p-4">
  <section class="row g-3 align-items-center">
    <article class="col-lg-2 fw-semibold"><?= e($booking['title']) ?></article>
    <article class="col-lg-2 text-muted"><?= e($booking['park_name']) ?></article>
    <article class="col-lg-2 text-muted"><?= date('m/d/Y', strtotime($booking['start_datetime'])) ?></article>
    <article class="col-lg-2 text-muted"><?= date('g:i A', strtotime($booking['start_datetime'])) ?></article>
    <article class="col-lg-2"><span class="status-pill <?= booking_status_class($booking['booking_status']) ?>"><?= e(ucfirst($booking['booking_status'])) ?></span></article>
    <article class="col-lg-2 text-muted">$<?= number_format((float)$booking['reservation_fee'], 0) ?></article>
  </section>
</section>
<?php endforeach; ?>
<section class="note-box">
        The booking table here represents what the client would see once requests and approvals are loaded from the bookings SQL table.
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