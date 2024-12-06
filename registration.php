<?php
error_reporting(0);

require_once "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("login/db/config.php");
// Storing google recaptcha response
// in $recaptcha variable
$recaptcha = $_POST['g-recaptcha-response'];

// Put secret key here, which we get
// from google console
$secret_key = '6LeyShEqAAAAAKVRQAie1sCk9E5rBjvR9Ce0x5k_';

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


// Register user
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $firmname = $_POST['firmName'];
    $email = $_POST['email'];
    $phone = $_POST['mobile'];
    $city = $_POST['city'];
    $password = md5(($_POST['password']));
    $activationToken = bin2hex(random_bytes(16)); // Replace with your activation token
$adminEmail="quotetenderindia@gmail.com";

    date_default_timezone_set('Asia/Kolkata');
    $created_date = date('Y-m-d H:i:s A'); // query for inser user log in to data base

    $qu = "SELECT email_id FROM members WHERE email_id = '$email'";
    $re = mysqli_query($db, $qu);
    //$count=mysqli_num_rows($result);
    $row1 = mysqli_fetch_row($re);
    $username = $row1['0'];

    if ($username) {

        $msg = "Email id  is already exists in our record";
    } else {

        $serial = "";
        $status = 1;

        //    $valid=0;
        $query = "insert into members (name, firm_name, email_id,mobile,city_state, password,created_date,activation_token )values
       ('$name', '$firmname','$email','$phone','$city','$password','$created_date','$activationToken')";
        $quuery = mysqli_query($db, $query);


        if ($quuery > 0) {


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
            $mail->addAddress($adminEmail);

            $mail->isHTML(true);

            $activationLink = 'https://quotetender.in/activate.php?token=' . $activationToken;
            $mail->Subject = "Account Activation";

            $mail->Body =  "<p> Dear User, <br/>
               Your registration process is completed please click here to activate you account. <a href='$activationLink' style='background-color:green; color:#fff; padding:10px; text-decoration:none;'>Click Here</a> </p><br/>
                     <p><b>Name. :-</b> ".  $name  ." </p>
     <p><b>Firm Name. :-</b> ".  $firmname  ." </p>
      <p><b>Mobile No:-</b> ". $phone ."</p>
    <p><b>Email Id :-</b> ".  $email  ."</p>
                <strong>Thanks , <br/>Quote Tender</strong> <br/>
            Mobile: 94176 01244| Email: info@quotender.in ";



            if (!$mail->send()) {

                echo "Mailer Error: " . $mail->ErrorInfo;
            } else {

                $msg = "Message has been sent successfully";
            }

            $msg = "Thank you for completing the registration process Please wait for your account to be Authenticated.  ";

            header("refresh:10;url=login.php");
        } else {

            echo "error in registration page";
        }
    }
}

$web = "SELECT * FROM web_content  ";
$contet = mysqli_query($db, $web);
$cont = mysqli_fetch_row($contet);

$q = "SELECT * FROM category where show_in_menu='yes'";
$q = mysqli_query($db, $q);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Here</title>
    <link rel="shortcut icon" href="assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/icofont.min.css">
    <link rel="stylesheet" href="assets/css/swiper.min.css">
    <link rel="stylesheet" href="assets/css/lightcase.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer>
    </script>
</head>

<body>

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
                        <h2>Register Now</h2>
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

                <h3 class="title">Register Now</h3>
                <?php if (isset($quuery)) {
                    echo " <div class='alert alert-success alert-dismissible fade show' role='alert' style='font-size:16px;' id='successMessage'>
  <strong><i class=' feather  icon icon-info'></i>Success!</strong>$msg.
  
</div> ";
                }

                if ($username) {

                    echo " <div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='successMessage'>
	<strong><i class=' feather  icon icon-info'></i>Error!</strong>$msg.
	
  </div> ";
                }
                ?><br />
                <form class="account-form" action="" method="post" autocomplete="off">
                    <div class="form-group">
                        <input type="text" placeholder=" Name" name="name" required>
                    </div>
                    <div class="form-group">
                        <input type="text" placeholder="Firm Name" name="firmName" required>
                    </div>
                    <div class="form-group">
                        <input type="text" placeholder="Email" name="email" required>
                    </div>
                    <div class="form-group">
                        <input type="Number" placeholder="Mobile" name="mobile" required>
                    </div>
                    <div class="form-group">
                        <input type="text" placeholder="City" name="city" required>
                    </div>
                    <div class="form-group">
                        <input type="password" placeholder=" Password" name="password" required>
                    </div>
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="6LeyShEqAAAAAJIMoyXfN7DmfesxwLNYOgBHIh4N"
                            data-callback="callback" style="border:none;">
                        </div>

                    </div>
                    <div class="form-group">
                        <input type="submit" id="submit" value="Sign Up" class="btn btn-block btn-primary mb-0"
                            name="submit" disabled>
                    </div>
                </form>
                <div class="account-bottom">
                    <span class="d-block cate pt-10">Are you a member? <a href="login.php">Login</a></span>
                    <span class="or"><span>or</span></span>
                    <h5 class="subtitle">Register With Social Media</h5>
                    <ul class="lab-ul social-icons justify-content-center">
                        <li>
                            <a href="#" class="facebook"><i class="icofont-facebook"></i></a>
                        </li>
                        <li>
                            <a href="#" class="twitter"><i class="icofont-twitter"></i></a>
                        </li>
                        <li>
                            <a href="#" class="linkedin"><i class="icofont-linkedin"></i></a>
                        </li>
                        <li>
                            <a href="#" class="instagram"><i class="icofont-instagram"></i></a>
                        </li>
                        <li>
                            <a href="#" class="pinterest"><i class="icofont-pinterest"></i></a>
                        </li>
                    </ul>
                </div>
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