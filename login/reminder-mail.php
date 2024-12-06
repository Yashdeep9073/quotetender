<?php
require_once "../vendor/autoload.php";


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include("db/config.php");

$query = "SELECT sm.email_id, ur.reminder_days, ur.allotted_at FROM user_tender_requests ur 
inner join members sm on ur.selected_user_id= sm.member_id where ur.status= 'Allotted' AND DATEDIFF(NOW(), ur.allotted_at)=ur.reminder_days 
AND ur.reminder_days!=0";

$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_row($result)) {

    $email = $row[0];

    $mail = new PHPMailer(true);

    //Enable SMTP debugging.

    $mail->SMTPDebug = 0;


    //Set PHPMailer to use SMTP.

    $mail->isSMTP();

    //Set SMTP host name                      

    $mail->Host = "smtp.hostinger.com";

    //Set this to true if SMTP host requires authentication to send email

    $mail->SMTPAuth = true;

    //Provide username and password

    $mail->Username = "info@quotetender.in";

    $mail->Password = "Zxcv@123";

    //If SMTP requires TLS encryption then set it

    $mail->SMTPSecure = "ssl";

    //Set TCP port to connect to

    $mail->Port = 465;

    $mail->From = "info@quotetender.in";


    $mail->FromName = "Quote Tender  ";

    $mail->addAddress($email, "Recepient Name");

    $mail->isHTML(true);


    $mail->Subject = "Reminder: Follow-up on Alot Tender";

    $mail->Body =  "<p> Dear user, <br/>" .
        "This is a friendly reminder to follow up on alloted Tender.<br/><br/>
        The Tender has been alloted. Kindly enter into your login panel and see the details<br/><br/>
        <strong>Quote Tender</strong> <br/>
    Mobile: +91-9870443528 | Email: info@quotender.com ";

    if (!$mail->send()) {

        echo "Mailer Error: " . $mail->ErrorInfo;
    }
}
