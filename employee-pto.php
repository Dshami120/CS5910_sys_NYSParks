<?php
require 'bootstrap.php';
$user = require_role($db, 'employee');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave = post('leave_type');
    $start = post('start_date');
    $end = post('end_date');
    $reason = post('reason');
    if (!$leave || !$start || !$end) {
        flash_set('error', 'Complete the PTO form.');
        redirect('employee-pto.php');
    }
    if ($start > $end) {
        flash_set('error', 'End date must be on or after start date.');
        redirect('employee-pto.php');
    }
    $dup = $db->prepare("SELECT COUNT(*) FROM pto_requests WHERE employee_id=? AND pto_status IN ('pending','approved') AND NOT (end_date < ? OR start_date > ?)");
    $dup->execute([$user['id'], $start, $end]);
    if ((int) $dup->fetchColumn() > 0) {
        flash_set('error', 'That PTO range overlaps an existing request.');
        redirect('employee-pto.php');
    }
    $db->prepare("INSERT INTO pto_requests (employee_id, leave_type, start_date, end_date, reason, pto_status) VALUES (?,?,?,?,?, 'pending')")
       ->execute([$user['id'], $leave, $start, $end, $reason ?: null]);
    flash_set('success', 'PTO request submitted.');
    redirect('employee-pto.php');
}
$stmt = $db->prepare("SELECT * FROM pto_requests WHERE employee_id=? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Employee PTO</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="employee-pto">
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
          <li><a href="employee-schedule.php" class="nav-link-custom" data-page-link="employee-schedule"><i class="bi bi-calendar3"></i>Schedule</a></li>
          <li><a href="employee-pto.php" class="nav-link-custom active" data-page-link="employee-pto"><i class="bi bi-briefcase"></i>PTO</a></li>
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
        <h1 class="h2 fw-bold mb-1">Create PTO Request</h1>
        <p class="text-muted mb-0">Submit new time-off requests and check the status of recent submissions.</p>
      </header>

      <section class="row g-4">
        <article class="col-xl-5">
          <section class="form-panel p-4 h-100">
            <h2 class="h5 fw-bold mb-3">New PTO Request</h2>
            <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>"><?= e($flash['message']) ?></div><?php endif; ?>
            <form method="post">
              <label class="form-label">Leave type</label>
              <select name="leave_type" class="form-select mb-3"><option>Vacation</option><option>Sick Leave</option><option>Personal Leave</option><option>Bereavement</option></select>
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

        <article class="col-xl-7">
          <section class="list-shell">
            <section class="p-4 border-bottom">
              <section class="row g-3">
                <article class="col-md-3 fw-bold">Dates</article>
                <article class="col-md-3 fw-bold">Type</article>
                <article class="col-md-3 fw-bold">Submitted</article>
                <article class="col-md-3 fw-bold">Status</article>
              </section>
            </section>
            <?php foreach ($requests as $i => $row): ?>
            <section class="<?= $i < count($requests) - 1 ? 'list-row' : '' ?> p-4"><section class="row g-3 align-items-center"><article class="col-md-3"><?= format_date($row['start_date']) ?><?= $row['start_date'] !== $row['end_date'] ? '–' . format_date($row['end_date']) : '' ?></article><article class="col-md-3 text-muted"><?= e($row['leave_type']) ?></article><article class="col-md-3 text-muted"><?= format_date($row['created_at']) ?></article><article class="col-md-3"><span class="status-pill <?= booking_status_class($row['pto_status']) ?>"><?= e(ucfirst($row['pto_status'])) ?></span></article></section></section>
            <?php endforeach; ?>
            <?php if (!$requests): ?><section class="p-4"><section class="row g-3 align-items-center"><article class="col-12 text-muted">No PTO requests yet.</article></section></section><?php endif; ?>
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
