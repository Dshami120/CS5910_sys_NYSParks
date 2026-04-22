<?php
/*
 |--------------------------------------------------------------------------
 | Simple AI chatbot endpoint for the NYS Parks capstone project
 |--------------------------------------------------------------------------
 | Frontend page: ai-guide.php
 | Frontend JS: assets/js/main.js
 |
 | This file is called by fetch() from the browser, but the OpenAI request
 | happens here on the server so the API key stays secret.
 */

header('Content-Type: application/json; charset=utf-8');

// Only allow POST requests to keep the endpoint simple and predictable.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed.'
    ]);
    exit;
}

$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput, true);

$message = trim((string) ($payload['message'] ?? ''));
$history = $payload['history'] ?? [];

if ($message === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a message before sending.'
    ]);
    exit;
}

if (mb_strlen($message) > 800) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Please keep the message under 800 characters.'
    ]);
    exit;
}

/*
 |--------------------------------------------------------------------------
 | Build a small conversation list
 |--------------------------------------------------------------------------
 | We keep only recent messages and only allow user/assistant roles.
 | This keeps the request smaller and easier to explain in class.
 */
$conversation = [];
$systemPrompt = <<<PROMPT
You are the NYS Parks AI Guide for a capstone student website.

Your job:
- Help visitors explore New York State parks, outdoor destinations, and public-facing site features.
- Give helpful suggestions for parks, activities, planning, family trips, hiking, beaches, waterfalls, picnics, and events.
- Keep answers clear, friendly, and practical.
- If the user asks something outside the website's scope, politely redirect them back toward parks, recreation, events, or site navigation.
- Do not invent official park hours, reservation rules, or fees if you are unsure.
- When details may vary by park, say that users should verify current details with the park or official listings.
- Keep most answers between 2 and 6 short paragraphs or concise bullet-style sentences.
PROMPT;

$conversation[] = [
    'role' => 'system',
    'content' => [
        [
            'type' => 'input_text',
            'text' => $systemPrompt
        ]
    ]
];

if (is_array($history)) {
    $history = array_slice($history, -6);

    foreach ($history as $item) {
        $role = $item['role'] ?? '';
        $content = trim((string) ($item['content'] ?? ''));

        if (!in_array($role, ['user', 'assistant'], true)) {
            continue;
        }

        if ($content === '') {
            continue;
        }

        $conversation[] = [
            'role' => $role,
            'content' => [
                [
                    'type' => 'input_text',
                    'text' => mb_substr($content, 0, 1200)
                ]
            ]
        ];
    }
}

$conversation[] = [
    'role' => 'user',
    'content' => [
        [
            'type' => 'input_text',
            'text' => $message
        ]
    ]
];

$apiKey = getenv('OPENAI_API_KEY');

/*
 |--------------------------------------------------------------------------
 | Fallback mode
 |--------------------------------------------------------------------------
 | If the project does not have a real API key yet, return a demo answer.
 | This helps your page still function during local testing in class.
 */
if (!$apiKey) {
    echo json_encode([
        'success' => true,
        'mode' => 'demo',
        'reply' => buildDemoReply($message)
    ]);
    exit;
}

if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'PHP cURL is not enabled on this server.'
    ]);
    exit;
}

$requestBody = [
    // The official quickstart uses the Responses API.
    'model' => 'gpt-5.4',
    'input' => $conversation
];

$ch = curl_init('https://api.openai.com/v1/responses');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_POSTFIELDS => json_encode($requestBody),
    CURLOPT_TIMEOUT => 45
]);

$responseBody = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($responseBody === false || $curlError) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => 'The server could not reach the OpenAI API right now.',
        'details' => $curlError ?: 'Unknown cURL error.'
    ]);
    exit;
}

$decoded = json_decode($responseBody, true);

if ($httpCode >= 400) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => false,
        'message' => $decoded['error']['message'] ?? 'OpenAI API request failed.'
    ]);
    exit;
}

$reply = extractResponseText($decoded);

if ($reply === '') {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'The chatbot returned an empty response.'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'mode' => 'live',
    'reply' => $reply
]);
exit;


/*
 |--------------------------------------------------------------------------
 | Helper: extract text from the Responses API payload
 |--------------------------------------------------------------------------
 | We try a direct field first, then fall back to searching nested output.
 */
function extractResponseText(array $data): string
{
    if (!empty($data['output_text']) && is_string($data['output_text'])) {
        return trim($data['output_text']);
    }

    $parts = [];

    if (!empty($data['output']) && is_array($data['output'])) {
        foreach ($data['output'] as $item) {
            if (!empty($item['content']) && is_array($item['content'])) {
                foreach ($item['content'] as $contentPiece) {
                    if (!empty($contentPiece['text']) && is_string($contentPiece['text'])) {
                        $parts[] = trim($contentPiece['text']);
                    }
                }
            }
        }
    }

    return trim(implode("\n\n", array_filter($parts)));
}


/*
 |--------------------------------------------------------------------------
 | Helper: lightweight demo fallback
 |--------------------------------------------------------------------------
 | This is NOT AI. It is just a simple class-demo backup response.
 */
function buildDemoReply(string $message): string
{
    $text = mb_strtolower($message);

    if (str_contains($text, 'waterfall') || str_contains($text, 'hike')) {
        return "Demo mode: For waterfalls and scenic hiking, a great starting point is Watkins Glen State Park or Letchworth State Park. Both are strong picks for dramatic views, walking trails, and photography. Once your real API key is added, this answer will come from the live OpenAI chatbot.";
    }

    if (str_contains($text, 'beach') || str_contains($text, 'ocean')) {
        return "Demo mode: For a beach-style outing, Jones Beach State Park is an easy example to highlight on this capstone site. You could also compare it with other waterfront parks depending on whether the user wants swimming, boardwalk access, or a family picnic atmosphere.";
    }

    if (str_contains($text, 'family') || str_contains($text, 'kids')) {
        return "Demo mode: For families, the chatbot can recommend parks with easy walking, picnic areas, open space, and nearby restrooms or visitor amenities. In the final version, we can connect this page to your parks database so the answers are based on your real site content.";
    }

    if (str_contains($text, 'event')) {
        return "Demo mode: This assistant can help visitors discover upcoming events, compare parks, and decide which public pages to visit next. In the full build, we can also connect it to your live Events table so it answers with dynamic event data.";
    }

    return "Demo mode: The AI Guide page is built and the backend endpoint is working, but a real OpenAI API key still needs to be added on the server. After that, this page will send live chatbot requests through PHP instead of answering with demo fallback text.";
}
