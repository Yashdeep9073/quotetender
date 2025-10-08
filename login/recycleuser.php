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

if (isset($_GET['makeaward_id'])) {
    try {
        $id = $_GET['makeaward_id'];
        $remark = "accepted";
        $updatedAt = (new DateTime())->format('Y-m-d H:i:s'); // ✅ Convert to string

        $stmtUpdate = $db->prepare("UPDATE user_tender_requests set  remark=?, remarked_at=? WHERE id = ?");
        $stmtUpdate->bind_param(
            "ssi",
            $remark,
            $updatedAt,
            $id
        );

        if (!$stmtUpdate->execute()) {
            throw new Exception($stmtUpdate->error);
        }
        echo json_encode([
            "status" => 200,
            "message" => "Testing",
            "data" => $_GET,

        ]);
        exit;
    } catch (\Throwable $th) {
        echo json_encode([
            "status" => 500,
            "error" => $th->getMessage(),
        ]);
        exit;
    }
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
if (isset($_POST['tender_request_ids'])) {
    $tender_request_ids = trim($_POST['tender_request_ids']);
    $sql = "UPDATE user_tender_requests set delete_tender = '1' WHERE id in ($tender_request_ids)";
    $resultset = mysqli_query($db, $sql);

}

// tender sent 
if (isset($_POST['tender_sent_ids'])) {
    $tender_sent_ids = trim($_POST['tender_sent_ids']);
    $sql = "UPDATE user_tender_requests set delete_tender = '1' WHERE id in ($tender_sent_ids)";
    $resultset = mysqli_query($db, $sql);
}



if (isset($_POST['alot_request_ids'])) {
    $alot_request_ids = trim($_POST['alot_request_ids']);
    $sql = "UPDATE user_tender_requests set delete_tender = '1' WHERE id in ($alot_request_ids)";
    $resultset = mysqli_query($db, $sql);
}

if (isset($_POST['alot_request_ids_bulk'])) {
    try {
        $ids = explode(',', trim($_POST['alot_request_ids_bulk'])); // convert "100,104,155" to [100,104,155]
        $ids = array_filter($ids, 'is_numeric'); // keep only numeric IDs for safety

        if (empty($ids)) {
            echo json_encode(["status" => 400, "error" => "No valid IDs provided"]);
            exit;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "UPDATE user_tender_requests SET delete_tender = '1' WHERE id IN ($placeholders)";
        $stmt = $db->prepare($sql);

        // Create dynamic bind_param string (e.g. 'iii' for 3 integers)
        $types = str_repeat('i', count($ids));

        $stmt->bind_param($types, ...$ids);
        $stmt->execute();

        echo json_encode([
            "status" => 200,
            "message" => "Deleted successfully",
            "affected_rows" => $stmt->affected_rows,
        ]);
    } catch (Throwable $th) {
        echo json_encode([
            "status" => 500,
            "error" => $th->getMessage(),
        ]);
    }
}


if (isset($_POST['award_request_ids'])) {
    $award_request_ids = trim($_POST['award_request_ids']);
    $sql = "UPDATE user_tender_requests set delete_tender = '1' WHERE id in ($award_request_ids)";
    $resultset = mysqli_query($db, $sql);
}