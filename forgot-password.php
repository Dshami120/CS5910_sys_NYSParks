<?php
require 'bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(post('email'));
    $first = post('first_name');
    $last = post('last_name');
    $stmt = $db->prepare("SELECT id FROM users WHERE email=? AND first_name=? AND last_name=? AND account_status='active' LIMIT 1");
    $stmt->execute([$email, $first, $last]);
    $user = $stmt->fetch();
    if (!$user) {
        flash_set('error', 'We could not verify that user. Try the exact account name and email.');
        redirect('forgot-password.php');
    }
    $_SESSION['reset_user_id'] = (int) $user['id'];
    flash_set('success', 'User verified. Set a new password below.');
    redirect('reset-password.php');
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Forgot Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="forgot-password">
  <header class="site-header">
    <nav class="container py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
      <section class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 gap-lg-4">
        <a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2">
          <span class="brand-badge">NY</span>
          <span class="brand-mark text-dark">
            NYS Parks<br />
            <small>&amp; RECREATION</small>
          </span>
        </a>

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
        <li><a href="login.php" class="nav-link-custom" data-page-link="login">Log In</a></li>
        <li><a href="register.php" class="btn btn-dark nav-pill-btn" data-page-link="register">Register</a></li>
      </ul>
    </nav>
  </header>


  <main class="auth-shell d-flex align-items-center py-5">
    <section class="container">
      <section class="auth-panel p-4 p-lg-5">
        <p class="section-kicker mb-2">Account recovery</p>
        <h1 class="h3 fw-bold mb-3">Forgot your password?</h1>
        <p class="text-muted mb-4">Enter the email associated with your account and we will send a password reset link.</p>

        <form method="post">
          <label class="form-label">Email address</label>
          <input type="email" name="email" class="form-control form-control-lg mb-3" placeholder="you@example.com" required /><label class="form-label">First name</label><input type="text" name="first_name" class="form-control form-control-lg mb-3" placeholder="First name" required /><label class="form-label">Last name</label><input type="text" name="last_name" class="form-control form-control-lg mb-4" placeholder="Last name" required />
          <button type="submit" class="btn btn-success rounded-pill w-100 py-3 fw-semibold">Send Reset Link</button>
        </form>

        <section class="d-flex flex-column flex-sm-row justify-content-between gap-2 mt-4">
          <a href="login.php" class="text-decoration-none fw-semibold">Back to login</a>
          <a href="register.php" class="text-decoration-none fw-semibold">Create a new account</a>
        </section>
      </section>
    </section>
  </main>

  <footer class="footer-shell py-5 mt-5">
    <section class="container">
      <section class="footer-five">
        <article class="footer-block footer-brand">
          <a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2 mb-3">
            <span class="brand-badge">NY</span>
            <span class="brand-mark text-dark">
              NYS Parks<br />
              <small>&amp; RECREATION</small>
            </span>
          </a>
          <p class="text-muted mb-0">
            A modern gateway to New York State parks, events, maps, news, and role-based operations.
          </p>
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
          <ul class="list-unstyled m-0">
            <li class="mb-2"><a href="about.php" class="text-muted text-decoration-none">About</a></li>
            <li class="mb-2"><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li>
            <li class="mb-2"><a href="donate.php" class="text-muted text-decoration-none">Donate</a></li>
            <li class="mb-2"><a href="login.php" class="text-muted text-decoration-none">Log In</a></li>
            <li class="mb-2"><a href="register.php" class="text-muted text-decoration-none">Register</a></li>
            <li class="mb-2"><a href="account.php" class="text-muted text-decoration-none">Account</a></li>
          </ul>
        </article>

        <article class="footer-block">
          <h2 class="h6 fw-bold mb-3">Portals</h2>
          <ul class="list-unstyled m-0">
            <li class="mb-2"><a href="client-dashboard.php" class="text-muted text-decoration-none">Client Dashboard</a></li>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="app.js"></script>
</body>
</html>

