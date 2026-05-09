<?php
// Load project setup.
require 'bootstrap.php';
$user = require_role($db, 'admin');

/**
 * Re-check booking approval rules before admin creates/updates a private event.
 */
function admin_booking_approval_error(PDO $db, array $booking): string {
    $bookingId = (int) $booking['id'];
    $eventId = !empty($booking['event_id']) ? (int) $booking['event_id'] : 0;
    $parkId = (int) $booking['park_id'];
    $fieldId = !empty($booking['field_id']) ? (int) $booking['field_id'] : 0;
    $guestCount = (int) $booking['guest_count'];
    $startDt = (string) $booking['start_datetime'];
    $endDt = (string) $booking['end_datetime'];

    // Confirm park capacity before approval.
    $parkStmt = $db->prepare("SELECT max_capacity FROM parks WHERE id=?");
    $parkStmt->execute([$parkId]);
    $parkCapacity = $parkStmt->fetchColumn();

    if ($parkCapacity === false) {
        return 'The selected park no longer exists.';
    }

    if ($guestCount > (int) $parkCapacity) {
        return 'Guest count exceeds the selected park capacity of ' . (int) $parkCapacity . '.';
    }

    if ($fieldId > 0) {
        // Confirm selected field still belongs to this park and is available.
        $fieldStmt = $db->prepare("SELECT capacity, availability_status FROM fields WHERE id=? AND park_id=?");
        $fieldStmt->execute([$fieldId, $parkId]);
        $field = $fieldStmt->fetch();

        if (!$field) {
            return 'The selected field no longer exists for this park.';
        }

        if (($field['availability_status'] ?? '') !== 'available') {
            return 'The selected field is no longer available.';
        }

        if ($guestCount > (int) $field['capacity']) {
            return 'Guest count exceeds the selected field capacity of ' . (int) $field['capacity'] . '.';
        }

        // Block overlapping approved/confirmed/completed bookings for the same field.
        $fieldBookingOverlap = $db->prepare("
            SELECT COUNT(*)
            FROM bookings
            WHERE field_id=?
              AND id<>?
              AND booking_status IN ('approved','confirmed','completed')
              AND NOT (end_datetime <= ? OR start_datetime >= ?)
        ");
        $fieldBookingOverlap->execute([$fieldId, $bookingId, $startDt, $endDt]);

        // Block overlapping active events for the same field.
        $fieldEventOverlap = $db->prepare("
            SELECT COUNT(*)
            FROM events
            WHERE field_id=?
              AND id<>?
              AND event_status IN ('draft','published','closed')
              AND NOT (end_datetime <= ? OR start_datetime >= ?)
        ");
        $fieldEventOverlap->execute([$fieldId, $eventId, $startDt, $endDt]);

        // Whole-park bookings also block individual field bookings.
        $wholeParkBookingOverlap = $db->prepare("
            SELECT COUNT(*)
            FROM bookings
            WHERE park_id=?
              AND field_id IS NULL
              AND id<>?
              AND booking_status IN ('approved','confirmed','completed')
              AND NOT (end_datetime <= ? OR start_datetime >= ?)
        ");
        $wholeParkBookingOverlap->execute([$parkId, $bookingId, $startDt, $endDt]);

        // Whole-park events also block individual field bookings.
        $wholeParkEventOverlap = $db->prepare("
            SELECT COUNT(*)
            FROM events
            WHERE park_id=?
              AND field_id IS NULL
              AND id<>?
              AND event_status IN ('draft','published','closed')
              AND NOT (end_datetime <= ? OR start_datetime >= ?)
        ");
        $wholeParkEventOverlap->execute([$parkId, $eventId, $startDt, $endDt]);

        $conflicts =
            (int) $fieldBookingOverlap->fetchColumn() +
            (int) $fieldEventOverlap->fetchColumn() +
            (int) $wholeParkBookingOverlap->fetchColumn() +
            (int) $wholeParkEventOverlap->fetchColumn();

        if ($conflicts > 0) {
            return 'This field or whole park already has an approved booking or active event during that time.';
        }

        return '';
    }

    // Whole-park booking must not overlap any active booking at this park.
    $parkBookingOverlap = $db->prepare("
        SELECT COUNT(*)
        FROM bookings
        WHERE park_id=?
          AND id<>?
          AND booking_status IN ('approved','confirmed','completed')
          AND NOT (end_datetime <= ? OR start_datetime >= ?)
    ");
    $parkBookingOverlap->execute([$parkId, $bookingId, $startDt, $endDt]);

    // Whole-park booking must not overlap any active event at this park.
    $parkEventOverlap = $db->prepare("
        SELECT COUNT(*)
        FROM events
        WHERE park_id=?
          AND id<>?
          AND event_status IN ('draft','published','closed')
          AND NOT (end_datetime <= ? OR start_datetime >= ?)
    ");
    $parkEventOverlap->execute([$parkId, $eventId, $startDt, $endDt]);

    if (((int) $parkBookingOverlap->fetchColumn() + (int) $parkEventOverlap->fetchColumn()) > 0) {
        return 'This park already has an approved booking or active event during that time.';
    }

    return '';
}

// Handle submitted form actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate requested admin action.
    $id = (int) post('booking_id');
    $decision = post('decision');
    $validDecisions = ['approved','confirmed','completed','denied','cancelled'];

    if (!in_array($decision, $validDecisions, true)) {
        flash_set('error', 'Invalid booking action.');
        redirect('admin-bookings.php');
    }

    // Load the booking being reviewed.
    $stmt = $db->prepare("SELECT * FROM bookings WHERE id=?");
    $stmt->execute([$id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        flash_set('error', 'Booking not found.');
        redirect('admin-bookings.php');
    }

    // Re-check conflicts and capacity before approval-style decisions.
    if (in_array($decision, ['approved','confirmed','completed'], true)) {
        $approvalError = admin_booking_approval_error($db, $booking);

        if ($approvalError !== '') {
            flash_set('error', $approvalError);
            redirect('admin-bookings.php');
        }
    }

    $db->beginTransaction();

    try {
        // Create or update linked private event when approval requires it.
        $eventId = $booking['event_id'] ? (int) $booking['event_id'] : null;

        if (in_array($decision, ['approved','confirmed','completed'], true)) {
            $eventStatus = $decision === 'completed' ? 'completed' : 'published';

            if (!$eventId) {
                $insert = $db->prepare("
                    INSERT INTO events (
                        park_id,
                        field_id,
                        title,
                        description,
                        card_summary,
                        category,
                        event_type,
                        start_datetime,
                        end_datetime,
                        capacity,
                        fee_amount,
                        event_status,
                        created_by
                    )
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
                ");

                $insert->execute([
                    (int) $booking['park_id'],
                    $booking['field_id'] ?: null,
                    $booking['title'],
                    $booking['event_description'],
                    $booking['requested_setup'] ?: null,
                    $booking['booking_type'],
                    'private',
                    $booking['start_datetime'],
                    $booking['end_datetime'],
                    (int) $booking['guest_count'],
                    (float) $booking['reservation_fee'],
                    $eventStatus,
                    (int) $user['id']
                ]);

                $eventId = (int) $db->lastInsertId();
            } else {
                $update = $db->prepare("
                    UPDATE events
                    SET park_id=?,
                        field_id=?,
                        title=?,
                        description=?,
                        card_summary=?,
                        category=?,
                        event_type='private',
                        start_datetime=?,
                        end_datetime=?,
                        capacity=?,
                        fee_amount=?,
                        event_status=?,
                        updated_at=NOW()
                    WHERE id=?
                ");

                $update->execute([
                    (int) $booking['park_id'],
                    $booking['field_id'] ?: null,
                    $booking['title'],
                    $booking['event_description'],
                    $booking['requested_setup'] ?: null,
                    $booking['booking_type'],
                    $booking['start_datetime'],
                    $booking['end_datetime'],
                    (int) $booking['guest_count'],
                    (float) $booking['reservation_fee'],
                    $eventStatus,
                    $eventId
                ]);
            }
        } elseif ($eventId && in_array($decision, ['denied','cancelled'], true)) {
            // Cancel linked private event when booking is denied or cancelled.
            $db->prepare("UPDATE events SET event_status='cancelled', updated_at=NOW() WHERE id=?")
                ->execute([$eventId]);
        }

        // Save final admin decision.
        $db->prepare("
            UPDATE bookings
            SET booking_status=?,
                event_id=?,
                reviewed_by=?,
                reviewed_at=NOW(),
                admin_notes=?
            WHERE id=?
        ")->execute([
            $decision,
            $eventId,
            $user['id'],
            post('admin_notes') ?: null,
            $id
        ]);

        $db->commit();
        flash_set('success', 'Booking updated. Linked private event was created or updated when required.');
    } catch (Throwable $e) {
        $db->rollBack();
        flash_set('error', 'Booking could not be updated. Please review the request and try again.');
    }

    redirect('admin-bookings.php');
}
$pending = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE booking_status='pending'")->fetchColumn();
$approvedToday = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE booking_status IN ('approved','confirmed') AND DATE(reviewed_at)=CURDATE()")->fetchColumn();
$feesQueued = (float) $db->query("SELECT COALESCE(SUM(reservation_fee),0) FROM bookings WHERE booking_status='pending'")->fetchColumn();
$rows = $db->query("SELECT b.*, p.name AS park_name, CONCAT(u.first_name,' ',u.last_name) AS requester, (SELECT py.payment_status FROM payments py WHERE py.booking_id=b.id AND py.payment_type='reservation' ORDER BY py.created_at DESC LIMIT 1) AS payment_status, (SELECT py.payment_method FROM payments py WHERE py.booking_id=b.id AND py.payment_type='reservation' ORDER BY py.created_at DESC LIMIT 1) AS payment_method FROM bookings b JOIN parks p ON p.id=b.park_id JOIN users u ON u.id=b.client_id ORDER BY b.created_at DESC")->fetchAll();
?><?php
// Set page metadata.
$pageTitle = 'NYS Parks - Admin Bookings';
$bodyPage = 'admin-bookings';
$extraHead = '';
?>


<?php include __DIR__ . '/includes/header.php'; ?>

<main class="py-5">
      <section class="container">
          <?php if ($flash): ?>
              <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> mb-4"><?= e($flash['message']) ?></div>
          <?php endif; ?>
          <header class="mb-4">
              <p class="section-kicker mb-2">Admin portal</p>
              <h1 class="h2 fw-bold mb-1">Client Booking Approval Queue</h1>
              <p class="text-muted mb-0">Approve or deny client event booking requests and monitor related fees.</p>
              <section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                  <div></div>
                  <div class="d-flex flex-wrap gap-2">
                      <a class="btn btn-outline-dark" href="admin-dashboard.php"><i class="bi bi-speedometer2"></i> Admin Dash</a>
                      <a class="btn btn-outline-dark" href="admin-employee-schedule.php"><i class="bi bi-calendar3"></i> Employee Schedules</a>
                      <a class="btn btn-outline-dark" href="admin-pto.php"><i class="bi bi-briefcase"></i> PTO Requests</a>
                      <a class="btn btn-success" href="admin-bookings.php"><i class="bi bi-journal-check"></i> Client Bookings</a>
                      <a class="btn btn-outline-dark" href="admin-news.php"><i class="bi bi-journal-check"></i> News Manager</a>
                      <a class="btn btn-outline-dark" href="admin-employee-accounts.php"><i class="bi bi-journal-check"></i> Employee Accounts</a>
                      <a class="btn btn-outline-dark" href="admin-csv.php"><i class="bi bi-journal-check"></i> CSV</a>
                  </div>
              </section>
          </header>
          <section class="row g-4 mb-4">
              <article class="col-md-4">
                  <section class="stats-card h-100">
                      <p class="small text-uppercase text-muted mb-1">Pending</p>
                      <h2 class="h3 fw-bold mb-2"><?= $pending ?></h2>
                      <p class="text-muted mb-0">Requests waiting for staff approval.</p>
                  </section>
              </article>

              <article class="col-md-4">
                  <section class="stats-card h-100">
                      <p class="small text-uppercase text-muted mb-1">Approved today</p>
                      <h2 class="h3 fw-bold mb-2"><?= $approvedToday ?></h2>
                      <p class="text-muted mb-0">Moved to confirmed status.</p>
                  </section>
              </article>

              <article class="col-md-4">
                  <section class="stats-card h-100">
                      <p class="small text-uppercase text-muted mb-1">Fees queued</p>
                      <h2 class="h3 fw-bold mb-2">$<?= number_format($feesQueued, 0) ?></h2>
                      <p class="text-muted mb-0">Reservation charges tied to open requests.</p>
                  </section>
              </article>
          </section>

          <section class="list-shell" style="width: 115%; margin-left: -7.5%;">
              <section class="table-responsive">
                  <table class="table align-middle mb-0">
                      <thead>
                          <tr>
                              <th scope="col">Requester</th>
                              <th scope="col">Event</th>
                              <th scope="col">Park</th>
                              <th scope="col">Date</th>
                              <th scope="col">Fee</th>
                              <th scope="col">Current Booking Status</th>
                              <th scope="col">Payment</th>
                              <th scope="col">Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($rows as $row): ?>
                          <tr>
                              <td><?= e($row['requester']) ?></td>
                              <td><?= e($row['title']) ?></td>
                              <td class="text-muted"><?= e($row['park_name']) ?></td>
                              <td class="text-muted"><?= format_date(substr((string)$row['start_datetime'], 0, 10)) ?></td>
                              <td class="text-muted">$<?= number_format((float) $row['reservation_fee'], 0) ?></td>
                              <td>
                                  <span class="status-pill <?= booking_status_class((string)$row['booking_status']) ?>">
                                      <?= e(ucfirst(str_replace('_', ' ', (string)$row['booking_status']))) ?>
                                  </span>
                              </td>
                              <td class="text-muted">
                                  <?php if ($row['payment_status']): ?>
                                      <span class="status-pill <?= booking_status_class((string)$row['payment_status']) ?>"><?= e(ucfirst(str_replace('_', ' ', (string)$row['payment_status']))) ?></span><br>
                                      <small><?= e(str_replace('_', ' ', (string)$row['payment_method'])) ?></small>
                                  <?php else: ?>
                                      No payment
                                  <?php endif; ?>
                              </td>
                              <td>
                                  <form method="post" class="d-flex flex-wrap gap-3 mb-3">
                                      <input type="hidden" name="booking_id" value="<?= (int)$row['id'] ?>" />
                                      <input type="text" name="admin_notes" class="form-control form-control-sm" placeholder="Admin note" style="max-width: 140px;" />
                                      <select name="decision" class="form-select form-select-sm" style="max-width: 150px;">
                                          <?php foreach (['approved' => 'Approve', 'confirmed' => 'Confirm', 'completed' => 'Complete', 'denied' => 'Deny', 'cancelled' => 'Cancel'] as $value => $label): ?>
                                              <option value="<?= e($value) ?>" <?= ($row['booking_status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                          <?php endforeach; ?>
                                      </select>

                                      <button class="btn btn-sm btn-success rounded-pill" type="submit">Update</button>
                                  </form>
                              </td>
                          </tr>
                          <?php endforeach; ?>
                      </tbody>
                  </table>
              </section>
          </section>
</section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
