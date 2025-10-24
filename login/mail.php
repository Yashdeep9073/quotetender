<?php
session_start();
include("db/config.php");
require_once "../vendor/autoload.php";
require_once "../env.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$en = $_POST["tender_sent_ids"]; // Comma-separated string of tender IDs
$tenderIDs = explode(',', $en); // Split the string into an array of tender IDs
$result = [];

$stat = 1;
$re = base64_encode($stat);

$upload_directory = "tender/";

if (isset($en)) {

    foreach ($tenderIDs as $id) {
        $id = trim($id);

        $stmt = $db->prepare("SELECT m.email_id,  m.name, ur.file_name, ur.file_name2, ur.tenderID, ur.id FROM user_tender_requests ur 
        inner join members m on ur.member_id= m.member_id  WHERE ur.id= ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $memberData = $stmt->get_result()->fetch_assoc();

        if ($memberData) {
            $result[] = $memberData;
        }
    }

    foreach ($result as $data) {

        $mail = new PHPMailer(true);

        //Enable SMTP debugging.
        $mail->SMTPDebug = 0;

        //Set PHPMailer to use SMTP.
        $mail->isSMTP();

        //Set SMTP host name                      
        $mail->Host = getenv('SMTP_HOST');

        //Set this to true if SMTP host requires authentication to send email
        $mail->SMTPAuth = true;

        //Provide username and password
        $mail->Username = getenv('SMTP_USER_NAME');
        $mail->Password = getenv('SMTP_PASSCODE');

        //If SMTP requires TLS encryption then set it
        $mail->SMTPSecure = "ssl";

        //Set TCP port to connect to
        $mail->Port = getenv('SMTP_PORT');

        $mail->From = getenv('SMTP_USER_NAME');
        $mail->FromName = "DVEPL";

        $mail->addAddress($data["email_id"], "Recepient Name");

        $mail->IsHTML(true);

        $mail->Subject = "Tender Request Approved";
        $mail->Body = "
<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
    <div style='text-align: center; margin-bottom: 20px;'>
        <img src='https://dvepl.com/assets/images/logo/dvepl-logo.png' alt='Quote Tender Logo' style='max-width: 200px; height: auto;'>
    </div>
    <p style='font-size: 18px; color: #555;'>Dear <strong>" . htmlspecialchars($data["name"]) . "</strong>,</p>
    <p>We are pleased to inform you that the <strong>Tender ID:</strong> " . htmlspecialchars($data["tenderID"]) . " has been approved for you. The quotation file is attached below for your reference.</p>
    
    <p>If you have any questions or need further assistance regarding the process, please don’t hesitate to contact us. We’re always here to help!</p>
    
    <p style='margin-top: 20px;'>
        <strong>Thanks & Regards,</strong><br/>
        <span style='color: #4CBB17;'>Admin, DVEPL</span><br/>
        <span>Mobile: <a href='tel:+919417601244' style='color: #4CBB17; text-decoration: none;'>+91-9417601244</a></span><br/>
        <span>Email: <a href='mailto:help@quotetender.in' style='color: #4CBB17; text-decoration: none;'>help@quotetender.in</a></span>
    </p>

    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>

    <p style='text-align: center; font-size: 12px; color: #888;'>
        &#169 2025 DVEPL. All Rights Reserved.
    </p>
</div>";


        // Add attachments
        $mail->addAttachment($upload_directory . $data["file_name"]);
        if (!empty($data["file_name2"])) {
            $mail->addAttachment($upload_directory . $data["file_name2"]);
        }

        if (!$mail->send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            date_default_timezone_set("Asia/Calcutta");
            $emailSentDate = date("Y-m-d h:i A");

            // Assuming you want to update an existing record with a specific id
            $stmt = $db->prepare("UPDATE user_tender_requests SET email_sent_date = ? WHERE id = ?");
            $stmt->bind_param("ss", $emailSentDate, $data['id']); // Assuming both email_sent_date and id are strings
            $stmt->execute();
        }

        // Clear all recipients and attachments after sending the email
        $mail->clearAddresses();
        $mail->clearAttachments();
    }

    echo ("<SCRIPT LANGUAGE='JavaScript'>
    window.location.href='tender-request.php?status=$re';
    </SCRIPT>");
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $mail = new PHPMailer(true);

    //Enable SMTP debugging.

    $mail->SMTPDebug = 0;

    //Set PHPMailer to use SMTP.

    $mail->isSMTP();

    //Set SMTP host name                      

    $mail->Host = getenv('SMTP_HOST');

    //Set this to true if SMTP host requires authentication to send email

    $mail->SMTPAuth = true;

    //Provide username and password

    $mail->Username = getenv('SMTP_USER_NAME');

    $mail->Password = getenv('SMTP_PASSCODE');

    //If SMTP requires TLS encryption then set it

    $mail->SMTPSecure = "ssl";

    //Set TCP port to connect to

    $mail->Port = getenv('SMTP_PORT');

    $mail->From = getenv('SMTP_USER_NAME');


    $mail->FromName = "Quote Tender  ";
    $adminEmail = getenv('SMTP_USER_NAME');

    $mail->addAddress('quotetenderindia@gmail.com');
    $mail->addAddress($adminEmail);
    $mail->IsHTML(true);

    $membersQuery = "SELECT m.email_id,  m.name, ur.file_name, ur.file_name2, ur.tenderID, ur.id,ur.additional_files FROM user_tender_requests ur 
    inner join members m on ur.member_id= m.member_id  WHERE ur.id='" . $id . "'";
    $membersResult = mysqli_query($db, $membersQuery);
    $memberData = mysqli_fetch_row($membersResult);

    $mail->addAddress($memberData[0], "Recepient Name");

    $mail->Subject = "Tender Request Approved";


    $processedFiles = [];
    if (!empty($memberData['6'])) {
        $extraFiles = json_decode($memberData['6'], true);
        if (is_array($extraFiles)) {
            foreach ($extraFiles as $filePath) {
                $mail->addAttachment($filePath);
                $processedFiles[] = $filePath; // Store processed file
            }

            // // Send response after processing all files
            // echo json_encode([
            //     "status" => 400,
            //     "error" => "auto mail",
            //     "data" => $processedFiles, // All files in array
            // ]);
            // exit;
        }
    }

    $mail->Body = "
<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
    <div style='text-align: center; margin-bottom: 20px;'>
        <img src='https://dvepl.com/quotetender/assets/images/logo/logo.png' alt='Quote Tender Logo' style='max-width: 200px; height: auto;'>
    </div>
    <p style='font-size: 18px; color: #555;'>Dear <strong>" . $memberData[1] . "</strong>,</p>
    <p>We are pleased to inform you that the <strong>Tender ID:</strong> " . htmlspecialchars($memberData[4]) . " has been approved for you. The quotation file is attached below for your reference.</p>
    
    <p>If you have any questions or need further assistance regarding the process, please don’t hesitate to contact us. We’re here to help!</p>
    
    <p style='margin-top: 20px;'>
        <strong>Thanks & Regards,</strong><br/>
        <span style='color: #4CBB17;'>Admin, DVEPL</span><br/>
        <span>Mobile: <a href='tel:+919417601244' style='color: #4CBB17; text-decoration: none;'>+91-9417601244</a></span><br/>
        <span>Email: <a href='mailto:info@quotender.com' style='color: #4CBB17; text-decoration: none;'>info@quotender.com</a></span>
    </p>

    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>

    <p style='text-align: center; font-size: 12px; color: #888;'>
        &#169 2025 DVEPL. All Rights Reserved.
    </p>
</div>";


    if (!$mail->send()) {

        echo "Mailer Error: " . $mail->ErrorInfo;
    } else {
        date_default_timezone_set("Asia/Calcutta");
        $emailSentDate = date("Y-m-d h:i A");

        // Assuming you want to update an existing record with a specific id
        $stmt = $db->prepare("UPDATE user_tender_requests SET email_sent_date = ? WHERE id = ?");
        $stmt->bind_param("ss", $emailSentDate, $id); // Assuming both email_sent_date and id are strings
        $stmt->execute();


    }

    echo ("<SCRIPT LANGUAGE='JavaScript'>
    window.location.href='tender-request.php?status=$re';
    </SCRIPT>");

}
?>