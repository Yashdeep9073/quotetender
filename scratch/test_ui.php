<?php
define('TASK_TEST_MODE', true);
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/..');
session_start();
$_SESSION['login_user'] = 'DVEPL';
$_SESSION['login_user_id'] = 3;

require_once __DIR__ . '/../login/db/config.php';

// Clean old tests
$db->query("DELETE FROM notifications WHERE reference_id = 999");

// Create unread notification
$db->query("INSERT INTO notifications (user_id, type, title, message, reference_type, reference_id) VALUES (3, 'TASK_ASSIGNED', 'UI Test Task', 'This is a test notification for UI.', 'task', 999)");
$notifId = $db->insert_id;
echo "Inserted Notification ID: $notifId\n";

// 1. Verify Unread Count
$stmtCount = $db->prepare("SELECT COUNT(*) AS c FROM notifications WHERE user_id = 3 AND is_read = 0");
$stmtCount->execute();
$countBefore = $stmtCount->get_result()->fetch_assoc()['c'];
echo "Unread Count before read: $countBefore (Should be at least 1)\n";

// 2. Simulate reading notification
$_GET['id'] = $notifId;
$_GET['redirect'] = '../dashboard.php';
// We must simulate read.php logic
require __DIR__ . '/../login/notifications/read.php';

// 3. Verify count decreases
$stmtCount->execute();
$countAfter = $stmtCount->get_result()->fetch_assoc()['c'];
echo "Unread Count after read: $countAfter (Should be " . ($countBefore - 1) . ")\n";

// 4. Simulate mark all as read
// Let's add 2 more unread notifications
$db->query("INSERT INTO notifications (user_id, type, title, message, reference_type, reference_id) VALUES (3, 'TASK_ASSIGNED', 'UI Test Task 2', 'Message 2', 'task', 999)");
$db->query("INSERT INTO notifications (user_id, type, title, message, reference_type, reference_id) VALUES (3, 'TASK_ASSIGNED', 'UI Test Task 3', 'Message 3', 'task', 999)");

$stmtCount->execute();
$countBeforeAll = $stmtCount->get_result()->fetch_assoc()['c'];
echo "Unread Count before Mark All: $countBeforeAll\n";

// Execute read_all.php logic
require __DIR__ . '/../login/notifications/read_all.php';

$stmtCount->execute();
$countAfterAll = $stmtCount->get_result()->fetch_assoc()['c'];
echo "Unread Count after Mark All: $countAfterAll (Should be 0)\n";

// 5. Security - user B cannot mark user A's notification
$_SESSION['login_user_id'] = 19; // User B
$db->query("INSERT INTO notifications (user_id, type, title, message, reference_type, reference_id) VALUES (3, 'TASK_ASSIGNED', 'UI Test Task 4', 'Message 4', 'task', 999)");
$lastNotifId = $db->insert_id;
$_GET['id'] = $lastNotifId;
$_GET['redirect'] = '../dashboard.php';
require __DIR__ . '/../login/notifications/read.php';

// Check if it's still unread for User A (ID 3)
$res = $db->query("SELECT is_read FROM notifications WHERE id = $lastNotifId");
$isRead = $res->fetch_assoc()['is_read'];
echo "Is Read by User B: $isRead (Should be 0)\n";

// Clean up
$db->query("DELETE FROM notifications WHERE reference_id = 999");
echo "Test complete.\n";
