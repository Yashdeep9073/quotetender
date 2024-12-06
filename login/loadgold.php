<?php
session_start();

include("db/config.php");

mysqli_select_db($db, DB_NAME);
$result1 = mysqli_query($db, "select count(1) FROM members");
$row = mysqli_fetch_array($result1);

if ($row > 0) {

    $total = $row[0];

    echo $total;
}