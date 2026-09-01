<?php
define('TASK_TEST_MODE', true);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/..');

session_start();
$_SESSION['login_user'] = 'admin'; // Assume there is an admin user

require_once __DIR__ . '/../login/db/config.php';

// Clean DB for tests
$db->query("DELETE FROM notifications WHERE reference_id IN (9991, 9992)");
$db->query("DELETE FROM tasks WHERE id IN (9991, 9992)");

// 1. Create task with employee
$_POST = [
    'title' => 'Test Task 1',
    'description' => 'Desc',
    'task_type' => 'General',
    'priority' => 'Medium',
    'status' => 'Pending',
    'assigned_to' => '19' // assuming 19 exists
];
// Mock insert ID using a trick? The file executes insert, we just have to check the highest task.
ob_start();
require __DIR__ . '/../login/task-management/create.php';
ob_end_clean();

$res = $db->query("SELECT id FROM tasks ORDER BY id DESC LIMIT 1");
$taskId1 = $res->fetch_row()[0];
echo "Created task ID: $taskId1\n";

$res = $db->query("SELECT * FROM notifications WHERE reference_id = $taskId1 AND type = 'TASK_ASSIGNED'");
echo "Test 1 TASK_ASSIGNED count: " . $res->num_rows . "\n";

// 2. Create task without employee
$_POST['assigned_to'] = '';
ob_start();
require __DIR__ . '/../login/task-management/create.php'; // wait, require again might redefine functions
ob_end_clean();
// Cannot require the same file twice if it declares functions, but create.php doesn't declare functions.
