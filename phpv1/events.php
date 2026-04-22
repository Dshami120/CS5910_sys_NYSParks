<?php
$pageTitle = 'Events | ' . SITE_NAME;
$activePage = 'events';

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

/*
 |--------------------------------------------------------------------------
 | Pull public events data
 |--------------------------------------------------------------------------
 | The helper reads from the Events table first.
 | If the database is missing or empty, fallback demo events are shown.
 */
$events = getAllPublicEvents();
$eventParks = getEventParkNames($events);
$eventCategories = getEventCategories($events);
$eventMonths = getEventMonths($events);
$dbConnected = getDbConnection() instanceof mysqli;
?>

<!-- PAGE HERO -->
<section class="subpage-hero subpage-hero-events text-white d-flex align-items-center">
    <div class="subpage-hero-overlay"></div>
    <div class="container position-relative py-5">
        <div class="row">
            <div class="col-lg-8">
                <span class="hero-kicker">EVENTS CALENDAR</span>
                <h1 class="display-5 fw-bold mb-3">Find Upcoming Events Across New York State Parks</h1>
                <p class="lead text-white-50 mb-0">
                    Browse public events, filter by park or month, and preview event details before
                    we build the booking and RSVP pages.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- INTRO / STATUS -->
<section class="py-4 border-bottom bg-light">
    <div class="container d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h2 class="h4 fw-bold mb-1">Public statewide events page</h2>
            <p class="text-muted mb-0">This is the main public events listing for guests and future client users.</p>
        </div>
        <div class="small">
            <?php if ($dbConnected): ?>
                <span class="text-success"><i class="bi bi-check-circle-fill"></i> Connected to MySQL. Event cards are ready to read from the <strong>Events</strong> and <strong>Parks</strong> tables.</span>
            <?php else: ?>
                <span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Using fallback demo events until the database is fully imported in XAMPP.</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- FILTERS -->
<section class="py-4">
    <div class="container">
        <div class="card border-0 shadow-sm events-filter-card">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label for="eventSearch" class="form-label fw-semibold">Search events</label>
                        <input type="text" id="eventSearch" class="form-control form-control-lg" placeholder="Search by title, park, category, or city...">
                    </div>
                    <div class="col-lg-3">
                        <label for="eventParkFilter" class="form-label fw-semibold">Park</label>
                        <select id="eventParkFilter" class="form-select form-select-lg">
                            <option value="all">All Parks</option>
                            <?php foreach ($eventParks as $parkName): ?>
                                <option value="<?php echo e($parkName); ?>"><?php echo e($parkName); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label for="eventMonthFilter" class="form-label fw-semibold">Month</label>
                        <select id="eventMonthFilter" class="form-select form-select-lg">
                            <option value="all">All Months</option>
                            <?php foreach ($eventMonths as $monthValue => $monthLabel): ?>
                                <option value="<?php echo e($monthValue); ?>"><?php echo e($monthLabel); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label for="eventCategoryFilter" class="form-label fw-semibold">Category</label>
                        <select id="eventCategoryFilter" class="form-select form-select-lg">
                            <option value="all">All Categories</option>
                            <?php foreach ($eventCategories as $category): ?>
                                <option value="<?php echo e($category); ?>"><?php echo e($category); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-1 d-grid">
                        <button type="button" id="resetEventFilters" class="btn btn-outline-success btn-lg">Reset</button>
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
                    <div class="mini-stat-number" id="eventCount"><?php echo count($events); ?></div>
                    <div class="mini-stat-label">Events in current view</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mini-stat-card h-100">
                    <div class="mini-stat-number"><?php echo count($eventParks); ?></div>
                    <div class="mini-stat-label">Parks hosting events</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mini-stat-card h-100">
                    <div class="mini-stat-number"><?php echo count($eventCategories); ?></div>
                    <div class="mini-stat-label">Categories available</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- EVENTS GRID -->
<section class="pb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <p class="section-label mb-1">PUBLIC EVENTS</p>
                <h2 class="fw-bold mb-0">Browse upcoming events</h2>
            </div>
            <p class="text-muted mb-0" id="eventsResultsText">Showing <?php echo count($events); ?> events</p>
        </div>

        <div class="row g-4" id="eventsGrid">
            <?php foreach ($events as $event): ?>
                <?php
                $searchBlob = strtolower(
                    ($event['title'] ?? '') . ' ' .
                    ($event['park_name'] ?? '') . ' ' .
                    ($event['region'] ?? '') . ' ' .
                    ($event['city'] ?? '') . ' ' .
                    ($event['category'] ?? '') . ' ' .
                    ($event['description'] ?? '')
                );
                $monthValue = date('Y-m', strtotime($event['start_datetime']));
                $dayNumber = date('d', strtotime($event['start_datetime']));
                $monthShort = date('M', strtotime($event['start_datetime']));
                $fullDate = date('l, F j, Y', strtotime($event['start_datetime']));
                $timeRange = date('g:i A', strtotime($event['start_datetime'])) . ' - ' . date('g:i A', strtotime($event['end_datetime']));
                ?>
                <div class="col-lg-4 col-md-6 event-item"
                     data-search="<?php echo e($searchBlob); ?>"
                     data-park="<?php echo e($event['park_name']); ?>"
                     data-month="<?php echo e($monthValue); ?>"
                     data-category="<?php echo e($event['category']); ?>">
                    <div class="card border-0 shadow-sm h-100 event-directory-card overflow-hidden">
                        <div class="event-directory-image" style="background-image: url('<?php echo e($event['image_url']); ?>');">
                            <div class="event-date-badge shadow-sm">
                                <span class="event-date-month"><?php echo e($monthShort); ?></span>
                                <span class="event-date-day"><?php echo e($dayNumber); ?></span>
                            </div>
                            <span class="badge rounded-pill bg-white text-success fw-semibold m-3 float-end"><?php echo e($event['category']); ?></span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <p class="small text-success fw-semibold mb-2"><?php echo e($event['park_name']); ?></p>
                            <h3 class="h4 fw-bold mb-2"><?php echo e($event['title']); ?></h3>
                            <p class="text-muted mb-2"><i class="bi bi-calendar-event"></i> <?php echo e($fullDate); ?></p>
                            <p class="text-muted mb-2"><i class="bi bi-clock"></i> <?php echo e($timeRange); ?></p>
                            <p class="text-muted mb-2"><i class="bi bi-geo-alt"></i> <?php echo e($event['city'] . ', ' . $event['region']); ?></p>
                            <p class="text-muted mb-3"><?php echo e(mb_strimwidth($event['description'], 0, 125, '...')); ?></p>

                            <div class="mb-3">
                                <span class="badge text-bg-light border me-1 mb-1"><?php echo e($event['category']); ?></span>
                                <span class="badge text-bg-light border me-1 mb-1"><?php echo e(ucfirst($event['event_type'])); ?></span>
                                <span class="badge text-bg-light border me-1 mb-1"><?php echo e(ucfirst($event['event_status'])); ?></span>
                            </div>

                            <div class="mt-auto d-flex gap-2">
                                <a href="#" class="btn btn-success flex-grow-1">View Details</a>
                                <button type="button"
                                        class="btn btn-outline-success"
                                        data-bs-toggle="modal"
                                        data-bs-target="#eventPreviewModal"
                                        data-event-title="<?php echo e($event['title']); ?>"
                                        data-event-park="<?php echo e($event['park_name']); ?>"
                                        data-event-category="<?php echo e($event['category']); ?>"
                                        data-event-date="<?php echo e($fullDate); ?>"
                                        data-event-time="<?php echo e($timeRange); ?>"
                                        data-event-location="<?php echo e($event['city'] . ', ' . $event['region']); ?>"
                                        data-event-type="<?php echo e(ucfirst($event['event_type'])); ?>"
                                        data-event-status="<?php echo e(ucfirst($event['event_status'])); ?>"
                                        data-event-description="<?php echo e($event['description']); ?>">
                                    Quick View
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="noEventsMessage" class="text-center py-5 d-none">
            <i class="bi bi-calendar-x fs-1 text-muted"></i>
            <h3 class="h4 mt-3">No events matched your filters</h3>
            <p class="text-muted">Try clearing the search or choosing a different park or month.</p>
        </div>
    </div>
</section>

<!-- NEXT STEP TEASER -->
<section class="pb-5">
    <div class="container">
        <div class="card border-0 shadow-lg overflow-hidden map-teaser-card">
            <div class="row g-0 align-items-stretch">
                <div class="col-lg-6 p-4 p-lg-5">
                    <p class="section-label mb-2">COMING NEXT</p>
                    <h2 class="fw-bold mb-3">Event details and booking flow</h2>
                    <p class="text-muted mb-3">
                        After this public events listing page, the next logical step is building an event details page
                        and then wiring it into booking requests, approvals, and payment later in the capstone.
                    </p>
                    <ul class="text-muted ps-3 mb-4">
                        <li>Single event details page</li>
                        <li>Reservation request buttons for future client users</li>
                        <li>Admin review workflow later in the project</li>
                    </ul>
                    <a href="#" class="btn btn-success">Build event details next</a>
                </div>
                <div class="col-lg-6 map-teaser-visual d-flex align-items-center justify-content-center">
                    <div class="map-teaser-box text-center">
                        <i class="bi bi-ticket-perforated fs-1 text-success"></i>
                        <h3 class="h4 fw-bold mt-3">Event workflow preview</h3>
                        <p class="text-muted mb-0">This page is now ready to become the front door for bookings and RSVPs later.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- QUICK VIEW MODAL -->
<div class="modal fade" id="eventPreviewModal" tabindex="-1" aria-labelledby="eventPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="section-label mb-1" id="modalEventCategory">Category</p>
                    <h2 class="modal-title h3 fw-bold" id="eventPreviewModalLabel">Event Title</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted" id="modalEventDescription"></p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="quick-view-stat">
                            <strong>Park</strong>
                            <p class="mb-0 text-muted" id="modalEventPark"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="quick-view-stat">
                            <strong>Date</strong>
                            <p class="mb-0 text-muted" id="modalEventDate"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="quick-view-stat">
                            <strong>Time</strong>
                            <p class="mb-0 text-muted" id="modalEventTime"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="quick-view-stat">
                            <strong>Location</strong>
                            <p class="mb-0 text-muted" id="modalEventLocation"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="quick-view-stat">
                            <strong>Type</strong>
                            <p class="mb-0 text-muted" id="modalEventType"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="quick-view-stat">
                            <strong>Status</strong>
                            <p class="mb-0 text-muted" id="modalEventStatus"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-success">Go to event page</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
