<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Admin Dashboard | ' . SITE_NAME;
$activePage = '';
$adminSection = 'dashboard';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin(['admin']);

$user = currentUser();
$flash = getFlashMessage();
$bookings = getAdminBookingRequests();
$ptoRequests = getAdminPtoRequests();
$metrics = getAdminDashboardMetrics($bookings, $ptoRequests);
$notifications = getAdminNotifications($bookings, $ptoRequests);
$buckets = getAdminBookingBuckets($bookings);
$analytics = getFallbackAdminAnalytics();
$defaultRange = '30';
$defaultAnalytics = $analytics[$defaultRange];
$siteTrafficTotal = array_sum($defaultAnalytics['siteTraffic']);
$parkTrafficTotal = array_sum(array_column($defaultAnalytics['parkTraffic'], 'count'));

$extraScripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="admin-portal-page py-5">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo e($flash['type']); ?> mb-4"><?php echo e($flash['message']); ?></div>
        <?php endif; ?>

        <section class="admin-dashboard-hero rounded-4 p-4 p-lg-5 mb-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <span class="badge rounded-pill bg-light text-success px-3 py-2 mb-3">Admin Portal</span>
                    <h1 class="display-6 fw-bold mb-3">Operations dashboard</h1>
                    <p class="lead text-white-75 mb-4">
                        This screen is the best place for high-level admin oversight. It keeps the key metrics, alerts,
                        pending approvals, and upcoming reservations on one screen, while deeper work happens on the
                        dedicated Booking Requests, Employee Schedule, and PTO pages.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="admin-bookings.php" class="btn btn-light text-success fw-semibold">
                            <i class="bi bi-calendar2-week me-2"></i>Review Booking Requests
                        </a>
                        <a href="admin-schedule.php" class="btn btn-outline-light">
                            <i class="bi bi-calendar3 me-2"></i>Manage Schedule
                        </a>
                        <a href="admin-pto.php" class="btn btn-outline-light">
                            <i class="bi bi-person-workspace me-2"></i>Review PTO
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="admin-hero-panel p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <div class="small text-uppercase fw-bold text-white-50 mb-1">Signed in as</div>
                                <div class="fw-bold text-white"><?php echo e(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?></div>
                                <div class="text-white-50"><?php echo e($user['email'] ?? ''); ?></div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-light position-relative admin-notification-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-bell-fill"></i>
                                    <?php if (!empty($notifications)): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            <?php echo e((string) count($notifications)); ?>
                                        </span>
                                    <?php endif; ?>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end p-0 admin-notification-menu">
                                    <div class="px-3 py-2 border-bottom">
                                        <strong>Notifications</strong>
                                    </div>
                                    <?php if (empty($notifications)): ?>
                                        <div class="px-3 py-3 text-muted small">No new notifications right now.</div>
                                    <?php else: ?>
                                        <?php foreach ($notifications as $note): ?>
                                            <a href="<?php echo e($note['link']); ?>" class="dropdown-item px-3 py-3 small">
                                                <div class="d-flex gap-2">
                                                    <i class="bi <?php echo e($note['icon']); ?> text-success mt-1"></i>
                                                    <div>
                                                        <div class="fw-semibold"><?php echo e($note['title']); ?></div>
                                                        <div class="text-muted"><?php echo e($note['message']); ?></div>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="small text-uppercase fw-bold text-white-50 mb-2">Priority items</div>
                        <ul class="list-unstyled mb-0 text-white-75 admin-priority-list">
                            <li><?php echo e((string) $metrics['pending_bookings']); ?> booking requests still need approval.</li>
                            <li><?php echo e((string) $metrics['pending_pto']); ?> PTO request<?php echo $metrics['pending_pto'] === 1 ? '' : 's'; ?> still need review.</li>
                            <li><?php echo e((string) $metrics['upcoming_booked']); ?> approved / confirmed events are coming up soon.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <?php require __DIR__ . '/includes/admin-nav.php'; ?>

        <section class="mb-4">
            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <div class="dashboard-stat-card h-100">
                        <div class="dashboard-stat-icon bg-success-subtle text-success"><i class="bi bi-check2-circle"></i></div>
                        <div class="dashboard-stat-value"><?php echo e((string) $metrics['events_booked']); ?></div>
                        <div class="dashboard-stat-label">Booked events</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="dashboard-stat-card h-100">
                        <div class="dashboard-stat-icon bg-primary-subtle text-primary"><i class="bi bi-globe-americas"></i></div>
                        <div class="dashboard-stat-value" id="siteTrafficMetric"><?php echo e(number_format($siteTrafficTotal)); ?></div>
                        <div class="dashboard-stat-label">Site traffic (selected range)</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="dashboard-stat-card h-100">
                        <div class="dashboard-stat-icon bg-info-subtle text-info"><i class="bi bi-people"></i></div>
                        <div class="dashboard-stat-value" id="parkTrafficMetric"><?php echo e(number_format($parkTrafficTotal)); ?></div>
                        <div class="dashboard-stat-label">Park traffic (selected range)</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="dashboard-stat-card h-100">
                        <div class="dashboard-stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
                        <div class="dashboard-stat-value"><?php echo e((string) $metrics['pending_bookings']); ?></div>
                        <div class="dashboard-stat-label">Pending approvals</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="card border-0 shadow-sm admin-analytics-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
                    <div>
                        <span class="section-kicker">Analytics</span>
                        <h2 class="h4 fw-bold mb-1">Traffic and booking trends</h2>
                        <p class="text-muted mb-0">Use the range filter to switch the chart view. Traffic metrics are demo-ready analytics until a live analytics table is added later.</p>
                    </div>
                    <div>
                        <label for="adminAnalyticsRange" class="form-label fw-semibold mb-1">Range</label>
                        <select id="adminAnalyticsRange" class="form-select">
                            <option value="7">Last 7 days</option>
                            <option value="30" selected>Last 30 days</option>
                            <option value="90">Last 90 days</option>
                        </select>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="chart-shell h-100">
                            <h3 class="h6 fw-bold mb-3">Site traffic vs event bookings</h3>
                            <canvas id="adminSiteTrafficChart" height="120"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="chart-shell h-100">
                            <h3 class="h6 fw-bold mb-3">Park traffic by location</h3>
                            <canvas id="adminParkTrafficChart" height="120"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-4">
            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm h-100 admin-list-card">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h5 fw-bold mb-0">Upcoming booked events</h2>
                                <span class="badge text-bg-success"><?php echo e((string) count($buckets['upcoming'])); ?></span>
                            </div>
                            <?php if (empty($buckets['upcoming'])): ?>
                                <p class="text-muted mb-0">No upcoming approved or confirmed events.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush admin-dashboard-list">
                                    <?php foreach (array_slice($buckets['upcoming'], 0, 5) as $booking): ?>
                                        <div class="list-group-item px-0">
                                            <div class="fw-semibold"><?php echo e($booking['title'] ?? 'Reservation Request'); ?></div>
                                            <div class="small text-muted"><?php echo e($booking['park_name'] ?? 'NYS Park'); ?> • <?php echo e(date('M d, Y g:i A', strtotime($booking['start_datetime']))); ?></div>
                                            <div class="small text-muted">Client: <?php echo e($booking['client_name'] ?? 'Client User'); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm h-100 admin-list-card">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h5 fw-bold mb-0">Pending event requests</h2>
                                <span class="badge text-bg-warning"><?php echo e((string) count($buckets['pending'])); ?></span>
                            </div>
                            <?php if (empty($buckets['pending'])): ?>
                                <p class="text-muted mb-0">No pending requests right now.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush admin-dashboard-list">
                                    <?php foreach (array_slice($buckets['pending'], 0, 5) as $booking): ?>
                                        <div class="list-group-item px-0">
                                            <div class="fw-semibold"><?php echo e($booking['title'] ?? 'Reservation Request'); ?></div>
                                            <div class="small text-muted"><?php echo e($booking['park_name'] ?? 'NYS Park'); ?> • <?php echo e($booking['guest_count'] ?? '0'); ?> guests</div>
                                            <div class="small text-muted">Submitted <?php echo e(date('M d, Y g:i A', strtotime($booking['created_at'] ?? 'now'))); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm h-100 admin-list-card">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h5 fw-bold mb-0">Denied event requests</h2>
                                <span class="badge text-bg-danger"><?php echo e((string) count($buckets['denied'])); ?></span>
                            </div>
                            <?php if (empty($buckets['denied'])): ?>
                                <p class="text-muted mb-0">No denied requests to show.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush admin-dashboard-list">
                                    <?php foreach (array_slice($buckets['denied'], 0, 5) as $booking): ?>
                                        <div class="list-group-item px-0">
                                            <div class="fw-semibold"><?php echo e($booking['title'] ?? 'Reservation Request'); ?></div>
                                            <div class="small text-muted"><?php echo e($booking['park_name'] ?? 'NYS Park'); ?></div>
                                            <div class="small text-muted"><?php echo e($booking['special_requests'] ?? 'Review notes available on booking page.'); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <span class="section-kicker">Employee management</span>
                        <h2 class="h5 fw-bold mb-3">Schedule tools</h2>
                        <p class="text-muted mb-3">Create and review employee shifts on the dedicated schedule screen.</p>
                        <a href="admin-schedule.php" class="btn btn-success">Open Schedule Screen</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <span class="section-kicker">Employee management</span>
                        <h2 class="h5 fw-bold mb-3">PTO review queue</h2>
                        <p class="text-muted mb-3">Approve or deny time-off requests without cluttering the main dashboard.</p>
                        <a href="admin-pto.php" class="btn btn-success">Open PTO Screen</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <span class="section-kicker">Booking operations</span>
                        <h2 class="h5 fw-bold mb-3">Request management</h2>
                        <p class="text-muted mb-3">Approve, deny, and track reservation workflows on a full management screen.</p>
                        <a href="admin-bookings.php" class="btn btn-success">Open Booking Screen</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>
<script>
window.adminDashboardAnalytics = <?php echo json_encode($analytics, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
window.adminDashboardDefaultRange = <?php echo json_encode($defaultRange); ?>;
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
