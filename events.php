<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Basic page metadata -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Page title -->
  <title>NYS Parks - Events</title>

  <!-- Bootstrap core CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />

  <!-- Bootstrap Icons for lightweight icons -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
  />

  <!-- Shared site stylesheet -->
  <link rel="stylesheet" href="styles.css" />
</head>
<?php require 'bootstrap.php';
$search = get('q');
$category = get('category');
$sql = "SELECT e.*, p.name AS park_name, p.region, COALESCE(p.image_url,'') AS image_url FROM events e JOIN parks p ON p.id=e.park_id WHERE e.event_status='published'";
$params = [];
if ($search !== '') { $sql .= " AND (e.title LIKE ? OR e.description LIKE ? OR p.name LIKE ?)"; $like = "%{$search}%"; array_push($params,$like,$like,$like); }
if ($category !== '') { $sql .= " AND e.category = ?"; $params[] = $category; }
$sql .= " ORDER BY e.start_datetime";
$stmt = $db->prepare($sql); $stmt->execute($params); $events = $stmt->fetchAll();
$categories = $db->query("SELECT DISTINCT category FROM events ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>
<body data-page="events">
  <!-- =====================================================
       PUBLIC SITE HEADER
       - Shared top navigation for guest-facing pages
       - Using semantic tags as much as possible
       ===================================================== -->
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
          <li><a href="about.php" class="nav-link-custom" data-page-link="about"><i class="bi bi-info-circle"></i>About Us</a></li>
          <li><a href="faq.php" class="nav-link-custom" data-page-link="faq"><i class="bi bi-question-circle"></i>FAQ</a></li>
          <li><a href="donate.php" class="nav-link-custom" data-page-link="donate"><i class="bi bi-heart"></i>Donate</a></li>
        </ul>
      </section>

      <ul class="list-unstyled d-flex flex-wrap gap-2 gap-lg-3 m-0 align-items-center">
        <li><a href="login.php" class="nav-link-custom" data-page-link="login">Log In</a></li>
        <li><a href="register.php" class="btn btn-dark nav-pill-btn" data-page-link="register">Register</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <section class="page-hero-mini">
      <section class="container">
        <p class="section-kicker mb-2">Upcoming events</p>
        <h1 class="display-6 fw-bold mb-3">Events across New York State</h1>
        <p class="text-muted col-lg-8 mb-0">
          Browse featured public events and use lightweight filters. Later, this can connect to your events table.
        </p>
      </section>
    </section>

    <section class="py-4">
      <section class="container">
        <section class="soft-card p-3 p-lg-4">
          <section class="row g-3 align-items-center">
            <article class="col-lg-4">
              <input type="text" name="q" value="<?= e($search) ?>" class="form-control" placeholder="Search events..." />
            </article>

            <article class="col-lg-5">
              <ul class="list-unstyled d-flex flex-wrap gap-3 m-0">
                <li><button type="button" class="btn btn-link nav-link-custom active-link p-0" data-category="All">All</button></li>
                <li><button type="button" class="btn btn-link nav-link-custom p-0" data-category="Music">Music</button></li>
                <li><button type="button" class="btn btn-link nav-link-custom p-0" data-category="Wellness">Wellness</button></li>
                <li><button type="button" class="btn btn-link nav-link-custom p-0" data-category="Food">Food</button></li>
                <li><button type="button" class="btn btn-link nav-link-custom p-0" data-category="Sports">Sports</button></li>
              </ul>
            </article>

            <article class="col-lg-3 text-lg-end">
              <a href="client-create-event.php" class="btn btn-dark rounded-pill px-4">Book / Create Request</a>
            </article>
          </section>
        </section>
      </section>
    </section>

    <section class="pb-5">
      <section class="container">
        <section class="row g-4" id="events-grid">
<?php foreach ($events as $event): ?>
<article class="col-md-6 col-xl-4">
  <article class="event-card h-100">
    <img src="<?= e($event['image_url']) ?>" alt="<?= e($event['title']) ?>" class="image-cover-event" />
    <section class="p-3 p-lg-4">
      <section class="d-flex justify-content-between align-items-start gap-3 mb-3">
        <section class="event-date-badge text-center py-2 px-1">
          <p class="small text-uppercase text-muted fw-bold mb-1"><?= strtoupper(date('M', strtotime($event['start_datetime']))) ?></p>
          <h3 class="h5 fw-bold mb-0"><?= date('d', strtotime($event['start_datetime'])) ?></h3>
        </section>
        <section class="text-end">
          <p class="category-badge mb-1"><?= e($event['category']) ?></p>
          <p class="small text-muted mb-0">From <?= ((float)$event['ticket_price'] > 0 ? '$' . number_format((float)$event['ticket_price'], 0) : 'Free') ?></p>
        </section>
      </section>
      <h3 class="h5 fw-bold mb-2"><?= e($event['title']) ?></h3>
      <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i><?= e($event['park_name']) ?>, <?= e($event['region']) ?></p>
      <p class="small text-muted mb-3"><i class="bi bi-clock me-1"></i><?= date('g:i A', strtotime($event['start_datetime'])) ?></p>
      <a href="client-create-event.php" class="btn btn-success w-100 rounded-pill fw-semibold">View Details & Book</a>
    </section>
  </article>
</article>
<?php endforeach; ?>
<?php if (!$events): ?><p class="text-muted">No events matched your filters.</p><?php endif; ?>
</section>
    </section>
    </section>
  </main>
  <!-- =====================================================
       SHARED FOOTER
       - Keeps the bottom section consistent across pages
       ===================================================== -->
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

  <!-- Bootstrap JavaScript bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Shared site JavaScript -->
  <script src="app.js"></script>
</body>
</html>
