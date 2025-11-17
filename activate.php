<?php
require("login/db/config.php");
error_reporting(0);
if (!isset($_GET['token'])) {
    header("Location: index.php");
    exit;
}

require 'env.php';
require_once "vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$token = $_GET['token'];
$sql = "SELECT * FROM  members WHERE activation_token = '" . $token . "'";
$re = mysqli_query($db, $sql);

//$count=mysqli_num_rows($result);
$row1 = mysqli_fetch_row($re);
$id = $row1[0];

if ($row1 > 0) {
    $updateSql = "UPDATE members SET status = 1 WHERE member_id = '" . $id . "'";
    mysqli_query($db, $updateSql);
    $msg = "Account activated successfully!";

    $template = emailTemplate($db, "WELCOME");

    // Replace placeholders in template
    $search = [
        '{$name}',
        '{$firmName}',
        '{$registeredEmail}',
        '{$supportPhone}',
        '{$enquiryEmail}',
        '{$supportEmail}',
    ];

    $replace = [
        $row1[1],         // name
        $row1[2],         // firm name
        $row1[4],         // registered email
        $supportPhone ?? 'N/A',
        $enquiryMail ?? 'N/A',
        $supportEmail ?? 'N/A',
    ];
    $emailBody = nl2br($template['content_1']) . "<br><br>" . nl2br($template['content_2']);
    // Replace placeholders
    $finalBody = str_replace($search, $replace, $emailBody);

    // echo "<pre>";
    // print_r($finalBody);
    // exit;


    // Attempt to send email first
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USER_NAME');
        $mail->Password = getenv('SMTP_PASSCODE');
        $mail->SMTPSecure = "ssl";
        $mail->Port = getenv('SMTP_PORT');
        $mail->setFrom(getenv('SMTP_USER_NAME'), $emailSettingData['email_from_title'] ?? "Dvepl");
        $mail->addAddress($row1[4], $row1[1]); // Primary recipient
        $mail->isHTML(true);

        // Add CC recipients dynamically
        foreach ($ccEmailData as $ccEmail) { // Use the fetched array
            $mail->addCC($ccEmail['cc_email']); // Use addCC, not addAddress
        }

        // Assuming $emailSettingData['email_from_title'] is defined somewhere relevant
        $activationLink = getenv('BASE_URL') . '/activate.php?token=' . $activationToken;
        // Corrected version with proper precedence
        $logo = getenv('BASE_URL') . "/login/" . ($emailSettingData['logo_url'] ?? "https://dvepl.com/assets/images/logo/dvepl-logo.png");
        $mail->Subject = $template['email_template_subject'] ?? "Welcome to DVEPL";

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; color:#333; line-height:1.6;'>
            <div style='text-align:center;'>
                <img src='" . $logo . "' alt='DVEPL Logo' style='max-width:150px; height:auto; margin-bottom:20px;'>
            </div>
            $finalBody
        </div>
    ";
        $emailSent = $mail->send();

    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        // Email failed to send
    }





    $sql = "UPDATE members SET activation_token = 0 WHERE member_id = '" . $id . "'";
    mysqli_query($db, $sql);
    header("refresh:5;url=login.php");
} else {
    $msg = "Invalid or expired token.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Activation</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" style="color:#000;"> Quote Tender Account Activation</h4>
                        <?php
                        if ($id) {

                            echo " <div class='alert alert-success alert-dismissible fade show' role='alert' style='font-size:16px;' id='successMessage'>
                            <strong><i class=' feather  icon icon-info'></i>Success! </strong>$msg.
                            
                          </div> ";
                        } else {
                            echo " <div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='successMessage'>
                            <strong><i class=' feather  icon icon-info'></i>Error! </strong>$msg.
                            
                          </div> ";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>