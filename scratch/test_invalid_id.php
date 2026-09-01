<?php
define('TASK_TEST_MODE', true);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/..');

session_start();
$_SESSION['login_user'] = 'DVEPL';

require_once __DIR__ . '/../login/db/config.php';

$_GET['id'] = 999999; // Invalid ID
$_POST = [
    'title' => 'Test Invalid',
    'description' => 'Desc',
    'task_type' => 'General',
    'priority' => 'Medium',
    'status' => 'Pending',
    'start_date' => '2026-09-01',
    'due_date' => '2026-09-05',
    'assigned_to' => '9'
];

echo "Attempting to edit invalid task...\n";
require __DIR__ . '/../login/task-management/edit.php';
echo "Test completed without crashing.\n";
