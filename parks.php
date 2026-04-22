<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Basic page metadata -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Page title -->
  <title>NYS Parks - Explore Parks</title>

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
$region = get('region');
$type = get('type');
$sql = "SELECT * FROM parks WHERE 1=1";
$params = [];
if ($search !== '') { $sql .= " AND (name LIKE ? OR description LIKE ? OR amenities LIKE ?)"; $like = "%{$search}%"; array_push($params, $like, $like, $like); }
if ($region !== '') { $sql .= " AND region = ?"; $params[] = $region; }
if ($type !== '') { $sql .= " AND park_type = ?"; $params[] = $type; }
$sql .= " ORDER BY is_featured DESC, name";
$stmt = $db->prepare($sql); $stmt->execute($params); $parks = $stmt->fetchAll();
$regions = $db->query("SELECT DISTINCT region FROM parks ORDER BY region")->fetchAll(PDO::FETCH_COLUMN);
$types = $db->query("SELECT DISTINCT park_type FROM parks ORDER BY park_type")->fetchAll(PDO::FETCH_COLUMN);
?>
<body data-page="parks">
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
        <p class="section-kicker mb-2">Discover parks</p>
        <h1 class="display-6 fw-bold mb-3">Explore parks across New York State</h1>
        <p class="text-muted col-lg-8 mb-0">
          Browse featured destinations, compare regions, and discover the types of outdoor experiences available statewide.
        </p>
      </section>
    </section>

    <section class="py-5">
      <section class="container">
        <section class="row g-4 align-items-start">
          <article class="col-lg-4">
            <section class="soft-card p-4">
              <h2 class="h5 fw-bold mb-3">Park Filters</h2>

              <form method="get">
                <label class="form-label">Search by keyword</label>
                <input type="text" name="q" value="<?= e($search) ?>" class="form-control mb-3" placeholder="Waterfalls, beaches, trails..." />

                <label class="form-label">Region</label>
                <select name="region" class="form-select mb-3">
<option value="">All regions</option>
<?php foreach ($regions as $item): ?>
<option value="<?= e($item) ?>" <?= $region === $item ? 'selected' : '' ?>><?= e($item) ?></option>
<?php endforeach; ?>
</select>

                <label class="form-label">Experience type</label>
                <select name="type" class="form-select mb-4">
<option value="">All experiences</option>
<?php foreach ($types as $item): ?>
<option value="<?= e($item) ?>" <?= $type === $item ? 'selected' : '' ?>><?= e($item) ?></option>
<?php endforeach; ?>
</select>

                <button type="button" class="btn btn-success rounded-pill w-100 fw-semibold">
                  Apply Filters
                </button>
              </form>
            </section>
          </article>

          <article class="col-lg-8">
            <header class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
              <section>
                <h2 class="fw-bold mb-1">Featured Parks</h2>
                <p class="text-muted mb-0">A simple starter layout that can later be filled from SQL data</p>
              </section>

              <a href="map.php" class="map-link">Open map view →</a>
            </header>

            <section class="row g-4" id="parks-grid">
<?php foreach ($parks as $park): ?>
<article class="col-md-6 col-xl-4">
  <figure class="image-card h-100 mb-0">
    <img src="<?= e($park['image_url']) ?>" alt="<?= e($park['name']) ?>" class="image-cover-md" />
    <figcaption class="p-4">
      <p class="section-kicker mb-2"><?= e($park['region']) ?> · <?= e($park['park_type']) ?></p>
      <h3 class="h5 fw-bold mb-2"><?= e($park['name']) ?></h3>
      <p class="text-muted mb-2"><?= e($park['description']) ?></p>
      <p class="small text-muted mb-0"><i class="bi bi-geo-alt me-1"></i><?= e($park['city']) ?>, <?= e($park['state']) ?></p>
    </figcaption>
  </figure>
</article>
<?php endforeach; ?>
<?php if (!$parks): ?><p class="text-muted">No parks matched your filters.</p><?php endif; ?>
</section>
        </article>
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
