<?php
declare(strict_types=1);

require_once "../vendor/autoload.php";
require_once "db/config.php";
require_once "service/NotificationService.php";

define('DEBUG_MODE', false); // set true for testing

try {
    $notificationService = new NotificationService($db);

    // 1. TASK_DUE_SOON (e.g. Due Tomorrow)
    $dueSoonSql = "
        SELECT t.id, t.title, t.due_date, a.employee_id
        FROM tasks t
        JOIN task_assignees a ON t.id = a.task_id
        WHERE t.status IN ('Pending', 'In Progress')
          AND t.due_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
          AND NOT EXISTS (
              SELECT 1 FROM notifications n 
              WHERE n.reference_id = t.id 
                AND n.reference_type = 'task' 
                AND n.type = 'TASK_DUE_SOON'
                AND n.user_id = a.employee_id
                AND DATE(n.created_at) = CURDATE()
          )
    ";

    $stmt = $db->prepare($dueSoonSql);
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if (DEBUG_MODE) {
                echo "Task Due Soon: {$row['id']} assigned to {$row['employee_id']}\n";
            } else {
                $notificationService->notifyTaskDueSoon($row['id'], $row['employee_id'], $row['title'], $row['due_date']);
            }
        }
        $stmt->close();
    }

    // 2. TASK_OVERDUE (Due date is in the past)
    $overdueSql = "
        SELECT t.id, t.title, t.due_date, a.employee_id
        FROM tasks t
        JOIN task_assignees a ON t.id = a.task_id
        WHERE t.status IN ('Pending', 'In Progress')
          AND t.due_date < CURDATE()
          AND NOT EXISTS (
              SELECT 1 FROM notifications n 
              WHERE n.reference_id = t.id 
                AND n.reference_type = 'task' 
                AND n.type = 'TASK_OVERDUE'
                AND n.user_id = a.employee_id
                AND DATE(n.created_at) = CURDATE()
          )
    ";

    $stmt = $db->prepare($overdueSql);
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if (DEBUG_MODE) {
                echo "Task Overdue: {$row['id']} assigned to {$row['employee_id']}\n";
            } else {
                $notificationService->notifyTaskOverdue($row['id'], $row['employee_id'], $row['title']);
            }
        }
        $stmt->close();
    }

} catch (Throwable $e) {
    error_log("Task Reminder Cron Failed: " . $e->getMessage());
    echo "Task Reminder Cron Failed: " . $e->getMessage();
}
