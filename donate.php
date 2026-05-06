<?php
require 'bootstrap.php';
$user = $currentUser;
$donationFlow = $_SERVER['REQUEST_METHOD'] === 'POST' || get('action') === 'donate';
if ($donationFlow && (!$user || $user['role'] !== 'client')) {
    flash_set('error', 'Please log in with a client account before donating.');
    redirect('login.php?next=' . urlencode('donate.php?action=donate'));
}
$parks = park_options($db);
$parkNames = array_map(fn($p) => $p['name'], $parks);
sort($parkNames);
$donationFocusOptions = ['general'=>'Overall Parks Fund','trails'=>'Trail Maintenance','habitat'=>'Habitat & Conservation','programs'=>'Programs & Education','events'=>'Community Events','accessibility'=>'Accessibility Improvements'];
$amountOptions = [15,25,50,100,250];
$frequencyOptions = ['once'=>'One-time','monthly'=>'Monthly','yearly'=>'Yearly'];
$paymentOptions = ['card'=>'Credit / Debit Card','in_person'=>'In Person'];
$formData = [
    'donor_name'=>$user ? full_name($user) : '',
    'donor_email'=>$user['email'] ?? '',
    'selected_park'=>'statewide','donation_focus'=>'general','amount_choice'=>'50','custom_amount'=>'',
    'frequency'=>'once','payment_method'=>'card','tribute_name'=>'','message'=>'',
    'card_number'=>'','exp_month'=>'','exp_year'=>'','cvv'=>'','agree_terms'=>''
];
foreach ($formData as $k=>$v) { if (isset($_POST[$k])) $formData[$k] = trim((string)$_POST[$k]); }
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
    if ($donorName === '') $errors[] = 'Please enter your full name.';
    if (!valid_email($donorEmail)) $errors[] = 'Please enter a valid email address.';
    if (!isset($donationFocusOptions[$donationFocus])) $errors[] = 'Please choose a valid donation focus.';
    if (!isset($frequencyOptions[$frequency])) $errors[] = 'Please choose a valid donation frequency.';
    if (!isset($paymentOptions[$paymentMethod])) $errors[] = 'Please choose a valid payment method.';
    if ($selectedPark !== 'statewide' && !in_array($selectedPark, $parkNames, true)) $errors[] = 'Please choose a valid park option.';
    $finalAmount = 0.0;
    if ($amountChoice === 'custom') {
        if ($customAmount === '' || !is_numeric($customAmount) || (float)$customAmount <= 0) $errors[] = 'Please enter a custom amount greater than 0.';
        else $finalAmount = round((float)$customAmount, 2);
    } else {
        if (!in_array((int)$amountChoice, $amountOptions, true)) $errors[] = 'Please choose a valid donation amount.';
        else $finalAmount = (float)$amountChoice;
    }
    if (empty($formData['agree_terms'])) $errors[] = 'Please confirm the donation acknowledgement checkbox.';
    $cardNum = null; $month = null; $year = null; $cvv = null;
    $paymentStatus = 'pending';
    if ($paymentMethod === 'card') {
        $cardError = validate_card($formData['card_number'], $formData['exp_month'], $formData['exp_year'], $formData['cvv']);
        if ($cardError) $errors[] = $cardError;
        $cardNum = number_only($formData['card_number']) ?: null;
        $cvv = number_only($formData['cvv']) ?: null;
        $month = $formData['exp_month'] !== '' ? (int)$formData['exp_month'] : null;
        $year = $formData['exp_year'] !== '' ? (int)$formData['exp_year'] : null;
        $paymentStatus = 'completed';
    }
    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect('donate.php?action=donate');
    }
    $ref = 'DON-' . date('YmdHis') . '-' . random_int(1000,9999);
    $db->prepare("INSERT INTO payments (user_id, booking_id, payment_type, payer_name, payer_email, amount, payment_method, card_num, exp_month, exp_year, cvv, payment_status, transaction_ref) VALUES (?,?, 'donation',?,?,?,?,?,?,?,?, ?,?)")
       ->execute([(int)$user['id'], null, $donorName, $donorEmail, $finalAmount, $paymentMethod, $cardNum, $month, $year, $cvv, $paymentStatus, $ref]);
    flash_set('success', $paymentMethod === 'card' ? 'Thank you! Your mock card donation was recorded.' : 'Thank you! Your in-person donation pledge was recorded as pending.');
    redirect('donate.php?action=donate');
}
$donateUrl = $currentUser && $currentUser['role'] === 'client' ? 'donate.php?action=donate' : 'login.php?next=' . urlencode('donate.php?action=donate');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Donate</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="css/styles.css" />
</head>


<body data-page="donate">

<header class="site-header">
    <nav class="container py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <section class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 gap-lg-4">
            <a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a>
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
                <li><a href="account.php" class="nav-link-custom"><i class="bi bi-person-circle"></i>Account</a></li>
                <li><a href="logout.php" class="btn btn-dark nav-pill-btn" data-page-link="logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="login.php" class="nav-link-custom" data-page-link="login">Log In</a></li>
                <li><a href="register.php" class="btn btn-dark nav-pill-btn" data-page-link="register">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<section class="subpage-hero subpage-hero-donate d-flex align-items-center text-white"><div class="subpage-hero-overlay"></div><div class="container position-relative py-5"><div class="row"><div class="col-lg-8 col-xl-7"><span class="hero-kicker">SUPPORT NEW YORK STATE PARKS</span><h1 class="display-5 fw-bold mb-3">Help protect parks, expand programs, and support outdoor access.</h1><p class="lead text-white-50 mb-0">Donation information is public. The mock payment form opens after a client login.</p></div></div></div></section>
<section class="py-5 section-soft-green border-bottom"><div class="container"><div class="row g-4"><div class="col-md-4"><div class="impact-card h-100"><div class="impact-icon"><i class="bi bi-signpost-split"></i></div><h2 class="h5 fw-bold">Maintain trails</h2><p class="text-muted mb-0">Support trail repairs, signage, cleanup, and safe visitor access across the state.</p></div></div><div class="col-md-4"><div class="impact-card h-100"><div class="impact-icon"><i class="bi bi-tree"></i></div><h2 class="h5 fw-bold">Protect habitats</h2><p class="text-muted mb-0">Help preserve natural spaces, scenic views, and the long-term health of public lands.</p></div></div><div class="col-md-4"><div class="impact-card h-100"><div class="impact-icon"><i class="bi bi-people"></i></div><h2 class="h5 fw-bold">Fund public programs</h2><p class="text-muted mb-0">Expand educational programs, family activities, seasonal events, and community outreach.</p></div></div></div></div></section>
<?php if ($donationFlow && $user && $user['role'] === 'client'): ?>
<section class="py-5"><div class="container"><div class="row g-4 align-items-start"><div class="col-lg-7"><div class="card border-0 shadow-sm donate-form-card"><div class="card-body p-4 p-lg-5"><div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4"><div><p class="section-label mb-2">DONATION FORM</p><h2 class="fw-bold mb-1">Make a contribution</h2><p class="text-muted mb-0">Logged in as <?= e(full_name($user)) ?>.</p></div><span class="badge rounded-pill text-bg-light border px-3 py-2"><i class="bi bi-shield-lock me-1"></i> Mock secure flow</span></div><?php if ($flash): ?><div class="alert alert-<?= $flash['type']==='error'?'danger':'success' ?>" role="alert"><?= e($flash['message']) ?></div><?php endif; ?><form method="post" action="donate.php?action=donate" id="donateForm" novalidate><div class="row g-3 mb-4"><div class="col-md-6"><label for="donor_name" class="form-label">Full Name</label><input type="text" class="form-control form-control-lg" id="donor_name" name="donor_name" value="<?= e($formData['donor_name']) ?>" required></div><div class="col-md-6"><label for="donor_email" class="form-label">Email Address</label><input type="email" class="form-control form-control-lg" id="donor_email" name="donor_email" value="<?= e($formData['donor_email']) ?>" required></div></div><div class="row g-3 mb-4"><div class="col-md-6"><label for="selected_park" class="form-label">Choose a Park</label><select class="form-select form-select-lg" id="selected_park" name="selected_park"><option value="statewide" <?= $formData['selected_park']==='statewide'?'selected':'' ?>>Statewide Parks Fund</option><?php foreach($parkNames as $parkName): ?><option value="<?= e($parkName) ?>" <?= $formData['selected_park']===$parkName?'selected':'' ?>><?= e($parkName) ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label for="donation_focus" class="form-label">Use of Donation</label><select class="form-select form-select-lg" id="donation_focus" name="donation_focus"><?php foreach($donationFocusOptions as $value=>$label): ?><option value="<?= e($value) ?>" <?= $formData['donation_focus']===$value?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></div></div><div class="mb-4"><label class="form-label d-block mb-3">Choose Amount</label><div class="row g-3"><?php foreach($amountOptions as $amount): ?><div class="col-sm-6 col-lg-4"><label class="amount-choice-card h-100 w-100"><input class="form-check-input amount-choice-input" type="radio" name="amount_choice" value="<?= e((string)$amount) ?>" <?= $formData['amount_choice']===(string)$amount?'checked':'' ?>><span class="amount-choice-content"><span class="amount-choice-value">$<?= e((string)$amount) ?></span><span class="amount-choice-copy">Suggested gift</span></span></label></div><?php endforeach; ?><div class="col-sm-6 col-lg-4"><label class="amount-choice-card h-100 w-100 custom-amount-card"><input class="form-check-input amount-choice-input" type="radio" name="amount_choice" value="custom" <?= $formData['amount_choice']==='custom'?'checked':'' ?>><span class="amount-choice-content"><span class="amount-choice-value">Custom</span><span class="amount-choice-copy">Enter your own amount</span><input type="number" min="1" step="0.01" class="form-control mt-3" id="custom_amount" name="custom_amount" placeholder="Enter amount" value="<?= e($formData['custom_amount']) ?>"></span></label></div></div></div><div class="row g-4 mb-4"><div class="col-md-6"><label class="form-label d-block mb-3">Donation Frequency</label><div class="donate-pill-group"><?php foreach($frequencyOptions as $value=>$label): ?><label class="pill-option"><input type="radio" name="frequency" value="<?= e($value) ?>" <?= $formData['frequency']===$value?'checked':'' ?>><span><?= e($label) ?></span></label><?php endforeach; ?></div></div><div class="col-md-6"><label class="form-label d-block mb-3">Payment Method</label><div class="donate-pill-group"><?php foreach($paymentOptions as $value=>$label): ?><label class="pill-option"><input type="radio" name="payment_method" value="<?= e($value) ?>" <?= $formData['payment_method']===$value?'checked':'' ?>><span><?= e($label) ?></span></label><?php endforeach; ?></div></div></div><div id="cardDetailsBlock" class="row g-3 mb-4"><div class="col-md-6"><label for="card_number" class="form-label">Card Number</label><input type="text" class="form-control" id="card_number" name="card_number" maxlength="19" value="<?= e($formData['card_number']) ?>" placeholder="Card number"></div><div class="col-md-3"><label for="exp_month" class="form-label">Exp. Month</label><input type="number" min="1" max="12" class="form-control" id="exp_month" name="exp_month" value="<?= e($formData['exp_month']) ?>" placeholder="MM"></div><div class="col-md-3"><label for="exp_year" class="form-label">Exp. Year</label><input type="number" min="<?= date('Y') ?>" class="form-control" id="exp_year" name="exp_year" value="<?= e($formData['exp_year']) ?>" placeholder="YYYY"></div><div class="col-md-3"><label for="cvv" class="form-label">CVV</label><input type="text" maxlength="4" class="form-control" id="cvv" name="cvv" value="<?= e($formData['cvv']) ?>" placeholder="CVV"></div></div><div class="row g-3 mb-4"><div class="col-md-6"><label for="tribute_name" class="form-label">Dedication / Memorial Name <span class="text-muted">(optional)</span></label><input type="text" class="form-control" id="tribute_name" name="tribute_name" value="<?= e($formData['tribute_name']) ?>" placeholder="Ex: In honor of Jane Doe"></div><div class="col-md-6"><label for="message" class="form-label">Message <span class="text-muted">(optional)</span></label><input type="text" class="form-control" id="message" name="message" value="<?= e($formData['message']) ?>" placeholder="Add a short note"></div></div><div class="alert alert-light border rounded-4 small mb-4"><strong>Capstone note:</strong> this page uses mock payment handling. Card numbers and CVV are validated only by digit count/range for the capstone database.</div><div class="form-check mb-4"><input class="form-check-input" type="checkbox" value="1" id="agree_terms" name="agree_terms" <?= !empty($formData['agree_terms'])?'checked':'' ?>><label class="form-check-label" for="agree_terms">I understand this capstone version creates a mock confirmation instead of charging a real payment method.</label></div><div class="d-grid d-sm-flex gap-3"><button type="submit" class="btn btn-success btn-lg px-4"><i class="bi bi-heart-fill me-2"></i>Submit Donation</button><a href="parks.php" class="btn btn-outline-success btn-lg px-4">Explore Parks First</a></div></form></div></div></div><div class="col-lg-5"><div class="donate-sidebar-stack"><div class="card border-0 shadow-sm donate-summary-card"><div class="card-body p-4"><p class="section-label mb-2">GIVING SNAPSHOT</p><h2 class="h4 fw-bold mb-3">Why donations matter</h2><div class="summary-line"><span>Supports public access</span><strong>Statewide</strong></div><div class="summary-line"><span>Card donations</span><strong>Completed</strong></div><div class="summary-line"><span>In-person donations</span><strong>Pending</strong></div></div></div><div class="card border-0 shadow-sm donate-summary-card"><div class="card-body p-4"><p class="section-label mb-2">WHAT HAPPENS NEXT</p><ol class="small text-muted mb-0 ps-3"><li class="mb-2">PHP validates the fields on the server.</li><li class="mb-2">Card payments are marked completed.</li><li class="mb-2">In-person payments are recorded as pending.</li><li>The payment record is stored in the shared payments table.</li></ol></div></div></div></div></div></div></section>
<?php else: ?>
<section class="py-5"><div class="container"><div class="row justify-content-center"><div class="col-lg-8"><div class="card border-0 shadow-sm donate-form-card"><div class="card-body p-4 p-lg-5 text-center"><?php if ($flash): ?><div class="alert alert-<?= $flash['type']==='error'?'danger':'success' ?>" role="alert"><?= e($flash['message']) ?></div><?php endif; ?><p class="section-label mb-2">DONATION FORM</p><h2 class="fw-bold mb-3">Ready to make a contribution?</h2><p class="text-muted mb-4">Anyone can view donation information. Click Donate Now to log in as a client and open the mock payment form.</p><a class="btn btn-success btn-lg rounded-pill px-5" href="<?= e($donateUrl) ?>"><i class="bi bi-heart-fill me-2"></i>Donate Now</a></div></div></div></div></div></section>
<?php endif; ?>
<footer class="footer-shell py-5 mt-5"><section class="container"><section class="footer-five"><article class="footer-block footer-brand"><a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2 mb-3"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a><p class="text-muted mb-0">A modern gateway to New York State parks, events, maps, news, and role-based operations.</p></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Explore</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="parks.php" class="text-muted text-decoration-none">Parks</a></li><li class="mb-2"><a href="events.php" class="text-muted text-decoration-none">Events</a></li><li class="mb-2"><a href="map.php" class="text-muted text-decoration-none">Map</a></li><li class="mb-2"><a href="ai.php" class="text-muted text-decoration-none">AI</a></li><li class="mb-2"><a href="news.php" class="text-muted text-decoration-none">News</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Account</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="about.php" class="text-muted text-decoration-none">About</a></li><li class="mb-2"><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li><li class="mb-2"><a href="donate.php" class="text-muted text-decoration-none">Donate</a></li><li class="mb-2"><a href="login.php" class="text-muted text-decoration-none">Log In</a></li><li class="mb-2"><a href="register.php" class="text-muted text-decoration-none">Register</a></li><li class="mb-2"><a href="account.php" class="text-muted text-decoration-none">Account</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Portals</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="client-dashboard.php" class="text-muted text-decoration-none">Client Dashboard</a></li><li class="mb-2"><a href="admin-dashboard.php" class="text-muted text-decoration-none">Admin Dashboard</a></li><li class="mb-2"><a href="employee-dashboard.php" class="text-muted text-decoration-none">Employee Dashboard</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Contact</h2><p class="text-muted mb-2">info@nysparks.gov</p><p class="text-muted mb-2">(555) 123-4567</p><p class="text-muted mb-0">Albany, New York</p></article></section></section></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
<script>
document.querySelectorAll('input[name="payment_method"]').forEach(input => {
  input.addEventListener('change', () => {
    const block = document.getElementById('cardDetailsBlock');
    if (block) block.style.display = document.querySelector('input[name="payment_method"]:checked')?.value === 'card' ? '' : 'none';
  });
});
const checkedPayment = document.querySelector('input[name="payment_method"]:checked');
if (checkedPayment) checkedPayment.dispatchEvent(new Event('change'));
</script>
</body></html>
