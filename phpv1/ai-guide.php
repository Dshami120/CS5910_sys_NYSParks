<?php
require_once __DIR__ . '/includes/config.php';

$pageTitle = 'AI Guide | ' . SITE_NAME;
$activePage = 'ai-guide';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

/*
 |--------------------------------------------------------------------------
 | Check whether the API key exists on the server
 |--------------------------------------------------------------------------
 | We only show a simple yes/no status to the user.
 | We never print the actual key to the page.
 */
$apiConfigured = !empty(getenv('OPENAI_API_KEY'));
?>

<!-- PAGE HERO -->
<section class="subpage-hero ai-guide-hero d-flex align-items-center text-white">
    <div class="subpage-hero-overlay"></div>
    <div class="container position-relative py-5">
        <div class="row justify-content-center text-center">
            <div class="col-xl-8 col-lg-10">
                <span class="hero-kicker">AI TRIP PLANNER + SITE GUIDE</span>
                <h1 class="display-5 fw-bold mb-3">Ask the NYS Parks AI Guide</h1>
                <p class="lead text-white-50 mb-0">
                    Get help finding parks, planning outings, comparing destinations,
                    and understanding what this statewide portal offers.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- STATUS + QUICK INFO -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="mini-stat-card h-100">
                    <p class="text-uppercase small fw-bold text-success mb-2">Chatbot status</p>
                    <?php if ($apiConfigured): ?>
                        <h3 class="h5 mb-2"><i class="bi bi-check-circle-fill text-success"></i> Live OpenAI mode ready</h3>
                        <p class="text-muted mb-0">
                            The page detected <code>OPENAI_API_KEY</code> on the server, so the chatbot can make real API calls.
                        </p>
                    <?php else: ?>
                        <h3 class="h5 mb-2"><i class="bi bi-tools text-warning"></i> Setup still needed</h3>
                        <p class="text-muted mb-0">
                            The page is built, but you still need to set <code>OPENAI_API_KEY</code> in your XAMPP/PHP environment
                            before live chatbot answers will work.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="mini-stat-card h-100">
                    <p class="text-uppercase small fw-bold text-success mb-2">What it can do</p>
                    <ul class="text-muted mb-0 ai-guide-mini-list">
                        <li>Suggest parks by region or activity</li>
                        <li>Help plan day trips and weekend outings</li>
                        <li>Answer site-related public questions</li>
                        <li>Guide users to parks, events, FAQ, and map pages</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="mini-stat-card h-100">
                    <p class="text-uppercase small fw-bold text-success mb-2">Capstone note</p>
                    <p class="text-muted mb-0">
                        This version is intentionally simple and heavily commented.
                        It uses a PHP backend endpoint so the API key stays on the server,
                        not in browser JavaScript.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MAIN CHAT AREA -->
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
                        <button type="button" id="clearAiChat" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-clockwise"></i> Clear chat
                        </button>
                    </div>

                    <div class="card-body p-4">
                        <!-- Chat transcript -->
                        <div id="aiChatMessages" class="ai-chat-messages mb-3">
                            <div class="ai-message ai-message-assistant">
                                <div class="ai-message-label">AI Guide</div>
                                <div class="ai-message-bubble">
                                    Hi! I can help you explore New York State parks, compare destinations,
                                    and suggest ideas for hiking, family trips, beaches, waterfalls, and more.
                                </div>
                            </div>
                        </div>

                        <!-- Small helper text under the transcript -->
                        <div id="aiChatStatus" class="small text-muted mb-3">
                            Tip: try one of the suggested prompts below to test the chatbot quickly.
                        </div>

                        <!-- Suggested prompt buttons -->
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <button type="button" class="btn btn-sm btn-outline-success ai-prompt-chip" data-prompt="Suggest a family-friendly state park day trip near Albany.">Family trip near Albany</button>
                            <button type="button" class="btn btn-sm btn-outline-success ai-prompt-chip" data-prompt="Which New York State parks are best for waterfalls and easy hiking?">Waterfalls + easy hiking</button>
                            <button type="button" class="btn btn-sm btn-outline-success ai-prompt-chip" data-prompt="Plan a spring weekend in the Finger Lakes with parks and outdoor activities.">Finger Lakes weekend plan</button>
                            <button type="button" class="btn btn-sm btn-outline-success ai-prompt-chip" data-prompt="What should I bring for a beach day at Jones Beach State Park?">Beach packing list</button>
                        </div>

                        <!-- Chat form -->
                        <form id="aiChatForm" class="row g-2">
                            <div class="col-12">
                                <label for="aiChatInput" class="form-label fw-semibold">Your message</label>
                                <textarea
                                    id="aiChatInput"
                                    class="form-control"
                                    rows="3"
                                    maxlength="800"
                                    placeholder="Example: Help me find a scenic NY state park for a weekend picnic and short hike."
                                    required
                                ></textarea>
                            </div>
                            <div class="col-sm-8 col-lg-9">
                                <div class="form-text">
                                    Keep prompts short and natural. The backend sends your message to a PHP endpoint.
                                </div>
                            </div>
                            <div class="col-sm-4 col-lg-3 d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-send-fill"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 ai-guide-side-card mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 mb-3">How this page works</h3>
                        <ol class="text-muted small ps-3 mb-0">
                            <li class="mb-2">The user types a question in the browser.</li>
                            <li class="mb-2">JavaScript sends it to <code>api/chatbot.php</code>.</li>
                            <li class="mb-2">PHP calls the OpenAI Responses API on the server.</li>
                            <li class="mb-0">The answer is returned to the page and shown in the chat window.</li>
                        </ol>
                    </div>
                </div>

                <div class="card border-0 shadow-sm ai-guide-side-card mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 mb-3">Good questions to ask</h3>
                        <ul class="text-muted small mb-0 ps-3">
                            <li class="mb-2">Which parks are best for families with young kids?</li>
                            <li class="mb-2">What region has the best waterfall hikes?</li>
                            <li class="mb-2">Plan a low-cost outdoor day trip from NYC.</li>
                            <li class="mb-2">What pages on this site should I visit for events and maps?</li>
                            <li class="mb-0">Compare beach parks versus mountain parks in NY.</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm ai-guide-side-card">
                    <div class="card-body p-4">
                        <h3 class="h5 mb-3">Important note</h3>
                        <p class="text-muted small mb-0">
                            This is a public-facing assistant demo for your capstone site.
                            Later, we can connect it to your real parks/events database so it answers
                            using live site data instead of only a general guide prompt.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
