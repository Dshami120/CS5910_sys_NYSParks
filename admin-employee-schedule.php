<?php
require 'bootstrap.php';
$user = require_role($db, 'admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (post('action') === 'delete') {
        $db->prepare("DELETE FROM employee_schedules WHERE id=?")->execute([(int) post('schedule_id')]);
        flash_set('success', 'Selected shift deleted.');
        redirect('admin-employee-schedule.php');
    }
    $employeeId = (int) post('employee_id');
    $parkId = (int) post('park_id');
    $date = post('shift_date');
    $start = post('start_time');
    $end = post('end_time');
    $assignment = post('assignment');
    if (!$employeeId || !$parkId || !$date || !$start || !$end || !$assignment) { flash_set('error', 'Complete every schedule field.'); redirect('admin-employee-schedule.php'); }
    if ($start >= $end) { flash_set('error', 'Shift end must be after shift start.'); redirect('admin-employee-schedule.php'); }
    $overlap = $db->prepare("SELECT COUNT(*) FROM employee_schedules WHERE employee_id=? AND shift_date=? AND schedule_status <> 'cancelled' AND NOT (end_time <= ? OR start_time >= ?)");
    $overlap->execute([$employeeId, $date, $start, $end]);
    if ((int)$overlap->fetchColumn() > 0) { flash_set('error', 'That employee already has an overlapping shift.'); redirect('admin-employee-schedule.php'); }
    $db->prepare("INSERT INTO employee_schedules (employee_id, park_id, shift_date, start_time, end_time, assignment, schedule_status, notes, created_by) VALUES (?,?,?,?,?,?, 'scheduled', ?, ?)")
       ->execute([$employeeId,$parkId,$date,$start,$end,$assignment, post('notes') ?: null, $user['id']]);
    flash_set('success', 'Schedule saved.');
    redirect('admin-employee-schedule.php');
}
$employees = user_options($db, 'employee');
$parks = park_options($db);
$schedules = $db->query("SELECT s.*, CONCAT(u.first_name, ' ', u.last_name) AS employee_name, p.name AS park_name FROM employee_schedules s JOIN users u ON u.id=s.employee_id JOIN parks p ON p.id=s.park_id ORDER BY s.shift_date ASC, s.start_time ASC")->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Admin Schedule</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="admin-schedule">
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
          <li><a href="admin-dashboard.php" class="nav-link-custom" data-page-link="admin-dashboard"><i class="bi bi-speedometer2"></i>Admin Dash</a></li>
          <li><a href="admin-employee-schedule.php" class="nav-link-custom active" data-page-link="admin-schedule"><i class="bi bi-calendar3"></i>Schedule</a></li>
          <li><a href="admin-pto.php" class="nav-link-custom" data-page-link="admin-pto"><i class="bi bi-briefcase"></i>PTO</a></li>
          <li><a href="admin-bookings.php" class="nav-link-custom" data-page-link="admin-bookings"><i class="bi bi-journal-check"></i>Bookings</a></li>
        </ul>
      </section>
      <ul class="list-unstyled d-flex flex-wrap gap-3 m-0 align-items-center">
        <li><a href="admin-xml.php" class="nav-link-custom" data-page-link="admin-xml"><i class="bi bi-filetype-xml"></i>XML</a></li>
        <li><a href="logout.php" class="nav-link-custom" data-page-link="logout"><i class="bi bi-box-arrow-right"></i>Logout</a></li>
      </ul>
    </nav>
  </header>
  <main class="py-5">
    <section class="container">
      <?php if ($flash): ?><div class="alert alert-<?= $flash['type']==='error'?'danger':'success' ?> mb-4"><?= e($flash['message']) ?></div><?php endif; ?>
      <header class="mb-4">
        <p class="section-kicker mb-2">Admin portal</p>
        <h1 class="h2 fw-bold mb-1">Employee Schedule Creation</h1>
        <p class="text-muted mb-0">Create, review, and update employee shift assignments by park and date.</p>
      </header>

      <section class="row g-4">
        <article class="col-xl-4">
          <section class="form-panel p-4 h-100">
            <h2 class="h5 fw-bold mb-3">Create / Update Shift</h2>
            <form method="post">
              <label class="form-label">Employee</label>
              <select name="employee_id" class="form-select mb-3"><?php foreach ($employees as $emp): ?><option value="<?= $emp['id'] ?>"><?= e(full_name($emp)) ?></option><?php endforeach; ?></select>
              <label class="form-label">Park</label>
              <select name="park_id" class="form-select mb-3"><?php foreach ($parks as $park): ?><option value="<?= $park['id'] ?>"><?= e($park['name']) ?></option><?php endforeach; ?></select>
              <label class="form-label">Shift date</label>
              <input type="date" name="shift_date" class="form-control mb-3" required />
              <section class="row g-3">
                <article class="col-6"><label class="form-label">Start</label><input type="time" name="start_time" class="form-control" required /></article>
                <article class="col-6"><label class="form-label">End</label><input type="time" name="end_time" class="form-control" required /></article>
              </section>
              <label class="form-label mt-3">Role / assignment</label>
              <input type="text" name="assignment" class="form-control mb-3" placeholder="Events support, ranger duty, maintenance" required />
              <input type="hidden" name="notes" value="" />
              <button type="submit" class="btn btn-success rounded-pill w-100 fw-semibold">Save Schedule</button>
            </form>
            <form method="post" class="mt-3">
              <input type="hidden" name="action" value="delete" />
              <div class="col-12 d-flex flex-wrap gap-3">
                <input type="number" name="schedule_id" class="form-control" placeholder="Shift ID to delete" />
                <button type="submit" class="btn btn-outline-danger btn-lg">Delete Selected Shift</button>
              </div>
            </form>
          </section>
        </article>

        <article class="col-xl-8">
          <section class="list-shell mb-4">
            <section class="p-4 border-bottom">
              <section class="row g-3">
                <article class="col-md-3 fw-bold">Employee</article>
                <article class="col-md-3 fw-bold">Park</article>
                <article class="col-md-2 fw-bold">Date</article>
                <article class="col-md-2 fw-bold">Hours</article>
                <article class="col-md-2 fw-bold">Assignment</article>
              </section>
            </section>
            <?php foreach ($schedules as $i => $row): ?>
            <section class="<?= $i < count($schedules) - 1 ? 'list-row' : '' ?> p-4"><section class="row g-3"><article class="col-md-3"><?= e($row['employee_name']) ?></article><article class="col-md-3"><?= e($row['park_name']) ?></article><article class="col-md-2 text-muted"><?= date('m/d/Y', strtotime($row['shift_date'])) ?></article><article class="col-md-2 text-muted"><?= date('g:i A', strtotime($row['start_time'])) ?>–<?= date('g:i A', strtotime($row['end_time'])) ?></article><article class="col-md-2 text-muted"><?= e($row['assignment']) ?></article></section></section>
            <?php endforeach; ?>
          </section>
          <section class="note-box">Employees can only view this schedule on their side. Editing stays in the admin portal.</section>
        </article>
      </section>
    </section>
  </main>
  <footer class="footer-shell py-5 mt-5">
    <section class="container"><section class="footer-five"><article class="footer-block footer-brand"><a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2 mb-3"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a><p class="text-muted mb-0">A modern gateway to New York State parks, events, maps, news, and role-based operations.</p></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Explore</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="parks.php" class="text-muted text-decoration-none">Parks</a></li><li class="mb-2"><a href="events.php" class="text-muted text-decoration-none">Events</a></li><li class="mb-2"><a href="map.php" class="text-muted text-decoration-none">Map</a></li><li class="mb-2"><a href="ai.php" class="text-muted text-decoration-none">AI</a></li><li class="mb-2"><a href="news.php" class="text-muted text-decoration-none">News</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Account</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="about.php" class="text-muted text-decoration-none">About</a></li><li class="mb-2"><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li><li class="mb-2"><a href="donate.php" class="text-muted text-decoration-none">Donate</a></li><li class="mb-2"><a href="login.php" class="text-muted text-decoration-none">Log In</a></li><li class="mb-2"><a href="register.php" class="text-muted text-decoration-none">Register</a></li><li class="mb-2"><a href="account.php" class="text-muted text-decoration-none">Account</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Portals</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="client-dashboard.php" class="text-muted text-decoration-none">Client Dashboard</a></li><li class="mb-2"><a href="admin-dashboard.php" class="text-muted text-decoration-none">Admin Dashboard</a></li><li class="mb-2"><a href="employee-dashboard.php" class="text-muted text-decoration-none">Employee Dashboard</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Contact</h2><p class="text-muted mb-2">info@nysparks.gov</p><p class="text-muted mb-2">(555) 123-4567</p><p class="text-muted mb-0">Albany, New York</p></article></section></section>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="app.js"></script>
</body>
</html>
