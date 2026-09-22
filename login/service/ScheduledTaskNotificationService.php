<?php

require_once __DIR__ . '/NotificationService.php';

class ScheduledTaskNotificationService
{
    private $db;
    private $notificationService;

    public function __construct($db)
    {
        $this->db = $db;
        $this->notificationService = new NotificationService($db);
    }

    public function tableExists()
    {
        $result = $this->db->query("SHOW TABLES LIKE 'scheduled_notifications'");
        return $result && $result->num_rows > 0;
    }

    public function recipientsForTask($taskId)
    {
        $taskId = (int) $taskId;
        $sql = "SELECT DISTINCT a.id, a.username, a.email, a.mobile
                  FROM task_assignees ta
                  JOIN admin a ON a.id = ta.employee_id AND a.status = 1
                 WHERE ta.task_id = ?
                 ORDER BY a.username ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $taskId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (!empty($rows)) {
            return $rows;
        }

        $stmt = $this->db->prepare(
            "SELECT DISTINCT a.id, a.username, a.email, a.mobile
               FROM tasks t
               JOIN admin a ON a.id = t.assigned_to AND a.status = 1
              WHERE t.id = ?"
        );
        $stmt->bind_param('i', $taskId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function sendNow($taskId, $channel, $note, $createdBy)
    {
        $channel = $this->normalizeChannel($channel);
        if ($channel === false) {
            throw new InvalidArgumentException('Invalid notification channel.');
        }

        $recipients = $this->recipientsForTask($taskId);
        if (empty($recipients)) {
            throw new RuntimeException('No active task recipients found.');
        }

        $sent = 0;
        $failed = [];
        foreach ($recipients as $recipient) {
            $result = $this->notificationService->sendTaskReminder($taskId, (int) $recipient['id'], $channel, $note);
            if (!empty($result['success'])) {
                $sent++;
            } else {
                $failed[] = $recipient['username'] . ': ' . ($result['error'] ?? 'Send failed.');
            }
        }

        return [
            'total' => count($recipients),
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    public function schedule($taskId, $channel, $note, $scheduledAt, $createdBy)
    {
        if (!$this->tableExists()) {
            throw new RuntimeException('scheduled_notifications table is missing. Please run the migration SQL.');
        }

        $channel = $this->normalizeChannel($channel);
        if ($channel === false) {
            throw new InvalidArgumentException('Invalid notification channel.');
        }

        $recipients = $this->recipientsForTask($taskId);
        if (empty($recipients)) {
            throw new RuntimeException('No active task recipients found.');
        }

        $this->db->begin_transaction();
        try {
            $existsStmt = $this->db->prepare(
                "SELECT id
                   FROM scheduled_notifications
                  WHERE task_id = ?
                    AND user_id = ?
                    AND channel = ?
                    AND scheduled_at = ?
                    AND status IN ('PENDING','PROCESSING')
                  LIMIT 1"
            );
            $stmt = $this->db->prepare(
                "INSERT INTO scheduled_notifications
                    (task_id, user_id, channel, notification_type, message, scheduled_at, status, created_by)
                 VALUES (?, ?, ?, 'TASK_REMINDER', ?, ?, 'PENDING', ?)"
            );
            $created = 0;
            foreach ($recipients as $recipient) {
                $userId = (int) $recipient['id'];
                $existsStmt->bind_param('iiss', $taskId, $userId, $channel, $scheduledAt);
                $existsStmt->execute();
                if ($existsStmt->get_result()->fetch_assoc()) {
                    continue;
                }
                $stmt->bind_param('iisssi', $taskId, $userId, $channel, $note, $scheduledAt, $createdBy);
                $stmt->execute();
                $created += $stmt->affected_rows > 0 ? 1 : 0;
            }
            $this->db->commit();
            return $created;
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function statusesForTask($taskId)
    {
        if (!$this->tableExists()) {
            return [];
        }

        $stmt = $this->db->prepare(
            "SELECT sn.id, sn.channel, sn.notification_type, sn.scheduled_at, sn.status,
                    sn.sent_at, sn.error_message, a.username
               FROM scheduled_notifications sn
               JOIN admin a ON a.id = sn.user_id
              WHERE sn.task_id = ?
              ORDER BY sn.scheduled_at DESC, sn.id DESC
              LIMIT 100"
        );
        $stmt->bind_param('i', $taskId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function cancel($scheduleId, $actorUserId, $taskCanViewAll)
    {
        if (!$this->tableExists()) {
            throw new RuntimeException('scheduled_notifications table is missing. Please run the migration SQL.');
        }

        $stmt = $this->db->prepare("SELECT task_id FROM scheduled_notifications WHERE id = ? AND status = 'PENDING'");
        $stmt->bind_param('i', $scheduleId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            return false;
        }

        if (!$taskCanViewAll && !$this->userCanAccessTask((int) $row['task_id'], $actorUserId)) {
            throw new RuntimeException('You do not have permission to cancel this scheduled notification.');
        }

        $stmt = $this->db->prepare(
            "UPDATE scheduled_notifications
                SET status = 'CANCELLED', updated_at = NOW()
              WHERE id = ? AND status = 'PENDING'"
        );
        $stmt->bind_param('i', $scheduleId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function processDue($limit = 25)
    {
        if (!$this->tableExists()) {
            throw new RuntimeException('scheduled_notifications table is missing. Please run the migration SQL.');
        }

        $limit = max(1, min((int) $limit, 100));
        $processed = 0;

        $stmt = $this->db->prepare(
            "SELECT id
               FROM scheduled_notifications
              WHERE status = 'PENDING'
                AND scheduled_at <= NOW()
              ORDER BY scheduled_at ASC, id ASC
              LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($rows as $row) {
            if ($this->claimAndSend((int) $row['id'])) {
                $processed++;
            }
        }

        return $processed;
    }

    private function claimAndSend($scheduleId)
    {
        $stmt = $this->db->prepare(
            "UPDATE scheduled_notifications
                SET status = 'PROCESSING', attempts = attempts + 1, updated_at = NOW()
              WHERE id = ?
                AND status = 'PENDING'
                AND scheduled_at <= NOW()"
        );
        $stmt->bind_param('i', $scheduleId);
        $stmt->execute();
        if ($stmt->affected_rows !== 1) {
            return false;
        }

        $stmt = $this->db->prepare(
            "SELECT id, task_id, user_id, channel, message
               FROM scheduled_notifications
              WHERE id = ? AND status = 'PROCESSING'"
        );
        $stmt->bind_param('i', $scheduleId);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        if (!$item) {
            return false;
        }

        $result = $this->notificationService->sendTaskReminder(
            (int) $item['task_id'],
            (int) $item['user_id'],
            $item['channel'],
            (string) $item['message']
        );

        if (!empty($result['success'])) {
            $stmt = $this->db->prepare(
                "UPDATE scheduled_notifications
                    SET status = 'SENT', sent_at = NOW(), error_message = NULL, updated_at = NOW()
                  WHERE id = ? AND status = 'PROCESSING'"
            );
            $stmt->bind_param('i', $scheduleId);
            $stmt->execute();
            return true;
        }

        $error = substr((string) ($result['error'] ?? 'Send failed.'), 0, 1000);
        $stmt = $this->db->prepare(
            "UPDATE scheduled_notifications
                SET status = 'FAILED', error_message = ?, updated_at = NOW()
              WHERE id = ? AND status = 'PROCESSING'"
        );
        $stmt->bind_param('si', $error, $scheduleId);
        $stmt->execute();
        return true;
    }

    private function normalizeChannel($channel)
    {
        $channel = strtoupper((string) $channel);
        return in_array($channel, ['EMAIL', 'WHATSAPP'], true) ? $channel : false;
    }

    private function userCanAccessTask($taskId, $userId)
    {
        $stmt = $this->db->prepare(
            "SELECT 1
               FROM tasks t
              WHERE t.id = ?
                AND (
                    t.assigned_to = ?
                    OR t.created_by = ?
                    OR EXISTS (
                        SELECT 1 FROM task_assignees ta
                         WHERE ta.task_id = t.id AND ta.employee_id = ?
                    )
                )
              LIMIT 1"
        );
        $stmt->bind_param('iiii', $taskId, $userId, $userId, $userId);
        $stmt->execute();
        return (bool) $stmt->get_result()->fetch_assoc();
    }
}
