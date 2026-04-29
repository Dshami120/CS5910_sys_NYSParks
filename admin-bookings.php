<?php
require 'bootstrap.php';
$user = require_role($db, 'admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) post('booking_id');
    $decision = post('decision');
    $validDecisions = ['approved','confirmed','completed','denied','cancelled'];
    if (!in_array($decision, $validDecisions, true)) {
        flash_set('error', 'Invalid booking action.');
        redirect('admin-bookings.php');
    }
    $stmt = $db->prepare("SELECT * FROM bookings WHERE id=?");
    $stmt->execute([$id]);
    $booking = $stmt->fetch();
    if (!$booking) {
        flash_set('error', 'Booking not found.');
        redirect('admin-bookings.php');
    }
    $db->beginTransaction();
    try {
        $eventId = $booking['event_id'] ? (int)$booking['event_id'] : null;
        if (in_array($decision, ['approved','confirmed','completed'], true) && !$eventId) {
            $insert = $db->prepare("INSERT INTO events (park_id, field_id, title, description, card_summary, category, event_type, start_datetime, end_datetime, capacity, fee_amount, event_status, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $eventStatus = $decision === 'completed' ? 'completed' : 'published';
            $insert->execute([(int)$booking['park_id'], $booking['field_id'] ?: null, $booking['title'], $booking['event_description'], $booking['requested_setup'] ?: null, $booking['booking_type'], 'private', $booking['start_datetime'], $booking['end_datetime'], (int)$booking['guest_count'], (float)$booking['reservation_fee'], $eventStatus, (int)$user['id']]);
            $eventId = (int)$db->lastInsertId();
        } elseif ($eventId) {
            if (in_array($decision, ['approved','confirmed'], true)) {
                $db->prepare("UPDATE events SET event_status='published', updated_at=NOW() WHERE id=?")->execute([$eventId]);
            } elseif ($decision === 'completed') {
                $db->prepare("UPDATE events SET event_status='completed', updated_at=NOW() WHERE id=?")->execute([$eventId]);
            } elseif (in_array($decision, ['denied','cancelled'], true)) {
                $db->prepare("UPDATE events SET event_status='cancelled', updated_at=NOW() WHERE id=?")->execute([$eventId]);
            }
        }
        $db->prepare("UPDATE bookings SET booking_status=?, event_id=?, reviewed_by=?, reviewed_at=NOW(), admin_notes=? WHERE id=?")
           ->execute([$decision, $eventId, $user['id'], post('admin_notes') ?: null, $id]);
        $db->commit();
        flash_set('success', 'Booking updated. Linked private event was created or updated when required.');
    } catch (Throwable $e) {
        $db->rollBack();
        flash_set('error', $e->getMessage());
    }
    redirect('admin-bookings.php');
}
$pending = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE booking_status='pending'")->fetchColumn();
$approvedToday = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE booking_status IN ('approved','confirmed') AND DATE(reviewed_at)=CURDATE()")->fetchColumn();
$feesQueued = (float) $db->query("SELECT COALESCE(SUM(reservation_fee),0) FROM bookings WHERE booking_status='pending'")->fetchColumn();
$rows = $db->query("SELECT b.*, p.name AS park_name, CONCAT(u.first_name,' ',u.last_name) AS requester, (SELECT py.payment_status FROM payments py WHERE py.booking_id=b.id AND py.payment_type='reservation' ORDER BY py.created_at DESC LIMIT 1) AS payment_status, (SELECT py.payment_method FROM payments py WHERE py.booking_id=b.id AND py.payment_type='reservation' ORDER BY py.created_at DESC LIMIT 1) AS payment_method FROM bookings b JOIN parks p ON p.id=b.park_id JOIN users u ON u.id=b.client_id ORDER BY b.created_at DESC")->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Admin Bookings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="css/styles.css" />
</head>
<body data-page="admin-bookings">
  <header class="site-header"><nav class="container py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3"><section class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 gap-lg-4"><a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a><ul class="list-unstyled d-flex flex-wrap gap-3 gap-lg-4 m-0 align-items-center"><li><a href="parks.php" class="nav-link-custom" data-page-link="parks"><i class="bi bi-tree"></i>Parks</a></li><li><a href="events.php" class="nav-link-custom" data-page-link="events"><i class="bi bi-calendar-event"></i>Events</a></li><li><a href="map.php" class="nav-link-custom" data-page-link="map"><i class="bi bi-geo-alt"></i>Map</a></li><li><a href="ai.php" class="nav-link-custom" data-page-link="ai"><i class="bi bi-stars"></i>AI</a></li><li><a href="news.php" class="nav-link-custom" data-page-link="news"><i class="bi bi-newspaper"></i>News</a></li><li><a href="admin-dashboard.php" class="nav-link-custom" data-page-link="admin-dashboard"><i class="bi bi-speedometer2"></i>Admin Dash</a></li><li><a href="admin-employee-schedule.php" class="nav-link-custom" data-page-link="admin-schedule"><i class="bi bi-calendar3"></i>Schedule</a></li><li><a href="admin-pto.php" class="nav-link-custom" data-page-link="admin-pto"><i class="bi bi-briefcase"></i>PTO</a></li><li><a href="admin-bookings.php" class="nav-link-custom active" data-page-link="admin-bookings"><i class="bi bi-journal-check"></i>Bookings</a></li></ul></section><ul class="list-unstyled d-flex flex-wrap gap-3 m-0 align-items-center"><li><a href="admin-xml.php" class="nav-link-custom" data-page-link="admin-xml"><i class="bi bi-filetype-xml"></i>XML</a></li><li><a href="logout.php" class="nav-link-custom" data-page-link="logout"><i class="bi bi-box-arrow-right"></i>Logout</a></li></ul></nav></header>
  <main class="py-5"><section class="container"><?php if ($flash): ?><div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> mb-4"><?= e($flash['message']) ?></div><?php endif; ?><header class="mb-4"><p class="section-kicker mb-2">Admin portal</p><h1 class="h2 fw-bold mb-1">Client Booking Approval Queue</h1><p class="text-muted mb-0">Approve or deny client event booking requests and monitor related fees.</p></header><section class="row g-4 mb-4"><article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Pending</p><h2 class="h3 fw-bold mb-2"><?= $pending ?></h2><p class="text-muted mb-0">Requests waiting for staff approval.</p></section></article><article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Approved today</p><h2 class="h3 fw-bold mb-2"><?= $approvedToday ?></h2><p class="text-muted mb-0">Moved to confirmed status.</p></section></article><article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Fees queued</p><h2 class="h3 fw-bold mb-2">$<?= number_format($feesQueued, 0) ?></h2><p class="text-muted mb-0">Reservation charges tied to open requests.</p></section></article></section><section class="list-shell"><section class="p-4 border-bottom"><section class="row g-3"><article class="col-lg-2 fw-bold">Requester</article><article class="col-lg-2 fw-bold">Event</article><article class="col-lg-2 fw-bold">Park</article><article class="col-lg-2 fw-bold">Date</article><article class="col-lg-1 fw-bold">Fee</article><article class="col-lg-2 fw-bold">Payment</article><article class="col-lg-2 fw-bold">Actions</article></section></section><?php foreach ($rows as $i => $row): ?><section class="<?= $i < count($rows) - 1 ? 'list-row' : '' ?> p-4"><section class="row g-3 align-items-center"><article class="col-lg-2"><?= e($row['requester']) ?></article><article class="col-lg-2"><?= e($row['title']) ?></article><article class="col-lg-2 text-muted"><?= e($row['park_name']) ?></article><article class="col-lg-2 text-muted"><?= format_date(substr((string)$row['start_datetime'],0,10)) ?></article><article class="col-lg-1 text-muted">$<?= number_format((float) $row['reservation_fee'], 0) ?></article><article class="col-lg-2 text-muted"><?php if ($row['payment_status']): ?><span class="status-pill <?= booking_status_class($row['payment_status']) ?>"><?= e(ucfirst($row['payment_status'])) ?></span><br><small><?= e(str_replace('_',' ', (string)$row['payment_method'])) ?></small><?php else: ?>No payment<?php endif; ?></article><article class="col-lg-2"><section class="d-flex gap-2"><form method="post" class="d-flex flex-wrap gap-2 mb-0"><input type="hidden" name="booking_id" value="<?= $row['id'] ?>" /><input type="text" name="admin_notes" class="form-control form-control-sm" placeholder="Admin note" style="max-width: 140px;" /><select name="decision" class="form-select form-select-sm" style="max-width: 130px;"><option value="approved">Approve</option><option value="confirmed">Confirm</option><option value="completed">Complete</option><option value="denied">Deny</option><option value="cancelled">Cancel</option></select><button class="btn btn-sm btn-success rounded-pill" type="submit">Update</button></form></section></article></section></section><?php endforeach; ?></section></section></main>
  <footer class="footer-shell py-5 mt-5"><section class="container"><section class="footer-five"><article class="footer-block footer-brand"><a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2 mb-3"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a><p class="text-muted mb-0">A modern gateway to New York State parks, events, maps, news, and role-based operations.</p></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Explore</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="parks.php" class="text-muted text-decoration-none">Parks</a></li><li class="mb-2"><a href="events.php" class="text-muted text-decoration-none">Events</a></li><li class="mb-2"><a href="map.php" class="text-muted text-decoration-none">Map</a></li><li class="mb-2"><a href="ai.php" class="text-muted text-decoration-none">AI</a></li><li class="mb-2"><a href="news.php" class="text-muted text-decoration-none">News</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Account</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="about.php" class="text-muted text-decoration-none">About</a></li><li class="mb-2"><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li><li class="mb-2"><a href="donate.php" class="text-muted text-decoration-none">Donate</a></li><li class="mb-2"><a href="login.php" class="text-muted text-decoration-none">Log In</a></li><li class="mb-2"><a href="register.php" class="text-muted text-decoration-none">Register</a></li><li class="mb-2"><a href="account.php" class="text-muted text-decoration-none">Account</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Portals</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="client-dashboard.php" class="text-muted text-decoration-none">Client Dashboard</a></li><li class="mb-2"><a href="admin-dashboard.php" class="text-muted text-decoration-none">Admin Dashboard</a></li><li class="mb-2"><a href="employee-dashboard.php" class="text-muted text-decoration-none">Employee Dashboard</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Contact</h2><p class="text-muted mb-2">info@nysparks.gov</p><p class="text-muted mb-2">(555) 123-4567</p><p class="text-muted mb-0">Albany, New York</p></article></section></section></footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="js/app.js"></script>
</body>
</html>
