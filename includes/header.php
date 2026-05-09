<?php
/**
 * Shared page head and site header/navigation partial.
 * Expected variables: $pageTitle, $bodyPage, $extraHead, $currentUser.
 */
if (!function_exists('e')) {
    function e($value): string {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
$navUser = $currentUser ?? null;
$navBodyPage = (string) ($bodyPage ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($pageTitle ?? 'NYS Parks') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
<?php if (!empty($extraHead)) { echo rtrim((string) $extraHead) . "
"; } ?>
  <link rel="stylesheet" href="css/styles.css" />
</head>
<body data-page="<?= e($navBodyPage) ?>">
<header class="site-header">
  <nav class="container py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
    <section class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 gap-lg-4">
      <a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2">
        <span class="brand-badge">NY</span>
        <span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span>
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
      <?php if (is_array($navUser) && function_exists('account_chip')): ?>
        <?= account_chip($navUser, $navBodyPage === 'account') ?>
        <li><a href="logout.php" class="btn btn-dark nav-pill-btn" data-page-link="logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      <?php else: ?>
        <li><a href="login.php" class="nav-link-custom" data-page-link="login">Log In</a></li>
        <li><a href="register.php" class="btn btn-dark nav-pill-btn" data-page-link="register">Register</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>
