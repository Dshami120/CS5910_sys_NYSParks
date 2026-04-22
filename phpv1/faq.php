<?php
require_once __DIR__ . '/includes/config.php';

$pageTitle = 'FAQ | ' . SITE_NAME;
$activePage = 'faq';

/*
 |--------------------------------------------------------------------------
 | FAQ CONTENT
 |--------------------------------------------------------------------------
 | For now, we keep FAQs in a simple PHP array because it is easy to explain
 | in a capstone presentation. Later, these could be moved into a database.
 */
$faqItems = [
    [
        'topic' => 'visiting',
        'question' => 'Do I need an account to browse parks and public events?',
        'answer' => 'No. Guests can browse parks, maps, and public events without logging in. Accounts are only needed later for client actions such as reservations, payments, or managing bookings.'
    ],
    [
        'topic' => 'visiting',
        'question' => 'Can I search for parks by region or activity?',
        'answer' => 'Yes. The public parks page is designed to support filtering by region, park type, and keyword so users can quickly find locations that match their interests.'
    ],
    [
        'topic' => 'events',
        'question' => 'What kinds of events can users view on the site?',
        'answer' => 'The public events page focuses on published public events such as guided hikes, wellness programs, educational activities, and seasonal community events hosted at parks across New York State.'
    ],
    [
        'topic' => 'events',
        'question' => 'Will users be able to request private reservations later?',
        'answer' => 'Yes. In the full capstone system, registered clients will be able to submit reservation requests for private field or facility use. Those portal features are planned after the public pages are finished.'
    ],
    [
        'topic' => 'donations',
        'question' => 'Is the donation page connected to a real payment processor right now?',
        'answer' => 'Not yet. The current donation page uses a mock submission flow with server-side validation so the public workflow can be demonstrated safely during the capstone presentation.'
    ],
    [
        'topic' => 'donations',
        'question' => 'Can donors choose a specific park or support the whole system?',
        'answer' => 'Yes. The donation form supports a statewide fund option and can also target a specific park, depending on the selected option in the form.'
    ],
    [
        'topic' => 'technical',
        'question' => 'What technology stack is being used for this project?',
        'answer' => 'This build uses PHP, MySQL, Bootstrap 5, JavaScript, and reusable include files such as header.php, navbar.php, footer.php, and db.php. It is designed to run locally in XAMPP.'
    ],
    [
        'topic' => 'technical',
        'question' => 'Why are the public pages being built first?',
        'answer' => 'The public-facing pages create the visual and functional foundation of the system. Once those are stable, the team can build client, employee, and admin portals on top of the same reusable structure.'
    ],
    [
        'topic' => 'future',
        'question' => 'What features are planned after the public pages?',
        'answer' => 'The next major phases are the client portal, employee portal, and admin portal, including reservation workflows, approvals, schedules, PTO requests, and operational dashboards.'
    ],
    [
        'topic' => 'future',
        'question' => 'Will the website include an AI assistant?',
        'answer' => 'Yes. The design documents include an AI guide feature to help users retrieve information faster and navigate the site more easily. The public placeholder can be expanded later into a real assistant integration.'
    ],
];

$topicLabels = [
    'all' => 'All Topics',
    'visiting' => 'Visiting Parks',
    'events' => 'Events & Reservations',
    'donations' => 'Donations',
    'technical' => 'Technical Build',
    'future' => 'Future Features',
];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="subpage-hero subpage-hero-faq text-white d-flex align-items-center">
    <div class="subpage-hero-overlay"></div>
    <div class="container position-relative py-5">
        <div class="row">
            <div class="col-lg-8">
                <span class="hero-kicker">HELP & SUPPORT</span>
                <h1 class="display-5 fw-bold mb-3">Frequently asked questions</h1>
                <p class="lead text-white-50 mb-0">
                    This page answers common questions about the public NYS Parks build,
                    including visiting, events, donations, and the capstone system itself.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-light">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-4">
                <div class="faq-info-card h-100">
                    <p class="section-label mb-2">QUICK HELP</p>
                    <h2 class="h4 fw-bold mb-3">Find answers fast</h2>
                    <p class="text-muted mb-0">
                        Use the search box or topic buttons below to narrow the FAQ list.
                        This keeps the page easy to use and easy to explain in your demo.
                    </p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="faq-info-card h-100">
                    <p class="section-label mb-2">PUBLIC FIRST</p>
                    <h2 class="h4 fw-bold mb-3">Focused on public users</h2>
                    <p class="text-muted mb-0">
                        These answers mainly support the current public pages. More portal-specific
                        questions can be added later when the client, employee, and admin areas are built.
                    </p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="faq-info-card h-100">
                    <p class="section-label mb-2">FUTURE READY</p>
                    <h2 class="h4 fw-bold mb-3">Easy to expand</h2>
                    <p class="text-muted mb-0">
                        Right now the questions are stored in PHP arrays. Later, they can be moved to a
                        database table or content management workflow without changing the page layout.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm faq-filter-card sticky-lg-top" style="top: 96px;">
                    <div class="card-body p-4">
                        <p class="section-label mb-2">SEARCH THE FAQ</p>
                        <h2 class="h4 fw-bold mb-3">Need something specific?</h2>
                        <p class="text-muted small mb-4">
                            Type a keyword like <strong>events</strong>, <strong>donate</strong>, or <strong>account</strong>
                            and the list will update automatically.
                        </p>

                        <div class="mb-4">
                            <label for="faqSearch" class="form-label">Search questions</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="faqSearch" placeholder="Search the FAQ...">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-block">Filter by topic</label>
                            <div class="faq-topic-grid">
                                <?php foreach ($topicLabels as $topicValue => $topicLabel): ?>
                                    <button type="button"
                                            class="btn btn-outline-success faq-topic-button <?php echo $topicValue === 'all' ? 'active' : ''; ?>"
                                            data-faq-topic="<?php echo e($topicValue); ?>">
                                        <?php echo e($topicLabel); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <span class="text-muted small" id="faqResultsText">Showing <?php echo count($faqItems); ?> questions</span>
                            <button type="button" class="btn btn-sm btn-link text-success text-decoration-none p-0" id="resetFaqFilters">Reset</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <p class="section-label mb-2">COMMON QUESTIONS</p>
                        <h2 class="fw-bold mb-0">FAQ list</h2>
                    </div>
                    <span class="badge rounded-pill text-bg-light border px-3 py-2">
                        <i class="bi bi-patch-question me-1"></i>
                        <span id="faqCount"><?php echo count($faqItems); ?></span> questions
                    </span>
                </div>

                <div class="accordion faq-accordion" id="faqAccordion">
                    <?php foreach ($faqItems as $index => $item): ?>
                        <?php
                        $question = $item['question'];
                        $answer = $item['answer'];
                        $topic = $item['topic'];
                        $topicLabel = $topicLabels[$topic] ?? 'General';
                        $itemSearch = strtolower($question . ' ' . $answer . ' ' . $topicLabel);
                        ?>
                        <div class="accordion-item faq-item border-0 shadow-sm mb-3"
                             data-topic="<?php echo e($topic); ?>"
                             data-search="<?php echo e($itemSearch); ?>">
                            <h3 class="accordion-header" id="faqHeading<?php echo $index; ?>">
                                <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#faqCollapse<?php echo $index; ?>"
                                        aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                        aria-controls="faqCollapse<?php echo $index; ?>">
                                    <span class="d-flex flex-column text-start">
                                        <span class="fw-semibold"><?php echo e($question); ?></span>
                                        <small class="text-muted mt-1"><?php echo e($topicLabel); ?></small>
                                    </span>
                                </button>
                            </h3>
                            <div id="faqCollapse<?php echo $index; ?>"
                                 class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                 aria-labelledby="faqHeading<?php echo $index; ?>"
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p class="mb-0 text-muted"><?php echo e($answer); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="noFaqMessage" class="alert alert-light border d-none mt-4">
                    <h3 class="h6 fw-bold mb-1">No FAQ matches your filters</h3>
                    <p class="mb-0 text-muted">Try a different keyword or reset the topic filter.</p>
                </div>

                <div class="card border-0 shadow-sm faq-contact-card mt-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row g-4 align-items-center">
                            <div class="col-md-8">
                                <p class="section-label mb-2">STILL NEED HELP?</p>
                                <h2 class="h4 fw-bold mb-2">Can’t find your answer?</h2>
                                <p class="text-muted mb-0">
                                    This starter build can later connect FAQ support with the contact page,
                                    AI guide, or a support form. For now, this section gives the page a clean
                                    public call-to-action.
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <a href="donate.php" class="btn btn-success px-4 me-2 mb-2 mb-md-0">Visit Donate</a>
                                <a href="about.php" class="btn btn-outline-success px-4">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
