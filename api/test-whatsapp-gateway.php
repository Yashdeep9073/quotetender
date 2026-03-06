<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Connection failed',
    ]);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!is_array($input)) {
    $input = $_POST;
}

$apiKey = trim($input['apiKey'] ?? '');
$secretKey = trim($input['secretKey'] ?? '');
$baseUrl = trim($input['baseUrl'] ?? '');

function isValidHttpUrl($url)
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $scheme = parse_url($url, PHP_URL_SCHEME);
    return in_array(strtolower((string) $scheme), ['http', 'https'], true);
}

function generateApiHeaders($apiKey, $apiSecret, $method, $requestPath, $body = '', $isMultipart = false)
{
    $timestamp = (string) round(microtime(true) * 1000);

    $bodyString = '';
    if (!$isMultipart && is_array($body)) {
        $bodyString = json_encode($body, JSON_UNESCAPED_SLASHES);
    }

    if (!$isMultipart && is_string($body)) {
        $bodyString = $body;
    }

    if ($isMultipart) {
        $bodyString = '';
    }

    $bodyHash = hash('sha256', $bodyString);

    $canonical = strtoupper($method) . "\n"
        . $requestPath . "\n"
        . $timestamp . "\n"
        . $bodyHash;

    $signature = hash_hmac('sha256', $canonical, $apiSecret);

    return [
        'x-api-key: ' . $apiKey,
        'x-timestamp: ' . $timestamp,
        'x-signature: ' . $signature,
    ];
}

if ($apiKey === '' || $secretKey === '' || !isValidHttpUrl($baseUrl)) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => 'Connection failed',
    ]);
    exit;
}

$method = 'GET';
$testUrl = $baseUrl;
$parsedPath = parse_url($testUrl, PHP_URL_PATH) ?: '/';
$parsedQuery = parse_url($testUrl, PHP_URL_QUERY);
$requestPath = $parsedPath . ($parsedQuery ? ('?' . $parsedQuery) : '');

$headers = generateApiHeaders($apiKey, $secretKey, $method, $requestPath, '', false);
$headers[] = 'Accept: application/json';

$ch = curl_init($testUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 12,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_HTTPGET => true,
    CURLOPT_HTTPHEADER => $headers,
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlError !== '') {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Connection failed',
    ]);
    exit;
}

if ($httpCode >= 200 && $httpCode < 300 && $response !== false) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Gateway connected successfully',
    ]);
    exit;
}

http_response_code(400);
echo json_encode([
    'status' => 'error',
    'message' => 'Connection failed',
]);
