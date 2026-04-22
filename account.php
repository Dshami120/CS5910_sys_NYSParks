<?php
require 'bootstrap.php';
$user = require_login($db);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full = post('full_name');
    [$first, $last] = split_name($full);
    if (!$first || !$last) {
        flash_set('error', 'Enter first and last name.');
        redirect('account.php');
    }
    $db->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, organization=?, birthdate=?, notes=? WHERE id=?")
       ->execute([$first, $last, post('phone') ?: null, post('organization') ?: null, post('birthdate') ?: null, post('notes') ?: null, $user['id']]);
    flash_set('success', 'Profile updated.');
    redirect('account.php');
}
$user = current_user($db);
$dashboardPath = match ($user['role']) {
    'admin' => 'admin-dashboard.php',
    'employee' => 'employee-dashboard.php',
    default => 'client-dashboard.php',
};
$dashboardLabel = match ($user['role']) {
    'admin' => 'Open Admin Dashboard',
    'employee' => 'Open Employee Dashboard',
    default => 'Open Client Dashboard',
};
$actionPath = match ($user['role']) {
    'admin' => 'admin-dashboard.php',
    'employee' => 'employee-pto.php',
    default => 'client-create-event.php',
};
$actionLabel = match ($user['role']) {
    'admin' => 'Open Admin Tools',
    'employee' => 'Create PTO Request',
    default => 'Create Event Request',
};
if ($user['role'] === 'client') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE client_id=? AND booking_status='pending'");
    $stmt->execute([$user['id']]);
    $stat1 = ['Open requests', (int) $stmt->fetchColumn(), 'Pending approval from the admin booking queue.'];
    $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE client_id=? AND booking_status IN ('approved','confirmed')");
    $stmt->execute([$user['id']]);
    $stat2 = ['Confirmed events', (int) $stmt->fetchColumn(), 'Already approved and scheduled at NYS Parks locations.'];
    $stat3 = ['Saved parks', (int) $db->query("SELECT COUNT(*) FROM parks WHERE is_featured=1")->fetchColumn(), 'Quick access to favorite parks and event venues.'];
    $detailsTitle = 'Client account details';
    $detailsText = 'Editable front-end profile fields for the future users table binding.';
    $roleText = 'Client account';
} elseif ($user['role'] === 'employee') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM employee_schedules WHERE employee_id=? AND shift_date >= CURDATE() AND schedule_status <> 'cancelled'");
    $stmt->execute([$user['id']]);
    $stat1 = ['Upcoming shifts', (int) $stmt->fetchColumn(), 'Future scheduled shifts assigned from the admin portal.'];
    $stmt = $db->prepare("SELECT COUNT(*) FROM pto_requests WHERE employee_id=? AND pto_status='pending'");
    $stmt->execute([$user['id']]);
    $stat2 = ['Pending PTO', (int) $stmt->fetchColumn(), 'Time-off requests waiting for admin review.'];
    $stat3 = ['Assigned park', $user['park_name'] ? 1 : 0, 'Primary park or department assignment on file.'];
    $detailsTitle = 'Employee account details';
    $detailsText = 'Editable profile information tied to the shared users table.';
    $roleText = 'Employee account';
} else {
    $stat1 = ['Managed employees', (int) $db->query("SELECT COUNT(*) FROM users WHERE role='employee' AND account_status='active'")->fetchColumn(), 'Active employee accounts currently managed by admin.'];
    $stat2 = ['Pending approvals', (int) $db->query("SELECT (SELECT COUNT(*) FROM bookings WHERE booking_status='pending') + (SELECT COUNT(*) FROM pto_requests WHERE pto_status='pending')")->fetchColumn(), 'Combined booking and PTO requests waiting in queues.'];
    $stat3 = ['Managed parks', (int) $db->query("SELECT COUNT(*) FROM parks")->fetchColumn(), 'Park records currently available across the site.'];
    $detailsTitle = 'Admin account details';
    $detailsText = 'Shared profile fields for the admin users table record.';
    $roleText = 'Admin account';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Account</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="account">
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
          <li><a href="client-create-event.php" class="nav-link-custom" data-page-link="client-create-event"><i class="bi bi-plus-circle"></i>Create Event</a></li>
        </ul>
      </section>
      <ul class="list-unstyled d-flex flex-wrap gap-3 m-0 align-items-center">
        <li><a href="account.php" class="nav-link-custom active" data-page-link="account"><i class="bi bi-person-circle"></i>Account</a></li>
          <li><a href="logout.php" class="nav-link-custom" data-page-link="logout"><i class="bi bi-box-arrow-right"></i>Logout</a></li>
      </ul>
    </nav>
  </header>
  <main class="py-5">
    <section class="container">
      <section class="row g-4">
        <article class="col-xl-4">
          <section class="soft-card overflow-hidden h-100">
            <section class="profile-banner"></section>
            <section class="p-4">
              <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=200&q=80" alt="Profile photo" class="profile-photo-lg" />
              <h1 class="h3 fw-bold mt-3 mb-1"><?= e(full_name($user)) ?></h1>
              <p class="text-muted mb-3"><?= e($roleText) ?><?= $user['park_name'] ? ' · ' . e($user['park_name']) : '' ?></p>
              <section class="d-grid gap-2">
                <a href="<?= e($dashboardPath) ?>" class="btn btn-success rounded-pill"><?= e($dashboardLabel) ?></a>
                <a href="<?= e($actionPath) ?>" class="btn btn-outline-dark rounded-pill"><?= e($actionLabel) ?></a>
              </section>
            </section>
          </section>
        </article>

        <article class="col-xl-8">
          <section class="row g-4 mb-4">
            <article class="col-md-4">
              <section class="stats-card h-100">
                <p class="small text-uppercase text-muted mb-1"><?= e($stat1[0]) ?></p>
                <h2 class="h3 fw-bold mb-2"><?= e((string) $stat1[1]) ?></h2>
                <p class="text-muted mb-0"><?= e($stat1[2]) ?></p>
              </section>
            </article>
            <article class="col-md-4">
              <section class="stats-card h-100">
                <p class="small text-uppercase text-muted mb-1"><?= e($stat2[0]) ?></p>
                <h2 class="h3 fw-bold mb-2"><?= e((string) $stat2[1]) ?></h2>
                <p class="text-muted mb-0"><?= e($stat2[2]) ?></p>
              </section>
            </article>
            <article class="col-md-4">
              <section class="stats-card h-100">
                <p class="small text-uppercase text-muted mb-1"><?= e($stat3[0]) ?></p>
                <h2 class="h3 fw-bold mb-2"><?= e((string) $stat3[1]) ?></h2>
                <p class="text-muted mb-0"><?= e($stat3[2]) ?></p>
              </section>
            </article>
          </section>

          <section class="list-shell">
            <section class="p-4 border-bottom">
              <h2 class="h5 fw-bold mb-1"><?= e($detailsTitle) ?></h2>
              <p class="text-muted mb-0"><?= e($detailsText) ?></p>
            </section>
            <section class="p-4">
              <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>"><?= e($flash['message']) ?></div><?php endif; ?>
              <form method="post">
                <section class="row g-3">
                  <article class="col-md-6">
                    <label class="form-label">Full name</label>
                    <input class="form-control" name="full_name" value="<?= e(full_name($user)) ?>" />
                  </article>
                  <article class="col-md-6">
                    <label class="form-label">Email</label>
                    <input class="form-control" value="<?= e($user['email']) ?>" readonly />
                  </article>
                  <article class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input class="form-control" name="phone" value="<?= e((string) $user['phone']) ?>" />
                  </article>
                  <article class="col-md-6">
                    <label class="form-label">Organization</label>
                    <input class="form-control" name="organization" value="<?= e((string) $user['organization']) ?>" />
                  </article>
                  <article class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea rows="4" class="form-control" name="notes"><?= e((string) $user['notes']) ?></textarea>
                  </article>
                </section>
                <section class="d-flex flex-wrap gap-2 mt-4">
                  <button type="submit" class="btn btn-success rounded-pill px-4">Save Changes</button>
                  <a href="<?= e($actionPath) ?>" class="btn btn-outline-dark rounded-pill px-4"><?= e($actionLabel) ?></a>
                </section>
              </form>
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
