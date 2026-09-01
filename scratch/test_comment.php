<?php
define('TASK_TEST_MODE', true);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/..');

session_start();
$_SESSION['login_user'] = 'DVEPL';

require_once __DIR__ . '/../login/db/config.php';

$_GET['id'] = 12;
$_GET['action'] = 'comment';
$_POST['comment'] = 'This is a single comment test';

require __DIR__ . '/../login/task-management/view.php';
echo "Done comment test.\n";
