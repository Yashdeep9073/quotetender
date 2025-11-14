<?php
error_reporting(0);

require_once "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("login/db/config.php");
require 'env.php';



$web = "SELECT * FROM web_content  ";
$contet = mysqli_query($db, $web);
$cont = mysqli_fetch_row($contet);

$q = "SELECT * FROM category where show_in_menu='yes'";
$q = mysqli_query($db, $q);

try {
    // Fetch unique, non-empty cities only
    $stmtFetchStates = $db->prepare("SELECT * FROM state WHERE is_active = 1 ");
    $stmtFetchStates->execute();
    $states = $stmtFetchStates->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (\Throwable $th) {
    //throw $th;
}

// Register user
if (isset($_POST['firmName']) && $_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        // Validate required fields
        $required_fields = ['name', 'firmName', 'email', 'mobile', 'state', 'city', 'password'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field])) {
                echo json_encode([
                    "status" => 400,
                    "error" => "Missing required field: " . $field
                ]);
                exit;
            }
        }

        // Sanitize input data
        $name = trim($_POST['name']);
        $firmname = trim($_POST['firmName']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['mobile']);
        $state = trim($_POST['state']); // Assuming this is the state_code
        $city = trim($_POST['city']);
        $password = trim($_POST['password']);

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => 400,
                "error" => "Invalid email format"
            ]);
            exit;
        }

        // reCAPTCHA validation
        $recaptcha = $_POST['g-recaptcha-response'];
        $secret_key = '6LeyShEqAAAAAKVRQAie1sCk9E5rBjvR9Ce0x5k_';
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha;
        $response = json_decode(file_get_contents($url));

        if (!$response->success) {
            echo json_encode([
                "status" => 400,
                "error" => "Error in Google reCAPTCHA"
            ]);
            exit;
        }

        // Set timezone and create date
        date_default_timezone_set('Asia/Kolkata');
        $created_date = date('Y-m-d H:i:s A');

        // Start transaction to ensure atomicity: insert only if email sends
        mysqli_autocommit($db, FALSE); // Turn off auto-commit
        $transaction_started = true; // Flag to track transaction state

        // Check if email already exists - USING PREPARED STATEMENT
        $check_query = "SELECT email_id FROM members WHERE email_id = ?";
        $check_stmt = mysqli_prepare($db, $check_query);
        if (!$check_stmt) {
            throw new Exception("Prepare check query failed: " . mysqli_error($db));
        }
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($result) > 0) {
            mysqli_stmt_close($check_stmt);
            mysqli_rollback($db); // Rollback if transaction was started
            mysqli_autocommit($db, TRUE); // Re-enable auto-commit
            echo json_encode([
                "status" => 400,
                "error" => "Email ID already exists in our records"
            ]);
            exit;
        }
        mysqli_stmt_close($check_stmt);


        // Prepare activation token and hash password
        $password_hash = md5($password); // Consider using password_hash() instead
        $activationToken = bin2hex(random_bytes(16));
        $status = '0'; // Default status

        // Prepare the insert statement
        $insert_query = "INSERT INTO members (name, firm_name, email_id, mobile, city_state, state_code, password, created_date, activation_token, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($db, $insert_query);
        if (!$insert_stmt) {
            throw new Exception("Prepare insert query failed: " . mysqli_error($db));
        }
        mysqli_stmt_bind_param($insert_stmt, "ssssssssss", $name, $firmname, $email, $phone, $city, $state, $password_hash, $created_date, $activationToken, $status);

        // Attempt to send email first
        $mail = new PHPMailer(true);
        $emailSent = false;
        try {

            $template = emailTemplate($db, "VERIFICATION");

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
            $mail->addAddress($email, $name); // Primary recipient
            $mail->isHTML(true);

            // Add CC recipients dynamically
            foreach ($ccEmailData as $ccEmail) { // Use the fetched array
                $mail->addCC($ccEmail['cc_email']); // Use addCC, not addAddress
            }

            // Assuming $emailSettingData['email_from_title'] is defined somewhere relevant
            $activationLink = getenv('BASE_URL') . '/activate.php?token=' . $activationToken;


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
                $activationLink ?? 'N/A',
            ];


            $emailBody = nl2br($template['content_1']) . "<br><br>" . nl2br($template['content_2']);
            // Replace placeholders
            $finalBody = str_replace($search, $replace, $emailBody);


            // Corrected version with proper precedence
            $logo = getenv('BASE_URL') . "/login/" . ($emailSettingData['logo_url'] ?? "https://dvepl.com/assets/images/logo/dvepl-logo.png");
            $mail->Subject = $template['email_template_subject'] ?? "Account Activation";


            $mail->Body = "
                        <div style='font-family: Arial, sans-serif; color:#333; line-height:1.6;'>
                            <div style='text-align:center;'>
                                <img src='" . $logo . "' alt='DVEPL Logo' style='max-width:150px; height:auto; margin-bottom:20px;'>
                            </div>
                            $finalBody
                        </div>
                ";

            // $mail->Body = "
            // <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            //     <div style='text-align: center; margin-bottom: 30px;'>
            //         <img src='" . $logo . "' alt='Quote Tender Logo' style='max-width: 200px; height: auto; display: block; margin: 0 auto;'>
            //     </div>

            //     <div style='background-color: #f9f9f9; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 1px solid #eee;'>
            //         <h2 style='color: #4CBB17; text-align: center; margin-bottom: 25px; font-size: 24px;'>Account Activation</h2>

            //         <p style='font-size: 16px; color: #555; margin-bottom: 20px;'>
            //             Dear <strong>" . htmlspecialchars($name) . "</strong>,
            //         </p>

            //         <p style='margin-bottom: 25px; font-size: 16px;'>
            //             Thank you for registering with Quote Tender. Your registration process is completed.
            //             Please click the button below to activate your account:
            //         </p>

            //         <div style='text-align: center; margin: 30px 0;'>
            //             <a href='" . htmlspecialchars($activationLink) . "'
            //             style='background-color: #4CBB17; color: #ffffff; padding: 15px 30px; text-decoration: none;
            //                     border-radius: 5px; font-weight: bold; display: inline-block;
            //                     box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 16px; border: none; cursor: pointer;'>
            //                 Activate Account
            //             </a>
            //         </div>

            //         <div style='text-align: center; margin: 20px 0;'>
            //             <p style='margin-bottom: 15px; font-size: 14px; color: #666;'>
            //                 <strong>Activation Link:</strong>
            //             </p>
            //             <p style='font-size: 12px; color: #666; word-break: break-all; background-color: #f0f0f0; padding: 10px; border-radius: 4px;'>
            //                 " . htmlspecialchars($activationLink) . "
            //             </p>
            //         </div>
            //     </div>
            // </div>";

            $emailSent = $mail->send();

        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $e->getMessage());
            // Email failed to send
        }

        if ($emailSent) {
            // If email sent successfully, execute the insert
            if (mysqli_stmt_execute($insert_stmt)) {
                mysqli_commit($db); // Commit the transaction
                echo json_encode([
                    "status" => 201,
                    "message" => "Thank you for completing the registration process. Please check your email (and CC recipients) to activate your account."
                ]);
            } else {
                mysqli_rollback($db); // Rollback on insert failure
                echo json_encode([
                    "status" => 500,
                    "error" => "Registration failed during database save after email sent: " . mysqli_error($db)
                ]);
            }
        } else {
            // Email failed to send, rollback the transaction
            mysqli_rollback($db);
            echo json_encode([
                "status" => 500,
                "error" => "Registration failed: Could not send activation email."
            ]);
        }

        // Close the insert statement
        if (isset($insert_stmt)) {
            mysqli_stmt_close($insert_stmt);
        }

        // Re-enable auto-commit if transaction was started
        if (isset($transaction_started)) {
            mysqli_autocommit($db, TRUE);
        }

    } catch (Exception $e) {
        // Rollback in case of other exceptions during the process
        if (isset($transaction_started)) {
            mysqli_rollback($db);
            mysqli_autocommit($db, TRUE);
        }
        echo json_encode([
            "status" => 500,
            "error" => "An error occurred: " . $e->getMessage()
        ]);
    }
    exit;
}


// fetch city by state code with ajax
if (isset($_POST['stateCode']) && $_SERVER['REQUEST_METHOD'] == "POST") {
    try {

        $stateCode = $_POST['stateCode'];

        if (empty($stateCode)) {
            echo json_encode([
                "status" => 400,
                "error" => "Invalid state",
            ]);
            exit;
        }

        $db->begin_transaction();

        // Fetch unique, non-empty cities only
        $stmtFetchCities = $db->prepare("SELECT * FROM cities WHERE state_code = ? AND is_active = 1");
        $stmtFetchCities->bind_param("s", $stateCode);
        $stmtFetchCities->execute();
        $cities = $stmtFetchCities->get_result()->fetch_all(MYSQLI_ASSOC);


        echo json_encode([
            "status" => 200,
            "data" => $cities,
        ]);
        exit;

    } catch (\Throwable $th) {
        //throw $th;
        echo json_encode([
            "status" => 500,
            "error" => $th->getMessage(),
        ]);
        exit;
    }
}
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

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <style>
        /* Force Select2 to take full width */
        .select2-container {
            width: 100% !important;
        }

        /* Style for single select box */
        .select2-container--default .select2-selection--single {
            height: auto !important;
            min-height: 40px;
            border: 1px solid #d8d8d8 !important;
            border-radius: 2px !important;
            width: 100% !important;
        }

        /* Rendered text inside */
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            text-align: left !important;
            line-height: 38px !important;
            padding-left: 12px !important;
            padding-right: 20px !important;
            /* font-size: 14px; */
            white-space: normal;
            /* allows wrapping on smaller screens */
        }

        /* Dropdown arrow */
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        /* Mobile-friendly dropdown */
        @media (max-width: 600px) {
            .select2-container--default .select2-selection--single {
                min-height: 45px;
                font-size: 16px;
                /* bigger text for mobile */
            }

            .select2-dropdown {
                font-size: 16px;
                /* dropdown items bigger */
            }
        }
    </style>


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
                        <select name="state" class="js-example-basic-single select-state">
                            <option>State</option>
                            <?php foreach ($states as $state) { ?>
                                <option value="<?= $state['state_code'] ?>"><?= $state['state_name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="city" class="js-example-basic-single select-city">
                            <option>City</option>
                        </select>
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
                    <!-- <span class="or"><span>or</span></span>
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
                    </ul> -->
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

            $(document).on("change", ".select-state", async function (e) {
                let stateCode = $(this).val();
                await $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { stateCode: stateCode },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 200) {
                            let citySelect = $(".select-city");
                            citySelect.empty(); // clear old options
                            citySelect.append('<option value="">Select City</option>');
                            $.each(response.data, function (index, city) {
                                citySelect.append(
                                    `<option value="${city.city_id}">${city.city_name}</option>`
                                );
                            });
                        } else {
                            Swal.fire("No Data", "No cities found.", "warning");
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        console.error("Raw Response:", xhr.responseText);
                        Swal.fire("Error", "An error occurred while processing your request. Please try again.", "error");
                    }
                });
            });

            $('.js-example-basic-single').select2();

            $(document).on("submit", ".account-form", async function (e) {
                e.preventDefault();

                // Get data from input fields
                let name = $('input[name="name"]').val().trim();
                let firmName = $('input[name="firmName"]').val().trim();
                let email = $('input[name="email"]').val().trim();
                let mobile = $('input[name="mobile"]').val().trim();
                let state = $('select[name="state"]').val().trim();
                let city = $('select[name="city"]').val().trim();
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
                    state: state,
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