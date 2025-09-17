<?php
error_reporting(0);

require_once "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("login/db/config.php");
require 'env.php';


// Register user
if (isset($_POST['firmName']) && $_SERVER['REQUEST_METHOD'] == "POST") {


    try {
        $name = $_POST['name'];
        $firmname = $_POST['firmName'];
        $email = $_POST['email'];
        $phone = $_POST['mobile'];
        $city = $_POST['city'];
        $password = md5(($_POST['password']));
        $activationToken = bin2hex(random_bytes(16)); // Replace with your activation token
        $adminEmail = "quotetenderindia@gmail.com";

        // Storing google recaptcha response
        $recaptcha = $_POST['g-recaptcha-response'];

        // from google console
        $secret_key = '6LeyShEqAAAAAKVRQAie1sCk9E5rBjvR9Ce0x5k_';

        // Hitting request to the URL, Google will
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret='
            . $secret_key . '&response=' . $recaptcha;

        // Making request to verify captcha
        $response = file_get_contents($url);


        $response = json_decode($response);

        // Checking, if response is true or not
        if ($response->success == false) {
            echo json_encode([
                "status" => 400,
                "error" => "Error in Google reCAPTACHA",

            ]);
            exit;
        }

        date_default_timezone_set('Asia/Kolkata');
        $created_date = date('Y-m-d H:i:s A'); // query for inser user log in to data base

        $qu = "SELECT email_id FROM members WHERE email_id = '$email'";
        $re = mysqli_query($db, $qu);
        //$count=mysqli_num_rows($result);
        $row1 = mysqli_fetch_row($re);
        $username = $row1['0'];


        if ($username) {
            echo json_encode([
                "status" => 400,
                "error" => "Email id  is already exists in our record",

            ]);
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
                $mail->isSMTP();
                $mail->Host = getenv('SMTP_HOST');
                $mail->SMTPAuth = true;
                $mail->Username = getenv('SMTP_USER_NAME');
                $mail->Password = getenv('SMTP_PASSCODE');
                $mail->SMTPSecure = "ssl";
                $mail->Port = getenv('SMTP_PORT');
                $mail->setFrom(getenv('SMTP_USER_NAME'), "Quote Tender");
                $mail->addAddress($email, "Recepient Name");
                $mail->addAddress($adminEmail);
                $mail->isHTML(true);
                $activationLink = getenv('BASE_URL') . '/activate.php?token=' . $activationToken;
                $mail->Subject = "Account Activation";
                $mail->Body = "
<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
    <div style='text-align: center; margin-bottom: 30px;'>
        <img src='https://dvepl.com/quotetender/assets/images/logo/logo.png' alt='Quote Tender Logo' style='max-width: 200px; height: auto; display: block; margin: 0 auto;'>
    </div>
    
    <div style='background-color: #f9f9f9; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 1px solid #eee;'>
        <h2 style='color: #4CBB17; text-align: center; margin-bottom: 25px; font-size: 24px;'>Account Activation</h2>
        
        <p style='font-size: 16px; color: #555; margin-bottom: 20px;'>
            Dear <strong>" . htmlspecialchars($name) . "</strong>,
        </p>
        
        <p style='margin-bottom: 25px; font-size: 16px;'>
            Thank you for registering with Quote Tender. Your registration process is completed. 
            Please click the button below to activate your account:
        </p>
        
        <div style='text-align: center; margin: 30px 0;'>
            <a href='" . htmlspecialchars($activationLink) . "' 
            style='background-color: #4CBB17; color: #ffffff; padding: 15px 30px; text-decoration: none; 
                    border-radius: 5px; font-weight: bold; display: inline-block; 
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1); -webkit-box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
                    font-size: 16px; border: none; cursor: pointer;'>
                Activate Account
            </a>
        </div>
        
        <div style='text-align: center; margin: 20px 0;'>
            <p style='margin-bottom: 15px; font-size: 14px; color: #666;'>
                <strong>Activation Link:</strong>
            </p>
            <p style='font-size: 12px; color: #666; word-break: break-all; background-color: #f0f0f0; padding: 10px; border-radius: 4px;'>
                " . htmlspecialchars($activationLink) . "
            </p>
        </div>
        
        <div style='background-color: #e8f5e9; padding: 20px; border-radius: 8px; margin: 25px 0; border: 1px solid #c8e6c9;'>
            <h3 style='color: #2e7d32; margin-top: 0; margin-bottom: 15px; font-size: 18px;'>Registration Details</h3>
            <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                <tr>
                    <td style='padding: 8px 0; border-bottom: 1px solid #ddd;'><strong>Name:</strong></td>
                    <td style='padding: 8px 0; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($name) . "</td>
                </tr>
                <tr>
                    <td style='padding: 8px 0; border-bottom: 1px solid #ddd;'><strong>Firm Name:</strong></td>
                    <td style='padding: 8px 0; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($firmname) . "</td>
                </tr>
                <tr>
                    <td style='padding: 8px 0; border-bottom: 1px solid #ddd;'><strong>Mobile:</strong></td>
                    <td style='padding: 8px 0; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($phone) . "</td>
                </tr>
                <tr>
                    <td style='padding: 8px 0;'><strong>Email:</strong></td>
                    <td style='padding: 8px 0;'>" . htmlspecialchars($email) . "</td>
                </tr>
            </table>
        </div>
    </div>
    
    <div style='margin-top: 30px; text-align: center;'>
        <p style='margin-bottom: 20px; font-size: 14px;'>
            <strong>Thanks & Regards,</strong><br/>
            <span style='color: #4CBB17;'>Admin, Quote Tender</span><br/>
            <span>Mobile: <a href='tel:+919417601244' style='color: #4CBB17; text-decoration: none;'>+91-9417601244</a></span><br/>
            <span>Email: <a href='mailto:help@quotetender.in' style='color: #4CBB17; text-decoration: none;'>help@quotetender.in</a></span>
        </p>

        <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>

        <p style='text-align: center; font-size: 12px; color: #888;'>
            &copy; 2024 Quote Tender. All Rights Reserved.
        </p>
    </div>
</div>";
                if (!$mail->send()) {
                    echo json_encode([
                        "status" => 500,
                        "error" => "Mailer Error: " . $mail->ErrorInfo,

                    ]);
                    exit;
                } else {
                    $msg = "Message has been sent successfully";

                    echo json_encode([
                        "status" => 201,
                        "message" => "Thank you for completing the registration process Please wait for your account to be Authenticated",

                    ]);
                    exit;
                }
            } else {
                echo "error in registration page";

                echo json_encode([
                    "status" => 400,
                    "error" => "error in registration page",

                ]);
                exit;
            }
        }
    } catch (\Throwable $th) {
        echo json_encode([
            "status" => 500,
            "error" => $th->getMessage(),

        ]);
        exit;
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                <form class="account-form">
                    <div class="form-group">
                        <input type="text" placeholder="Name" name="name">
                    </div>
                    <div class="form-group">
                        <input type="text" placeholder="Firm Name" name="firmName">
                    </div>
                    <div class="form-group">
                        <input type="text" placeholder="Email" name="email">
                    </div>
                    <div class="form-group">
                        <input type="Number" placeholder="Mobile" name="mobile">
                    </div>
                    <div class="form-group">
                        <input type="text" placeholder="City" name="city">
                    </div>
                    <div class="form-group">
                        <input type="password" placeholder=" Password" name="password">
                    </div>
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="6LeyShEqAAAAAJIMoyXfN7DmfesxwLNYOgBHIh4N"
                            data-callback="callback" style="border:none;">
                        </div>

                    </div>
                    <div class="form-group">
                        <button type="submit" id="submitBtn" value="Sign Up" class="btn btn-block btn-primary mb-0"
                            name="submit" disabled>Sign Up</button>

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
            const submitButton = document.getElementById("submitBtn");
            submitButton.removeAttribute("disabled");
        }
    </script>

    <script>
        $(document).ready(function () {
            $(document).on("submit", ".account-form", async function (e) {
                e.preventDefault();

                // Get data from input fields
                let name = $('input[name="name"]').val().trim();
                let firmName = $('input[name="firmName"]').val().trim();
                let email = $('input[name="email"]').val().trim();
                let mobile = $('input[name="mobile"]').val().trim();
                let city = $('input[name="city"]').val().trim();
                let password = $('input[name="password"]').val().trim();
                let recaptchaResponse = grecaptcha.getResponse(); // Get reCAPTCHA response


                // Basic validation
                if (!name || !firmName || !email || !mobile || !city || !password) {
                    Swal.fire("Error", "All fields are required. Please fill out the form completely.", "error");
                    return;
                }

                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    Swal.fire("Error", "Please enter a valid email address", "error");
                    return;
                }

                // Mobile validation (assuming 10 digits for India)
                const mobileRegex = /^[0-9]{10}$/;
                if (!mobileRegex.test(mobile)) {
                    Swal.fire("Error", "Please enter a valid 10-digit mobile number", "error");
                    return;
                }

                // Password validation (minimum 6 characters)
                if (password.length < 6) {
                    Swal.fire("Error", "Password must be at least 6 characters long", "error");
                    return;
                }

                // Check if reCAPTCHA is completed
                if (!recaptchaResponse) {
                    Swal.fire("Error", "Please complete the reCAPTCHA verification.", "error");
                    return;
                }

                // Store original button text and disable button during processing
                const $submitBtn = $('#submitBtn');
                const originalBtnText = $submitBtn.html();
                $submitBtn.prop('disabled', true).html('<i class="feather icon-loader"></i>&nbsp;Signing In...');


                let formData = {
                    name: name,
                    firmName: firmName,
                    email: email,
                    mobile: mobile,
                    city: city,
                    password: password,
                    'g-recaptcha-response': recaptchaResponse // Include reCAPTCHA response
                };

                await $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 201) {
                            // Success - reset form
                            $('.account-form')[0].reset();
                            grecaptcha.reset();

                            // Restore button state
                            $submitBtn.prop('disabled', false).val(originalBtnText);

                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Registration successful! Please check your email for activation link.',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            // Reset reCAPTCHA on failure
                            grecaptcha.reset();
                            // Restore button state

                            $submitBtn.prop('disabled', false).html(originalBtnText);

                            Swal.fire("Error", response.error, "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        // Reset reCAPTCHA on error
                        grecaptcha.reset();
                        $('#submitBtn').prop('disabled', false); // Enable button - user needs to complete new reCAPTCHA

                        console.error("AJAX Error:", status, error);
                        console.error("Raw Response:", xhr.responseText);
                        Swal.fire("Error", "An error occurred while processing your request. Please try again.", "error");
                    }
                });
            });

        });
    </script>
</body>

</html>