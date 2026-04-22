<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Book New Event | ' . SITE_NAME;
$activePage = '';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin(['client']);

$user = currentUser();
$flash = getFlashMessage();
$parks = getBookableParks();
$fields = getBookableFields();

$formData = [
    'title' => '',
    'description' => '',
    'park_id' => '',
    'field_id' => '',
    'event_date' => '',
    'start_time' => '',
    'end_time' => '',
    'guest_count' => '25',
    'attendee_email' => $user['email'] ?? '',
    'special_requests' => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formData as $key => $value) {
        $formData[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    $result = createClientEventRequest($user, $formData);

    if ($result['success']) {
        setFlashMessage('success', $result['message']);
        redirectTo('client-dashboard.php');
    }

    $errors[] = $result['message'];
}

$fieldsJson = json_encode($fields, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="client-portal-page py-5">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo e($flash['type']); ?> mb-4"><?php echo e($flash['message']); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mb-4">
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm form-shell-card">
                    <div class="card-body p-4 p-lg-5">
                        <span class="section-kicker">Client Portal</span>
                        <h1 class="h2 fw-bold mb-3">Book a new event request</h1>
                        <p class="text-muted mb-4">
                            Clients use this page to submit a private reservation request. For now the page focuses on the main booking fields your capstone needs: event title, park, field, date, time, guest count, and special requests.
                        </p>

                        <form method="post" action="client-create-event.php" novalidate>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="title" class="form-label fw-semibold">Event title</label>
                                    <input type="text" class="form-control form-control-lg" id="title" name="title" value="<?php echo e($formData['title']); ?>" placeholder="Example: Family Reunion Picnic" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="guest_count" class="form-label fw-semibold">Guest count</label>
                                    <input type="number" min="1" class="form-control form-control-lg" id="guest_count" name="guest_count" value="<?php echo e($formData['guest_count']); ?>" required>
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label fw-semibold">Event description</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Briefly describe the event and what the space will be used for." required><?php echo e($formData['description']); ?></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label for="park_id" class="form-label fw-semibold">Select park</label>
                                    <select class="form-select form-select-lg" id="park_id" name="park_id" required>
                                        <option value="">Choose a park</option>
                                        <?php foreach ($parks as $park): ?>
                                            <option value="<?php echo e((string) $park['park_id']); ?>" <?php echo $formData['park_id'] === (string) $park['park_id'] ? 'selected' : ''; ?>>
                                                <?php echo e($park['park_name']); ?><?php echo !empty($park['region']) ? ' - ' . e($park['region']) : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="field_id" class="form-label fw-semibold">Select field / area</label>
                                    <select class="form-select form-select-lg" id="field_id" name="field_id" required>
                                        <option value="">Choose a field</option>
                                    </select>
                                    <div class="form-text" id="fieldCapacityText">Choose a park first to see matching fields.</div>
                                </div>

                                <div class="col-md-4">
                                    <label for="event_date" class="form-label fw-semibold">Event date</label>
                                    <input type="date" class="form-control form-control-lg" id="event_date" name="event_date" value="<?php echo e($formData['event_date']); ?>" min="<?php echo e(date('Y-m-d')); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="start_time" class="form-label fw-semibold">Start time</label>
                                    <input type="time" class="form-control form-control-lg" id="start_time" name="start_time" value="<?php echo e($formData['start_time']); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="end_time" class="form-label fw-semibold">End time</label>
                                    <input type="time" class="form-control form-control-lg" id="end_time" name="end_time" value="<?php echo e($formData['end_time']); ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="attendee_email" class="form-label fw-semibold">Contact email</label>
                                    <input type="email" class="form-control form-control-lg" id="attendee_email" name="attendee_email" value="<?php echo e($formData['attendee_email']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Booking status on submit</label>
                                    <input type="text" class="form-control form-control-lg" value="Pending admin review" disabled>
                                </div>

                                <div class="col-12">
                                    <label for="special_requests" class="form-label fw-semibold">Special requests</label>
                                    <textarea class="form-control" id="special_requests" name="special_requests" rows="4" placeholder="Accessibility notes, setup needs, or questions for the park staff."><?php echo e($formData['special_requests']); ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-send-check me-2"></i>Submit Event Request
                                </button>
                                <a href="client-dashboard.php" class="btn btn-outline-secondary btn-lg">Back to Dashboard</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm portal-side-card mb-4">
                    <div class="card-body p-4">
                        <span class="section-kicker">How the flow works</span>
                        <ol class="portal-step-list mb-0">
                            <li>Client fills out the event request form.</li>
                            <li>Request appears on the client dashboard as pending.</li>
                            <li>Admin later reviews the request and approves or denies it.</li>
                            <li>Approved reservations can later connect to payment pages.</li>
                        </ol>
                    </div>
                </div>

                <div class="card border-0 shadow-sm portal-side-card">
                    <div class="card-body p-4">
                        <span class="section-kicker">Helpful note</span>
                        <p class="mb-0 text-muted">
                            This page already supports server-side validation and a demo-friendly save flow, so it will still work during class even if the live Bookings and Events tables are not fully imported yet.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
window.clientPortalFields = <?php echo $fieldsJson ?: '[]'; ?>;
window.clientSelectedFieldId = <?php echo json_encode($formData['field_id']); ?>;
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
