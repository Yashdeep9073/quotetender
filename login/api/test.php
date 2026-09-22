<?php


require("../../env.php");

$token = getenv("TOKEN");
$secretKey = getenv("SECRET_KEY");

// Keep compatibility with your current env typo,
// but prefer SERVER_URL if you add the corrected variable later.
$serverUrl = getenv("SERVER_URL") ?: getenv("SEVER_URL");

if (!$token || !$secretKey || !$serverUrl) {
    die("Missing TOKEN, SECRET_KEY or SERVER_URL/SEVER_URL in env.php");
}

$timestamp = time();

$signature = hash_hmac(
    'sha256',
    $token . $timestamp,
    $secretKey
);

$query = http_build_query([
    'token' => $token,
    'ts'    => $timestamp,
    'sig'   => $signature,
]);

$apis = [

    // Existing Tender API
    'Award Tenders' => '/login/api/awardTenders.php',

    // Staff / Admin API
    'Staff' => '/login/api/staff.php',

    // Members / Customers API
    'Customers' => '/login/api/customers.php',

    // Roles API
    'Roles' => '/login/api/roles.php',

    // Permissions API
    'Permissions' => '/login/api/permissions.php',
];

header('Content-Type: text/plain; charset=utf-8');

foreach ($apis as $name => $path) {

    $apiUrl = rtrim($serverUrl, '/') . $path . '?' . $query;

    echo $name . " API\n";
    echo $apiUrl . "\n\n";
}

// // Initialize cURL
// $ch = curl_init();

// // Set cURL options
// curl_setopt($ch, CURLOPT_URL, $apiUrl);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response instead of printing
// curl_setopt($ch, CURLOPT_HTTPGET, true); // HTTP GET request

// // Execute cURL request
// $response = curl_exec($ch);

// // Check for errors
// if (curl_errno($ch)) {
//     echo "cURL Error: " . curl_error($ch);
// } else {
//     // Convert JSON response to PHP array
//     $awardTenders = json_decode($response, true);
// }

?>