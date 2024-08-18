<?php


header('Content-type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Send a 405 Method Not Allowed HTTP response
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Please use POST.']);
    exit;
}

// Get the JSON payload from the request body
$json = file_get_contents('php://input');

// Decode the JSON payload into an associative array
$data = json_decode($json, true);

$feedbackSessionId = $data['session_uuid'] ?? null;

if (!isset($feedbackSessionId)) {
    echo json_encode(['error' => 'feedback_session_id is required']);
    exit;
}

$permission = $data['permission'] ?? 'RW';

function post(string $url, $options = [])
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($options['payload']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL session and fetch the response
    $response = curl_exec($ch);

    // Get the HTTP status code of the response
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Close the cURL session
    curl_close($ch);

    // Check the status code and return the response if it's 200
    if ($httpcode == 200) {
        return json_decode($response, true);
    }

    return $response;
}

$consumer_key = 'lok-dEPDmOP1FlGt3Txf8EVEgo1j9cmSXEHE';
$consumer_secret = 'los-Otd2GXl6QxdHrNMHdVtvr2XOj6bYkVezEgVQgMBiFAF788FdVM2311N46jCy';

// Load token
$credentials = base64_encode("$consumer_key:$consumer_secret");

// Create the Authorization header
$authHeader = "Authorization: Basic $credentials";

$tokenResponse = post('https://feedbackaide.staging.learnosity.com/api/token', [
    'payload' => [
        'grant_type' => 'client_credentials',
        'scope' => "api:feedbackaide feedback_session_id:$feedbackSessionId:$permission"
    ],
    'headers' => [
        $authHeader,
        'Content-Type: application/json'
    ]
]);

echo json_encode([
    'security' => $tokenResponse,
    'session_uuid' => $feedbackSessionId
]);
