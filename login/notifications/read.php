<?php
session_start();
require_once __DIR__ . '/../db/config.php';

if (!isset($_SESSION["login_user_id"])) {
    header("Location: ../index.php");
    exit();
}

$userId = (int)$_SESSION['login_user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: ../dashboard.php");
    exit();
}

// Fetch the notification to determine redirect path safely
$stmt = $db->prepare("SELECT reference_type, reference_id FROM notifications WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $id, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Notification not found or belongs to another user
    header("Location: ../dashboard.php");
    exit();
}

$notification = $result->fetch_assoc();
$refType = $notification['reference_type'];
$refId = (int)$notification['reference_id'];

// Mark as read
$stmtUpdate = $db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?");
$stmtUpdate->bind_param('ii', $id, $userId);
$stmtUpdate->execute();

// Determine redirect safely server-side
$redirectUrl = '../dashboard.php';
if ($refType === 'task') {
    $redirectUrl = '../task-management/view.php?id=' . $refId;
}

header("Location: " . $redirectUrl);
exit();
