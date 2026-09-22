<?php

require_once __DIR__ . '/inc/init.php';
require_once __DIR__ . '/../service/ScheduledTaskNotificationService.php';

header('Content-Type: application/json');

function task_notification_json($ok, $message, array $extra = [])
{
    echo json_encode(array_merge(['success' => $ok, 'message' => $message], $extra));
    exit;
}

$taskCanNotify = $taskCanViewAll || $taskCanCreate || $taskCanEdit;
if (!$taskCanNotify) {
    http_response_code(403);
    task_notification_json(false, 'You do not have permission to send task notifications.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    task_notification_json(false, 'Invalid request method.');
}

$action = isset($_POST['action']) ? (string) $_POST['action'] : '';
$service = new ScheduledTaskNotificationService($db);

try {
    if ($action === 'send_now' || $action === 'schedule') {
        $taskId = task_get_int($_POST['task_id'] ?? null);
        if ($taskId === false) {
            task_notification_json(false, 'Invalid task ID.');
        }

        $task = task_load($db, $taskId);
        if (!task_can_view_row($task, $taskUserId, $taskCanViewAll)) {
            http_response_code(403);
            task_notification_json(false, 'Task not found or you do not have permission to access it.');
        }

        $channel = strtoupper((string) ($_POST['channel'] ?? ''));
        if (!in_array($channel, ['EMAIL', 'WHATSAPP'], true)) {
            task_notification_json(false, 'Invalid notification channel.');
        }

        $note = trim((string) ($_POST['message'] ?? ''));
        if (mb_strlen($note) > 1000) {
            task_notification_json(false, 'Notification note is too long (max 1000 characters).');
        }

        $delivery = $action === 'send_now' ? 'now' : 'later';
        $dispatchRequest = $service->validateDispatchRequest(
            [$channel],
            $delivery,
            $_POST['schedule_date'] ?? '',
            $_POST['schedule_time'] ?? ''
        );
        $result = $service->dispatch(
            $taskId,
            $dispatchRequest['channels'],
            $dispatchRequest['delivery'],
            $note,
            $dispatchRequest['scheduled_at'],
            $taskUserId
        );

        if ($delivery === 'now') {
            $message = $channel . ' notification sent to ' . (int) $result['sent'] . ' of ' . (int) $result['total'] . ' recipient(s).';
        } else {
            $scheduledDateTime = new DateTime($dispatchRequest['scheduled_at'], new DateTimeZone('Asia/Kolkata'));
            $message = !empty($result['failed'])
                ? 'Notification scheduling completed with errors.'
                : 'Notification scheduled successfully.';
        }
        task_notification_json(true, $message, [
            'result' => $result,
            'scheduled_at' => $delivery === 'later' ? $scheduledDateTime->format('d M Y H:i') : null,
        ]);
    }

    if ($action === 'cancel_schedule') {
        $scheduleId = task_get_int($_POST['schedule_id'] ?? null);
        if ($scheduleId === false) {
            task_notification_json(false, 'Invalid schedule ID.');
        }
        $cancelled = $service->cancel($scheduleId, $taskUserId, $taskCanViewAll);
        task_notification_json($cancelled, $cancelled ? 'Scheduled notification cancelled.' : 'Scheduled notification could not be cancelled.');
    }

    task_notification_json(false, 'Invalid notification action.');
} catch (Throwable $e) {
    error_log('Task notification action failed: ' . $e->getMessage());
    http_response_code(400);
    task_notification_json(false, $e->getMessage());
}
