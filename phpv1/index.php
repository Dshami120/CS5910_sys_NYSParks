<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Home | ' . SITE_NAME;
$activePage = 'home';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$featuredParks = getFeaturedParks(3);
$upcomingEvents = getUpcomingEvents(3);
?>
<main>
    <section class="hero-section text-center text-white d-flex align-items-center">
        <section class="container position-relative hero-content-shell">
            <p class="hero-badge mb-3">NEW YORK STATE</p>
            <h1 class="display-3 fw-bold mb-3">
                Experience Your<br>
                <span class="text-accent">Natural Backyard</span>
            </h1>
            <p class="lead hero-subtitle mx-auto mb-4">
                Discover parks, book events, and explore the outdoors across New York State.
            </p>

            <form class="search-bar hero-search mx-auto p-2 d-flex flex-column flex-lg-row gap-2" action="parks.php" method="get">
                <input type="text" class="form-control border-0 shadow-none" name="search" placeholder="Search parks, events (try 'Summer')">
                <select class="form-select border-0 shadow-none location-select" name="region">
                    <option value="">Any Region</option>
                    <option>Long Island</option>
                    <option>Hudson Valley</option>
                    <option>Capital Region</option>
                    <option>Finger Lakes</option>
                    <option>Central New York</option>
                    <option>North Country</option>
                    <option>Western New York</option>
                    <option>New York City</option>
                </select>
                <button type="submit" class="btn btn-success rounded-pill px-4">Search</button>
            </form>

            <ul class="list-unstyled d-flex flex-wrap justify-content-center gap-2 mt-4 mb-0">
                <li><a href="parks.php?type=Beach" class="tag-pill">Beaches</a></li>
                <li><a href="parks.php?search=hiking" class="tag-pill">Hiking Trails</a></li>
                <li><a href="parks.php?search=camping" class="tag-pill">Camping</a></li>
                <li><a href="events.php?category=concert" class="tag-pill">Concerts</a></li>
                <li><a href="parks.php?search=picnic" class="tag-pill">Picnic Spots</a></li>
            </ul>
        </section>
    </section>

    <section class="py-5 bg-light section-trending">
        <section class="container">
            <header class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <section>
                    <h2 class="fw-bold mb-1">Trending Destinations</h2>
                    <p class="text-muted mb-0">Most visited parks this season</p>
                </section>
                <a href="map.php" class="map-link text-decoration-none fw-semibold">View on Map &rarr;</a>
            </header>

            <section class="row g-4">
                <?php foreach ($featuredParks as $park): ?>
                    <article class="col-md-4">
                        <figure class="park-card mb-0 h-100">
                            <img src="<?php echo e($park['image_url']); ?>" alt="<?php echo e($park['park_name']); ?>" class="img-fluid w-100 card-image">
                            <figcaption class="p-3 p-lg-4">
                                <p class="small text-success fw-semibold mb-2"><?php echo e($park['region'] ?? 'New York State'); ?></p>
                                <h3 class="h5 mb-1 fw-bold"><?php echo e($park['park_name']); ?></h3>
                                <p class="text-muted mb-0"><?php echo e($park['summary'] ?? $park['amenities'] ?? 'Outdoor recreation, scenic views, and seasonal experiences.'); ?></p>
                            </figcaption>
                        </figure>
                    </article>
                <?php endforeach; ?>
            </section>
        </section>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Upcoming Events</h2>
                    <p class="text-muted mb-0">Public programs, seasonal happenings, and outdoor experiences.</p>
                </div>
                <a href="events.php" class="map-link text-decoration-none fw-semibold">View all events &rarr;</a>
            </div>

            <div class="row g-4">
                <?php foreach ($upcomingEvents as $event): ?>
                    <div class="col-lg-4">
                        <div class="event-home-card h-100">
                            <div class="event-home-top d-flex justify-content-between align-items-start gap-3 mb-3">
                                <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2 text-uppercase"><?php echo e($event['event_type']); ?></span>
                                <span class="event-home-date"><?php echo date('M j', strtotime($event['start_datetime'])); ?></span>
                            </div>
                            <h3 class="h5 fw-bold mb-2"><?php echo e($event['title']); ?></h3>
                            <p class="text-muted mb-2"><i class="bi bi-geo-alt me-1"></i><?php echo e($event['park_name']); ?></p>
                            <p class="text-muted mb-4"><i class="bi bi-clock me-1"></i><?php echo date('g:i A', strtotime($event['start_datetime'])); ?></p>
                            <a href="events.php" class="btn btn-outline-success rounded-pill px-4">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="ai-guide-banner p-4 p-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-6">
                        <p class="hero-badge hero-badge-dark mb-3">AI GUIDE</p>
                        <h2 class="fw-bold text-white mb-3">Plan your next trip faster</h2>
                        <p class="text-white-50 mb-4">Ask the chatbot for family-friendly parks, places to hike, or ideas for weekend events anywhere in New York State.</p>
                        <a href="ai-guide.php" class="btn btn-success rounded-pill px-4">Open AI Guide</a>
                    </div>
                    <div class="col-lg-6">
                        <div class="chat-demo-card">
                            <div class="chat-bubble chat-bot">Hi! I can help you find family-friendly parks, weekend events, and scenic hiking spots.</div>
                            <div class="chat-bubble chat-user">Show me a good park in the Hudson Valley with hiking and waterfalls.</div>
                            <div class="chat-bubble chat-bot">Try Watkins Glen or Minnewaska for dramatic scenery, hiking, and photo-friendly views.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
