<?php
session_start();

require_once "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once '../env.php';

// error_reporting(1);
$baseUrl = getenv("BASE_URL");

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

$name = $_SESSION['login_user'];
require("db/config.php");


try {
    $stmtRole = $db->prepare("Select * from roles");
    $stmtRole->execute();
    $roles = $stmtRole->get_result()->fetch_all(MYSQLI_ASSOC);

} catch (\Throwable $th) {
    $_SESSION['error'] = $th->getMessage();
}

// Register user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    try {
        // Sanitize and validate inputs
        $name = trim($_POST['username']);
        $password = $_POST['password']; // Don't hash yet, validate first
        $mobile = trim($_POST['mobile']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $roleId = intval($_POST['roleId']); // Ensure it's an integer

        // Validate required fields
        if (empty($name) || empty($password) || empty($mobile) || empty($roleId)) {
            throw new Exception("Fill All Details");
        }

        // Validate email format
        if (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $email)) {
            throw new Exception("Invalid email format");
        }


        // Validate mobile format (example: 10 digits)
        if (!preg_match('/^[0-9]{10}$/', $mobile)) {
            throw new Exception("Invalid mobile number format");
        }


        $stmtCheckExistingUser = $db->prepare("SELECT * FROM admin WHERE email = ? OR mobile = ?"); // Added 'FROM'
        $stmtCheckExistingUser->bind_param(
            "ss",
            $email,
            $mobile
        );

        if (!$stmtCheckExistingUser->execute()) {
            throw new Exception($stmtCheckExistingUser->error);
        }

        // Get the result to access num_rows
        $existingUser = $stmtCheckExistingUser->get_result();

        if ($existingUser->num_rows > 0) {
            throw new Exception("Email or Phone already registered");
        }

        $hashed_password = md5($password);

        $stmtInsertData = $db->prepare("INSERT INTO admin (
        username,password,email,role_id,mobile) VALUES(?,?,?,?,?)");
        $stmtInsertData->bind_param(
            "sssis",
            $name,
            $hashed_password,
            $email,
            $roleId,
            $mobile
        );

        if (!$stmtInsertData->execute()) {
            throw new Exception($stmtInsertData->error);
        }

        $mail = new PHPMailer(true);
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
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->addAddress('enquiry@dvepl.com');
        $loginUrl = $baseUrl . "/login";

        $mail->Subject = "Welcome to DVEPL - Your Account is Active!";
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <img src='https://dvepl.com/assets/images/logo/dvepl-logo.png' alt='DVEPL Logo' style='max-width: 200px; height: auto; display: block; margin: 0 auto;'>
            </div>
            
            <div style='background-color: #f9f9f9; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 1px solid #eee;'>
                <h2 style='color: #4CBB17; text-align: center; margin-bottom: 25px; font-size: 24px;'>🎉 Welcome to DVEPL!</h2>
                
                <p style='font-size: 16px; color: #555; margin-bottom: 20px;'>
                    Dear <strong>" . htmlspecialchars($name) . "</strong>,
                </p>
                
                <p style='margin-bottom: 25px; font-size: 16px;'>
                    Congratulations! Your account has been successfully created. You are now a valued member of the DVEPL community.
                </p>
                
                <div style='background-color: #e8f5e8; border-left: 4px solid #4CBB17; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    <h3 style='color: #2e7d32; margin-top: 0;'>Your Account Details:</h3>
                    <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                    <p><strong>Mobile:</strong> " . htmlspecialchars($mobile) . "</p>
                </div>
                
                <p style='margin-bottom: 25px; font-size: 16px;'>
                    You can now log in to your account and start exploring all the features we have to offer.
                </p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href=" . $loginUrl . " style='display: inline-block; background-color: #4CBB17; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                        Log In to Your Account
                    </a>
                </div>
                
                <p style='margin-bottom: 15px; font-size: 16px;'>
                    If you have any questions or need assistance, please don't hesitate to contact our support team.
                </p>
                
                <p style='margin-bottom: 0; font-size: 16px;'>
                    Welcome aboard and enjoy your experience with us!
                </p>
            </div>
            
            <div style='text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px;'>
                <p>&copy; " . date('Y') . " DVEPL. All rights reserved.</p>
                <p>If you have any questions, contact us at enquiry@dvepl.com</p>
            </div>
        </div>";

        if ($mail->send()) {
            echo json_encode([
                "status" => 201,
                "message" => "User Registered successfully. Welcome email sent."
            ]);
            exit;
        } else {
            echo json_encode([
                "status" => 201,
                "message" => "Registration successful but welcome email could not be sent. Please contact support."
            ]);
            exit;
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo json_encode([
            'status' => 500,
            'error' => $th->getMessage()
        ]);
        exit;
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />



<head>
    <title>Add User </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="#" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">


    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            border-radius: 5px !important;
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

<body class="">

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

    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include 'navbar.php'; ?>

    <header class="navbar pcoded-header navbar-expand-lg navbar-light headerpos-fixed header-blue">
        <div class="m-header">
            <a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>
            <a href="#!" class="b-brand" style="font-size:24px;">
                ADMIN PANEL

            </a>
            <a href="#!" class="mob-toggler">
                <i class="feather icon-more-vertical"></i>
            </a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">

                    <div class="search-bar">

                        <button type="button" class="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="#!" class="full-screen" onClick="javascript:toggleFullScreen()"><i
                            class="feather icon-maximize"></i></a>
                </li>
            </ul>


        </div>
        </div>
        </li>

        <div class="dropdown drp-user">
            <a href="#!" class="dropdown-toggle" data-toggle="dropdown">
                <img src="assets/images/user.png" class="img-radius wid-40" alt="User-Profile-Image">
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-notification">
                <div class="pro-head">
                    <img src="assets/images/user.png" class="img-radius" alt="User-Profile-Image">
                    <span><?php echo $name ?></span>
                    <a href="logout.php" class="dud-logout" title="Logout">
                        <i class="feather icon-log-out"></i>
                    </a>
                </div>
                <ul class="pro-body">
                    <li><a href="logout.php" class="dropdown-item"><i class="feather icon-lock"></i> Log out</a></li>
                </ul>
            </div>
        </div>
        </li>
        </ul>
        </div>
    </header>


    <section class="pcoded-main-container">
        <div class="pcoded-content">

            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h5 class="m-b-10">Add User
                                </h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="view-user.php">Manage User</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">

                <div class="col-sm-12">
                    <div class="card">


                        <div class="card-header table-card-header">
                            <form class="add-user-form" autocomplete="off">
                                <div class=" ">
                                    <!-- Text input-->
                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Enter Username <span class="text-danger">*</span>
                                                <label class="sr-only control-label" for="name">Username<span class=" ">
                                                    </span></label>
                                                <input name="username" type="text" id="username" placeholder=" Username"
                                                    class="form-control input-md" onBlur="checkUserAvailability()"
                                                    autocomplete="off"
                                                    oninvalid="this.setCustomValidity('Please Enter Username')"
                                                    oninput="setCustomValidity('')">
                                                <p><img src="loader.gif" id="loader" style="display:none" /></p>

                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Enter Password <span class="text-danger">*</span>
                                                <label class="sr-only control-label" for="name">Password<span class=" ">
                                                    </span></label>
                                                <input id="name" name="password" type="password"
                                                    placeholder=" Enter Password *" class="form-control input-md"
                                                    oninvalid="this.setCustomValidity('Please Enter Password')"
                                                    oninput="setCustomValidity('')">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Mobile No <span class="text-danger">*</span>
                                                <label class="sr-only control-label" for="name">Mobile No<span
                                                        class=" ">
                                                    </span></label>
                                                <input id="name" name="mobile" type="number"
                                                    placeholder=" Enter Mobile No *" class="form-control input-md"
                                                    oninvalid="this.setCustomValidity('Please Enter Mobile Number')"
                                                    oninput="setCustomValidity('')">
                                            </div>
                                        </div>



                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Email <span class="text-danger">*</span>
                                                <label class="sr-only control-label" for="name">Email<span class=" ">
                                                    </span></label>
                                                <input id="name" name="email" type="email" class="form-control input-md"
                                                    placeholder="Enter valid email address" autocomplete="off"
                                                    title="Enter valid Email Address">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Role <span class="text-danger">*</span>

                                                <select id="role_id" name="role_id" class="form-control">
                                                    <option>Select</option>
                                                    <?php foreach ($roles as $key => $value) { ?>
                                                        <option value="<?= $value['role_id'] ?>">
                                                            <?= $value['role_name'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- Button -->
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                            <button type="submit" class="btn btn-secondary" name="submit" id="submit">
                                                <i class="feather icon-save lg"></i>&nbsp; Add User
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>




    <!-- jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <!--<script src="assets/js/menu-setting.min.js"></script>-->

    <script src="assets/js/plugins/jquery.dataTables.min.js"></script>
    <script src="assets/js/plugins/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/plugins/buttons.colVis.min.js"></script>
    <script src="assets/js/plugins/buttons.print.min.js"></script>
    <script src="assets/js/plugins/pdfmake.min.js"></script>
    <script src="assets/js/plugins/jszip.min.js"></script>
    <script src="assets/js/plugins/dataTables.buttons.min.js"></script>
    <script src="assets/js/plugins/buttons.html5.min.js"></script>
    <script src="assets/js/plugins/buttons.bootstrap4.min.js"></script>
    <script src="assets/js/pages/data-export-custom.js"></script>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 (must come AFTER jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


</body>
<script>
    function checkUserAvailability() {
        $("#loader").show();
        jQuery.ajax({
            url: "check.php",
            data: 'username=' + $("#username").val(),
            type: "POST",
            success: function (data) {
                if (data == 1) {
                    $("#user-availability-status").html(
                        "<div class='alert alert-danger'> <i class=' feather  icon icon-info'></i> &nbsp;Username already exists in our record.</div>"
                    );
                    $("#user-availability-status").removeClass('available');
                    $("#user-availability-status").addClass('not-available');
                    $("#submit").attr('disabled', true);
                } else {
                    $("#user-availability-status").html(
                        "<div class='alert alert-success' ><i class='feather icon-check'></i> &nbsp;Username is Available.</div>"
                    );
                    $("#user-availability-status").removeClass('not-available');
                    $("#user-availability-status").addClass('available');
                    $("#submit").attr('disabled', false);
                }
                $("#loader").hide();
            },
            error: function () { }
        });
    }
</script>


<script>
    $(document).ready(function () {

        $("#role_id").select2({
            placeholder: "Select Role",
            width: "100%"
        });


        $(document).on("submit", ".add-user-form", function (e) {
            e.preventDefault();

            // Get values correctly using the name attributes
            let username = $("input[name='username']").val();
            let password = $("input[name='password']").val();
            let mobile = $("input[name='mobile']").val();
            let email = $("input[name='email']").val();
            let roleId = $("select[name='role_id']").val();

            if (!username || !password || !mobile || !roleId || !email) {
                Swal.fire("Error", "All fields are required. Please fill out the form completely.", "error");
                return;
            }

            // Password validation (minimum 6 characters)
            if (password.length < 6) {
                Swal.fire("Error", "Password must be at least 6 characters long", "error");
                return;
            }

            // Mobile validation (assuming 10 digits for India)
            const mobileRegex = /^[0-9]{10}$/;
            if (!mobileRegex.test(mobile)) {
                Swal.fire("Error", "Please enter a valid 10-digit mobile number", "error");
                return;
            }



            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Swal.fire("Error", "Please enter a valid email address", "error");
                return;
            }




            // Your AJAX submission logic here
            $.ajax({
                url: window.location.href, // Change to your actual endpoint
                method: 'POST',
                data: {
                    username: username,
                    password: password,
                    mobile: mobile,
                    email: email,
                    roleId: roleId,
                },

                success: function (response) {

                    let result = JSON.parse(response);
                    if (result.status == 201) {

                        // Show success message
                        Swal.fire({
                            title: 'User Registered!',
                            text: result.message,
                            icon: 'success',
                            confirmButtonColor: "#33cc33",
                            timer: 1000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });

                    } else {
                        // Show error message
                        Swal.fire({
                            title: 'Error!',
                            text: result.error || 'Something went wrong',
                            icon: 'error',
                            confirmButtonColor: "#dc3545",
                            timer: 1500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    }


                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    // Show error message
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to update reference code',
                        icon: 'error',
                        confirmButtonColor: "#dc3545",
                        timer: 1500,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                }
            });
        });

    });
</script>

</html>