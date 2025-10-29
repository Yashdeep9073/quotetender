<?php
session_start();
include("db/config.php");
require_once __DIR__ . "/./utility/fileUploader.php";
if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

$uploadDir = "public/uploads/email-settings/";

$msg = null;
$name = $_SESSION['login_user'];
// Register user
$result = mysqli_query($db, "SELECT * FROM  google_captcha ");
$row = mysqli_fetch_row($result);

$result1 = mysqli_query($db, "SELECT * FROM  email_settings ");
$row1 = mysqli_fetch_row($result1);


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

// Update Smtp settings
if ($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['action'] === "update-smtp-settings") {

    try {
        // Validate required fields
        $required_fields = ['settingsId', 'title', 'email', 'password', 'host', 'port', 'supportEmail', 'supportPhone', 'addressLine'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode([
                    "status" => 400,
                    "error" => "Missing required field: " . $field
                ]);
                exit;
            }
        }

        // Sanitize input data
        $settingsId = (int) $_POST['settingsId'];
        $title = trim($_POST['title']);
        $email = trim($_POST['email']);
        $password = $_POST['password']; // Don't trim password as spaces might be valid
        $host = trim($_POST['host']);
        $port = (int) $_POST['port'];
        $supportEmail = trim($_POST['supportEmail']);
        $supportPhone = trim($_POST['supportPhone']);
        $addressLine = trim($_POST['addressLine']);

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => 400,
                "error" => "Invalid email address format"
            ]);
            exit;
        }

        if (!filter_var($supportEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => 400,
                "error" => "Invalid support email address format"
            ]);
            exit;
        }

        // Validate port
        if ($port < 1 || $port > 65535) {
            echo json_encode([
                "status" => 400,
                "error" => "Port must be between 1 and 65535"
            ]);
            exit;
        }

        // Handle file upload if logo is provided
        $logoUrl = null;
        if (isset($_FILES['logoUrl']) && $_FILES['logoUrl']['error'] == 0) {
            $result = uploadMedia($_FILES['logoUrl'], $uploadDir, [
                // Images
                'jpg',
                'jpeg',
                'png',
                'webp',
                'bmp',
                'svg',
            ], 2 * 1024 * 1024);
            if (!isset($result['error'])) {
                $logoUrl = $result[0]['filename'];
            } else {
                echo json_encode([
                    "status" => 400,
                    "error" => "Error while updating logo"
                ]);
                exit;
            }

        }

        // Check if settings record exists
        $checkQuery = "SELECT email_settings_id FROM email_settings WHERE email_settings_id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bind_param("i", $settingsId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows == 0) {
            // If no record exists, insert a new one
            $insertQuery = "INSERT INTO email_settings (
                email_from_title, 
                email_address, 
                email_password, 
                email_host, 
                email_port, 
                logo_url, 
                support_email, 
                phone, 
                address_line1
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->bind_param(
                "ssssissss",
                $title,
                $email,
                $password,
                $host,
                $port,
                $logoUrl,
                $supportEmail,
                $supportPhone,
                $addressLine
            );

            if (!$insertStmt->execute()) {
                throw new Exception("Insert failed: " . $db->error);
            }
        } else {
            // Update existing record
            $updateQuery = "UPDATE email_settings SET 
                email_from_title = ?, 
                email_address = ?, 
                email_password = ?, 
                email_host = ?, 
                email_port = ?, 
                logo_url = ?,
                support_email = ?,
                phone = ?,
                address_line1 = ?,
                update_at = CURRENT_TIMESTAMP
            WHERE email_settings_id = ?";

            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bind_param(
                "ssssissssi",
                $title,
                $email,
                $password,
                $host,
                $port,
                $logoUrl,
                $supportEmail,
                $supportPhone,
                $addressLine,
                $settingsId
            );

            if (!$updateStmt->execute()) {
                throw new Exception("Update failed: " . $db->error);
            }
        }


        // Close statements
        if (isset($checkStmt))
            $checkStmt->close();
        if (isset($insertStmt))
            $insertStmt->close();
        if (isset($updateStmt))
            $updateStmt->close();
        $db->close();

        echo json_encode([
            "status" => 201,
            "message" => "Email Settings Updated successfully"
        ]);
        exit;

    } catch (Exception $th) {
        // Log the error for debugging (optional)
        error_log("Email settings update error: " . $th->getMessage());

        echo json_encode([
            "status" => 500,
            "error" => $th->getMessage()
        ]);
        exit;
    }
}

// Update Smtp Status
if ($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['action'] === "update-smtp-status") {

    try {

        // echo json_encode([
        //     "status" => 400,
        //     "error" => "Testing",
        //     "Data" => $_POST
        // ]);
        // exit;

        // Validate required fields
        $required_fields = ['settingsId', 'status'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || $_POST[$field] === '') {
                echo json_encode([
                    "status" => 400,
                    "error" => "Missing required field: " . $field
                ]);
                exit;
            }
        }

        // Sanitize input data
        $settingsId = (int) $_POST['settingsId'];
        $status = (int) $_POST['status'];

        // Check if settings record exists
        $checkQuery = "SELECT email_settings_id FROM email_settings WHERE email_settings_id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bind_param("i", $settingsId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows == 0) {
            throw new Exception("Update failed: Unable find setting with this id  $settingsId");
        } else {
            // Update existing record
            $updateQuery = "UPDATE email_settings SET 
                is_active = ?
            WHERE email_settings_id = ?";

            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bind_param(
                "ii",
                $status,
                $settingsId
            );

            if (!$updateStmt->execute()) {
                throw new Exception("Update failed: " . $db->error);
            }

        }


        // Close statements
        if (isset($checkStmt))
            $checkStmt->close();
        if (isset($insertStmt))
            $insertStmt->close();
        if (isset($updateStmt))
            $updateStmt->close();
        $db->close();

        echo json_encode([
            "status" => 200,
            "message" => "Email Settings Updated successfully"
        ]);
        exit;

    } catch (Exception $th) {
        // Log the error for debugging (optional)
        error_log("Email settings update error: " . $th->getMessage());

        echo json_encode([
            "status" => 500,
            "error" => $th->getMessage()
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


// Create Email template
if ($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['action'] === "create-email-template") {

    try {

        $required_fields = ['templateName', 'templateSubject', 'content1', 'content2', 'templateType'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || $_POST[$field] === '') {
                echo json_encode([
                    "status" => 400,
                    "error" => "Missing required field: " . $field
                ]);
                exit;
            }
        }

        $templateName = trim($_POST['templateName']);
        $templateSubject = trim($_POST['templateSubject']);
        $content1 = trim($_POST['content1']);
        $content2 = trim($_POST['content2']);
        $templateType = trim($_POST['templateType']);

        $stmtInsertEmailTemplate = $db->prepare("INSERT INTO email_template (email_template_title,email_template_subject,content_1,content_2,type) 
        VALUES(?,?,?,?,?)");
        $stmtInsertEmailTemplate->bind_param("sssss", $templateName, $templateSubject, $content1, $content2, $templateType);

        if ($stmtInsertEmailTemplate->execute()) {
            echo json_encode([
                "status" => 201,
                "message" => "Email Template created successfully",
            ]);
            exit;
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

// Delete Email template
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['deleteEmailTemplateId'])) {
    try {
        $deleteEmailTemplateId = $_POST['deleteEmailTemplateId'];


        $db->begin_transaction();

        $stmtDeleteEmailTemplate = $db->prepare("DELETE FROM email_template WHERE idemail_template = ?");
        $stmtDeleteEmailTemplate->bind_param('i', $deleteEmailTemplateId);
        $stmtDeleteEmailTemplate->execute();

        $db->commit(); // Commit the transaction
        echo json_encode([
            "status" => 200,
            "message" => "Email template deleted successfully",
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

//Update Email Template
if ($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['action'] === "update-email-template") {

    try {
        $required_fields = ['editTemplateId', 'templateName', 'templateSubject', 'content1', 'content2', 'templateType'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || $_POST[$field] === '') {
                echo json_encode([
                    "status" => 400,
                    "error" => "Missing required field: " . $field
                ]);
                exit;
            }
        }

        $editTemplateId = (int) trim($_POST['editTemplateId']);
        $templateName = trim($_POST['templateName']);
        $templateSubject = trim($_POST['templateSubject']);
        $content1 = trim($_POST['content1']);
        $content2 = trim($_POST['content2']);
        $templateType = trim($_POST['templateType']);

        // Validate template type against enum values
        $validTypes = ['WELCOME', 'PASSWORD_RESET', '2FA', 'VERIFICATION', 'TENDER_REQUEST', 'SENT_TENDER', 'ALOT_TENDER', 'AWARD_TENDER'];
        if (!in_array($templateType, $validTypes)) {
            echo json_encode([
                "status" => 400,
                "error" => "Invalid template type. Must be one of: " . implode(', ', $validTypes)
            ]);
            exit;
        }

        // Check if template exists
        $checkStmt = $db->prepare("SELECT idemail_template FROM email_template WHERE idemail_template = ?");
        $checkStmt->bind_param("i", $editTemplateId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows == 0) {
            echo json_encode([
                "status" => 404,
                "error" => "Email template not found"
            ]);
            exit;
        }

        // Update the existing template
        $stmtUpdateEmailTemplate = $db->prepare("UPDATE email_template SET 
            email_template_title = ?, 
            email_template_subject = ?, 
            content_1 = ?, 
            content_2 = ?, 
            type = ?,
            update_at = CURRENT_TIMESTAMP
        WHERE idemail_template = ?");

        $stmtUpdateEmailTemplate->bind_param(
            "sssssi",
            $templateName,
            $templateSubject,
            $content1,
            $content2,
            $templateType,
            $editTemplateId
        );

        if ($stmtUpdateEmailTemplate->execute()) {
            echo json_encode([
                "status" => 200, // Use 200 for successful update
                "message" => "Email Template updated successfully",
            ]);
            exit;
        } else {
            throw new Exception("Update failed: " . $db->error);
        }

    } catch (Exception $th) {
        echo json_encode([
            "status" => 500,
            "error" => $th->getMessage(),
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

    $stmtFetchEmailTemplates = $db->prepare("SELECT * FROM email_template");
    $stmtFetchEmailTemplates->execute();
    $emailTemplates = $stmtFetchEmailTemplates->get_result()->fetch_all(MYSQLI_ASSOC);
    // echo "<pre>";
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

    <!-- Include stylesheet -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />

    <!-- Include the Quill library -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
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

                                <?php if ($isAdmin || hasPermission('Captcha Settings', $privileges, $roleData['role_name'])) { ?>
                                    <a class="nav-link active" id="recaptcha-setting-tab" data-toggle="pill"
                                        href="#recaptcha-setting" role="tab">
                                        <i class="feather icon-shield"></i> Captcha Settings
                                    </a>
                                <?php } ?>

                                <?php if ($isAdmin || hasPermission('SMTP Settings', $privileges, $roleData['role_name'])) { ?>

                                    <a class="nav-link" id="smtp-setting-tab" data-toggle="pill" href="#smtp-setting"
                                        role="tab">
                                        <i class="feather icon-mail"></i> SMTP Settings
                                    </a>
                                <?php } ?>
                                <?php if ($isAdmin || hasPermission('Email Templates', $privileges, $roleData['role_name'])) { ?>

                                    <a class="nav-link" id="email-template-setting-tab" data-toggle="pill"
                                        href="#email-template-setting" role="tab">
                                        <i class="feather icon-file-text"></i> Email Templates
                                    </a>
                                <?php } ?>
                                <?php if ($isAdmin || hasPermission('Reference Code', $privileges, $roleData['role_name'])) { ?>

                                    <a class="nav-link" id="reference-setting-tab" data-toggle="pill"
                                        href="#reference-setting" role="tab">
                                        <i class="feather icon-hash"></i> Reference Code
                                    </a>
                                <?php } ?>
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
                                    <h4>Captcha Settings</h4>
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
                                    <?php if ($isAdmin || hasPermission('Add SMTP Configuration', $privileges, $roleData['role_name'])) { ?>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#smtpSettingsModal">
                                            <i class="feather icon-plus"></i> Add New SMTP Configuration
                                        </button>
                                    <?php } ?>
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
                                                                            <?php if ($isAdmin || hasPermission('Edit SMTP Configuration', $privileges, $roleData['role_name'])) { ?>
                                                                                <li>
                                                                                    <a class="dropdown-item updateSmtpSettingsButton"
                                                                                        href="javascript:void(0);"
                                                                                        data-settings-id="<?= $emailSettingData['email_settings_id'] ?? "" ?>"
                                                                                        data-email-title="<?= $emailSettingData['email_from_title'] ?? "" ?>"
                                                                                        data-email-address="<?= $emailSettingData['email_address'] ?? "" ?>"
                                                                                        data-email-password="<?= $emailSettingData['email_password'] ?? "" ?>"
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

                                                                            <?php if ($isAdmin || hasPermission('Delete SMTP Configuration', $privileges, $roleData['role_name'])) { ?>
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

                                                                            <?php if ($isAdmin || hasPermission('Edit SMTP Configuration', $privileges, $roleData['role_name'])) { ?>
                                                                                <li>
                                                                                    <a class="dropdown-item updateSmtpStatusButton"
                                                                                        href="javascript:void(0);"
                                                                                        data-settings-id="<?= $emailSettingData['email_settings_id'] ?? "" ?>"
                                                                                        data-email-status="<?= $emailSettingData['is_active'] ?? "" ?>"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#updateSmtpStatusModel"
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
                                    <?php if ($isAdmin || hasPermission('Add Template', $privileges, $roleData['role_name'])) { ?>
                                        <!-- Button to trigger modal -->
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#emailTemplateModal">
                                            <i class="feather icon-plus"></i> Add New Template
                                        </button>
                                    <?php } ?>
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
                                                        <?php $count = 1;
                                                        foreach ($emailTemplates as $template):
                                                            ?>
                                                            <tr>
                                                                <td><?= $count ?></td>
                                                                <td><?= $template['email_template_title'] ?></td>
                                                                <td><?= $template['email_template_subject'] ?></td>
                                                                <td><?= $template['type'] ?></td>
                                                                <td>

                                                                    <?php
                                                                    if (isset($template['is_active'])) {
                                                                        echo $template['is_active'] == 1 ? "Active" : "Inactive";
                                                                    } else {
                                                                        echo "";
                                                                    }
                                                                    ?>

                                                                </td>
                                                                <td><?= $template['created_at'] ?></td>
                                                                <td>
                                                                    <div class="dropdown">
                                                                        <button class="btn btn-primary" type="button"
                                                                            id="actionMenu" data-bs-toggle="dropdown"
                                                                            aria-expanded="false">
                                                                            <i class="feather icon-more-vertical"></i>
                                                                        </button>
                                                                        <ul class="dropdown-menu"
                                                                            aria-labelledby="actionMenu">
                                                                            <?php if ($isAdmin || hasPermission('Edit Template', $privileges, $roleData['role_name'])) { ?>
                                                                                <li>
                                                                                    <a class="dropdown-item updateEmailTemplateButton"
                                                                                        href="javascript:void(0);"
                                                                                        data-template-id="<?= $template['idemail_template'] ?? "" ?>"
                                                                                        data-template-title="<?= $template['email_template_title'] ?? "" ?>"
                                                                                        data-template-subject="<?= $template['email_template_subject'] ?? "" ?>"
                                                                                        data-template-content1="<?= base64_encode($template['content_1']) ?? "" ?>"
                                                                                        data-template-content2="<?= base64_encode($template['content_2']) ?? "" ?>"
                                                                                        data-template-type="<?= $template['type'] ?? "" ?>"
                                                                                        data-template-status="<?= $template['is_active'] ?? "" ?>"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#updateEmailTemplateModal"
                                                                                        title="Update Email Template">
                                                                                        <i
                                                                                            class="feather icon-edit me-2"></i>Update
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>

                                                                            <?php if ($isAdmin || hasPermission('Delete Template', $privileges, $roleData['role_name'])) { ?>
                                                                                <!-- <li>
                                                                               <hr class="dropdown-divider">
                                                                        </li> -->
                                                                                <li>
                                                                                    <a class="dropdown-item deleteEmailTemplateButton"
                                                                                        href="javascript:void(0);"
                                                                                        data-template-id="<?= $template['idemail_template'] ?? "" ?>"
                                                                                        title="Move to Bin">
                                                                                        <i
                                                                                            class="feather icon-trash me-2"></i>Delete
                                                                                        Template
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>
                                                                        </ul>
                                                                    </div>

                                                                </td>
                                                            </tr>
                                                            <?php $count++;
                                                        endforeach; ?>
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
                                    <div class="input-group">
                                        <input type="password" name="password" id="modalPassword"
                                            placeholder="Enter Email Password" class="form-control input-md" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button"
                                                id="addModelSmtpTogglePassword"
                                                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                                <i class="fas fa-eye" id="modalEyeIcon"></i>
                                            </button>
                                        </div>
                                    </div>
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
                    <input type="hidden" id="updateModalSmtpSettingsId" name="settings_id" value="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalSmtpTitle">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="updateModalSmtpTitle" placeholder="Enter Title"
                                        class="form-control input-md">
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalSmtpEmail">Email Address <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="email" id="updateModalSmtpEmail"
                                        placeholder="Enter Email Address" class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalSmtpPassword">Email Password <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="updateModalSmtpPassword"
                                            placeholder="Enter Email Password" class="form-control input-md"
                                            required="">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button"
                                                id="updateModelSmtpTogglePassword"
                                                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                                <i class="fas fa-eye" id="eyeIcon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalSmtpHost">Host <span class="text-danger">*</span></label>
                                    <input type="text" name="host" id="updateModalSmtpHost" placeholder="Enter Host"
                                        class="form-control input-md">
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalSmtpPort">Port <span class="text-danger">*</span></label>
                                    <input type="number" name="port" id="updateModalSmtpPort" placeholder="Enter Port"
                                        class="form-control input-md">
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalSmtpLogo">Logo <span class="text-danger">*</span></label>
                                    <input type="file" name="logoUrl" id="updateModalSmtpLogo"
                                        class="form-control input-md" accept=".png,.jpeg,.jpg,.svg,.webp">
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalSmtpSupportEmail">Support Email <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="supportEmail" id="updateModalSmtpSupportEmail"
                                        placeholder="Enter Support Email" class="form-control input-md" required>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalSmtpSupportPhone">Support Phone <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="supportPhone" id="updateModalSmtpSupportPhone"
                                        placeholder="Enter Support Phone" class="form-control input-md" required>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="updateModalSmtpAddressLine">Address Line <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="addressLine" id="updateModalSmtpAddressLine"
                                        placeholder="Enter Address Line " class="form-control input-md" required>
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

    <!-- Update SMTP Status Settings Modal -->
    <div class="modal fade" id="updateSmtpStatusModel" tabindex="-1" role="dialog"
        aria-labelledby="updateSmtpSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateSmtpSettingsModalLabel">Update SMTP Configuration Status</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="update-smtp-status-form">
                    <input type="hidden" id="updateStatusModalSmtpSettingsId" name="settings_id" value="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="smtpStatus">Status <span class="text-danger">*</span></label>
                                    <select name="smtpStatus" id="smtpStatus" class="form-control">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                        <button type="submit" class="btn btn-primary" id="updateSmtpStatusBtn">
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
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="email-template-form">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateName">Template Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="templateName" id="modalTemplateName"
                                        placeholder="Enter template name" class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateSubject">Subject <span class="text-danger">*</span></label>
                                    <input type="text" name="templateSubject" id="modalTemplateSubject"
                                        placeholder="Enter email subject" class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateBody">Content 1 <span class="text-danger">*</span></label>
                                    <div id="editor-content1">
                                        <p></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateBody">Content 2<span class="text-danger">*</span></label>
                                    <div id="editor-content2">
                                        <p></p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateType">Template Type <span
                                            class="text-danger">*</span></label>
                                    <select name="templateType" id="modalTemplateType" class="form-control input-md"
                                        required>
                                        <option value="">Select Template Type</option>
                                        <option value="WELCOME">Welcome Email</option>
                                        <option value="PASSWORD_RESET">Password Reset</option>
                                        <option value="2FA">2FA</option>
                                        <option value="VERIFICATION">Verification
                                        </option>
                                        <option value="TENDER_REQUEST">Tender Request</option>
                                        <option value="SENT_TENDER">Sent Tender</option>
                                        <option value="ALOT_TENDER">Alot Tender</option>
                                        <option value="AWARD_TENDER">Award Tender</option>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveTemplateModalBtn">
                            <i class="feather icon-save lg"></i>&nbsp; Save Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Email Template Modal -->
    <div class="modal fade" id="updateEmailTemplateModal" tabindex="-1" role="dialog"
        aria-labelledby="updateEmailTemplateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateEmailTemplateModalLabel">Update Email Template</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="update-email-template-form">
                    <input type="hidden" name="editTemplateId" id="modalEditTemplateId" value="">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateName">Template Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="templateName" id="editModalTemplateName"
                                        placeholder="Enter template name" class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateSubject">Subject <span class="text-danger">*</span></label>
                                    <input type="text" name="templateSubject" id="editModalTemplateSubject"
                                        placeholder="Enter email subject" class="form-control input-md" required>
                                </div>
                            </div>

                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateBody">Content 1 <span class="text-danger">*</span></label>
                                    <div id="edit-editor-content1">
                                        <p></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateBody">Content 2<span class="text-danger">*</span></label>
                                    <div id="edit-editor-content2">
                                        <p></p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="modalTemplateType">Template Type <span
                                            class="text-danger">*</span></label>
                                    <select name="templateType" id="editModalTemplateType" class="form-control input-md"
                                        required>
                                        <option value="">Select Template Type</option>
                                        <option value="WELCOME">Welcome Email</option>
                                        <option value="PASSWORD_RESET">Password Reset</option>
                                        <option value="2FA">2FA</option>
                                        <option value="VERIFICATION">Verification
                                        </option>
                                        <option value="TENDER_REQUEST">Tender Request</option>
                                        <option value="SENT_TENDER">Sent Tender</option>
                                        <option value="ALOT_TENDER">Alot Tender</option>
                                        <option value="AWARD_TENDER">Award Tender</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="form-group">
                                    <label for="editModalTemplateStatus">Status *</label>
                                    <select name="templateStatus" id="editModalTemplateStatus"
                                        class="form-control input-md" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="editTemplateModalBtn">
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

            // quill editor
            const quill1 = new Quill('#editor-content1', {
                theme: 'snow'
            });
            const quill2 = new Quill('#editor-content2', {
                theme: 'snow'
            });
            const editQuill1 = new Quill('#edit-editor-content1', {
                theme: 'snow'
            });

            const editQuill2 = new Quill('#edit-editor-content2', {
                theme: 'snow'
            });

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

            $(document).on("submit", ".update-smtp-settings-form", async function (e) {
                e.preventDefault();

                // Get data from input fields within the form
                let settingsId = $(this).find('input[name="settings_id"]').val().trim();
                let title = $(this).find('input[name="title"]').val().trim();
                let email = $(this).find('input[name="email"]').val().trim();
                let password = $(this).find('input[name="password"]').val().trim();
                let host = $(this).find('input[name="host"]').val().trim();
                let port = $(this).find('input[name="port"]').val().trim();
                let logoUrl = $(this).find('input[name="logoUrl"]')[0].files[0]; // Get file object
                let supportEmail = $(this).find('input[name="supportEmail"]').val().trim();
                let supportPhone = $(this).find('input[name="supportPhone"]').val().trim();
                let addressLine = $(this).find('input[name="addressLine"]').val().trim();


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
                const $submitBtn = $(this).find('#updateSmtpSettingsBtn');
                const originalBtnText = $submitBtn.html();
                $submitBtn.prop('disabled', true).html('<i class="feather icon-loader"></i>&nbsp;Updating...');


                let formData = new FormData();
                // Append form fields
                formData.append('settingsId', settingsId);
                formData.append('title', title);
                formData.append('email', email);
                formData.append('password', password);
                formData.append('host', host);
                formData.append('port', port);
                if (logoUrl) {
                    formData.append('logoUrl', logoUrl); // Append the file object
                }
                formData.append('supportEmail', supportEmail);
                formData.append('supportPhone', supportPhone);
                formData.append('addressLine', addressLine);
                formData.append('action', "update-smtp-settings");

                try {
                    const response = await $.ajax({
                        url: window.location.href,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json'
                    });

                    if (response.status == 201) {
                        // Restore button state
                        $submitBtn.prop('disabled', false).html(originalBtnText);

                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: `${response.message}`,
                            confirmButtonColor: "#33cc33",
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        $submitBtn.prop('disabled', false).html(originalBtnText);
                        Swal.fire("Error", response.error || "An error occurred", "error");
                    }
                } catch (xhr) {
                    // Restore button state
                    $submitBtn.prop('disabled', false).html(originalBtnText);

                    console.error("AJAX Error:", xhr);
                    console.error("Status:", xhr.status);
                    console.error("Response Text:", xhr.responseText);

                    let errorMessage = "An error occurred while processing your request. Please try again.";
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (xhr.responseText) {
                        errorMessage = `Server Error: ${xhr.status}`;
                    }

                    Swal.fire("Error", errorMessage, "error");
                }
            });

            $(document).on('click', ".updateSmtpSettingsButton", function (event) {
                // Get all data attributes
                let settingsId = $(this).data('settings-id');
                let emailTitle = $(this).data('email-title');
                let emailAddress = $(this).data('email-address');
                let emailPassword = $(this).data('email-password');
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



                // Example: Set values in the update modal form fields
                $('#updateModalSmtpTitle').val(emailTitle);
                $('#updateModalSmtpEmail').val(emailAddress);
                $('#updateModalSmtpPassword').val(emailPassword);
                $('#updateModalSmtpHost').val(emailHost);
                $('#updateModalSmtpPort').val(emailPort);
                $('#updateModalSmtpPort').val(emailPort);
                $('#updateModalSmtpSupportEmail').val(supportEmail);
                $('#updateModalSmtpSupportPhone').val(supportPhone);
                $('#updateModalSmtpAddressLine').val(supportAddressLine);

                // Store the settings ID for update operation (you might need this hidden field in your form)
                // If you don't have a hidden field for settings ID, add one to your form:
                // <input type="hidden" id="updateModalSmtpSettingsId" name="settings_id" value="">
                $('#updateModalSmtpSettingsId').val(settingsId);

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


            $(document).on('click', '#updateModelSmtpTogglePassword', function () {
                const $passwordInput = $('#updateModalSmtpPassword');
                const $eyeIcon = $('#eyeIcon');

                if ($passwordInput.attr('type') === 'password') {
                    $passwordInput.attr('type', 'text');
                    $eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    $passwordInput.attr('type', 'password');
                    $eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $(document).on('click', '#addModelSmtpTogglePassword', function () {
                const $passwordInput = $('#modalPassword');
                const $eyeIcon = $('#modalEyeIcon');

                if ($passwordInput.attr('type') === 'password') {
                    $passwordInput.attr('type', 'text');
                    $eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    $passwordInput.attr('type', 'password');
                    $eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            })


            $(document).on('click', ".updateSmtpStatusButton", function (event) {
                let settingsId = $(this).data('settings-id');
                let settingsStatus = $(this).data('email-status');
                $('#updateStatusModalSmtpSettingsId').val(settingsId);
                $('#smtpStatus').val(settingsStatus);
            });



            $(document).on("submit", ".update-smtp-status-form", async function (e) {
                e.preventDefault();

                // Get data from input fields within the form
                let settingsId = $(this).find('input[name="settings_id"]').val().trim();
                let status = $(this).find('select[name="smtpStatus"]').val();


                console.log(status);


                // // Basic validation
                // if (status == null || settingsId == null || status === "" || settingsId === "") {
                //     Swal.fire("Error", "All fields are required. Please fill out the form completely.", "error");
                //     return;
                // }

                // Store original button text and disable button during processing
                const $submitBtn = $(this).find('#updateSmtpStatusBtn');
                const originalBtnText = $submitBtn.html();
                $submitBtn.prop('disabled', true).html('<i class="feather icon-loader"></i>&nbsp;Updating...');


                let formData = {
                    settingsId: settingsId,
                    status: status,
                    action: "update-smtp-status",

                }

                try {
                    const response = await $.ajax({
                        url: window.location.href,
                        type: 'POST',
                        data: formData,
                        dataType: 'json'
                    });

                    if (response.status == 200) {
                        // Restore button state
                        $submitBtn.prop('disabled', false).html(originalBtnText);

                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: `${response.message}`,
                            confirmButtonColor: "#33cc33",
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        $submitBtn.prop('disabled', false).html(originalBtnText);
                        Swal.fire("Error", response.error || "An error occurred", "error");
                    }
                } catch (xhr) {
                    // Restore button state
                    $submitBtn.prop('disabled', false).html(originalBtnText);

                    console.error("AJAX Error:", xhr);
                    console.error("Status:", xhr.status);
                    console.error("Response Text:", xhr.responseText);

                    let errorMessage = "An error occurred while processing your request. Please try again.";
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (xhr.responseText) {
                        errorMessage = `Server Error: ${xhr.status}`;
                    }

                    Swal.fire("Error", errorMessage, "error");
                }
            });


            $(document).on("submit", ".email-template-form", async function (e) {
                e.preventDefault();

                // Get data from input fields within the form
                let templateName = $(this).find('input[name="templateName"]').val().trim();
                let templateSubject = $(this).find('input[name="templateSubject"]').val().trim();
                let content1 = quill1.root.innerHTML;
                let content2 = quill2.root.innerHTML;
                let templateType = $(this).find('select[name="templateType"]').val();


                // Basic validation
                if (!templateName || !templateSubject || !content1 || !templateType || !content2) {
                    Swal.fire("Error", "All fields are required. Please fill out the form completely.", "error");
                    return;
                }


                // Store original button text and disable button during processing
                const $submitBtn = $(this).find('#saveTemplateModalBtn');
                const originalBtnText = $submitBtn.html();
                $submitBtn.prop('disabled', true).html('<i class="feather icon-loader"></i>&nbsp;Saving...');

                let formData = {
                    templateName: templateName,
                    templateSubject: templateSubject,
                    content1: content1,
                    content2: content2,
                    templateType: templateType,
                    action: "create-email-template"
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

            $(document).on('click', ".updateEmailTemplateButton", function (event) {
                let templateId = $(this).data('template-id');
                let templateTitle = $(this).data('template-title');
                let templateSubject = $(this).data('template-subject');
                let templateContent1 = $(this).data('template-content1');
                let templateContent2 = $(this).data('template-content2');
                let templateType = $(this).data('template-type');
                let templateStatus = $(this).data('template-status');

                $("#modalEditTemplateId").val(templateId);
                $("#editModalTemplateName").val(templateTitle);
                $("#editModalTemplateSubject").val(templateSubject);
                $("#editModalTemplateType").val(templateType);
                $("#editModalTemplateStatus").val(templateStatus);

                // Set content in Quill editors
                editQuill1.root.innerHTML = atob(templateContent1);
                editQuill2.root.innerHTML = atob(templateContent2);

            });



            $(document).on("click", ".deleteEmailTemplateButton", function (e) {
                e.preventDefault();

                let tenderId = $(this).data("template-id");
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
                                deleteEmailTemplateId: tenderId,
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

            $(document).on("submit", ".update-email-template-form", async function (e) {
                e.preventDefault();

                // Get data from input fields within the form
                let editTemplateId = $(this).find('input[name="editTemplateId"]').val().trim();
                let templateName = $(this).find('input[name="templateName"]').val().trim();
                let templateSubject = $(this).find('input[name="templateSubject"]').val().trim();
                let content1 = editQuill1.root.innerHTML;
                let content2 = editQuill2.root.innerHTML;
                let templateType = $(this).find('select[name="templateType"]').val();

                // Basic validation
                if (!templateName || !templateSubject || !content1 || !templateType || !content2) {
                    Swal.fire("Error", "All fields are required. Please fill out the form completely.", "error");
                    return;
                }


                // Store original button text and disable button during processing
                const $submitBtn = $(this).find('#editTemplateModalBtn');
                const originalBtnText = $submitBtn.html();
                $submitBtn.prop('disabled', true).html('<i class="feather icon-loader"></i>&nbsp;Saving...');

                let formData = {
                    editTemplateId: editTemplateId,
                    templateName: templateName,
                    templateSubject: templateSubject,
                    content1: content1,
                    content2: content2,
                    templateType: templateType,
                    action: "update-email-template"
                };


                await $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 200) {
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






        });
    </script>


</body>

</html>