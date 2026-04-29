<?php
require 'bootstrap.php';
$user = require_role($db, 'client');
$stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE client_id=? AND booking_status IN ('approved','confirmed')");
$stmt->execute([$user['id']]); $confirmedCount = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE client_id=? AND booking_status='pending'");
$stmt->execute([$user['id']]); $pendingCount = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT b.*, p.name AS park_name FROM bookings b JOIN parks p ON p.id=b.park_id WHERE b.client_id=? AND booking_status IN ('approved','confirmed') AND start_datetime >= NOW() ORDER BY start_datetime ASC LIMIT 1");
$stmt->execute([$user['id']]); $nextBooking = $stmt->fetch();
$stmt = $db->prepare("SELECT b.*, p.name AS park_name FROM bookings b JOIN parks p ON p.id=b.park_id WHERE b.client_id=? ORDER BY b.start_datetime DESC");
$stmt->execute([$user['id']]); $bookings = $stmt->fetchAll();
$bookingChartRows = array_map(fn($b) => ['date' => substr((string)$b['start_datetime'], 0, 10), 'status' => $b['booking_status'], 'park' => $b['park_name'], 'title' => $b['title']], $bookings);
$bookingStatusCounts = array_count_values(array_map(fn($b) => $b['booking_status'], $bookings));
?>
<!DOCTYPE html>
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
  <header class="site-header"><nav class="container py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3"><section class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 gap-lg-4"><a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a><ul class="list-unstyled d-flex flex-wrap gap-3 gap-lg-4 m-0 align-items-center"><li><a href="parks.php" class="nav-link-custom">Parks</a></li><li><a href="events.php" class="nav-link-custom">Events</a></li><li><a href="map.php" class="nav-link-custom">Map</a></li><li><a href="news.php" class="nav-link-custom">News</a></li><li><a href="client-dashboard.php" class="nav-link-custom active">Client Dash</a></li><li><a href="client-create-event.php" class="nav-link-custom">Create Event</a></li></ul></section><ul class="list-unstyled d-flex flex-wrap gap-3 m-0 align-items-center"><li><a href="account.php" class="nav-link-custom">Account</a></li><li><a href="logout.php" class="nav-link-custom"><i class="bi bi-box-arrow-right"></i>Logout</a></li></ul></nav></header>
  <main class="py-5"><section class="container">
    <?php if ($flash): ?><div class="alert alert-<?= $flash['type']==='error'?'danger':'success' ?> mb-4"><?= e($flash['message']) ?></div><?php endif; ?>
    <section class="row g-4 mb-4"><article class="col-xl-8"><section class="soft-card p-4 p-lg-5 h-100"><div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-start"><div><p class="section-kicker mb-2">Client portal</p><h1 class="h2 fw-bold mb-1">Client Dashboard</h1><p class="text-muted mb-0">All booking requests, approvals, cancellations, and completed events in one place.</p></div><a href="client-create-event.php" class="btn btn-success rounded-pill"><i class="bi bi-plus-circle me-1"></i>Create Event Request</a></div><section class="row g-3 mt-4"><article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Approved / Confirmed</p><h2 class="h3 fw-bold mb-2"><?= $confirmedCount ?></h2><p class="text-muted mb-0">Approved client bookings.</p></section></article><article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Pending requests</p><h2 class="h3 fw-bold mb-2"><?= $pendingCount ?></h2><p class="text-muted mb-0">Pending requests also block overlapping slots.</p></section></article><article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Next event</p><h2 class="h5 fw-bold mb-2"><?= $nextBooking ? date('M d', strtotime($nextBooking['start_datetime'])) : '—' ?></h2><p class="text-muted mb-0"><?= $nextBooking ? e($nextBooking['title']) . ' at ' . e($nextBooking['park_name']) : 'No upcoming approved event.' ?></p></section></article></section></section></article><article class="col-xl-4"><section class="soft-card p-4 h-100"><h2 class="h5 fw-bold mb-3">Quick actions</h2><a href="client-create-event.php" class="action-card mb-3 text-decoration-none d-block"><strong>New booking request</strong><span>Start a new event request for review.</span></a><a href="account.php" class="action-card mb-3 text-decoration-none d-block"><strong>Update account</strong><span>Edit contact details.</span></a><a href="events.php" class="action-card text-decoration-none d-block"><strong>Browse events</strong><span>See the public and private event calendar.</span></a></section></article></section>

    <section class="client-chart-panel mb-4 p-4 rounded-4 bg-light border"><div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4"><div><p class="client-section-kicker mb-1">Bookings Overview</p><h2 class="client-section-title mb-0">My Booking Activity</h2></div><div class="client-chart-filters d-flex flex-wrap gap-3"><div><label class="form-label small text-uppercase text-muted mb-1" for="clientChartRange">View By</label><select class="form-select client-filter-select" id="clientChartRange"><option value="day">Day</option><option value="week">Week</option><option value="month" selected>Month</option><option value="year">Year</option></select></div><div><label class="form-label small text-uppercase text-muted mb-1" for="clientChartStatus">Status</label><select class="form-select client-filter-select" id="clientChartStatus"><option value="all" selected>All Statuses</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="confirmed">Confirmed</option><option value="completed">Completed</option><option value="denied">Denied</option><option value="cancelled">Cancelled</option></select></div></div></div><div class="client-chart-box mb-4 p-3 bg-white rounded-4 border"><canvas id="clientBookingsChart" height="130"></canvas></div><div class="row g-3"><div class="col-md-4"><article class="client-metric-card"><span class="client-metric-label">Pending</span><strong class="client-metric-value"><?= (int)($bookingStatusCounts['pending'] ?? 0) ?></strong></article></div><div class="col-md-4"><article class="client-metric-card"><span class="client-metric-label">Approved / Confirmed</span><strong class="client-metric-value"><?= (int)(($bookingStatusCounts['approved'] ?? 0) + ($bookingStatusCounts['confirmed'] ?? 0)) ?></strong></article></div><div class="col-md-4"><article class="client-metric-card"><span class="client-metric-label">Completed / Closed</span><strong class="client-metric-value"><?= (int)(($bookingStatusCounts['completed'] ?? 0) + ($bookingStatusCounts['denied'] ?? 0) + ($bookingStatusCounts['cancelled'] ?? 0)) ?></strong></article></div></div></section>

    <section class="list-shell mb-4"><section class="p-4 border-bottom"><section class="row g-3"><article class="col-lg-2 fw-bold">Event</article><article class="col-lg-2 fw-bold">Park</article><article class="col-lg-2 fw-bold">Date</article><article class="col-lg-2 fw-bold">Time</article><article class="col-lg-2 fw-bold">Status</article><article class="col-lg-2 fw-bold">Fee</article></section></section><?php foreach ($bookings as $booking): ?><section class="list-row p-4"><section class="row g-3 align-items-center"><article class="col-lg-2 fw-semibold"><?= e($booking['title']) ?></article><article class="col-lg-2 text-muted"><?= e($booking['park_name']) ?></article><article class="col-lg-2 text-muted"><?= date('m/d/Y', strtotime($booking['start_datetime'])) ?></article><article class="col-lg-2 text-muted"><?= date('g:i A', strtotime($booking['start_datetime'])) ?></article><article class="col-lg-2"><span class="status-pill <?= booking_status_class($booking['booking_status']) ?>"><?= e(ucfirst($booking['booking_status'])) ?></span></article><article class="col-lg-2 text-muted">$<?= number_format((float)$booking['reservation_fee'], 0) ?></article></section></section><?php endforeach; ?><?php if (!$bookings): ?><section class="p-4 text-muted">No booking requests yet.</section><?php endif; ?></section>
  </section></main>
  <footer class="footer-shell py-5 mt-5"><section class="container"><section class="footer-five"><article class="footer-block footer-brand"><a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2 mb-3"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a><p class="text-muted mb-0">A modern gateway to New York State parks, events, maps, news, and role-based operations.</p></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Explore</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="parks.php" class="text-muted text-decoration-none">Parks</a></li><li class="mb-2"><a href="events.php" class="text-muted text-decoration-none">Events</a></li><li class="mb-2"><a href="news.php" class="text-muted text-decoration-none">News</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Account</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="account.php" class="text-muted text-decoration-none">Account</a></li><li class="mb-2"><a href="logout.php" class="text-muted text-decoration-none">Logout</a></li></ul></article></section></section></footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    const clientBookingData = <?= json_encode($bookingChartRows, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
    const rangeSelect = document.getElementById('clientChartRange');
    const statusSelect = document.getElementById('clientChartStatus');
    const ctx = document.getElementById('clientBookingsChart');
    function groupKey(dateString, range) {
      const d = new Date(dateString + 'T00:00:00');
      if (range === 'day') return d.toLocaleDateString(undefined, {month:'short', day:'numeric'});
      if (range === 'week') { const first = new Date(d); first.setDate(d.getDate() - d.getDay()); return 'Week of ' + first.toLocaleDateString(undefined, {month:'short', day:'numeric'}); }
      if (range === 'year') return String(d.getFullYear());
      return d.toLocaleDateString(undefined, {month:'short', year:'numeric'});
    }
    function chartPayload() {
      const range = rangeSelect.value;
      const status = statusSelect.value;
      const grouped = new Map();
      clientBookingData.filter(row => status === 'all' || row.status === status).forEach(row => {
        const key = groupKey(row.date, range);
        grouped.set(key, (grouped.get(key) || 0) + 1);
      });
      return { labels: [...grouped.keys()], values: [...grouped.values()] };
    }
    const chart = new Chart(ctx, { type: 'bar', data: { labels: [], datasets: [{ label: 'Bookings', data: [] }] }, options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
    function renderChart(){ const p = chartPayload(); chart.data.labels = p.labels; chart.data.datasets[0].data = p.values; chart.update(); }
    rangeSelect.addEventListener('change', renderChart); statusSelect.addEventListener('change', renderChart); renderChart();
  </script>
  <script src="app.js"></script>
</body>
</html>
