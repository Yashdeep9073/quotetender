<?php
declare(strict_types=1);

require_once "../vendor/autoload.php";
require_once "db/config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('DEBUG_MODE', false); // set true for testing

function getMailer($supportEmail): PHPMailer
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->SMTPDebug = 1;
    $mail->Host = getenv('SMTP_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USER_NAME');
    $mail->Password = getenv('SMTP_PASSCODE');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = (int) getenv('SMTP_PORT');

    $mail->setFrom(getenv('SMTP_USER_NAME'), 'DVEPL');
    $mail->addReplyTo($supportEmail, 'DVEPL Support');
    $mail->isHTML(true);

    return $mail;
}

try {

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
    $stmt->execute();

    $result = $stmt->get_result();

    echo "<h3>Total Rows Found: {$result->num_rows}</h3>";

    $template = emailTemplate($db, 'NOTIFICATION');

    $search = [
        '{$name}',
        '{$tenderId}',
        '{$supportPhone}',
        '{$enquiryEmail}',
        '{$supportEmail}',
    ];

    foreach ($result as $row) {

        $requestId = $row['id'];
        $email = $row['email_id'];
        $userName = $row['name'];
        $tenderId = $row['tenderID'];
        $allottedAt = $row['allotted_at'];
        $reminderDays = (int) $row['reminder_days'];
        $daysPassed = (int) $row['days_passed'];

        // ⏱ Duration math
        $pendingDays = max(0, $reminderDays - $daysPassed);
        $reminderStartDate = date(
            'Y-m-d',
            strtotime($allottedAt . " +{$reminderDays} days")
        );

        // 🐞 Debug
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
            echo "✔️ STATUS           : $supportEmail\n";
            echo "✔️ STATUS           : $supportPhone\n";
            echo "✔️ STATUS           : $enquiryMail\n";
            echo "---------------------------------\n";
            echo "</pre>";
            continue;
        }

        // ✉️ Mail body
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
            $mail = getMailer($supportEmail);
            $mail->addAddress($email);
            $mail->Subject = $template['email_template_subject'] ?? 'Tender Reminder';
            $mail->Body = $finalBody;
            $mail->send();

            // mark sent today
            $update = $db->prepare(
                "UPDATE user_tender_requests 
                 SET email_sent_date = CURDATE() 
                 WHERE id = ?"
            );
            $update->bind_param('i', $requestId);
            $update->execute();

            echo "✅ Mail sent to {$email}<br>";

        } catch (Exception $e) {
            error_log("Mail failed for {$email}: " . $e->getMessage());
            echo "❌ Mail failed for {$email}<br>";
        }
    }

    $stmt->close();
    $db->close();

} catch (Throwable $e) {
    error_log("Reminder Cron Failed: " . $e->getMessage());
}
