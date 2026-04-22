<?php
require 'bootstrap.php';
$apiConfigured = !empty(getenv('OPENAI_API_KEY'));
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NYS Parks - AI Guide</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body data-page="ai">
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
        <?php if ($currentUser): ?>
          <li><a href="account.php" class="nav-link-custom"><i class="bi bi-person-circle"></i>Account</a></li>
          <li><a href="logout.php" class="btn btn-dark nav-pill-btn">Logout</a></li>
        <?php else: ?>
          <li><a href="login.php" class="nav-link-custom" data-page-link="login">Log In</a></li>
          <li><a href="register.php" class="btn btn-dark nav-pill-btn" data-page-link="register">Register</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>

  <section class="subpage-hero ai-guide-hero d-flex align-items-center text-white">
    <div class="subpage-hero-overlay"></div>
    <div class="container position-relative py-5">
      <div class="row justify-content-center text-center">
        <div class="col-xl-8 col-lg-10">
          <span class="hero-kicker">AI TRIP PLANNER + SITE GUIDE</span>
          <h1 class="display-5 fw-bold mb-3">Ask the NYS Parks AI Guide</h1>
          <p class="lead text-white-50 mb-0">Get help finding parks, planning outings, comparing destinations, and understanding what this statewide portal offers.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="py-4 bg-light border-bottom">
    <div class="container">
      <div class="row g-3">
        <div class="col-lg-4"><div class="mini-stat-card h-100"><p class="text-uppercase small fw-bold text-success mb-2">Chatbot status</p><?php if ($apiConfigured): ?><h3 class="h5 mb-2"><i class="bi bi-check-circle-fill text-success"></i> Live OpenAI mode ready</h3><p class="text-muted mb-0">The server detected <code>OPENAI_API_KEY</code>. This page is ready for a real backend chat upgrade later.</p><?php else: ?><h3 class="h5 mb-2"><i class="bi bi-stars text-warning"></i> Demo mode active</h3><p class="text-muted mb-0">This capstone build uses guided sample replies in the browser so the page still demos cleanly without another API file.</p><?php endif; ?></div></div>
        <div class="col-lg-4"><div class="mini-stat-card h-100"><p class="text-uppercase small fw-bold text-success mb-2">What it can do</p><ul class="text-muted mb-0 ai-guide-mini-list"><li>Suggest parks by region or activity</li><li>Help plan day trips and weekend outings</li><li>Answer site-related public questions</li><li>Guide users to parks, events, FAQ, and map pages</li></ul></div></div>
        <div class="col-lg-4"><div class="mini-stat-card h-100"><p class="text-uppercase small fw-bold text-success mb-2">Capstone note</p><p class="text-muted mb-0">This merged version keeps the stronger UI from your alternate file while still fitting the flat root-file structure of the final site.</p></div></div>
      </div>
    </div>
  </section>

  <section class="py-5">
    <div class="container">
      <div class="row g-4 align-items-start">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm ai-chat-shell">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-2 p-4 pb-0">
              <div>
                <h2 class="h4 mb-1">Chat with the guide</h2>
                <p class="text-muted mb-0">Ask plain-English questions about parks, events, and trip ideas.</p>
              </div>
              <button type="button" id="clearAiChat" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i> Clear chat</button>
            </div>
            <div class="card-body p-4">
              <div id="aiChatMessages" class="ai-chat-messages mb-3">
                <div class="ai-message ai-message-assistant"><div class="ai-message-label">AI Guide</div><div class="ai-message-bubble">Hi! I can help you explore New York State parks, compare destinations, and suggest ideas for hiking, family trips, beaches, waterfalls, and more.</div></div>
              </div>
              <div id="aiChatStatus" class="small text-muted mb-3">Tip: try one of the suggested prompts below to test the chatbot quickly.</div>
              <div class="d-flex flex-wrap gap-2 mb-4">
                <button type="button" class="btn btn-sm btn-outline-success ai-prompt-chip" data-prompt="Suggest a family-friendly state park day trip near Albany.">Family trip near Albany</button>
                <button type="button" class="btn btn-sm btn-outline-success ai-prompt-chip" data-prompt="Which New York State parks are best for waterfalls and easy hiking?">Waterfalls + easy hiking</button>
                <button type="button" class="btn btn-sm btn-outline-success ai-prompt-chip" data-prompt="Plan a spring weekend in the Finger Lakes with parks and outdoor activities.">Finger Lakes weekend plan</button>
                <button type="button" class="btn btn-sm btn-outline-success ai-prompt-chip" data-prompt="What should I bring for a beach day at Jones Beach State Park?">Beach packing list</button>
              </div>
              <form id="aiChatForm" class="row g-2">
                <div class="col-12">
                  <label for="aiChatInput" class="form-label fw-semibold">Your message</label>
                  <textarea id="aiChatInput" class="form-control" rows="3" maxlength="800" placeholder="Example: Help me find a scenic NY state park for a weekend picnic and short hike." required></textarea>
                </div>
                <div class="col-sm-8 col-lg-9"><div class="form-text">This capstone build responds on-page with guided demo replies. It can be upgraded later to a live server-side API call.</div></div>
                <div class="col-sm-4 col-lg-3 d-grid"><button type="submit" class="btn btn-success btn-lg"><i class="bi bi-send-fill"></i> Send</button></div>
              </form>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm h-100 ai-guide-side-card mb-4"><div class="card-body p-4"><h3 class="h5 mb-3">How this page works</h3><ol class="text-muted small ps-3 mb-0"><li class="mb-2">The visitor types a question in the browser.</li><li class="mb-2">JavaScript renders the conversation on the page.</li><li class="mb-2">Demo replies simulate how a real guide would respond.</li><li class="mb-0">Later, this same layout can connect to a live AI backend.</li></ol></div></div>
          <div class="card border-0 shadow-sm ai-guide-side-card mb-4"><div class="card-body p-4"><h3 class="h5 mb-3">Good questions to ask</h3><ul class="text-muted small mb-0 ps-3"><li class="mb-2">Which parks are best for families with young kids?</li><li class="mb-2">What region has the best waterfall hikes?</li><li class="mb-2">Plan a low-cost outdoor day trip from NYC.</li><li class="mb-2">What pages on this site should I visit for events and maps?</li><li class="mb-0">Compare beach parks versus mountain parks in NY.</li></ul></div></div>
          <div class="card border-0 shadow-sm ai-guide-side-card"><div class="card-body p-4"><h3 class="h5 mb-3">Important note</h3><p class="text-muted small mb-0">This is a public-facing assistant demo for your capstone. The page now looks much stronger while still cooperating with the no-extra-files flat build.</p></div></div>
        </div>
      </div>
    </div>
  </section>

  <footer class="footer-shell py-5 mt-5"><section class="container"><section class="footer-five"><article class="footer-block footer-brand"><a href="index.php" class="brand-link text-decoration-none d-inline-flex align-items-center gap-2 mb-3"><span class="brand-badge">NY</span><span class="brand-mark text-dark">NYS Parks<br /><small>&amp; RECREATION</small></span></a><p class="text-muted mb-0">A modern gateway to New York State parks, events, maps, news, and role-based operations.</p></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Explore</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="parks.php" class="text-muted text-decoration-none">Parks</a></li><li class="mb-2"><a href="events.php" class="text-muted text-decoration-none">Events</a></li><li class="mb-2"><a href="map.php" class="text-muted text-decoration-none">Map</a></li><li class="mb-2"><a href="ai.php" class="text-muted text-decoration-none">AI</a></li><li class="mb-2"><a href="news.php" class="text-muted text-decoration-none">News</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Account</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="about.php" class="text-muted text-decoration-none">About</a></li><li class="mb-2"><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li><li class="mb-2"><a href="donate.php" class="text-muted text-decoration-none">Donate</a></li><li class="mb-2"><a href="login.php" class="text-muted text-decoration-none">Log In</a></li><li class="mb-2"><a href="register.php" class="text-muted text-decoration-none">Register</a></li><li class="mb-2"><a href="account.php" class="text-muted text-decoration-none">Account</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Portals</h2><ul class="list-unstyled m-0"><li class="mb-2"><a href="client-dashboard.php" class="text-muted text-decoration-none">Client Dashboard</a></li><li class="mb-2"><a href="admin-dashboard.php" class="text-muted text-decoration-none">Admin Dashboard</a></li><li class="mb-2"><a href="employee-dashboard.php" class="text-muted text-decoration-none">Employee Dashboard</a></li></ul></article><article class="footer-block"><h2 class="h6 fw-bold mb-3">Contact</h2><p class="text-muted mb-2">info@nysparks.gov</p><p class="text-muted mb-2">(555) 123-4567</p><p class="text-muted mb-0">Albany, New York</p></article></section></section></footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="app.js"></script>
</body>
</html>
