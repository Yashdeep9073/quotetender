<?php

session_start();
include("db/config.php");


if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}
$name = $_SESSION['login_user'];
$emailEncoded = $_GET["id"];
$emailDecoded = base64_decode($emailEncoded);

try {

    $stmtRole = $db->prepare("Select * from roles");
    $stmtRole->execute();
    $roles = $stmtRole->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmtFetchUser = $db->prepare("SELECT * FROM admin WHERE email = ?");
    $stmtFetchUser->bind_param("s", $emailDecoded);
    if (!$stmtFetchUser->execute()) {
        throw new Exception($stmtFetchUser->error);
    }
    $usersData = $stmtFetchUser->get_result()->fetch_array(MYSQLI_ASSOC);

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
        $userId = intval($_POST['userId']); // Ensure it's an integer
        $status = intval($_POST['status']); // Ensure it's an integer

        // throw new Exception("test");
        // Validate required fields
        if (empty($name) || empty($email) || empty($mobile) || empty($roleId)) {
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



        $stmtCheckExistingUser = $db->prepare("SELECT * FROM admin WHERE (email = ? OR mobile = ?) AND id != ?");
        $stmtCheckExistingUser->bind_param(
            "ssi",
            $email,
            $mobile,
            $userId
        );

        if (!$stmtCheckExistingUser->execute()) {
            throw new Exception($stmtCheckExistingUser->error);
        }

        // Get the result to access num_rows
        $existingUser = $stmtCheckExistingUser->get_result();

        if ($existingUser->num_rows > 0) {
            throw new Exception("Email or Phone already registered");
        }

        // Check if password is provided
        if (!empty($password)) {
            // Validate password if provided
            if (strlen($password) < 6) {
                throw new Exception("Password must be at least 6 characters long");
            }

            // Hash password securely (use password_hash instead of md5)
            $hashed_password = md5($password);

            // Update with new password
            $stmtInsertData = $db->prepare("UPDATE admin SET username=?, password=?, email=?, role_id=?, mobile=? , status=? WHERE id=?");
            $stmtInsertData->bind_param(
                "ssssiii",
                $name,
                $hashed_password,
                $email,
                $roleId,
                $mobile,
                $status,
                $userId
            );
        } else {
            // Update without password
            $stmtInsertData = $db->prepare("UPDATE admin SET username=?, email=?, role_id=?, mobile=?,status=? WHERE id=?");
            $stmtInsertData->bind_param(
                "sssiii",
                $name,
                $email,
                $roleId,
                $mobile,
                $status,
                $userId
            );
        }

        if (!$stmtInsertData->execute()) {
            throw new Exception($stmtInsertData->error);
        }

        echo json_encode([
            "status" => 201,
            "message" => "User Updated successfully."
        ]);
        exit;
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
    <title>Update User</title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="Codedthemes" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">

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
                                <h5 class="m-b-10">Update User
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
                            <form class="user-edit-form">
                                <input type="hidden" name="user_id" id="user_id" value="<?= $usersData['id'] ?? "" ?>">
                                <div class=" ">
                                    <!-- Text input-->
                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Enter Username <span class="text-danger">*</span>
                                                <label class="sr-only control-label" for="name">Username<span class=" ">
                                                    </span></label>
                                                <input id="name" name="username" type="text" placeholder="Username"
                                                    class="form-control input-md"
                                                    value="<?= $usersData['username']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Enter Password <span class="text-danger">*</span>
                                                <label class="sr-only control-label" for="name">Password<span class=" ">
                                                    </span></label>
                                                <input id="name" name="password" type="password"
                                                    placeholder="Enter new password,if you want to change current password"
                                                    class="form-control input-md" value="">
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Mobile No <span class="text-danger">*</span>
                                                <label class="sr-only control-label" for="name">Mobile No<span
                                                        class=" ">
                                                    </span></label>
                                                <input id="name" name="mobile" type="number"
                                                    placeholder=" Enter Mobile No *" class="form-control input-md"
                                                    value="<?= $usersData['mobile']; ?>">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Email <span class="text-danger">*</span>
                                                <label class="sr-only control-label" for="name">Email<span class=" ">
                                                    </span></label>
                                                <input id="name" name="email" type="email" class="form-control input-md"
                                                    placeholder="Enter Email" value="<?= $usersData['email']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Status <span class="text-danger">*</span>
                                                <label class="sr-only control-label" for="name">Status<span class=" ">
                                                    </span></label>
                                                <select id="" name="status" class="form-control">
                                                    <option <?= $usersData['status'] == 1 ? "selected" : "" ?> value="1">
                                                        Enable</option>
                                                    <option <?= $usersData['status'] == 0 ? "selected" : "" ?> value="0">
                                                        Disabe</option>

                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Role <span class="text-danger">*</span>

                                                <select id="role_id" name="role_id" class="form-control">
                                                    <?php foreach ($roles as $key => $value) {
                                                        $selected = $usersData['role_id'] == $value['role_id'] ? "selected" : "";
                                                        ?>
                                                        <option <?= $selected ?> value="<?= $value['role_id'] ?>">
                                                            <?= $value['role_name'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- Button -->
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                            <button type="submit" class="btn btn-secondary" name="submit" id="submit">
                                                <i class="feather icon-save lg"></i>&nbsp; Update
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

    <script>
        $(document).ready(function () {

            $("#role_id").select2({
                placeholder: "Select Role",
                width: "100%"
            });


            $(document).on("submit", ".user-edit-form", function (e) {
                e.preventDefault();

                // Get values correctly using the name attributes
                let username = $("input[name='username']").val();
                let password = $("input[name='password']").val();
                let mobile = $("input[name='mobile']").val();
                let email = $("input[name='email']").val();
                let userId = $("input[name='user_id']").val();
                let status = $("select[name='status']").val();
                let roleId = $("select[name='role_id']").val();

                if (!username || !mobile || !roleId || !email || !status) {
                    Swal.fire("Error", "All fields are required. Please fill out the form completely.", "error");
                    return;
                }


                // Password validation (minimum 6 characters)
                if (password && password.length < 6) {
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
                        status: status,
                        userId: userId,
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

</body>

</html>