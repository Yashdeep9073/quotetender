<?php

session_start();
require("login/db/config.php");
error_reporting(0);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$web = "SELECT * FROM web_content";
$contet = mysqli_query($db, $web);
$cont = mysqli_fetch_row($contet);

$q = "SELECT * FROM category where show_in_menu='yes'";
$q = mysqli_query($db, $q);



if (isset($_POST['submit'])) {

    try {

        $recaptcha = $_POST['g-recaptcha-response'];

        $secret_key = '6LfSlQInAAAAAP1xtljxRqKdVToEOaAEuEsvVGT7';

        $url = 'https://www.google.com/recaptcha/api/siteverify?secret='
            . $secret_key . '&response=' . $recaptcha;

        $response = file_get_contents($url);

        $response = json_decode($response);

        if ($response->success == true) {
            $msg = "Google reCAPTACHA verified";
        } else {
            $msg = "Error in Google reCAPTACHA";
        }


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

            $updateSql = "UPDATE members SET expiry_time ='$expiryTime',  activation_token ='$activationToken' WHERE email_id = '" . $username . "'";
            mysqli_query($db, $updateSql);

            $template = emailTemplate($db, "PASSWORD_RESET");

            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER_NAME');
            $mail->Password = getenv('SMTP_PASSCODE');
            $mail->SMTPSecure = "ssl";
            $mail->Port = getenv('SMTP_PORT');
            $mail->setFrom(getenv('SMTP_USER_NAME'), $emailSettingData['email_from_title'] ?? "Dvepl");
            $mail->addAddress($email, $name); // Primary recipient
            $mail->isHTML(true);

            // Add CC recipients dynamically
            foreach ($ccEmailData as $ccEmail) { // Use the fetched array
                $mail->addCC($ccEmail['cc_email']); // Use addCC, not addAddress
            }

            $resetLink = getenv('BASE_URL') . "/reset-password.php?token=" . $activationToken;


            // Replace placeholders in template
            $search = [
                '{$name}',
                '{$supportPhone}',
                '{$enquiryEmail}',
                '{$link}',
                '{$supportEmail}',
            ];


            $replace = [
                $name,         // name
                $supportPhone ?? 'N/A',
                $enquiryMail ?? 'N/A',
                $resetLink ?? 'N/A',
                $supportEmail
            ];
            $emailBody = nl2br($template['content_1']) . "<br><br>" . nl2br($template['content_2']);
            // Replace placeholders
            $finalBody = str_replace($search, $replace, $emailBody);

            // Corrected version with proper precedence
            $mail->Subject = $template['email_template_subject'] ?? "Account Activation";

            $mail->Body = "
                        <div style='font-family: Arial, sans-serif; color:#333; line-height:1.6;'>
                            <div style='text-align:center;'>
                                <img src='" . $logo . "' alt='DVEPL Logo' style='max-width:150px; height:auto; margin-bottom:20px;'>
                            </div>
                            $finalBody
                        </div>
                ";

            if (!$mail->send()) {
                $_SESSION['error'] = "Mailer Error: " . $mail->ErrorInfo;
            }
            $_SESSION['success'] = "Password reset link sent to your email.";
            header("location:forgetpass.php");
            exit(0);
        } else {
            $_SESSION['error'] = "Error! Email id  is not exists in our record";
            header("location:forgetpass.php");
            exit(0);
        }
    } catch (\Throwable $th) {
        $_SESSION['error'] = $th->getMessage();
    }

}




?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="shortcut icon" href="assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/icofont.min.css">
    <link rel="stylesheet" href="assets/css/swiper.min.css">
    <link rel="stylesheet" href="assets/css/lightcase.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer>
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
</head>

<body>


    <?php if (isset($_SESSION['success'])) { ?>
        <script>
            const notyf = new Notyf({
                position: {
                    x: 'center',
                    y: 'top'
                },
                types: [
                    {
                        type: 'success',
                        background: '#26c975', // Change background color
                        textColor: '#FFFFFF',  // Change text color
                        dismissible: true,
                        duration: 10000
                    }
                ]
            });
            notyf.success("<?php echo $_SESSION['success']; ?>");
        </script>
        <?php
        unset($_SESSION['success']);
        ?>
    <?php } ?>

    <?php if (isset($_SESSION['error'])) { ?>
        <script>
            const notyf = new Notyf({
                position: {
                    x: 'center',
                    y: 'top'
                },
                types: [
                    {
                        type: 'error',
                        background: '#ff1916',
                        textColor: '#FFFFFF',
                        dismissible: true,
                        duration: 10000
                    }
                ]
            });
            notyf.error("<?php echo $_SESSION['error']; ?>");
        </script>
        <?php
        unset($_SESSION['error']);
        ?>
    <?php } ?>

    <!-- preloader start here -->
    <div class="preloader">
        <div class="preloader-inner">
            <div class="preloader-icon">
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
    <!-- preloader ending here -->


    <!-- scrollToTop start here -->
    <a href="#" class="scrollToTop"><i class="icofont-rounded-up"></i></a>
    <!-- scrollToTop ending here -->

    <header class="header-section">
        <div class="header-top">
            <?php include_once("header.php"); ?>
        </div>
        <div class="header-bottom">
            <?php include_once("menu.php"); ?>
        </div>
    </header>
    <!-- header section ending here -->

    <!-- Page Header section start here -->
    <div class="pageheader-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="pageheader-content text-center">
                        <h2>Forgot Password</h2>
                        <nav aria-label="breadcrumb">

                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header section ending here -->

    <!-- Login Section Section Starts Here -->
    <div class="login-section padding-tb section-bg">
        <div class="container">

            <div class="account-wrapper">
                <?php

                if (isset($_SESSION['status'])) {
                    $msg = $_SESSION['status'];

                    echo $msg;

                    unset($_SESSION['status']);
                }
                ?>
                <h3 class="title">Forget Password</h3>
                <form class="account-form" action="" method="post" autocomplete="off">
                    <div class="form-group">
                        <input type="email" placeholder="Enter Registered Email Id" required name="reset">
                    </div>
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="6LeyShEqAAAAAJIMoyXfN7DmfesxwLNYOgBHIh4N"
                            data-callback="callback" style="border:none;">
                        </div>
                    </div>
                    <div class="form-group text-center">
                        <input type="submit" id="submit" value="Reset my Password"
                            class="btn btn-block btn-primary mb-0" name="submit" disabled>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Login Section Section Ends Here -->


    <!-- footer -->
    <div class="news-footer-wrap">
        <div class="fs-shape">
            <img src="assets/images/shape-img/03.png" alt="fst" class="fst-1">
            <img src="assets/images/shape-img/04.png" alt="fst" class="fst-2">
        </div>
        <!-- Newsletter Section Start Here -->
        <div class="news-letter">
            <div class="container">
                <div class="section-wrapper">
                    <div class="news-title">
                        <h3>Want Us To Email You About Special Offers And Updates?</h3>
                    </div>
                    <div class="news-form">
                        <form action="https://demos.codexcoder.com/">
                            <div class="nf-list">
                                <input type="email" name="email" placeholder="Enter Your Email">
                                <input type="submit" name="submit" value="Subscribe Now">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Newsletter Section Ending Here -->

        <!-- Footer Section Start Here -->
        <footer>
            <?php include_once("footer.php"); ?>
        </footer>
        <!-- Footer Section Ending Here -->
    </div>
    <!-- footer -->



    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/swiper.min.js"></script>
    <script src="assets/js/progress.js"></script>
    <script src="assets/js/lightcase.js"></script>
    <script src="assets/js/counter-up.js"></script>
    <script src="assets/js/isotope.pkgd.js"></script>
    <script src="assets/js/functions.js"></script>

    <script type="text/javascript">
        function callback() {
            const submitButton = document.getElementById("submit");
            submitButton.removeAttribute("disabled");
        }
    </script>
</body>

</html>