<?php
// Your original date and time string
$originalTime = '2023-07-31 13:00:00'; // Example: '2023-07-31 15:00:00'

// The original time zone offset in hours (e.g., UTC is 0, EST is -5, etc.)
$originalTimeZoneOffset = 0; // Adjust as needed

// Calculate the IST offset from UTC (IST is UTC+5:30)
$istOffset = 5.5;

// Convert the original time to a timestamp
$timestamp = strtotime($originalTime);

// Calculate the difference in seconds
$timeDifference = ($istOffset - $originalTimeZoneOffset) * 3600;

// Adjust the timestamp to IST
$istTimestamp = $timestamp + $timeDifference;

// Format the adjusted timestamp to the desired format
$formattedTime = date('Y-m-d h:i A', $istTimestamp);

echo "Original Time: $originalTime (Timezone Offset: $originalTimeZoneOffset hours)<br>";
echo "Converted Time: $formattedTime (IST)";
?>
