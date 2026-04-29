<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
}
session_destroy();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Logout</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="logout">
  <header class="site-header">
    <nav class="container py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
      <a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a>
      <ul class="list-unstyled d-flex flex-wrap gap-3 m-0 align-items-center">
        <li><a href="parks.php" class="nav-link-custom">Parks</a></li>
        <li><a href="events.php" class="nav-link-custom">Events</a></li>
        <li><a href="news.php" class="nav-link-custom">News</a></li>
        <li><a href="login.php" class="nav-link-custom"><i class="bi bi-box-arrow-in-right"></i>Log In</a></li>
      </ul>
    </nav>
  </header>
  <main class="py-5">
    <section class="container">
      <section class="row justify-content-center">
        <article class="col-lg-7">
          <section class="auth-panel p-4 p-lg-5 text-center">
            <p class="section-kicker mb-2">Session ended</p>
            <h1 class="h2 fw-bold mb-3">You have been logged out</h1>
            <p class="text-muted mb-4">Your session was fully cleared. You must log in again to access any dashboard.</p>
            <section class="d-flex flex-wrap justify-content-center gap-2">
              <a href="login.php" class="btn btn-success rounded-pill px-4">Log In Again</a>
              <a href="index.php" class="btn btn-outline-dark rounded-pill px-4">Back to Home</a>
            </section>
          </section>
        </article>
      </section>
    </section>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="app.js"></script>
</body>
</html>
