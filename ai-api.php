<?php
declare(strict_types=1);

// Configure OpenAI for the AI Guide.
define('OPENAI_API_KEY', 'PASTE_YOUR_OPENAI_API_KEY_HERE'); //rmv dots from s.k. -> s.k.-proj-4zXxhGuBvwn2NLANye4FVNZ0fr-nPiL9phXA4beV5WzuUGIM_QTEKmJ8baEmwelqMkDOs70YieT3BlbkFJ67d5gHtynTukh1rlXTYNS_6_T1urVjp35luQLBZ317MyhiyslJp3YYh7tXPEj-gIaHFDyYvaEA
define('OPENAI_MODEL', 'gpt-4.1-mini');

// Only accept chat POST requests.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    return;
}

header('Content-Type: application/json; charset=utf-8');

// Send one JSON response and stop.
function ai_json_response(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// Read API key from environment first.
function ai_configured_key(): string {
    $key = getenv('OPENAI_API_KEY') ?: OPENAI_API_KEY;
    $key = trim((string) $key);
    if ($key === '' || $key === 'PASTE_YOUR_OPENAI_API_KEY_HERE') {
        return '';
    }
    return $key;
}

// Extract assistant text from API response.
function ai_text_from_response(array $response): string {
    if (!empty($response['output_text']) && is_string($response['output_text'])) {
        return trim($response['output_text']);
    }

    $parts = [];
    foreach (($response['output'] ?? []) as $outputItem) {
        foreach (($outputItem['content'] ?? []) as $contentItem) {
            if (!empty($contentItem['text']) && is_string($contentItem['text'])) {
                $parts[] = $contentItem['text'];
            }
        }
    }
    return trim(implode("\n", $parts));
}

// Build site-specific AI context.
function ai_site_context(): string {
    $context = "Official NYS Parks reference points: New York State Parks, Recreation and Historic Preservation provides safe and enjoyable recreational and interpretive opportunities while stewarding natural, historic, and cultural resources. The statewide system includes more than 250 parks, historic sites, trails, golf courses, boat launches, and campgrounds across more than 360,000 acres. Site pages in this application: Parks, Events, Map, News, FAQ, Donate, Login/Register, Client Dashboard, Employee Dashboard, and Admin Dashboard.";

    try {
        require_once 'bootstrap.php';
        $db = db();

        // Add park records to AI context.
        $parks = $db->query("SELECT name, region, city, park_type, amenities FROM parks ORDER BY is_featured DESC, name LIMIT 30")->fetchAll();
        if ($parks) {
            $parkLines = array_map(static function (array $park): string {
                return sprintf(
                    '- %s (%s, %s): %s; amenities: %s',
                    $park['name'] ?? 'Park',
                    $park['city'] ?? 'NY',
                    $park['region'] ?? 'New York',
                    $park['park_type'] ?? 'State Park',
                    $park['amenities'] ?? 'See park details'
                );
            }, $parks);
            $context .= "\n\nParks currently in this site database:\n" . implode("\n", $parkLines);
        }

        // Add public events to AI context.
        $events = $db->query("SELECT e.title, e.category, e.start_datetime, p.name AS park_name FROM events e LEFT JOIN parks p ON p.id = e.park_id WHERE e.event_status = 'published' AND e.event_type = 'public' ORDER BY e.start_datetime ASC LIMIT 20")->fetchAll();
        if ($events) {
            $eventLines = array_map(static function (array $event): string {
                return sprintf(
                    '- %s at %s on %s (%s)',
                    $event['title'] ?? 'Event',
                    $event['park_name'] ?? 'a NYS park',
                    $event['start_datetime'] ?? 'date TBD',
                    $event['category'] ?? 'event'
                );
            }, $events);
            $context .= "\n\nPublished events currently in this site database:\n" . implode("\n", $eventLines);
        }
    } catch (Throwable $e) {
        $context .= "\n\nDatabase context is temporarily unavailable, so answer using general NYS Parks guidance and direct users to the Parks, Events, Map, and FAQ pages.";
    }

    return $context;
}

// Stop when key is missing.
$key = ai_configured_key();
if ($key === '') {
    ai_json_response([
        'ok' => false,
        'error' => 'The AI Guide is temporarily unavailable. Please try again later.'
    ], 200);
}

// Decode and validate request JSON.
$rawBody = file_get_contents('php://input') ?: '';
$data = json_decode($rawBody, true);
if (!is_array($data)) {
    ai_json_response(['ok' => false, 'error' => 'Invalid JSON request.'], 400);
}

// Validate visitor message.
$message = trim((string) ($data['message'] ?? ''));
if ($message === '') {
    ai_json_response(['ok' => false, 'error' => 'Please enter a message first.'], 400);
}
if (strlen($message) > 800) {
    ai_json_response(['ok' => false, 'error' => 'Please keep your message under 800 characters.'], 400);
}

// Include short conversation history.
$history = [];
if (!empty($data['history']) && is_array($data['history'])) {
    foreach (array_slice($data['history'], -6) as $item) {
        $role = ($item['role'] ?? '') === 'assistant' ? 'Assistant' : 'User';
        $text = trim((string) ($item['text'] ?? ''));
        if ($text !== '') {
            $history[] = $role . ': ' . substr($text, 0, 600);
        }
    }
}

// Compose the AI input.
$conversationInput = '';
if ($history) {
    $conversationInput .= "Recent conversation:\n" . implode("\n", $history) . "\n\n";
}
$conversationInput .= "Current user question:\n" . $message;

// Prepare OpenAI request payload.
$payload = [
    'model' => OPENAI_MODEL,
    'instructions' => "You are the NYS Parks virtual guide for a public-facing parks website. Write in a polished, professional, visitor-friendly tone. Help users explore parks, compare destinations, plan visits, find events, understand private event request steps, use maps, and navigate the website. Use the provided site/database context when relevant. Keep answers concise, practical, and action-oriented. For hours, fees, closures, permits, accessibility details, swimming conditions, safety rules, or other time-sensitive official information, tell users to verify with the official NYS Parks website or the specific park office before visiting. Do not claim to complete bookings, payments, official reservations, permit approvals, or emergency services.\n\n" . ai_site_context(),
    'input' => $conversationInput,
    'max_output_tokens' => 500,
];

// Verify cURL is enabled.
if (!function_exists('curl_init')) {
    ai_json_response(['ok' => false, 'error' => 'PHP cURL is not enabled on this server. Enable cURL in PHP to call OpenAI.'], 500);
}

// Call OpenAI Responses API.
$ch = curl_init('https://api.openai.com/v1/responses');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $key,
    ],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT => 30,
]);

$responseBody = curl_exec($ch);
$curlError = curl_error($ch);
$statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Handle transport errors.
if ($responseBody === false) {
    ai_json_response(['ok' => false, 'error' => 'Could not reach OpenAI: ' . $curlError], 502);
}

// Parse OpenAI JSON response.
$response = json_decode($responseBody, true);
if (!is_array($response)) {
    ai_json_response(['ok' => false, 'error' => 'OpenAI returned an unreadable response.'], 502);
}

// Return API errors safely to UI.
if ($statusCode < 200 || $statusCode >= 300) {
    $apiError = $response['error']['message'] ?? 'OpenAI request failed.';
    ai_json_response(['ok' => false, 'error' => $apiError], 200);
}

// Send final assistant reply.
$reply = ai_text_from_response($response);
if ($reply === '') {
    $reply = 'I could not generate a response this time. Please try again.';
}

ai_json_response(['ok' => true, 'reply' => $reply]);
