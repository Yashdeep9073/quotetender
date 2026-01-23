<?php

include("db/config.php");
session_start();
require_once "../vendor/autoload.php";
require_once "../env.php";
require_once __DIR__ . "/./utility/fileUploader.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$upload_directory = "tender/";
if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}


$name = $_SESSION['login_user'];
$en = $_GET["id"];
$d = base64_decode($en);

if (isset($_GET['is_update'])) {
    $isUpdate = (int) $_GET['is_update'] ?? null;
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['departmentId'])) {

    try {
        $required_fields = ['departmentId'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field])) {
                echo json_encode([
                    "status" => 400,
                    "error" => "Missing required field: " . $field
                ]);
                exit;
            }
        }

        $departmentId = trim($_POST['departmentId']);



        $db->begin_transaction();

        // Fetch unique, non-empty cities only
        $stmtFetchSections = $db->prepare("SELECT * FROM section WHERE department_id = ? AND status = 1");
        $stmtFetchSections->bind_param("i", $departmentId);
        $stmtFetchSections->execute();
        $sections = $stmtFetchSections->get_result()->fetch_all(MYSQLI_ASSOC);


        echo json_encode([
            "status" => 200,
            "data" => $sections,
        ]);

        $db->commit();
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

try {

    // Lock the sequence row
    $stmtCount = $db->prepare("SELECT last_sequence FROM reference_sequence WHERE id = 1 ");
    $stmtCount->execute();
    $lastCount = $stmtCount->get_result()->fetch_array();

    $stmtFetchTenderData = $db->prepare("SELECT 
    m.name, 
    m.firm_name, 
    m.mobile, 
    ur.tender_no, 
    dept.department_name, 
    ur.name_of_work,
    ur.due_date, 
    ur.created_at, 
    ur.sent_at, 
    ur.file_name, 
    ur.tenderID, 
    ur.id, 
    ur.reference_code, 
    ur.file_name2, 
    ur.tentative_cost, 
    ur.section_id, 
    ur.division_id,
    ur.sub_division_id,
    ur.additional_files,
    MAX(s.section_name) AS section_name,
    MAX(dv.division_name) AS division_name,
    MAX(sd.subdivision) AS subdivision,
    ur.auto_quotation,
    ur.email_sent_date
FROM 
    user_tender_requests ur
INNER JOIN 
    members m ON ur.member_id = m.member_id
LEFT JOIN 
    department dept ON ur.department_id = dept.department_id
LEFT JOIN 
    section s ON ur.section_id = s.section_id
LEFT JOIN 
    division dv ON ur.division_id = dv.division_id
LEFT JOIN
    sub_division sd ON ur.sub_division_id = sd.id
WHERE 
    ur.delete_tender = '0' ANd ur.id = ?
GROUP BY 
    ur.id
ORDER BY 
    NOW() >= CAST(ur.sent_at AS DATE), 
    CAST(ur.sent_at AS DATE) ASC, 
    ABS(DATEDIFF(NOW(), CAST(ur.sent_at AS DATE)))
    ");

    $stmtFetchTenderData->bind_param("s", $d);

    $stmtFetchTenderData->execute();
    $tenderData = $stmtFetchTenderData->get_result()->fetch_array(MYSQLI_ASSOC);



    // $requestQuery = mysqli_query($db, "SELECT department.department_name, ur.file_name, ur.tenderID, ur.id ,ur.reference_code,
    // ur.tender_no,ur.name_of_work,ur.tentative_cost,ur.auto_quotation
    // FROM user_tender_requests ur 
    // inner join members m on ur.member_id= m.member_id
    // inner join department on ur.department_id = department.department_id where ur.id='" . $d . "'");

    // $requestData = mysqli_fetch_row($requestQuery);


    $departmentQuery = "SELECT * FROM department ";
    $departments = mysqli_query($db, $departmentQuery);

    $sectionQuery = "SELECT * FROM section where status= 1 ";
    $sections = mysqli_query($db, $sectionQuery);

    $stmtFetchDivision = $db->prepare("SELECT * FROM division ");
    $stmtFetchDivision->execute();
    $divisions = $stmtFetchDivision->get_result()->fetch_all(MYSQLI_ASSOC);


    $stmtFetchSubDivision = $db->prepare("SELECT * FROM sub_division ");
    $stmtFetchSubDivision->execute();
    $subDivisions = $stmtFetchSubDivision->get_result()->fetch_all(MYSQLI_ASSOC);
    // echo "<pre>";
    // // print_r($divisions);

    // foreach ($divisions as $key => $value) {
    //     print_r($value);
    // }
    // exit;

} catch (\Throwable $th) {
    $_SESSION['error'] = $th->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    try {

        $updatedBy = $_SESSION['login_user'];

        if (isset($isUpdate) && $isUpdate === 1) {

            // echo json_encode([ 
            //     "status" => 400,
            //     "error" => "debugging",
            //     "data" => $tenderData,
            // ]);

            // Check if files were actually uploaded
            if (isset($_FILES['multi_file']) && !empty($_FILES['multi_file']['name'][0])) {

                $tender = $_POST['tenderno'];
                $code = $_POST['code'];
                $work = $_POST['work'];
                $tender1 = $_POST['tender'];
                $dept = $_POST['department'];
                $section = $_POST['coutrycode'];
                $division_id = $_POST['statelist'];
                $sub_division_id = $_POST['city'];
                $tentative_cost = $_POST['tentative_cost'];
                $autoEmail = $_POST['autoEmail'];

                // Upload new files
                $multiUploadResult = uploadMedia($_FILES['multi_file'], $upload_directory, [
                    // Images
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                    'webp',
                    'bmp',
                    'svg',
                    'tiff',
                    'ico',
                    // Documents
                    'pdf',
                    'doc',
                    'docx',
                    'xls',
                    'xlsx',
                    'ppt',
                    'pptx',
                    'txt',
                    'rtf',
                    // Data
                    'csv',
                ], 2 * 1024 * 1024);

                $newMediaUrls = [];
                foreach ($multiUploadResult as $result) {
                    if (isset($result['error'])) {
                        echo json_encode([
                            "status" => 400,
                            "error" => "Error:" . $result['error'],
                        ]);
                        exit;
                    } else {
                        $newMediaUrls[] = $result['filename'];
                    }
                }

                // Get existing additional files to append to them
                mysqli_select_db($db, DB_NAME);
                $query = "SELECT additional_files FROM user_tender_requests WHERE id='" . $d . "'";
                $result = mysqli_query($db, $query);
                $row = mysqli_fetch_assoc($result);

                $existingFiles = [];
                if ($row && !empty($row['additional_files'])) {
                    $existingFiles = json_decode($row['additional_files'], true);
                    if (!is_array($existingFiles)) {
                        $existingFiles = [];
                    }
                }

                // Merge existing files with new files
                $allMediaUrls = array_merge($existingFiles, $newMediaUrls);
                $mediaUrlsJson = json_encode($allMediaUrls);

                date_default_timezone_set('Asia/Kolkata');
                $sent_at = date('Y-m-d H:i:s');

                $tender2 = $tenderData['tenderID']; // Use the tenderID from $tenderData

                // Update main fields
                mysqli_query($db, "UPDATE user_tender_requests SET 
                    `tender_no` = '$tender', 
                    `reference_code` = '$code',
                    `tenderID` = '$tender1', 
                    `tentative_cost` = '$tentative_cost', 
                    `department_id` = '$dept',
                    `section_id` = '$section',
                    `sub_division_id` = '$sub_division_id',
                    `division_id` = '$division_id',
                    `name_of_work` = '$work',
                    `file_name` = 'null',
                    `file_name2` = 'null',
                    `status` = 'Sent', 
                    `sent_at` = '$sent_at', 
                    `auto_quotation` = '$autoEmail', 
                    `updated_by` = '$updatedBy' 
                    WHERE `tenderID` = '" . $tender2 . "'");

                // Update additional_files with merged array
                $stmtUpdateMedia = $db->prepare("UPDATE user_tender_requests SET additional_files = ? WHERE tenderID = ?");
                $stmtUpdateMedia->bind_param("ss", $mediaUrlsJson, $tender2);
                $stmtUpdateMedia->execute();

            } else {
                // No files uploaded, just update the main fields without touching additional_files
                $tender = $_POST['tenderno'];
                $code = $_POST['code'];
                $work = $_POST['work'];
                $tender1 = $_POST['tender'];
                $dept = $_POST['department'];
                $section = $_POST['coutrycode'];
                $division_id = $_POST['statelist'];
                $sub_division_id = $_POST['city'];
                $tentative_cost = $_POST['tentative_cost'];
                $autoEmail = $_POST['autoEmail'];

                date_default_timezone_set('Asia/Kolkata');
                $sent_at = date('Y-m-d H:i:s');

                $tender2 = $tenderData['tenderID']; // Use the tenderID from $tenderData

                // Update main fields only (don't touch additional_files)
                mysqli_query($db, "UPDATE user_tender_requests SET 
                        `tender_no` = '$tender', 
                        `reference_code` = '$code',
                        `tenderID` = '$tender1', 
                        `tentative_cost` = '$tentative_cost', 
                        `department_id` = '$dept',
                        `section_id` = '$section',
                        `sub_division_id` = '$sub_division_id',
                        `division_id` = '$division_id',
                        `name_of_work` = '$work',
                        `file_name` = 'null',
                        `file_name2` = 'null',
                        `status` = 'Sent', 
                        `sent_at` = '$sent_at', 
                        `auto_quotation` = '$autoEmail',
                          `updated_by` = '$updatedBy'  
                        WHERE `tenderID` = '" . $tender2 . "'");
            }
        } else {
            // DEBUG
            $multiUploadResult = uploadMedia($_FILES['multi_file'], $upload_directory, [
                // Images
                'jpg',
                'jpeg',
                'png',
                'gif',
                'webp',
                'bmp',
                'svg',
                'tiff',
                'ico',
                // Documents
                'pdf',
                'doc',
                'docx',
                'xls',
                'xlsx',
                'ppt',
                'pptx',
                'txt',
                'rtf',
                // Data
                'csv',
                // Archives (optional, be cautious with these)
                // 'zip', 'rar', 'tar', 'gz',
            ], 2 * 1024 * 1024);


            $mediaUrls = [];

            foreach ($multiUploadResult as $result) {
                if (isset($result['error'])) {
                    // echo "Upload Error: " . $result['error'] . "\n";
                } else {
                    $mediaUrls[] = $result['filename'];
                }
            }

            $mediaUrlsJson = json_encode($mediaUrls);


            $tender = $_POST['tenderno'];
            $code = $_POST['code'];
            $work = $_POST['work'];
            $tender1 = $_POST['tender'];
            $dept = $_POST['department'];

            $section = $_POST['coutrycode'];
            $division_id = $_POST['statelist'];
            $sub_division_id = $_POST['city'];
            $tentative_cost = $_POST['tentative_cost'];
            $autoEmail = $_POST['autoEmail'];


            mysqli_select_db($db, DB_NAME);

            // Delete the old file
            $query = "SELECT user_tender_requests.file_name , user_tender_requests.file_name2,user_tender_requests.tenderID  FROM user_tender_requests WHERE id='" . $d . "'";
            $result = mysqli_query($db, $query);
            $row = mysqli_fetch_row($result);


            date_default_timezone_set('Asia/Kolkata');

            $sent_at = date('Y-m-d H:i:s');

            $tender2 = $row['2'];

            mysqli_query($db, "UPDATE user_tender_requests set `tender_no` ='$tender', `reference_code`='$code',`tenderID`='$tender1', 
            `tentative_cost`='$tentative_cost', `department_id`='$dept',`section_id`='$section',`sub_division_id`='$sub_division_id',`division_id`='$division_id',`name_of_work`='$work',
            `file_name`='null',`file_name2`='null',`status`='Sent', `sent_at`='$sent_at' , `auto_quotation`='$autoEmail',
               `updated_by` = '$updatedBy' 
             WHERE `tenderID`='" . $tender2 . "' ");

            $stmtUpdateMedia = $db->prepare("UPDATE user_tender_requests SET additional_files = ? WHERE tenderID = ?");
            $stmtUpdateMedia->bind_param("ss", $mediaUrlsJson, $tender2); // "si" means first param is string (JSON), second is integer (ID)
            $stmtUpdateMedia->execute();
        }

        //auto quotation 
        $autoEmailQuery = mysqli_query($db, "SELECT `auto_quotation` FROM user_tender_requests WHERE `id`= '" . $d . "' ");
        $autoEmailResult = mysqli_fetch_assoc($autoEmailQuery);
        $autoEmailResponse = $autoEmailResult["auto_quotation"];

        $stat = 1;
        $re = base64_encode($stat);

        if ($autoEmailResponse == 1) {
            $mail = new PHPMailer(true);

            // Enable SMTP debugging.
            $mail->SMTPDebug = 0;

            // Set PHPMailer to use SMTP.
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER_NAME');
            $mail->Password = getenv('SMTP_PASSCODE');
            $mail->SMTPSecure = "ssl";
            $mail->Port = getenv('SMTP_PORT');
            $mail->From = getenv('SMTP_USER_NAME');
            $mail->setFrom(getenv('SMTP_USER_NAME'), $emailSettingData['email_from_title'] ?? "Dvepl");
            $mail->IsHTML(true);

            // Add CC recipients once (outside the loop)
            foreach ($ccEmailData as $ccEmail) {
                $mail->addCC($ccEmail['cc_email']);
            }

            $membersQuery = "SELECT m.email_id, m.name, ur.file_name, ur.file_name2, ur.tenderID, ur.id, ur.additional_files FROM user_tender_requests ur 
                    inner join members m on ur.member_id= m.member_id  
                    WHERE ur.auto_quotation = '1' AND ur.tenderID='" . $tender2 . "'";
            $membersResult = mysqli_query($db, $membersQuery);

            $sentCount = 0; // Counter for successfully sent emails

            while ($memberData = mysqli_fetch_row($membersResult)) {

                // Clear previous attachments and addresses (except CC which we set once)
                $mail->clearAddresses();
                $mail->clearAttachments();

                // Add the primary recipient
                $mail->addAddress($memberData[0], $memberData[1]); // email_id and name

                // Add CC recipients again for each email (PHPMailer clears them with clearAddresses)
                foreach ($ccEmailData as $ccEmail) {
                    $mail->addCC($ccEmail['cc_email']);
                }

                $template = emailTemplate($db, "SENT_TENDER");

                $search = [
                    '{$name}',
                    '{$tenderId}',
                    '{$supportPhone}',
                    '{$enquiryEmail}',
                    '{$supportEmail}',
                ];

                $replace = [
                    $memberData[1],         // name
                    $memberData[4],         // tender id
                    $supportPhone ?? 'N/A',
                    $enquiryMail ?? 'N/A',
                    $supportEmail ?? 'N/A'
                ];

                $emailBody = nl2br($template['content_1']) . "<br><br>" . nl2br($template['content_2']);
                $finalBody = str_replace($search, $replace, $emailBody);

                // Replace placeholders in template
                $searchInSubject = [
                    '{$tenderId}',
                ];

                $replaceInSubject = [
                    $memberData[4],         // tender id
                ];

                $emailSubject = nl2br($template['email_template_subject']);
                $finalSubject = str_replace($searchInSubject, $replaceInSubject, $emailSubject);
                $mail->Subject = $finalSubject;

                // Handle attachments
                if (!empty($memberData[6])) { // Use index 6 directly
                    $extraFiles = json_decode($memberData[6], true);
                    if (is_array($extraFiles)) {
                        foreach ($extraFiles as $filePath) {
                            if (file_exists($filePath)) { // Check if file exists before adding
                                $mail->addAttachment($filePath);
                            } else {
                                error_log("Attachment file not found: " . $filePath);
                            }
                        }
                    }
                }

                // Set email body
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; color:#333; line-height:1.6;'>
                        <div style='text-align:center;'>
                            <img src='" . $logo . "' alt='DVEPL Logo' style='max-width:150px; height:auto; margin-bottom:20px;'>
                        </div>
                        $finalBody
                    </div>
                ";

                // Send the email
                if ($mail->send()) {
                    $sentCount++;

                    date_default_timezone_set("Asia/Calcutta");
                    $emailSentDate = date("Y-m-d h:i A");

                    $stmtUpdateEmailSentAt = $db->prepare("UPDATE user_tender_requests SET email_sent_date = ? WHERE id = ?");
                    $stmtUpdateEmailSentAt->bind_param("ss", $emailSentDate, $memberData[5]);
                    $stmtUpdateEmailSentAt->execute();

                } else {
                    echo json_encode([
                        "status" => 400,
                        "error" => "Mailer Error for " . $memberData[0] . ": " . $mail->ErrorInfo,
                        "sent_count" => $sentCount
                    ]);
                    exit;
                }

                // Clear attachments for next iteration
                $mail->clearAttachments();
            }

            echo json_encode([
                "status" => 201,
                "success" => "Successfully sent $sentCount emails",
            ]);
            exit;
        } else {
            echo json_encode([
                "status" => 201,
                "success" => "Success",
            ]);
            exit;
        }


    } catch (\Throwable $th) {
        echo json_encode([
            "status" => 500,
            "error" => $th->getMessage(),

        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['refCode'])) {
    # code...
// Get current timestamp in YYYYMMDDHHMMSS format
    $timestamp = date('YmdHis'); // e.g., '20250725152030' for July 25, 2025, 15:20:30

    // Use a transaction to ensure atomicity
    try {
        $db->begin_transaction();

        // Fetch invoice settings
        $prefix = "REF";

        // Lock the sequence row
        $stmt = $db->prepare("SELECT last_sequence FROM reference_sequence WHERE id = 1 FOR UPDATE");
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // No sequence row, create one
            $stmt = $db->prepare("INSERT INTO reference_sequence (id, last_sequence) VALUES (1, 0)");
            $stmt->execute();
            $lastSequence = 0;
        } else {
            $row = $result->fetch_assoc();
            $lastSequence = $row['last_sequence'];
        }

        // Increment sequence
        $newSequence = $lastSequence + 1;

        // Update sequence
        $stmt = $db->prepare("UPDATE reference_sequence SET last_sequence = ? WHERE id = 1");
        $stmt->bind_param("i", $newSequence);
        $stmt->execute();

        $db->commit();

        // Format invoice number with prefix, timestamp, and sequence
        $refNumber = sprintf("%s-%s-%05d", $prefix, $timestamp, $newSequence); // e.g., VIS-20250725152030-00001
        echo json_encode([
            "status" => 201,
            "data" => $refNumber
        ]);
        exit;
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode([
            "status" => 500,
            "error" => $e->getMessage()
        ]);
        exit;
    }
}



?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Tender Update </title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="#" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>


    <script>
        function getstate(val) {
            //alert(val);
            $.ajax({
                type: "POST",
                url: "get_state.php",
                data: 'coutrycode=' + val,
                success: function (data) {
                    $("#statelist").html(data);
                }
            });
        }

        function getcity(val) {
            //alert(val);
            $.ajax({
                type: "POST",
                url: "get_city.php",
                data: 'statecode=' + val,
                success: function (data) {
                    $("#city").html(data);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const buttons = document.querySelectorAll('.refNumber');

            buttons.forEach(function (btn) {
                btn.addEventListener('click', async function (e) {
                    e.preventDefault();

                    const codeInput = document.getElementById("code");
                    if (codeInput) {
                        try {
                            // Clear the existing value first
                            codeInput.value = '';

                            // Generate and set the new reference number
                            const refNumber = await generateReferenceNumber();
                            codeInput.value = refNumber;

                        } catch (error) {
                            console.error('Error generating reference number:', error);
                        }
                    }
                });
            });
        });

        async function generateReferenceNumber() {
            const response = await fetch("tender-edit.php?id=<?php echo $_GET['id'] ?>", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "refCode=true"
            });

            const data = await response.json();
            return data.data; // This matches your API response structure
        }

    </script>

    <style>
        .file-input-wrapper {
            position: relative;
            margin-bottom: 10px;
        }

        .remove-file-btn {
            position: absolute;
            right: 5px;
            top: 5px;
            padding: 2px 8px;
            font-size: 0.8rem;
        }

        .add-file-btn {
            margin-top: 10px;
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
                                <h5 class="m-b-10">Tender Update - Tender ID : <?php echo $tenderData['tenderID']; ?>
                                </h5>
                            </div>

                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="index.php"><i class="feather icon-home"></i> Home</a>
                                </li>
                                <li class="breadcrumb-item active"><a
                                        href="<?= isset($_GET['is_update']) && $_GET['is_update'] == 1 ? "sent-tender2.php" : "tender-request2.php" ?>"><?= isset($_GET['is_update']) && $_GET['is_update'] == 1 ? "Sent-tender" : "Tender Request" ?></a>
                                </li>
                            </ul>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-xl-3">
                    <div class="card bg-c-green order-card">
                        <div class="card-body">
                            <h6 class="text-white">Last Reference Code</h6>
                            <h2 class="text-right text-white"><i class="feather icon-bookmark float-left"></i><span
                                    id="total"><?php echo $lastCount['last_sequence']; ?></span></h2>

                        </div>
                    </div>
                </div>
            </div>


            <div class="row">

                <div class="col-sm-12">
                    <div class="card">


                        <?php
                        if (isset($msg)) {
                            echo $msg;
                        }
                        ?>

                        <div class="card-header table-card-header">
                            <form class="update-price" action="" method="post" enctype="multipart/form-data"
                                autocomplete="off">
                                <div class=" ">
                                    <!-- Text input-->
                                    <div class="row">

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">
                                                <label>Files <span class="text-danger">*</span></label>

                                                <div id="files-container">
                                                    <!-- First file input -->
                                                    <div class="file-input-wrapper">
                                                        <input name="multi_file[]" type="file"
                                                            class="form-control input-md file-input"
                                                            accept="application/pdf,application/vnd.ms-excel,.csv,.xlsx,.png,.jpeg,.jpg,.webp,.svg"
                                                            required>
                                                    </div>
                                                </div>

                                                <!-- Add file button -->
                                                <button type="button" class="btn btn-sm btn-success mt-2 add-file-btn">
                                                    <i class="fas fa-plus"></i> Add File
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">CA No <span class="text-danger">*</span>

                                                </span></label>
                                                <input id="tenderno" name="tenderno" type="text"
                                                    value="<?php echo $tenderData['tender_no'] ?? ""; ?>"
                                                    placeholder=" Enter CA No *" class="form-control input-md" value="">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                            <div class="form-group">
                                                <label for="code" class="form-label fw-bold">Reference Code <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input id="code" name="code" type="text" placeholder="Enter Code *"
                                                        class="form-control"
                                                        value="<?php echo $tenderData['reference_code'] ?? ""; ?>">
                                                    <!-- <button type="button"
                                                        class="btn btn-primary refNumber">Generate</button> -->
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Name of Work <span class="text-danger">*</span>

                                                <input id="work" name="work" type="text" class="form-control input-md"
                                                    placeholder="Name of the work"
                                                    value="<?php echo $tenderData['name_of_work'] ?? ""; ?>">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Tender ID <span class="text-danger">*</span>
                                                <label class="sr-only control-label" for="name">Tender ID<span
                                                        class=" ">
                                                    </span></label>
                                                <input id="tender" name="tender" type="text"
                                                    class="form-control input-md" placeholder="Enter tender id"
                                                    value="<?php echo $tenderData['tenderID'] ?? ""; ?>">
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Tentative Cost <span class="text-danger">*</span>
                                                <label class="sr-only control-label" for="name">Tentative Cost<span
                                                        class=" ">
                                                    </span></label>
                                                <input id="tentative_cost" name="tentative_cost" type="number" min="0"
                                                    class="form-control input-md" placeholder="Enter Tentative Cost"
                                                    value="<?php echo $tenderData['tentative_cost'] ?? ""; ?>">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Departments <span class="text-danger">*</span>
                                                <label class="sr-only control-label" for="name">Departments*<span
                                                        class=" ">
                                                    </span></label>
                                                <?php

                                                echo "<select class='form-control' name='department' id='department' >";
                                                while ($row = mysqli_fetch_row($departments)) {
                                                    $selected = $tenderData['division_id'] == $row['1'] ? "selected=''" : '';

                                                    echo "<option value='" . $row['0'] . "' " . $selected . ">" . $row['1'] . "</option>";
                                                }
                                                echo "</select>";
                                                ?>
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group"> Section <span class="text-danger">*</span>
                                                <label class="sr-only control-label" for="name">Section<span class=" ">
                                                    </span></label>
                                                <select onChange="getstate(this.value);" name="coutrycode" id="section"
                                                    class="form-control">
                                                    <option value="">Select Section</option>
                                                    <?php
                                                    while ($row = mysqli_fetch_row($sections)) {
                                                        $selected = $tenderData['section_id'] == $row['0'] ? "selected=''" : '';
                                                        echo "<option $selected value='" . $row['0'] . "'>" . $row['1'] . "</option>";
                                                    }

                                                    ?>

                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">

                                            <div class="form-group"> Division <span class="text-danger">*</span>

                                                <select class='form-control' name="statelist" id="statelist"
                                                    onChange="getcity(this.value);">
                                                    <option value=''>Select Division </option>
                                                    <?php foreach ($divisions as $key => $value) {
                                                        $selected = $value['division_id'] == $tenderData['division_id'] ? "selected" : "";
                                                        ?>
                                                        <option <?= $selected ?> value='<?= $value['division_id'] ?>'>
                                                            <?= $value['division_name'] ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>

                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">

                                            <div class="form-group"> Sub Division <span class="text-danger">*</span>

                                                <select name="city" id="city" class="form-control">
                                                    <option value="">Select Sub Division</option>
                                                    <?php foreach ($subDivisions as $key => $value) {
                                                        $selected = $value['id'] == $tenderData['sub_division_id'] ? "selected" : "";
                                                        ?>
                                                        <option <?= $selected ?> value='<?= $value['id'] ?>'>
                                                            <?= $value['subdivision'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">

                                            <div class="form-group"> Send Quotation Automatically <span
                                                    class="text-danger">*</span>
                                                <select name="autoEmail" id="auto-email" class="form-control">
                                                    <option value="">Select</option>
                                                    <option value="1" <?php echo (isset($tenderData['auto_quotation']) && $tenderData['auto_quotation'] == 1) ? 'selected' : ''; ?>>Yes
                                                    </option>
                                                    <option value="0" <?php echo (isset($tenderData['auto_quotation']) && $tenderData['auto_quotation'] == 0) ? 'selected' : ''; ?>>No
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Text input-->

                                        <!-- Button -->
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">


                                            <button type="submit" class="btn btn-secondary rounded-sm" name="submit"
                                                id="submit">
                                                <i class="feather icon-save lg"></i>&nbsp; Update
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="dt-responsive table-responsive">


                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
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
        $(document).ready(function () {

            let fileInputCount = 1;
            const maxFiles = 10;

            $('.add-file-btn').click(function () {
                if (fileInputCount < maxFiles) {
                    fileInputCount++;

                    const newFileInput = `
            <div class="file-input-wrapper mt-2">
                <input name="multi_file[]" type="file" class="form-control input-md file-input"
                    accept="application/pdf,application/vnd.ms-excel,.csv,.xlsx,.png,.jpeg,.jpg,.webp,.svg">
                <button type="button" class="btn btn-sm btn-danger remove-file-btn mt-1 ms-1">
                    <i class="fas fa-times"></i> Remove
                </button>
            </div>
        `;

                    $('#files-container').append(newFileInput);
                } else {
                    Swal.fire("Error", `Maximum ${maxFiles} files allowed!`, "error");
                }
            });

            $(document).on('click', '.remove-file-btn', function () {
                $(this).closest('.file-input-wrapper').remove();
                fileInputCount--;
            });

            // File type validation
            $(document).on('change', '.file-input', function () {
                const file = this.files[0];
                if (file) {
                    const allowedTypes = [
                        'application/pdf',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'image/png',
                        'image/jpeg',
                        'image/jpg',
                        'image/webp',
                        'image/svg+xml'
                    ];

                    if (!allowedTypes.includes(file.type)) {
                        Swal.fire("Error", `Invalid file type. Please select a valid file.`, "error");
                        $(this).val(''); // Clear the input
                    }
                }
            });

            $(document).on("submit", ".update-price", async function (e) {
                e.preventDefault();

                // Get data from input fields
                let tenderno = $('input[name="tenderno"]').val().trim();
                let code = $('input[name="code"]').val().trim();
                let work = $('input[name="work"]').val().trim();
                let tender = $('input[name="tender"]').val().trim();
                let tentative_cost = $('input[name="tentative_cost"]').val().trim();
                let department = $('select[name="department"]').val().trim();
                let coutrycode = $('select[name="coutrycode"]').val().trim();
                let statelist = $('select[name="statelist"]').val().trim();
                let city = $('select[name="city"]').val().trim();
                let autoEmail = $('select[name="autoEmail"]').val().trim();

                // Get all file inputs
                let allFileInputs = $('input[name="multi_file[]"]');
                let filesArray = [];


                // Collect all files from all file inputs
                allFileInputs.each(function () {
                    if (this.files.length > 0) {
                        for (let j = 0; j < this.files.length; j++) {
                            filesArray.push(this.files[j]);
                        }
                    }
                });

                // Basic validation
                if (!tenderno || !code || !work || !tender || !tentative_cost || !department || !coutrycode || !statelist || !city || !autoEmail || !filesArray) {
                    Swal.fire("Error", "All fields are required. Please fill out the form completely.", "error");
                    return;
                }

                // Store original button text and disable button during processing
                const $submitBtn = $('#submit');
                const originalBtnText = $submitBtn.html();
                $submitBtn.prop('disabled', true).html('<i class="feather icon-loader"></i>&nbsp;Updating...');


                let formData = new FormData();

                // Append form fields
                formData.append('tenderno', tenderno);
                formData.append('code', code);
                formData.append('work', work); // Fixed typo
                formData.append('tender', tender);
                formData.append('tentative_cost', tentative_cost);
                formData.append('department', department);
                formData.append('coutrycode', coutrycode); // Use the correct name
                formData.append('statelist', statelist); // Use the correct name
                formData.append('city', city);
                formData.append('autoEmail', autoEmail);

                // Append all files
                for (let i = 0; i < filesArray.length; i++) {
                    formData.append('multi_file[]', filesArray[i]);
                }

                // Optional: Log the FormData contents (for debugging)
                // for (let [key, value] of formData.entries()) {
                //     console.log(key, value);
                // };

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

                        $submitBtn.prop('disabled', false).val(originalBtnText);

                        await Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Tender updated successfully!',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false
                        });

                        console.log("User confirmed alert 👉 redirecting 🚀");
                        window.location.href = "tender-request2.php";

                    } else {
                        $submitBtn.prop('disabled', false).val(originalBtnText);
                        Swal.fire("Error", response.error, "error");
                    }

                } catch (err) {

                    $submitBtn.prop('disabled', false).val(originalBtnText);
                    Swal.fire("Error", "An error occurred while processing your request.", "error");
                }

            });

            console.log('working');
            $(document).on("change", "#department", async function (e) {
                let departmentId = $(this).val();


                await $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { departmentId: departmentId },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 200) {
                            let sectionSelect = $("#section");
                            sectionSelect.empty(); // clear old options
                            sectionSelect.append('<option value="">Select Section</option>');
                            console.log(response.data);

                            $.each(response.data, function (index, section) {
                                sectionSelect.append(
                                    `<option value="${section.section_id}">${section.section_name}</option>`
                                );
                            });
                        } else {
                            Swal.fire("No Data", "No Sections found.", "warning");
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        console.error("Raw Response:", xhr.responseText);
                        Swal.fire("Error", "An error occurred while processing your request. Please try again.", "error");
                    }
                });
            });

        })
    </script>



</body>

</html>