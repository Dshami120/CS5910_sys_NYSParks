<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Basic page metadata -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Page title -->
  <title>NYS Parks & Recreation</title>

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
$featuredParks = $db->query("SELECT * FROM parks WHERE is_featured=1 ORDER BY id LIMIT 6")->fetchAll();
$featuredEvents = $db->query("SELECT e.*, p.name AS park_name, p.region, p.image_url AS park_image FROM events e JOIN parks p ON p.id=e.park_id WHERE e.event_status='published' AND e.start_datetime >= NOW() ORDER BY e.start_datetime LIMIT 3")->fetchAll();
?>
<body data-page="home">
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

  <!-- =====================================================
       LANDING PAGE
       - Public entry page
       - Follows the mockup style: full hero, search, cards
       ===================================================== -->
  <main>
    <section class="hero-shell text-white d-flex align-items-center">
      <section class="container text-center">
        <p class="hero-badge mb-3">NEW YORK STATE</p>

        <h1 class="display-3 fw-bold mb-3">
          Experience Your<br />
          <span class="text-accent">Natural Backyard</span>
        </h1>

        <p class="lead hero-subtitle mx-auto mb-4">
          Discover parks, browse events, and explore the outdoors across New York State.
        </p>

        <form class="search-shell mx-auto p-2 d-flex flex-column flex-lg-row gap-2" action="parks.php" method="get">
          <input
            type="text"
            class="form-control border-0 shadow-none py-3"
            placeholder="Search parks, events, and activities"
          />

          <select class="form-select border-0 shadow-none py-3">
            <option>Any Region</option>
            <option>Long Island</option>
            <option>Hudson Valley</option>
            <option>Finger Lakes</option>
            <option>Western New York</option>
          </select>

          <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
            Search
          </button>
        </form>

        <ul class="list-unstyled d-flex flex-wrap justify-content-center gap-2 mt-4 mb-0">
          <li><a href="parks.php" class="filter-pill">Beaches</a></li>
          <li><a href="parks.php" class="filter-pill">Hiking Trails</a></li>
          <li><a href="parks.php" class="filter-pill">Camping</a></li>
          <li><a href="events.php" class="filter-pill">Concerts</a></li>
          <li><a href="parks.php" class="filter-pill">Picnic Spots</a></li>
        </ul>
      </section>
    </section>

    <section class="py-5 bg-light">
      <section class="container">
        <header class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
          <section>
            <h2 class="fw-bold mb-1">Trending Destinations</h2>
            <p class="text-muted mb-0">Most visited parks this season</p>
          </section>

          <a href="map.php" class="map-link">View on Map →</a>
        </header>

        <section class="row g-4" id="parks-grid">
<?php foreach ($featuredParks as $park): ?>
  <article class="col-md-6 col-xl-4">
    <figure class="image-card h-100 mb-0">
      <img src="<?= e($park['image_url']) ?>" alt="<?= e($park['name']) ?>" class="image-cover-md" />
      <figcaption class="p-4">
        <p class="section-kicker mb-2"><?= e($park['region']) ?> · <?= e($park['park_type']) ?></p>
        <h3 class="h5 fw-bold mb-2"><?= e($park['name']) ?></h3>
        <p class="text-muted mb-0"><?= e($park['description']) ?></p>
      </figcaption>
    </figure>
  </article>
<?php endforeach; ?>
</section>
      </section>
    </section>

    <section class="py-5">
      <section class="container">
        <header class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
          <section>
            <h2 class="fw-bold mb-1">Featured Events</h2>
            <p class="text-muted mb-0">Browse public events happening across the state</p>
          </section>

          <a href="events.php" class="map-link">Explore all events →</a>
        </header>

        <section class="row g-4" id="events-grid">
<?php foreach ($featuredEvents as $event): ?>
  <article class="col-md-6 col-xl-4">
    <article class="event-card h-100">
      <img src="<?= e($event['park_image']) ?>" alt="<?= e($event['title']) ?>" class="image-cover-event" />
      <section class="p-3 p-lg-4">
        <section class="d-flex justify-content-between align-items-start gap-3 mb-3">
          <section class="event-date-badge text-center py-2 px-1">
            <p class="small text-uppercase text-muted fw-bold mb-1"><?= strtoupper(date('M', strtotime($event['start_datetime']))) ?></p>
            <h3 class="h5 fw-bold mb-0"><?= date('d', strtotime($event['start_datetime'])) ?></h3>
          </section>
          <section class="text-end">
            <p class="category-badge mb-1"><?= e($event['category']) ?></p>
            <p class="small text-muted mb-0">From $<?= number_format((float)$event['ticket_price'], 0) ?></p>
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
</section>
      </section>
    </section>

    <section class="pb-5">
      <section class="container">
        <section class="feature-panel p-4 p-lg-5">
          <section class="row align-items-center g-4">
            <article class="col-lg-6">
              <p class="section-kicker text-white-50 mb-2">Smart assistance</p>
              <h2 class="display-6 fw-bold mb-3">Plan your visit with AI guidance</h2>
              <p class="mb-4 text-white-50">
                Ask questions about parks, events, accessibility, and outdoor activities without digging through pages.
              </p>
              <ul class="list-unstyled d-flex flex-wrap gap-2 mb-0">
                <li><a href="ai.php" class="btn btn-success rounded-pill px-4 fw-semibold">Try AI Guide</a></li>
                <li><a href="about.php" class="btn btn-outline-light rounded-pill px-4 fw-semibold">Learn More</a></li>
              </ul>
            </article>

            <article class="col-lg-6">
              <section class="soft-card p-4 bg-transparent border border-light-subtle">
                <p class="chat-bubble assistant mb-3">
                  Hi! I can help you find waterfront parks, quiet hiking routes, and upcoming family events.
                </p>
                <p class="chat-bubble user mb-0">
                  Show me scenic parks with trails and picnic areas near the Hudson Valley.
                </p>
              </section>
            </article>
          </section>
        </section>
      </section>
    </section>

    <section class="pb-5">
      <section class="container">
        <section class="row g-4">
          <article class="col-lg-7">
            <section class="simple-card p-4 p-lg-5 h-100">
              <p class="section-kicker mb-2">Support the parks</p>
              <h2 class="fw-bold mb-3">Help preserve New York's outdoor spaces</h2>
              <p class="text-muted mb-4">
                Donations help support programming, park improvements, recreation access, and environmental stewardship.
              </p>
              <a href="register.php" class="btn btn-success rounded-pill px-4 fw-semibold">Donate / Get Involved</a>
            </section>
          </article>

          <article class="col-lg-5">
            <section class="simple-card p-4 h-100">
              <p class="section-kicker mb-2">Quick access</p>
              <ul class="list-unstyled m-0">
                <li class="mb-3"><a href="parks.php" class="map-link">Browse park information</a></li>
                <li class="mb-3"><a href="events.php" class="map-link">View upcoming events</a></li>
                <li class="mb-3"><a href="map.php" class="map-link">Open the statewide map</a></li>
                <li><a href="faq.php" class="map-link">Read FAQs</a></li>
              </ul>
            </section>
          </article>
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
