<?php
declare(strict_types=1);

require_once "../vendor/autoload.php";
require_once "db/config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    $mail->addReplyTo('dvepl@yahoo.in', 'DVEPL Support');
    $mail->isHTML(true);

    return $mail;
}

try {

    $sql = "
        SELECT 
            m.email_id,
            m.name,
            utr.tenderID
        FROM user_tender_requests utr
        INNER JOIN members m 
            ON utr.member_id = m.member_id
        WHERE utr.status = 'Allotted'
          AND utr.reminder_days > 0
    ";

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw new Exception("DB Prepare Failed: " . $db->error);
    }

    $stmt->execute();
    $stmt->store_result();

    echo "Total Rows Found: " . $stmt->num_rows . "<br>";

    $stmt->bind_result($email, $userName, $tenderId);

    // 🔹 Fetch template ONCE
    $template = emailTemplate($db, 'NOTIFICATION');

    $search = [
        '{$name}',
        '{$tenderId}',
        '{$supportPhone}',
        '{$enquiryEmail}',
        '{$supportEmail}',
    ];


    while ($stmt->fetch()) {

        $replace = [
            $userName,
            $tenderId,
            $supportPhone,
            $enquiryMail,
            $supportEmail
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
