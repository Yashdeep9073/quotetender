<?php
declare(strict_types=1);

require_once "../vendor/autoload.php";
require_once "db/config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    /**
     * Fetch users who need reminder mails
     */
    $sql = "
        SELECT m.email_id, utr.*
        FROM user_tender_requests utr
        LEFT JOIN members m 
            ON utr.member_id = m.member_id
        WHERE utr.status = 'Allotted'
        AND utr.reminder_days > 0
    ";

    $stmt = $db->prepare($sql);

    if (!$stmt) {
        throw new Exception("DB Prepare Failed: " . $db->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("DB Execute Failed: " . $stmt->error);
    }

    // 🔥 IMPORTANT for debugging
    $stmt->store_result();

    echo "Total Rows Found: " . $stmt->num_rows . "<br>";

    $stmt->bind_result($email);

    // /**
    //  * Common mail configuration
    //  */
    // function getMailer(): PHPMailer
    // {
    //     $mail = new PHPMailer(true);

    //     $mail->isSMTP();
    //     $mail->SMTPDebug = 0;
    //     $mail->Host = getenv('SMTP_HOST');
    //     $mail->SMTPAuth = true;
    //     $mail->Username = getenv('SMTP_USER_NAME');
    //     $mail->Password = getenv('SMTP_PASSCODE');
    //     $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    //     $mail->Port = (int) getenv('SMTP_PORT');

    //     $mail->setFrom('enquiry@dvepl.com', 'DVEPL');
    //     $mail->isHTML(true);

    //     return $mail;
    // }

    // /**
    //  * Email Body
    //  */
    // $emailBody = <<<HTML
    // <p>Dear User,</p>

    // <p>This is a friendly reminder to follow up on your <strong>allotted tender</strong>.</p>

    // <p>
    // The tender has already been allotted.  
    // Please log in to your dashboard to view the complete details.
    // </p>

    // <p>
    // <strong>QuoteTender</strong><br>
    // 📞 +91-9870443528<br>
    // ✉️ info@quotender.com
    // </p>
    // HTML;

    // while ($stmt->fetch()) {
    //     try {
    //         $mail = getMailer();
    //         $mail->addAddress($email);
    //         $mail->Subject = "Reminder: Follow-up on Allotted Tender";
    //         $mail->Body = $emailBody;

    //         $mail->send();
    //     } catch (Exception $e) {
    //         error_log("Mail failed for {$email}: " . $e->getMessage());
    //     }
    // }

    // $stmt->close();
    // $db->close();



    while ($stmt->fetch()) {
        echo "Mail Sent Successfully" . $email;
    }

} catch (Throwable $e) {
    // Global safety net
    error_log("Reminder Cron Failed: " . $e->getMessage());
}
