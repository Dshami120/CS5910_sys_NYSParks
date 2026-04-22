<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Client Dashboard | ' . SITE_NAME;
$activePage = '';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin(['client']);

$user = currentUser();
$flash = getFlashMessage();
$bookings = getClientDashboardBookings($user);
$stats = getClientBookingStats($bookings);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="client-portal-page py-5">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo e($flash['type']); ?> mb-4"><?php echo e($flash['message']); ?></div>
        <?php endif; ?>

        <section class="client-dashboard-hero rounded-4 p-4 p-lg-5 mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="badge rounded-pill bg-light text-success px-3 py-2 mb-3">Client Portal</span>
                    <h1 class="display-6 fw-bold mb-3">Welcome back, <?php echo e($user['first_name'] ?? 'Client'); ?>.</h1>
                    <p class="lead mb-4 text-white-75">
                        This dashboard shows your event booking requests, approval progress, and upcoming reservations.
                        Use the button below to submit a new event request.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="client-create-event.php" class="btn btn-light btn-lg text-success fw-semibold">
                            <i class="bi bi-calendar-plus me-2"></i>Book New Event
                        </a>
                        <a href="events.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-compass me-2"></i>Browse Public Events
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="client-hero-card p-4">
                        <div class="small text-uppercase fw-bold text-white-50 mb-2">Signed in as</div>
                        <div class="fs-5 fw-bold text-white"><?php echo e(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></div>
                        <div class="text-white-50 mb-4"><?php echo e($user['email'] ?? ''); ?></div>
                        <div class="client-next-up">
                            <div class="small text-uppercase fw-bold text-white-50 mb-2">Next upcoming request</div>
                            <?php if ($stats['next_up']): ?>
                                <div class="fw-semibold text-white"><?php echo e($stats['next_up']['title'] ?? 'Reservation Request'); ?></div>
                                <div class="text-white-50"><?php echo e($stats['next_up']['park_name'] ?? 'NYS Park'); ?></div>
                                <div class="text-white-50"><?php echo e(date('M d, Y g:i A', strtotime($stats['next_up']['start_datetime']))); ?></div>
                            <?php else: ?>
                                <div class="text-white-50">No upcoming requests yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-4">
            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <div class="dashboard-stat-card h-100">
                        <div class="dashboard-stat-icon bg-success-subtle text-success"><i class="bi bi-journal-check"></i></div>
                        <div class="dashboard-stat-value"><?php echo e((string) $stats['total']); ?></div>
                        <div class="dashboard-stat-label">Total requests</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="dashboard-stat-card h-100">
                        <div class="dashboard-stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
                        <div class="dashboard-stat-value"><?php echo e((string) $stats['pending']); ?></div>
                        <div class="dashboard-stat-label">Pending review</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="dashboard-stat-card h-100">
                        <div class="dashboard-stat-icon bg-success-subtle text-success"><i class="bi bi-patch-check"></i></div>
                        <div class="dashboard-stat-value"><?php echo e((string) $stats['approved']); ?></div>
                        <div class="dashboard-stat-label">Approved</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="dashboard-stat-card h-100">
                        <div class="dashboard-stat-icon bg-primary-subtle text-primary"><i class="bi bi-calendar2-event"></i></div>
                        <div class="dashboard-stat-value"><?php echo e((string) $stats['confirmed']); ?></div>
                        <div class="dashboard-stat-label">Confirmed</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm client-list-card">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <div>
                                <span class="section-kicker">My Event Requests</span>
                                <h2 class="h3 fw-bold mb-0">Booked and requested events</h2>
                            </div>
                            <a href="client-create-event.php" class="btn btn-success">
                                <i class="bi bi-plus-circle me-2"></i>New Event Request
                            </a>
                        </div>
                        <p class="text-muted mb-4">
                            Each card below shows one reservation request. This is where a client can quickly see status, date, location, and basic request details.
                        </p>

                        <?php if (empty($bookings)): ?>
                            <div class="alert alert-light border">
                                No event requests were found yet. Use the button above to create your first booking request.
                            </div>
                        <?php else: ?>
                            <div class="client-booking-list d-grid gap-3">
                                <?php foreach ($bookings as $booking): ?>
                                    <?php $badgeClass = getBookingStatusBadgeClass($booking['booking_status'] ?? 'pending'); ?>
                                    <article class="client-booking-card p-3 p-md-4">
                                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                                            <div>
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                    <h3 class="h5 fw-bold mb-0"><?php echo e($booking['title'] ?? 'Reservation Request'); ?></h3>
                                                    <span class="badge text-bg-<?php echo e($badgeClass); ?> text-uppercase"><?php echo e($booking['booking_status'] ?? 'pending'); ?></span>
                                                </div>
                                                <div class="text-muted"><?php echo e($booking['park_name'] ?? 'NYS Park'); ?> • <?php echo e($booking['field_name'] ?? 'Reserved Area'); ?></div>
                                            </div>
                                            <div class="text-md-end small text-muted">
                                                Request #<?php echo e((string) ($booking['booking_id'] ?? 0)); ?><br>
                                                Submitted <?php echo e(date('M d, Y', strtotime($booking['created_at'] ?? 'now'))); ?>
                                            </div>
                                        </div>

                                        <div class="row g-3 small">
                                            <div class="col-md-4">
                                                <div class="client-booking-meta-label">Date & Time</div>
                                                <div class="fw-semibold"><?php echo e(date('M d, Y', strtotime($booking['start_datetime']))); ?></div>
                                                <div class="text-muted"><?php echo e(date('g:i A', strtotime($booking['start_datetime']))); ?> - <?php echo e(date('g:i A', strtotime($booking['end_datetime']))); ?></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="client-booking-meta-label">Guest Count</div>
                                                <div class="fw-semibold"><?php echo e((string) ($booking['guest_count'] ?? 0)); ?> guests</div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="client-booking-meta-label">Fee</div>
                                                <div class="fw-semibold">$<?php echo e(number_format((float) ($booking['reservation_fee'] ?? 0), 2)); ?></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="client-booking-meta-label">Contact</div>
                                                <div class="fw-semibold text-break"><?php echo e($booking['attendee_email'] ?? ($user['email'] ?? '')); ?></div>
                                            </div>
                                        </div>

                                        <?php if (!empty($booking['special_requests'])): ?>
                                            <div class="client-booking-note mt-3">
                                                <strong>Notes:</strong> <?php echo e($booking['special_requests']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm portal-side-card mb-4">
                    <div class="card-body p-4">
                        <span class="section-kicker">Quick Actions</span>
                        <h2 class="h4 fw-bold mb-3">What would you like to do next?</h2>
                        <div class="d-grid gap-2">
                            <a href="client-create-event.php" class="btn btn-success">
                                <i class="bi bi-calendar-plus me-2"></i>Book New Event
                            </a>
                            <a href="parks.php" class="btn btn-outline-success">
                                <i class="bi bi-tree me-2"></i>Explore Parks
                            </a>
                            <a href="faq.php" class="btn btn-outline-success">
                                <i class="bi bi-question-circle me-2"></i>Read Booking FAQ
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm portal-side-card">
                    <div class="card-body p-4">
                        <span class="section-kicker">How this page fits the project</span>
                        <ul class="portal-mini-list mb-0">
                            <li>Shows the client's existing booked or requested events.</li>
                            <li>Gives a clear button to create a new event request.</li>
                            <li>Keeps the layout simple enough for a capstone demo.</li>
                            <li>Can later connect to payment and cancellation pages.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
