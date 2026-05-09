<?php
require 'bootstrap.php';

// Read search inputs.
$q = trim(get('q'));
$region = trim(get('region'));

// Normalize region dropdown value.
if ($region === 'Any Region') {
    $region = '';
}

// Prepare search state.
$like = '%' . $q . '%';
$results = [];
$hasSearchRequest = isset($_GET['q']) || isset($_GET['region']);

// Run search only when requested.
if ($hasSearchRequest) {
    $parkSql = "SELECT name, region, park_type, card_summary, description FROM parks WHERE 1=1";
    $parkParams = [];

    // Apply park keyword search.
    if ($q !== '') {
        $parkSql .= " AND (name LIKE ? OR region LIKE ? OR park_type LIKE ? OR amenities LIKE ? OR description LIKE ?)";
        array_push($parkParams, $like, $like, $like, $like, $like);
    }

    // Apply park region search.
    if ($region !== '') {
        $parkSql .= " AND region = ?";
        $parkParams[] = $region;
    }

    // Load park search results.
    $stmt = $db->prepare($parkSql . " ORDER BY is_featured DESC, name LIMIT 12");
    $stmt->execute($parkParams);

    foreach ($stmt->fetchAll() as $row) {
        $results[] = [
            'type' => 'Park',
            'title' => $row['name'],
            'summary' => $row['card_summary'] ?: mb_strimwidth($row['description'], 0, 140, '…'),
            'url' => 'parks.php?q=' . urlencode($row['name'])
        ];
    }

    // Build event search query.
    $eventSql = "SELECT e.title, e.description, e.card_summary, e.event_type, p.name AS park_name, p.region FROM events e JOIN parks p ON p.id=e.park_id WHERE e.event_status='published'";
    $eventParams = [];

    // Apply event keyword search.
    if ($q !== '') {
        $eventSql .= " AND (e.title LIKE ? OR e.description LIKE ? OR e.card_summary LIKE ? OR e.category LIKE ? OR p.name LIKE ? OR p.region LIKE ?)";
        array_push($eventParams, $like, $like, $like, $like, $like, $like);
    }

    // Apply event region search.
    if ($region !== '') {
        $eventSql .= " AND p.region = ?";
        $eventParams[] = $region;
    }

    // Load event search results.
    $stmt = $db->prepare($eventSql . " ORDER BY e.start_datetime ASC LIMIT 12");
    $stmt->execute($eventParams);

    foreach ($stmt->fetchAll() as $row) {
        $label = $row['event_type'] === 'private' ? 'Private event' : 'Event';

        $results[] = [
            'type' => $label,
            'title' => $row['title'],
            'summary' => ($row['card_summary'] ?: mb_strimwidth($row['description'], 0, 140, '…')) . ' · ' . $row['park_name'],
            'url' => 'events.php?q=' . urlencode($row['title'])
        ];
    }

    // Build news search query.
    $newsSql = "SELECT title, summary, content, region, topic FROM news WHERE news_status='published'";
    $newsParams = [];

    // Apply news keyword search.
    if ($q !== '') {
        $newsSql .= " AND (title LIKE ? OR summary LIKE ? OR content LIKE ? OR region LIKE ? OR tag LIKE ? OR topic LIKE ?)";
        array_push($newsParams, $like, $like, $like, $like, $like, $like);
    }

    // Apply news region search.
    if ($region !== '') {
        $newsSql .= " AND region = ?";
        $newsParams[] = $region;
    }

    // Load news search results.
    $stmt = $db->prepare($newsSql . " ORDER BY published_date DESC LIMIT 12");
    $stmt->execute($newsParams);

    foreach ($stmt->fetchAll() as $row) {
        $results[] = [
            'type' => 'News',
            'title' => $row['title'],
            'summary' => $row['summary'],
            'url' => 'news.php'
        ];
    }

    // Search static site destinations.
    $staticPages = [
        ['FAQ', 'Frequently Asked Questions', 'faq questions answers help support booking payment account parks events', 'faq.php'],
        ['Map', 'Park Map', 'map locations regions directions nearby parks', 'map.php'],
        ['Donate', 'Donate', 'donate donations support conservation payment gift', 'donate.php'],
        ['Contact', 'Contact Information', 'contact email phone Albany info@nysparks.gov 555 123 4567', 'about.php'],
        ['About', 'About NYS Parks', 'about mission parks recreation outdoor access statewide', 'about.php'],
        ['Booking', 'Client Event Booking', 'client booking create event private reservation request field whole park', 'client-create-event.php'],
    ];

    $needle = strtolower($q);

    foreach ($staticPages as [$type, $title, $text, $url]) {
        if ($q === '' || str_contains(strtolower($title . ' ' . $text), $needle)) {
            $results[] = [
                'type' => $type,
                'title' => $title,
                'summary' => $text,
                'url' => $url
            ];
        }
    }
}

// Load region dropdown values.
$regions = $db
    ->query("SELECT DISTINCT region FROM parks WHERE region IS NOT NULL AND region <> '' ORDER BY region")
    ->fetchAll(PDO::FETCH_COLUMN);
?>

<?php
$pageTitle = 'NYS Parks - Search';
$bodyPage = 'search';
$extraHead = '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

    <main class="py-5">
        <section class="container">

            <!-- Search page heading -->
            <header class="mb-4">
                <p class="section-kicker mb-2">Site search</p>
                <h1 class="display-6 fw-bold mb-3">Search NYS Parks & Recreation Website</h1>
                <p class="text-muted mb-0">
                    Search parks, events, news, FAQ/static pages, map, donate, contact/about, and the client booking page.
                </p>
            </header>

            <!-- Search form -->
            <form class="soft-card p-3 p-lg-4 mb-4" method="get" action="search.php">
                <div class="row g-3 align-items-end">

                    <!-- Search keyword field -->
                    <div class="col-lg-7">
                        <label class="form-label">Search</label>
                        <input
                                type="text"
                                name="q"
                                value="<?= e($q) ?>"
                                class="form-control form-control-lg"
                                placeholder="Search the entire site..."
                        />
                    </div>

                    <!-- Region field -->
                    <div class="col-lg-3">
                        <label class="form-label">Region</label>

                        <select name="region" class="form-select form-select-lg">
                            <option value="">Any Region</option>

                            <?php foreach ($regions as $regionName): ?>
                                <option value="<?= e($regionName) ?>" <?= $region === $regionName ? 'selected' : '' ?>>
                                    <?= e($regionName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Submit button -->
                    <div class="col-lg-2">
                        <button class="btn btn-success rounded-pill w-100 btn-lg" type="submit">Search</button>
                    </div>
                </div>
            </form>

            <!-- Search results -->
            <section class="list-shell">

                <!-- Results count -->
                <section class="p-4 border-bottom">
                    <h2 class="h5 fw-bold mb-0">
                        <?= count($results) ?> result<?= count($results) === 1 ? '' : 's' ?>
                    </h2>
                </section>

                <!-- Result rows -->
                <?php foreach ($results as $i => $result): ?>
                    <section class="<?= $i < count($results) - 1 ? 'list-row' : '' ?> p-4">
                        <span class="badge text-bg-light border mb-2"><?= e($result['type']) ?></span>

                        <h3 class="h5 fw-bold mb-2">
                            <a href="<?= e($result['url']) ?>" class="text-decoration-none text-dark">
                                <?= e($result['title']) ?>
                            </a>
                        </h3>

                        <p class="text-muted mb-3"><?= e($result['summary']) ?></p>

                        <a href="<?= e($result['url']) ?>" class="btn btn-sm btn-outline-success rounded-pill">Open</a>
                    </section>
                <?php endforeach; ?>

                <!-- Empty start message -->
                <?php if (!$results && !$hasSearchRequest): ?>
                    <section class="p-4 text-muted">
                        Enter a search term or choose a region to search the site.
                    </section>
                <?php elseif (!$results): ?>
                    <section class="p-4 text-muted">
                        No results found.
                    </section>
                <?php endif; ?>
            </section>
        </section>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>