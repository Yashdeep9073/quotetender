<?php
define('TASK_TEST_MODE', true);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/..');

session_start();
$_SESSION['login_user'] = 'DVEPL';

require_once __DIR__ . '/../login/db/config.php';

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
require __DIR__ . '/../login/task-management/create.php';
if (!empty($errors)) var_dump($errors);
echo "DB Error: " . $db->error . "\n";
