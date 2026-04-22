<?php
require 'bootstrap.php';
$user = require_role($db, 'client');
$parks = park_options($db);
$fields = $db->query("SELECT f.*, p.name AS park_name FROM fields f JOIN parks p ON p.id=f.park_id WHERE availability_status='available' ORDER BY p.name, f.name")->fetchAll();
$fieldMap = [];
foreach ($fields as $field) {
    $fieldMap[(int)$field['park_id']][] = $field;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = post('title');
    $parkId = (int) post('park_id');
    $fieldId = (int) post('field_id');
    $bookingType = post('booking_type') ?: 'Community';
    $date = post('booking_date');
    $start = post('start_time');
    $end = post('end_time');
    $guestCount = max(1, (int) post('guest_count'));
    $setup = post('requested_setup');
    $desc = post('event_description');
    $special = post('special_requests');
    if (!$title || !$parkId || !$date || !$start || !$end || !$desc) {
        flash_set('error', 'Please complete all required fields.');
        redirect('client-create-event.php');
    }
    $startDt = $date . ' ' . $start . ':00';
    $endDt = $date . ' ' . $end . ':00';
    if (strtotime($startDt) >= strtotime($endDt)) {
        flash_set('error', 'End time must be after start time.');
        redirect('client-create-event.php');
    }
    if (strtotime($startDt) <= time()) {
        flash_set('error', 'Booking must be in the future.');
        redirect('client-create-event.php');
    }
    if ($fieldId > 0) {
        $overlap = $db->prepare("SELECT COUNT(*) FROM bookings WHERE field_id=? AND booking_status IN ('pending','approved','confirmed') AND NOT (end_datetime <= ? OR start_datetime >= ?)");
        $overlap->execute([$fieldId, $startDt, $endDt]);
        if ((int) $overlap->fetchColumn() > 0) {
            flash_set('error', 'That field already has an overlapping booking.');
            redirect('client-create-event.php');
        }
    }
    $fee = max(25, $guestCount * 4);
    $db->prepare("INSERT INTO bookings (client_id, park_id, field_id, title, booking_type, attendee_email, start_datetime, end_datetime, guest_count, requested_setup, event_description, special_requests, reservation_fee, booking_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?, 'pending')")
       ->execute([$user['id'], $parkId, $fieldId ?: null, $title, $bookingType, $user['email'], $startDt, $endDt, $guestCount, $setup ?: null, $desc, $special ?: null, $fee]);
    flash_set('success', 'Booking request submitted.');
    redirect('client-create-event.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Client Create Event</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="client-create-event">
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
          <li><a href="client-dashboard.php" class="nav-link-custom" data-page-link="client-dashboard"><i class="bi bi-speedometer2"></i>Client Dash</a></li>
          <li><a href="client-create-event.php" class="nav-link-custom active" data-page-link="client-create-event"><i class="bi bi-plus-circle"></i>Create Event</a></li>
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
      <section class="row g-4">
        <article class="col-xl-8">
          <section class="feature-panel p-4 mb-4">
            <p class="mb-1 fw-semibold">Client portal</p>
            <h1 class="h3 fw-bold mb-1">Create Booking Request</h1>
            <p class="mb-0 text-white-50">Clients submit requests here. Admins approve or deny them in the bookings queue.</p>
          </section>

          <section class="form-panel p-4 p-lg-5">
            <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> mb-4"><?= e($flash['message']) ?></div><?php endif; ?>
            <form method="post">
              <section class="row g-4">
                <article class="col-12">
                  <label class="form-label">Event title</label>
                  <input type="text" name="title" class="form-control form-control-lg" placeholder="e.g. Sunset Jazz Festival" required />
                </article>
                <article class="col-lg-6">
                  <label class="form-label">Park location</label>
                  <select name="park_id" class="form-select form-select-lg" required>
                    <?php foreach ($parks as $park): ?>
                    <option value="<?= $park['id'] ?>"><?= e($park['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </article>
                <article class="col-lg-6">
                  <label class="form-label">Event type</label>
                  <select name="booking_type" class="form-select form-select-lg">
                    <option>Music</option>
                    <option>Festival</option>
                    <option>Fitness</option>
                    <option>Community</option>
                    <option>Sports</option>
                  </select>
                </article>
                <article class="col-lg-4">
                  <label class="form-label">Date</label>
                  <input type="date" name="booking_date" class="form-control form-control-lg" required />
                </article>
                <article class="col-lg-4">
                  <label class="form-label">Start time</label>
                  <input type="time" name="start_time" class="form-control form-control-lg" required />
                </article>
                <article class="col-lg-4">
                  <label class="form-label">End time</label>
                  <input type="time" name="end_time" class="form-control form-control-lg" required />
                </article>
                <article class="col-lg-6">
                  <label class="form-label">Estimated attendees</label>
                  <input type="number" name="guest_count" class="form-control form-control-lg" placeholder="150" min="1" required />
                </article>
                <article class="col-lg-6">
                  <label class="form-label">Requested setup</label>
                  <select name="requested_setup" class="form-select form-select-lg">
                    <option>Stage + sound</option>
                    <option>Tent area</option>
                    <option>Sports field</option>
                    <option>Picnic shelter</option>
                  </select>
                </article>
                <article class="col-12">
                  <label class="form-label">Preferred field or area</label>
                  <select name="field_id" class="form-select form-select-lg">
                    <option value="">No specific field</option>
                    <?php foreach ($fields as $field): ?>
                    <option value="<?= $field['id'] ?>"><?= e($field['park_name']) ?> — <?= e($field['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </article>
                <article class="col-12">
                  <label class="form-label">Event description</label>
                  <textarea rows="5" name="event_description" class="form-control form-control-lg" placeholder="Purpose, audience, staffing, and setup notes" required></textarea>
                </article>
                <article class="col-12">
                  <label class="form-label">Special requests</label>
                  <textarea rows="3" name="special_requests" class="form-control" placeholder="Security, parking, power, permits, or ADA notes"></textarea>
                </article>
              </section>
              <section class="d-flex flex-wrap gap-2 mt-4">
                <button type="submit" class="btn btn-success rounded-pill px-4">Submit Booking Request</button>
                <a href="client-dashboard.php" class="btn btn-outline-dark rounded-pill px-4">Back to Dashboard</a>
              </section>
            </form>
          </section>
        </article>

        <article class="col-xl-4">
          <section class="soft-card p-4 h-100">
            <h2 class="h5 fw-bold mb-3">How this flows</h2>
            <ol class="text-muted ps-3 mb-4">
              <li class="mb-2">Client submits a booking request.</li>
              <li class="mb-2">Admin reviews it in the bookings queue.</li>
              <li class="mb-2">Admin approves or denies the request.</li>
              <li class="mb-0">Approved requests appear on the client dashboard.</li>
            </ol>
            <section class="note-box">
              This page now writes directly to the live bookings table and checks for overlapping field requests.
            </section>
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
