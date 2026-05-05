<?php
require 'bootstrap.php';
$faqItems = [
    ['topic'=>'visiting','question'=>'Do I need an account to browse parks and public events?','answer'=>'No. Guests can browse parks, maps, news, and public events without logging in. Accounts are only needed for actions such as reservations, saved activity, or managing requests.'],
    ['topic'=>'visiting','question'=>'Can I search for parks by region or activity?','answer'=>'Yes. The public parks page is designed to support filtering by region, park type, and keyword so visitors can quickly find locations that match their interests.'],
    ['topic'=>'visiting','question'=>'Where can I find park hours and facility details?','answer'=>'Park hours, amenities, fees, and seasonal information can vary by location. Visitors should check the specific park page or contact the park directly for the most accurate details before traveling.'],
    ['topic'=>'visiting','question'=>'Are pets allowed in New York State Parks?','answer'=>'Many parks allow pets in designated areas, but rules can vary by site. Where pets are allowed, they generally must be supervised and kept on a leash no longer than six feet. Some areas such as buildings, guarded beaches, pools, playgrounds, or boardwalks may restrict pets.'],
    ['topic'=>'visiting','question'=>'Do all parks charge an entrance fee?','answer'=>'Not every park charges the same fee. Many New York State Parks collect a vehicle-use fee during certain seasons or hours, and fees may vary by location. Visitors should check the park page before visiting.'],

    ['topic'=>'events','question'=>'What kinds of events can users view on the site?','answer'=>'The public events page focuses on published public events such as guided hikes, wellness programs, educational activities, family programs, and seasonal community events hosted at parks across New York State.'],
    ['topic'=>'events','question'=>'Do I need an account to view events?','answer'=>'No. Public events can be viewed without an account. If a future event requires registration, the site can direct users to the proper request or reservation workflow.'],
    ['topic'=>'events','question'=>'How do I reserve a pavilion or shelter?','answer'=>'Pavilion and shelter reservations for New York State Parks are typically handled through ReserveAmerica or by contacting the park directly. This site can help users discover parks and events, while official reservation services handle final booking.'],
    ['topic'=>'events','question'=>'Can event details change after they are posted?','answer'=>'Yes. Weather, staffing, trail conditions, or park operations can affect event details. Visitors should check for updates before attending.'],
    ['topic'=>'events','question'=>'Will users be able to request private reservations later?','answer'=>'Yes. Registered clients can submit reservation requests and admins can review them from the booking approval queue.'],

    ['topic'=>'camping','question'=>'How are camping reservations handled?','answer'=>'Camping reservations for New York State Parks are generally made through ReserveAmerica. This project can connect visitors to park information, while official reservation systems handle campsite booking.'],
    ['topic'=>'camping','question'=>'How far in advance can visitors book camping?','answer'=>'New York State Parks camping reservations are generally limited by a reservation window. Visitors should check the official reservation system for current availability and booking rules.'],
    ['topic'=>'camping','question'=>'Can I see campsite amenities before booking?','answer'=>'Yes. Official reservation listings usually include details such as amenities, photos, hookups, season dates, and site-specific information when available.'],
    ['topic'=>'camping','question'=>'Who should I contact about a specific campsite or amenity?','answer'=>'For detailed questions about a specific campground, campsite, cabin, or amenity, visitors should contact the park or campground directly.'],

    ['topic'=>'passes','question'=>'What is the Empire Pass?','answer'=>'The Empire Pass is a New York State Parks pass used for vehicle entry where vehicle-use fees are charged. Pass rules and accepted uses can vary by pass type, so visitors should check official pass information.'],
    ['topic'=>'passes','question'=>'Can an Empire Pass be used for every permit or activity?','answer'=>'No. Some permits, such as certain fishing or 4x4 access permits, are separate from the Empire Pass and may be limited to specific regions or activities.'],
    ['topic'=>'passes','question'=>'What is the Access Pass?','answer'=>'The Access Pass is a New York State Parks program for eligible New York State residents. Eligibility and required documents are handled through the official Access Pass application process.'],
    ['topic'=>'passes','question'=>'Do visitors need ID for certain passes?','answer'=>'Some pass programs require proof of eligibility or identity. Visitors should review the official pass requirements before applying or using a pass.'],

    ['topic'=>'news','question'=>'What kind of updates appear on the News page?','answer'=>'The News page is intended for public announcements, park notices, seasonal reminders, safety updates, program highlights, conservation stories, and statewide updates.'],
    ['topic'=>'news','question'=>'Are news posts searchable?','answer'=>'Yes. The News page supports keyword search and filters by topic and region so visitors can quickly find relevant updates.'],
    ['topic'=>'news','question'=>'Where does the news content come from?','answer'=>'In this build, published news items are loaded from the news table in the database, which makes the page easier to maintain than hardcoded articles.'],

    ['topic'=>'donations','question'=>'Is the donation page connected to a real payment processor right now?','answer'=>'Not yet. The current donation page uses mock validation and stores safe payment metadata only so the workflow can be demonstrated during the capstone.'],
    ['topic'=>'donations','question'=>'Can donors choose a specific park or support the whole system?','answer'=>'Yes. The donation form supports a statewide fund option and can also target a specific park.'],
    ['topic'=>'donations','question'=>'What types of park needs can donations support?','answer'=>'Donations can be presented as support for public programs, conservation, education, park improvements, maintenance, and community access initiatives.'],

    ['topic'=>'technical','question'=>'What technology stack is being used for this project?','answer'=>'This build uses PHP, MySQL, Bootstrap 5, JavaScript, and a flat XAMPP-friendly file structure.'],
    ['topic'=>'technical','question'=>'Why was the site flattened into root PHP files?','answer'=>'That structure makes the project easier to run in XAMPP, easier to submit for capstone review, and simpler to explain.'],
    ['topic'=>'technical','question'=>'Are FAQs currently stored in the database?','answer'=>'No. The current FAQ page stores questions in a PHP array. This keeps the page simple for the current build, but the same layout could later be connected to a database table.'],
    ['topic'=>'technical','question'=>'Which pages currently use database content?','answer'=>'The News page already loads published articles from the database. Other pages can be connected to database tables depending on the final project scope.'],

    ['topic'=>'future','question'=>'What features are planned after the public pages?','answer'=>'The client, employee, and admin portal workflows are already present and can continue to be hardened and polished after submission.'],
    ['topic'=>'future','question'=>'Will the website include an AI assistant?','answer'=>'Yes. The AI Guide page is already part of the public experience and can later connect to a live AI backend.'],
    ['topic'=>'future','question'=>'Could FAQs be managed from an admin page later?','answer'=>'Yes. A future admin FAQ manager could allow staff to add, edit, publish, or retire questions without editing PHP code directly.'],
];

$topicLabels = [
    'all'=>'All Topics',
    'visiting'=>'Visiting Parks',
    'events'=>'Events & Reservations',
    'camping'=>'Camping',
    'passes'=>'Passes & Fees',
    'news'=>'News & Updates',
    'donations'=>'Donations',
    'technical'=>'Technical Build',
    'future'=>'Future Features'
];
$topicLabels = ['all'=>'All Topics','visiting'=>'Visiting Parks','events'=>'Events & Reservations','donations'=>'Donations','technical'=>'Technical Build','future'=>'Future Features'];
?>
<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NYS Parks - FAQ</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="css/styles.css" />
</head>

<body data-page="faq">
<header class="site-header">
    <nav class="container py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <section class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 gap-lg-4">
            <a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a><ul class="list-unstyled d-flex flex-wrap gap-3 gap-lg-4 m-0 align-items-center"><li><a href="parks.php" class="nav-link-custom" data-page-link="parks"><i class="bi bi-tree"></i>Parks</a></li><li><a href="events.php" class="nav-link-custom" data-page-link="events"><i class="bi bi-calendar-event"></i>Events</a></li><li><a href="map.php" class="nav-link-custom" data-page-link="map"><i class="bi bi-geo-alt"></i>Map</a></li><li><a href="ai.php" class="nav-link-custom" data-page-link="ai"><i class="bi bi-stars"></i>AI</a></li><li><a href="news.php" class="nav-link-custom" data-page-link="news"><i class="bi bi-newspaper"></i>News</a></li><li><a href="about.php" class="nav-link-custom" data-page-link="about"><i class="bi bi-info-circle"></i>About Us</a></li><li><a href="faq.php" class="nav-link-custom" data-page-link="faq"><i class="bi bi-question-circle"></i>FAQ</a></li><li><a href="donate.php" class="nav-link-custom" data-page-link="donate"><i class="bi bi-heart"></i>Donate</a></li></ul></section><ul class="list-unstyled d-flex flex-wrap gap-2 gap-lg-3 m-0 align-items-center"><?php if ($currentUser): ?><li><a href="account.php" class="nav-link-custom"><i class="bi bi-person-circle"></i>Account</a></li><li><a href="logout.php" class="btn btn-dark nav-pill-btn">Logout</a></li><?php else: ?><li><a href="login.php" class="nav-link-custom" data-page-link="login">Log In</a></li><li><a href="register.php" class="btn btn-dark nav-pill-btn" data-page-link="register">Register</a></li><?php endif; ?></ul></nav></header>
<section class="subpage-hero subpage-hero-faq text-white d-flex align-items-center">
    <div class="subpage-hero-overlay"></div>
        <div class="container position-relative py-5">
            <div class="row">
                <div class="col-lg-8">
                    <span class="hero-kicker">HELP & SUPPORT</span>
                    <h1 class="display-5 fw-bold mb-3">Frequently asked questions</h1>
                    <p class="lead text-white-50 mb-0">This page answers common questions about the public NYS Parks build, including visiting, events, donations, and the capstone system itself.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-light">
    <div class="container">
        <div class="row g-4 align-items-stretch">

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <p class="section-label mb-2">QUICK HELP</p>
                        <h2 class="h4 fw-bold mb-3">Find answers fast</h2>
                        <p class="text-muted mb-0">Use the search box or topic buttons below to narrow the FAQ list. This keeps the page easy to use and easy to explain in your demo.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <p class="section-label mb-2">PUBLIC FIRST</p>
                        <h2 class="h4 fw-bold mb-3">Focused on public users</h2>
                        <p class="text-muted mb-0">These answers mainly support the current public pages while still reinforcing how the role-based portals fit into the site.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <p class="section-label mb-2">FUTURE READY</p>
                        <h2 class="h4 fw-bold mb-3">Easy to expand</h2>
                        <p class="text-muted mb-0">Right now the questions are stored in a PHP array. Later, they can move into a database table without changing the page layout.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="py-5"><div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm faq-filter-card sticky-lg-top" style="top: 96px;">
                    <div class="card-body p-4">
                        <p class="section-label mb-2">SEARCH THE FAQ</p>
                        <h2 class="h4 fw-bold mb-3">Need something specific?</h2>
                        <p class="text-muted small mb-4">Type a keyword like <strong>events</strong>, <strong>donate</strong>, or <strong>account</strong> and the list will update automatically.</p>
                        <div class="mb-4">
                            <label for="faqSearch" class="form-label">Search questions</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" id="faqSearch" placeholder="Search the FAQ...">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label d-block">Filter by topic</label>
                            <div class="faq-topic-grid"><?php foreach($topicLabels as $topicValue=>$topicLabel): ?>
                                    <button type="button" class="btn btn-outline-success faq-topic-button <?= $topicValue==='all'?'active':'' ?>" data-faq-topic="<?= e($topicValue) ?>"><?= e($topicLabel) ?>
                                    </button><?php endforeach; ?>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <span class="text-muted small" id="faqResultsText">Showing <?= count($faqItems) ?> questions</span>
                            <button type="button" class="btn btn-sm btn-link text-success text-decoration-none p-0" id="resetFaqFilters">Reset</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8"><div class="card border-0 shadow-sm faq-hero-card mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <p class="section-label mb-2">SUPPORT CENTER</p>
                        <h2 class="fw-bold mb-2">Clear answers for visitors and reviewers</h2>
                        <p class="text-muted mb-0">This merged FAQ keeps the stronger search-and-filter experience from the alternate version while fitting the current site structure cleanly.</p>
                    </div>
                </div>
                <div class="accordion faq-accordion" id="faqAccordion"><?php foreach($faqItems as $i=>$item): $collapse='faqItem'.$i; $search=strtolower($item['question'].' '.$item['answer'].' '.$item['topic']); ?>
                    <div class="accordion-item border-0 mb-3 soft-card overflow-hidden faq-item" data-faq-topic="<?= e($item['topic']) ?>" data-search="<?= e($search) ?>">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?= $i===0?'':'collapsed' ?> fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#<?= e($collapse) ?>"><?= e($item['question']) ?>
                            </button>
                        </h2>
                        <div id="<?= e($collapse) ?>" class="accordion-collapse collapse <?= $i===0?'show':'' ?>" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted"><?= e($item['answer']) ?>
                            </div>
                        </div>
                        </div><?php endforeach; ?>
                </div>
                <div id="noFaqMessage" class="alert alert-light border d-none mt-4">
                    <h3 class="h6 fw-bold mb-1">No FAQ items match your filters</h3>
                    <p class="mb-0 text-muted">Try a different keyword or reset the selected topic.</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- keep exploring section -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm news-cta-card mt-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-md-7">
                        <p class="section-label mb-2">KEEP EXPLORING</p>
                        <h2 class="h4 fw-bold mb-2">Turn updates into action</h2>
                        <p class="text-muted mb-0">After reading statewide updates, public users can jump into the: <br> park directory, browse events, or support the system through the donate flow.</p>
                    </div>
                    <div class="col-md-5 text-md-end ">
                        <a href="parks.php" class="btn btn-success px-4 me-2 mb-2 mb-md-0">Go to Parks</a>
                        <a href="events.php" class="btn btn-success px-4 me-2 mb-2 mb-md-0">Go to Events</a>
                        <a href="news.php" class="btn btn-success px-4 me-2 mb-2 mb-md-0">Go to News</a>
                        <a href="donate.php" class="btn btn-outline-success px-5 ">Donate</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- shared footer -->
<footer class="footer-shell py-5 mt-5"><section class="container">
        <section class="footer-five">

            <article class="footer-block footer-brand">
                <a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2 mb-3">
                    <span class="brand-badge">NY</span>
                    <span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span>
                </a>
                <p class="text-muted mb-0">A modern gateway to New York State parks, events, maps, news, and role-based operations.</p>
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
                <ul class="list-unstyled m-0"><li class="mb-2"><a href="about.php" class="text-muted text-decoration-none">About</a>
                    </li>
                    <li class="mb-2"><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li>
                    <li class="mb-2"><a href="donate.php" class="text-muted text-decoration-none">Donate</a></li>
                    <li class="mb-2"><a href="login.php" class="text-muted text-decoration-none">Log In</a></li>
                    <li class="mb-2"><a href="register.php" class="text-muted text-decoration-none">Register</a></li>
                    <li class="mb-2"><a href="account.php" class="text-muted text-decoration-none">Account</a></li>
                </ul>
            </article>

            <article class="footer-block">
                <h2 class="h6 fw-bold mb-3">Portals</h2>
                <ul class="list-unstyled m-0"><li class="mb-2"><a href="client-dashboard.php" class="text-muted text-decoration-none">Client Dashboard</a></li>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="js/app.js"></script></body></html>
