<?php

session_start();

require("login/db/config.php");
require_once "./vendor/autoload.php";
require_once "./env.php";
require "./login/utility/referenceCodeGenerator.php";


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$adminEmail = "quotetenderindia@gmail.com";
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

$mail->addAddress($email, "Recepient Name");
$mail->addAddress($adminEmail);


$mail->isHTML(true);




$activationLink = 'https://quotetender.in/reset-password.php?token=' . $activationToken;
$mail->Subject = "Tender Request";

$mail->Body = "
<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
    <div style='text-align: center; margin-bottom: 20px;'>
        <img src='https://dvepl.com/assets/images/logo/dvepl-logo.png' alt='Quote Tender Logo' style='max-width: 200px; height: auto;'>
    </div>
    <p style='font-size: 18px; color: #555;'>Dear <strong>" . $memberData[1] . "</strong>,</p>
    <p>Thank you for your valuable enquiry! We truly appreciate your interest. We will provide the pricing details within 3-5 working days for the following tender:</p>
    
    <div style='background-color: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-top: 10px;'>
        <p style='margin: 0;'><strong>Firm Name:</strong> " . $memberData[2] . "</p>
        <p style='margin: 0;'><strong>Tender ID:</strong> " . $tender . "</p>
    </div>

    <p style='margin-top: 15px;'>If you have any questions or require further assistance, please don’t hesitate to contact us.</p>

    <p style='margin-top: 20px;'>
        <strong>Thanks & Regards,</strong><br/>
        <span style='color: #4CBB17;'>Admin, DVEPL</span><br/>
        <span>Mobile: <a href='tel:+919417601244' style='color: #4CBB17; text-decoration: none;'>+91-94176-01244</a></span><br/>
        <span>Email: <a href='mailto:help@quotetender.in' style='color: #4CBB17; text-decoration: none;'>help@quotetender.in</a></span>
    </p>

    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>

    <p style='text-align: center; font-size: 12px; color: #888;'>
       Copyright 2025 DVEPL. All Rights Reserved.
    </p>
</div>";



$mail->Subject = "Tender Request Approved";

$mail->addAttachment($upload_directory . $memberData[3]);
if (!empty($memberData[4])) {
    $mail->addAttachment($upload_directory . $memberData[4]);
}
$mail->Body = "<p> Dear " . $memberData[1] . " , <br/>" .
    "The <b>Tender ID: </b> " . $tender . "</b>  has been approved. Quotation file is attached below. For the further process, feel free to contact us.<br/><br/>
                <strong>Thanks, <br /> Admin Quote Tender</strong> <br/>
                Mobile: +91-9417601244 | Email: info@quotender.com ";

