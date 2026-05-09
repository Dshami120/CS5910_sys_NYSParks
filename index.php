<?php
// Set page metadata.
$pageTitle = 'NYS Parks & Recreation';
$bodyPage = 'home';
$extraHead = '';
?>
<?php require 'bootstrap.php';
$featuredParks = $db->query("SELECT * FROM parks WHERE is_featured=1 ORDER BY id LIMIT 6")->fetchAll();
$featuredEvents = $db->query("SELECT e.*, p.name AS park_name, p.region, p.image_url AS park_image, p.image_alt AS park_image_alt, COALESCE(e.image_url, p.image_url) AS card_image, COALESCE(e.image_alt, p.image_alt, e.title) AS card_image_alt FROM events e JOIN parks p ON p.id=e.park_id WHERE e.event_status='published' AND e.start_datetime >= NOW() ORDER BY e.start_datetime LIMIT 6")->fetchAll();
$regions = $db->query("SELECT DISTINCT region FROM parks WHERE region IS NOT NULL AND region <> '' ORDER BY region")->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include __DIR__ . '/includes/header.php'; ?>
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

        <form class="search-shell mx-auto p-2 d-flex flex-column flex-lg-row gap-2 " action="search.php" method="get">
          <input
            type="text"
            class="form-control border-0 shadow-none py-3 flex-grow-1"
            placeholder="Search: parks, events, news, maps, Ai, donate, about us, FAQ & more*"
            name="q"
          />

            <select class="form-select border-0 shadow-none py-3 flex-grow-0 w-auto" name="region">
                <option value="">Any Region</option>

                <?php foreach ($regions as $regionName): ?>
                    <option value="<?= e($regionName) ?>">
                        <?= e($regionName) ?>
                    </option>
                <?php endforeach; ?>
            </select>

          <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
            Search
          </button>
        </form>

          <ul class="list-unstyled d-flex flex-wrap justify-content-center gap-2 mt-4 mb-0">
              <li><a href="search.php?q=Beach" class="filter-pill">Beaches</a></li>
              <li><a href="search.php?q=Hiking%20Trails" class="filter-pill">Hiking Trails</a></li>
              <li><a href="search.php?q=Camping" class="filter-pill">Camping</a></li>
              <li><a href="search.php?q=Concert" class="filter-pill">Concerts</a></li>
              <li><a href="search.php?q=Picnic" class="filter-pill">Picnic Spots</a></li>
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
    <a href="parks.php" class="text-decoration-none text-reset d-block h-100">
      <figure class="image-card h-100 mb-0">
      <img src="<?= e($park['image_url']) ?>" alt="<?= e($park['name']) ?>" class="image-cover-md" />
      <figcaption class="p-4">
        <p class="section-kicker mb-2"><?= e($park['region']) ?> · <?= e($park['park_type']) ?></p>
        <h3 class="h5 fw-bold mb-2"><?= e($park['name']) ?></h3>
        <p class="text-muted mb-3"><?= e($park['description']) ?></p>
        <span class="btn btn-success w-100 rounded-pill fw-semibold">View Park</span>
      </figcaption>
      </figure>
    </a>
  </article>
<?php endforeach; ?>
</section>
      </section>
    </section>

    <section class="py-5">
      <section class="container">
        <header class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
          <section>
            <h2 class="fw-bold mb-1">Upcoming Events</h2>
            <p class="text-muted mb-0">Browse the next events happening across the state</p>
          </section>

          <a href="events.php" class="map-link">See more →</a>
        </header>

        <section class="row g-4" id="events-grid">
<?php foreach ($featuredEvents as $event): ?>
  <article class="col-md-6 col-xl-4">
    <a href="events.php" class="text-decoration-none text-reset d-block h-100">
      <article class="event-card h-100">
        <img src="<?= e($event['card_image']) ?>" alt="<?= e($event['card_image_alt']) ?>" class="image-cover-md" />
        <section class="p-3 p-lg-4">
          <section class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <section class="event-date-badge text-center py-2 px-1">
              <p class="small text-uppercase text-muted fw-bold mb-1"><?= strtoupper(date('M', strtotime($event['start_datetime']))) ?></p>
              <h3 class="h5 fw-bold mb-0"><?= date('d', strtotime($event['start_datetime'])) ?></h3>
            </section>
            <section class="text-end">
              <p class="category-badge mb-1"><?= e($event['category']) ?></p>
              <?php if ($event['event_type'] === 'private'): ?><span class="badge text-bg-warning mb-1">Private</span><?php else: ?><span class="badge text-bg-success mb-1">Public</span><?php endif; ?>
            </section>
          </section>
          <h3 class="h5 fw-bold mb-2"><?= e($event['title']) ?></h3>
          <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i><?= e($event['park_name']) ?>, <?= e($event['region']) ?></p>
          <p class="small text-muted mb-2"><i class="bi bi-clock me-1"></i><?= date('g:i A', strtotime($event['start_datetime'])) ?></p>
          <p class="small text-muted mb-2"><i class="bi bi-people me-1"></i>Capacity: <?= (int)$event['capacity'] ?></p>
          <p class="small text-muted mb-3"><?= e($event['card_summary'] ?: mb_strimwidth($event['description'], 0, 120, '…')) ?></p>
          <span class="btn btn-success w-100 rounded-pill fw-semibold">View Event</span>
        </section>
      </article>
    </a>
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
              <a href="donate.php" class="btn btn-success rounded-pill px-4 fw-semibold">Donate / Get Involved</a>
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
<!-- Bootstrap JavaScript bundle -->
<!-- Shared site JavaScript -->
<?php include __DIR__ . '/includes/footer.php'; ?>
