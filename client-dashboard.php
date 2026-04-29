<?php
require 'bootstrap.php';
$user = require_role($db, 'client');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'pay_reservation') {
    $bookingId = (int) post('booking_id');
    $method = post('payment_method') ?: 'card';
    if (!in_array($method, ['card','in_person'], true)) {
        flash_set('error', 'Please choose a valid payment method.');
        redirect('client-dashboard.php');
    }
    $stmt = $db->prepare("SELECT b.*, u.first_name, u.last_name, u.email FROM bookings b JOIN users u ON u.id=b.client_id WHERE b.id=? AND b.client_id=?");
    $stmt->execute([$bookingId, $user['id']]);
    $booking = $stmt->fetch();
    if (!$booking) {
        flash_set('error', 'Booking was not found.');
        redirect('client-dashboard.php');
    }
    if (!in_array($booking['booking_status'], ['approved','confirmed'], true)) {
        flash_set('error', 'Reservation fees can only be paid after the booking is approved or confirmed.');
        redirect('client-dashboard.php');
    }
    $paidStmt = $db->prepare("SELECT COUNT(*) FROM payments WHERE booking_id=? AND payment_type='reservation' AND payment_status IN ('pending','completed')");
    $paidStmt->execute([$bookingId]);
    if ((int)$paidStmt->fetchColumn() > 0) {
        flash_set('error', 'This reservation already has a pending or completed payment record.');
        redirect('client-dashboard.php');
    }
    $cardNum = null; $month = null; $year = null; $cvv = null; $status = 'pending';
    if ($method === 'card') {
        $cardError = validate_card(post('card_number'), post('exp_month'), post('exp_year'), post('cvv'));
        if ($cardError) {
            flash_set('error', $cardError);
            redirect('client-dashboard.php');
        }
        $cardNum = number_only(post('card_number'));
        $month = (int) post('exp_month');
        $year = (int) post('exp_year');
        $cvv = number_only(post('cvv'));
        $status = 'completed';
    }
    $ref = 'RES-' . date('YmdHis') . '-' . random_int(1000,9999);
    $payerName = full_name($user);
    $db->prepare("INSERT INTO payments (user_id, booking_id, payment_type, payer_name, payer_email, amount, payment_method, card_num, exp_month, exp_year, cvv, payment_status, transaction_ref) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)")
       ->execute([$user['id'], $bookingId, 'reservation', $payerName, $user['email'], (float)$booking['reservation_fee'], $method, $cardNum, $month, $year, $cvv, $status, $ref]);
    flash_set('success', $method === 'card' ? 'Reservation fee paid by mock card.' : 'In-person reservation payment marked as pending. Bring ID when you come in.');
    redirect('client-dashboard.php');
}

$stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE client_id=? AND booking_status IN ('approved','confirmed')");
$stmt->execute([$user['id']]); $confirmedCount = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE client_id=? AND booking_status='pending'");
$stmt->execute([$user['id']]); $pendingCount = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT b.*, p.name AS park_name FROM bookings b JOIN parks p ON p.id=b.park_id WHERE b.client_id=? AND booking_status IN ('approved','confirmed') AND start_datetime >= NOW() ORDER BY start_datetime ASC LIMIT 1");
$stmt->execute([$user['id']]); $nextBooking = $stmt->fetch();
$stmt = $db->prepare("SELECT b.*, p.name AS park_name,
    (SELECT py.payment_status FROM payments py WHERE py.booking_id=b.id AND py.payment_type='reservation' ORDER BY py.created_at DESC LIMIT 1) AS payment_status,
    (SELECT py.payment_method FROM payments py WHERE py.booking_id=b.id AND py.payment_type='reservation' ORDER BY py.created_at DESC LIMIT 1) AS payment_method
    FROM bookings b JOIN parks p ON p.id=b.park_id WHERE b.client_id=? ORDER BY b.start_datetime DESC");
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
  <link rel="stylesheet" href="css/styles.css" />
</head>
<body data-page="client-dashboard">
<header class="site-header"><nav class="container py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3"><section class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 gap-lg-4"><a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a><ul class="list-unstyled d-flex flex-wrap gap-3 gap-lg-4 m-0 align-items-center"><li><a href="parks.php" class="nav-link-custom">Parks</a></li><li><a href="events.php" class="nav-link-custom">Events</a></li><li><a href="map.php" class="nav-link-custom">Map</a></li><li><a href="news.php" class="nav-link-custom">News</a></li><li><a href="donate.php" class="nav-link-custom">Donate</a></li><li><a href="client-dashboard.php" class="nav-link-custom active">Client Dash</a></li><li><a href="client-create-event.php" class="nav-link-custom">Create Event</a></li></ul></section><ul class="list-unstyled d-flex flex-wrap gap-3 m-0 align-items-center"><li><a href="account.php" class="nav-link-custom">Account</a></li><li><a href="logout.php" class="nav-link-custom"><i class="bi bi-box-arrow-right"></i>Logout</a></li></ul></nav></header>
<main class="py-5"><section class="container">
<?php if ($flash): ?><div class="alert alert-<?= $flash['type']==='error'?'danger':'success' ?> mb-4"><?= e($flash['message']) ?></div><?php endif; ?>
<section class="row g-4 mb-4"><article class="col-xl-8"><section class="soft-card p-4 p-lg-5 h-100"><div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-start"><div><p class="section-kicker mb-2">Client portal</p><h1 class="h2 fw-bold mb-1">Client Dashboard</h1><p class="text-muted mb-0">Track booking requests, approval status, reservation fees, and private event visibility.</p></div><a href="client-create-event.php" class="btn btn-success rounded-pill"><i class="bi bi-plus-circle me-1"></i>Create Event Request</a></div><section class="row g-3 mt-4"><article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Approved / Confirmed</p><h2 class="h3 fw-bold mb-2"><?= $confirmedCount ?></h2><p class="text-muted mb-0">These become public-facing private events.</p></section></article><article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Pending requests</p><h2 class="h3 fw-bold mb-2"><?= $pendingCount ?></h2><p class="text-muted mb-0">Pending requests block overlapping slots.</p></section></article><article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Next event</p><h2 class="h5 fw-bold mb-2"><?= $nextBooking ? date('M d', strtotime($nextBooking['start_datetime'])) : '—' ?></h2><p class="text-muted mb-0"><?= $nextBooking ? e($nextBooking['title']) . ' at ' . e($nextBooking['park_name']) : 'No upcoming approved event.' ?></p></section></article></section></section></article><article class="col-xl-4"><section class="soft-card p-4 h-100"><h2 class="h5 fw-bold mb-3">Payment rules</h2><p class="text-muted mb-3">Reservation fees can be paid after admin approval by mock card or marked pending for in-person payment. You still bring ID in person.</p><a href="events.php" class="action-card text-decoration-none d-block"><strong>Browse public calendar</strong><span>Approved private bookings appear with a Private label.</span></a></section></article></section>
<section class="client-chart-panel mb-4 p-4 rounded-4 bg-light border"><div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4"><div><p class="client-section-kicker mb-1">Bookings Overview</p><h2 class="client-section-title mb-0">My Booking Activity</h2></div><div class="client-chart-filters d-flex flex-wrap gap-3"><div><label class="form-label small text-uppercase text-muted mb-1" for="clientChartRange">View By</label><select class="form-select client-filter-select" id="clientChartRange"><option value="day">Day</option><option value="week">Week</option><option value="month" selected>Month</option><option value="year">Year</option></select></div><div><label class="form-label small text-uppercase text-muted mb-1" for="clientChartStatus">Status</label><select class="form-select client-filter-select" id="clientChartStatus"><option value="all" selected>All Statuses</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="confirmed">Confirmed</option><option value="completed">Completed</option><option value="denied">Denied</option><option value="cancelled">Cancelled</option></select></div></div></div><div class="client-chart-box mb-4 p-3 bg-white rounded-4 border"><canvas id="clientBookingsChart" height="130"></canvas></div><div class="row g-3"><div class="col-md-4"><article class="client-metric-card"><span class="client-metric-label">Pending</span><strong class="client-metric-value"><?= (int)($bookingStatusCounts['pending'] ?? 0) ?></strong></article></div><div class="col-md-4"><article class="client-metric-card"><span class="client-metric-label">Approved / Confirmed</span><strong class="client-metric-value"><?= (int)(($bookingStatusCounts['approved'] ?? 0) + ($bookingStatusCounts['confirmed'] ?? 0)) ?></strong></article></div><div class="col-md-4"><article class="client-metric-card"><span class="client-metric-label">Completed / Closed</span><strong class="client-metric-value"><?= (int)(($bookingStatusCounts['completed'] ?? 0) + ($bookingStatusCounts['denied'] ?? 0) + ($bookingStatusCounts['cancelled'] ?? 0)) ?></strong></article></div></div></section>
<section class="list-shell mb-4"><section class="p-4 border-bottom"><section class="row g-3"><article class="col-lg-2 fw-bold">Event</article><article class="col-lg-2 fw-bold">Park</article><article class="col-lg-2 fw-bold">Date / Time</article><article class="col-lg-1 fw-bold">Status</article><article class="col-lg-1 fw-bold">Fee</article><article class="col-lg-2 fw-bold">Payment</article><article class="col-lg-2 fw-bold">Pay Reservation</article></section></section><?php foreach ($bookings as $booking): ?><section class="list-row p-4"><section class="row g-3 align-items-start"><article class="col-lg-2 fw-semibold"><?= e($booking['title']) ?></article><article class="col-lg-2 text-muted"><?= e($booking['park_name']) ?></article><article class="col-lg-2 text-muted"><?= date('m/d/Y g:i A', strtotime($booking['start_datetime'])) ?></article><article class="col-lg-1"><span class="status-pill <?= booking_status_class($booking['booking_status']) ?>"><?= e(ucfirst($booking['booking_status'])) ?></span></article><article class="col-lg-1 text-muted">$<?= number_format((float)$booking['reservation_fee'], 0) ?></article><article class="col-lg-2 text-muted"><?php if ($booking['payment_status']): ?><span class="status-pill <?= booking_status_class($booking['payment_status']) ?>"><?= e(ucfirst($booking['payment_status'])) ?></span><br><small><?= e(str_replace('_',' ', (string)$booking['payment_method'])) ?></small><?php else: ?>Not paid<?php endif; ?></article><article class="col-lg-2"><?php if (!$booking['payment_status'] && in_array($booking['booking_status'], ['approved','confirmed'], true)): ?><form method="post" class="small reservation-pay-form"><input type="hidden" name="action" value="pay_reservation"><input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>"><select name="payment_method" class="form-select form-select-sm mb-2 reservation-method"><option value="card">Mock card</option><option value="in_person">In person</option></select><div class="reservation-card-fields"><input name="card_number" class="form-control form-control-sm mb-1" placeholder="Card number"><section class="d-flex gap-1"><input name="exp_month" class="form-control form-control-sm" placeholder="MM"><input name="exp_year" class="form-control form-control-sm" placeholder="YYYY"><input name="cvv" class="form-control form-control-sm" placeholder="CVV"></section></div><button class="btn btn-sm btn-success rounded-pill mt-2" type="submit">Submit</button></form><?php elseif (!$booking['payment_status']): ?><span class="text-muted small">Available after approval.</span><?php else: ?><span class="text-muted small">Payment recorded.</span><?php endif; ?></article></section></section><?php endforeach; ?><?php if (!$bookings): ?><section class="p-4 text-muted">No booking requests yet.</section><?php endif; ?></section>
</section></main>
<footer class="footer-shell py-5 mt-5"><section class="container"><p class="text-muted mb-0">NYS Parks &amp; Recreation</p></section></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="js/dashboard-charts.js"></script>
<script>
window.initClientBookingChart(<?= json_encode($bookingChartRows, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>);
window.initReservationPaymentForms();
</script>
<script src="js/app.js"></script>
</body>
</html>
