<?php
require 'bootstrap.php';
$user = require_role($db, 'admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');
    if ($action === 'create_employee') {
        [$first,$last] = split_name(post('full_name'));
        if (!$first || !$last || !valid_email(post('email')) || strlen(post('password')) < 8) {
            flash_set('error', 'Enter a full name, valid email, and password of at least 8 characters.');
            redirect('admin-dashboard.php');
        }
        $db->prepare("INSERT INTO users (first_name,last_name,email,password_hash,role,phone,birthdate,notes,park_id,account_status) VALUES (?,?,?,?, 'employee',?,?,?,?, 'active')")
           ->execute([$first,$last,strtolower(post('email')),password_hash(post('password'), PASSWORD_DEFAULT),post('phone') ?: null, post('birthdate') ?: null, post('notes') ?: null, (int) post('park_id') ?: null]);
        flash_set('success', 'Employee account created.');
        redirect('admin-dashboard.php');
    }
    if ($action === 'update_employee') {
        $id = (int) post('employee_id');
        [$first,$last] = split_name(post('full_name'));
        $db->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, birthdate=?, notes=?, park_id=? WHERE id=? AND role='employee'")
           ->execute([$first,$last,strtolower(post('email')),post('phone') ?: null, post('birthdate') ?: null, post('notes') ?: null, (int) post('park_id') ?: null, $id]);
        if (post('password') !== '') {
            $db->prepare("UPDATE users SET password_hash=? WHERE id=? AND role='employee'")->execute([password_hash(post('password'), PASSWORD_DEFAULT), $id]);
        }
        flash_set('success', 'Employee account updated.');
        redirect('admin-dashboard.php?employee_id=' . $id);
    }
    if ($action === 'disable_employee') {
        $db->prepare("UPDATE users SET account_status='disabled' WHERE id=? AND role='employee'")->execute([(int) post('employee_id')]);
        flash_set('success', 'Employee account disabled.');
        redirect('admin-dashboard.php');
    }
    if (in_array($action, ['create_news','update_news','delete_news'], true)) {
        $newsId = (int) post('news_id');
        $allowedTopics = ['alerts','community','events','parks','safety','support','conservation','volunteer','maintenance','education','seasonal'];
        $allowedStatuses = ['draft','published','archived'];
        if ($action === 'delete_news') {
            if ($newsId <= 0) {
                flash_set('error', 'Select a news item to delete.');
                redirect('admin-dashboard.php#news-manager');
            }
            $db->prepare("DELETE FROM news WHERE id=?")->execute([$newsId]);
            flash_set('success', 'News item deleted.');
            redirect('admin-dashboard.php#news-manager');
        }
        $topic = post('topic') ?: 'community';
        $status = post('news_status') ?: 'draft';
        if (!in_array($topic, $allowedTopics, true)) { $topic = 'community'; }
        if (!in_array($status, $allowedStatuses, true)) { $status = 'draft'; }
        if (post('title') === '' || post('summary') === '' || post('content') === '' || post('region') === '') {
            flash_set('error', 'News title, summary, content, and region are required.');
            redirect('admin-dashboard.php#news-manager');
        }
        $publishedDate = post('published_date') ?: date('Y-m-d');
        $isFeatured = post('is_featured') === '1' ? 1 : 0;
        if ($action === 'create_news') {
            $db->prepare("INSERT INTO news (title, topic, published_date, region, summary, content, image_url, image_alt, card_summary, tag, is_featured, news_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([post('title'), $topic, $publishedDate, post('region'), post('summary'), post('content'), post('image_url') ?: null, post('image_alt') ?: null, post('card_summary') ?: null, post('tag') ?: ucwords(str_replace('_',' ', $topic)), $isFeatured, $status]);
            flash_set('success', 'News item created.');
            redirect('admin-dashboard.php#news-manager');
        }
        if ($action === 'update_news') {
            if ($newsId <= 0) {
                flash_set('error', 'Select a news item to update.');
                redirect('admin-dashboard.php#news-manager');
            }
            $db->prepare("UPDATE news SET title=?, topic=?, published_date=?, region=?, summary=?, content=?, image_url=?, image_alt=?, card_summary=?, tag=?, is_featured=?, news_status=? WHERE id=?")
               ->execute([post('title'), $topic, $publishedDate, post('region'), post('summary'), post('content'), post('image_url') ?: null, post('image_alt') ?: null, post('card_summary') ?: null, post('tag') ?: ucwords(str_replace('_',' ', $topic)), $isFeatured, $status, $newsId]);
            flash_set('success', 'News item updated.');
            redirect('admin-dashboard.php?news_id=' . $newsId . '#news-manager');
        }
    }
}
$parksCount = (int) $db->query("SELECT COUNT(*) FROM parks")->fetchColumn();
$publishedEventsCount = (int) $db->query("SELECT COUNT(*) FROM events WHERE event_status='published'")->fetchColumn();
$approvedBookingsCount = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE booking_status='approved'")->fetchColumn();
$pendingBookingsCount = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE booking_status='pending'")->fetchColumn();
$pendingBookings = $pendingBookingsCount;
$pendingPto = (int) $db->query("SELECT COUNT(*) FROM pto_requests WHERE pto_status='pending'")->fetchColumn();
$registeredAttendanceCount = (int) $db->query("SELECT COUNT(*) FROM attendance WHERE attendance_status='registered'")->fetchColumn();
$attendedAttendanceCount = (int) $db->query("SELECT COUNT(*) FROM attendance WHERE attendance_status='attended'")->fetchColumn();
$upcomingRsvpGuests = (int) $db->query("SELECT COALESCE(SUM(a.guest_count),0) FROM attendance a JOIN events e ON e.id=a.event_id WHERE a.attendance_status='registered' AND e.end_datetime >= NOW()")->fetchColumn();
$recentAttendance = $db->query("SELECT a.*, e.title, e.start_datetime, p.name AS park_name FROM attendance a JOIN events e ON e.id=a.event_id JOIN parks p ON p.id=e.park_id ORDER BY a.registered_at DESC LIMIT 8")->fetchAll();
$employeeActions = (int) $db->query("SELECT COUNT(*) FROM users WHERE role='employee' AND account_status='active'")->fetchColumn();
$openAlerts = $pendingBookings + $pendingPto;
$adminChartRows = [];
foreach ($db->query("SELECT DATE(b.start_datetime) AS date, p.name AS park, 'bookings' AS metric, 1 AS value FROM bookings b JOIN parks p ON p.id=b.park_id WHERE b.booking_status IN ('approved','confirmed') ORDER BY b.start_datetime")->fetchAll() as $row) { $adminChartRows[] = $row; }
foreach ($db->query("SELECT DATE(e.start_datetime) AS date, p.name AS park, 'events' AS metric, 1 AS value FROM events e JOIN parks p ON p.id=e.park_id WHERE e.event_status='published' ORDER BY e.start_datetime")->fetchAll() as $row) { $adminChartRows[] = $row; }
foreach ($db->query("SELECT DATE(a.registered_at) AS date, p.name AS park, 'attendance' AS metric, a.guest_count AS value FROM attendance a JOIN events e ON e.id=a.event_id JOIN parks p ON p.id=e.park_id WHERE a.attendance_status IN ('registered','attended') ORDER BY a.registered_at")->fetchAll() as $row) { $adminChartRows[] = $row; }
foreach ($db->query("SELECT DATE(py.created_at) AS date, COALESCE(p.name, 'No park') AS park, 'donations' AS metric, py.amount AS value FROM payments py LEFT JOIN bookings b ON b.id=py.booking_id LEFT JOIN parks p ON p.id=b.park_id WHERE py.payment_type='donation' AND py.payment_status='completed' ORDER BY py.created_at")->fetchAll() as $row) { $adminChartRows[] = $row; }
$trafficAvailable = false;
$newsTopics = ['alerts','community','events','parks','safety','support','conservation','volunteer','maintenance','education','seasonal'];
$newsStatuses = ['draft','published','archived'];
$newsSearch = get('news_q');
$newsParams = [];
$newsSql = "SELECT * FROM news WHERE 1=1";
if ($newsSearch !== '') {
    $newsSql .= " AND (title LIKE ? OR summary LIKE ? OR content LIKE ? OR region LIKE ? OR tag LIKE ?)";
    $like = "%{$newsSearch}%";
    array_push($newsParams, $like, $like, $like, $like, $like);
}
$newsSql .= " ORDER BY published_date DESC, updated_at DESC LIMIT 20";
$newsStmt = $db->prepare($newsSql);
$newsStmt->execute($newsParams);
$newsItems = $newsStmt->fetchAll();
$selectedNewsId = (int) get('news_id');
$selectedNews = null;
if ($selectedNewsId) {
    $stmt = $db->prepare("SELECT * FROM news WHERE id=?");
    $stmt->execute([$selectedNewsId]);
    $selectedNews = $stmt->fetch();
}
if (!$selectedNews && $newsItems) { $selectedNews = $newsItems[0]; }
$employees = user_options($db, 'employee');
$parks = park_options($db);
$selectedId = (int) get('employee_id');
$selected = null;
if ($selectedId) { $stmt=$db->prepare("SELECT * FROM users WHERE id=? AND role='employee'"); $stmt->execute([$selectedId]); $selected=$stmt->fetch(); }
if (!$selected && $employees) { $selected = $employees[0]; }
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="css/styles.css" />
</head>
<body data-page="admin-dashboard">
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
          <li><a href="admin-dashboard.php" class="nav-link-custom active" data-page-link="admin-dashboard"><i class="bi bi-speedometer2"></i>Admin Dash</a></li>
          <li><a href="admin-employee-schedule.php" class="nav-link-custom" data-page-link="admin-schedule"><i class="bi bi-calendar3"></i>Schedule</a></li>
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
  <main class="container py-5">
    <?php if ($flash): ?><div class="alert alert-<?= $flash['type']==='error'?'danger':'success' ?> mb-4"><?= e($flash['message']) ?></div><?php endif; ?>
    <section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
      <div>
        <p class="eyebrow mb-2">Admin Workspace</p>
        <h1 class="display-6 fw-bold mb-2">Admin Dashboard</h1>
        <p class="text-muted mb-0">Analytics, approvals, notifications, and employee account management.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-success" href="admin-employee-schedule.php">Employee Schedules</a>
        <a class="btn btn-outline-dark" href="admin-pto.php">PTO Requests</a>
        <a class="btn btn-outline-dark" href="admin-bookings.php">Client Bookings</a>
      </div>
    </section>

    <section class="row g-4 mb-4">
      <div class="col-md-6 col-xl-3">
        <article class="admin-stat-card">
          <span class="admin-stat-icon icon-blue">☑</span>
          <p class="admin-stat-label mb-1">Parks</p>
          <h2 class="admin-stat-value mb-0"><?= $parksCount ?></h2>
        </article>
      </div>
      <div class="col-md-6 col-xl-3">
        <article class="admin-stat-card">
          <span class="admin-stat-icon icon-green">↗</span>
          <p class="admin-stat-label mb-1">Published events</p>
          <h2 class="admin-stat-value mb-0"><?= $publishedEventsCount ?></h2>
        </article>
      </div>
      <div class="col-md-6 col-xl-3">
        <article class="admin-stat-card">
          <span class="admin-stat-icon icon-purple">◫</span>
          <p class="admin-stat-label mb-1">Approved bookings</p>
          <h2 class="admin-stat-value mb-0"><?= $approvedBookingsCount ?></h2>
        </article>
      </div>
      <div class="col-md-6 col-xl-3">
        <article class="admin-stat-card">
          <span class="admin-stat-icon icon-orange">🔔</span>
          <p class="admin-stat-label mb-1">Pending bookings</p>
          <h2 class="admin-stat-value mb-0"><?= $pendingBookingsCount ?></h2>
        </article>
      </div>
    </section>

    <section class="row g-4 mb-4" id="attendance-summary">
      <div class="col-md-6 col-xl-3">
        <article class="admin-stat-card">
          <span class="admin-stat-icon icon-green"><i class="bi bi-person-check"></i></span>
          <p class="admin-stat-label mb-1">Registered RSVPs</p>
          <h2 class="admin-stat-value mb-0"><?= $registeredAttendanceCount ?></h2>
          <p class="text-muted small mb-0">Active attendance records.</p>
        </article>
      </div>
      <div class="col-md-6 col-xl-3">
        <article class="admin-stat-card">
          <span class="admin-stat-icon icon-blue"><i class="bi bi-people"></i></span>
          <p class="admin-stat-label mb-1">Upcoming RSVP Guests</p>
          <h2 class="admin-stat-value mb-0"><?= $upcomingRsvpGuests ?></h2>
          <p class="text-muted small mb-0">Guest count for future/current events.</p>
        </article>
      </div>
      <div class="col-md-6 col-xl-3">
        <article class="admin-stat-card">
          <span class="admin-stat-icon icon-purple"><i class="bi bi-clipboard-check"></i></span>
          <p class="admin-stat-label mb-1">Attended</p>
          <h2 class="admin-stat-value mb-0"><?= $attendedAttendanceCount ?></h2>
          <p class="text-muted small mb-0">Manually completed attendance.</p>
        </article>
      </div>
      <div class="col-md-6 col-xl-3">
        <article class="admin-stat-card">
          <span class="admin-stat-icon icon-orange"><i class="bi bi-bar-chart"></i></span>
          <p class="admin-stat-label mb-1">Attendance Chart</p>
          <h2 class="h5 fw-bold mb-1">Available</h2>
          <p class="text-muted small mb-0">Use the dashboard metric filter and choose Attendance.</p>
        </article>
      </div>
    </section>

    <section class="row g-4 mb-4">
      <div class="col-lg-7">
        <article class="admin-panel h-100">
          <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-4">
            <div>
              <h2 class="h3 fw-bold mb-1">Dashboard metrics</h2>
              <p class="text-muted mb-0">Database-backed bookings, events, attendance, and donation dollars.</p>
            </div>
            <div class="admin-chart-filters">
              <div>
                <label class="form-label small text-uppercase text-muted mb-1">Metric</label>
                <select class="form-select admin-filter-select" id="adminChartMetric">
                  <option value="bookings" selected>Bookings</option>
                  <option value="events">Events</option>
                  <option value="attendance">Attendance</option>
                  <option value="donations">Donation dollars</option>
                </select>
              </div>
              <div>
                <label class="form-label small text-uppercase text-muted mb-1">View By</label>
                <select class="form-select admin-filter-select" id="adminChartRange">
                  <option value="day">Day</option>
                  <option value="week">Week</option>
                  <option value="month" selected>Month</option>
                  <option value="year">Year</option>
                </select>
              </div>
              <div>
                <label class="form-label small text-uppercase text-muted mb-1">Park</label>
                <select class="form-select admin-filter-select" id="adminChartPark">
                  <option value="all" selected>All Parks</option>
                  <?php foreach ($parks as $park): ?><option value="<?= e($park['name']) ?>"><?= e($park['name']) ?></option><?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="admin-chart bg-white rounded-4 border p-3">
            <canvas id="adminBookingsChart" height="150"></canvas>
          </div>
          <?php if (!$trafficAvailable): ?><p class="text-muted small mt-3 mb-0">Traffic is not available yet because there is no site_visits or page_views table.</p><?php endif; ?>
        </article>
      </div>
      <div class="col-lg-5">
        <article class="admin-panel h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 fw-bold mb-0">Notifications</h2>
            <span class="admin-badge"><?= $openAlerts ?> new</span>
          </div>

          <div class="vstack gap-3">
            <article class="admin-note-card">
              <h3 class="h5 fw-bold mb-2"><?= $pendingBookings ?> booking requests</h3>
              <p class="text-muted mb-3">Open the bookings page to approve or deny client event requests.</p>
              <a href="admin-bookings.php" class="btn btn-sm btn-outline-dark">Review bookings</a>
            </article>

            <article class="admin-note-card">
              <h3 class="h5 fw-bold mb-2"><?= $pendingPto ?> PTO requests</h3>
              <p class="text-muted mb-3">Review employee time-off submissions waiting in the PTO queue.</p>
              <a href="admin-pto.php" class="btn btn-sm btn-outline-dark">Review PTO</a>
            </article>

            <article class="admin-note-card">
              <h3 class="h5 fw-bold mb-2"><?= $employeeActions ?> employee actions</h3>
              <p class="text-muted mb-3">New employee account creation and one schedule conflict to resolve.</p>
              <a href="admin-employee-schedule.php" class="btn btn-sm btn-outline-dark">Open employee tools</a>
            </article>
          </div>
        </article>
      </div>
    </section>

    <section class="card shadow-sm border-0 rounded-4 mb-4" id="attendance-records">
      <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
          <div>
            <p class="eyebrow mb-1">Attendance</p>
            <h2 class="h3 fw-bold mb-1">Recent RSVP / Attendance Records</h2>
            <p class="text-muted mb-0">Read-only overview from the existing attendance table. Manual attendance updates can stay in your existing workflow.</p>
          </div>
          <div class="pill-note">Admin only · attendance table</div>
        </div>

        <section class="list-shell mb-0">
          <section class="p-4 border-bottom">
            <section class="row g-3">
              <article class="col-lg-3 fw-bold">Event</article>
              <article class="col-lg-2 fw-bold">Park</article>
              <article class="col-lg-2 fw-bold">Event Date</article>
              <article class="col-lg-2 fw-bold">Attendee</article>
              <article class="col-lg-1 fw-bold">Guests</article>
              <article class="col-lg-2 fw-bold">Status</article>
            </section>
          </section>
          <?php foreach ($recentAttendance as $record): ?>
          <section class="list-row p-4">
            <section class="row g-3 align-items-start">
              <article class="col-lg-3 fw-semibold"><?= e($record['title']) ?></article>
              <article class="col-lg-2 text-muted"><?= e($record['park_name']) ?></article>
              <article class="col-lg-2 text-muted"><?= e(format_datetime($record['start_datetime'])) ?></article>
              <article class="col-lg-2 text-muted"><?= e($record['attendee_email']) ?></article>
              <article class="col-lg-1 text-muted"><?= (int)$record['guest_count'] ?></article>
              <article class="col-lg-2"><span class="status-pill <?= booking_status_class($record['attendance_status']) ?>"><?= e(ucfirst(str_replace('_',' ', $record['attendance_status']))) ?></span></article>
            </section>
          </section>
          <?php endforeach; ?>
          <?php if (!$recentAttendance): ?><section class="p-4 text-muted">No attendance records yet.</section><?php endif; ?>
        </section>
      </div>
    </section>

    <section class="card shadow-sm border-0 rounded-4 mb-4">
      <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
          <div>
            <p class="eyebrow mb-1">Employee Accounts</p>
            <h2 class="h3 fw-bold mb-1">Create, Update, or Delete Employee Accounts</h2>
            <p class="text-muted mb-0">Admin can find employee accounts from the user list, update passwords, create new accounts, or deactivate/delete existing ones.</p>
          </div>
          <div class="pill-note">Admin only · users table workflow</div>
        </div>

        <div class="row g-4">
          <div class="col-xl-5">
            <div class="account-panel h-100">
              <h3 class="h5 fw-bold mb-3">Find Employee Account</h3>
              <div class="vstack gap-3">
                <div>
                  <label class="form-label">Employee Dropdown</label>
                  <form method="get">
                    <select class="form-select form-select-lg" name="employee_id" onchange="this.form.submit()">
                      <option value="">Select employee account</option>
                      <?php foreach ($employees as $emp): ?>
                      <option value="<?= $emp['id'] ?>" <?= $selected && (int)$selected['id'] === (int)$emp['id'] ? 'selected' : '' ?>><?= e(full_name($emp)) ?> — <?= e($emp['email']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </div>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Quick Filter</label>
                    <select class="form-select">
                      <option selected>All Employees</option>
                      <option>Park Operations</option>
                      <option>Visitor Services</option>
                      <option>Maintenance</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Assigned Park</label>
                    <select class="form-select">
                      <option selected>All Parks</option>
                      <?php foreach ($parks as $park): ?><option><?= e($park['name']) ?></option><?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="mini-panel">
                  <strong class="d-block mb-2">Selected Account Preview</strong>
                  <?php if ($selected): ?>
                  <p class="small text-muted mb-1">Name: <?= e(full_name($selected)) ?></p>
                  <p class="small text-muted mb-1">Email: <?= e($selected['email']) ?></p>
                  <p class="small text-muted mb-1">Birthdate: <?= e((string)$selected['birthdate']) ?: '—' ?></p>
                  <p class="small text-muted mb-1">Park: <?= e((string)($selected['park_name'] ?? 'Unassigned')) ?></p>
                  <p class="small text-muted mb-0">Role: Employee</p>
                  <?php else: ?>
                  <p class="small text-muted mb-0">Select an employee to preview account details.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-7">
            <div class="account-panel h-100">
              <h3 class="h5 fw-bold mb-3">Employee Account CRUD</h3>
              <form class="row g-3" method="post">
                <input type="hidden" name="employee_id" value="<?= (int)($selected['id'] ?? 0) ?>" />
                <div class="col-md-6">
                  <label class="form-label">Full Name</label>
                  <input type="text" name="full_name" class="form-control form-control-lg" placeholder="Jordan Lee" value="<?= e($selected ? full_name($selected) : '') ?>" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control form-control-lg" placeholder="employee@nysparks.gov" value="<?= e($selected['email'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Password</label>
                  <input type="password" name="password" class="form-control form-control-lg" placeholder="Update or set password" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Birthdate</label>
                  <input type="date" name="birthdate" class="form-control form-control-lg" value="<?= e((string)($selected['birthdate'] ?? '')) ?>" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Assigned Park</label>
                  <select name="park_id" class="form-select form-select-lg">
                    <option value="">Select park</option>
                    <?php foreach ($parks as $park): ?><option value="<?= $park['id'] ?>" <?= (int)($selected['park_id'] ?? 0)===(int)$park['id']?'selected':'' ?>><?= e($park['name']) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Role</label>
                  <select class="form-select form-select-lg" disabled>
                    <option selected>Employee</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone</label>
                  <input type="text" name="phone" class="form-control form-control-lg" value="<?= e((string)($selected['phone'] ?? '')) ?>" />
                </div>
                <div class="col-12">
                  <label class="form-label">Notes</label>
                  <textarea class="form-control" name="notes" rows="3" placeholder="Optional employee account notes, department details, or hiring status."><?= e((string)($selected['notes'] ?? '')) ?></textarea>
                </div>
                <div class="col-12 d-flex flex-wrap gap-3">
                  <button type="submit" name="action" value="create_employee" class="btn btn-success btn-lg">Create Account</button>
                  <button type="submit" name="action" value="update_employee" class="btn btn-outline-dark btn-lg">Update Account</button>
                  <button type="submit" name="action" value="disable_employee" class="btn btn-outline-danger btn-lg" <?= $selected ? '' : 'disabled' ?>>Disable Account</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="card shadow-sm border-0 rounded-4 mb-4" id="news-manager">
      <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
          <div>
            <p class="eyebrow mb-1">News Manager</p>
            <h2 class="h3 fw-bold mb-1">Create, Edit, or Delete News</h2>
            <p class="text-muted mb-0">Manage public news directly from the news table without leaving the dashboard.</p>
          </div>
          <form method="get" class="d-flex gap-2 align-items-start">
            <input type="text" name="news_q" value="<?= e($newsSearch) ?>" class="form-control" placeholder="Search news..." />
            <button class="btn btn-outline-dark" type="submit">Search</button>
          </form>
        </div>

        <div class="row g-4">
          <div class="col-xl-5">
            <div class="account-panel h-100">
              <h3 class="h5 fw-bold mb-3">Existing News</h3>
              <div class="vstack gap-2">
                <?php foreach ($newsItems as $item): ?>
                  <a class="text-decoration-none text-dark border rounded-3 p-3 <?= $selectedNews && (int)$selectedNews['id']===(int)$item['id'] ? 'bg-light' : '' ?>" href="admin-dashboard.php?news_id=<?= (int)$item['id'] ?>#news-manager">
                    <strong class="d-block"><?= e($item['title']) ?></strong>
                    <span class="small text-muted"><?= e(ucfirst($item['news_status'])) ?> · <?= e($item['region']) ?> · <?= e(format_date($item['published_date'])) ?></span>
                  </a>
                <?php endforeach; ?>
                <?php if (!$newsItems): ?><p class="text-muted mb-0">No news items matched your search.</p><?php endif; ?>
              </div>
            </div>
          </div>

          <div class="col-xl-7">
            <div class="account-panel h-100">
              <h3 class="h5 fw-bold mb-3">News Form</h3>
              <form class="row g-3" method="post">
                <input type="hidden" name="news_id" value="<?= (int)($selectedNews['id'] ?? 0) ?>" />
                <div class="col-md-8">
                  <label class="form-label">Title</label>
                  <input type="text" name="title" class="form-control form-control-lg" value="<?= e($selectedNews['title'] ?? '') ?>" required />
                </div>
                <div class="col-md-4">
                  <label class="form-label">Published Date</label>
                  <input type="date" name="published_date" class="form-control form-control-lg" value="<?= e((string)($selectedNews['published_date'] ?? date('Y-m-d'))) ?>" />
                </div>
                <div class="col-md-4">
                  <label class="form-label">Topic</label>
                  <select name="topic" class="form-select form-select-lg">
                    <?php foreach ($newsTopics as $topic): ?><option value="<?= e($topic) ?>" <?= ($selectedNews['topic'] ?? '')===$topic ? 'selected' : '' ?>><?= e(ucwords(str_replace('_',' ', $topic))) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Region</label>
                  <input type="text" name="region" class="form-control form-control-lg" value="<?= e($selectedNews['region'] ?? '') ?>" required />
                </div>
                <div class="col-md-4">
                  <label class="form-label">Status</label>
                  <select name="news_status" class="form-select form-select-lg">
                    <?php foreach ($newsStatuses as $status): ?><option value="<?= e($status) ?>" <?= ($selectedNews['news_status'] ?? '')===$status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Tag</label>
                  <input type="text" name="tag" class="form-control" value="<?= e($selectedNews['tag'] ?? '') ?>" />
                </div>
                <div class="col-md-6 d-flex align-items-end">
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="newsFeatured" <?= !empty($selectedNews['is_featured']) ? 'checked' : '' ?> />
                    <label class="form-check-label" for="newsFeatured">Featured news</label>
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label">Summary</label>
                  <textarea name="summary" class="form-control" rows="2" required><?= e($selectedNews['summary'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label">Article Content</label>
                  <textarea name="content" class="form-control" rows="5" required><?= e($selectedNews['content'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Image URL</label>
                  <input type="text" name="image_url" class="form-control" value="<?= e($selectedNews['image_url'] ?? '') ?>" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Image Alt Text</label>
                  <input type="text" name="image_alt" class="form-control" value="<?= e($selectedNews['image_alt'] ?? '') ?>" />
                </div>
                <div class="col-12">
                  <label class="form-label">Card Summary</label>
                  <input type="text" name="card_summary" class="form-control" value="<?= e($selectedNews['card_summary'] ?? '') ?>" />
                </div>
                <div class="col-12 d-flex flex-wrap gap-3">
                  <button type="submit" name="action" value="create_news" class="btn btn-success btn-lg">Create News</button>
                  <button type="submit" name="action" value="update_news" class="btn btn-outline-dark btn-lg">Update News</button>
                  <button type="submit" name="action" value="delete_news" class="btn btn-outline-danger btn-lg" data-confirm="Delete this news item?">Delete News</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
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
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="js/dashboard-charts.js"></script>
  <script>
    window.initAdminDashboardChart(<?= json_encode($adminChartRows, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>);
  </script>
  <script src="js/app.js"></script>
</body>
</html>
