<?php
// Load project setup.
require 'bootstrap.php';

$user = $currentUser;
$donationFlow = $_SERVER['REQUEST_METHOD'] === 'POST' || get('action') === 'donate';

// Require a client account before showing or submitting the donation form.
if ($donationFlow && (!$user || $user['role'] !== 'client')) {
    flash_set('error', 'Please log in with a client account before donating.');
    redirect('login.php?next=' . urlencode('donate.php?action=donate'));
}

// Load park options for the donation dropdown.
$parks = park_options($db);
$parkNames = array_map(fn($p) => $p['name'], $parks);
sort($parkNames);

// Donation form option lists.
$donationFocusOptions = [
    'general' => 'Overall Parks Fund',
    'trails' => 'Trail Maintenance',
    'habitat' => 'Habitat & Conservation',
    'programs' => 'Programs & Education',
    'events' => 'Community Events',
    'accessibility' => 'Accessibility Improvements'
];

$amountOptions = [15, 25, 50, 100, 250];

$frequencyOptions = [
    'once' => 'One-time',
    'monthly' => 'Monthly',
    'yearly' => 'Yearly'
];

$paymentOptions = [
    'card' => 'Credit / Debit Card',
    'in_person' => 'In Person'
];

// Default form values.
$formData = [
    'donor_name' => $user ? full_name($user) : '',
    'donor_email' => $user['email'] ?? '',
    'selected_park' => 'statewide',
    'donation_focus' => 'general',
    'amount_choice' => '50',
    'custom_amount' => '',
    'frequency' => 'once',
    'payment_method' => 'card',
    'tribute_name' => '',
    'message' => '',
    'card_number' => '',
    'exp_month' => '',
    'exp_year' => '',
    'cvv' => '',
    'agree_terms' => ''
];

// Keep submitted values if validation fails.
foreach ($formData as $k => $v) {
    if (isset($_POST[$k])) {
        $formData[$k] = trim((string) $_POST[$k]);
    }
}

// Validate and save donation submissions.
// Handle submitted form actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $donorName = $formData['donor_name'];
    $donorEmail = strtolower($formData['donor_email']);
    $selectedPark = $formData['selected_park'];
    $donationFocus = $formData['donation_focus'];
    $amountChoice = $formData['amount_choice'];
    $customAmount = $formData['custom_amount'];
    $frequency = $formData['frequency'];
    $paymentMethod = $formData['payment_method'];

    if ($donorName === '') {
        $errors[] = 'Please enter your full name.';
    }

    if (!valid_email($donorEmail)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!isset($donationFocusOptions[$donationFocus])) {
        $errors[] = 'Please choose a valid donation focus.';
    }

    if (!isset($frequencyOptions[$frequency])) {
        $errors[] = 'Please choose a valid donation frequency.';
    }

    if (!isset($paymentOptions[$paymentMethod])) {
        $errors[] = 'Please choose a valid payment method.';
    }

    if ($selectedPark !== 'statewide' && !in_array($selectedPark, $parkNames, true)) {
        $errors[] = 'Please choose a valid park option.';
    }

    // Validate preset or custom donation amount.
    $finalAmount = 0.0;

    if ($amountChoice === 'custom') {
        if ($customAmount === '' || !is_numeric($customAmount) || (float) $customAmount <= 0) {
            $errors[] = 'Please enter a custom amount greater than 0.';
        } else {
            $finalAmount = round((float) $customAmount, 2);
        }
    } else {
        if (!in_array((int) $amountChoice, $amountOptions, true)) {
            $errors[] = 'Please choose a valid donation amount.';
        } else {
            $finalAmount = (float) $amountChoice;
        }
    }

    if (empty($formData['agree_terms'])) {
        $errors[] = 'Please confirm the donation acknowledgement checkbox.';
    }

    $cardNum = null;
    $month = null;
    $year = null;
    $cvv = null;
    $paymentStatus = 'pending';

    // Validate mock card details only for card donations.
    if ($paymentMethod === 'card') {
        $cardError = validate_card(
            $formData['card_number'],
            $formData['exp_month'],
            $formData['exp_year'],
            $formData['cvv']
        );

        if ($cardError) {
            $errors[] = $cardError;
        }

        $cardNum = number_only($formData['card_number']) ?: null;
        $cvv = number_only($formData['cvv']) ?: null;
        $month = $formData['exp_month'] !== '' ? (int) $formData['exp_month'] : null;
        $year = $formData['exp_year'] !== '' ? (int) $formData['exp_year'] : null;
        $paymentStatus = 'completed';
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect('donate.php?action=donate');
    }

    // Create a mock transaction reference.
    $ref = 'DON-' . date('YmdHis') . '-' . random_int(1000, 9999);

    // Store the mock donation payment.
    $db->prepare("
        INSERT INTO payments (
            user_id,
            booking_id,
            payment_type,
            payer_name,
            payer_email,
            amount,
            payment_method,
            card_num,
            exp_month,
            exp_year,
            cvv,
            payment_status,
            transaction_ref
        )
        VALUES (?, ?, 'donation', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ")->execute([
        (int) $user['id'],
        null,
        $donorName,
        $donorEmail,
        $finalAmount,
        $paymentMethod,
        $cardNum,
        $month,
        $year,
        $cvv,
        $paymentStatus,
        $ref
    ]);

    flash_set(
        'success',
        $paymentMethod === 'card'
            ? 'Thank you! Your mock card donation was recorded.'
            : 'Thank you! Your in-person donation pledge was recorded as pending.'
    );

    redirect('donate.php?action=donate');
}

// Public visitors are sent to login before accessing the donation form.
$donateUrl = $currentUser && $currentUser['role'] === 'client'
    ? 'donate.php?action=donate'
    : 'login.php?next=' . urlencode('donate.php?action=donate');
?>

<?php
// Set page metadata.
$pageTitle = 'NYS Parks - Donate';
$bodyPage = 'donate';
$extraHead = '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

    <!-- Donation page hero -->
    <section class="subpage-hero subpage-hero-donate d-flex align-items-center text-white">
        <div class="subpage-hero-overlay"></div>

        <div class="container position-relative py-5">
            <div class="row">
                <div class="col-lg-8 col-xl-7">
                    <span class="hero-kicker">SUPPORT NEW YORK STATE PARKS</span>

                    <h1 class="display-5 fw-bold mb-3">
                        Help protect parks, expand programs, and support outdoor access.
                    </h1>

                    <p class="lead text-white-50 mb-0">
                        Donation information is public. The mock payment form opens after a client login.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Donation impact cards -->
    <section class="py-5 section-soft-green border-bottom">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="impact-card h-100">
                        <div class="impact-icon">
                            <i class="bi bi-signpost-split"></i>
                        </div>

                        <h2 class="h5 fw-bold">Maintain trails</h2>

                        <p class="text-muted mb-0">
                            Support trail repairs, signage, cleanup, and safe visitor access across the state.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="impact-card h-100">
                        <div class="impact-icon">
                            <i class="bi bi-tree"></i>
                        </div>

                        <h2 class="h5 fw-bold">Protect habitats</h2>

                        <p class="text-muted mb-0">
                            Help preserve natural spaces, scenic views, and the long-term health of public lands.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="impact-card h-100">
                        <div class="impact-icon">
                            <i class="bi bi-people"></i>
                        </div>

                        <h2 class="h5 fw-bold">Fund public programs</h2>

                        <p class="text-muted mb-0">
                            Expand educational programs, family activities, seasonal events, and community outreach.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php if ($donationFlow && $user && $user['role'] === 'client'): ?>
    <!-- Logged-in client donation form -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4 align-items-start">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm donate-form-card">
                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                                <div>
                                    <p class="section-label mb-2">DONATION FORM</p>
                                    <h2 class="fw-bold mb-1">Make a contribution</h2>
                                    <p class="text-muted mb-0">Logged in as <?= e(full_name($user)) ?>.</p>
                                </div>

                                <span class="badge rounded-pill text-bg-light border px-3 py-2">
                                    <i class="bi bi-shield-lock me-1"></i> Mock secure flow
                                </span>
                            </div>

                            <?php if ($flash): ?>
                                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>" role="alert">
                                    <?= e($flash['message']) ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" action="donate.php?action=donate" id="donateForm" novalidate>
                                <!-- Donor contact fields -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="donor_name" class="form-label">Full Name</label>
                                        <input
                                                type="text"
                                                class="form-control form-control-lg"
                                                id="donor_name"
                                                name="donor_name"
                                                value="<?= e($formData['donor_name']) ?>"
                                                required
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label for="donor_email" class="form-label">Email Address</label>
                                        <input
                                                type="email"
                                                class="form-control form-control-lg"
                                                id="donor_email"
                                                name="donor_email"
                                                value="<?= e($formData['donor_email']) ?>"
                                                required
                                        >
                                    </div>
                                </div>

                                <!-- Park and donation focus -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="selected_park" class="form-label">Choose a Park</label>

                                        <select class="form-select form-select-lg" id="selected_park" name="selected_park">
                                            <option value="statewide" <?= $formData['selected_park'] === 'statewide' ? 'selected' : '' ?>>
                                                Statewide Parks Fund
                                            </option>

                                            <?php foreach ($parkNames as $parkName): ?>
                                                <option
                                                        value="<?= e($parkName) ?>"
                                                    <?= $formData['selected_park'] === $parkName ? 'selected' : '' ?>
                                                >
                                                    <?= e($parkName) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="donation_focus" class="form-label">Use of Donation</label>

                                        <select class="form-select form-select-lg" id="donation_focus" name="donation_focus">
                                            <?php foreach ($donationFocusOptions as $value => $label): ?>
                                                <option
                                                        value="<?= e($value) ?>"
                                                    <?= $formData['donation_focus'] === $value ? 'selected' : '' ?>
                                                >
                                                    <?= e($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Donation amount choices -->
                                <div class="mb-4">
                                    <label class="form-label d-block mb-3">Choose Amount</label>

                                    <div class="row g-3">
                                        <?php foreach ($amountOptions as $amount): ?>
                                            <div class="col-sm-6 col-lg-4">
                                                <label class="amount-choice-card h-100 w-100">
                                                    <input
                                                            class="form-check-input amount-choice-input"
                                                            type="radio"
                                                            name="amount_choice"
                                                            value="<?= e((string) $amount) ?>"
                                                        <?= $formData['amount_choice'] === (string) $amount ? 'checked' : '' ?>
                                                    >

                                                    <span class="amount-choice-content">
                                                        <span class="amount-choice-value">$<?= e((string) $amount) ?></span>
                                                        <span class="amount-choice-copy">Suggested gift</span>
                                                    </span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>

                                        <div class="col-sm-6 col-lg-4">
                                            <label class="amount-choice-card h-100 w-100 custom-amount-card">
                                                <input
                                                        class="form-check-input amount-choice-input"
                                                        type="radio"
                                                        name="amount_choice"
                                                        value="custom"
                                                    <?= $formData['amount_choice'] === 'custom' ? 'checked' : '' ?>
                                                >

                                                <span class="amount-choice-content">
                                                    <span class="amount-choice-value">Custom</span>
                                                    <span class="amount-choice-copy">Enter your own amount</span>

                                                    <input
                                                            type="number"
                                                            min="1"
                                                            step="0.01"
                                                            class="form-control mt-3"
                                                            id="custom_amount"
                                                            name="custom_amount"
                                                            placeholder="Enter amount"
                                                            value="<?= e($formData['custom_amount']) ?>"
                                                    >
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Frequency and payment method -->
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label d-block mb-3">Donation Frequency</label>

                                        <div class="donate-pill-group">
                                            <?php foreach ($frequencyOptions as $value => $label): ?>
                                                <label class="pill-option">
                                                    <input
                                                            type="radio"
                                                            name="frequency"
                                                            value="<?= e($value) ?>"
                                                        <?= $formData['frequency'] === $value ? 'checked' : '' ?>
                                                    >

                                                    <span><?= e($label) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label d-block mb-3">Payment Method</label>

                                        <div class="donate-pill-group">
                                            <?php foreach ($paymentOptions as $value => $label): ?>
                                                <label class="pill-option">
                                                    <input
                                                            type="radio"
                                                            name="payment_method"
                                                            value="<?= e($value) ?>"
                                                        <?= $formData['payment_method'] === $value ? 'checked' : '' ?>
                                                    >

                                                    <span><?= e($label) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mock card details -->
                                <div id="cardDetailsBlock" class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="card_number" class="form-label">Card Number</label>
                                        <input
                                                type="text"
                                                class="form-control"
                                                id="card_number"
                                                name="card_number"
                                                maxlength="19"
                                                value="<?= e($formData['card_number']) ?>"
                                                placeholder="Card number"
                                        >
                                    </div>

                                    <div class="col-md-3">
                                        <label for="exp_month" class="form-label">Exp. Month</label>
                                        <input
                                                type="number"
                                                min="1"
                                                max="12"
                                                class="form-control"
                                                id="exp_month"
                                                name="exp_month"
                                                value="<?= e($formData['exp_month']) ?>"
                                                placeholder="MM"
                                        >
                                    </div>

                                    <div class="col-md-3">
                                        <label for="exp_year" class="form-label">Exp. Year</label>
                                        <input
                                                type="number"
                                                min="<?= date('Y') ?>"
                                                class="form-control"
                                                id="exp_year"
                                                name="exp_year"
                                                value="<?= e($formData['exp_year']) ?>"
                                                placeholder="YYYY"
                                        >
                                    </div>

                                    <div class="col-md-3">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input
                                                type="text"
                                                maxlength="4"
                                                class="form-control"
                                                id="cvv"
                                                name="cvv"
                                                value="<?= e($formData['cvv']) ?>"
                                                placeholder="CVV"
                                        >
                                    </div>
                                </div>

                                <!-- Optional dedication fields -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="tribute_name" class="form-label">
                                            Dedication / Memorial Name <span class="text-muted">(optional)</span>
                                        </label>

                                        <input
                                                type="text"
                                                class="form-control"
                                                id="tribute_name"
                                                name="tribute_name"
                                                value="<?= e($formData['tribute_name']) ?>"
                                                placeholder="Ex: In honor of Jane Doe"
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label for="message" class="form-label">
                                            Message <span class="text-muted">(optional)</span>
                                        </label>

                                        <input
                                                type="text"
                                                class="form-control"
                                                id="message"
                                                name="message"
                                                value="<?= e($formData['message']) ?>"
                                                placeholder="Add a short note"
                                        >
                                    </div>
                                </div>

                                <!-- Mock payment notice -->
                                <div class="alert alert-light border rounded-4 small mb-4">
                                    <strong>Capstone note:</strong>
                                    this page uses mock payment handling. Card numbers and CVV are validated only by digit count/range for the capstone database.
                                </div>

                                <div class="form-check mb-4">
                                    <input
                                            class="form-check-input"
                                            type="checkbox"
                                            value="1"
                                            id="agree_terms"
                                            name="agree_terms"
                                        <?= !empty($formData['agree_terms']) ? 'checked' : '' ?>
                                    >

                                    <label class="form-check-label" for="agree_terms">
                                        I understand this capstone version creates a mock confirmation instead of charging a real payment method.
                                    </label>
                                </div>

                                <!-- Donation form actions -->
                                <div class="d-grid d-sm-flex gap-3">
                                    <button type="submit" class="btn btn-success btn-lg px-4">
                                        <i class="bi bi-heart-fill me-2"></i>Submit Donation
                                    </button>

                                    <a href="parks.php" class="btn btn-outline-success btn-lg px-4">
                                        Explore Parks First
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Donation sidebar -->
                <div class="col-lg-5">
                    <div class="donate-sidebar-stack">
                        <div class="card border-0 shadow-sm donate-summary-card">
                            <div class="card-body p-4">
                                <p class="section-label mb-2">GIVING SNAPSHOT</p>
                                <h2 class="h4 fw-bold mb-3">Why donations matter</h2>

                                <div class="summary-line">
                                    <span>Supports public access</span>
                                    <strong>Statewide</strong>
                                </div>

                                <div class="summary-line">
                                    <span>Card donations</span>
                                    <strong>Completed</strong>
                                </div>

                                <div class="summary-line">
                                    <span>In-person donations</span>
                                    <strong>Pending</strong>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm donate-summary-card">
                            <div class="card-body p-4">
                                <p class="section-label mb-2">WHAT HAPPENS NEXT</p>

                                <ol class="small text-muted mb-0 ps-3">
                                    <li class="mb-2">PHP validates the fields on the server.</li>
                                    <li class="mb-2">Card payments are marked completed.</li>
                                    <li class="mb-2">In-person payments are recorded as pending.</li>
                                    <li>The payment record is stored in the shared payments table.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>
    <!-- Public donation call-to-action -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm donate-form-card">
                        <div class="card-body p-4 p-lg-5 text-center">
                            <?php if ($flash): ?>
                                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>" role="alert">
                                    <?= e($flash['message']) ?>
                                </div>
                            <?php endif; ?>

                            <p class="section-label mb-2">DONATION FORM</p>

                            <h2 class="fw-bold mb-3">
                                Ready to make a contribution?
                            </h2>

                            <p class="text-muted mb-4">
                                Anyone can view donation information. Click Donate Now to log in as a client and open the mock payment form.
                            </p>

                            <a class="btn btn-success btn-lg rounded-pill px-5" href="<?= e($donateUrl) ?>">
                                <i class="bi bi-heart-fill me-2"></i>Donate Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

    <script>
        // Show card fields only when card payment is selected.
        document.querySelectorAll('input[name="payment_method"]').forEach(input => {
            input.addEventListener('change', () => {
                const block = document.getElementById('cardDetailsBlock');
                const selectedPayment = document.querySelector('input[name="payment_method"]:checked')?.value;

                if (block) {
                    block.style.display = selectedPayment === 'card' ? '' : 'none';
                }
            });
        });

        // Apply payment field visibility when page loads.
        const checkedPayment = document.querySelector('input[name="payment_method"]:checked');

        if (checkedPayment) {
            checkedPayment.dispatchEvent(new Event('change'));
        }
    </script>

<?php include __DIR__ . '/includes/footer.php'; ?>
