<?php
$activePage = $activePage ?? '';
$navItems = [
    ['key' => 'home', 'label' => 'Home', 'href' => 'index.php'],
    ['key' => 'parks', 'label' => 'Parks', 'href' => 'parks.php'],
    ['key' => 'events', 'label' => 'Events', 'href' => 'events.php'],
    ['key' => 'map', 'label' => 'Map', 'href' => 'map.php'],
    ['key' => 'ai-guide', 'label' => 'AI Guide', 'href' => 'ai-guide.php'],
    ['key' => 'about', 'label' => 'About', 'href' => 'about.php'],
    ['key' => 'faq', 'label' => 'FAQ', 'href' => 'faq.php'],
    ['key' => 'news', 'label' => 'News', 'href' => 'news.php'],
    ['key' => 'donate', 'label' => 'Donate', 'href' => 'donate.php'],
];
?>
<nav class="navbar navbar-expand-xl site-navbar border-bottom bg-white sticky-top">
    <div class="container py-3">
        <a class="navbar-brand site-brand d-flex align-items-center gap-2 text-decoration-none" href="index.php">
            <span class="brand-badge">NYS</span>
            <span class="site-brand-text fw-bold text-dark lh-sm">
                Parks<br>
                <small class="text-success-emphasis">RECREATION</small>
            </span>
        </a>

        <button class="navbar-toggler border-0 shadow-none px-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav site-nav-list mx-auto mb-3 mb-xl-0 align-items-xl-center">
                <?php foreach ($navItems as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?php echo $activePage === $item['key'] ? 'active active-link' : ''; ?>" href="<?php echo e($item['href']); ?>">
                            <?php echo e($item['label']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="d-flex flex-column flex-xl-row gap-2 align-items-xl-center site-auth-links">
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo e(getRoleHomePath()); ?>" class="nav-link-custom text-decoration-none px-xl-2 py-2 py-xl-0">
                        <?php echo e(getRoleHomeLabel()); ?>
                    </a>
                    <a href="logout.php" class="btn btn-dark rounded-pill px-4">Log Out</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link-custom text-decoration-none px-xl-2 py-2 py-xl-0">Log In</a>
                    <a href="register.php" class="btn btn-dark rounded-pill px-4">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
