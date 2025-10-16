<?php
session_start();
error_reporting(0);

// username and password sent from form 
require "./db/config.php";
require './utility/otpGenerator.php';
require '../env.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    $myusername2 = mysqli_real_escape_string($db, $_POST['username']);
    $mypassword = mysqli_real_escape_string($db, $_POST['password']);
    $mypassword = md5($mypassword);


    $sql = "SELECT * FROM admin WHERE username = '$myusername2' and password = '$mypassword' and status='1'";
    $result = mysqli_query($db, $sql);
    $adminData = mysqli_fetch_row($result);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $active = $row['active'];


    // Storing google recaptcha response
    // in $recaptcha variable
    $recaptcha = $_POST['g-recaptcha-response'];

    // Check if reCAPTCHA response exists
    if (empty($recaptcha)) {
        echo json_encode([
            "success" => false,
            "message" => "reCAPTCHA verification is required."
        ]);
        exit();
    }

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
    if ($response->success != true) {
        echo json_encode([
            "success" => false,
            "message" => "Error in Google reCAPTCHA verification. Please try again."
        ]);
        exit();
    }

    $count = mysqli_num_rows($result);

    // If result matched $myusername2 and $mypassword, table row must be 1 row

    if ($count == 1) {

        $_SESSION['login_user'] = $myusername2;
        $_SESSION['login_user_id'] = $adminData[0];
        $_SESSION['login_user_id'] = $adminData[0];

        /*?>setcookie('password',$myusername2,time() + (86400 * 7));<?php */

        $_SESSION['id'] = session_id(); // hold the user id in session

        $ipAddress = $_SERVER['REMOTE_ADDR']; // get the user ip

        // Your API Key from ipinfo.io (you can use a free tier key or subscribe for more features)
        $accessToken = 'c922e696cae131'; // Replace with your ipinfo.io token

        // IPinfo API endpoint
        $url = "http://ipinfo.io/{$ipAddress}/json?token={$accessToken}";

        // Initialize a cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Execute the cURL session and get the response
        $response = curl_exec($ch);

        // Close cURL session
        curl_close($ch);

        // Decode the JSON response
        $data = json_decode($response, true);

        $ip = $data['ip'];
        $city = $data['city'];
        $region = $data['region'];

        date_default_timezone_set('Asia/Kolkata');
        $action = date('Y-m-d H:i:s A'); // query for inser user log in to data base
        mysqli_query($db, "insert into user_logs(user_id,username,user_ip,login_time,city,region) values('" . $_SESSION['id'] . "','" . $_SESSION['login_user'] . "','$ip','$action','$city','$region')");

        session_regenerate_id(true);
        $st = 1;

        $st = base64_encode($st);

        // otpGenerate otpGenerate(_SESSION['login_user_id'], $db, $mail)

        // $otpResponse = otpGenerate($adminData[9], $db, $mail);
        // $otp = $otpResponse['otp'];
        // $otpId = base64_encode($otpResponse['otpId']);

        echo json_encode([
            "success" => true,
            "message" => "Login successful.",
            // "otpId" => $otpId,
        ]);
        exit();

        // echo json_encode([
        //     "success" => true,
        //     "message" => "Login successful.",
        //     "url" => "dashboard.php?loginin=" . $st,
        // ]);
        // exit();

        // header("location: dashboard.php?loginin=$st");
    } else {
        $error = "Your Username or Password is invalid";
        echo json_encode([
            "success" => false,
            "message" => $error
        ]);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otpId'])) {

    $otp = trim($_POST['otp']);
    $otpId = base64_decode($_POST['otpId']);

    // Validate OTP format
    if (!preg_match('/^\d{6}$/', $otp)) {
        echo json_encode([
            "success" => false,
            "message" => "Please enter a valid 6-digit OTP."
        ]);
        exit();
    }

    // Prepare and execute query to validate OTP
    $stmtIsValidOtp = $db->prepare("SELECT * FROM admin_otp WHERE otp_id = ? AND is_used = 0");

    if (!$stmtIsValidOtp) {
        echo json_encode([
            "success" => false,
            "message" => "Database error occurred. Please try again."
        ]);
        exit();
    }

    $stmtIsValidOtp->bind_param("i", $otpId);
    $stmtIsValidOtp->execute();
    $result = $stmtIsValidOtp->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $storedOtp = $row['otp_code'];
        $adminId = $row['admin_id'];

        // Compare server OTP with client OTP
        if ($otp !== $storedOtp) {
            echo json_encode([
                "success" => false,
                "message" => "Invalid OTP. Please try again."
            ]);
            exit();
        }

        // OTP is valid - mark as used
        $stmtUpdateOtp = $db->prepare("UPDATE admin_otp SET is_used = 1 WHERE otp_id = ?");
        $stmtUpdateOtp->bind_param("i", $otpId);
        $stmtUpdateOtp->execute();

        // Admin
        // Prepare and execute query to validate OTP
        $stmtIsValidAdmin = $db->prepare("SELECT * FROM admin WHERE id = ? AND status = 1");

        if (!$stmtIsValidAdmin) {
            echo json_encode([
                "success" => false,
                "message" => "Database error occurred. Please try again."
            ]);
            exit();
        }

        $stmtIsValidAdmin->bind_param("i", $adminId);
        $stmtIsValidAdmin->execute();
        $resultAdmin = $stmtIsValidAdmin->get_result();
        $rowAdmin = $resultAdmin->fetch_assoc();

        $_SESSION['login_user'] = $rowAdmin['username'];
        $_SESSION['login_user_id'] = $rowAdmin['id'];

        echo json_encode([
            "success" => true,
            "message" => "OTP verified successfully."
        ]);
        exit();
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid or expired OTP. Please try again."
        ]);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['userId'])) {

    $adminId = $_POST['userId'];

    // Validate OTP format
    if (!preg_match('/^[0-9]*$/', $userId)) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid user id ."
        ]);
        exit();
    }


    // Admin
    $stmtIsValidAdmin = $db->prepare("SELECT * FROM admin WHERE id = ? AND status = 1");
    if (!$stmtIsValidAdmin) {
        error_log("Admin Select Prepare Error: " . $db->error);
        return [
            "success" => false,
            "message" => "Failed to prepare admin selection: " . $db->error
        ];
    }

    $stmtIsValidAdmin->bind_param("i", $adminId);
    if (!$stmtIsValidAdmin->execute()) {
        error_log("Admin Select Execute Error: " . $stmtIsValidAdmin->error);
        return [
            "success" => false,
            "message" => "Failed to execute admin selection: " . $stmtIsValidAdmin->error
        ];
    }

    $resultAdmin = $stmtIsValidAdmin->get_result();
    if ($resultAdmin->num_rows === 0) {
        return [
            "success" => false,
            "message" => "Admin not found or inactive"
        ];
    }

    $rowAdmin = $resultAdmin->fetch_assoc();
    $adminId = $rowAdmin['id'];
    $adminEmail = $rowAdmin['email'];

    $otpResponse = otpGenerate($adminId, $db, $mail);
    $otp = $otpResponse['otp'];
    $otpId = base64_encode($otpResponse['otpId']);

    echo json_encode([
        "success" => true,
        "message" => "New OTP has been sent to your registered email.",
        "otpId" => $otpId,
    ]);
    exit();
}

if (isset($_SESSION["login_user"])) {
    header("location: dashboard.php?loginin=$st");
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Welcome to Quote tender</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="#" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/style.css">
    <script language="javascript" type="text/javascript">
        window.history.forward();
    </script>

    <script src="https://www.google.com/recaptcha/api.js" async defer>
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<div class="auth-wrapper align-items-stretch aut-bg-img">
    <div class="flex-grow-1">
        <div class="h-100 d-md-flex align-items-center auth-side-img">

        </div>
        <div class="auth-side-form" style="background-color:#f8f7f2;">

            <form class="login-form" action="" method="post">
                <div class=" auth-content">
                    <img src="https://dvepl.com/assets/images/logo/dvepl-logo.png" alt="" class="img-fluid">
                    <hr />

                    <h3 class="mb-4 f-w-400">Signin</h3>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background-color:#33cc33;color:#fff;"><i
                                    class="feather icon-mail"></i></span>
                        </div>

                        <input type="text" class="form-control" placeholder="Username" name="username" id="userName"
                            style="border-color:#33cc33">
                    </div>
                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background-color:#33cc33;color:#fff;"><i
                                    class="feather icon-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" placeholder="Password" name="password"
                            id="userPassword" style="border-color:#33cc33">
                    </div>
                    <div class="g-recaptcha" data-sitekey="6LeyShEqAAAAAJIMoyXfN7DmfesxwLNYOgBHIh4N"
                        data-callback="callback" data-expired-callback="expiredCallback" style="border:none;"
                        align="center">
                    </div>
                    <button type="submit" class="btn btn-secondary " name="submit" id="submitBtn">
                        <i class="feather icon-save lg"></i>&nbsp;Sign In
                    </button>
            </form>
            <br /> <br />
            <hr style="border-color:#33cc33">
            <p style="color:#000;">HelpDesk/Helpline No:+91-9870443528</p>
        </div>
    </div>
</div>
</div>
</div>


<script src="assets/js/vendor-all.min.js"></script>
<script src="assets/js/plugins/bootstrap.min.js"></script>
<script src="assets/js/waves.min.js"></script>
</body>

<script>
    $(document).ready(function () {

        let currentOtpId = null;
        let isOtpVerified = false; // Track if OTP is verified


        // Block browser back/forward navigation
        function enableNavigationProtection() {
            $(window).on('beforeunload.navigationProtection', function (e) {
                if (!isOtpVerified) { // Only block if OTP is not verified
                    e.preventDefault();
                    e.returnValue = '';
                    return 'Are you sure you want to leave?';
                }
            });
        }

        // Remove navigation protection
        function disableNavigationProtection() {
            $(window).off('beforeunload.navigationProtection');
            isOtpVerified = true;
        }

        // Enable protection initially
        enableNavigationProtection();

        // Block right-click
        $(document).on('contextmenu', function (e) {
            if (!isOtpVerified) { // Only block if OTP is not verified
                e.preventDefault();
                return false;
            }
        });

        // Block specific keys (F12, Ctrl+Shift+I, etc.)
        $(document).on('keydown', function (e) {
            if (!isOtpVerified) { // Only block if OTP is not verified
                // Block F12 (developer tools)
                if (e.key === 'F12') {
                    e.preventDefault();
                    return false;
                }

                // Block Ctrl+Shift+I (developer tools)
                if (e.ctrlKey && e.shiftKey && e.key === 'I') {
                    e.preventDefault();
                    return false;
                }

                // Block Ctrl+U (view source)
                if (e.ctrlKey && e.key === 'u') {
                    e.preventDefault();
                    return false;
                }
            }
        });

        $(document).on("submit", ".login-form", async function (e) {
            e.preventDefault();

            let userName = $('#userName').val();
            let password = $('#userPassword').val();
            let recaptchaResponse = grecaptcha.getResponse(); // Get reCAPTCHA response

            // Check if any field is empty
            if (!userName || !password) {
                Swal.fire("Error", "All fields are required. Please fill out the form completely.", "error");
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


            // Prepare data to send
            let formData = {
                username: userName,
                password: password,
                'g-recaptcha-response': recaptchaResponse // Include reCAPTCHA response
            };

            await $.ajax({
                url: window.location.href,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        // // Store OTP ID for later verification
                        // currentOtpId = response.otpId || null;

                        // // Show OTP verification modal
                        // showOtpModal();

                        // Disable navigation protection before redirect
                        disableNavigationProtection();

                        Swal.fire({
                            title: 'Success!',
                            text: 'User verified successfully. Redirecting to dashboard...',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                            allowOutsideClick: true,
                            allowEscapeKey: true,
                            allowEnterKey: true
                        }).then(() => {
                            window.location.href = 'dashboard.php';
                        });

                        $('#userName').val(''); // Reset form
                        $('#userPassword').val(''); // Reset form
                        grecaptcha.reset(); // Reset reCAPTCHA

                        // Restore button state
                        $submitBtn.prop('disabled', false).html(originalBtnText);
                    } else {
                        // Reset reCAPTCHA on failure
                        grecaptcha.reset();
                        // Restore button state

                        $submitBtn.prop('disabled', false).html(originalBtnText);

                        Swal.fire("Error", response.message, "error");
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


        // Function to show OTP modal
        function showOtpModal() {
            Swal.fire({
                title: 'OTP Verification',
                html: `
                    <p>Please enter the 6-digit OTP sent to your registered mobile/email</p>
                    <input type="text" id="otp-input" class="swal2-input" placeholder="Enter OTP" maxlength="6" style="text-align: center;">
                    <p id="otp-timer" style="color: #666; font-size: 14px;">OTP expires in <span id="timer-countdown">5:00</span></p>
                    <a href="#" id="resend-otp-link" style="color: #33cc33; text-decoration: none; font-size: 14px; ">
                                ↻ Resend OTP
                    </a>                `,
                showCancelButton: false,
                showConfirmButton: true,
                confirmButtonText: 'Verify OTP',
                confirmButtonColor: '#33cc33',
                allowOutsideClick: false,
                preConfirm: () => {
                    const otp = $('#otp-input').val();
                    if (!otp || otp.length !== 6) {
                        Swal.showValidationMessage('Please enter a valid 6-digit OTP');
                        return false;
                    }
                    return otp;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    verifyOtp(result.value);
                }
            });

            // Start OTP timer
            startOtpTimer();

            // Handle resend OTP button
            $(document).on('click', '#resend-otp-link', function () {
                resendOtp();
            });

            // Auto-focus OTP input
            setTimeout(() => {
                $('#otp-input').focus();
            }, 100);
        }

        // Function to verify OTP
        function verifyOtp(otp) {
            if (!currentOtpId) {
                Swal.fire("Error", "Invalid OTP session. Please login again.", "error");
                return;
            }

            $.ajax({
                url: window.location.href, // Create this PHP file for OTP verification
                type: 'POST',
                data: {
                    otp: otp,
                    otpId: currentOtpId
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {

                        // Disable navigation protection before redirect
                        disableNavigationProtection();

                        Swal.fire({
                            title: 'Success!',
                            text: 'OTP verified successfully. Redirecting to dashboard...',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'dashboard.php';
                        });
                    } else {
                        Swal.fire("Error", response.message, "error").then(() => {
                            showOtpModal(); // Show OTP modal again
                        });
                    }
                },
                error: function () {
                    Swal.fire("Error", "Failed to verify OTP. Please try again.", "error");
                }
            });
        }

        // Function to resend OTP
        function resendOtp() {
            $.ajax({
                url: window.location.href, // Create this PHP file for OTP resend
                type: 'POST',
                data: {
                    userId: '<?php echo $_SESSION['login_user_id'] ?? ''; ?>'
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        currentOtpId = response.otpId || null;

                        // Make sure we update currentOtpId with the new OTP ID
                        currentOtpId = response.otpId;

                        console.log('Updated currentOtpId:', currentOtpId); // Debug log

                        // Show success message and then reopen OTP modal
                        Swal.fire({
                            title: "Success",
                            text: "New OTP has been sent to your registered email.",
                            icon: "success",
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Reopen the OTP modal after success message
                            showOtpModal();
                        });
                    } else {
                        Swal.fire("Error", response.message, "error");
                    }
                },
                error: function () {
                    Swal.fire("Error", "Failed to resend OTP. Please try again.", "error");
                }
            });
        }

        // Function to start OTP timer
        function startOtpTimer() {
            let minutes = 1;
            let seconds = 59;

            const timer = setInterval(() => {
                if (minutes === 0 && seconds === 0) {
                    clearInterval(timer);
                    $('#otp-timer').html('<span style="color: red;">OTP expired</span>');
                    $('#resend-otp-link').prop('disabled', false);
                } else {
                    if (seconds === 0) {
                        minutes--;
                        seconds = 59;
                    } else {
                        seconds--;
                    }
                    $('#timer-countdown').text(`${minutes}:${seconds < 10 ? '0' : ''}${seconds}`);
                }
            }, 1000);
        }


        // reCAPTCHA callback - enable submit button when verified
        function callback() {
            $('#submitBtn').prop('disabled', false);
        }

        // reCAPTCHA expired callback - disable submit button
        function expiredCallback() {
            $('#submitBtn').prop('disabled', true);
            Swal.fire("Error", "reCAPTCHA expired. Please verify again.", "error");
        }
    });
</script>



</html>