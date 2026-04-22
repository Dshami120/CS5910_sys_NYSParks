<footer id="footer" class="site-footer">
    <div class="container py-5">
        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <a href="index.php" class="site-brand text-decoration-none d-inline-flex align-items-center gap-2 mb-3">
                    <span class="brand-badge">NYS</span>
                    <span class="site-brand-text fw-bold text-dark lh-sm">
                        Parks<br>
                        <small class="text-success-emphasis">RECREATION</small>
                    </span>
                </a>
                <p class="text-muted mb-0 footer-copy">
                    Discover parks, browse events, explore the map, and plan outdoor experiences across New York State.
                </p>
            </div>

            <div class="col-sm-6 col-lg-2">
                <h6 class="fw-bold mb-3">Explore</h6>
                <ul class="list-unstyled footer-links mb-0">
                    <li><a href="parks.php">Parks</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="map.php">Map</a></li>
                    <li><a href="ai-guide.php">AI Guide</a></li>
                </ul>
            </div>

            <div class="col-sm-6 col-lg-2">
                <h6 class="fw-bold mb-3">Info</h6>
                <ul class="list-unstyled footer-links mb-0">
                    <li><a href="about.php">About</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="news.php">News</a></li>
                    <li><a href="donate.php">Donate</a></li>
                </ul>
            </div>

            <div class="col-lg-4">
                <h6 class="fw-bold mb-3">Contact</h6>
                <p class="text-muted mb-1">info@nysparks.example</p>
                <p class="text-muted mb-1">(555) 123-4567</p>
                <p class="text-muted mb-0">Albany, New York</p>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-5 pt-4">
            <p class="small text-muted mb-0">&copy; <?php echo date('Y'); ?> NYS Parks & Recreation. Capstone project build.</p>
            <div class="d-flex gap-3 small">
                <a href="about.php" class="text-muted text-decoration-none">About</a>
                <a href="faq.php" class="text-muted text-decoration-none">Support</a>
                <a href="login.php" class="text-muted text-decoration-none">Account</a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($extraScripts)) echo $extraScripts; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
