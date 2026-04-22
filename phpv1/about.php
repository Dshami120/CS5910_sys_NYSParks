<?php
$pageTitle = 'About | ' . SITE_NAME;
$activePage = 'about';

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!--
    ABOUT PAGE
    --------------------------------------------------------------------------
    This page is still public-facing, so it focuses on the "why" behind the
    website and the overall mission of NYS Parks & Recreation.

    We are keeping the content simple, clean, and easy to explain during a
    capstone presentation.
-->
<section class="subpage-hero subpage-hero-about text-white d-flex align-items-center">
    <div class="subpage-hero-overlay"></div>
    <div class="container position-relative py-5">
        <div class="row">
            <div class="col-lg-8">
                <span class="hero-kicker">ABOUT THE PLATFORM</span>
                <h1 class="display-5 fw-bold mb-3">A cleaner, smarter digital front door for New York State Parks</h1>
                <p class="lead text-white-50 mb-0">
                    This capstone project reimagines the public NYS Parks experience with
                    better navigation, clearer information, stronger event discovery,
                    and room for future client, employee, and admin tools.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 border-bottom bg-light">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-4">
                <div class="about-highlight-card h-100">
                    <p class="section-label mb-2">MISSION</p>
                    <h2 class="h3 fw-bold mb-3">Make park information easier to access</h2>
                    <p class="text-muted mb-0">
                        The goal of this redesign is to help visitors quickly find parks,
                        browse events, explore the statewide map, and plan outdoor trips
                        without digging through outdated layouts.
                    </p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="about-highlight-card h-100">
                    <p class="section-label mb-2">VISION</p>
                    <h2 class="h3 fw-bold mb-3">One unified statewide experience</h2>
                    <p class="text-muted mb-0">
                        Instead of disconnected or confusing pages, the website is designed
                        as a single system where public visitors and staff-facing tools can
                        eventually live under one consistent design.
                    </p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="about-highlight-card h-100">
                    <p class="section-label mb-2">GOAL</p>
                    <h2 class="h3 fw-bold mb-3">Balance beauty with usability</h2>
                    <p class="text-muted mb-0">
                        The visual style should feel modern and scenic like a parks brand,
                        while still being simple enough for users to search, click, and
                        complete tasks with very little friction.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <p class="section-label mb-2">WHY THIS REDESIGN MATTERS</p>
                <h2 class="fw-bold mb-3">What the new version improves</h2>
                <p class="text-muted mb-4">
                    The current public experience can feel dated and harder to navigate than
                    it needs to be. This redesign focuses on discoverability, readability,
                    modern page structure, and future-ready functionality.
                </p>

                <div class="about-check-list">
                    <div class="about-check-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Cleaner homepage with clearer calls to action</span>
                    </div>
                    <div class="about-check-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Better park browsing, map browsing, and event browsing</span>
                    </div>
                    <div class="about-check-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Reusable PHP structure instead of repeated static HTML</span>
                    </div>
                    <div class="about-check-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>MySQL-ready foundation for future dynamic features</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="about-visual-card shadow-sm">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="about-mini-stat">
                                <span class="about-mini-stat-icon"><i class="bi bi-tree"></i></span>
                                <h3 class="h5 fw-bold mb-1">Parks</h3>
                                <p class="text-muted small mb-0">Browse destinations by region, type, and amenities.</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="about-mini-stat">
                                <span class="about-mini-stat-icon"><i class="bi bi-calendar-event"></i></span>
                                <h3 class="h5 fw-bold mb-1">Events</h3>
                                <p class="text-muted small mb-0">Find upcoming public activities in a cleaner format.</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="about-mini-stat">
                                <span class="about-mini-stat-icon"><i class="bi bi-map"></i></span>
                                <h3 class="h5 fw-bold mb-1">Map</h3>
                                <p class="text-muted small mb-0">Explore the statewide system visually and interactively.</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="about-mini-stat">
                                <span class="about-mini-stat-icon"><i class="bi bi-diagram-3"></i></span>
                                <h3 class="h5 fw-bold mb-1">Portals</h3>
                                <p class="text-muted small mb-0">Client, employee, and admin areas can be added later.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 section-soft-green">
    <div class="container">
        <div class="text-center mb-5">
            <p class="section-label mb-2">PLATFORM STRUCTURE</p>
            <h2 class="fw-bold mb-2">Who the full system is designed for</h2>
            <p class="text-muted mb-0">These are the main user groups planned in the capstone build.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-xl-3">
                <div class="role-card h-100">
                    <div class="role-icon"><i class="bi bi-compass"></i></div>
                    <h3 class="h5 fw-bold">Guests</h3>
                    <p class="text-muted mb-0">
                        Public visitors can browse parks, maps, events, FAQs, and general information
                        without needing an account.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="role-card h-100">
                    <div class="role-icon"><i class="bi bi-person-circle"></i></div>
                    <h3 class="h5 fw-bold">Clients</h3>
                    <p class="text-muted mb-0">
                        Registered users will later be able to manage event requests,
                        reservations, payments, and account activity.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="role-card h-100">
                    <div class="role-icon"><i class="bi bi-briefcase"></i></div>
                    <h3 class="h5 fw-bold">Employees</h3>
                    <p class="text-muted mb-0">
                        Staff tools are planned for work schedules, PTO requests,
                        and internal operations.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="role-card h-100">
                    <div class="role-icon"><i class="bi bi-shield-check"></i></div>
                    <h3 class="h5 fw-bold">Administrators</h3>
                    <p class="text-muted mb-0">
                        Admin tools will handle approvals, employee management,
                        scheduling, and reporting dashboards.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5 align-items-start">
            <div class="col-lg-6">
                <p class="section-label mb-2">BUILD APPROACH</p>
                <h2 class="fw-bold mb-3">How this public-first build is being developed</h2>
                <div class="journey-list">
                    <div class="journey-step">
                        <span class="journey-number">1</span>
                        <div>
                            <h3 class="h6 fw-bold mb-1">Public pages first</h3>
                            <p class="text-muted mb-0">Home, parks, events, map, and public information pages are built before the private portals.</p>
                        </div>
                    </div>
                    <div class="journey-step">
                        <span class="journey-number">2</span>
                        <div>
                            <h3 class="h6 fw-bold mb-1">Reusable PHP layout</h3>
                            <p class="text-muted mb-0">Shared includes keep the code cleaner and easier to maintain in XAMPP.</p>
                        </div>
                    </div>
                    <div class="journey-step">
                        <span class="journey-number">3</span>
                        <div>
                            <h3 class="h6 fw-bold mb-1">Database-ready structure</h3>
                            <p class="text-muted mb-0">Pages are designed so static demo content can later be replaced with MySQL data.</p>
                        </div>
                    </div>
                    <div class="journey-step">
                        <span class="journey-number">4</span>
                        <div>
                            <h3 class="h6 fw-bold mb-1">Portals added last</h3>
                            <p class="text-muted mb-0">Client, employee, and admin features can be layered in once the public experience is solid.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm about-callout-card">
                    <div class="card-body p-4 p-lg-5">
                        <p class="section-label mb-2">NEXT STEPS</p>
                        <h2 class="fw-bold mb-3">Where the build can go next</h2>
                        <p class="text-muted mb-4">
                            After this public About page, the next logical pages would be
                            the AI Guide page, Donate page, FAQ page, or single-detail pages
                            for parks and events.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="map.php" class="btn btn-success">Back to Map</a>
                            <a href="events.php" class="btn btn-outline-success">Browse Events</a>
                            <a href="parks.php" class="btn btn-outline-success">Browse Parks</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
