<?php
// $ipAddress = $_SERVER['REMOTE_ADDR'];
// Your IP address to lookup
// $ipAddress = '2405:201:5023:4015:95b9:512e:fb75:426b'; // Replace with the IP address you want to look up

// Your API Key from ipinfo.io (you can use a free tier key or subscribe for more features)
$accessToken = 'c922e696cae131'; // Replace with your ipinfo.io token

// IPinfo API endpoint
$url = "http://ipinfo.io/{$ipAddress}/json?token={$accessToken}";

// Initialize a cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Execute the cURL session and get the response
$response = curl_exec($ch);

// Close cURL session
curl_close($ch);

// Decode the JSON response
$data = json_decode($response, true);

// Output the location data
echo "<pre>";
print_r($data);
echo $data['ip'];
echo "</pre>";
?>
