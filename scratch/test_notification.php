<?php
// Mock PHP environment for testing NotificationService
session_start();
$_SESSION['login_user'] = 'admin'; // mock

require_once __DIR__ . '/../login/db/config.php';
require_once __DIR__ . '/../login/service/NotificationService.php';

$service = new NotificationService($db);

// Clean up before test
$db->query("DELETE FROM notifications WHERE reference_id = 9999");

echo "Test 1: createInAppNotification directly\n";
$service->createInAppNotification(19, 'TEST', 'Test Title', 'Test Msg', 'task', 9999);
$res = $db->query("SELECT * FROM notifications WHERE reference_id = 9999");
echo "DB rows created: " . $res->num_rows . "\n";

echo "Test 2: notifyTaskAssigned (triggers email and in-app)\n";
$service->notifyTaskAssigned(9999, 19, 'Test Task Setup');
$res = $db->query("SELECT * FROM notifications WHERE reference_id = 9999 AND type = 'TASK_ASSIGNED'");
echo "DB TASK_ASSIGNED rows: " . $res->num_rows . "\n";
if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo "Title: " . $row['title'] . "\n";
    echo "Reference ID: " . $row['reference_id'] . "\n";
}

// Clean up
$db->query("DELETE FROM notifications WHERE reference_id = 9999");
echo "Done.\n";
