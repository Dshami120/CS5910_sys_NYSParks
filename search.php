<?php
require 'bootstrap.php';
$q = get('q');
$region = get('region');
$like = '%' . $q . '%';
$results = [];

if ($q !== '' || $region !== '') {
    $parkSql = "SELECT name, region, park_type, card_summary, description FROM parks WHERE 1=1";
    $parkParams = [];
    if ($q !== '') {
        $parkSql .= " AND (name LIKE ? OR region LIKE ? OR park_type LIKE ? OR amenities LIKE ? OR description LIKE ?)";
        array_push($parkParams, $like, $like, $like, $like, $like);
    }
    if ($region !== '') { $parkSql .= " AND region = ?"; $parkParams[] = $region; }
    $stmt = $db->prepare($parkSql . " ORDER BY is_featured DESC, name LIMIT 12");
    $stmt->execute($parkParams);
    foreach ($stmt->fetchAll() as $row) {
        $results[] = ['type'=>'Park', 'title'=>$row['name'], 'summary'=>$row['card_summary'] ?: mb_strimwidth($row['description'], 0, 140, '…'), 'url'=>'parks.php?q=' . urlencode($row['name'])];
    }

    $eventSql = "SELECT e.title, e.description, e.card_summary, e.event_type, p.name AS park_name, p.region FROM events e JOIN parks p ON p.id=e.park_id WHERE e.event_status='published'";
    $eventParams = [];
    if ($q !== '') {
        $eventSql .= " AND (e.title LIKE ? OR e.description LIKE ? OR e.card_summary LIKE ? OR e.category LIKE ? OR p.name LIKE ? OR p.region LIKE ?)";
        array_push($eventParams, $like, $like, $like, $like, $like, $like);
    }
    if ($region !== '') { $eventSql .= " AND p.region = ?"; $eventParams[] = $region; }
    $stmt = $db->prepare($eventSql . " ORDER BY e.start_datetime ASC LIMIT 12");
    $stmt->execute($eventParams);
    foreach ($stmt->fetchAll() as $row) {
        $label = $row['event_type'] === 'private' ? 'Private event' : 'Event';
        $results[] = ['type'=>$label, 'title'=>$row['title'], 'summary'=>($row['card_summary'] ?: mb_strimwidth($row['description'], 0, 140, '…')) . ' · ' . $row['park_name'], 'url'=>'events.php?q=' . urlencode($row['title'])];
    }

    $newsSql = "SELECT title, summary, content, region, topic FROM news WHERE news_status='published'";
    $newsParams = [];
    if ($q !== '') {
        $newsSql .= " AND (title LIKE ? OR summary LIKE ? OR content LIKE ? OR region LIKE ? OR tag LIKE ? OR topic LIKE ?)";
        array_push($newsParams, $like, $like, $like, $like, $like, $like);
    }
    if ($region !== '') { $newsSql .= " AND region = ?"; $newsParams[] = $region; }
    $stmt = $db->prepare($newsSql . " ORDER BY published_date DESC LIMIT 12");
    $stmt->execute($newsParams);
    foreach ($stmt->fetchAll() as $row) {
        $results[] = ['type'=>'News', 'title'=>$row['title'], 'summary'=>$row['summary'], 'url'=>'news.php'];
    }

    $staticPages = [
        ['FAQ', 'Frequently Asked Questions', 'faq questions answers help support booking payment account parks events', 'faq.php'],
        ['Map', 'Park Map', 'map locations regions directions nearby parks', 'map.php'],
        ['Donate', 'Donate', 'donate donations support conservation payment gift', 'donate.php'],
        ['Contact', 'Contact Information', 'contact email phone Albany info@nysparks.gov 555 123 4567', 'about.php'],
        ['About', 'About NYS Parks', 'about mission parks recreation outdoor access statewide', 'about.php'],
        ['Booking', 'Client Event Booking', 'client booking create event private reservation request field whole park', 'client-create-event.php'],
    ];
    $needle = strtolower($q);
    foreach ($staticPages as [$type, $title, $text, $url]) {
        if ($q === '' || str_contains(strtolower($title . ' ' . $text), $needle)) {
            $results[] = ['type'=>$type, 'title'=>$title, 'summary'=>$text, 'url'=>$url];
        }
    }
}
$regions = $db->query("SELECT DISTINCT region FROM parks ORDER BY region")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - Search</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="css/styles.css" />
</head>
<body data-page="search">
<header class="site-header"><nav class="container py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3"><section class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 gap-lg-4"><a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a><ul class="list-unstyled d-flex flex-wrap gap-3 gap-lg-4 m-0 align-items-center"><li><a href="parks.php" class="nav-link-custom" data-page-link="parks"><i class="bi bi-tree"></i>Parks</a></li><li><a href="events.php" class="nav-link-custom" data-page-link="events"><i class="bi bi-calendar-event"></i>Events</a></li><li><a href="map.php" class="nav-link-custom" data-page-link="map"><i class="bi bi-geo-alt"></i>Map</a></li><li><a href="news.php" class="nav-link-custom" data-page-link="news"><i class="bi bi-newspaper"></i>News</a></li><li><a href="about.php" class="nav-link-custom">About Us</a></li><li><a href="faq.php" class="nav-link-custom">FAQ</a></li><li><a href="donate.php" class="nav-link-custom">Donate</a></li></ul></section><ul class="list-unstyled d-flex flex-wrap gap-2 gap-lg-3 m-0 align-items-center"><?php if ($currentUser): ?><li><a href="account.php" class="nav-link-custom">Account</a></li><li><a href="logout.php" class="btn btn-dark nav-pill-btn">Logout</a></li><?php else: ?><li><a href="login.php" class="nav-link-custom">Log In</a></li><li><a href="register.php" class="btn btn-dark nav-pill-btn">Register</a></li><?php endif; ?></ul></nav></header>
<main class="py-5">
  <section class="container">
    <header class="mb-4">
      <p class="section-kicker mb-2">Site search</p>
      <h1 class="display-6 fw-bold mb-3">Search NYS Parks</h1>
      <p class="text-muted mb-0">Search parks, events, news, FAQ/static pages, map, donate, contact/about, and the client booking page.</p>
    </header>
    <form class="soft-card p-3 p-lg-4 mb-4" method="get" action="search.php">
      <div class="row g-3 align-items-end">
        <div class="col-lg-7"><label class="form-label">Search</label><input type="text" name="q" value="<?= e($q) ?>" class="form-control form-control-lg" placeholder="Search the entire site..." /></div>
        <div class="col-lg-3"><label class="form-label">Region</label><select name="region" class="form-select form-select-lg"><option value="">Any Region</option><?php foreach ($regions as $regionName): ?><option value="<?= e($regionName) ?>" <?= $region===$regionName?'selected':'' ?>><?= e($regionName) ?></option><?php endforeach; ?></select></div>
        <div class="col-lg-2"><button class="btn btn-success rounded-pill w-100 btn-lg" type="submit">Search</button></div>
      </div>
    </form>
    <section class="list-shell">
      <section class="p-4 border-bottom"><h2 class="h5 fw-bold mb-0"><?= count($results) ?> result<?= count($results) === 1 ? '' : 's' ?></h2></section>
      <?php foreach ($results as $i => $result): ?>
        <section class="<?= $i < count($results) - 1 ? 'list-row' : '' ?> p-4">
          <span class="badge text-bg-light border mb-2"><?= e($result['type']) ?></span>
          <h3 class="h5 fw-bold mb-2"><a href="<?= e($result['url']) ?>" class="text-decoration-none text-dark"><?= e($result['title']) ?></a></h3>
          <p class="text-muted mb-3"><?= e($result['summary']) ?></p>
          <a href="<?= e($result['url']) ?>" class="btn btn-sm btn-outline-success rounded-pill">Open</a>
        </section>
      <?php endforeach; ?>
      <?php if (!$results): ?><section class="p-4 text-muted">Enter a search term or choose a region to search the site.</section><?php endif; ?>
    </section>
  </section>
</main>
<footer class="footer-shell py-5 mt-5"><section class="container"><p class="text-muted mb-0">NYS Parks &amp; Recreation</p></section></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="js/app.js"></script>
</body>
</html>
