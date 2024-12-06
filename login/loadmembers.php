<?php
session_start();

include("db/config.php");

mysqli_select_db($db, DB_NAME);
$result1 = mysqli_query($db, "SELECT COUNT(id) FROM user_tender_requests WHERE status='Allotted'");
$row = mysqli_fetch_array($result1);

if ($row > 0) {

    $total1 = $row[0];

    echo $total1;
}