<?php
require 'bootstrap.php';
$user = require_role($db, 'admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) post('pto_id');
    $decision = post('decision');
    if (in_array($decision, ['approved', 'denied'], true)) {
        $db->prepare("UPDATE pto_requests SET pto_status=?, reviewed_by=?, reviewed_at=NOW(), admin_notes=? WHERE id=?")
           ->execute([$decision, $user['id'], post('admin_notes') ?: null, $id]);
        flash_set('success', 'PTO request updated.');
    }
    redirect('admin-pto.php');
}
$pending = (int) $db->query("SELECT COUNT(*) FROM pto_requests WHERE pto_status='pending'")->fetchColumn();
$approvedMonth = (int) $db->query("SELECT COUNT(*) FROM pto_requests WHERE pto_status='approved' AND YEAR(start_date)=YEAR(CURDATE()) AND MONTH(start_date)=MONTH(CURDATE())")->fetchColumn();
$conflicts = (int) $db->query("SELECT COUNT(*) FROM pto_requests WHERE pto_status='pending'")->fetchColumn();
$rows = $db->query("SELECT p.*, CONCAT(u.first_name,' ',u.last_name) AS employee_name FROM pto_requests p JOIN users u ON u.id=p.employee_id ORDER BY p.created_at DESC")->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Admin PTO</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="admin-pto">
  <header class="site-header"><nav class="container py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3"><section class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 gap-lg-4"><a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a><ul class="list-unstyled d-flex flex-wrap gap-3 gap-lg-4 m-0 align-items-center"><li><a href="parks.php" class="nav-link-custom" data-page-link="parks"><i class="bi bi-tree"></i>Parks</a></li><li><a href="events.php" class="nav-link-custom" data-page-link="events"><i class="bi bi-calendar-event"></i>Events</a></li><li><a href="map.php" class="nav-link-custom" data-page-link="map"><i class="bi bi-geo-alt"></i>Map</a></li><li><a href="ai.php" class="nav-link-custom" data-page-link="ai"><i class="bi bi-stars"></i>AI</a></li><li><a href="news.php" class="nav-link-custom" data-page-link="news"><i class="bi bi-newspaper"></i>News</a></li><li><a href="admin-dashboard.php" class="nav-link-custom" data-page-link="admin-dashboard"><i class="bi bi-speedometer2"></i>Admin Dash</a></li><li><a href="admin-employee-schedule.php" class="nav-link-custom" data-page-link="admin-schedule"><i class="bi bi-calendar3"></i>Schedule</a></li><li><a href="admin-pto.php" class="nav-link-custom active" data-page-link="admin-pto"><i class="bi bi-briefcase"></i>PTO</a></li><li><a href="admin-bookings.php" class="nav-link-custom" data-page-link="admin-bookings"><i class="bi bi-journal-check"></i>Bookings</a></li></ul></section><ul class="list-unstyled d-flex flex-wrap gap-3 m-0 align-items-center"><li><a href="admin-xml.php" class="nav-link-custom" data-page-link="admin-xml"><i class="bi bi-filetype-xml"></i>XML</a></li><li><a href="logout.php" class="nav-link-custom" data-page-link="logout"><i class="bi bi-box-arrow-right"></i>Logout</a></li></ul></nav></header>
  <main class="py-5">
    <section class="container">
      <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> mb-4"><?= e($flash['message']) ?></div><?php endif; ?>
      <header class="mb-4"><p class="section-kicker mb-2">Admin portal</p><h1 class="h2 fw-bold mb-1">PTO Approval Queue</h1><p class="text-muted mb-0">Approve or deny employee PTO requests submitted from the employee portal.</p></header>
      <section class="row g-4 mb-4"><article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Pending requests</p><h2 class="h3 fw-bold mb-2"><?= $pending ?></h2><p class="text-muted mb-0">Waiting for decision.</p></section></article><article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Approved this month</p><h2 class="h3 fw-bold mb-2"><?= $approvedMonth ?></h2><p class="text-muted mb-0">Time off already granted.</p></section></article><article class="col-md-4"><section class="stats-card h-100"><p class="small text-uppercase text-muted mb-1">Coverage conflicts</p><h2 class="h3 fw-bold mb-2"><?= $conflicts ?></h2><p class="text-muted mb-0">Needs schedule adjustment.</p></section></article></section>
      <section class="list-shell">
        <section class="p-4 border-bottom"><section class="row g-3"><article class="col-md-3 fw-bold">Employee</article><article class="col-md-2 fw-bold">Dates</article><article class="col-md-2 fw-bold">Type</article><article class="col-md-2 fw-bold">Coverage</article><article class="col-md-3 fw-bold">Actions</article></section></section>
        <?php foreach ($rows as $i => $row): ?>
        <section class="<?= $i < count($rows) - 1 ? 'list-row' : '' ?> p-4">
          <section class="row g-3 align-items-center">
            <article class="col-md-3"><?= e($row['employee_name']) ?></article>
            <article class="col-md-2 text-muted"><?= format_date($row['start_date']) ?><?= $row['start_date'] !== $row['end_date'] ? '–' . format_date($row['end_date']) : '' ?></article>
            <article class="col-md-2 text-muted"><?= e($row['leave_type']) ?></article>
            <article class="col-md-2"><span class="status-pill <?= $row['pto_status'] === 'approved' ? 'status-approved' : ($row['pto_status'] === 'denied' ? 'status-denied' : 'status-pending') ?>"><?= $row['pto_status'] === 'pending' ? 'Review' : ($row['pto_status'] === 'approved' ? 'Covered' : 'Denied') ?></span></article>
            <article class="col-md-3"><section class="d-flex gap-2"><form method="post" class="d-flex gap-2 mb-0"><input type="hidden" name="pto_id" value="<?= $row['id'] ?>" /><input type="hidden" name="admin_notes" value="" /><button type="submit" name="decision" value="approved" class="btn btn-sm btn-success rounded-pill">Approve</button><button type="submit" name="decision" value="denied" class="btn btn-sm btn-outline-danger rounded-pill">Deny</button></form></section></article>
          </section>
        </section>
        <?php endforeach; ?>
      </section>
    </section>
  </main>
  <footer class="footer-shell py-5 mt-5"><section class="container"><section class="footer-five"><article class="footer-block footer-brand"><a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2 mb-3"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a><p class="text-muted mb-0">A modern gateway to New York State parks, events, maps, news, and role-based operations.</p></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Explore</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="parks.php" class="text-muted text-decoration-none">Parks</a></li><li class="mb-2"><a href="events.php" class="text-muted text-decoration-none">Events</a></li><li class="mb-2"><a href="map.php" class="text-muted text-decoration-none">Map</a></li><li class="mb-2"><a href="ai.php" class="text-muted text-decoration-none">AI</a></li><li class="mb-2"><a href="news.php" class="text-muted text-decoration-none">News</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Account</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="about.php" class="text-muted text-decoration-none">About</a></li><li class="mb-2"><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li><li class="mb-2"><a href="donate.php" class="text-muted text-decoration-none">Donate</a></li><li class="mb-2"><a href="login.php" class="text-muted text-decoration-none">Log In</a></li><li class="mb-2"><a href="register.php" class="text-muted text-decoration-none">Register</a></li><li class="mb-2"><a href="account.php" class="text-muted text-decoration-none">Account</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Portals</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="client-dashboard.php" class="text-muted text-decoration-none">Client Dashboard</a></li><li class="mb-2"><a href="admin-dashboard.php" class="text-muted text-decoration-none">Admin Dashboard</a></li><li class="mb-2"><a href="employee-dashboard.php" class="text-muted text-decoration-none">Employee Dashboard</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Contact</h2><p class="text-muted mb-2">info@nysparks.gov</p><p class="text-muted mb-2">(555) 123-4567</p><p class="text-muted mb-0">Albany, New York</p></article></section></section></footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="app.js"></script>
</body>
</html>
