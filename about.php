<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Basic page metadata -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Page title -->
  <title>NYS Parks - About Us</title>

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
<body data-page="about">
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
        <p class="section-kicker mb-2">About the department</p>
        <h1 class="display-6 fw-bold mb-3">About NYS Parks &amp; Recreation</h1>
        <p class="text-muted col-lg-8 mb-0">
          A simple public information page that can later be filled from a CMS or database content table.
        </p>
      </section>
    </section>

    <section class="py-5">
      <section class="container">
        <section class="row g-4">
          <article class="col-lg-7">
            <section class="soft-card p-4 p-lg-5 h-100">
              <h2 class="h4 fw-bold mb-3">Our Mission</h2>
              <p class="text-muted">
                We aim to modernize how residents and visitors access park information, events, maps, and services across New York State.
              </p>

              <h2 class="h4 fw-bold mt-5 mb-3">Our History</h2>
              <p class="text-muted mb-0">
                What started as a more fragmented experience is being reimagined into a modern, centralized platform for public access and internal operations.
              </p>
            </section>
          </article>

          <article class="col-lg-5">
            <section class="soft-card p-4 p-lg-5 h-100">
              <h2 class="h4 fw-bold mb-3">Core Values</h2>
              <ul class="list-unstyled m-0">
                <li class="mb-3"><strong>Accessibility:</strong> clear navigation and inclusive design</li>
                <li class="mb-3"><strong>Community:</strong> increased participation in events and public programs</li>
                <li class="mb-3"><strong>Efficiency:</strong> better workflows for bookings, schedules, and PTO</li>
                <li><strong>Stewardship:</strong> protecting parks while improving digital services</li>
              </ul>
            </section>
          </article>
        </section>

        <section class="pt-5">
          <header class="mb-4">
            <h2 class="fw-bold mb-1">Meet the Team</h2>
            <p class="text-muted mb-0">Starter placeholder content based on your existing about page idea</p>
          </header>

          <section class="row g-4">
            <article class="col-md-4">
              <section class="image-card h-100">
                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=900&q=80" alt="Director Sarah Johnson" class="image-cover-sm" />
                <section class="p-4">
                  <h3 class="h5 fw-bold mb-1">Sarah Johnson</h3>
                  <p class="text-success fw-semibold mb-2">Director</p>
                  <p class="text-muted mb-0">Leads modernization strategy, public service delivery, and department planning.</p>
                </section>
              </section>
            </article>

            <article class="col-md-4">
              <section class="image-card h-100">
                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=900&q=80" alt="Deputy Director Mike Chen" class="image-cover-sm" />
                <section class="p-4">
                  <h3 class="h5 fw-bold mb-1">Mike Chen</h3>
                  <p class="text-success fw-semibold mb-2">Deputy Director</p>
                  <p class="text-muted mb-0">Supports operations, staff coordination, and program planning across parks.</p>
                </section>
              </section>
            </article>

            <article class="col-md-4">
              <section class="image-card h-100">
                <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=900&q=80" alt="Recreation Coordinator Lisa Rodriguez" class="image-cover-sm" />
                <section class="p-4">
                  <h3 class="h5 fw-bold mb-1">Lisa Rodriguez</h3>
                  <p class="text-success fw-semibold mb-2">Recreation Coordinator</p>
                  <p class="text-muted mb-0">Coordinates public programming, seasonal activities, and outreach.</p>
                </section>
              </section>
            </article>
          </section>
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
