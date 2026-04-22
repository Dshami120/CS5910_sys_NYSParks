<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Statewide Park Map | ' . SITE_NAME;
$activePage = 'map';

/*
 |--------------------------------------------------------------------------
 | Page-specific assets
 |--------------------------------------------------------------------------
 | We load Leaflet only on this page so the rest of the site stays lighter.
 | Leaflet gives us a clean interactive map without needing a Google Maps key.
 */
$extraHead = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">';
$extraScripts = '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>';

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

/*
 |--------------------------------------------------------------------------
 | Pull parks for the map
 |--------------------------------------------------------------------------
 | We reuse the same park helper from parks.php.
 | If MySQL is not ready yet, the fallback park list still gives us coordinates.
 */
$parks = getAllParks();
$dbConnected = getDbConnection() instanceof mysqli;

/*
 |--------------------------------------------------------------------------
 | Keep only parks with usable coordinates
 |--------------------------------------------------------------------------
 | The public map needs latitude and longitude. If any database row is missing
 | coordinates, we skip it instead of breaking the page.
 */
$mappableParks = array_values(array_filter($parks, function ($park) {
    return isset($park['latitude'], $park['longitude'])
        && $park['latitude'] !== ''
        && $park['longitude'] !== ''
        && is_numeric($park['latitude'])
        && is_numeric($park['longitude']);
}));

$regions = getParkRegions($mappableParks);

/*
 |--------------------------------------------------------------------------
 | Prepare safe JSON for JavaScript
 |--------------------------------------------------------------------------
 | We encode the park array once in PHP and let main.js render the map.
 */
$mapParksJson = json_encode($mappableParks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>

<section class="subpage-hero subpage-hero-map text-white d-flex align-items-center">
    <div class="subpage-hero-overlay"></div>
    <div class="container position-relative py-5">
        <div class="row">
            <div class="col-lg-8">
                <span class="hero-kicker">INTERACTIVE STATEWIDE MAP</span>
                <h1 class="display-5 fw-bold mb-3">Explore parks across New York on one map</h1>
                <p class="lead text-white-50 mb-0">
                    Search by keyword, narrow by region, and click markers to preview parks before
                    we build the full park details pages.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-4 border-bottom bg-light">
    <div class="container d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h2 class="h4 fw-bold mb-1">Public statewide park map</h2>
            <p class="text-muted mb-0">A cleaner replacement for the hardcoded example map page.</p>
        </div>
        <div class="small">
            <?php if ($dbConnected): ?>
                <span class="text-success"><i class="bi bi-check-circle-fill"></i> Connected to MySQL. The map is ready to read parks from the <strong>Parks</strong> table.</span>
            <?php else: ?>
                <span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Using fallback demo parks so the map still works before the full database is imported.</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="card border-0 shadow-sm parks-filter-card">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-6">
                        <label for="mapSearch" class="form-label fw-semibold">Search parks on the map</label>
                        <input type="text" id="mapSearch" class="form-control form-control-lg" placeholder="Search by park name, city, region, or amenity...">
                    </div>
                    <div class="col-lg-3">
                        <label for="mapRegionFilter" class="form-label fw-semibold">Filter by region</label>
                        <select id="mapRegionFilter" class="form-select form-select-lg">
                            <option value="all">All Regions</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?php echo e($region); ?>"><?php echo e($region); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 d-grid">
                        <button type="button" id="resetMapFilters" class="btn btn-outline-success btn-lg">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm overflow-hidden h-100">
                    <div class="card-body p-0">
                        <div id="statewideMap"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <p class="section-label mb-1">MAP RESULTS</p>
                                <h2 class="h4 fw-bold mb-0">Visible parks</h2>
                            </div>
                            <span class="badge rounded-pill text-bg-success" id="mapCountBadge"><?php echo count($mappableParks); ?></span>
                        </div>

                        <p class="text-muted small mb-3">
                            Click a result to zoom the map to that park.
                        </p>

                        <div id="mapEmptyMessage" class="alert alert-light border d-none mb-3">
                            No parks matched your search.
                        </div>

                        <div class="map-results-list" id="mapResultsList">
                            <?php foreach ($mappableParks as $index => $park): ?>
                                <?php
                                $searchBlob = strtolower(
                                    ($park['park_name'] ?? '') . ' ' .
                                    ($park['region'] ?? '') . ' ' .
                                    ($park['city'] ?? '') . ' ' .
                                    ($park['amenities'] ?? '')
                                );
                                ?>
                                <button type="button"
                                        class="map-result-item text-start w-100"
                                        data-map-index="<?php echo $index; ?>"
                                        data-region="<?php echo e($park['region']); ?>"
                                        data-search="<?php echo e($searchBlob); ?>">
                                    <span class="map-result-title"><?php echo e($park['park_name']); ?></span>
                                    <span class="map-result-meta"><?php echo e($park['city'] . ', ' . $park['region']); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <div class="card border-0 shadow-lg overflow-hidden map-teaser-card">
            <div class="row g-0 align-items-stretch">
                <div class="col-lg-6 p-4 p-lg-5">
                    <p class="section-label mb-2">WHAT THIS PAGE DOES</p>
                    <h2 class="fw-bold mb-3">A better public map foundation</h2>
                    <p class="text-muted mb-3">
                        This version avoids hardcoding a Google Maps API key directly in the page and keeps the map data reusable.
                        Later we can connect each marker to a real <code>park-details.php</code> page.
                    </p>
                    <ul class="text-muted ps-3 mb-4">
                        <li>Search parks from the sidebar</li>
                        <li>Filter by region</li>
                        <li>Zoom to parks by clicking a result or marker</li>
                    </ul>
                    <a href="parks.php" class="btn btn-success">Back to park directory</a>
                </div>
                <div class="col-lg-6 map-teaser-visual d-flex align-items-center justify-content-center">
                    <div class="map-teaser-box text-center">
                        <i class="bi bi-map fs-1 text-success"></i>
                        <h3 class="h4 fw-bold mt-3">Ready for park detail pages</h3>
                        <p class="text-muted mb-0">The next clean public page after this could be <strong>about.php</strong> or a single <strong>park-details.php</strong> page.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    window.nysMapParksData = <?php echo $mapParksJson ?: '[]'; ?>;
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
