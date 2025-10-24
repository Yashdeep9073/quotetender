<?php
session_start();
require("login/db/config.php");
require_once "vendor/autoload.php";
error_reporting(0);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// Storing google recaptcha response
// in $recaptcha variable
$recaptcha = $_POST['g-recaptcha-response'];

// Put secret key here, which we get
// from google console
$secret_key = '6LfSlQInAAAAAP1xtljxRqKdVToEOaAEuEsvVGT7';

// Hitting request to the URL, Google will
// respond with success or error scenario
$url = 'https://www.google.com/recaptcha/api/siteverify?secret='
    . $secret_key . '&response=' . $recaptcha;

// Making request to verify captcha
$response = file_get_contents($url);

// Response return by google is in
// JSON format, so we have to parse
// that json
$response = json_decode($response);

// Checking, if response is true or not
if ($response->success == true) {
    $msg = "Google reCAPTACHA verified";
} else {
    $msg = "Error in Google reCAPTACHA";
}

if (isset($_POST['submit'])) {
    $email = $_POST['reset'];

    $qu = "SELECT email_id FROM members WHERE email_id = '$email'";
    $re = mysqli_query($db, $qu);
    //$count=mysqli_num_rows($result);
    $row1 = mysqli_fetch_row($re);
    $username = $row1['0'];

    $activationToken = bin2hex(random_bytes(16)); // Replace with your activation token
    date_default_timezone_set('Asia/Kolkata');
    $expiryTime = date('Y-m-d H:i:s', strtotime('+5 hour'));

    if (isset($username)) {

        $updateSql = "UPDATE members SET expiry_time ='$expiryTime',  activation_token ='$activationToken' WHERE email_id = '"  . $username . "'";
        mysqli_query($db, $updateSql);

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

                $mail->Username = "quotetenderindia@gmail.com";

                $mail->Password = "Zxcv@123";

                //If SMTP requires TLS encryption then set it

                $mail->SMTPSecure = "ssl";

                //Set TCP port to connect to

                $mail->Port = 465;

                $mail->From = "quotetenderindia@gmail.com";

        $mail->FromName = "Quote Tender  ";

        $mail->addAddress($email, "Recepient Name");

        $mail->isHTML(true);

        $activationLink = 'https://www.quotetender.in/reset-password.php?token=' . $activationToken;
        $mail->Subject = "Reset Password";

        $mail->Body =  "<p> Dear user, <br/>" .
            "Click the following link to reset your password: <a href='$activationLink' style='background-color:green; color:#fff; padding:10px; text-decoration:none;'>Click Here</a> <br/><br/>
        <strong>Admin Quote Tender</strong> <br/>
    Mobile: +91-994176 01244  | Email: info@quotender.com ";


        if (!$mail->send()) {

            echo "Mailer Error: " . $mail->ErrorInfo;
        }
        $_SESSION['status'] = "<div class='alert alert-success alert-dismissible fade show' role='alert' style='font-size:16px;'
        id='successMessage'>
        <strong><i class=' feather  icon icon-info'></i>Success !</strong>Password reset link sent to your email.

    </div>";
        header("location:forgetpass.php");
        exit(0);
    } else {

        $_SESSION['status'] = "<div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;'
        id='successMessage'>
        <strong><i class=' feather  icon icon-info'></i>Error!</strong>Email id  is not exists in our record

    </div>";
        header("location:forgetpass.php");
        exit(0);
    }
}
echo $_SESSION['status'];