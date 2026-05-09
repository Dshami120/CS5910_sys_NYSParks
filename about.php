<?php
// Load project setup.
require 'bootstrap.php';
?>

<?php
// Set page metadata.
$pageTitle = 'NYS Parks - About Us';
$bodyPage = 'about';
$extraHead = '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

    <main>

        <!-- Blue title section -->
        <section class="subpage-hero subpage-hero-news text-white d-flex align-items-center">
            <div class="subpage-hero-overlay"></div>

            <div class="container position-relative py-5">
                <div class="row">
                    <div class="col-lg-8">
                        <span class="hero-kicker">About NYS Parks</span>
                        <h1 class="display-5 fw-bold mb-3">Explore, reserve, and reconnect with New York outdoors.</h1>
                        <p class="lead text-white-50 mb-0">NYS Parks &amp; Recreation brings together park discovery, events, maps, reservations, news, and visitor support in one simple digital experience inspired by New York State's public parks and historic places.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Purpose and numbers section -->
        <section class="py-5">
            <section class="container">

                <!-- Purpose and metrics cards -->
                <section class="row g-4 align-items-stretch">

                    <!-- Purpose card -->
                    <article class="col-lg-7">
                        <section class="soft-card p-4 p-lg-5 h-100">
                            <p class="section-kicker mb-2">Our purpose</p>
                            <h2 class="h3 fw-bold mb-3">A modern gateway to New York's parks and recreation experiences.</h2>

                            <p class="text-muted">
                                New York State's park system includes more than 250 parks, historic sites, trails, golf courses, boat launches, campgrounds, and other public destinations across more than 360,000 acres. This site is designed to make that experience easier to explore by helping visitors find parks, browse events, review maps, request reservations, and stay connected with park news.
                            </p>

                            <p class="text-muted mb-0">
                                From waterfalls and lakefront beaches to wooded trails, historic destinations, family programs, and seasonal recreation, the platform focuses on helping visitors plan their next park visit with clear information and easy-to-use tools.
                            </p>
                        </section>
                    </article>

                    <!-- Metrics card -->
                    <article class="col-lg-5">
                        <section class="soft-card p-4 p-lg-5 h-100">
                            <p class="section-kicker mb-2">By the numbers</p>

                            <section class="row g-3">

                                <!-- Parks metric -->
                                <article class="col-6">
                                    <section class="metric-tile p-3 rounded-4 bg-light h-100">
                                        <p class="display-6 fw-bold text-success mb-0">250+</p>
                                        <p class="text-muted small mb-0">parks, historic sites, trails, golf courses, boat launches, and campgrounds</p>
                                    </section>
                                </article>

                                <!-- Acres metric -->
                                <article class="col-6">
                                    <section class="metric-tile p-3 rounded-4 bg-light h-100">
                                        <p class="display-6 fw-bold text-success mb-0">360k+</p>
                                        <p class="text-muted small mb-0">acres of public recreation and historic resources</p>
                                    </section>
                                </article>

                                <!-- Visits metric -->
                                <article class="col-6">
                                    <section class="metric-tile p-3 rounded-4 bg-light h-100">
                                        <p class="display-6 fw-bold text-success mb-0">88M</p>
                                        <p class="text-muted small mb-0">record visits reported across the state park system in 2024</p>
                                    </section>
                                </article>

                                <!-- Years metric -->
                                <article class="col-6">
                                    <section class="metric-tile p-3 rounded-4 bg-light h-100">
                                        <p class="display-6 fw-bold text-success mb-0">100+</p>
                                        <p class="text-muted small mb-0">years of public access, preservation, recreation, and stewardship</p>
                                    </section>
                                </article>

                            </section>
                        </section>
                    </article>

                </section>

                <!-- Feature cards -->
                <section class="row g-4 pt-5">

                    <!-- Discover card -->
                    <article class="col-md-4">
                        <section class="soft-card p-4 h-100">
                            <span class="feature-icon mb-3"><i class="bi bi-compass"></i></span>
                            <h2 class="h5 fw-bold mb-2">Discover</h2>
                            <p class="text-muted mb-0">
                                Search parks by region, experience type, amenities, and location so visitors can quickly find destinations that match their plans.
                            </p>
                        </section>
                    </article>

                    <!-- Plan card -->
                    <article class="col-md-4">
                        <section class="soft-card p-4 h-100">
                            <span class="feature-icon mb-3"><i class="bi bi-calendar-check"></i></span>
                            <h2 class="h5 fw-bold mb-2">Plan</h2>
                            <p class="text-muted mb-0">
                                Browse public events, review details, request private bookings, and use maps to understand where each park experience takes place.
                            </p>
                        </section>
                    </article>

                    <!-- Stewardship card -->
                    <article class="col-md-4">
                        <section class="soft-card p-4 h-100">
                            <span class="feature-icon mb-3"><i class="bi bi-shield-check"></i></span>
                            <h2 class="h5 fw-bold mb-2">Support stewardship</h2>
                            <p class="text-muted mb-0">
                                Promote responsible recreation by connecting visitors with accurate park information, clear guidance, and donation support.
                            </p>
                        </section>
                    </article>

                </section>

                <!-- Mission and values -->
                <section class="row g-4 pt-5">

                    <!-- Mission card -->
                    <article class="col-lg-7">
                        <section class="soft-card p-4 p-lg-5 h-100">
                            <h2 class="h4 fw-bold mb-3">Our Mission</h2>

                            <p class="text-muted">
                                The official mission of New York State Parks is centered on providing safe and enjoyable recreational and interpretive opportunities for residents and visitors while serving as responsible stewards of natural, historic, and cultural resources.
                            </p>

                            <h2 class="h4 fw-bold mt-5 mb-3">Our Digital Goal</h2>

                            <p class="text-muted mb-0">
                                This project supports that mission by presenting park information in a modern public website with discovery tools, event browsing, reservation workflows, visitor support, role-based dashboards, and a simple structure that can be expanded over time.
                            </p>
                        </section>
                    </article>

                    <!-- Values card -->
                    <article class="col-lg-5">
                        <section class="soft-card p-4 p-lg-5 h-100">
                            <h2 class="h4 fw-bold mb-3">Core Values</h2>

                            <ul class="list-unstyled m-0">
                                <li class="mb-3"><strong>Accessibility:</strong> clear navigation and inclusive public information</li>
                                <li class="mb-3"><strong>Community:</strong> stronger participation in parks, events, and programs</li>
                                <li class="mb-3"><strong>Efficiency:</strong> better workflows for bookings, schedules, and staff operations</li>
                                <li><strong>Innovation:</strong> using modern tools to make park information easier to access, manage, and update</li>
                            </ul>
                        </section>
                    </article>

                </section>

                <!-- Student team -->
                <section class="pt-5">

                    <!-- Student team heading -->
                    <header class="mb-4 text-center">
                        <p class="section-kicker mb-2">Digital experience team</p>
                        <h2 class="fw-bold mb-2">Meet the Team</h2>
                        <p class="text-muted col-lg-8 mx-auto mb-0">
                            This visitor platform was designed and implemented as a senior systems design and implementation project by final-semester computer science undergraduates.
                        </p>
                    </header>

                    <!-- Student team cards -->
                    <section class="row g-4 justify-content-center">

                        <!-- Dany profile -->
                        <article class="col-md-6 col-lg-5">
                            <section class="image-card h-100 text-center p-4">
                                <section class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-4" style="width: 96px; height: 96px; font-size: 2rem; font-weight: 800;">
                                    DS
                                </section>

                                <h3 class="h5 fw-bold mb-1">Dany S.</h3>
                                <p class="text-success fw-semibold mb-3">Full Stack Developer</p>

                                <p class="text-muted mb-0">
                                    Dany led the full-stack build of the platform, bringing together the database, public pages, account features, dashboards, booking workflows, maps, chatbot connection, testing, and final system integration. His work helped turn the project vision into a functioning end-to-end web application.
                                </p>
                            </section>
                        </article>

                        <!-- Davina profile -->
                        <article class="col-md-6 col-lg-5">
                            <section class="image-card h-100 text-center p-4">
                                <section class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-4" style="width: 96px; height: 96px; font-size: 2rem; font-weight: 800;">
                                    DG
                                </section>

                                <h3 class="h5 fw-bold mb-1">Davina G.</h3>
                                <p class="text-success fw-semibold mb-3">Front End Developer</p>

                                <p class="text-muted mb-0">
                                    Davina helped shape the original idea and early design direction for the site, focusing on a welcoming visitor experience, page structure, and visual planning for how guests, clients, employees, and administrators would move through the system.
                                </p>
                            </section>
                        </article>

                    </section>
                </section>

                <!-- Public service team -->
                <section class="pt-5">

                    <!-- Public service heading -->
                    <header class="mb-4 text-center">
                        <p class="section-kicker mb-2">Parks operations team</p>
                        <h2 class="fw-bold mb-2">Public Service Leadership</h2>
                        <p class="text-muted col-lg-8 mx-auto mb-0">
                            These sample staff profiles represent the types of public service roles the system supports through booking management, scheduling, communication, and visitor operations.
                        </p>
                    </header>

                    <!-- Public service profile cards -->
                    <section class="row g-4">

                        <!-- Director profile -->
                        <article class="col-md-4">
                            <section class="image-card h-100">
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=900&q=80" alt="Director Sarah Johnson" class="image-cover-sm" />

                                <section class="p-4">
                                    <h3 class="h5 fw-bold mb-1">Sarah Johnson</h3>
                                    <p class="text-success fw-semibold mb-2">Director</p>
                                    <p class="text-muted mb-0">Leads modernization strategy, public service delivery, and department planning.</p>
                                </section>
                            </section>
                        </article>

                        <!-- Deputy Director profile -->
                        <article class="col-md-4">
                            <section class="image-card h-100">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=900&q=80" alt="Deputy Director Mike Chen" class="image-cover-sm" />

                                <section class="p-4">
                                    <h3 class="h5 fw-bold mb-1">Mike Chen</h3>
                                    <p class="text-success fw-semibold mb-2">Deputy Director</p>
                                    <p class="text-muted mb-0">Supports operations, staff coordination, and program planning across parks.</p>
                                </section>
                            </section>
                        </article>

                        <!-- Recreation Coordinator profile -->
                        <article class="col-md-4">
                            <section class="image-card h-100">
                                <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=900&q=80" alt="Recreation Coordinator Lisa Rodriguez" class="image-cover-sm" />

                                <section class="p-4">
                                    <h3 class="h5 fw-bold mb-1">Lisa Rodriguez</h3>
                                    <p class="text-success fw-semibold mb-2">Recreation Coordinator</p>
                                    <p class="text-muted mb-0">Coordinates public programming, seasonal activities, and outreach.</p>
                                </section>
                            </section>
                        </article>

                    </section>
                </section>

            </section>
        </section>
    </main>

    <!-- Bootstrap JavaScript bundle -->
    <!-- Shared site JavaScript -->
<?php include __DIR__ . '/includes/footer.php'; ?>