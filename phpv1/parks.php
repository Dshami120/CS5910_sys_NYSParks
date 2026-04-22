<?php
$pageTitle = 'Discover Parks | ' . SITE_NAME;
$activePage = 'parks';

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

/*
 |--------------------------------------------------------------------------
 | Pull park directory data
 |--------------------------------------------------------------------------
 | We try the real Parks table first.
 | If the database is not ready yet, fallback content is used automatically.
 */
$parks = getAllParks();
$regions = getParkRegions($parks);
$dbConnected = getDbConnection() instanceof mysqli;
?>

<!-- PAGE HERO -->
<section class="subpage-hero subpage-hero-parks text-white d-flex align-items-center">
    <div class="subpage-hero-overlay"></div>
    <div class="container position-relative py-5">
        <div class="row">
            <div class="col-lg-8">
                <span class="hero-kicker">PARK DIRECTORY</span>
                <h1 class="display-5 fw-bold mb-3">Discover New York State Parks</h1>
                <p class="lead text-white-50 mb-0">
                    Browse parks across New York by region, search for amenities, and preview locations
                    before we connect each park to its own dynamic details page.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- INTRO / STATUS -->
<section class="py-4 border-bottom bg-light">
    <div class="container d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h2 class="h4 fw-bold mb-1">Public parks browsing page</h2>
            <p class="text-muted mb-0">This is the main statewide park listing page for guests and future client users.</p>
        </div>
        <div class="small">
            <?php if ($dbConnected): ?>
                <span class="text-success"><i class="bi bi-check-circle-fill"></i> Connected to MySQL. Park cards are ready to read from the <strong>Parks</strong> table.</span>
            <?php else: ?>
                <span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Using fallback demo parks until the database is fully imported in XAMPP.</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- FILTERS -->
<section class="py-4">
    <div class="container">
        <div class="card border-0 shadow-sm parks-filter-card">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-5">
                        <label for="parkSearch" class="form-label fw-semibold">Search parks</label>
                        <input type="text" id="parkSearch" class="form-control form-control-lg" placeholder="Search by park name, city, region, or amenity...">
                    </div>
                    <div class="col-lg-3">
                        <label for="regionFilter" class="form-label fw-semibold">Filter by region</label>
                        <select id="regionFilter" class="form-select form-select-lg">
                            <option value="all">All Regions</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?php echo e($region); ?>"><?php echo e($region); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label for="typeFilter" class="form-label fw-semibold">Type</label>
                        <select id="typeFilter" class="form-select form-select-lg">
                            <option value="all">All Types</option>
                            <option value="Nature">Nature</option>
                            <option value="Beach">Beach</option>
                            <option value="Mountain">Mountain</option>
                            <option value="Family">Family</option>
                            <option value="Landmark">Landmark</option>
                            <option value="Waterfront">Waterfront</option>
                        </select>
                    </div>
                    <div class="col-lg-2 d-grid">
                        <button type="button" id="resetParksFilters" class="btn btn-outline-success btn-lg">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- QUICK STATS -->
<section class="pb-4">
    <div class="container">
        <div class="row g-3 text-center">
            <div class="col-md-4">
                <div class="mini-stat-card h-100">
                    <div class="mini-stat-number" id="parkCount"><?php echo count($parks); ?></div>
                    <div class="mini-stat-label">Parks in current view</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mini-stat-card h-100">
                    <div class="mini-stat-number"><?php echo count($regions); ?></div>
                    <div class="mini-stat-label">Regions represented</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mini-stat-card h-100">
                    <div class="mini-stat-number"><i class="bi bi-tree-fill"></i></div>
                    <div class="mini-stat-label">Nature, beaches, mountains, and landmarks</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PARKS GRID -->
<section class="pb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <p class="section-label mb-1">STATEWIDE LISTING</p>
                <h2 class="fw-bold mb-0">Browse parks</h2>
            </div>
            <p class="text-muted mb-0" id="parksResultsText">Showing <?php echo count($parks); ?> parks</p>
        </div>

        <div class="row g-4" id="parksGrid">
            <?php foreach ($parks as $park): ?>
                <?php
                $searchBlob = strtolower(
                    ($park['park_name'] ?? '') . ' ' .
                    ($park['region'] ?? '') . ' ' .
                    ($park['city'] ?? '') . ' ' .
                    ($park['amenities'] ?? '') . ' ' .
                    ($park['park_type'] ?? '')
                );
                ?>
                <div class="col-lg-4 col-md-6 park-item"
                     data-region="<?php echo e($park['region']); ?>"
                     data-type="<?php echo e($park['park_type']); ?>"
                     data-search="<?php echo e($searchBlob); ?>">
                    <div class="card border-0 shadow-sm h-100 park-directory-card overflow-hidden">
                        <div class="park-directory-image" style="background-image: url('<?php echo e($park['image_url']); ?>');">
                            <span class="badge rounded-pill bg-white text-success fw-semibold m-3"><?php echo e($park['park_type']); ?></span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <p class="small text-success fw-semibold mb-2"><?php echo e($park['region']); ?></p>
                            <h3 class="h4 fw-bold mb-2"><?php echo e($park['park_name']); ?></h3>
                            <p class="text-muted mb-2"><i class="bi bi-geo-alt"></i> <?php echo e($park['city'] . ', ' . $park['state']); ?></p>
                            <p class="text-muted mb-2"><i class="bi bi-clock"></i> <?php echo e($park['hours']); ?></p>
                            <p class="text-muted mb-3"><?php echo e($park['summary']); ?></p>

                            <div class="mb-3">
                                <?php
                                $amenities = array_filter(array_map('trim', explode(',', $park['amenities'] ?? '')));
                                foreach (array_slice($amenities, 0, 3) as $amenity):
                                ?>
                                    <span class="badge text-bg-light border me-1 mb-1"><?php echo e($amenity); ?></span>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-auto d-flex gap-2">
                                <a href="#" class="btn btn-success flex-grow-1">View Park</a>
                                <button type="button"
                                        class="btn btn-outline-success park-more-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#parkPreviewModal"
                                        data-park-name="<?php echo e($park['park_name']); ?>"
                                        data-park-region="<?php echo e($park['region']); ?>"
                                        data-park-address="<?php echo e($park['address'] . ', ' . $park['city'] . ', ' . $park['state'] . ' ' . $park['zip_code']); ?>"
                                        data-park-hours="<?php echo e($park['hours']); ?>"
                                        data-park-amenities="<?php echo e($park['amenities']); ?>"
                                        data-park-summary="<?php echo e($park['summary']); ?>">
                                    Quick View
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="noParksMessage" class="text-center py-5 d-none">
            <i class="bi bi-search fs-1 text-muted"></i>
            <h3 class="h4 mt-3">No parks matched your filters</h3>
            <p class="text-muted">Try clearing the search or choosing a different region.</p>
        </div>
    </div>
</section>

<!-- SIMPLE MAP / FUTURE FEATURE TEASER -->
<section class="pb-5">
    <div class="container">
        <div class="card border-0 shadow-lg overflow-hidden map-teaser-card">
            <div class="row g-0 align-items-stretch">
                <div class="col-lg-6 p-4 p-lg-5">
                    <p class="section-label mb-2">COMING NEXT</p>
                    <h2 class="fw-bold mb-3">Interactive map integration</h2>
                    <p class="text-muted mb-3">
                        Your partner's example used a hardcoded Google Maps page. In the cleaned-up build,
                        we'll connect that feature as a separate public page with safer config handling,
                        reusable data, and cleaner UI.
                    </p>
                    <ul class="text-muted ps-3 mb-4">
                        <li>Map markers for parks by region</li>
                        <li>Park popups with key information</li>
                        <li>Links from the map to each park details page</li>
                    </ul>
                    <a href="#" class="btn btn-success">Build map page next</a>
                </div>
                <div class="col-lg-6 map-teaser-visual d-flex align-items-center justify-content-center">
                    <div class="map-teaser-box text-center">
                        <i class="bi bi-map fs-1 text-success"></i>
                        <h3 class="h4 fw-bold mt-3">Statewide map preview</h3>
                        <p class="text-muted mb-0">We will turn this into a dynamic NYS parks map page in the next step.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- QUICK VIEW MODAL -->
<div class="modal fade" id="parkPreviewModal" tabindex="-1" aria-labelledby="parkPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="section-label mb-1" id="modalParkRegion">Region</p>
                    <h2 class="modal-title h3 fw-bold" id="parkPreviewModalLabel">Park Name</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted" id="modalParkSummary"></p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="quick-view-stat">
                            <strong>Address</strong>
                            <p class="mb-0 text-muted" id="modalParkAddress"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="quick-view-stat">
                            <strong>Hours</strong>
                            <p class="mb-0 text-muted" id="modalParkHours"></p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="quick-view-stat">
                            <strong>Amenities</strong>
                            <p class="mb-0 text-muted" id="modalParkAmenities"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-success">Go to full park page</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
