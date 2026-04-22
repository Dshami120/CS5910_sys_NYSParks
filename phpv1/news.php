<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'News | ' . SITE_NAME;
$activePage = 'news';

$newsItems = [
    [
        'title' => 'Spring trail readiness updates across NYS parks',
        'topic' => 'alerts',
        'date' => 'April 14, 2026',
        'region' => 'Statewide',
        'summary' => 'Trail teams are preparing signage, cleanup areas, and visitor guidance ahead of the spring season.',
        'content' => 'This mock update shows how the capstone site can present important public-facing park notices in one clean statewide feed. Later, these stories can come from a database table managed by admins.',
        'image_url' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
        'tag' => 'Trail Conditions',
    ],
    [
        'title' => 'Weekend volunteer day expands native planting projects',
        'topic' => 'community',
        'date' => 'April 10, 2026',
        'region' => 'Hudson Valley',
        'summary' => 'Community volunteers are helping improve park landscapes, restore native plant beds, and support seasonal beautification efforts.',
        'content' => 'This item demonstrates a community-focused news post. It helps show public engagement, mission-driven updates, and statewide participation opportunities on the redesigned site.',
        'image_url' => 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&w=1200&q=80',
        'tag' => 'Community',
    ],
    [
        'title' => 'New family nature programs added to the events calendar',
        'topic' => 'events',
        'date' => 'April 6, 2026',
        'region' => 'Finger Lakes',
        'summary' => 'The public events calendar now highlights more kid-friendly hikes, ranger talks, and beginner outdoor learning sessions.',
        'content' => 'This post connects the News page to the Events page and shows how the public side of the site can guide users toward upcoming programming.',
        'image_url' => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&w=1200&q=80',
        'tag' => 'Programs',
    ],
    [
        'title' => 'Visitor safety reminder for waterfront and gorge areas',
        'topic' => 'safety',
        'date' => 'April 3, 2026',
        'region' => 'Southern Tier',
        'summary' => 'Guests are encouraged to stay on marked trails, observe posted guidance, and prepare for changing weather conditions.',
        'content' => 'Safety messages are a good fit for a centralized news feed because they can be surfaced quickly and kept visible across public pages.',
        'image_url' => 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80',
        'tag' => 'Safety',
    ],
    [
        'title' => 'Historic site exhibits receive a refreshed digital spotlight',
        'topic' => 'parks',
        'date' => 'March 29, 2026',
        'region' => 'Capital Region',
        'summary' => 'Updated site content helps visitors explore park history, exhibits, and destination highlights more easily online.',
        'content' => 'This news item supports the idea of a more discoverable site with better statewide content organization and easier public browsing.',
        'image_url' => 'https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=1200&q=80',
        'tag' => 'Park Spotlight',
    ],
    [
        'title' => 'Donation campaign supports seasonal improvements and programming',
        'topic' => 'support',
        'date' => 'March 24, 2026',
        'region' => 'Statewide',
        'summary' => 'Public support can help fund park improvements, community programming, and visitor experience projects across the state.',
        'content' => 'This article gives the Donate page a natural companion section and shows how news can support public awareness for funding goals.',
        'image_url' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=1200&q=80',
        'tag' => 'Support',
    ],
];

$topicLabels = [
    'all' => 'All Topics',
    'alerts' => 'Alerts',
    'community' => 'Community',
    'events' => 'Events',
    'parks' => 'Park Spotlights',
    'safety' => 'Safety',
    'support' => 'Support',
];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="subpage-hero subpage-hero-news text-white d-flex align-items-center">
    <div class="subpage-hero-overlay"></div>
    <div class="container position-relative py-5">
        <div class="row">
            <div class="col-lg-8">
                <span class="hero-kicker">STATEWIDE UPDATES</span>
                <h1 class="display-5 fw-bold mb-3">News, updates, and public park notices</h1>
                <p class="lead text-white-50 mb-0">
                    This public news feed gives the redesigned NYS Parks site a cleaner place to share alerts,
                    stories, event highlights, and statewide updates.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-light">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-4">
                <div class="news-info-card h-100">
                    <p class="section-label mb-2">WHY THIS PAGE MATTERS</p>
                    <h2 class="h4 fw-bold mb-3">A clearer public update hub</h2>
                    <p class="text-muted mb-0">
                        Your concept doc calls out missing live park-related updates and poor discoverability.
                        This page gives the public side of the site a clean statewide place for that content.
                    </p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="news-info-card h-100">
                    <p class="section-label mb-2">EASY TO DEMO</p>
                    <h2 class="h4 fw-bold mb-3">Searchable and filterable</h2>
                    <p class="text-muted mb-0">
                        Users can search by keyword, filter by topic, and quickly scan updates without digging through
                        cluttered navigation.
                    </p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="news-info-card h-100">
                    <p class="section-label mb-2">FUTURE READY</p>
                    <h2 class="h4 fw-bold mb-3">Easy to connect later</h2>
                    <p class="text-muted mb-0">
                        Right now the updates are stored in a PHP array. Later, admins can publish articles from a
                        database table or CMS without changing the page layout.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm news-filter-card sticky-lg-top" style="top: 96px;">
                    <div class="card-body p-4">
                        <p class="section-label mb-2">FILTER THE FEED</p>
                        <h2 class="h4 fw-bold mb-3">Find the updates you need</h2>
                        <p class="text-muted small mb-4">
                            Search the news feed by keywords like <strong>trail</strong>, <strong>event</strong>,
                            <strong>safety</strong>, or <strong>donation</strong>.
                        </p>

                        <div class="mb-4">
                            <label for="newsSearch" class="form-label">Search news</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="newsSearch" placeholder="Search news updates...">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-block">Filter by topic</label>
                            <div class="news-topic-grid">
                                <?php foreach ($topicLabels as $topicValue => $topicLabel): ?>
                                    <button type="button"
                                            class="btn btn-outline-success news-topic-button <?php echo $topicValue === 'all' ? 'active' : ''; ?>"
                                            data-news-topic="<?php echo e($topicValue); ?>">
                                        <?php echo e($topicLabel); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <span class="text-muted small" id="newsResultsText">Showing <?php echo count($newsItems); ?> updates</span>
                            <button type="button" class="btn btn-sm btn-link text-success text-decoration-none p-0" id="resetNewsFilters">Reset</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="news-highlight-card p-4 p-lg-5 mb-4">
                    <div class="row align-items-center g-4">
                        <div class="col-md-8">
                            <span class="badge rounded-pill px-3 py-2 text-white mb-3">Featured Public Update</span>
                            <h2 class="fw-bold mb-3">Centralized public news supports a better statewide experience</h2>
                            <p class="mb-0 text-white-50">
                                This page helps solve one of the public-site problems identified in your project concept:
                                important updates, notices, and stories should be easier to find, cleaner to scan, and
                                simpler to maintain.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="events.php" class="btn btn-light px-4">View Events</a>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <p class="section-label mb-2">PUBLIC NEWS FEED</p>
                        <h2 class="fw-bold mb-0">Latest updates</h2>
                    </div>
                    <span class="badge rounded-pill text-bg-light border px-3 py-2">
                        <i class="bi bi-newspaper me-1"></i>
                        <span id="newsCount"><?php echo count($newsItems); ?></span> updates
                    </span>
                </div>

                <div class="row g-4">
                    <?php foreach ($newsItems as $item): ?>
                        <?php
                        $itemSearch = strtolower($item['title'] . ' ' . $item['summary'] . ' ' . $item['content'] . ' ' . $item['region'] . ' ' . $item['tag']);
                        ?>
                        <div class="col-12 news-item"
                             data-topic="<?php echo e($item['topic']); ?>"
                             data-search="<?php echo e($itemSearch); ?>">
                            <article class="card border-0 news-card h-100">
                                <div class="row g-0 h-100">
                                    <div class="col-md-4">
                                        <div class="news-card-image h-100" style="background-image: url('<?php echo e($item['image_url']); ?>');"></div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body p-4 p-lg-4 d-flex flex-column h-100">
                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                <span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle"><?php echo e($item['tag']); ?></span>
                                                <span class="badge text-bg-light border"><?php echo e($topicLabels[$item['topic']] ?? 'Update'); ?></span>
                                            </div>

                                            <h3 class="h4 fw-bold mb-3"><?php echo e($item['title']); ?></h3>

                                            <div class="news-meta mb-3">
                                                <span><i class="bi bi-calendar3 me-1"></i><?php echo e($item['date']); ?></span>
                                                <span><i class="bi bi-geo-alt me-1"></i><?php echo e($item['region']); ?></span>
                                            </div>

                                            <p class="text-muted mb-3"><?php echo e($item['summary']); ?></p>
                                            <p class="text-muted mb-4"><?php echo e($item['content']); ?></p>

                                            <div class="mt-auto d-flex flex-wrap gap-2">
                                                <a href="parks.php" class="btn btn-outline-success btn-sm">Explore Parks</a>
                                                <a href="map.php" class="btn btn-light btn-sm border">Open Map</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="noNewsMessage" class="alert alert-light border d-none mt-4">
                    <h3 class="h6 fw-bold mb-1">No news items match your filters</h3>
                    <p class="mb-0 text-muted">Try a different keyword or reset the selected topic.</p>
                </div>

                <div class="card border-0 shadow-sm news-cta-card mt-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row g-4 align-items-center">
                            <div class="col-md-8">
                                <p class="section-label mb-2">KEEP EXPLORING</p>
                                <h2 class="h4 fw-bold mb-2">Turn updates into action</h2>
                                <p class="text-muted mb-0">
                                    After reading statewide updates, public users can jump into the park directory,
                                    browse the events page, or support the system through the donate flow.
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <a href="events.php" class="btn btn-success px-4 me-2 mb-2 mb-md-0">Go to Events</a>
                                <a href="donate.php" class="btn btn-outline-success px-4">Donate</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
