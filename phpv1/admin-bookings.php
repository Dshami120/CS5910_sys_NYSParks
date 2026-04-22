<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Admin Booking Requests | ' . SITE_NAME;
$activePage = '';
$adminSection = 'bookings';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin(['admin']);

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $action = trim((string) ($_POST['booking_action'] ?? ''));

    $result = updateAdminBookingStatus($bookingId, $action, (int) ($user['user_id'] ?? 0));
    setFlashMessage($result['success'] ? 'success' : 'danger', $result['message']);
    redirectTo('admin-bookings.php');
}

$flash = getFlashMessage();
$bookings = getAdminBookingRequests();
$metrics = getAdminDashboardMetrics($bookings, getAdminPtoRequests());

$statusFilter = strtolower(trim((string) ($_GET['status'] ?? 'all')));
$search = strtolower(trim((string) ($_GET['q'] ?? '')));
$allowedStatuses = ['all', 'pending', 'approved', 'confirmed', 'denied', 'cancelled'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

$filteredBookings = array_values(array_filter($bookings, function ($booking) use ($statusFilter, $search) {
    $status = strtolower($booking['booking_status'] ?? 'pending');
    $searchBlob = strtolower(implode(' ', [
        $booking['title'] ?? '',
        $booking['park_name'] ?? '',
        $booking['field_name'] ?? '',
        $booking['client_name'] ?? '',
        $booking['client_email'] ?? '',
    ]));

    $matchesStatus = $statusFilter === 'all' || $status === $statusFilter;
    $matchesSearch = $search === '' || str_contains($searchBlob, $search);

    return $matchesStatus && $matchesSearch;
}));

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="admin-portal-page py-5">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo e($flash['type']); ?> mb-4"><?php echo e($flash['message']); ?></div>
        <?php endif; ?>

        <?php require __DIR__ . '/includes/admin-nav.php'; ?>

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <span class="section-kicker">Booking operations</span>
                        <h1 class="h2 fw-bold mb-2">Manage event booking requests</h1>
                        <p class="text-muted mb-0">
                            Keep the dashboard focused on summary information, and use this screen for the full booking approval queue.
                            Pending requests can be approved or denied here.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="mini-stat-card h-100">
                                    <div class="mini-stat-number"><?php echo e((string) $metrics['pending_bookings']); ?></div>
                                    <div class="mini-stat-label">Pending</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mini-stat-card h-100">
                                    <div class="mini-stat-number"><?php echo e((string) $metrics['events_booked']); ?></div>
                                    <div class="mini-stat-label">Booked</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-md-6 col-lg-5">
                        <label for="bookingSearch" class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" id="bookingSearch" name="q" value="<?php echo e($_GET['q'] ?? ''); ?>" placeholder="Search by client, park, field, or event title">
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <label for="bookingStatusFilter" class="form-label fw-semibold">Status</label>
                        <select class="form-select" id="bookingStatusFilter" name="status">
                            <?php foreach ($allowedStatuses as $statusOption): ?>
                                <option value="<?php echo e($statusOption); ?>" <?php echo $statusFilter === $statusOption ? 'selected' : ''; ?>>
                                    <?php echo e(ucfirst($statusOption)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8 col-lg-4 d-flex gap-2">
                        <button type="submit" class="btn btn-success w-100">Apply Filters</button>
                        <a href="admin-bookings.php" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </section>

        <section class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 fw-bold mb-0">Booking request queue</h2>
                    <span class="badge text-bg-light"><?php echo e((string) count($filteredBookings)); ?> result<?php echo count($filteredBookings) === 1 ? '' : 's'; ?></span>
                </div>

                <?php if (empty($filteredBookings)): ?>
                    <div class="alert alert-light border mb-0">No booking requests match the current filters.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle admin-data-table">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Client</th>
                                    <th>Park / Field</th>
                                    <th>Date</th>
                                    <th>Guests</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($filteredBookings as $booking): ?>
                                    <?php $status = strtolower($booking['booking_status'] ?? 'pending'); ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?php echo e($booking['title'] ?? 'Reservation Request'); ?></div>
                                            <div class="small text-muted"><?php echo e($booking['special_requests'] ?? 'No special notes.'); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?php echo e($booking['client_name'] ?? 'Client User'); ?></div>
                                            <div class="small text-muted"><?php echo e($booking['client_email'] ?? 'email not available'); ?></div>
                                        </td>
                                        <td>
                                            <div><?php echo e($booking['park_name'] ?? 'NYS Park'); ?></div>
                                            <div class="small text-muted"><?php echo e($booking['field_name'] ?? 'Reserved Area'); ?></div>
                                        </td>
                                        <td>
                                            <div><?php echo e(date('M d, Y', strtotime($booking['start_datetime']))); ?></div>
                                            <div class="small text-muted"><?php echo e(date('g:i A', strtotime($booking['start_datetime']))); ?> - <?php echo e(date('g:i A', strtotime($booking['end_datetime']))); ?></div>
                                        </td>
                                        <td><?php echo e((string) ($booking['guest_count'] ?? 0)); ?></td>
                                        <td><span class="badge text-bg-<?php echo e(getBookingStatusBadgeClass($status)); ?>"><?php echo e(ucfirst($status)); ?></span></td>
                                        <td>
                                            <?php if ($status === 'pending'): ?>
                                                <div class="d-flex flex-column gap-2">
                                                    <form method="post">
                                                        <input type="hidden" name="booking_id" value="<?php echo e((string) $booking['booking_id']); ?>">
                                                        <input type="hidden" name="booking_action" value="approved">
                                                        <button type="submit" class="btn btn-sm btn-success w-100">Approve</button>
                                                    </form>
                                                    <form method="post">
                                                        <input type="hidden" name="booking_id" value="<?php echo e((string) $booking['booking_id']); ?>">
                                                        <input type="hidden" name="booking_action" value="denied">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Deny</button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <div class="small text-muted">Updated <?php echo e(!empty($booking['decision_date']) ? date('M d, Y', strtotime($booking['decision_date'])) : 'previously'); ?></div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
