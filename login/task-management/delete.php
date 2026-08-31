<?php
/**
 * Delete a task. POST-only, permission-gated, server-side validation.
 * task_comments and task_history are removed by ON DELETE CASCADE foreign keys.
 */

require_once __DIR__ . '/inc/init.php';

if (!$taskCanDelete) {
    task_redirect('index.php', 'danger', 'You do not have permission to delete tasks.');
    return;
}

// Deletion must never be an unrestricted destructive GET.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    task_redirect('index.php', 'danger', 'Invalid request.');
    return;
}

$taskId = task_get_int(isset($_POST['id']) ? $_POST['id'] : null);
if ($taskId === false) {
    task_redirect('index.php', 'danger', 'Invalid task ID.');
}

$stmtCheck = $db->prepare("SELECT id, title FROM tasks WHERE id = ?");
$stmtCheck->bind_param('i', $taskId);
$stmtCheck->execute();
$taskRow = $stmtCheck->get_result()->fetch_assoc();
if (!$taskRow) {
    task_redirect('index.php', 'danger', 'Task not found.');
}

$stmtDelete = $db->prepare("DELETE FROM tasks WHERE id = ?");
$stmtDelete->bind_param('i', $taskId);
if ($stmtDelete->execute() && $stmtDelete->affected_rows > 0) {
    task_redirect('index.php', 'success', 'Task deleted successfully.');
    return; // do not fall through to the error message (also keeps CLI tests safe)
}

task_redirect('index.php', 'danger', 'Failed to delete the task. Please try again.');
