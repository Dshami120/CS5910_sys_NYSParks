<?php
// Set page metadata.
$pageTitle = 'NYS Parks - Explore Parks';
$bodyPage = 'parks';
$extraHead = '';
?>

<?php
// Load project setup.
require 'bootstrap.php';

// Read park filters.
$search = get('q');
$region = get('region');
$type = get('type');

// Build parks query.
$sql = "SELECT * FROM parks WHERE 1=1";
$params = [];

// Apply keyword filter.
if ($search !== '') {
    $sql .= " AND (name LIKE ? OR description LIKE ? OR amenities LIKE ?)";
    $like = "%{$search}%";
    array_push($params, $like, $like, $like);
}

// Apply region filter.
if ($region !== '') {
    $sql .= " AND region = ?";
    $params[] = $region;
}

// Apply experience type filter.
if ($type !== '') {
    $sql .= " AND park_type = ?";
    $params[] = $type;
}

// Load filtered parks.
$sql .= " ORDER BY is_featured DESC, name";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$parks = $stmt->fetchAll();

// Load filter dropdown values.
$regions = $db->query("SELECT DISTINCT region FROM parks ORDER BY region")->fetchAll(PDO::FETCH_COLUMN);
$types = $db->query("SELECT DISTINCT park_type FROM parks ORDER BY park_type")->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include __DIR__ . '/includes/header.php'; ?>

    <main>

        <!-- Blue title section -->
        <section class="subpage-hero subpage-hero-news text-white d-flex align-items-center">
            <div class="subpage-hero-overlay"></div>

            <div class="container position-relative py-5">
                <div class="row">
                    <div class="col-lg-8">
                        <span class="hero-kicker">Discover parks</span>
                        <h1 class="display-5 fw-bold mb-3">Explore parks across New York State</h1>
                        <p class="lead text-white-50 mb-0">Browse featured destinations, compare regions, and discover the types of outdoor experiences available statewide.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Park filters -->
        <section class="py-4">
            <section class="container">
                <form class="soft-card p-3 p-lg-4" method="get" action="parks.php">
                    <section class="row g-3 align-items-end">

                        <!-- Keyword filter -->
                        <article class="col-lg-3">
                            <label class="form-label">Search by keyword</label>
                            <input type="text" name="q" value="<?= e($search) ?>" class="form-control" placeholder="Waterfalls, beaches, trails..." />
                        </article>

                        <!-- Region filter -->
                        <article class="col-lg-3">
                            <label class="form-label">Region</label>
                            <select name="region" class="form-select">
                                <option value="">All regions</option>

                                <?php foreach ($regions as $item): ?>
                                    <option value="<?= e($item) ?>" <?= $region === $item ? 'selected' : '' ?>>
                                        <?= e($item) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </article>

                        <!-- Experience type filter -->
                        <article class="col-lg-3">
                            <label class="form-label">Experience type</label>
                            <select name="type" class="form-select">
                                <option value="">All experiences</option>

                                <?php foreach ($types as $item): ?>
                                    <option value="<?= e($item) ?>" <?= $type === $item ? 'selected' : '' ?>>
                                        <?= e($item) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </article>

                        <!-- Filter buttons -->
                        <article class="col-lg-3 d-flex gap-2 justify-content-lg-end">
                            <button type="submit" class="btn btn-success rounded-pill px-4">Apply Filters</button>
                            <a href="parks.php" class="btn btn-outline-dark rounded-pill px-4">Reset</a>
                        </article>
                    </section>
                </form>
            </section>
        </section>

        <!-- Featured parks -->
        <section class="py-5 pt-2">
            <section class="container">

                <!-- Results heading -->
                <header class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <section>
                        <h2 class="fw-bold mb-1">Featured Parks</h2>
                        <p class="text-muted mb-0">Browse parks that match your selected filters.</p>
                    </section>

                    <a href="map.php" class="map-link">Open map view →</a>
                </header>

                <!-- Parks grid -->
                <section class="row g-4" id="parks-grid">
                    <?php foreach ($parks as $park): ?>
                        <article class="col-md-6 col-xl-4">

                            <!-- Park card -->
                            <figure class="image-card h-100 mb-0">
                                <img src="<?= e($park['image_url']) ?>" alt="<?= e($park['image_alt'] ?: $park['name']) ?>" class="image-cover-md" />

                                <figcaption class="p-4">
                                    <p class="section-kicker mb-2"><?= e($park['region']) ?> · <?= e($park['park_type']) ?></p>
                                    <h3 class="h5 fw-bold mb-2"><?= e($park['name']) ?></h3>
                                    <p class="text-muted mb-2"><?= e($park['card_summary'] ?: $park['description']) ?></p>
                                    <p class="small text-muted mb-2"><i class="bi bi-grid-3x3-gap me-1"></i><?= (int)$park['total_fields'] ?> fields · max <?= (int)$park['max_capacity'] ?> guests</p>
                                    <p class="small text-muted mb-3"><i class="bi bi-geo-alt me-1"></i><?= e($park['city']) ?>, <?= e($park['state']) ?></p>

                                    <button type="button" class="btn btn-success w-100 rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#parkModal<?= (int)$park['id'] ?>">
                                        View Park
                                    </button>
                                </figcaption>
                            </figure>

                            <!-- Park detail modal -->
                            <section class="modal fade" id="parkModal<?= (int)$park['id'] ?>" tabindex="-1" aria-labelledby="parkModalLabel<?= (int)$park['id'] ?>" aria-hidden="true">
                                <section class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                    <section class="modal-content border-0 rounded-4 overflow-hidden">
                                        <img src="<?= e($park['image_url']) ?>" alt="<?= e($park['image_alt'] ?: $park['name']) ?>" class="image-cover-md" />

                                        <!-- Modal header -->
                                        <section class="modal-header border-0 pb-0">
                                            <section>
                                                <p class="section-kicker mb-2"><?= e($park['region']) ?> · <?= e($park['park_type']) ?></p>
                                                <h3 class="modal-title fw-bold" id="parkModalLabel<?= (int)$park['id'] ?>"><?= e($park['name']) ?></h3>
                                            </section>

                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </section>

                                        <!-- Modal body -->
                                        <section class="modal-body pt-3">
                                            <p class="text-muted"><?= e($park['description']) ?></p>

                                            <section class="row g-3 mb-3">

                                                <!-- Location details -->
                                                <article class="col-md-6">
                                                    <section class="soft-card p-3 h-100">
                                                        <h4 class="h6 fw-bold mb-2"><i class="bi bi-geo-alt me-1"></i>Location</h4>
                                                        <p class="mb-0 text-muted"><?= e($park['address_line']) ?><br /><?= e($park['city']) ?>, <?= e($park['state']) ?> <?= e($park['zip_code']) ?></p>
                                                    </section>
                                                </article>

                                                <!-- Hours details -->
                                                <article class="col-md-6">
                                                    <section class="soft-card p-3 h-100">
                                                        <h4 class="h6 fw-bold mb-2"><i class="bi bi-clock me-1"></i>Hours</h4>
                                                        <p class="mb-0 text-muted"><?= e($park['hours']) ?></p>
                                                    </section>
                                                </article>

                                                <!-- Capacity details -->
                                                <article class="col-md-6">
                                                    <section class="soft-card p-3 h-100">
                                                        <h4 class="h6 fw-bold mb-2"><i class="bi bi-people me-1"></i>Capacity</h4>
                                                        <p class="mb-0 text-muted"><?= (int)$park['total_fields'] ?> fields · max <?= (int)$park['max_capacity'] ?> guests</p>
                                                    </section>
                                                </article>

                                                <!-- Amenities details -->
                                                <article class="col-md-6">
                                                    <section class="soft-card p-3 h-100">
                                                        <h4 class="h6 fw-bold mb-2"><i class="bi bi-grid-3x3-gap me-1"></i>Amenities</h4>
                                                        <p class="mb-0 text-muted"><?= e($park['amenities'] ?: 'Amenities vary by season. Check official park guidance before visiting.') ?></p>
                                                    </section>
                                                </article>
                                            </section>
                                        </section>

                                        <!-- Modal footer -->
                                        <section class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                                            <a href="map.php" class="btn btn-success rounded-pill fw-semibold">Open Map</a>
                                        </section>
                                    </section>
                                </section>
                            </section>
                        </article>
                    <?php endforeach; ?>

                    <!-- Empty parks message -->
                    <?php if (!$parks): ?>
                        <p class="text-muted">No parks matched your filters.</p>
                    <?php endif; ?>
                </section>
            </section>
        </section>
    </main>

    <!-- Bootstrap JavaScript bundle -->
    <!-- Shared site JavaScript -->
<?php include __DIR__ . '/includes/footer.php'; ?>