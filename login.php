<?php
require 'bootstrap.php';
if ($currentUser) { redirect($currentUser['role'] === 'admin' ? 'admin-dashboard.php' : ($currentUser['role'] === 'employee' ? 'employee-dashboard.php' : 'client-dashboard.php')); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(post('email'));
    $password = post('password');
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND account_status='active' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        login_user($user);
        $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$user['id']]);
        redirect($user['role'] === 'admin' ? 'admin-dashboard.php' : ($user['role'] === 'employee' ? 'employee-dashboard.php' : 'client-dashboard.php'));
    }
    flash_set('error', 'Invalid email or password.');
    redirect('login.php');
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <!-- Basic page metadata -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Page title -->
  <title>NYS Parks - Login</title>

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
<body data-page="login">
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
          <p class="section-kicker mb-2">Secure access</p>
          <h1 class="h2 fw-bold mb-2">Log in to your account</h1>
          <p class="text-muted mb-0">Clients, employees, and admins can all start here.</p>
        </header>

        <form method="post">
          <label class="form-label">Email / Username</label>
          <input type="email" name="email" class="form-control form-control-lg mb-3" placeholder="you@example.com" required />

          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control form-control-lg mb-4" placeholder="Enter password" required />

          <button type="submit" class="btn btn-success w-100 rounded-pill py-3 fw-semibold mb-3">
            Sign In
          </button>

          <p class="text-center mb-2"><a href="forgot-password.php" class="map-link">Forgot Password?</a></p>
          <p class="text-center mb-0">New here? <a href="register.php" class="map-link">Create an account</a></p>
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
