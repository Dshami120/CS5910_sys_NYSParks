<?php
// Load project setup.
require 'bootstrap.php';

$newsItems = $db->query("SELECT id, title, topic, DATE_FORMAT(published_date, '%M %e, %Y') AS date, region, summary, content, COALESCE(image_url,'') AS image_url, COALESCE(image_alt, title) AS image_alt, tag FROM news WHERE news_status='published' ORDER BY published_date DESC, id DESC")->fetchAll();

$topicLabels = ['all'=>'All Topics'];
foreach ($db->query("SELECT DISTINCT topic FROM news WHERE news_status='published' ORDER BY topic")->fetchAll(PDO::FETCH_COLUMN) as $topic) {
    $topicLabels[$topic] = ucwords(str_replace('_', ' ', $topic));
}

$activeNewsRegions = $db->query("
    SELECT DISTINCT region
    FROM news
    WHERE news_status='published'
      AND region IS NOT NULL
      AND region <> ''
    ORDER BY region
")->fetchAll(PDO::FETCH_COLUMN);

$inactiveNewsRegions = $db->query("
    SELECT DISTINCT p.region
    FROM parks p
    WHERE p.region IS NOT NULL
      AND p.region <> ''
      AND p.region NOT IN (
          SELECT DISTINCT n.region
          FROM news n
          WHERE n.news_status='published'
            AND n.region IS NOT NULL
            AND n.region <> ''
      )
    ORDER BY p.region
")->fetchAll(PDO::FETCH_COLUMN);
?>
<?php
// Set page metadata.
$pageTitle = 'NYS Parks - News';
$bodyPage = 'news';
$extraHead = '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>

    <!-- blue hero section -->
    <section class="subpage-hero subpage-hero-news text-white d-flex align-items-center">
        <div class="subpage-hero-overlay"></div>
        <div class="container position-relative py-5">
            <div class="row">
                <div class="col-lg-8">
                    <span class="hero-kicker">STATEWIDE UPDATES</span>
                    <h1 class="display-5 fw-bold mb-3">News, updates, and public park notices</h1>
                    <p class="lead text-white-50 mb-0">Browse published NYS Parks announcements, alerts, notices, stories, event highlights, and statewide updates.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- info cards -->
    <section class="py-5 border-bottom bg-light">
        <div class="container">
            <div class="row g-4 align-items-stretch">

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body p-4">
                            <p class="section-label mb-2">OFFICIAL UPDATES</p>
                            <h2 class="h4 fw-bold mb-3">Public notices in one place</h2>
                            <p class="text-muted mb-0">Find timely announcements, service updates, alerts, and statewide notices from New York State Parks.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body p-4">
                            <p class="section-label mb-2">PLAN YOUR VISIT</p>
                            <h2 class="h4 fw-bold mb-3">Stay informed before you go</h2>
                            <p class="text-muted mb-0">Check updates about trails, facilities, seasonal conditions, programs, and public events before visiting a park.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body p-4">
                            <p class="section-label mb-2">COMMUNITY STORIES</p>
                            <h2 class="h4 fw-bold mb-3">See what is happening statewide</h2>
                            <p class="text-muted mb-0">Explore stories about conservation, education, recreation, volunteers, and park improvements across New York.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- box filter and blue view events box -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4 align-items-start">

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm news-filter-card sticky-lg-top" style="top: 96px;">
                        <div class="card-body p-4">
                            <p class="section-label mb-2">FILTER THE FEED</p>
                            <h2 class="h4 fw-bold mb-3">Find the updates you need</h2>
                            <p class="text-muted small mb-4">Search the news feed by keywords like <strong>trail</strong>, <strong>event</strong>, <strong>safety</strong>, or <strong>donation</strong>.</p>

                            <div class="mb-4">
                                <label for="newsSearch" class="form-label">Search news</label>
                                <div class="input-group input-group-lg">
                  <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                  </span>
                                    <input type="text" class="form-control" id="newsSearch" placeholder="Search news updates...">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label d-block">Filter by topic</label>
                                <div class="news-topic-grid">
                                    <?php foreach($topicLabels as $topicValue=>$topicLabel): ?>
                                        <button type="button" class="btn btn-outline-success news-topic-button <?= $topicValue==='all'?'active':'' ?>" data-news-topic="<?= e($topicValue) ?>">
                                            <?= e($topicLabel) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="newsRegionFilter" class="form-label d-block">Filter by region</label>
                                <select class="form-select" id="newsRegionFilter">
                                    <option value="all">All regions</option>

                                    <?php if ($activeNewsRegions): ?>
                                        <option disabled>── Regions with news ──</option>
                                        <?php foreach ($activeNewsRegions as $regionName): ?>
                                            <option value="<?= e($regionName) ?>"><?= e($regionName) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <?php if ($inactiveNewsRegions): ?>
                                        <option disabled>── Regions with no news currently ──</option>
                                        <?php foreach ($inactiveNewsRegions as $regionName): ?>
                                            <option value="<?= e($regionName) ?>"><?= e($regionName) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <span class="text-muted small">Showing <?= count($newsItems) ?> updates</span>
                                <button type="button" class="btn btn-sm btn-link text-success text-decoration-none p-0" id="resetNewsFilters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="news-highlight-card p-4 p-lg-5">
                        <div class="row align-items-center g-4">

                            <div class="col-md-8">
                                <span class="badge rounded-pill px-3 py-2 text-white mb-3">Featured Public Update</span>
                                <h2 class="fw-bold mb-3">Centralized public news supports a better statewide experience</h2>
                                <p class="mb-0 text-white-50">This page is now backed by the news table, making important updates easier to find, cleaner to scan, and simpler to maintain.</p>
                            </div>

                            <div class="col-md-4 text-md-end">
                                <a href="events.php" class="btn btn-light px-4">View Events</a>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- public news feed / latest updates / showing updates -->
    <section class="pb-5">
        <div class="container">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <p class="section-label mb-2">PUBLIC NEWS FEED</p>
                    <h2 class="fw-bold mb-0">Latest updates</h2>
                </div>

                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <p class="text-muted mb-0" id="newsResultsText">Showing <?= count($newsItems) ?> update<?= count($newsItems) === 1 ? '' : 's' ?></p>

                    <span class="badge rounded-pill text-bg-light border px-3 py-2">
            <i class="bi bi-newspaper me-1"></i>
            <span id="newsCount"><?= count($newsItems) ?></span> update<?= count($newsItems) === 1 ? '' : 's' ?>
          </span>
                </div>
            </div>

            <section class="row g-4" id="news-grid">
                <?php foreach($newsItems as $item): ?>
                    <?php
                    $itemSearch = strtolower($item['title'].' '.$item['summary'].' '.$item['content'].' '.$item['region'].' '.$item['tag'].' '.$item['topic']);
                    $imageUrl = $item['image_url'] !== '' ? $item['image_url'] : 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1200&q=80';
                    ?>

                    <article class="col-md-6 col-xl-4 news-item"
                             id="news-<?= (int)$item['id'] ?>"
                             data-topic="<?= e($item['topic']) ?>"
                             data-region="<?= e($item['region']) ?>"
                             data-search="<?= e($itemSearch) ?>">

                        <article class="news-card h-100">
                            <img src="<?= e($imageUrl) ?>" alt="<?= e($item['image_alt']) ?>" class="image-cover-event" />

                            <div class="card-body p-3 p-lg-4 d-flex flex-column h-100">

                                <section class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <section>
                                        <p class="category-badge mb-1"><?= e($item['tag']) ?></p>
                                        <span class="badge text-bg-light border"><?= e($topicLabels[$item['topic']] ?? 'Update') ?></span>
                                    </section>

                                    <section class="text-end small text-muted">
                                        <i class="bi bi-calendar3 me-1"></i><?= e($item['date']) ?>
                                    </section>
                                </section>

                                <h3 class="h5 fw-bold mb-2"><?= e($item['title']) ?></h3>

                                <p class="small text-muted mb-2">
                                    <i class="bi bi-geo-alt me-1"></i><?= e($item['region']) ?>
                                </p>

                                <p class="small text-muted mb-3 news-card-summary"><?= e($item['summary']) ?></p>

                                <button type="button" class="btn btn-outline-success btn-sm news-open-article">
                                    Open article
                                </button>

                                <div class="collapse news-article-content">
                                    <p class="small text-muted mb-0"><?= e($item['content']) ?></p>
                                </div>

                            </div>
                        </article>

                    </article>

                <?php endforeach; ?>
            </section>

            <section id="noNewsMessage" class="soft-card p-4 text-center d-none mt-4">
                <p class="text-muted mb-3">No news items matched your filters. Try clearing filters or choosing another region.</p>
            </section>

        </div>
    </section>

</main>

<!-- keep exploring section -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm news-cta-card mt-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-md-8">
                        <p class="section-label mb-2">KEEP EXPLORING</p>
                        <h2 class="h4 fw-bold mb-2">Turn updates into action</h2>
                        <p class="text-muted mb-0">After reading statewide updates, public users can jump into the park directory, browse events, or support the system through the donate flow.</p>
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


<!-- footer-->
<script>
document.addEventListener("DOMContentLoaded", () => {
    if (!window.location.hash || !window.location.hash.startsWith("#news-")) return;
    const article = document.querySelector(window.location.hash);
    if (!article) return;
    article.classList.remove("d-none");
    const content = article.querySelector(".news-article-content");
    const button = article.querySelector(".news-open-article");
    if (content) content.classList.add("show");
    if (button) button.textContent = "Close article";
    article.scrollIntoView({ behavior: "smooth", block: "start" });
});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
