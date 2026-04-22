<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'Donate | ' . SITE_NAME;
$activePage = 'donate';

/*
|--------------------------------------------------------------------------
| Donate page data
|--------------------------------------------------------------------------
| We reuse the public parks list so the donation form can offer a real NYS
| park selection even before the database is fully connected.
*/
$parks = getAllParks();
$parkNames = array_values(array_unique(array_map(fn($park) => $park['park_name'] ?? '', $parks)));
sort($parkNames);

$donationFocusOptions = [
    'general' => 'Overall Parks Fund',
    'trails' => 'Trail Maintenance',
    'habitat' => 'Habitat & Conservation',
    'programs' => 'Programs & Education',
    'events' => 'Community Events',
    'accessibility' => 'Accessibility Improvements',
];

$amountOptions = [15, 25, 50, 100, 250];
$frequencyOptions = [
    'once' => 'One-time',
    'monthly' => 'Monthly',
    'yearly' => 'Yearly',
];
$paymentOptions = [
    'card' => 'Credit / Debit Card',
    'paypal' => 'PayPal',
    'applepay' => 'Apple Pay',
];

$errors = [];
$successMessage = '';
$receiptXml = '';
$formData = [
    'donor_name' => '',
    'donor_email' => '',
    'selected_park' => 'statewide',
    'donation_focus' => 'general',
    'amount_choice' => '50',
    'custom_amount' => '',
    'frequency' => 'once',
    'payment_method' => 'card',
    'tribute_name' => '',
    'message' => '',
    'agree_terms' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formData as $key => $value) {
        $formData[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    $donorName = $formData['donor_name'];
    $donorEmail = $formData['donor_email'];
    $selectedPark = $formData['selected_park'];
    $donationFocus = $formData['donation_focus'];
    $amountChoice = $formData['amount_choice'];
    $customAmount = $formData['custom_amount'];
    $frequency = $formData['frequency'];
    $paymentMethod = $formData['payment_method'];
    $tributeName = $formData['tribute_name'];
    $message = $formData['message'];

    if ($donorName === '') {
        $errors[] = 'Please enter your full name.';
    }

    if ($donorEmail === '' || !filter_var($donorEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!array_key_exists($donationFocus, $donationFocusOptions)) {
        $errors[] = 'Please choose a valid donation focus.';
    }

    if (!array_key_exists($frequency, $frequencyOptions)) {
        $errors[] = 'Please choose a valid donation frequency.';
    }

    if (!array_key_exists($paymentMethod, $paymentOptions)) {
        $errors[] = 'Please choose a valid payment method.';
    }

    if ($selectedPark !== 'statewide' && !in_array($selectedPark, $parkNames, true)) {
        $errors[] = 'Please choose a valid park option.';
    }

    // Work out the final donation amount.
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

    if (empty($_POST['agree_terms'])) {
        $errors[] = 'Please confirm the donation acknowledgement checkbox.';
    }

    if (empty($errors)) {
        $transactionRef = 'DON-' . date('Ymd') . '-' . strtoupper(substr(md5($donorEmail . microtime()), 0, 8));
        $parkTarget = $selectedPark === 'statewide' ? 'Statewide Parks Fund' : $selectedPark;
        $focusLabel = $donationFocusOptions[$donationFocus];
        $frequencyLabel = $frequencyOptions[$frequency];
        $paymentLabel = $paymentOptions[$paymentMethod];

        $successMessage = 'Thank you! Your donation form was submitted successfully. This capstone build is using a mock confirmation instead of a live payment processor.';

        // Build a simple XML receipt preview so the project can later export
        // structured donation data if needed.
        $xml = new SimpleXMLElement('<donation_receipt/>' );
        $xml->addChild('transaction_ref', $transactionRef);
        $xml->addChild('submitted_at', date('c'));
        $xml->addChild('donor_name', htmlspecialchars($donorName));
        $xml->addChild('donor_email', htmlspecialchars($donorEmail));
        $xml->addChild('park_target', htmlspecialchars($parkTarget));
        $xml->addChild('focus_area', htmlspecialchars($focusLabel));
        $xml->addChild('amount', number_format($finalAmount, 2, '.', ''));
        $xml->addChild('frequency', htmlspecialchars($frequencyLabel));
        $xml->addChild('payment_method', htmlspecialchars($paymentLabel));
        $xml->addChild('tribute_name', htmlspecialchars($tributeName));
        $xml->addChild('message', htmlspecialchars($message));
        $receiptXml = $xml->asXML() ?: '';

        // Reset the form after a successful submission.
        $formData['donor_name'] = '';
        $formData['donor_email'] = '';
        $formData['selected_park'] = 'statewide';
        $formData['donation_focus'] = 'general';
        $formData['amount_choice'] = '50';
        $formData['custom_amount'] = '';
        $formData['frequency'] = 'once';
        $formData['payment_method'] = 'card';
        $formData['tribute_name'] = '';
        $formData['message'] = '';
        $formData['agree_terms'] = '';
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="subpage-hero subpage-hero-donate d-flex align-items-center text-white">
    <div class="subpage-hero-overlay"></div>
    <div class="container position-relative py-5">
        <div class="row">
            <div class="col-lg-8 col-xl-7">
                <span class="hero-kicker">SUPPORT NEW YORK STATE PARKS</span>
                <h1 class="display-5 fw-bold mb-3">Help protect parks, expand programs, and support outdoor access.</h1>
                <p class="lead text-white-50 mb-0">
                    This page is built as a clean public donation experience for your capstone.
                    It uses simple PHP validation now and can be connected to a real payment processor later.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 section-soft-green border-bottom">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="impact-card h-100">
                    <div class="impact-icon"><i class="bi bi-signpost-split"></i></div>
                    <h2 class="h5 fw-bold">Maintain trails</h2>
                    <p class="text-muted mb-0">Support trail repairs, signage, cleanup, and safe visitor access across the state.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="impact-card h-100">
                    <div class="impact-icon"><i class="bi bi-tree"></i></div>
                    <h2 class="h5 fw-bold">Protect habitats</h2>
                    <p class="text-muted mb-0">Help preserve natural spaces, scenic views, and the long-term health of public lands.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="impact-card h-100">
                    <div class="impact-icon"><i class="bi bi-people"></i></div>
                    <h2 class="h5 fw-bold">Fund public programs</h2>
                    <p class="text-muted mb-0">Expand educational programs, family activities, seasonal events, and community outreach.</p>
                </div>
            </div>
        </div>
    </div>
</section>

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
                                <p class="text-muted mb-0">Simple, heavily commented structure so it is easy to explain during your demo.</p>
                            </div>
                            <span class="badge rounded-pill text-bg-light border px-3 py-2">
                                <i class="bi bi-shield-lock me-1"></i> Mock secure flow
                            </span>
                        </div>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <h3 class="h6 fw-bold mb-2">Please fix the following:</h3>
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($successMessage !== ''): ?>
                            <div class="alert alert-success" role="alert">
                                <h3 class="h6 fw-bold mb-2">Donation submitted</h3>
                                <p class="mb-0"><?php echo e($successMessage); ?></p>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="donate.php" id="donateForm" novalidate>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="donor_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control form-control-lg" id="donor_name" name="donor_name" value="<?php echo e($formData['donor_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="donor_email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control form-control-lg" id="donor_email" name="donor_email" value="<?php echo e($formData['donor_email']); ?>" required>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="selected_park" class="form-label">Choose a Park</label>
                                    <select class="form-select form-select-lg" id="selected_park" name="selected_park">
                                        <option value="statewide" <?php echo $formData['selected_park'] === 'statewide' ? 'selected' : ''; ?>>Statewide Parks Fund</option>
                                        <?php foreach ($parkNames as $parkName): ?>
                                            <option value="<?php echo e($parkName); ?>" <?php echo $formData['selected_park'] === $parkName ? 'selected' : ''; ?>><?php echo e($parkName); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="donation_focus" class="form-label">Use of Donation</label>
                                    <select class="form-select form-select-lg" id="donation_focus" name="donation_focus">
                                        <?php foreach ($donationFocusOptions as $value => $label): ?>
                                            <option value="<?php echo e($value); ?>" <?php echo $formData['donation_focus'] === $value ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label d-block mb-3">Choose Amount</label>
                                <div class="row g-3">
                                    <?php foreach ($amountOptions as $amount): ?>
                                        <div class="col-sm-6 col-lg-4">
                                            <label class="amount-choice-card h-100 w-100">
                                                <input class="form-check-input amount-choice-input" type="radio" name="amount_choice" value="<?php echo e((string) $amount); ?>" <?php echo $formData['amount_choice'] === (string) $amount ? 'checked' : ''; ?>>
                                                <span class="amount-choice-content">
                                                    <span class="amount-choice-value">$<?php echo e((string) $amount); ?></span>
                                                    <span class="amount-choice-copy">Suggested gift</span>
                                                </span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="col-sm-6 col-lg-4">
                                        <label class="amount-choice-card h-100 w-100 custom-amount-card">
                                            <input class="form-check-input amount-choice-input" type="radio" name="amount_choice" value="custom" <?php echo $formData['amount_choice'] === 'custom' ? 'checked' : ''; ?>>
                                            <span class="amount-choice-content">
                                                <span class="amount-choice-value">Custom</span>
                                                <span class="amount-choice-copy">Enter your own amount</span>
                                                <input type="number" min="1" step="0.01" class="form-control mt-3" id="custom_amount" name="custom_amount" placeholder="Enter amount" value="<?php echo e($formData['custom_amount']); ?>">
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label d-block mb-3">Donation Frequency</label>
                                    <div class="donate-pill-group">
                                        <?php foreach ($frequencyOptions as $value => $label): ?>
                                            <label class="pill-option">
                                                <input type="radio" name="frequency" value="<?php echo e($value); ?>" <?php echo $formData['frequency'] === $value ? 'checked' : ''; ?>>
                                                <span><?php echo e($label); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block mb-3">Payment Method</label>
                                    <div class="donate-pill-group">
                                        <?php foreach ($paymentOptions as $value => $label): ?>
                                            <label class="pill-option">
                                                <input type="radio" name="payment_method" value="<?php echo e($value); ?>" <?php echo $formData['payment_method'] === $value ? 'checked' : ''; ?>>
                                                <span><?php echo e($label); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="tribute_name" class="form-label">Dedication / Memorial Name <span class="text-muted">(optional)</span></label>
                                    <input type="text" class="form-control" id="tribute_name" name="tribute_name" value="<?php echo e($formData['tribute_name']); ?>" placeholder="Ex: In honor of Jane Doe">
                                </div>
                                <div class="col-md-6">
                                    <label for="message" class="form-label">Message <span class="text-muted">(optional)</span></label>
                                    <input type="text" class="form-control" id="message" name="message" value="<?php echo e($formData['message']); ?>" placeholder="Add a short note">
                                </div>
                            </div>

                            <div class="alert alert-light border rounded-4 small mb-4">
                                <strong>Capstone note:</strong> this public page currently uses a mock donation submission.
                                In the full build, the form can connect to a real payment processor and save safe non-sensitive transaction data.
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" value="1" id="agree_terms" name="agree_terms" <?php echo !empty($formData['agree_terms']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="agree_terms">
                                    I understand this capstone version creates a mock confirmation instead of charging a real payment method.
                                </label>
                            </div>

                            <div class="d-grid d-sm-flex gap-3">
                                <button type="submit" class="btn btn-success btn-lg px-4">
                                    <i class="bi bi-heart-fill me-2"></i>Submit Donation
                                </button>
                                <a href="parks.php" class="btn btn-outline-success btn-lg px-4">Explore Parks First</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

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
                                <span>Best for this phase</span>
                                <strong>Mock workflow</strong>
                            </div>
                            <div class="summary-line">
                                <span>Future upgrade</span>
                                <strong>Live payment API</strong>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm donate-summary-card">
                        <div class="card-body p-4">
                            <p class="section-label mb-2">EXAMPLE IMPACT</p>
                            <div class="impact-list">
                                <div class="impact-list-item"><span class="impact-dollar">$25</span><span>Can support printed trail signage or visitor materials.</span></div>
                                <div class="impact-list-item"><span class="impact-dollar">$50</span><span>Can help fund educational programming materials.</span></div>
                                <div class="impact-list-item"><span class="impact-dollar">$100</span><span>Can support cleanup supplies or small public event needs.</span></div>
                            </div>
                        </div>
                    </div>

                    <?php if ($receiptXml !== ''): ?>
                        <div class="card border-0 shadow-sm donate-summary-card">
                            <div class="card-body p-4">
                                <p class="section-label mb-2">XML RECEIPT PREVIEW</p>
                                <h2 class="h6 fw-bold mb-3">Structured output for later export</h2>
                                <pre class="xml-preview mb-0"><?php echo e($receiptXml); ?></pre>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm donate-summary-card">
                            <div class="card-body p-4">
                                <p class="section-label mb-2">WHAT HAPPENS NEXT</p>
                                <ol class="small text-muted mb-0 ps-3">
                                    <li class="mb-2">Visitor fills out the donation form.</li>
                                    <li class="mb-2">PHP validates the fields on the server.</li>
                                    <li class="mb-2">The page returns a success message and a sample XML receipt.</li>
                                    <li>Later, a real payment gateway can replace the mock step.</li>
                                </ol>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
