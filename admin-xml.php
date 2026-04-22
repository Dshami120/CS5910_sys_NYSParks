<?php
require 'bootstrap.php';
$user = require_role($db, 'admin');
if (isset($_GET['download']) && $_GET['download'] === '1') {
    $dataset = get('dataset', 'events');
    $allowed = ['events','bookings','parks','employees'];
    if (!in_array($dataset, $allowed, true)) { $dataset = 'events'; }
    $table = match($dataset) {
        'bookings' => 'bookings',
        'parks' => 'parks',
        'employees' => 'users',
        default => 'events'
    };
    $stmt = $db->query("SELECT * FROM {$table}");
    $rows = $stmt->fetchAll();
    header('Content-Type: application/xml; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . csv_title($dataset) . '"');
    $root = new SimpleXMLElement('<export/>');
    $root->addChild('dataset', $dataset);
    foreach ($rows as $row) {
        $item = $root->addChild(rtrim($dataset, 's'));
        foreach ($row as $key => $value) {
            $item->addChild($key, htmlspecialchars((string)$value));
        }
    }
    echo $root->asXML();
    exit;
}
$recent = [
    ['name'=>'events_export.xml','dataset'=>'Events','date'=>date('m/d/Y'),'status'=>'Ready'],
    ['name'=>'bookings_export.xml','dataset'=>'Bookings','date'=>date('m/d/Y'),'status'=>'Ready'],
];
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Admin XML Export</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="admin-xml">
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
          <li><a href="admin-dashboard.php" class="nav-link-custom" data-page-link="admin-dashboard"><i class="bi bi-speedometer2"></i>Dashboard</a></li>
          <li><a href="admin-employee-schedule.php" class="nav-link-custom" data-page-link="admin-employee-schedule"><i class="bi bi-calendar3"></i>Schedule</a></li>
          <li><a href="admin-pto.php" class="nav-link-custom" data-page-link="admin-pto"><i class="bi bi-calendar-check"></i>PTO</a></li>
          <li><a href="admin-bookings.php" class="nav-link-custom" data-page-link="admin-bookings"><i class="bi bi-journal-check"></i>Bookings</a></li>
          <li><a href="admin-xml.php" class="nav-link-custom" data-page-link="admin-xml"><i class="bi bi-code-slash"></i>XML</a></li>
        </ul>
      </section>

      <ul class="list-unstyled d-flex flex-wrap gap-2 gap-lg-3 m-0 align-items-center">
        <li><a href="account.php" class="nav-link-custom" data-page-link="account"><i class="bi bi-person-circle"></i>Account</a></li>
        <li><a href="logout.php" class="nav-link-custom" data-page-link="logout"><i class="bi bi-box-arrow-right"></i>Logout</a></li>
        <li><a href="#" class="nav-link-custom"><i class="bi bi-bell"></i></a></li>
        <li>
          <a href="account.php" class="text-decoration-none text-dark d-inline-flex align-items-center gap-2">
            <img
              src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=200&q=80"
              alt="User profile photo"
              class="avatar-chip"
            />
            <span class="small fw-semibold d-none d-md-inline">Dev User</span>
          </a>
        </li>
      </ul>
    </nav>
  </header>


  <main class="py-5">
    <section class="container">
      <header class="mb-4">
        <p class="section-kicker mb-2">Admin portal</p>
        <h1 class="h2 fw-bold mb-1">XML Export Center</h1>
        <p class="text-muted mb-0">Prepare XML exports for events, bookings, and park records.</p>
      </header>

      <section class="row g-4">
        <article class="col-lg-4">
          <section class="form-panel p-4 h-100">
            <h2 class="h5 fw-bold mb-3">Generate export</h2>
            <form method="get">
              <label class="form-label">Dataset</label>
              <select name="dataset" class="form-select mb-3">
                <option>Events</option>
                <option>Bookings</option>
                <option>Parks</option>
                <option>Employees</option>
              </select>
              <label class="form-label">Date range</label>
              <input type="date" class="form-control mb-3" />
              <input type="date" class="form-control mb-4" />
              <input type="hidden" name="download" value="1" /><button class="btn btn-success rounded-pill w-100">Generate XML</button>
            </form>
          </section>
        </article>
        <article class="col-lg-8">
          <section class="list-shell">
            <section class="p-4 border-bottom">
              <h2 class="h5 fw-bold mb-1">Recent exports</h2>
              <p class="text-muted mb-0">Starter log table for generated XML files</p>
            </section>
            <?php foreach ($recent as $row): ?>
<section class="list-row p-4"><section class="row g-3"><article class="col-md-4 fw-semibold"><?= e($row['name']) ?></article><article class="col-md-3 text-muted"><?= e($row['dataset']) ?></article><article class="col-md-3 text-muted"><?= e($row['date']) ?></article><article class="col-md-2"><span class="status-pill status-approved"><?= e($row['status']) ?></span></article></section></section>
<?php endforeach; ?>
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

