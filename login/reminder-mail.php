<?php
declare(strict_types=1);

require_once "../vendor/autoload.php";
require_once "db/config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * CONFIG
 */
define('DEBUG_MODE', false);   // 🔁 set false in production


/**
 * Mailer factory
 */
function getMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = getenv('SMTP_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USER_NAME');
    $mail->Password = getenv('SMTP_PASSCODE');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = (int) getenv('SMTP_PORT');

    $mail->setFrom(getenv('SMTP_USER_NAME'), 'DVEPL');
    $mail->addReplyTo(ENQUIRY_EMAIL, 'DVEPL Support');
    $mail->isHTML(true);

    return $mail;
}

try {

    /**
     * Business Logic:
     * - Allotted tenders
     * - reminder_days > 0
     * - reminder date has passed
     * - not already mailed today
     */
    $sql = "
        SELECT 
            utr.id,
            m.email_id,
            m.name,
            utr.tenderID,
            utr.allotted_at,
            utr.reminder_days,
            DATEDIFF(CURDATE(), DATE(utr.allotted_at)) AS days_passed
        FROM user_tender_requests utr
        INNER JOIN members m 
            ON utr.member_id = m.member_id
        WHERE utr.status = 'Allotted'
          AND utr.reminder_days > 0
          AND DATE(utr.allotted_at) <= DATE_SUB(CURDATE(), INTERVAL utr.reminder_days DAY)
          AND (utr.email_sent_date IS NULL OR utr.email_sent_date <> CURDATE())
    ";

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw new Exception("DB Prepare Failed: " . $db->error);
    }

    $stmt->execute();
    $stmt->store_result();

    echo "<h3>Total Rows Found: {$stmt->num_rows}</h3>";

    $stmt->bind_result(
        $requestId,
        $email,
        $userName,
        $tenderId,
        $allottedAt,
        $reminderDays,
        $daysPassed
    );

    /**
     * Fetch email template ONCE
     */
    $template = emailTemplate($db, 'NOTIFICATION');

    $search = [
        '{$name}',
        '{$tenderId}',
        '{$supportPhone}',
        '{$enquiryEmail}',
        '{$supportEmail}',
    ];

    while ($stmt->fetch()) {

        // Duration math
        $pendingDays = max(0, $reminderDays - $daysPassed);
        $reminderStartDate = date(
            'Y-m-d',
            strtotime($allottedAt . " +{$reminderDays} days")
        );

        // DEBUG OUTPUT
        if (DEBUG_MODE) {
            echo "<pre>";
            echo "📧 Email            : {$email}\n";
            echo "👤 User             : {$userName}\n";
            echo "📄 Tender ID        : {$tenderId}\n";
            echo "📅 Allotted At      : {$allottedAt}\n";
            echo "⏳ Reminder Days    : {$reminderDays}\n";
            echo "📆 Days Passed      : {$daysPassed}\n";
            echo "⌛ Pending Days     : {$pendingDays}\n";
            echo "🚀 Reminder Starts  : {$reminderStartDate}\n";
            echo "✅ STATUS           : READY TO SEND\n";
            echo "---------------------------------\n";
            echo "</pre>";
        }

        // Skip sending while debugging
        if (DEBUG_MODE) {
            continue;
        }

        /**
         * ✉️ Build mail body
         */
        $replace = [
            $userName,
            $tenderId,
            $supportPhone ?? 'N/A',
            $enquiryMail ?? 'N/A',
            $supportEmail ?? 'N/A'
        ];

        $emailBody =
            nl2br($template['content_1']) .
            "<br><br>" .
            nl2br($template['content_2']);

        $finalBody = str_replace($search, $replace, $emailBody);

        try {
            $mail = getMailer();
            $mail->addAddress($email);
            $mail->Subject = $template['email_template_subject'] ?? 'Tender Reminder';
            $mail->Body = $finalBody;
            $mail->send();

            // ✅ Mark as mailed today
            $update = $db->prepare(
                "UPDATE user_tender_requests SET email_sent_date = CURDATE() WHERE id = ?"
            );
            $update->bind_param('i', $requestId);
            $update->execute();

            echo "✅ Mail sent to {$email}<br>";

        } catch (Exception $e) {
            error_log("Mail failed for {$email}: " . $e->getMessage());
        }
    }

    $stmt->close();
    $db->close();

} catch (Throwable $e) {
    error_log("Reminder Cron Failed: " . $e->getMessage());
}
