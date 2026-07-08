<?php

ini_set("display_errors", 0);
session_start();
include "db/config.php";
require_once "../vendor/autoload.php";
require_once "../env.php";
require "./utility/referenceCodeGenerator.php";
require_once __DIR__ . "/../login/utility/fileUploader.php";

$upload_directory = "login/tender/";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set("Asia/Kolkata");
$sent_at = date("Y-m-d H:i:s");

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

$name = $_SESSION["login_user"];


$adminID = $_SESSION["login_user_id"];

// Initialize the row number variable
mysqli_query($db, "SET @row_number = 0;");

$queryMain = "SELECT
    ROW_NUMBER() OVER (ORDER BY ur.created_at) AS sno,
    ur.id,
    m.name,
    m.member_id,
    m.firm_name,
    m.mobile,
    m.email_id,
    department.department_name,
    ur.due_date,
    ur.file_name,
    ur.tenderID,
    ur.reference_code,
    ur.created_at,
    ur.file_name2,
    ur.updated_by
FROM
    user_tender_requests ur
INNER JOIN
    members m ON ur.member_id = m.member_id
LEFT JOIN
    department ON ur.department_id = department.department_id
LEFT JOIN
    section s ON ur.section_id = s.section_id
LEFT JOIN
    division dv ON ur.division_id = dv.division_id
INNER JOIN
    (
        SELECT MIN(id) AS min_id, tenderID
        FROM user_tender_requests
        WHERE status = 'Requested' AND delete_tender = '0'
        GROUP BY tenderID
    ) AS unique_tenders ON ur.id = unique_tenders.min_id
ORDER BY
    ur.created_at ASC;
";

$resultMain = mysqli_query($db, $queryMain);

$adminID = $_SESSION["login_user_id"];

// Lock the sequence row
$stmtCount = $db->prepare(
    "SELECT last_sequence FROM reference_sequence WHERE id = 1 ",
);
$stmtCount->execute();
$lastCount = $stmtCount->get_result()->fetch_array();

try {
    $dept = "SELECT * FROM department where status=1 ";
    $dept = mysqli_query($db, $dept);

    $stmtFetchMembers = $db->prepare("SELECT * FROM members WHERE status = 1");
    $stmtFetchMembers->execute();
    $members = $stmtFetchMembers->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmtFetchTenderRequested = $db->prepare("SELECT
            ROW_NUMBER() OVER (ORDER BY ur.created_at) AS sno,
            COUNT(ur.id) OVER() as COUNT,  -- Window function instead of aggregate
            ur.id,
            m.name,
            m.member_id,
            m.firm_name,
            m.mobile,
            m.email_id,
            department.department_name,
            ur.due_date,
            ur.file_name,
            ur.tenderID,
            ur.reference_code,
            ur.created_at,
            ur.file_name2
        FROM
            user_tender_requests ur
        INNER JOIN
            members m ON ur.member_id = m.member_id
        LEFT JOIN
            department ON ur.department_id = department.department_id
        LEFT JOIN
            section s ON ur.section_id = s.section_id
        LEFT JOIN
            division dv ON ur.division_id = dv.division_id
        INNER JOIN
            (
                SELECT MIN(id) AS min_id, tenderID
                FROM user_tender_requests
                WHERE status = 'Requested' AND delete_tender = '0'
                GROUP BY tenderID
            ) AS unique_tenders ON ur.id = unique_tenders.min_id
        ORDER BY
    ur.created_at ASC");
    $stmtFetchTenderRequested->execute();
    $tenderRequestedCount = $stmtFetchTenderRequested
        ->get_result()
        ->fetch_array(MYSQLI_ASSOC);
} catch (\Throwable $th) {
    //throw $th;
}

if (
    $_SERVER["REQUEST_METHOD"] == "POST" &&
    isset($_POST["tender_id"]) &&
    isset($_POST["reference_code"])
) {
    try {
        $tenderId = trim($_POST["tender_id"]);
        $referenceCode = trim($_POST["reference_code"]);

        $db->begin_transaction();

        $stmtExistingTenderId = $db->prepare(
            "SELECT * FROM user_tender_requests WHERE id = ?",
        );
        $stmtExistingTenderId->bind_param("i", $tenderId);
        $stmtExistingTenderId->execute();

        $result = $stmtExistingTenderId->get_result();

        if ($result->num_rows == 0) {
            // Fixed: should be == 0, not < 0
            echo json_encode([
                "status" => 400,
                "error" => "Tender id is invalid",
            ]);
            $db->rollback(); // Add rollback
            exit();
        }

        $tenderData = $result->fetch_array(MYSQLI_ASSOC);
        // Reference Code Logs
        logReferenceCodeEvent(
            $db,
            $tenderData["tenderID"],
            $tenderData["reference_code"],
            $referenceCode,
            "UPDATED",
            "Reference code manually modified by user",
            $_SESSION["login_user"] ?? null,
        );

        // Fixed: bind parameters and execute the update statement
        $stmtUpdateReference = $db->prepare(
            "UPDATE user_tender_requests SET reference_code = ? WHERE id = ?",
        );
        $stmtUpdateReference->bind_param("si", $referenceCode, $tenderId); // Fixed: added bind_param
        $stmtUpdateReference->execute(); // Fixed: added execute

        $db->commit(); // Commit the transaction

        echo json_encode([
            "status" => 200,
            "message" => "Reference code updated successfully",
        ]);
        exit();
    } catch (\Throwable $th) {
        $db->rollback(); // Rollback on error
        echo json_encode([
            "status" => 500,
            "error" => "Database error: " . $th->getMessage(),
        ]);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["refCode"])) {
    // Use a transaction to ensure atomicity
    try {
        $tenderId = trim($_POST["tenderId"]);
        $prefix = "REF";
        $response = referenceCode($db, $prefix);
        $refNumber = $response["data"];

        $stmtFetchTender = $db->prepare(
            "Select tenderID,reference_code From user_tender_requests WHERE id = ?",
        );
        $stmtFetchTender->bind_param("i", $tenderId);
        $stmtFetchTender->execute();

        $tenderData = $stmtFetchTender->get_result()->fetch_array(MYSQLI_ASSOC);
        // Reference Code Logs
        logReferenceCodeEvent(
            $db,
            $tenderData["tenderID"],
            $tenderData["reference_code"],
            $refNumber,
            "UPDATED",
            "Reference code auto-generated using button",
            $_SESSION["login_user"] ?? null,
        );

        echo json_encode([
            "status" => 201,
            "data" => $refNumber,
        ]);
        exit();
    } catch (Exception $e) {
        echo json_encode([
            "status" => 500,
            "error" => $e->getMessage(),
        ]);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["departmentId"])) {
    try {
        $required_fields = ["departmentId"];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field])) {
                echo json_encode([
                    "status" => 400,
                    "error" => "Missing required field: " . $field,
                ]);
                exit();
            }
        }

        $departmentId = trim($_POST["departmentId"]);

        $db->begin_transaction();

        // Fetch unique, non-empty cities only
        $stmtFetchSections = $db->prepare(
            "SELECT * FROM section WHERE department_id = ? AND status = 1",
        );
        $stmtFetchSections->bind_param("i", $departmentId);
        $stmtFetchSections->execute();
        $sections = $stmtFetchSections->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            "status" => 200,
            "data" => $sections,
        ]);

        $db->commit();
        exit();
    } catch (\Throwable $th) {
        //throw $th;
        echo json_encode([
            "status" => 500,
            "error" => $th->getMessage(),
        ]);
        exit();
    }
}

$allowedTypes = [
    // Images
    "jpg",
    "jpeg",
    "png",
    "gif",
    "webp",
    "bmp",
    "svg",
    "tiff",
    "ico",
    // Documents
    "pdf",
    "doc",
    "docx",
    "xls",
    "xlsx",
    "ppt",
    "pptx",
    "txt",
    "rtf",
    // Data
    "csv",
];

function processTenderRequest(mysqli $db, array $data): array
{
    $db->begin_transaction();

    try {

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // exit();
        
        // 1️⃣ Count tender usage (lock)
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM user_tender_requests
            WHERE tenderID = ?
            AND section_id = ?
            AND member_id = ?
            FOR UPDATE
        ");

        $stmt->bind_param(
            "sii",
            $data["tender_id"],
            $data["section_id"],
            $data["member_id"],
        );
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();

        $stmt->close();

        if ($count >= 1) {
            $db->rollback();
            return [
                "success" => false,
                "message" => "You have already submitted this Tender ID.",
            ];
        }

        // Defaults (Case 1)
        $status = "Requested";
        $emailTemplate = "TENDER_REQUEST";
        $autoQuotation = 0;

        $userAdditionalFiles = $data["user_additional_files"] ?? null;
        $quotationFiles = null;
        $refCode = null;

        // 2️⃣ Check if quotation already exists (Case 3)
        $stmt = $db->prepare("
        SELECT reference_code
        FROM user_tender_requests
        WHERE tenderID = ?
        AND reference_code IS NOT NULL
        ORDER BY created_at ASC
        LIMIT 1
        ");

        $stmt->bind_param("s", $data["tender_id"]);
        $stmt->execute();
        $stmt->bind_result($existingRefCode);
        $stmt->fetch();
        $stmt->close();

        $stmt = $db->prepare("
            SELECT additional_files
            FROM user_tender_requests
            WHERE tenderID = ?
            AND status = 'Sent'
            AND auto_quotation = 1
            ORDER BY created_at ASC
            LIMIT 1
        ");
        $stmt->bind_param("s", $data["tender_id"]);
        $stmt->execute();
        $stmt->bind_result($existingQuotationFiles);
        $hasQuotation = $stmt->fetch();
        $stmt->close();

        // 3️⃣ Reference code
        if (!empty($existingRefCode)) {
            // 🔁 Reuse SAME ref for ALL users
            $refCode = $existingRefCode;
        } else {
            // 🆕 First time this tender appears globally
            $refResponse = referenceCode($db, "REF");
            $refCode = $refResponse["data"];
            logReferenceCodeEvent(
                $db,
                $data["tender_id"],
                null,
                $refCode,
                "GENERATED",
                "Initial generation",
                $_SESSION["login_register"] ?? null,
            );
        }

        $sectionId = $data["section_id"] ?? null;
        $divisionId = null;
        $subDivisionId = null;
        $serverDueDate = $data["due_date"] ?? null;
        $nameOfWork = null;
        $fileName = null;
        $fileName2 = null;
        $tentativeCost = null;
        $quotationFiles = null;
        $updatedBy = $data["updated_by"] ?? null;

        if ($hasQuotation) {
            // Case 3 → Sent
            $status = "Sent";
            $emailTemplate = "SENT_TENDER";
            $autoQuotation = 1;
            $quotationFiles = $existingQuotationFiles;

            // Fetch ALL metadata from first Sent record
            $stmt = $db->prepare("
          SELECT
            section_id,
            division_id,
            sub_division_id,
            due_date,
            name_of_work,
            file_name,
            file_name2,
            tentative_cost,
            additional_files
            FROM user_tender_requests
            WHERE tenderID = ?
            AND status = 'Sent'
            AND auto_quotation = 1
            ORDER BY created_at ASC
            LIMIT 1
        ");

            $stmt->bind_param("s", $data["tender_id"]);
            $stmt->execute();

            $stmt->bind_result(
                $sectionId,
                $divisionId,
                $subDivisionId,
                $serverDueDate,
                $nameOfWork,
                $fileName,
                $fileName2,
                $tentativeCost,
                $quotationFiles,
            );

            $stmt->fetch();
            $stmt->close();
        }

        // echo "<pre>";
        // echo "Tender Count Debug:\n";
        // echo "Tender ID: " . $data['tender_id'] . "\n";
        // echo "Section ID: " . $data['section_id'] . "\n";
        // echo "Due Date: " . $data['due_date'] . "\n";
        // echo "Section ID: " . $sectionId . "\n";
        // echo "Division ID: " . $divisionId . "\n";
        // echo "Sub Division ID: " . $subDivisionId . "\n";
        // echo "Server Due Date: " . $serverDueDate . "\n";
        // echo "Name of Work: " . $nameOfWork . "\n";
        // echo "File Name: " . $fileName . "\n";
        // echo "File Name 2: " . $fileName2 . "\n";
        // echo "Tentative Cost: " . $tentativeCost . "\n";
        // echo "Quotation Files: " . $quotationFiles . "\n";
        // echo "Member ID: " . $data['member_id'] . "\n";
        // echo "Project Name: " . $data['project_name'] . "\n";
        // echo "Project Location: " . $data['project_location'] . "\n";
        // echo "Count: " . $count . "\n";
        // print_r($stmt->error);
        // echo "</pre>";
        // exit;

        // 4️⃣ Case 3 logic (reuse quotation)
        if ($hasQuotation) {
            $status = "Sent";
            $emailTemplate = "SENT_TENDER";
            $autoQuotation = 1;
            $quotationFiles = $existingQuotationFiles;
        }

        // 5️⃣ Insert request
        $stmt = $db->prepare("
            INSERT INTO user_tender_requests
                (
                    member_id,
                    tenderID,
                    reference_code,
                    department_id,
                    section_id,
                    division_id,
                    sub_division_id,
                    due_date,
                    user_additional_files,
                    additional_files,
                    file_name,
                    file_name2,
                    tentative_cost,
                    status,
                    auto_quotation,
                    name_of_work,
                    sent_at,
                    created_at,
                    project_name,
                    project_location,
                    updated_by
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(),?,?,?)
        ");

        $stmt->bind_param(
            "issiiissssssisissss",
            $data["member_id"],
            $data["tender_id"],
            $refCode,
            $data["department_id"],
            $sectionId,
            $divisionId,
            $subDivisionId,
            $serverDueDate,
            $userAdditionalFiles,
            $quotationFiles,
            $fileName,
            $fileName2,
            $tentativeCost,
            $status,
            $autoQuotation,
            $nameOfWork,
            $data["project_name"],
            $data["project_location"],
            $updatedBy
        );

        if (!$stmt->execute()) {
            throw new Exception("Insert failed");
        }

        $stmt->close();

        // 6️⃣ Reduce max_request
        $stmt = $db->prepare("
            UPDATE members
            SET max_request = max_request - 1
            WHERE member_id = ? AND max_request > 0
        ");
        $stmt->bind_param("i", $data["member_id"]);
        $stmt->execute();
        $stmt->close();

        $db->commit();

        return [
            "success" => true,
            "status" => $status,
            "reference_code" => $refCode,
            "email_template" => $emailTemplate,
        ];
    } catch (Throwable $e) {
        print_r($e);
        exit();
        $db->rollback();
        return [
            "success" => false,
            "message" => "Something went wrong. Please try again.",
        ];
    }
}

function replaceTemplateVars(string $content, array $vars): string
{
    foreach ($vars as $key => $value) {
        $content = str_replace('{$' . $key . "}", $value, $content);
    }
    return $content;
}

function sendMail(
    array $template,
    string $toEmail,
    string $toName,
    array $placeholders,
    array $ccEmails = [],
    ?string $logo = null,
    array $attachments = [],
): bool {
    $mail = new PHPMailer(true);

    try {
        // SMTP config
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = getenv("SMTP_HOST");
        $mail->SMTPAuth = true;
        $mail->Username = getenv("SMTP_USER_NAME");
        $mail->Password = getenv("SMTP_PASSCODE");
        $mail->SMTPSecure = "ssl";
        $mail->Port = getenv("SMTP_PORT");

        // From / To
        $mail->setFrom(
            getenv("SMTP_USER_NAME"),
            $template["email_from_title"] ?? "Dvepl",
        );
        $mail->addAddress($toEmail, $toName);

        // CC (optional)
        $mail->clearCCs();
        if (!empty($ccEmails)) {
            foreach ($ccEmails as $cc) {
                if (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                    $mail->addCC($cc);
                }
            }
        }

        foreach ($attachments as $filePath) {
            $fullPath = UPLOAD_BASE_PATH . ltrim($filePath, "/");

            if (file_exists($fullPath)) {
                $mail->addAttachment($fullPath, basename($filePath));
                error_log("Attachment added ✅📎");
            } else {
                error_log("Attachment missing ❌📁 " . $fullPath);
            }
        }

        $mail->isHTML(true);

        // Subject
        $subject = $template["email_template_subject"] ?? "Notification";
        $mail->Subject = replaceTemplateVars($subject, $placeholders);

        // Body
        $body = nl2br($template["content_1"] ?? "");
        $body .= "<br><br>" . nl2br($template["content_2"] ?? "");

        $finalBody = replaceTemplateVars($body, $placeholders);

        $mail->Body =
            "
            <div style='font-family: Arial, sans-serif; color:#333; line-height:1.6;'>
                " .
            ($logo
                ? "<div style='text-align:center; margin-bottom:20px;'>
                        <img src='{$logo}' alt='Logo' style='max-width:150px;'>
                   </div>"
                : "") .
            "
                {$finalBody}
            </div>
        ";

        return $mail->send();
    } catch (Exception $e) {
        // You may log this if needed
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    try {
        if (!isset($_SESSION["login_register"])) {
            header("Location: login.php");
            exit();
        }



        // Get member & max_request
        $stmt = $db->prepare("
        SELECT member_id, name, email_id, max_request
        FROM members
        WHERE email_id = ?
        ");
        $stmt->bind_param("s", $_SESSION["login_register"]);
        $stmt->execute();
        $member = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$member || (int) $member["max_request"] <= 0) {
            $_SESSION["error"] =
                "You have reached the maximum allowed requests.";
            header("Location: index.php");
            exit();
        }

        //  Validate tender
        $tender = trim($_POST["tenderid"]);
        // if (!preg_match('/^[A-Z]+(_[A-Z]+)*_[0-9]{4}_[0-9]{2}_[0-9]{2}(_[0-9]+)?$/', $tender)) {
        //     $_SESSION['error'] = "Invalid Tender ID format.";
        //     header("Location: index.php");
        //     exit;
        // }

        $uploadedFiles = [];

        // file1
        if (!empty($_FILES["uploaded_file1"]["name"])) {
            $upload1 = uploadMedia(
                $_FILES["uploaded_file1"],
                $upload_directory,
                $allowedTypes,
                2 * 1024 * 1024,
            );

            if (isset($upload1[0]["filename"])) {
                $uploadedFiles[] = $upload1[0]["filename"];
            }
        }

        // file2
        if (!empty($_FILES["uploaded_file2"]["name"])) {
            $upload2 = uploadMedia(
                $_FILES["uploaded_file2"],
                $upload_directory,
                $allowedTypes,
                2 * 1024 * 1024,
            );

            if (isset($upload2[0]["filename"])) {
                $uploadedFiles[] = $upload2[0]["filename"];
            }
        }

        // Convert to JSON (or NULL if empty)
        $userAdditionalFilesJson = !empty($uploadedFiles)
            ? json_encode($uploadedFiles)
            : null;

        // echo "<pre>";
        // print_r($_POST);
        // echo "</pre>";
        // exit;

        // Process tender (SERVICE)
        $result = processTenderRequest($db, [
            "member_id" => (int) $member["member_id"],
            "tender_id" => $tender,
            "department_id" => $_POST["dept"],
            "section_id" => $_POST["sectionId"] ?? null,
            "project_name" => $_POST["projectName"] ?? null,
            "project_location" => $_POST["projectLocation"] ?? null,
            "due_date" => $_POST["datepicker"],
            "user_additional_files" => $userAdditionalFilesJson,
            "updated_by" => $_SESSION["login_user"] 
        ]);

        if (!$result["success"]) {
            $_SESSION["error"] = $result["message"];
            header("Location: index.php");
            exit();
        }

        // 🔹 Fetch quotation files ONLY if auto quotation applied
        $quotationFiles = [];

        if ($result["status"] === "Sent") {
            $stmt = $db->prepare("
            SELECT additional_files
            FROM user_tender_requests
            WHERE tenderID = ?
              AND status = 'Sent'
              AND auto_quotation = '1'
            ORDER BY created_at ASC
            LIMIT 1
        ");
            $stmt->bind_param("s", $tender);
            $stmt->execute();
            $stmt->bind_result($quotationFilesJson);
            $stmt->fetch();
            $stmt->close();

            if (!empty($quotationFilesJson)) {
                $quotationFiles = json_decode($quotationFilesJson, true) ?? [];
            }
        }

        // 🔹 Send email
        $template = emailTemplate($db, $result["email_template"]);

        $mailSent = sendMail(
            template: $template,
            toEmail: $member["email_id"],
            toName: $member["name"],
            placeholders: [
                "name" => $member["name"],
                "tenderId" => $tender,
                "firmName" => $member["firm_name"] ?? "",
                "supportPhone" => $supportPhone ?? "N/A",
                "enquiryEmail" => $enquiryMail ?? "N/A",
                "supportEmail" => $supportEmail ?? "N/A",
            ],
            ccEmails: array_column($ccEmailData ?? [], "cc_email"),
            logo: $logo ?? null,
            attachments: $quotationFiles,
        );

        if ($mailSent && $result["status"] === "Sent") {
            $stmt = $db->prepare("
                UPDATE user_tender_requests
                SET email_sent_date = NOW()
                WHERE tenderID = ?
                AND member_id = ?
                ORDER BY created_at DESC
                LIMIT 1
            ");

            $stmt->bind_param("si", $tender, $member["member_id"]);

            $stmt->execute();
            $stmt->close();
        }

        $_SESSION["success"] = "Tender request submitted successfully.";
        header("Location: tender-request2.php");
        exit();
    } catch (\Throwable $th) {
        print_r($th->getMessage());
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Tender Request</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        #basic-btn2_length {
            padding: 10px !important;
        }

        .dt-buttons {
            margin-top: 5px !important;
        }

        .btn-group {
            display: inline-block;
            /* margin: 0 5px; */
            padding: 8px 16px;
            border-radius: 10px;
            color: white;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;

        }

        .dt-buttons .dt-button:hover {
            background-color: #0056b3;
            /* Darker blue on hover */
            transform: scale(1.05);
            /* Slight zoom effect */
        }

        .dt-buttons .buttons-copy {
            background-color: #ff9f43;
            /* Grey for Copy */
        }

        .dt-buttons .buttons-copy:hover {
            background-color: #ff9f43;
        }

        .dt-buttons .buttons-excel {
            background-color: #28c76f;
            /* Green for Excel */
        }

        .dt-buttons .buttons-excel:hover {
            background-color: #218838;
        }

        .dt-buttons .buttons-csv {
            background-color: #00cfe8;
            /* Teal for CSV */
        }

        .dt-buttons .buttons-csv:hover {
            background-color: #138496;
        }

        .dt-buttons .buttons-print {
            background-color: #ff4560;
        }

        .dt-buttons .buttons-print:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body class="">

    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include "navbar.php"; ?>

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
                    <span><?php echo $name; ?></span>
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
                                <h5 class="m-b-10">Tender Request
                                </h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!"></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-xl-3">
                    <div class="card bg-c-blue order-card">
                        <div class="card-body">
                            <h6 class="text-white">Tender Request</h6>
                            <h2 class="text-right text-white"><i class="feather icon-message-square float-left"></i>
                                <span id="new">

                                    <?php
                                    $tenderRequestedCountValue = 0; // Default value

                                    if (
                                        $isAdmin ||
                                        hasPermission(
                                            "Tender Requests Count",
                                            $privileges,
                                            $roleData["role_name"],
                                        )
                                    ) {
                                        $tenderRequestedCountValue =
                                            $tenderRequestedCount["COUNT"] ?? 0;
                                    } else {
                                        $tenderRequestedCountValue = 0;
                                    }
                                    echo $tenderRequestedCountValue;
                                    ?>

                                </span>
                            </h2>

                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card bg-c-red order-card">
                        <div class="card-body">
                            <h6 class="text-white">Last Reference Code</h6>
                            <h2 class="text-right text-white"><i class="feather icon-bookmark float-left"></i><span
                                    id="total">
                                    <?php
                                    $lastReferenceCode = 0;
                                    if (
                                        $isAdmin ||
                                        hasPermission(
                                            "Tender Requests Last Reference Code",
                                            $privileges,
                                            $roleData["role_name"],
                                        )
                                    ) {
                                        $lastReferenceCode =
                                            $lastCount["last_sequence"] ?? 0;
                                    } else {
                                        $lastReferenceCode = 0;
                                    }
                                    echo $lastReferenceCode;
                                    ?>
                                </span>
                            </h2>

                        </div>
                    </div>
                </div>


                <div class="col-sm-12">
                    <div class="card">
                    <div class="card-header table-card-header">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-end">
                                    <?php if (
                                        $isAdmin ||
                                        hasPermission(
                                            "Create Tender Request",
                                            $privileges,
                                            $roleData["role_name"],
                                        )
                                    ) { ?>
                                        <a class="btn btn-primary rounded-sm" href="javascript:void(0);"
                                            data-bs-toggle="modal" data-bs-target="#create-tender-request-model"
                                            title="Create Tender Request" href="javascript:void(0);">Create Tender Request</a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                        <div class="card-body">
                            <div class="dt-responsive table-responsive">
                                <?php if (isset($_GET["status"])) {
                                    $st = $_GET["status"];
                                    $st1 = base64_decode($st);
                                    if ($st1 > 0) {
                                        echo " <div class='alert alert-success alert-dismissible fade show' role='alert' style='font-size:16px;' id='updateuser'>
                                        <strong><i class='feather icon-check'></i>Thanks!</strong> Tender has been Updated Successfully.
                                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                            <span aria-hidden='true'>&times;</span>
                                        </button>
                                        </div> ";
                                    } else {
                                        echo " <div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='updateuser'>
                                        <strong>Error!</strong> Tender has been not Updated
                                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                            <span aria-hidden='true'>&times;</span>
                                        </button>
                                        </div> ";
                                    }
                                } ?>
                                <br />
                                <?php if (
                                    $isAdmin ||
                                    hasPermission(
                                        "Bulk Delete Tender Request",
                                        $privileges,
                                        $roleData["role_name"],
                                    )
                                ) {
                                    echo "<a href='#' id='recycle_records' class='btn btn-danger me-3 rounded-sm'>
                                    <i class='feather icon-trash'></i> &nbsp; Move to Bin
                                    </a>&nbsp&nbsp&nbsp&nbsp";
                                } ?>
                                <div class="dt-buttons btn-group">
                                    <?php if (
                                        $isAdmin ||
                                        hasPermission(
                                            "Tender Request Excel",
                                            $privileges,
                                            $roleData["role_name"],
                                        )
                                    ) { ?>
                                        <button class="btn btn-secondary buttons-excel buttons-html5 btn-primary rounded-sm"
                                            tabindex="0" aria-controls="basic-btn2" type="button"
                                            onclick="exportTableToExcel()" title="Export to Excel"><span><i
                                                    class="fas fa-file-excel"></i>
                                                Excel</span></button>
                                    <?php } ?>
                                    <?php if (
                                        $isAdmin ||
                                        hasPermission(
                                            "Tender Request CSV",
                                            $privileges,
                                            $roleData["role_name"],
                                        )
                                    ) { ?>
                                        <button class="btn btn-secondary buttons-csv buttons-html5 btn-primary rounded-sm"
                                            tabindex="0" aria-controls="basic-btn2" type="button"
                                            onclick="exportTableToCSV()" title="Export to CSV"><span><i
                                                    class="fas fa-file-csv"></i> CSV</span></button>
                                    <?php } ?>
                                    <?php if (
                                        $isAdmin ||
                                        hasPermission(
                                            "Tender Request Print",
                                            $privileges,
                                            $roleData["role_name"],
                                        )
                                    ) { ?>
                                        <button class="btn btn-secondary buttons-print btn-primary rounded-sm" tabindex="0"
                                            onclick="printTable()" aria-controls="basic-btn2" type="button"
                                            title="Print"><span><i class="fas fa-print"></i> Print</span></button>

                                    <?php } ?>
                                </div>
                                <table id="basic-btn2" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th> <label class="checkboxs">
                                                    <input type="checkbox" id="select-all">
                                                    <span class="checkmarks"></span>
                                                </label> SNO</th>
                                            <th>Tender ID</th>
                                            <th>Reference Code</th>
                                            <th>Department</th>
                                            <th>Due Date</th>
                                            <th>Add Date </th>
                                            <th>Created By </th>
                                            <th>Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 1;
                                        while (
                                            $row = mysqli_fetch_assoc(
                                                $resultMain,
                                            )
                                        ) { ?>
                                            <tr class='record'>
                                                <td>
                                                    <div class='custom-control custom-checkbox'>
                                                        <input type='checkbox' style='margin-bottom:100px;'
                                                            class='custom-control-input request_checkbox'
                                                            id='customCheck<?php echo $row[
                                                                "sno"
                                                            ]; ?>' data-request-id=<?php echo $row[
    "id"
]; ?>
                                                        >
                                                        <label class='custom-control-label'
                                                            for='customCheck<?php echo $row[
                                                                "sno"
                                                            ]; ?>'><?php echo $row[
    "sno"
]; ?></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong>
                                                        <?php if (
                                                            $isAdmin ||
                                                            hasPermission(
                                                                "Tender Requests View",
                                                                $privileges,
                                                                $roleData[
                                                                    "role_name"
                                                                ],
                                                            )
                                                        ) { ?>
                                                            <a class='tender_id'
                                                                href='tender-request3.php?tender_id=<?php echo base64_encode(
                                                                    $row[
                                                                        "tenderID"
                                                                    ],
                                                                ); ?>'><?php echo $row[
    "tenderID"
]; ?></a>
                                                        <?php } else { ?>
                                                            <?php echo $row[
                                                                "tenderID"
                                                            ]; ?>
                                                        <?php } ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <?php echo $row[
                                                        "reference_code"
                                                    ]; ?>
                                                </td>
                                                <td>
                                                    <?php echo $row[
                                                        "department_name"
                                                    ]; ?>
                                                </td>
                                                <?php
                                                $dueDate = new DateTime(
                                                    $row["due_date"],
                                                );
                                                $formattedDueDate = $dueDate->format(
                                                    "d-m-Y",
                                                );
                                                ?>
                                                <td>
                                                    <?php echo $row[
                                                        "due_date"
                                                    ]; ?>
                                                </td>

                                                <?php
                                                $createdDate = new DateTime(
                                                    $row["created_at"],
                                                );
                                                $formattedCreatedDate = $createdDate->format(
                                                    "d-m-Y H:i:s",
                                                );
                                                echo "<td>" .
                                                    $row["created_at"] .
                                                    "</td>";
                                                $res = $row["id"];
                                                $res = base64_encode($res);
                                                ?>
                                                <td><?php echo $row["updated_by"]; ?></td>
                                                <td>

                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary " type="button"
                                                            id="actionMenu<?php echo $row[
                                                                "id"
                                                            ]; ?>"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="feather icon-more-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu"
                                                            aria-labelledby="actionMenu<?php echo $row[
                                                                "id"
                                                            ]; ?>">
                                                            <?php if (
                                                                $isAdmin ||
                                                                hasPermission(
                                                                    "Edit Tender Request",
                                                                    $privileges,
                                                                    $roleData[
                                                                        "role_name"
                                                                    ],
                                                                )
                                                            ) { ?>
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="tender-edit.php?id=<?php echo $res; ?>">
                                                                        <i class="feather icon-edit me-2"></i>Update
                                                                    </a>
                                                                </li>
                                                            <?php } ?>

                                                            <?php if (
                                                                $isAdmin ||
                                                                hasPermission(
                                                                    "Delete Tender Request",
                                                                    $privileges,
                                                                    $roleData[
                                                                        "role_name"
                                                                    ],
                                                                )
                                                            ) { ?>
                                                                <!-- <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li> -->
                                                                <li>
                                                                    <a class="dropdown-item recyclebutton" href="#"
                                                                        data-id="<?php echo $row[
                                                                            "id"
                                                                        ]; ?>" title="Move to Bin">
                                                                        <i class="feather icon-trash me-2"></i>Move to Bin
                                                                    </a>
                                                                </li>
                                                            <?php } ?>

                                                            <?php if (
                                                                $isAdmin ||
                                                                hasPermission(
                                                                    "Reference Tender Request",
                                                                    $privileges,
                                                                    $roleData[
                                                                        "role_name"
                                                                    ],
                                                                )
                                                            ) { ?>
                                                                <li>
                                                                    <a class="dropdown-item update-Reference"
                                                                        href="javascript:void(0);"
                                                                        data-tender-id="<?php echo $row[
                                                                            "id"
                                                                        ]; ?>"
                                                                        data-reference-code="<?php echo $row[
                                                                            "reference_code"
                                                                        ]; ?>"
                                                                        data-bs-toggle="modal" data-bs-target="#edit-units"
                                                                        title="Change Reference Number">
                                                                        <i class="feather icon-book me-2"></i>Reference No
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                        </ul>
                                                    </div>

                                                </td>
                                            </tr>

                                            <?php $count++;}
                                        ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="modal fade" id="create-tender-request-model" tabindex="-1"
        aria-labelledby="createTenderRequestLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="createTenderRequestLabel">
                        Create Tender Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="post" autocomplete="off" enctype="multipart/form-data">

                    <div class="modal-body">

                        <div class="row">

                            <!-- Department -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Department <span class="text-danger">*</span></label>

                                <select id="department" name="dept" class="form-select" required>
                                    <option value="">Select Department</option>

                                    <?php
                                    mysqli_data_seek($dept, 0);
                                    while ($row = mysqli_fetch_row($dept)) { ?>
                                        <option value="<?= $row[0] ?>">
                                            <?= $row[1] ?>
                                        </option>
                                        <?php }
                                    ?>
                                </select>
                            </div>

                            <!-- Section -->
                            <div class="col-md-12 mb-3" id="sectionIdContainer" style="display:none;">
                                <label class="form-label">Section</label>

                                <select id="sectionId" name="sectionId" class="form-select">
                                    <option value="">Select Section</option>
                                </select>
                            </div>

                            <!-- Project Name -->
                            <div class="col-md-12 mb-3" id="projectNameContainer" style="display:none;">
                                <label class="form-label">Project Name</label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="projectName"
                                    name="projectName"
                                    placeholder="Enter Project Name">
                            </div>

                            <!-- Project Location -->
                            <div class="col-md-12 mb-3" id="projectLocationContainer" style="display:none;">
                                <label class="form-label">Project Location</label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="projectLocation"
                                    name="projectLocation"
                                    placeholder="Enter Project Location">
                            </div>

                            <!-- Tender ID -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">
                                    Tender ID <span class="text-danger">*</span>
                                </label>

                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="tenderid"
                                        name="tenderid"
                                        placeholder="ABC_2025_12_14"
                                        required>

                                    <button
                                        type="button"
                                        class="btn btn-success"
                                        id="generateTenderId">
                                        Generate
                                    </button>
                                </div>
                            </div>

                            <!-- Bid End Date -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">
                                    Bid End Date <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    id="datepicker"
                                    name="datepicker"
                                    placeholder="Select Bid End Date"
                                    required>
                            </div>

                            <!-- Members -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">
                                    Members <span class="text-danger">*</span>
                                </label>

                                <select class="form-select" id="members" name="member_id" required>
                                    <option value="">Select Member</option>

                                    <?php foreach ($members as $member) { ?>
                                        <option value="<?= $member[
                                            "member_id"
                                        ] ?>">
                                            <?= $member["name"] ?>
                                            (<?= $member["firm_name"] ?>)
                                            (<?= $member["email_id"] ?>)
                                            (<?= $member["mobile"] ?>)

                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- File 1 -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Tender Document
                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    id="uploaded_file1"
                                    name="uploaded_file1"
                                    accept=".pdf,.xls,.xlsx">
                            </div>

                            <!-- File 2 -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Additional Document
                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    id="uploaded_file2"
                                    name="uploaded_file2"
                                    accept=".pdf,.xls,.xlsx"
                                    disabled>
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button
                            type="submit"
                            name="submit"
                            class="btn btn-primary">
                            Submit
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>



    <div class="modal fade" id="edit-units" tabindex="-1" aria-labelledby="editUnitsLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUnitsLabel">Update Reference Number</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="update-reference-code">
                    <div class="modal-body">
                        <input type="hidden" class="form-control" name="editTenderId" id="editTenderId">
                        <div class="row">
                            <div class="col-12 col-md-12 mb-3">
                                <label for="editReferenceCode" class="form-label">Reference Number</label>
                                <div class="input-group">

                                    <input type="text" class="form-control" id="editReferenceCode"
                                        name="editReferenceCode">
                                    <button type="button" name="updateReferenceCode"
                                        class="btn btn-primary refNumber">Generate</button>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src=" assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <script src="assets/js/plugins/jquery.dataTables.min.js"></script>
    <script src="assets/js/plugins/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/plugins/dataTables.buttons.min.js"></script>
    <script src="assets/js/plugins/buttons.colVis.min.js"></script>
    <script src="assets/js/plugins/buttons.print.min.js"></script>
    <script src="assets/js/plugins/pdfmake.min.js"></script>
    <script src="assets/js/plugins/jszip.min.js"></script>
    <script src="assets/js/plugins/buttons.html5.min.js"></script>
    <script src="assets/js/plugins/buttons.bootstrap4.min.js"></script>
    <!-- <script src="assets/js/pages/data-export-custom.js"></script> -->

    <!-- Excel Generate  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>




    <script>
        $(document).ready(function () {

            //     if ($.fn.DataTable.isDataTable('#basic-btn2')) {
            //     $('#basic-btn2').DataTable().clear().destroy();
            //     }
            //     let myTable = $("#basic-btn2").DataTable();
            //     let columnsToFilter = [8,9,10];


            //     columnsToFilter.forEach(function(colID) {

            //     let mySelectList = $("<br><select class='form-control' />")
            //         .appendTo(myTable.column(colID).header())
            //         .on("change", function () {
            //             myTable.column(colID).search($(this).val());
            //             // Update the changes using draw() method
            //             myTable.column(colID).draw();
            //         });

            //     myTable
            //         .column(colID)
            //         .cache("search")
            //         .sort()
            //         .each(function (param) {
            //             mySelectList.append(
            //                 $('<option value="' + param + '">'
            //                 + param + "</option>")
            //             );
            //         });
            // });

            // $('#basic-btn2 thead tr').clone(true).appendTo('#basic-btn2 thead');

            // var columnsWithSearch = [6, 8, 9, 10, 11, 13];

            // $('#basic-btn2 thead tr:eq(1) th').each(function (i) {

            //     if (columnsWithSearch.includes(i) && !$(this).hasClass("noFilter")) {
            //         var title = $(this).text();
            //         $(this).html('<input type="text" class="form-control" placeholder="Search ' + title + '" />');

            //         $('input', this).on('keyup change', function () {
            //             if (table.column(i).search() !== this.value) {
            //                 table
            //                     .column(i)
            //                     .search(this.value)
            //                     .draw();
            //             }
            //         });
            //     } else {
            //         $(this).html('<span></span>');
            //     }
            // });

            // var table = $('#basic-btn2').DataTable({
            //     orderCellsTop: true,
            //     fixedHeader: true,
            //     columnDefs: [
            //         {
            //             targets: 0,
            //             visible: true
            //         },

            //     ]
            // });


            // $("#updateuser").delay(5000).slideUp(300);


        });
    </script>

    <script type="text/javascript">
        $(function () {
            $(".recyclebutton").click(function () {

                var element = $(this);

                var del_id = element.attr("id");

                var info = 'id=' + del_id;
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
                            type: "GET",
                            url: "deleteuser.php",
                            data: info,
                            success: function () {
                                // Show success message
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'The record has been deleted.',
                                    icon: 'success',
                                    confirmButtonColor: "#33cc33",
                                    timer: 1500,
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
                                    }, 2000);
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

            $('#recycle_records').on('click', function (e) {
                var requestIDs = [];
                $(".request_checkbox:checked").each(function () {
                    requestIDs.push($(this).data('request-id'));
                });
                if (requestIDs.length <= 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Please select records!",
                        confirmButtonColor: "#33cc33"
                    });
                    return;
                } else {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert " + (requestIDs.length > 1 ? "these" : "this") + " Record" + (requestIDs.length > 1 ? "s" : "") + "!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#33cc33",
                        cancelButtonColor: "#ff5471",
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "Cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var selected_values = requestIDs.join(",");
                            $.ajax({
                                type: "POST",
                                url: "recycleuser.php",
                                cache: false,
                                data: 'alot_request_ids=' + selected_values,
                                success: function (response) {
                                    // Show success message
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'Record' + (requestIDs.length > 1 ? "s" : "") + ' deleted successfully.',
                                        icon: 'success',
                                        confirmButtonColor: "#33cc33",
                                        timer: 1500,
                                        timerProgressBar: true,
                                        showConfirmButton: false
                                    }).then(() => {
                                        // Animate and remove records
                                        $(".request_checkbox:checked").each(function () {
                                            $(this).closest(".record").animate({
                                                backgroundColor: "#FF3"
                                            }, "fast").animate({
                                                opacity: "hide"
                                            }, "slow", function () {
                                                $(this).remove();
                                            });
                                        });

                                        // Reload page after animation
                                        setTimeout(function () {
                                            window.location.reload();
                                        }, 2000);
                                    });
                                },
                                error: function (error) {
                                    console.log(error);
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'Something went wrong while deleting records.',
                                        icon: 'error',
                                        confirmButtonColor: "#33cc33"
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });

    </script>

    <script>
        $(document).ready(function () {
            setInterval(function () {
                //$("#new").load("load.php");
                // refresh();
            }, 100);
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function () {
            // Initialize the DataTable with buttons
            var table = $('#basic-btn2').DataTable({
                pageLength: 100,
                lengthMenu: [25, 50, 100, 200, 500, 1000], // Custom dropdown options
                responsive: true,
                ordering: true,
                searching: true
            });

            // // Fetch the number of entries
            // var info = table.page.info();
            // var totalEntries = info.recordsTotal;

            // $('#new').text(totalEntries);
        });
    </script>


    <script>
        function printTable() {
            // Clone the table to avoid altering the original
            const tableClone = document.getElementById("basic-btn2").cloneNode(true);

            // Remove the "Action" column and its corresponding cells
            const thElements = tableClone.querySelectorAll("th");
            const actionColumnIndex = Array.from(thElements).findIndex((th) =>
                th.textContent.trim().toLowerCase() === "edit"
            );

            if (actionColumnIndex !== -1) {
                // Remove the "Action" header
                thElements[actionColumnIndex].remove();

                // Remove cells in the "Action" column
                tableClone.querySelectorAll("tr").forEach((row) => {
                    const cells = row.querySelectorAll("td, th");
                    if (cells[actionColumnIndex]) {
                        cells[actionColumnIndex].remove();
                    }
                });
            }

            const pageTitle = document.title; // Get the current page title
            const printWindow = window.open("", "", "height=800,width=1200");

            printWindow.document.write(`
      <html>
        <head>
          <title>${pageTitle}</title>
          <style>
            body {
              font-family: Arial, sans-serif;
              margin: 20px;
              padding: 0;
              background-color: #f9f9f9;
              color: #333;
            }
            h1 {
              text-align: center;
              color: #007bff;
              margin-bottom: 20px;
              font-size: 24px;
              text-transform: uppercase;
            }
            table {
              width: 100%;
              border-collapse: collapse;
              margin-bottom: 20px;
              background-color: #fff;
              box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
              border-radius: 8px;
              overflow: hidden;
            }
            th {
              background-color: #007bff;
              color: white;
              text-align: left;
              padding: 12px 15px;
              font-size: 14px;
              text-transform: uppercase;
            }
            td {
              padding: 10px 15px;
              border-bottom: 1px solid #ddd;
              font-size: 13px;
            }
            tr:nth-child(even) {
              background-color: #f2f2f2;
            }
            tr:hover {
              background-color: #eaf4ff;
            }
            footer {
              text-align: center;
              margin-top: 20px;
              font-size: 12px;
              color: #555;
            }
          </style>
        </head>
        <body>
          <h1>${pageTitle}</h1>
          ${tableClone.outerHTML}
          <footer>
            Printed on: ${new Date().toLocaleString()}
          </footer>
        </body>
      </html>
    `);

            printWindow.document.close();
            printWindow.print();
        }
    </script>

    <script>
        function exportTableToExcel(tableId, filename = 'table.xlsx') {
            const table = document.getElementById("basic-btn2");
            const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
            XLSX.writeFile(wb, filename);
        }
    </script>

    <script>
        function exportTableToCSV(tableId, filename = 'table.csv') {
            const table = document.getElementById("basic-btn2");
            const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
            XLSX.writeFile(wb, filename);
        }
    </script>

    <script>
        $(document).ready(function () {

            $(document).on('change', '#select-all', function (e) {
                var isChecked = $(this).prop('checked');

                // Select/Deselect all checkboxes with class 'member_checkbox'
                $('.request_checkbox').prop('checked', isChecked);

                // Stop propagation
                e.stopPropagation();
            });

            // Prevent sorting when clicking on checkbox area in header
            $('.checkboxs').on('click', function (e) {
                e.stopPropagation();
            });

            // Handle individual checkbox clicks to update select-all state
            $(document).on('click', '.request_checkbox', function () {
                updateSelectAllState();
            });

            // Function to update select-all checkbox state
            function updateSelectAllState() {
                var totalCheckboxes = $('.request_checkbox').length;
                var checkedCheckboxes = $('.request_checkbox:checked').length;

                // Update select all checkbox state
                $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
            }


            $(document).on('click', ".update-Reference", function (event) {
                let tenderId = $(this).data('tender-id');
                let referenceCode = $(this).data('reference-code');

                // Set values in modal form
                $('#editTenderId').val(tenderId);
                $('#editReferenceCode').val(referenceCode);
            });



            $('.refNumber').on('click', async function (e) {
                e.preventDefault();

                const $codeInput = $("#editReferenceCode");
                // Get values correctly using the name attributes
                let tenderId = $("input[name='editTenderId']").val();
                if ($codeInput.length) {
                    try {
                        // Clear the existing value first
                        $codeInput.val('');



                        // Generate and set the new reference number
                        const refNumber = await generateReferenceNumber(tenderId);
                        console.log(refNumber)
                        $codeInput.val(refNumber);

                    } catch (error) {
                        console.error('Error generating reference number:', error);
                    }
                }
            });

            function generateReferenceNumber(tenderId) {
                return $.ajax({
                    url: window.location.href,
                    method: "POST",
                    data: { refCode: true, tenderId: tenderId },
                    dataType: "json"
                }).then(function (data) {
                    return data.data; // This matches your API response structure
                });
            }



            $(document).on("submit", ".update-reference-code", function (e) {
                e.preventDefault();

                // Get values correctly using the name attributes
                let tenderId = $("input[name='editTenderId']").val();
                let referenceCode = $("input[name='editReferenceCode']").val();


                // Your AJAX submission logic here
                $.ajax({
                    url: window.location.href, // Change to your actual endpoint
                    method: 'POST',
                    data: {
                        tender_id: tenderId,
                        reference_code: referenceCode
                    },

                    success: function (response) {
                        $('#edit-units').modal('hide');

                        let result = JSON.parse(response);
                        if (result.status == 200) {

                            // Show success message
                            Swal.fire({
                                title: 'Updated!',
                                text: result.message,
                                icon: 'success',
                                confirmButtonColor: "#33cc33",
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                // Reload page after animation
                                setTimeout(function () {
                                    window.location.reload();
                                }, 2000);
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

                        console.log(response);

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

    <script>
        $(document).ready(function () {

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
                ],
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
            $('#generateTenderId').on('click', function () {


                const deptSelect = $('select[name="dept"]');
                const selectedDeptValue = deptSelect.val();
                const selectedDeptText = deptSelect.find('option:selected').text();

                if (!selectedDeptValue) {
                    notyf.error("Please select a Department");
                    return;
                }

                // Date: YYYY_MM_DD
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const dateStr = `${year}`;

                // Department code
                let deptCode = selectedDeptText.split('-')[0].trim().split(' ')[0];
                deptCode = deptCode.substring(0, 3).toUpperCase();

                // Random suffix
                const randomNo = Math.floor(10 + Math.random() * 90);
                let tenderId;

                if (deptCode === "PRI") {
                    // PRI → NO trailing underscore
                    tenderId = `${deptCode}_${dateStr}`;
                } else {
                    // Other departments → keep underscore
                    tenderId = `${deptCode}_${dateStr}_`;
                }

                $('#tenderid').val(tenderId);

                notyf.success("Tender ID generated successfully");
            });




            $(document).on("change", "#department", async function (e) {
                let departmentId = $(this).val();


                await $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { departmentId: departmentId },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 200) {
                            let sectionSelect = $("#sectionId");
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


            $(document).on("change", "#department", async function (e) {
                const $department = $('select[name="dept"]');


                const selectedValue = $.trim(
                    $department.find('option:selected').text()
                );


                if (selectedValue === 'Private') {
                    $("#sectionIdContainer").show();
                    $("#projectNameContainer").show();
                    $("#projectLocationContainer").show();
                } else {
                    $("#sectionIdContainer").hide();
                    $("#sectionId").val("");
                    $("#projectNameContainer").hide();
                    $("#projectLocationContainer").hide();
                    return;
                }
            });


        });
    </script>
</body>

</html>
