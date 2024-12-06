<?php
require_once "../vendor/autoload.php";
require_once "../env.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include("db/config.php");

$query = "SELECT m.name, m.firm_name, m.mobile, m.email_id, department.department_name, ur.tenderID, ur.status,
ur.due_date,ur.created_at, sm.name, ur.allotted_at FROM user_tender_requests ur 
inner join members m on ur.member_id= m.member_id left join members sm on ur.selected_user_id= sm.member_id
inner join department on ur.department_id = department.department_id
where (ur.status='Requested' or ur.status='Allotted' )";

$requestedTenders = $allottedTenders = [];

$result = mysqli_query($db, $query);

while ($row = mysqli_fetch_row($result)) {
    if ($row[6] == 'Requested') {
        $requestedTenders[] = $row;
    }
    if ($row[6] == 'Allotted') {
        $allottedTenders[] = $row;
    }
}
// echo "<pre>";

$email = "ydeep9073@gmail.com";

$mail = new PHPMailer(true);

//Enable SMTP debugging.

$mail->SMTPDebug = 1;


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


$mail->FromName = "Quote Tender";

$mail->isHTML(true);


$mail->Subject = "List of All Tender Requests";

$body =  "<p> Dear user, <br/>" .
    "These are the list of Tender requests and Allotted Tender requests<br/><br/>";
if (count($requestedTenders) > 0) {

$body .= "<strong>Requested Tenders</strong> <br/>
<table style='width: 100%; border-collapse: collapse;' cellspacing='0' cellpadding='10'>
    <tr style='background-color: #333; color: #fff;'>
        <th style='padding: 8px; border: 1px solid #ddd;'>User Name</th>
        <th style='padding: 8px; border: 1px solid #ddd;'>Firm Name</th>
        <th style='padding: 8px; border: 1px solid #ddd;'>Mobile</th>
        <th style='padding: 8px; border: 1px solid #ddd;'>Email</th>
        <th style='padding: 8px; border: 1px solid #ddd;'>Department</th>
        <th style='padding: 8px; border: 1px solid #ddd;'>Tender ID</th>
        <th style='padding: 8px; border: 1px solid #ddd;'>Status</th>
        <th style='padding: 8px; border: 1px solid #ddd;'>Due Date</th>
        <th style='padding: 8px; border: 1px solid #ddd;'>Requested Date</th>
    </tr>";

foreach ($requestedTenders as $index => $item) {
    $mail->addAddress($email, "Recepient Name");
    $rowColor = $index % 2 === 0 ? '#f9f9f9' : '#ffffff';
    $body .= "<tr style='background-color: $rowColor;'>
        <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[0] . "</td>
        <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[1] . "</td>
        <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[2] . "</td>
        <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[3] . "</td>
        <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[4] . "</td>
        <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[5] . "</td>
        <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[6] . "</td>
        <td style='padding: 8px; border: 1px solid #ddd;'>" . date_format(date_create($item[7]), 'd-m-Y') . "</td>
        <td style='padding: 8px; border: 1px solid #ddd;'>" . date_format(date_create($item[8]), 'd-m-Y')  . "</td>
    </tr>";
}
}
$body .= "</table><br/><br/>";

if (count($allottedTenders) > 0) {
    $body .= "<div style='overflow-x: auto;'>
    <strong>Allotted Tenders</strong> <br/>
    <table style='width: 100%; border-collapse: collapse;' cellspacing='0' cellpadding='10'>
        <tr style='background-color: #333; color: #fff;'>
            <th style='padding: 8px; border: 1px solid #ddd;'>User Name</th>
            <th style='padding: 8px; border: 1px solid #ddd;'>Firm Name</th>
            <th style='padding: 8px; border: 1px solid #ddd;'>Mobile</th>
            <th style='padding: 8px; border: 1px solid #ddd;'>Email</th>
            <th style='padding: 8px; border: 1px solid #ddd;'>Department</th>
            <th style='padding: 8px; border: 1px solid #ddd;'>Tender ID</th>
            <th style='padding: 8px; border: 1px solid #ddd;'>Status</th>
            <th style='padding: 8px; border: 1px solid #ddd;'>Due Date</th>
            <th style='padding: 8px; border: 1px solid #ddd;'>Requested Date</th>
            <th style='padding: 8px; border: 1px solid #ddd;'>Allotted User</th>
            <th style='padding: 8px; border: 1px solid #ddd;'>Allotted Date</th>
        </tr>";
    
    foreach ($allottedTenders as $index => $item) {
        $mail->addAddress($email, "Recepient Name");
        $rowColor = $index % 2 === 0 ? '#f9f9f9' : '#ffffff';
        $body .= "<tr style='background-color: $rowColor;'>
            <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[0] . "</td>
            <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[1] . "</td>
            <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[2] . "</td>
            <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[3] . "</td>
            <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[4] . "</td>
            <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[5] . "</td>
            <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[6] . "</td>
            <td style='padding: 8px; border: 1px solid #ddd;'>" . date_format(date_create($item[7]), 'd-m-Y') . "</td>
            <td style='padding: 8px; border: 1px solid #ddd;'>" . date_format(date_create($item[8]), 'd-m-Y')  . "</td>
            <td style='padding: 8px; border: 1px solid #ddd;'>" . $item[9] . "</td>
            <td style='padding: 8px; border: 1px solid #ddd;'>" . date_format(date_create($item[10]), 'd-m-Y')  . "</td>
        </tr>";
    }
    }
$body .= "</table></div><br/><br/>
    Mobile: +91-9870443528 | Email: info@quotender.com ";

$mail->Body = $body;

if (!$mail->send()) {

    echo "Mailer Error: " . $mail->ErrorInfo;
}

?>