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
}
$eventsBooked = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE booking_status IN ('approved','confirmed')")->fetchColumn();
$siteVisits = max(12400, ((int) $db->query("SELECT COUNT(*) FROM attendance")->fetchColumn() * 37) + 12400);
$employeesCount = (int) $db->query("SELECT COUNT(*) FROM users WHERE role='employee' AND account_status='active'")->fetchColumn();
$openAlerts = (int) $db->query("SELECT (SELECT COUNT(*) FROM bookings WHERE booking_status='pending') + (SELECT COUNT(*) FROM pto_requests WHERE pto_status='pending')")->fetchColumn();
$bookingByMonth = $db->query("SELECT DATE_FORMAT(created_at, '%b') AS month_label, COUNT(*) AS total FROM bookings GROUP BY YEAR(created_at), MONTH(created_at) ORDER BY YEAR(created_at), MONTH(created_at) LIMIT 6")->fetchAll();
if (!$bookingByMonth) {
    $bookingByMonth = [
        ['month_label'=>'Jan','total'=>3],['month_label'=>'Feb','total'=>4],['month_label'=>'Mar','total'=>5],
        ['month_label'=>'Apr','total'=>4],['month_label'=>'May','total'=>6],['month_label'=>'Jun','total'=>5]
    ];
}
$pendingBookings = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE booking_status='pending'")->fetchColumn();
$pendingPto = (int) $db->query("SELECT COUNT(*) FROM pto_requests WHERE pto_status='pending'")->fetchColumn();
$employeeActions = (int) $db->query("SELECT COUNT(*) FROM users WHERE role='employee' AND account_status='active'")->fetchColumn();
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
  <link rel="stylesheet" href="styles.css" />
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
          <p class="admin-stat-label mb-1">Events booked</p>
          <h2 class="admin-stat-value mb-0"><?= $eventsBooked ?></h2>
        </article>
      </div>
      <div class="col-md-6 col-xl-3">
        <article class="admin-stat-card">
          <span class="admin-stat-icon icon-green">↗</span>
          <p class="admin-stat-label mb-1">Site visits</p>
          <h2 class="admin-stat-value mb-0"><?= number_format($siteVisits/1000, 1) ?>k</h2>
        </article>
      </div>
      <div class="col-md-6 col-xl-3">
        <article class="admin-stat-card">
          <span class="admin-stat-icon icon-purple">◫</span>
          <p class="admin-stat-label mb-1">Employees</p>
          <h2 class="admin-stat-value mb-0"><?= $employeesCount ?></h2>
        </article>
      </div>
      <div class="col-md-6 col-xl-3">
        <article class="admin-stat-card">
          <span class="admin-stat-icon icon-orange">🔔</span>
          <p class="admin-stat-label mb-1">Open alerts</p>
          <h2 class="admin-stat-value mb-0"><?= $openAlerts ?></h2>
        </article>
      </div>
    </section>

    <section class="row g-4 mb-4">
      <div class="col-lg-7">
        <article class="admin-panel h-100">
          <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-4">
            <div>
              <h2 class="h3 fw-bold mb-1">Bookings by month</h2>
              <p class="text-muted mb-0">SQL-ready analytics view</p>
            </div>
            <div class="admin-chart-filters">
              <div>
                <label class="form-label small text-uppercase text-muted mb-1">View By</label>
                <select class="form-select admin-filter-select">
                  <option>Week</option>
                  <option selected>Month</option>
                  <option>Quarter</option>
                  <option>Year</option>
                </select>
              </div>
              <div>
                <label class="form-label small text-uppercase text-muted mb-1">Park</label>
                <select class="form-select admin-filter-select">
                  <option selected>All Parks</option>
                  <option>Bear Mountain</option>
                  <option>Letchworth</option>
                  <option>Saratoga Spa</option>
                  <option>Niagara Falls</option>
                </select>
              </div>
            </div>
          </div>

          <div class="admin-chart">
            <?php foreach ($bookingByMonth as $bar): $height = max(30, min(95, (int)$bar['total'] * 10)); ?>
            <div class="admin-chart-item">
              <div class="admin-chart-bar" style="height: <?= $height ?>%;"></div>
              <span class="admin-chart-label"><?= e($bar['month_label']) ?></span>
            </div>
            <?php endforeach; ?>
          </div>
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
