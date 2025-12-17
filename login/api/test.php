<?php


require("../../env.php");

$token = getenv("TOKEN");
$secretKey = getenv("SECRET_KEY");
$timestamp = time();
$serverUrl = getenv("SEVER_URL");
$signature = hash_hmac('sha256', $token . $timestamp, $secretKey);
$apiUrl = $serverUrl . "/login/api/awardTenders.php?token=" . urlencode($token) . "&ts=" . $timestamp
    . "&sig=" . $signature;

// echo $apiUrl;

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