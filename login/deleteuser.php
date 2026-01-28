<?php

session_start();
include("db/config.php");
require "./utility/referenceCodeGenerator.php";


if (isset($_GET['id'])) {
	$id = $_GET['id'];

	$del = "DELETE from admin where username = '$id'";

	$result = mysqli_query($db, $del);

	$sql9 = "DELETE from members where member_id= '$id'";
	$result9 = mysqli_query($db, $sql9);


	$stmtFetchTender = $db->prepare("Select tenderID,reference_code From user_tender_requests WHERE id = ?");
	$stmtFetchTender->bind_param("i", $id);
	$stmtFetchTender->execute();

	$tenderData = $stmtFetchTender->get_result()->fetch_array(MYSQLI_ASSOC);
	// Reference Code Logs 
	logReferenceCodeEvent(
		$db,
		$tenderData['tenderID'],
		$tenderData['reference_code'],
		null,
		"DELETED",
		"Reference code delete with tender id",
		$_SESSION['login_user'] ?? null
	);

	$sql8 = "DELETE from user_tender_requests where id= '$id'";
	$result8 = mysqli_query($db, $sql8);
}
// tender request
if (isset($_POST['tender_request_ids'])) {
	$tender_request_ids = trim($_POST['tender_request_ids']);
	$sql = "DELETE FROM user_tender_requests WHERE id in ($tender_request_ids)";
	$resultset = mysqli_query($db, $sql);

}
// tender sent
if (isset($_POST['tender_sent_ids'])) {
	$tender_sent_ids = trim($_POST['tender_sent_ids']);
	$sql = "DELETE FROM user_tender_requests WHERE id in ($tender_sent_ids)";
	$resultset = mysqli_query($db, $sql);
}


// tender alot
if (isset($_POST['alot_request_ids'])) {
	$alot_request_ids = trim($_POST['alot_request_ids']);
	$sql = "DELETE FROM user_tender_requests WHERE id in ($alot_request_ids)";
	$resultset = mysqli_query($db, $sql);
}

if (isset($_POST['tender_recycle_ids'])) {

	try {
		$ids = $_POST['tender_recycle_ids'];

		// Convert to array & sanitize
		$idArray = array_filter(array_map('intval', explode(',', $ids)));

		if (empty($idArray)) {
			echo json_encode([
				"status" => 400,
				"error" => "No valid tender IDs provided"
			]);
			exit;
		}

		$placeholders = implode(',', array_fill(0, count($idArray), '?'));
		$types = str_repeat('i', count($idArray));

		$db->begin_transaction();

		// 1️⃣ Fetch tender data BEFORE delete (for logs)
		$stmtFetch = $db->prepare("
            SELECT id, tenderID, reference_code
            FROM user_tender_requests
            WHERE id IN ($placeholders)
        ");
		$stmtFetch->bind_param($types, ...$idArray);
		$stmtFetch->execute();
		$result = $stmtFetch->get_result();

		$tenders = $result->fetch_all(MYSQLI_ASSOC);

		// 2️⃣ Log each deletion
		foreach ($tenders as $tender) {
			logReferenceCodeEvent(
				$db,
				$tender['tenderID'],
				$tender['reference_code'],
				null,
				"DELETED",
				"Reference code deleted during bulk tender deletion",
				$_SESSION['login_user'] ?? null
			);
		}

		// 3️⃣ Delete tenders
		$stmtDelete = $db->prepare("
            DELETE FROM user_tender_requests
            WHERE id IN ($placeholders)
        ");
		$stmtDelete->bind_param($types, ...$idArray);
		$stmtDelete->execute();

		$db->commit();

		echo json_encode([
			"status" => 200,
			"message" => "Selected tenders deleted successfully"
		]);
		exit;

	} catch (\Throwable $th) {
		$db->rollback();
		echo json_encode([
			"status" => 500,
			"error" => "Bulk delete failed: " . $th->getMessage()
		]);
		exit;
	}
}


if (isset($_POST['member_ids'])) {
	$member_ids = trim($_POST['member_ids']);
	$sql = "DELETE FROM members WHERE member_id in ($member_ids)";
	$resultset = mysqli_query($db, $sql);
	echo $member_ids;
}



