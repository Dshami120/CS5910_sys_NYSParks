<?php
require 'bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = post('full_name');
    [$first, $last] = split_name($fullName);
    $email = strtolower(post('email'));
    $phone = post('phone');
    $birthdate = post('birthdate');
    $organization = post('organization');
    $password = post('password');
    if (!$first || !$last) { flash_set('error', 'Please enter your full name.'); redirect('register.php'); }
    if (!valid_email($email)) { flash_set('error', 'Enter a valid email address.'); redirect('register.php'); }
    if (strlen($password) < 8) { flash_set('error', 'Password must be at least 8 characters.'); redirect('register.php'); }
    $exists = $db->prepare("SELECT id FROM users WHERE email = ?"); $exists->execute([$email]);
    if ($exists->fetch()) { flash_set('error', 'That email is already registered.'); redirect('register.php'); }
    $db->prepare("INSERT INTO users (first_name,last_name,email,password_hash,role,phone,birthdate,organization,account_status) VALUES (?,?,?,?, 'client',?,?,?, 'active')")
       ->execute([$first,$last,$email,password_hash($password,PASSWORD_DEFAULT),$phone ?: null,$birthdate ?: null,$organization ?: null]);
    flash_set('success', 'Account created. Please log in.');
    redirect('login.php');
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <!-- Basic page metadata -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Page title -->
  <title>NYS Parks - Register</title>

  <!-- Bootstrap core CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />

  <!-- Bootstrap Icons for lightweight icons -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
  />

  <!-- Shared site stylesheet -->
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="register">
  <!-- =====================================================
       PUBLIC SITE HEADER
       - Shared top navigation for guest-facing pages
       - Using semantic tags as much as possible
       ===================================================== -->
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

  <main class="auth-shell d-flex align-items-center">
    <section class="container py-5">
      <section class="auth-panel p-4 p-lg-5">
        <header class="text-center mb-4">
          <p class="section-kicker mb-2">Client registration</p>
          <h1 class="h2 fw-bold mb-2">Create your account</h1>
          <p class="text-muted mb-0">Simple starter form for the future client registration workflow.</p>
        </header>

        <form method="post">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control form-control-lg mb-3" placeholder="Your full name" required />

          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control form-control-lg mb-3" placeholder="you@example.com" required />

          <label class="form-label">Phone Number</label>
          <input type="tel" name="phone" class="form-control form-control-lg mb-3" placeholder="(555) 123-4567" />

          <label class="form-label">Birthdate</label><input type="date" name="birthdate" class="form-control form-control-lg mb-3" /><label class="form-label">Organization</label><input type="text" name="organization" class="form-control form-control-lg mb-3" placeholder="Community group, company, or school" /><label class="form-label">Password</label>
          <input type="password" name="password" class="form-control form-control-lg mb-4" placeholder="Create password" required />

          <button type="submit" class="btn btn-success w-100 rounded-pill py-3 fw-semibold mb-3">
            Register
          </button>

          <p class="text-center mb-0">Already have an account? <a href="login.php" class="map-link">Log in</a></p>
        </form>
      </section>
    </section>
  </main>
  <!-- =====================================================
       SHARED FOOTER
       - Keeps the bottom section consistent across pages
       ===================================================== -->
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

  <!-- Bootstrap JavaScript bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Shared site JavaScript -->
  <script src="app.js"></script>
</body>
</html>
