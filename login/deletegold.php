<?php

session_start();
include("db/config.php");

$id = $_GET['id'];

$sql = "DELETE from category where category_id = '$id'";
$result = mysqli_query($db, $sql);

$sql1 = "DELETE from brand where brand_id = '$id'";
$result1 = mysqli_query($db, $sql1);
$sql2 = "DELETE from department where department_id = '$id'";
$result2 = mysqli_query($db, $sql2);

$sql3 = "DELETE from price_list where price_id = '$id'";
$result3 = mysqli_query($db, $sql3);


$sql4 = "DELETE from banner where banner_id = '$id'";
$result4 = mysqli_query($db, $sql4);

$sql5 = "DELETE from web_content where cont_id = '$id'";
$result5 = mysqli_query($db, $sql5);

$sql6 = "DELETE from members where member_id= '$id'";
$result6 = mysqli_query($db, $sql6);

$sql7 = "DELETE from section where section_id= '$id'";
$result7 = mysqli_query($db, $sql7);

$sql8 = "DELETE from sub_division where id= '$id'";
$result8 = mysqli_query($db, $sql8);


$sql9 = "DELETE from  division  where division_id= '$id'";
$result9 = mysqli_query($db, $sql9);

