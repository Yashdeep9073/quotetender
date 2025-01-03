<?php

session_start();
include("db/config.php");

// tender sent
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $del = "DELETE from admin where username = '$id'";

    $result = mysqli_query($db, $del);
	
    $sql9 = "DELETE from members where member_id= '$id'";
    $result9 = mysqli_query($db, $sql9);

    $sql8 = "UPDATE user_tender_requests set delete_tender = '1' where id= '$id'";
    $result8 = mysqli_query($db, $sql8);
}

// if (isset($_GET['del_id'])){

//     $id = $_GET['del_id'];
//     $sql8 = "UPDATE user_tender_requests set delete_tender = '1' where id= '$id'";
//     $result8 = mysqli_query($db, $sql8);

//     if($result8 ){
//         echo json_encode([
//             "status" => true,
//             "message" => "Tender request deleted successfully."
//         ]);
//     }
// }

// tender request
if(isset($_POST['tender_request_ids'])) {
    $tender_request_ids = trim($_POST['tender_request_ids']);	
    $sql = "UPDATE user_tender_requests set delete_tender = '1' WHERE id in ($tender_request_ids)";
    $resultset = mysqli_query($db, $sql);
    
}

// tender sent 
if(isset($_POST['tender_sent_ids'])) {
    $tender_sent_ids = trim($_POST['tender_sent_ids']);	
    $sql = "UPDATE user_tender_requests set delete_tender = '1' WHERE id in ($tender_sent_ids)";
    $resultset = mysqli_query($db, $sql);
}



if(isset($_POST['alot_request_ids'])) {
	$alot_request_ids = trim($_POST['alot_request_ids']);	
	$sql = "UPDATE user_tender_requests set delete_tender = '1' WHERE id in ($alot_request_ids)";
	$resultset = mysqli_query($db, $sql);
}