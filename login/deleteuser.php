<?php

session_start();
include("db/config.php");


if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $del = "DELETE from admin where username = '$id'";

    $result = mysqli_query($db, $del);
	
    $sql9 = "DELETE from members where member_id= '$id'";
    $result9 = mysqli_query($db, $sql9);

    $sql8 = "DELETE from user_tender_requests where id= '$id'";
    $result8 = mysqli_query($db, $sql8);
}
// tender request
if(isset($_POST['tender_request_ids'])) {
	$tender_request_ids = trim($_POST['tender_request_ids']);	
	$sql = "DELETE FROM user_tender_requests WHERE id in ($tender_request_ids)";
	$resultset = mysqli_query($db, $sql);
	
}
// tender sent
if(isset($_POST['tender_sent_ids'])) {
	$tender_sent_ids = trim($_POST['tender_sent_ids']);	
	$sql = "DELETE FROM user_tender_requests WHERE id in ($tender_sent_ids)";
	$resultset = mysqli_query($db, $sql);
}


// tender alot
if(isset($_POST['alot_request_ids'])) {
	$alot_request_ids = trim($_POST['alot_request_ids']);	
	$sql = "DELETE FROM user_tender_requests WHERE id in ($alot_request_ids)";
	$resultset = mysqli_query($db, $sql);
}

if(isset($_POST['tender_recycle_ids'])) {
	$tender_recycle_ids = trim($_POST['tender_recycle_ids']);	
	$sql = "DELETE FROM user_tender_requests WHERE id in ($tender_recycle_ids)";
	$resultset = mysqli_query($db, $sql);
	
}

if(isset($_POST['member_ids'])) {
	$member_ids = trim($_POST['member_ids']);	
	$sql = "DELETE FROM members WHERE member_id in ($member_ids)";
	$resultset = mysqli_query($db, $sql);
	echo $member_ids;
}



