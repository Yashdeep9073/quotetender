<?php
define('TASK_TEST_MODE', true);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/..');

session_start();
$_SESSION['login_user'] = 'DVEPL';

require_once __DIR__ . '/../login/db/config.php';

echo "Test 1: Create task WITH assignee\n";
$_POST = [
    'title' => 'Test Task 1',
    'description' => 'Desc',
    'task_type' => 'General',
    'priority' => 'Medium',
    'status' => 'Pending',
    'start_date' => '2026-09-01',
    'due_date' => '2026-09-05',
    'assigned_to' => '11'
];
ob_start();
require __DIR__ . '/../login/task-management/create.php';
ob_end_clean();

$res = $db->query("SELECT id FROM tasks ORDER BY id DESC LIMIT 1");
$taskId1 = $res->fetch_row()[0];
echo "- Task created: $taskId1\n";

$res = $db->query("SELECT * FROM notifications WHERE reference_id = $taskId1");
echo "- Notifications generated: " . $res->num_rows . "\n";
if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo "- Notification type: " . $row['type'] . "\n";
}

echo "Test 2: Create task WITHOUT assignee\n";
$_POST['title'] = 'Test Task 2';
$_POST['assigned_to'] = '';
ob_start();
require __DIR__ . '/../login/task-management/create.php';
ob_end_clean();

$res = $db->query("SELECT id FROM tasks ORDER BY id DESC LIMIT 1");
$taskId2 = $res->fetch_row()[0];
echo "- Task created: $taskId2\n";

$res = $db->query("SELECT * FROM notifications WHERE reference_id = $taskId2");
echo "- Notifications generated: " . $res->num_rows . "\n";

echo "Test 3 & 4: Reassign employee (Test 3) and Save without changing (Test 4)\n";
$_GET['id'] = $taskId1;
$_POST = [
    'title' => 'Test Task 1',
    'description' => 'Desc',
    'task_type' => 'General',
    'priority' => 'Medium',
    'status' => 'Pending',
    'start_date' => '2026-09-01',
    'due_date' => '2026-09-05',
    'assigned_to' => '9'
];
ob_start();
require __DIR__ . '/../login/task-management/edit.php';
ob_end_clean();

$res = $db->query("SELECT type FROM notifications WHERE reference_id = $taskId1 AND type = 'TASK_REASSIGNED'");
echo "- Test 3 TASK_REASSIGNED generated: " . $res->num_rows . "\n";
$res = $db->query("SELECT type FROM notifications WHERE reference_id = $taskId1 AND type = 'TASK_UPDATED'");
echo "- Test 3 TASK_UPDATED generated (expect 0): " . $res->num_rows . "\n";

ob_start();
require __DIR__ . '/../login/task-management/edit.php';
ob_end_clean();
$res = $db->query("SELECT type FROM notifications WHERE reference_id = $taskId1 AND type = 'TASK_REASSIGNED'");
echo "- Test 4 Total TASK_REASSIGNED generated (should still be 1): " . $res->num_rows . "\n";

echo "Test 5 & 6: Change status (Test 5) and Save same status (Test 6)\n";
$_POST['action'] = 'status';
$_POST = ['status' => 'In Progress'];
ob_start();
require __DIR__ . '/../login/task-management/view.php';
ob_end_clean();

$res = $db->query("SELECT type FROM notifications WHERE reference_id = $taskId1 AND type = 'TASK_STATUS_CHANGED'");
echo "- Test 5 TASK_STATUS_CHANGED generated: " . $res->num_rows . "\n";

$_POST = ['status' => 'In Progress'];
ob_start();
require __DIR__ . '/../login/task-management/view.php';
ob_end_clean();

$res = $db->query("SELECT type FROM notifications WHERE reference_id = $taskId1 AND type = 'TASK_STATUS_CHANGED'");
echo "- Test 6 Total TASK_STATUS_CHANGED (expect 1): " . $res->num_rows . "\n";

echo "Test 7: Change title only\n";
unset($_POST['action']);
$_POST = [
    'title' => 'Test Task 1 Updated',
    'description' => 'Desc',
    'task_type' => 'General',
    'priority' => 'Medium',
    'status' => 'In Progress',
    'start_date' => '2026-09-01',
    'due_date' => '2026-09-05',
    'assigned_to' => '9'
];
ob_start();
require __DIR__ . '/../login/task-management/edit.php';
ob_end_clean();

$res = $db->query("SELECT type FROM notifications WHERE reference_id = $taskId1 AND type = 'TASK_UPDATED'");
echo "- Test 7 TASK_UPDATED generated: " . $res->num_rows . "\n";

echo "Test 8: Add comment\n";
$_POST['action'] = 'comment';
$_POST = ['comment' => 'This is a test comment'];
ob_start();
require __DIR__ . '/../login/task-management/view.php';
ob_end_clean();

$res = $db->query("SELECT type, user_id FROM notifications WHERE reference_id = $taskId1 AND type = 'TASK_COMMENTED'");
echo "- Test 8 TASK_COMMENTED generated: " . $res->num_rows . "\n";

echo "Test 10: Invalid/nonexistent task ID\n";
$_GET['id'] = 999999;
unset($_POST['action']);
$_POST = [
    'title' => 'Invalid',
    'description' => 'Desc',
    'task_type' => 'General',
    'priority' => 'Medium',
    'status' => 'Pending',
    'start_date' => '2026-09-01',
    'due_date' => '2026-09-05',
    'assigned_to' => '9'
];
ob_start();
require __DIR__ . '/../login/task-management/edit.php';
ob_end_clean();

$res = $db->query("SELECT * FROM notifications WHERE reference_id = 999999");
echo "- Test 10 Notifications generated: " . $res->num_rows . "\n";

$db->query("DELETE FROM notifications WHERE reference_id IN ($taskId1, $taskId2)");
$db->query("DELETE FROM tasks WHERE id IN ($taskId1, $taskId2)");
$db->query("DELETE FROM task_comments WHERE task_id IN ($taskId1, $taskId2)");

echo "Tests Complete.\n";
