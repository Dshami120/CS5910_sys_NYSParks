<?php
require 'bootstrap.php';

$allowedWhen = ['upcoming','current','today','weekend','past','all'];
$allowedEventTypes = ['public','private'];

function event_timing_status(array $event): string {
    $now = time();
    $start = strtotime((string)$event['start_datetime']);
    $end = strtotime((string)$event['end_datetime']);
    if (($event['event_status'] ?? '') === 'cancelled') {
        return 'cancelled';
    }
    if ($end < $now) {
        return 'past';
    }
    if ($start <= $now && $end >= $now) {
        return 'current';
    }
    return 'upcoming';
}

function event_timing_label(string $status): string {
    return match ($status) {
        'current' => 'Happening now',
        'past' => 'Past event',
        'cancelled' => 'Cancelled',
        default => 'Upcoming',
    };
}

function event_timing_badge_class(string $status): string {
    return match ($status) {
        'current' => 'text-bg-info',
        'past' => 'text-bg-secondary',
        'cancelled' => 'text-bg-danger',
        default => 'text-bg-primary',
    };
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(post('action'), ['rsvp_event','cancel_rsvp'], true)) {
    $action = post('action');
    $eventId = (int) post('event_id');
    $returnQuery = post('return_query');
    $returnTo = 'events.php' . ($returnQuery !== '' ? '?' . ltrim($returnQuery, '?') : '') . ($eventId > 0 ? '#event-' . $eventId : '');

    if ($eventId <= 0) {
        flash_set('error', 'Please choose a valid event.');
        redirect($returnTo);
    }

    $eventStmt = $db->prepare("SELECT e.*, p.name AS park_name FROM events e JOIN parks p ON p.id=e.park_id WHERE e.id=?");
    $eventStmt->execute([$eventId]);
    $eventForRsvp = $eventStmt->fetch();
    if (!$eventForRsvp) {
        flash_set('error', 'Event was not found.');
        redirect($returnTo);
    }

    $email = $currentUser ? strtolower((string)$currentUser['email']) : strtolower(post('attendee_email'));
    if (!valid_email($email)) {
        flash_set('error', 'Please enter a valid email address for the RSVP.');
        redirect($returnTo);
    }

    if ($action === 'cancel_rsvp') {
        $cancelStmt = $db->prepare("UPDATE attendance SET attendance_status='cancelled' WHERE event_id=? AND attendee_email=? AND attendance_status='registered'");
        $cancelStmt->execute([$eventId, $email]);
        flash_set('success', 'Your RSVP was cancelled.');
        redirect($returnTo);
    }

    if ($eventForRsvp['event_type'] !== 'public') {
        flash_set('error', 'RSVPs are only available for public events.');
        redirect($returnTo);
    }
    if ($eventForRsvp['event_status'] !== 'published') {
        flash_set('error', 'This event is not currently open for RSVP.');
        redirect($returnTo);
    }
    if (strtotime((string)$eventForRsvp['end_datetime']) < time()) {
        flash_set('error', 'This event has already ended.');
        redirect($returnTo);
    }

    $guestCount = (int) post('guest_count', '1');
    if ($guestCount < 1) { $guestCount = 1; }
    if ($guestCount > 10) { $guestCount = 10; }

    $capacityStmt = $db->prepare("SELECT COALESCE(SUM(guest_count),0) FROM attendance WHERE event_id=? AND attendance_status IN ('registered','attended') AND attendee_email <> ?");
    $capacityStmt->execute([$eventId, $email]);
    $alreadyReservedByOthers = (int) $capacityStmt->fetchColumn();
    $capacity = (int) $eventForRsvp['capacity'];
    if ($alreadyReservedByOthers + $guestCount > $capacity) {
        $remaining = max(0, $capacity - $alreadyReservedByOthers);
        flash_set('error', $remaining > 0 ? "Only {$remaining} RSVP spot(s) remain for this event." : 'This event is full.');
        redirect($returnTo);
    }

    $userId = $currentUser ? (int)$currentUser['id'] : null;
    $rsvpStmt = $db->prepare("INSERT INTO attendance (event_id, user_id, attendee_email, guest_count, attendance_status, registered_at, checked_in_at)
        VALUES (?, ?, ?, ?, 'registered', CURRENT_TIMESTAMP, NULL)
        ON DUPLICATE KEY UPDATE user_id=VALUES(user_id), guest_count=VALUES(guest_count), attendance_status='registered', registered_at=CURRENT_TIMESTAMP, checked_in_at=NULL");
    $rsvpStmt->execute([$eventId, $userId, $email, $guestCount]);
    flash_set('success', 'Your RSVP has been recorded.');
    redirect($returnTo);
}

$search = get('q');
$category = get('category');
$eventType = get('event_type');
$region = get('region');
$when = get('when', 'upcoming');
if (!in_array($when, $allowedWhen, true)) { $when = 'upcoming'; }
if ($eventType !== '' && !in_array($eventType, $allowedEventTypes, true)) { $eventType = ''; }

$sql = "SELECT e.*, p.name AS park_name, p.region,
        COALESCE(a.registered_guests, 0) AS registered_guests,
        my.attendance_status AS my_attendance_status,
        my.guest_count AS my_guest_count,
        COALESCE(e.image_url, p.image_url, '') AS image_url,
        COALESCE(e.image_alt, p.image_alt, e.title) AS image_alt
    FROM events e
    JOIN parks p ON p.id=e.park_id
    LEFT JOIN (
        SELECT event_id, SUM(guest_count) AS registered_guests
        FROM attendance
        WHERE attendance_status IN ('registered','attended')
        GROUP BY event_id
    ) a ON a.event_id=e.id
    LEFT JOIN attendance my ON my.event_id=e.id AND my.attendee_email = ?
    WHERE e.event_status='published'";
$params = [$currentUser ? strtolower((string)$currentUser['email']) : ''];

if ($search !== '') {
    $sql .= " AND (e.title LIKE ? OR e.description LIKE ? OR e.card_summary LIKE ? OR p.name LIKE ? OR p.region LIKE ? OR e.category LIKE ?)";
    $like = "%{$search}%";
    array_push($params, $like, $like, $like, $like, $like, $like);
}
if ($eventType !== '') { $sql .= " AND e.event_type = ?"; $params[] = $eventType; }
if ($region !== '') { $sql .= " AND p.region = ?"; $params[] = $region; }
if ($category !== '') { $sql .= " AND e.category = ?"; $params[] = $category; }

if ($when === 'upcoming') {
    $sql .= " AND e.end_datetime >= NOW()";
} elseif ($when === 'current') {
    $sql .= " AND e.start_datetime <= NOW() AND e.end_datetime >= NOW()";
} elseif ($when === 'today') {
    $sql .= " AND e.start_datetime < DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND e.end_datetime >= CURDATE()";
} elseif ($when === 'weekend') {
    $today = new DateTime('today');
    $weekendStart = new DateTime('saturday this week');
    $weekendEnd = clone $weekendStart;
    $weekendEnd->modify('+2 days');
    if ($today >= $weekendEnd) {
        $weekendStart = new DateTime('saturday next week');
        $weekendEnd = clone $weekendStart;
        $weekendEnd->modify('+2 days');
    }
    $sql .= " AND e.start_datetime < ? AND e.end_datetime >= ?";
    $params[] = $weekendEnd->format('Y-m-d 00:00:00');
    $params[] = $weekendStart->format('Y-m-d 00:00:00');
} elseif ($when === 'past') {
    $sql .= " AND e.end_datetime < NOW()";
}

$orderParts = [];
if ($search !== '') {
    $orderParts[] = "CASE
        WHEN e.title LIKE " . $db->quote("%{$search}%") . " THEN 0
        WHEN p.name LIKE " . $db->quote("%{$search}%") . " THEN 1
        WHEN e.category LIKE " . $db->quote("%{$search}%") . " OR p.region LIKE " . $db->quote("%{$search}%") . " THEN 2
        WHEN e.card_summary LIKE " . $db->quote("%{$search}%") . " THEN 3
        ELSE 4
    END";
}
if ($when === 'past') {
    $orderParts[] = "e.end_datetime DESC";
} elseif ($when === 'all') {
    $orderParts[] = "CASE WHEN e.end_datetime >= NOW() THEN 0 ELSE 1 END";
    $orderParts[] = "CASE WHEN e.end_datetime >= NOW() THEN e.start_datetime END ASC";
    $orderParts[] = "CASE WHEN e.end_datetime < NOW() THEN e.end_datetime END DESC";
} else {
    $orderParts[] = "e.start_datetime ASC";
}
$sql .= " ORDER BY " . implode(', ', $orderParts);

$stmt = $db->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

$categories = $db->query("SELECT DISTINCT category FROM events WHERE event_status='published' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

$activeEventRegions = $db->query("
    SELECT DISTINCT p.region
    FROM events e
    JOIN parks p ON p.id = e.park_id
    WHERE e.event_status = 'published'
      AND e.end_datetime >= NOW()
      AND p.region IS NOT NULL
      AND p.region <> ''
    ORDER BY p.region
")->fetchAll(PDO::FETCH_COLUMN);

$inactiveEventRegions = $db->query("
    SELECT DISTINCT p.region
    FROM parks p
    WHERE p.region IS NOT NULL
      AND p.region <> ''
      AND p.region NOT IN (
          SELECT DISTINCT p2.region
          FROM events e
          JOIN parks p2 ON p2.id = e.park_id
          WHERE e.event_status = 'published'
            AND e.end_datetime >= NOW()
            AND p2.region IS NOT NULL
            AND p2.region <> ''
      )
    ORDER BY p.region
")->fetchAll(PDO::FETCH_COLUMN);

$returnQuery = http_build_query(array_filter([
    'q' => $search,
    'event_type' => $eventType,
    'region' => $region,
    'category' => $category,
    'when' => $when !== 'upcoming' ? $when : '',
], fn($value) => $value !== ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Basic page metadata -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Page title -->
  <title>NYS Parks - Events</title>

  <!-- Bootstrap core CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />

  <!-- Bootstrap Icons for lightweight icons -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
  />

  <!-- Shared site stylesheet -->
  <link rel="stylesheet" href="css/styles.css" />
</head>

<body data-page="events">
  <!-- =====================================================
       PUBLIC SITE HEADER
       - Shared top navigation for guest-facing pages
       - Using semantic tags as much as possible
       ===================================================== -->
  <header class="site-header">
    <nav class="container py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
      <section class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 gap-lg-4">
        <a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2">
          <span class="brand-badge">NY</span>
          <span class="brand-mark text-dark">
            NYS Parks<br />
            <small>&amp; RECREATION</small>
          </span>
        </a>

        <ul class="list-unstyled d-flex flex-wrap gap-3 gap-lg-4 m-0 align-items-center">
          <li><a href="parks.php" class="nav-link-custom" data-page-link="parks"><i class="bi bi-tree"></i>Parks</a></li>
          <li><a href="events.php" class="nav-link-custom" data-page-link="events"><i class="bi bi-calendar-event"></i>Events</a></li>
          <li><a href="map.php" class="nav-link-custom" data-page-link="map"><i class="bi bi-geo-alt"></i>Map</a></li>
          <li><a href="ai.php" class="nav-link-custom" data-page-link="ai"><i class="bi bi-stars"></i>AI</a></li>
          <li><a href="news.php" class="nav-link-custom" data-page-link="news"><i class="bi bi-newspaper"></i>News</a></li>
          <li><a href="about.php" class="nav-link-custom" data-page-link="about"><i class="bi bi-info-circle"></i>About Us</a></li>
          <li><a href="faq.php" class="nav-link-custom" data-page-link="faq"><i class="bi bi-question-circle"></i>FAQ</a></li>
          <li><a href="donate.php" class="nav-link-custom" data-page-link="donate"><i class="bi bi-heart"></i>Donate</a></li>
        </ul>
      </section>

      <ul class="list-unstyled d-flex flex-wrap gap-2 gap-lg-3 m-0 align-items-center">
        <?php if ($currentUser): ?>
          <li><a href="account.php" class="nav-link-custom" data-page-link="account"><i class="bi bi-person-circle"></i>Account</a></li>
          <li><a href="logout.php" class="nav-link-custom" data-page-link="logout"><i class="bi bi-box-arrow-right"></i>Logout</a></li>
        <?php else: ?>
          <li><a href="login.php" class="nav-link-custom" data-page-link="login">Log In</a></li>
          <li><a href="register.php" class="btn btn-dark nav-pill-btn" data-page-link="register">Register</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>

  <main>
      <!-- blue title section -->
      <section class="subpage-hero subpage-hero-news text-white d-flex align-items-center">
          <div class="subpage-hero-overlay"></div>
          <div class="container position-relative py-5"><div class="row">
                  <div class="col-lg-8">
                      <span class="hero-kicker">Upcoming & current events</span>
                      <h1 class="display-5 fw-bold mb-3">Events across New York State</h1>
                      <p class="lead text-white-50 mb-0">Browse published events, RSVP to public programs, and use filters to view current, upcoming, past, or all events.</p>
                  </div>
              </div>
          </div>
      </section>

    <section class="py-4">
      <section class="container">
        <?php if ($flash): ?><div class="alert alert-<?= $flash['type']==='error'?'danger':'success' ?> mb-4"><?= e($flash['message']) ?></div><?php endif; ?>
        <form class="soft-card p-3 p-lg-4" method="get" action="events.php">
          <section class="row g-3 align-items-end">
            <article class="col-lg-3">
              <label class="form-label">Search</label>
              <input type="text" name="q" value="<?= e($search) ?>" class="form-control" placeholder="Search events..." />
            </article>
            <article class="col-lg-2">
              <label class="form-label">When</label>
              <select name="when" class="form-select">
                <option value="upcoming" <?= $when==='upcoming'?'selected':'' ?>>Upcoming & Current</option>
                <option value="current" <?= $when==='current'?'selected':'' ?>>Happening Now</option>
                <option value="today" <?= $when==='today'?'selected':'' ?>>Today</option>
                <option value="weekend" <?= $when==='weekend'?'selected':'' ?>>This Weekend</option>
                <option value="past" <?= $when==='past'?'selected':'' ?>>Past Events</option>
                <option value="all" <?= $when==='all'?'selected':'' ?>>All Events</option>
              </select>
            </article>
            <article class="col-lg-2">
              <label class="form-label">Event type</label>
              <select name="event_type" class="form-select">
                <option value="">All types</option>
                <option value="public" <?= $eventType==='public'?'selected':'' ?>>Public</option>
                <option value="private" <?= $eventType==='private'?'selected':'' ?>>Private</option>
              </select>
            </article>
            <article class="col-lg-2">
              <label class="form-label">Region</label>
                <select name="region" class="form-select">
                    <option value="">All regions</option>

                    <?php if ($activeEventRegions): ?>
                        <option disabled>── Regions with booked upcoming events ──</option>
                        <?php foreach ($activeEventRegions as $regionName): ?>
                            <option value="<?= e($regionName) ?>" <?= $region === $regionName ? 'selected' : '' ?>>
                                <?= e($regionName) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($inactiveEventRegions): ?>
                        <option disabled>── All other regions ──</option>
                        <?php foreach ($inactiveEventRegions as $regionName): ?>
                            <option value="<?= e($regionName) ?>" <?= $region === $regionName ? 'selected' : '' ?>>
                                <?= e($regionName) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </article>
            <article class="col-lg-1">
              <label class="form-label">Category</label>
              <select name="category" class="form-select">
                <option value="">All</option>
                <?php foreach ($categories as $categoryName): ?><option value="<?= e($categoryName) ?>" <?= $category===$categoryName?'selected':'' ?>><?= e($categoryName) ?></option><?php endforeach; ?>
              </select>
            </article>
            <article class="col-lg-2 d-flex gap-2 justify-content-lg-end">
              <button type="submit" class="btn btn-success rounded-pill px-4">Apply</button>
              <a href="events.php" class="btn btn-outline-dark rounded-pill px-4">Reset</a>
            </article>
          </section>
        </form>
      </section>
    </section>

    <section class="pb-5">
      <section class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <p class="text-muted mb-0"><?= count($events) ?> event<?= count($events) === 1 ? '' : 's' ?> found</p>
          <?php if ($when !== 'upcoming'): ?><a href="events.php" class="small text-muted">Back to upcoming & current</a><?php endif; ?>
        </div>
        <section class="row g-4" id="events-grid">
<?php foreach ($events as $event): ?>
<?php
  $eventId = (int)$event['id'];
  $eventStart = strtotime($event['start_datetime']);
  $eventEnd = strtotime($event['end_datetime']);
  $eventFee = (float)$event['fee_amount'];
  $timingStatus = event_timing_status($event);
  $registeredGuests = (int)$event['registered_guests'];
  $spotsRemaining = max(0, (int)$event['capacity'] - $registeredGuests);
  $isFull = $spotsRemaining <= 0;
  $myStatus = (string)($event['my_attendance_status'] ?? '');
  $canRsvp = $event['event_type'] === 'public' && $timingStatus !== 'past' && $timingStatus !== 'cancelled' && !$isFull;
?>
<article class="col-md-6 col-xl-4" id="event-<?= $eventId ?>">
  <article class="event-card h-100">
    <img src="<?= e($event['image_url']) ?>" alt="<?= e($event['image_alt']) ?>" class="image-cover-event" />
    <section class="p-3 p-lg-4">
      <section class="d-flex justify-content-between align-items-start gap-3 mb-3">
        <section class="event-date-badge text-center py-2 px-1">
          <p class="small text-uppercase text-muted fw-bold mb-1"><?= strtoupper(date('M', $eventStart)) ?></p>
          <h3 class="h5 fw-bold mb-0"><?= date('d', $eventStart) ?></h3>
        </section>
        <section class="text-end">
          <p class="category-badge mb-1"><?= e($event['category']) ?></p>
          <span class="badge <?= event_timing_badge_class($timingStatus) ?> mb-1"><?= e(event_timing_label($timingStatus)) ?></span>
          <?php if ($event['event_type'] === 'private'): ?><span class="badge text-bg-warning mb-1">Private</span><?php else: ?><span class="badge text-bg-success mb-1">Public</span><?php endif; ?>
          <?php if ($event['event_type'] === 'public' && $isFull): ?><span class="badge text-bg-danger mb-1">Full</span><?php endif; ?>
          <p class="small text-muted mb-0"><?= ($eventFee > 0 ? '$' . number_format($eventFee, 0) : 'Free') ?></p>
        </section>
      </section>
      <h3 class="h5 fw-bold mb-2"><?= e($event['title']) ?></h3>
      <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i><?= e($event['park_name']) ?>, <?= e($event['region']) ?></p>
      <p class="small text-muted mb-2"><i class="bi bi-calendar3 me-1"></i><?= date('M j, Y', $eventStart) ?></p>
      <p class="small text-muted mb-2"><i class="bi bi-clock me-1"></i><?= date('g:i A', $eventStart) ?> - <?= date('g:i A', $eventEnd) ?></p>
      <p class="small text-muted mb-2"><i class="bi bi-people me-1"></i>Capacity: <?= (int)$event['capacity'] ?> · <?= $spotsRemaining ?> spot<?= $spotsRemaining === 1 ? '' : 's' ?> left</p>
      <p class="small text-muted mb-3"><?= e($event['card_summary'] ?: mb_strimwidth($event['description'], 0, 120, '…')) ?></p>
      <button type="button" class="btn btn-success w-100 rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#eventModal<?= $eventId ?>">
        View Event
      </button>
    </section>
  </article>

  <section class="modal fade" id="eventModal<?= $eventId ?>" tabindex="-1" aria-labelledby="eventModalLabel<?= $eventId ?>" aria-hidden="true">
    <section class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <section class="modal-content border-0 rounded-4 overflow-hidden">
        <img src="<?= e($event['image_url']) ?>" alt="<?= e($event['image_alt']) ?>" class="image-cover-md" />
        <section class="modal-header border-0 pb-0">
          <section>
            <p class="section-kicker mb-2"><?= e($event['category']) ?> Event</p>
            <h3 class="modal-title fw-bold" id="eventModalLabel<?= $eventId ?>"><?= e($event['title']) ?></h3>
          </section>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </section>
        <section class="modal-body pt-3">
          <section class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge <?= event_timing_badge_class($timingStatus) ?>"><?= e(event_timing_label($timingStatus)) ?></span>
            <?php if ($event['event_type'] === 'private'): ?><span class="badge text-bg-warning">Private</span><?php else: ?><span class="badge text-bg-success">Public</span><?php endif; ?>
            <?php if ($event['event_type'] === 'public' && $isFull): ?><span class="badge text-bg-danger">Full</span><?php endif; ?>
            <?php if ($myStatus === 'registered'): ?><span class="badge text-bg-primary">RSVP Confirmed</span><?php endif; ?>
            <span class="badge text-bg-light border"><?= ($eventFee > 0 ? '$' . number_format($eventFee, 2) : 'Free') ?></span>
            <span class="badge text-bg-light border">Capacity: <?= (int)$event['capacity'] ?></span>
            <span class="badge text-bg-light border"><?= $spotsRemaining ?> spot<?= $spotsRemaining === 1 ? '' : 's' ?> left</span>
          </section>
          <p class="text-muted mb-4"><?= e($event['description']) ?></p>
          <section class="row g-3">
            <article class="col-md-6">
              <section class="soft-card p-3 h-100">
                <p class="small text-muted mb-1">Park</p>
                <p class="fw-semibold mb-0"><i class="bi bi-geo-alt me-1"></i><?= e($event['park_name']) ?>, <?= e($event['region']) ?></p>
              </section>
            </article>
            <article class="col-md-6">
              <section class="soft-card p-3 h-100">
                <p class="small text-muted mb-1">Date</p>
                <p class="fw-semibold mb-0"><i class="bi bi-calendar3 me-1"></i><?= date('F j, Y', $eventStart) ?></p>
              </section>
            </article>
            <article class="col-md-6">
              <section class="soft-card p-3 h-100">
                <p class="small text-muted mb-1">Time</p>
                <p class="fw-semibold mb-0"><i class="bi bi-clock me-1"></i><?= date('g:i A', $eventStart) ?> - <?= date('g:i A', $eventEnd) ?></p>
              </section>
            </article>
            <article class="col-md-6">
              <section class="soft-card p-3 h-100">
                <p class="small text-muted mb-1">Event type</p>
                <p class="fw-semibold mb-0"><i class="bi bi-ticket-perforated me-1"></i><?= ucfirst(e($event['event_type'])) ?> / <?= e($event['category']) ?></p>
              </section>
            </article>
          </section>
        </section>
        <section class="modal-footer border-0 pt-0 d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
          <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
          <?php if ($myStatus === 'registered'): ?>
            <form method="post" class="d-inline">
              <input type="hidden" name="action" value="cancel_rsvp" />
              <input type="hidden" name="event_id" value="<?= $eventId ?>" />
              <input type="hidden" name="return_query" value="<?= e($returnQuery) ?>" />
              <button type="submit" class="btn btn-outline-danger rounded-pill fw-semibold" data-confirm="Cancel this RSVP?">Cancel RSVP</button>
            </form>
          <?php elseif ($canRsvp): ?>
            <form method="post" class="d-flex flex-column flex-md-row gap-2 align-items-md-center">
              <input type="hidden" name="action" value="rsvp_event" />
              <input type="hidden" name="event_id" value="<?= $eventId ?>" />
              <input type="hidden" name="return_query" value="<?= e($returnQuery) ?>" />
              <?php if ($currentUser): ?>
                <span class="small text-muted">RSVP as <?= e($currentUser['email']) ?></span>
              <?php else: ?>
                <input type="email" name="attendee_email" class="form-control" placeholder="Email for RSVP" required />
              <?php endif; ?>
              <select name="guest_count" class="form-select" style="max-width: 130px;">
                <?php for ($i=1; $i<=min(10, max(1, $spotsRemaining)); $i++): ?><option value="<?= $i ?>"><?= $i ?> guest<?= $i === 1 ? '' : 's' ?></option><?php endfor; ?>
              </select>
              <button type="submit" class="btn btn-success rounded-pill fw-semibold">RSVP</button>
            </form>
          <?php elseif ($event['event_type'] === 'private'): ?>
            <button type="button" class="btn btn-secondary rounded-pill fw-semibold" disabled>Private Event</button>
          <?php elseif ($timingStatus === 'past'): ?>
            <button type="button" class="btn btn-secondary rounded-pill fw-semibold" disabled>Event Ended</button>
          <?php elseif ($isFull): ?>
            <button type="button" class="btn btn-secondary rounded-pill fw-semibold" disabled>Event Full</button>
          <?php else: ?>
            <button type="button" class="btn btn-secondary rounded-pill fw-semibold" disabled>RSVP Closed</button>
          <?php endif; ?>
        </section>
      </section>
    </section>
  </section>
</article>
<?php endforeach; ?>
<?php if (!$events): ?>
  <section class="col-12">
    <section class="soft-card p-4 text-center">
      <p class="text-muted mb-3">No events matched your filters. Try clearing filters or viewing all upcoming events.</p>
      <a href="events.php" class="btn btn-outline-dark rounded-pill">Clear filters</a>
    </section>
  </section>
<?php endif; ?>
</section>
    </section>
    </section>
  </main>
  <!-- =====================================================
       SHARED FOOTER
       - Keeps the bottom section consistent across pages
       ===================================================== -->
  <footer class="footer-shell py-5 mt-5">
    <section class="container">
      <section class="footer-five">
        <article class="footer-block footer-brand">
          <a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2 mb-3">
            <span class="brand-badge">NY</span>
            <span class="brand-mark text-dark">
              NYS Parks<br />
              <small>&amp; RECREATION</small>
            </span>
          </a>
          <p class="text-muted mb-0">
            A modern gateway to New York State parks, events, maps, news, and role-based operations.
          </p>
        </article>

        <article class="footer-block">
          <h2 class="h6 fw-bold mb-3">Explore</h2>
          <ul class="list-unstyled m-0">
            <li class="mb-2"><a href="parks.php" class="text-muted text-decoration-none">Parks</a></li>
            <li class="mb-2"><a href="events.php" class="text-muted text-decoration-none">Events</a></li>
            <li class="mb-2"><a href="map.php" class="text-muted text-decoration-none">Map</a></li>
            <li class="mb-2"><a href="ai.php" class="text-muted text-decoration-none">AI</a></li>
            <li class="mb-2"><a href="news.php" class="text-muted text-decoration-none">News</a></li>
          </ul>
        </article>

        <article class="footer-block">
          <h2 class="h6 fw-bold mb-3">Account</h2>
          <ul class="list-unstyled m-0">
            <li class="mb-2"><a href="about.php" class="text-muted text-decoration-none">About</a></li>
            <li class="mb-2"><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li>
            <li class="mb-2"><a href="donate.php" class="text-muted text-decoration-none">Donate</a></li>
            <li class="mb-2"><a href="login.php" class="text-muted text-decoration-none">Log In</a></li>
            <li class="mb-2"><a href="register.php" class="text-muted text-decoration-none">Register</a></li>
            <li class="mb-2"><a href="account.php" class="text-muted text-decoration-none">Account</a></li>
          </ul>
        </article>

        <article class="footer-block">
          <h2 class="h6 fw-bold mb-3">Portals</h2>
          <ul class="list-unstyled m-0">
            <li class="mb-2"><a href="client-dashboard.php" class="text-muted text-decoration-none">Client Dashboard</a></li>
            <li class="mb-2"><a href="admin-dashboard.php" class="text-muted text-decoration-none">Admin Dashboard</a></li>
            <li class="mb-2"><a href="employee-dashboard.php" class="text-muted text-decoration-none">Employee Dashboard</a></li>
          </ul>
        </article>

        <article class="footer-block">
          <h2 class="h6 fw-bold mb-3">Contact</h2>
          <p class="text-muted mb-2">info@nysparks.gov</p>
          <p class="text-muted mb-2">(555) 123-4567</p>
          <p class="text-muted mb-0">Albany, New York</p>
        </article>
      </section>
    </section>
  </footer>

  <!-- Bootstrap JavaScript bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Shared site JavaScript -->
  <script src="js/app.js"></script>
</body>
</html>
