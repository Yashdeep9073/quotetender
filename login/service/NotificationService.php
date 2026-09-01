<?php

require_once __DIR__ . '/email/TaskAssignmentEmail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class NotificationService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Create an in-app notification
     */
    public function createInAppNotification($userId, $type, $title, $message, $referenceType = null, $referenceId = null)
    {
        // Respect the user's in-app preference for this notification type
        if (!$this->userAllows((int) $userId, $type, 'in_app')) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, type, title, message, reference_type, reference_id) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('isssss', $userId, $type, $title, $message, $referenceType, $referenceId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /**
     * Task Assigned Notification
     */
    public function notifyTaskAssigned($taskId, $assignedUserId, $taskTitle = '')
    {
        $type = 'TASK_ASSIGNED';
        $title = 'New Task Assigned';
        $message = "You have been assigned a new task: " . $taskTitle;
        $refType = 'task';

        $this->createInAppNotification($assignedUserId, $type, $title, $message, $refType, $taskId);
        $this->sendTaskAssignmentEmail($assignedUserId, $taskId, $type);
    }

    /**
     * Task Reassigned Notification
     */
    public function notifyTaskReassigned($taskId, $assignedUserId, $taskTitle = '')
    {
        $type = 'TASK_REASSIGNED';
        $title = 'Task Reassigned';
        $message = "You have been reassigned a task: " . $taskTitle;
        $refType = 'task';

        $this->createInAppNotification($assignedUserId, $type, $title, $message, $refType, $taskId);
        $this->sendEmailNotification($assignedUserId, $type, $title, $message);
    }

    /**
     * Task Status Changed Notification
     */
    public function notifyTaskStatusChanged($taskId, $oldStatus, $newStatus, $actorUserId, $taskCreatorId, $taskTitle = '')
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        $type = 'TASK_STATUS_CHANGED';
        $title = 'Task Status Changed';
        $message = "Task #{$taskId} ('{$taskTitle}') status changed from {$oldStatus} to {$newStatus}.";
        $refType = 'task';

        if ($actorUserId != $taskCreatorId) {
            $this->createInAppNotification($taskCreatorId, $type, $title, $message, $refType, $taskId);
            $this->sendEmailNotification($taskCreatorId, $type, $title, $message);
        }
    }

    /**
     * Task Updated Notification
     */
    public function notifyTaskUpdated($taskId, $actorUserId, $participantUserId, $taskTitle = '')
    {
        $type = 'TASK_UPDATED';
        $title = 'Task Updated';
        $message = "Task #{$taskId} ('{$taskTitle}') has been updated.";
        $refType = 'task';

        if ($actorUserId != $participantUserId) {
            $this->createInAppNotification($participantUserId, $type, $title, $message, $refType, $taskId);
            $this->sendEmailNotification($participantUserId, $type, $title, $message);
        }
    }

    /**
     * Task Commented Notification
     */
    public function notifyTaskCommented($taskId, $commentUserId, $participantUserId, $taskTitle = '')
    {
        $type = 'TASK_COMMENTED';
        $title = 'New Comment on Task';
        $message = "A new comment was added to Task #{$taskId} ('{$taskTitle}').";
        $refType = 'task';

        if ($commentUserId != $participantUserId) {
            $this->createInAppNotification($participantUserId, $type, $title, $message, $refType, $taskId);
            $this->sendEmailNotification($participantUserId, $type, $title, $message);
        }
    }

    /**
     * Tender Request Assigned as a Task Notification
     */
    public function notifyTenderTaskAssigned($taskId, $assignedUserId, $taskTitle = '')
    {
        $type = 'TENDER_TASK_ASSIGNED';
        $title = 'Tender Request Assigned';
        $message = "A tender/query task has been assigned to you: " . $taskTitle;
        $refType = 'task';

        $this->createInAppNotification($assignedUserId, $type, $title, $message, $refType, $taskId);
        $this->sendTaskAssignmentEmail($assignedUserId, $taskId, $type);
    }

    /**
     * Task Due Soon Notification (intended to be invoked by the future scheduled job)
     */
    public function notifyTaskDueSoon($taskId, $assignedUserId, $taskTitle = '', $dueDate = null)
    {
        $type = 'TASK_DUE_SOON';
        $title = 'Task Due Soon';
        $message = "Task #{$taskId} ('{$taskTitle}') is due on " . ($dueDate !== null ? $dueDate : 'the scheduled date') . ".";
        $refType = 'task';

        $this->createInAppNotification($assignedUserId, $type, $title, $message, $refType, $taskId);
        $this->sendEmailNotification($assignedUserId, $type, $title, $message);
    }

    /**
     * Task Overdue Notification (intended to be invoked by the future scheduled job)
     */
    public function notifyTaskOverdue($taskId, $assignedUserId, $taskTitle = '')
    {
        $type = 'TASK_OVERDUE';
        $title = 'Task Overdue';
        $message = "Task #{$taskId} ('{$taskTitle}') is overdue.";
        $refType = 'task';

        $this->createInAppNotification($assignedUserId, $type, $title, $message, $refType, $taskId);
        $this->sendEmailNotification($assignedUserId, $type, $title, $message);
    }

    /**
     * Reusable Email Sender Adapter (generic notifications).
     * Builds a simple HTML + plain-text message and sends via sendMail().
     */
    private function sendEmailNotification($userId, $type, $subject, $body)
    {
        // Respect the user's email preference for this notification type
        if (!$this->userAllows((int) $userId, $type, 'email')) {
            return false;
        }

        // 1. Retrieve recipient email from the admin table
        $stmt = $this->db->prepare("SELECT email, username FROM admin WHERE id = ? AND status = 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $userRow = $stmt->get_result()->fetch_assoc();

        if (!$userRow || empty($userRow['email'])) {
            return false;
        }

        $recipientEmail = $userRow['email'];
        $recipientName  = $userRow['username'];
        $safeName       = htmlspecialchars((string) $recipientName, ENT_QUOTES, 'UTF-8');
        $safeBody       = htmlspecialchars((string) $body, ENT_QUOTES, 'UTF-8');

        $html = '<p>Dear ' . $safeName . ',</p><p>' . $safeBody . '</p>';
        $text = 'Dear ' . $recipientName . ",\n\n" . $body;

        return $this->sendMail($recipientEmail, $recipientName, $subject, $html, $text);
    }

    /**
     * Task assignment email (TASK_ASSIGNED / TENDER_TASK_ASSIGNED).
     * Loads the authoritative task/tender data from the database and sends the
     * professional HTML + plain-text email through the shared sendMail().
     */
    private function sendTaskAssignmentEmail($assignedUserId, $taskId, $type)
    {
        // Respect the user's email preference for this notification type
        if (!$this->userAllows((int) $assignedUserId, $type, 'email')) {
            return false;
        }

        $task = $this->loadTaskAssignmentData((int) $taskId);
        if (!$task || empty($task['assigned_email'])) {
            return false;
        }

        $builder  = new TaskAssignmentEmail();
        $baseUrl  = $this->baseUrl();
        $subject  = $builder->subject($task, $type);
        $htmlBody = $builder->html($task, $type, $baseUrl);
        $textBody = $builder->text($task, $type, $baseUrl);

        return $this->sendMail(
            $task['assigned_email'],
            $task['assigned_username'],
            $subject,
            $htmlBody,
            $textBody
        );
    }

    /**
     * Load the fields required for the assignment email with a single JOIN.
     * No SELECT *, no per-field queries; recipient/creator/tender are all
     * resolved server-side from the task record.
     */
    private function loadTaskAssignmentData($taskId)
    {
        $stmt = $this->db->prepare(
            "SELECT
                t.id, t.title, t.description, t.priority, t.status,
                t.start_date, t.due_date, t.created_at,
                t.created_by, t.assigned_to, t.tender_request_id,
                a.email       AS assigned_email,
                a.username    AS assigned_username,
                c.username    AS creator_username,
                utr.tenderID,
                utr.reference_code,
                utr.status    AS tender_status,
                utr.due_date  AS tender_due_date,
                utr.created_at AS tender_created_at,
                utr.updated_by,
                m.name        AS member_name,
                m.firm_name   AS member_firm
               FROM tasks t
               JOIN admin a ON a.id = t.assigned_to
               LEFT JOIN admin c ON c.id = t.created_by
               LEFT JOIN user_tender_requests utr ON utr.id = t.tender_request_id
               LEFT JOIN members m ON m.member_id = utr.member_id
              WHERE t.id = ?"
        );
        $stmt->bind_param('i', $taskId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Shared SMTP sender (HTML + plain-text multipart).
     * Prefers the active smtp_settings row, falls back to the legacy env-based
     * config. The SMTP password is never logged or echoed.
     */
    private function sendMail($toEmail, $toName, $subject, $htmlBody, $textBody)
    {
        try {
            $mail = new PHPMailer(true);

            $mail->SMTPDebug = 0;
            $mail->isSMTP();

            // 1. Prefer the active row in smtp_settings (Notification Layer)
            $smtp = $this->activeSmtpSettings();
            if ($smtp) {
                $mail->Host     = $smtp['host'];
                $mail->Port     = (int) $smtp['port'];
                $mail->SMTPAuth = !empty($smtp['username']);
                if ($mail->SMTPAuth) {
                    $mail->Username = $smtp['username'];
                    $mail->Password = $smtp['password'];
                }
                $mail->SMTPSecure = ($smtp['encryption'] === 'ssl')
                    ? PHPMailer::ENCRYPTION_SMTPS
                    : PHPMailer::ENCRYPTION_STARTTLS;
                $mail->setFrom($smtp['from_email'], $smtp['from_name']);
            } else {
                // 2. Fall back to the legacy env-based SMTP configuration
                $mail->Host = getenv('SMTP_HOST');
                $mail->SMTPAuth = true;
                $mail->Username = getenv('SMTP_USER_NAME');
                $mail->Password = getenv('SMTP_PASSCODE');
                $mail->Port = getenv('SMTP_PORT') ?: 587;

                // Security based on port (matching existing PHPMailer practices)
                if ($mail->Port == 465) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } else {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // default tls for 587
                }

                // Fallbacks for missing globals (they are set in login/db/config.php)
                global $smtpTitleForMail;
                $fromName = !empty($smtpTitleForMail) ? $smtpTitleForMail : 'System Notification';
                $mail->setFrom(getenv('SMTP_USER_NAME'), $fromName);
            }

            $mail->addAddress($toEmail, $toName);

            global $ccEmailData;
            if (!empty($ccEmailData) && is_array($ccEmailData)) {
                foreach ($ccEmailData as $ccRow) {
                    if (!empty($ccRow['cc_email'])) {
                        $mail->addCC($ccRow['cc_email']);
                    }
                }
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log failure quietly without breaking the app (no credentials logged)
            error_log('Email Notification Failed: ' . $mail->ErrorInfo);
            return false;
        } catch (\Exception $e) {
            error_log('Email Notification Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Base URL for links inside emails, built from the existing BASE_URL
     * configuration (a scheme is only added when the configured value lacks one).
     */
    private function baseUrl()
    {
        $base = rtrim((string) getenv('BASE_URL'), '/');
        if ($base !== '' && !preg_match('#^https?://#i', $base)) {
            $base = 'http://' . $base;
        }
        return $base;
    }

    /**
     * Active SMTP configuration from the notification layer (smtp_settings).
     * Returns null when no usable row exists so the legacy env config is used.
     */
    private function activeSmtpSettings()
    {
        $stmt = $this->db->prepare(
            "SELECT host, port, username, password, encryption, from_email, from_name
               FROM smtp_settings
              WHERE is_active = 1
              ORDER BY id
              LIMIT 1"
        );
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row || empty($row['host']) || empty($row['from_email'])) {
            return null;
        }
        return $row;
    }

    // ---------- Per-user preference routing (notification_settings) ----------

    /** Map a notification type to its per-type settings column. */
    private function typeSettingColumn($type)
    {
        $map = [
            'TASK_ASSIGNED'        => 'task_assigned',
            'TASK_REASSIGNED'      => 'task_reassigned',
            'TASK_STATUS_CHANGED'  => 'task_status_changed',
            'TASK_UPDATED'         => 'task_updated',
            'TASK_COMMENTED'       => 'task_commented',
            'TASK_DUE_SOON'        => 'task_due_soon',
            'TASK_OVERDUE'         => 'task_overdue',
            'TENDER_TASK_ASSIGNED' => 'task_assigned',
        ];
        return isset($map[$type]) ? $map[$type] : null;
    }

    /** Create the default per-user settings row when missing (all channels/types ON). */
    private function ensureSettings($userId)
    {
        $stmt = $this->db->prepare("INSERT IGNORE INTO notification_settings (user_id) VALUES (?)");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    }

    /** Whether the user allows the given notification type on the given channel. */
    private function userAllows($userId, $type, $channel)
    {
        $this->ensureSettings($userId);

        $stmt = $this->db->prepare("SELECT * FROM notification_settings WHERE user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            return true; // no row yet -> defaults (all enabled)
        }
        if ($channel === 'email' && (int) $row['email_enabled'] !== 1) {
            return false;
        }
        if ($channel === 'in_app' && (int) $row['in_app_enabled'] !== 1) {
            return false;
        }
        $column = $this->typeSettingColumn($type);
        if ($column !== null && isset($row[$column]) && (int) $row[$column] !== 1) {
            return false;
        }
        return true;
    }

    // ---------- Read / mark queries (always scoped to the authenticated user) ----------

    /** Unread notification count for a user. */
    public function unreadCount($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    /** Most recent notifications for a user. */
    public function recent($userId, $limit = 50)
    {
        $limit = max(1, min((int) $limit, 200));
        $stmt = $this->db->prepare(
            "SELECT id, type, title, message, reference_type, reference_id, is_read, read_at, created_at
               FROM notifications
              WHERE user_id = ?
              ORDER BY created_at DESC
              LIMIT ?"
        );
        $stmt->bind_param('ii', $userId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Mark a single notification read (only if it belongs to the user). */
    public function markAsRead($notificationId, $userId)
    {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?"
        );
        $stmt->bind_param('ii', $notificationId, $userId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /** Mark every unread notification read for the user. */
    public function markAllAsRead($userId)
    {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}
