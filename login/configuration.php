<?php
session_start();
include("db/config.php");

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}
$msg = null;
$name = $_SESSION['login_user'];
// Register user
$result = mysqli_query($db, "SELECT * FROM  google_captcha ");
$row = mysqli_fetch_row($result);

$result1 = mysqli_query($db, "SELECT * FROM  email_settings ");
$row1 = mysqli_fetch_row($result1);


// $result22 = mysqli_query($db, "SELECT * FROM  admin ");
// $row22 = mysqli_fetch_row($result22);
// $d = $row[0];
// $e = $row1[0];

// $dc = $row22[0];


// Reference number settings
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['referenceSettingSave'])) {

    try {

        $id = $_POST['referenceSettingId'];
        $code = (int) $_POST['code'];
        // Update sequence
        $stmt = $db->prepare("UPDATE reference_sequence SET last_sequence = ? WHERE id = ?");
        $stmt->bind_param("ii", $code, $id);
        $stmt->execute();

        $_SESSION['success'] = "Reference Settings Updated";

    } catch (\Throwable $th) {
        $_SESSION['error'] = $th->getMessage();

    }

}

// Smtp Settings Change
if ($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['action'] === "smtp-settings") {

    try {


        if (isset($_POST['id'])) {

            // echo json_encode([
            //     "status" => 201,
            //     "message" => "Email Settings Updated successfully",
            // ]);
            // exit;

            $editEmailSettingId = trim($_POST['id']);
            $title = trim($_POST['title']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $host = trim($_POST['host']);
            $port = (int) trim($_POST['port']);

            // Additional validations
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    "status" => 400,
                    "error" => "Please enter a valid email address"
                ]);
                exit;
            }

            if (strlen($password) < 6) {
                echo json_encode([
                    "status" => 400,
                    "error" => "Password must be at least 6 characters long"
                ]);
                exit;
            }

            if ($port < 1 || $port > 65535) {
                echo json_encode([
                    "status" => 400,
                    "error" => "Port must be a number between 1 and 65535"
                ]);
                exit;
            }


            $stmtUpdate = $db->prepare("UPDATE email_settings SET email_from_title=?, email_address=?, email_password=?, email_host=?, email_port=? WHERE email_settings_id=?");
            $stmtUpdate->bind_param("ssssii", $title, $email, $password, $host, $port, $editEmailSettingId);

            if ($stmtUpdate->execute()) {
                echo json_encode([
                    "status" => 200,
                    "message" => "Email Settings updated successfully",
                ]);
                exit;
            }
        } else {

            // echo json_encode([
            //     "status" => 201,
            //     "message" => "Email Settings created successfully",
            // ]);
            // exit;
            $title = trim($_POST['title']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $host = trim($_POST['host']);
            $port = (int) trim($_POST['port']);

            // Additional validations
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    "status" => 400,
                    "error" => "Please enter a valid email address"
                ]);
                exit;
            }

            if (strlen($password) < 6) {
                echo json_encode([
                    "status" => 400,
                    "error" => "Password must be at least 6 characters long"
                ]);
                exit;
            }

            if ($port < 1 || $port > 65535) {
                echo json_encode([
                    "status" => 400,
                    "error" => "Port must be a number between 1 and 65535"
                ]);
                exit;
            }


            $stmtInsert = $db->prepare("INSERT INTO email_settings (email_from_title,email_address,email_password,email_host,email_port) 
        VALUES(?,?,?,?,?)");
            $stmtInsert->bind_param("ssssi", $title, $email, $password, $host, $port);

            if ($stmtInsert->execute()) {
                echo json_encode([
                    "status" => 201,
                    "message" => "Email Settings created successfully",
                ]);
                exit;
            }
        }


    } catch (\Throwable $th) {
        //throw $th;
        echo json_encode([
            "status" => 500,
            "error" => $th->getMessage(),
        ]);
        exit;

    }

}

// Smtp Settings Delete
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['deleteSmtpSettingsId'])) {
    try {
        $deleteSmtpSettingsId = $_POST['deleteSmtpSettingsId'];


        $db->begin_transaction();

        $stmtDeleteSmtpSettingsId = $db->prepare("DELETE FROM email_settings WHERE email_settings_id = ?");
        $stmtDeleteSmtpSettingsId->bind_param('i', $deleteSmtpSettingsId);
        $stmtDeleteSmtpSettingsId->execute();

        $db->commit(); // Commit the transaction
        echo json_encode([
            "status" => 200,
            "message" => "Smtp Settings deleted successfully",
        ]);
        exit;

    } catch (\Throwable $th) {
        $db->rollback(); // Rollback on error
        echo json_encode([
            "status" => 500,
            "error" => "Database error: " . $th->getMessage(),
        ]);
        exit;
    }
}

try {
    $stmtReferenceCodeSettings = $db->prepare("SELECT * FROM reference_sequence");
    $stmtReferenceCodeSettings->execute();
    $referenceCodeSettings = $stmtReferenceCodeSettings->get_result()->fetch_array(MYSQLI_ASSOC);

    $stmtFetchEmailSettingData = $db->prepare("SELECT * FROM email_settings");
    $stmtFetchEmailSettingData->execute();
    $emailSettingData = $stmtFetchEmailSettingData->get_result()->fetch_array(MYSQLI_ASSOC);

    // print_r($emailSettingData);
    // exit;

} catch (\Throwable $th) {
    $_SESSION['error'] = $th->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Admin Settings </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .nav-pills .nav-link.active {
            background-color: #04a9f5;
            color: white;
            border-radius: 4px;
        }

        .nav-pills .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 5px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            border-radius: 4px;
        }

        .nav-pills .nav-link:hover {
            background-color: #f0f0f0;
        }

        .nav-pills .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {

            .col-md-3,
            .col-md-9 {
                width: 100%;
                margin-bottom: 20px;
            }
        }

        .swal2-container {
            z-index: 20000 !important;
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
            <a class="mobile-menu" id="mobile-collapse" href="javascript:void(0);"><span></span></a>
            <a href="javascript:void(0);" class="b-brand" style="font-size:24px;">
                ADMIN PANEL

            </a>
            <a href="javascript:void(0);" class="mob-toggler">
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
                    <a href="javascript:void(0);" class="full-screen" onClick="javascript:toggleFullScreen()"><i
                            class="feather icon-maximize"></i></a>
                </li>
            </ul>


        </div>
        </div>
        </li>

        <div class="dropdown drp-user">
            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
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
                                <h5 class="m-b-10">Server Setting</h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="configuration.php">Settings</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Sidebar and Content -->
            <div class="row">
                <!-- Settings Sidebar -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5>Settings Menu</h5>
                        </div>
                        <div class="card-body">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                                aria-orientation="vertical">

                                <a class="nav-link active" id="recaptcha-setting-tab" data-toggle="pill"
                                    href="#recaptcha-setting" role="tab">
                                    <i class="feather icon-shield"></i> Captcha Settings
                                </a>
                                <a class="nav-link" id="smtp-setting-tab" data-toggle="pill" href="#smtp-setting"
                                    role="tab">
                                    <i class="feather icon-mail"></i> SMTP Settings
                                </a>
                                <a class="nav-link" id="email-template-setting-tab" data-toggle="pill"
                                    href="#email-template-setting" role="tab">
                                    <i class="feather icon-file-text"></i> Email Templates
                                </a>
                                <a class="nav-link" id="reference-setting-tab" data-toggle="pill"
                                    href="#reference-setting" role="tab">
                                    <i class="feather icon-hash"></i> Reference Code
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Content -->
                <div class="col-md-9">
                    <div class="tab-content" id="v-pills-tabContent">


                        <!-- Google ReCAPTCHA Setting Section -->
                        <div class="tab-pane fade show active" id="recaptcha-setting" role="tabpanel">
                            <div class="card">
                                <div class="card-header table-card-header">
                                    <h4>Display Google ReCAPTCHA</h4>
                                    <hr />
                                    <form method="post" action="">
                                        <div class="row">
                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-6">
                                                <div class="form-group">Google ReCAPTCHA Site Key *
                                                    <label class="sr-only control-label" for="key">Google ReCAPTCHA Site
                                                        Key *</label>
                                                    <input id="key" name="key" type="text"
                                                        placeholder="Enter Google ReCAPTCHA Site Key *"
                                                        class="form-control input-md" required
                                                        oninvalid="this.setCustomValidity('Please Enter Google ReCAPTCHA Site Key *')"
                                                        oninput="setCustomValidity('')" value="<?php echo $row[1]; ?>">
                                                </div>
                                            </div>

                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-6">
                                                <div class="form-group">Google ReCAPTCHA Secret Key *
                                                    <label class="sr-only control-label" for="secret">Google ReCAPTCHA
                                                        Secret Key *</label>
                                                    <input id="secret" name="secret" type="text"
                                                        placeholder="Enter Google ReCAPTCHA Secret Key *"
                                                        class="form-control input-md" required
                                                        oninvalid="this.setCustomValidity('Please Enter Google ReCAPTCHA Secret Key *')"
                                                        oninput="setCustomValidity('')" value="<?php echo $row[2]; ?>">
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                                <button type="submit" class="btn btn-secondary" name="submit"
                                                    id="submit">
                                                    <i class="feather icon-save lg"></i>&nbsp; Save
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                        <!-- SMTP Setting Section -->
                        <div class="tab-pane fade" id="smtp-setting" role="tabpanel">
                            <div class="card">
                                <div class="card-header table-card-header">
                                    <h4>SMTP Settings</h4>
                                    <hr />
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#smtpSettingsModal">
                                        <i class="feather icon-plus"></i> Add New SMTP Configuration
                                    </button>
                                </div>
                                <div class="card-body">
                                    <!-- SMTP Settings Table -->
                                    <div class="row">
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover" id="smtpSettingsTable">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Title</th>
                                                            <th>Email Address</th>
                                                            <th>Host</th>
                                                            <th>Port</th>
                                                            <th>Status</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="smtpSettingsList">
                                                        <!-- SMTP settings will be loaded here -->
                                                        <tr>
                                                            <td><?= $emailSettingData['email_from_title'] ?? "" ?></td>
                                                            <td><?= $emailSettingData['email_address'] ?? "" ?></td>
                                                            <td><?= $emailSettingData['email_host'] ?? "" ?></td>
                                                            <td><?= $emailSettingData['email_port'] ?? "" ?></td>
                                                            <td>
                                                                <?php
                                                                if (isset($emailSettingData['is_active'])) {
                                                                    echo $emailSettingData['is_active'] == 1 ? "Active" : "Inactive";
                                                                } else {
                                                                    echo "";
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if (isset($emailSettingData['email_settings_id'])) { ?>
                                                                    <div class="dropdown">
                                                                        <button class="btn btn-primary" type="button"
                                                                            id="actionMenu" data-bs-toggle="dropdown"
                                                                            aria-expanded="false">
                                                                            <i class="feather icon-more-vertical"></i>
                                                                        </button>
                                                                        <ul class="dropdown-menu"
                                                                            aria-labelledby="actionMenu">
                                                                            <?php if ($isAdmin || hasPermission('Edit Tender Request', $privileges, $roleData['role_name'])) { ?>
                                                                                <li>
                                                                                    <a class="dropdown-item updateSmtpSettingsButton"
                                                                                        href="javascript:void(0);"
                                                                                        data-settings-id="<?= $emailSettingData['email_settings_id'] ?? "" ?>"
                                                                                        data-email-title="<?= $emailSettingData['email_from_title'] ?? "" ?>"
                                                                                        data-email-address="<?= $emailSettingData['email_address'] ?? "" ?>"
                                                                                        data-email-host="<?= $emailSettingData['email_host'] ?? "" ?>"
                                                                                        data-email-port="<?= $emailSettingData['email_port'] ?? "" ?>"
                                                                                        data-email-status="<?= $emailSettingData['is_active'] ?? "" ?>"
                                                                                        data-email-logoUrl="<?= $emailSettingData['logo_url'] ?? "" ?>"
                                                                                        data-support-email="<?= $emailSettingData['support_email'] ?? "" ?>"
                                                                                        data-support-phone="<?= $emailSettingData['phone'] ?? "" ?>"
                                                                                        data-support-addressLine="<?= $emailSettingData['address_line1'] ?? "" ?>"
                                                                                        data-email-igUrl="<?= $emailSettingData['ig_url'] ?? "" ?>"
                                                                                        data-email-fbUrl="<?= $emailSettingData['fb_url'] ?? "" ?>"
                                                                                        data-email-linkedinUrl="<?= $emailSettingData['linkedin_url'] ?? "" ?>"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#updateSmtpSettingsModal"
                                                                                        title="Update Smtp Configuration">
                                                                                        <i
                                                                                            class="feather icon-edit me-2"></i>Update
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>

                                                                            <?php if ($isAdmin || hasPermission('Delete Tender Request', $privileges, $roleData['role_name'])) { ?>
                                                                                <!-- <li>
                                                                               <hr class="dropdown-divider">
                                                                        </li> -->
                                                                                <li>
                                                                                    <a class="dropdown-item deleteSmtpSettingButton"
                                                                                        href="javascript:void(0);"
                                                                                        data-settings-id="<?= $emailSettingData['email_settings_id'] ?? "" ?>"
                                                                                        title="Move to Bin">
                                                                                        <i
                                                                                            class="feather icon-trash me-2"></i>Delete
                                                                                        Settings
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>

                                                                            <?php if ($isAdmin || hasPermission('Reference Tender Request', $privileges, $roleData['role_name'])) { ?>
                                                                                <li>
                                                                                    <a class="dropdown-item update-Reference"
                                                                                        href="javascript:void(0);"
                                                                                        data-tender-id="<?= $emailSettingData['email_settings_id'] ?>"
                                                                                        data-reference-code="<?= $emailSettingData['email_settings_id'] ?>"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#edit-units"
                                                                                        title="Change Reference Number">
                                                                                        <i
                                                                                            class="feather icon-eye me-2"></i>Status
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>
                                                                        </ul>
                                                                    </div>
                                                                <?php } ?>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Email Template Setting Section -->
                        <div class="tab-pane fade" id="email-template-setting" role="tabpanel">
                            <div class="card">
                                <div class="card-header table-card-header">
                                    <h4>Email Template Settings</h4>
                                    <hr />
                                    <!-- Button to trigger modal -->
                                    <button type="button" class="btn btn-primary" data-toggle="modal"
                                        data-target="#emailTemplateModal">
                                        <i class="feather icon-plus"></i> Add New Template
                                    </button>
                                </div>
                                <div class="card-body">
                                    <!-- Templates Table -->
                                    <div class="row">
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover" id="emailTemplatesTable">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Template Name</th>
                                                            <th>Subject</th>
                                                            <th>Type</th>
                                                            <th>Status</th>
                                                            <th>Created Date</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="templatesList">
                                                        <!-- Templates will be loaded here -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reference Code Setting Section -->
                        <div class="tab-pane fade" id="reference-setting" role="tabpanel">
                            <div class="card">
                                <div class="card-header table-card-header">
                                    <h4>Reference Code</h4>
                                    <hr />
                                    <form action="" method="post">
                                        <div class="row">
                                            <input type="hidden" name="referenceSettingId"
                                                value="<?= isset($referenceCodeSettings['id']) ? $referenceCodeSettings['id'] : "" ?>"
                                                id="">

                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                                <div class="form-group">Code *
                                                    <label class="sr-only control-label" for="code">Code Start *</label>
                                                    <input id="code" name="code" type="number"
                                                        value="<?= isset($referenceCodeSettings['last_sequence']) ? (int) $referenceCodeSettings['last_sequence'] : "" ?>"
                                                        class="form-control input-md" required pattern="[0-9]*"
                                                        inputmode="numeric"
                                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                                <button type="submit" class="btn btn-secondary"
                                                    name="referenceSettingSave" id="submit">
                                                    <i class="feather icon-save lg"></i>&nbsp; Save Changes
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- SMTP Settings Modal -->
    <div class="modal fade" id="smtpSettingsModal" tabindex="-1" role="dialog" aria-labelledby="smtpSettingsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="smtpSettingsModalLabel">SMTP Configuration</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="smtp-settings-form">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTitle">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="modalTitle" placeholder="Enter Title"
                                        class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalEmail">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="modalEmail" placeholder="Enter Email Address"
                                        class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalPassword">Email Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" id="modalPassword"
                                        placeholder="Enter Email Password" class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalHost">Host <span class="text-danger">*</span></label>
                                    <input type="text" name="host" id="modalHost" placeholder="Enter Host"
                                        class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalPort">Port <span class="text-danger">*</span></label>
                                    <input type="number" name="port" id="modalPort" placeholder="Enter Port"
                                        class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="alert alert-info">
                                    <strong>Common SMTP Settings:</strong><br>
                                    Gmail: smtp.gmail.com, Port: 587<br>
                                    Outlook: smtp-mail.outlook.com, Port: 587<br>
                                    Hostinger: smtp.hostinger.com, Port: 587
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveSmtpSettingsBtn">
                            <i class="feather icon-save lg"></i>&nbsp; Save Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update SMTP Settings Modal -->
    <div class="modal fade" id="updateSmtpSettingsModal" tabindex="-1" role="dialog"
        aria-labelledby="updateSmtpSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateSmtpSettingsModalLabel">Update SMTP Configuration</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="update-smtp-settings-form">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalTitle">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="updateModalTitle" placeholder="Enter Title"
                                        class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalEmail">Email Address <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="email" id="updateModalEmail"
                                        placeholder="Enter Email Address" class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalPassword">Email Password <span
                                            class="text-danger">*</span></label>
                                    <input type="password" name="password" id="updateModalPassword"
                                        placeholder="Enter Email Password" class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalHost">Host <span class="text-danger">*</span></label>
                                    <input type="text" name="host" id="updateModalHost" placeholder="Enter Host"
                                        class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalPort">Port <span class="text-danger">*</span></label>
                                    <input type="number" name="port" id="updateModalPort" placeholder="Enter Port"
                                        class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="alert alert-info">
                                    <strong>Common SMTP Settings:</strong><br>
                                    Gmail: smtp.gmail.com, Port: 587<br>
                                    Outlook: smtp-mail.outlook.com, Port: 587<br>
                                    Hostinger: smtp.hostinger.com, Port: 587
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                        <button type="submit" class="btn btn-primary" id="updateSmtpSettingsBtn">
                            <i class="feather icon-save lg"></i>&nbsp; Update Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Email Template Modal -->
    <div class="modal fade" id="emailTemplateModal" tabindex="-1" role="dialog"
        aria-labelledby="emailTemplateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailTemplateModalLabel">Add Email Template</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="email-template-form">
                    <input type="hidden" name="editTemplateId" id="modalEditTemplateId" value="">
                    <input type="hidden" name="action" value="email-templates">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateName">Template Name *</label>
                                    <input type="text" name="templateName" id="modalTemplateName"
                                        placeholder="Enter template name" class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateSubject">Subject *</label>
                                    <input type="text" name="templateSubject" id="modalTemplateSubject"
                                        placeholder="Enter email subject" class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateBody">Email Body *</label>
                                    <textarea name="templateBody" id="modalTemplateBody"
                                        placeholder="Enter email body content" class="form-control input-md" rows="6"
                                        required></textarea>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateType">Template Type *</label>
                                    <select name="templateType" id="modalTemplateType" class="form-control input-md"
                                        required>
                                        <option value="">Select Template Type</option>
                                        <option value="welcome">Welcome Email</option>
                                        <option value="password-reset">Password Reset</option>
                                        <option value="order-confirmation">Order Confirmation
                                        </option>
                                        <option value="invoice">Invoice Notification</option>
                                        <option value="payment-reminder">Payment Reminder</option>
                                        <option value="custom">Custom Template</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateStatus">Status *</label>
                                    <select name="templateStatus" id="modalTemplateStatus" class="form-control input-md"
                                        required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="form-group">
                                    <label>Available Variables</label>
                                    <div class="alert alert-info">
                                        <strong>Variables:</strong> Use these variables in your
                                        template:
                                        <code>{customer_name}</code>, <code>{company_name}</code>,
                                        <code>{invoice_number}</code>,
                                        <code>{amount}</code>, <code>{date}</code>,
                                        <code>{link}</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveTemplateModalBtn">
                            <i class="feather icon-save lg"></i>&nbsp; Save Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



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


    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>

    <script>
        $(document).ready(function () {



            $(document).on("submit", ".smtp-settings-form", async function (e) {
                e.preventDefault();

                // Get data from input fields within the form
                let title = $(this).find('input[name="title"]').val().trim();
                let email = $(this).find('input[name="email"]').val().trim();
                let password = $(this).find('input[name="password"]').val().trim();
                let host = $(this).find('input[name="host"]').val().trim();
                let port = $(this).find('input[name="port"]').val().trim();


                // Basic validation
                if (!email || !password || !host || !port || !title) {
                    Swal.fire("Error", "All fields are required. Please fill out the form completely.", "error");
                    return;
                }

                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    Swal.fire("Error", "Please enter a valid email address", "error");
                    return;
                }

                // Password validation (minimum 6 characters)
                if (password.length < 6) {
                    Swal.fire("Error", "Password must be at least 6 characters long", "error");
                    return;
                }

                // Port validation (must be a number between 1 and 65535)
                const portNumber = parseInt(port, 10);
                if (isNaN(portNumber) || portNumber < 1 || portNumber > 65535) {
                    Swal.fire("Error", "Please enter a valid port number (1-65535)", "error");
                    return;
                }

                // Host validation (basic checks for valid host format)
                const hostRegex = /^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$|^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
                if (!hostRegex.test(host)) {
                    Swal.fire("Error", "Please enter a valid host (domain name or IP address)", "error");
                    return;
                }

                // Store original button text and disable button during processing
                const $submitBtn = $(this).find('#saveSmtpSettingsBtn');
                const originalBtnText = $submitBtn.html();
                $submitBtn.prop('disabled', true).html('<i class="feather icon-loader"></i>&nbsp;Saving...');

                let formData = {
                    title: title,
                    email: email,
                    password: password,
                    host: host,
                    port: port,
                    action: "smtp-settings"
                };


                await $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 201) {
                            // Restore button state
                            $submitBtn.prop('disabled', false).html(originalBtnText);

                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: `${response.message}`,
                                // confirmButtonText: 'OK',
                                confirmButtonColor: "#33cc33",
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                // // ✅ Correct Bootstrap 5 way to hide the modal
                                // const smtpModalEl = document.getElementById('smtpSettingsModal');
                                // const smtpModal = bootstrap.Modal.getInstance(smtpModalEl);
                                // smtpModal.hide();

                                window.location.reload();
                            });
                        }
                        else {
                            $submitBtn.prop('disabled', false).html(originalBtnText);
                            Swal.fire("Error", response.error || "An error occurred", "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        // Restore button state
                        $submitBtn.prop('disabled', false).html(originalBtnText);

                        console.error("AJAX Error:", status, error);
                        console.error("Raw Response:", xhr.responseText);
                        Swal.fire("Error", "An error occurred while processing your request. Please try again.", "error");
                    }
                });
            });


            $(document).on('click', ".updateSmtpSettingsButton", function (event) {
                // Get all data attributes
                let settingsId = $(this).data('settings-id');
                let emailTitle = $(this).data('email-title');
                let emailAddress = $(this).data('email-address');
                let emailHost = $(this).data('email-host');
                let emailPort = $(this).data('email-port');
                let emailStatus = $(this).data('email-status');
                let emailLogoUrl = $(this).data('email-logourl');
                let supportEmail = $(this).data('support-email');
                let supportPhone = $(this).data('support-phone');
                let supportAddressLine = $(this).data('support-addressline');
                let emailIgUrl = $(this).data('email-igurl');
                let emailFbUrl = $(this).data('email-fburl');
                let emailLinkedinUrl = $(this).data('email-linkedinurl');

                // You can now use these variables as needed
                console.log({
                    settingsId,
                    emailTitle,
                    emailAddress,
                    emailHost,
                    emailPort,
                    emailStatus,
                    emailLogoUrl,
                    supportEmail,
                    supportPhone,
                    supportAddressLine,
                    emailIgUrl,
                    emailFbUrl,
                    emailLinkedinUrl
                });

                // Example: Set values in the update modal form fields
                $('#updateModalTitle').val(emailTitle);
                $('#updateModalEmail').val(emailAddress);
                $('#updateModalHost').val(emailHost);
                $('#updateModalPort').val(emailPort);

                // Store the settings ID for update operation (you might need this hidden field in your form)
                // If you don't have a hidden field for settings ID, add one to your form:
                // <input type="hidden" id="updateSettingsId" name="settings_id" value="">
                $('#updateSettingsId').val(settingsId);

                // You can also populate other fields if they exist in your update form
                // Example for support email, phone, etc. if you have those fields in the update modal
                // $('#updateSupportEmail').val(supportEmail);
                // $('#updateSupportPhone').val(supportPhone);
                // etc.
            });



            $(document).on("click", ".deleteSmtpSettingButton", function (e) {
                e.preventDefault();

                let settingsId = $(this).data("settings-id");



                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this Record!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#33cc33",
                    cancelButtonColor: "#ff5471",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: window.location.href, // Change to your actual endpoint
                            method: 'POST',
                            data: {
                                deleteSmtpSettingsId: settingsId,
                            },
                            success: function (response) {

                                // Show success message
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'The record has been deleted.',
                                    icon: 'success',
                                    confirmButtonColor: "#33cc33",
                                    timer: 1000,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Animate and remove the record
                                    $(".record").animate({
                                        backgroundColor: "#FF3"
                                    }, "fast")
                                        .animate({
                                            opacity: "hide"
                                        }, "slow");

                                    // Reload page after animation
                                    setTimeout(function () {
                                        window.location.reload();
                                    }, 1500);
                                });
                            },
                            error: function (error) {
                                console.log(error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Something went wrong while deleting the record.',
                                    icon: 'error',
                                    confirmButtonColor: "#33cc33"
                                });
                            }
                        });
                    }
                });

                return false;
            });
        });
    </script>


</body>

</html>