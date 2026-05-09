<?php
// Load project setup.
require 'bootstrap.php';
?>
<?php
// Set page metadata.
$pageTitle = 'NYS Parks - AI Guide';
$bodyPage = 'ai';
$extraHead = '';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="subpage-hero ai-guide-hero d-flex align-items-center text-white">
      <div class="subpage-hero-overlay"></div>
      <div class="container position-relative py-5">
        <div class="row align-items-center g-4">
          <div class="col-lg-8">
            <span class="hero-kicker">Virtual Park Guide</span>
            <h1 class="display-5 fw-bold mb-3">Find the right park, event, or visitor resource</h1>
            <p class="lead text-white-50 mb-4">Ask for help exploring New York State parks, planning outdoor activities, finding public events, or navigating visitor services across this site.</p>
            <div class="d-flex flex-wrap gap-2">
              <a href="#aiChatForm" class="btn btn-success rounded-pill px-4 fw-semibold"><i class="bi bi-chat-dots me-1"></i> Ask the Guide</a>
              <a href="parks.php" class="btn btn-light rounded-pill px-4 fw-semibold"><i class="bi bi-tree me-1"></i> Browse Parks</a>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="ai-hero-card">
              <p class="section-label mb-2">Visitor assistance</p>
              <h2 class="h5 fw-bold mb-2"><i class="bi bi-compass-fill text-success me-1"></i> Personalized guidance</h2>
              <p class="text-muted mb-0">Get practical suggestions for park visits, seasonal activities, event planning, accessibility questions, and site navigation.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-4 bg-light border-bottom">
      <div class="container">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="mini-stat-card h-100 p-4">
              <div class="impact-icon"><i class="bi bi-map"></i></div>
              <h2 class="h5 fw-bold mb-2">Explore destinations</h2>
              <p class="text-muted mb-0">Compare parks by region, activity, amenities, and the kind of outdoor experience you want.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mini-stat-card h-100 p-4">
              <div class="impact-icon"><i class="bi bi-calendar-event"></i></div>
              <h2 class="h5 fw-bold mb-2">Plan around events</h2>
              <p class="text-muted mb-0">Find public programs, seasonal ideas, and helpful next steps for building your visit.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mini-stat-card h-100 p-4">
              <div class="impact-icon"><i class="bi bi-info-circle"></i></div>
              <h2 class="h5 fw-bold mb-2">Use official details</h2>
              <p class="text-muted mb-0">For hours, fees, closures, permits, and safety rules, confirm details with the official park page before visiting.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-5">
      <div class="container">
        <div class="row g-4 align-items-start">
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm ai-chat-shell overflow-hidden">
              <div class="card-header bg-white border-0 p-4 p-lg-5 pb-0">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                  <div>
                    <p class="section-label mb-2">Ask a question</p>
                    <h2 class="fw-bold mb-2">Chat with the NYS Parks Guide</h2>
                    <p class="text-muted mb-0">Use plain English to get quick guidance for planning a visit or finding the right resource.</p>
                  </div>
                  <button type="button" id="clearAiChat" class="btn btn-outline-dark rounded-pill btn-sm"><i class="bi bi-arrow-clockwise me-1"></i> Clear chat</button>
                </div>
              </div>
              <div class="card-body p-4 p-lg-5">
                <div id="aiChatMessages" class="ai-chat-messages mb-3">
                  <div class="ai-message ai-message-assistant">
                    <div class="ai-message-label">AI Guide</div>
                    <div class="ai-message-bubble">Welcome! I can help you explore parks, compare destinations, prepare for a visit, and find the right page for events, maps, news, donations, or account services.</div>
                  </div>
                </div>
                <div id="aiChatStatus" class="small text-muted mb-3">Choose a sample prompt or type your own question below.</div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                  <button type="button" class="btn btn-sm btn-outline-success rounded-pill ai-prompt-chip" data-prompt="Suggest a family-friendly New York State park day trip.">Family day trip</button>
                  <button type="button" class="btn btn-sm btn-outline-success rounded-pill ai-prompt-chip" data-prompt="Which parks are good for beaches, trails, or scenic picnic areas?">Beaches, trails + picnics</button>
                  <button type="button" class="btn btn-sm btn-outline-success rounded-pill ai-prompt-chip" data-prompt="Help me find public events and programs on this website.">Find public events</button>
                  <button type="button" class="btn btn-sm btn-outline-success rounded-pill ai-prompt-chip" data-prompt="Help me understand how to request a private event as a registered client.">Private event request help</button>
                </div>

                <form id="aiChatForm" class="row g-3">
                  <div class="col-12">
                    <label for="aiChatInput" class="form-label fw-semibold">Your message</label>
                    <textarea id="aiChatInput" class="form-control" rows="4" maxlength="800" placeholder="Example: Help me find a scenic NY state park for a weekend picnic and short hike." required></textarea>
                  </div>
                  <div class="col-lg-8">
                    <div class="form-text">This guide is a planning assistant. Verify time-sensitive details such as hours, fees, closures, permits, and safety rules with the official park office before visiting.</div>
                  </div>
                  <div class="col-lg-4 d-grid">
                    <button type="submit" class="btn btn-success btn-lg rounded-pill fw-semibold"><i class="bi bi-send-fill me-1"></i> Send Message</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card border-0 shadow-sm ai-guide-side-card mb-4">
              <div class="card-body p-4">
                <p class="section-label mb-2">How it helps</p>
                <h3 class="h5 fw-bold mb-3">Guidance for visitors</h3>
                <ul class="text-muted small mb-0 ps-3">
                  <li class="mb-2">Suggests parks by region, activity, amenities, or trip style.</li>
                  <li class="mb-2">Points visitors toward events, maps, FAQs, donations, and account tools.</li>
                  <li class="mb-2">Helps prepare practical questions for park offices and event staff.</li>
                  <li class="mb-0">Keeps answers concise, helpful, and focused on visitor needs.</li>
                </ul>
              </div>
            </div>

            <div class="card border-0 shadow-sm ai-guide-side-card mb-4">
              <div class="card-body p-4">
                <p class="section-label mb-2">Popular questions</p>
                <div class="d-grid gap-2">
                  <button type="button" class="btn btn-outline-success text-start ai-prompt-chip" data-prompt="What parks are good for a low-cost outdoor day trip from New York City?">Low-cost trip from NYC</button>
                  <button type="button" class="btn btn-outline-success text-start ai-prompt-chip" data-prompt="What should I bring for a beach day at a New York State park?">Beach day packing list</button>
                  <button type="button" class="btn btn-outline-success text-start ai-prompt-chip" data-prompt="Which page should I use to find upcoming public park events?">Find event pages</button>
                </div>
              </div>
            </div>

            <div class="card border-0 shadow-sm ai-guide-side-card">
              <div class="card-body p-4">
                <p class="section-label mb-2">Before you go</p>
                <ol class="text-muted small ps-3 mb-0">
                  <li class="mb-2">Choose the park, activity, or event that fits your visit.</li>
                  <li class="mb-2">Review maps, amenities, accessibility, and reservation needs.</li>
                  <li class="mb-2">Check official hours, fees, alerts, and safety guidance.</li>
                  <li class="mb-0">Arrive prepared and follow posted rules at the park.</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
<?php include __DIR__ . '/includes/footer.php'; ?>
