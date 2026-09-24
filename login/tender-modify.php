<?php
include "db/config.php";
session_start();

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
    exit();
}

$name = $_SESSION["login_user"];
$adminId = (int)($_SESSION["login_user_id"] ?? 0);

$encodedId = $_GET["id"] ?? "";
$tenderRowId = (int)base64_decode($encodedId, true);

if ($tenderRowId <= 0) {
    $_SESSION["error"] = "Invalid tender ID.";
    header("Location: sent-tender2.php");
    exit();
}

function jsonResponse(int $status, array $payload): void
{
    http_response_code($status >= 400 ? $status : 200);
    header("Content-Type: application/json");
    echo json_encode($payload);
    exit();
}

/*
 * AJAX: department -> sections
 * Data modification only. No status/mail/file behavior.
 */
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "get_sections"
) {
    $departmentId = (int)($_POST["department_id"] ?? 0);

    if ($departmentId <= 0) {
        jsonResponse(400, ["status" => 400, "error" => "Invalid department."]);
    }

    $stmtDept = $db->prepare("SELECT department_name FROM department WHERE department_id = ? LIMIT 1");
    $stmtDept->bind_param("i", $departmentId);
    $stmtDept->execute();
    $department = $stmtDept->get_result()->fetch_assoc();

    if (!$department) {
        jsonResponse(404, ["status" => 404, "error" => "Department not found."]);
    }

    /*
     * Preserve QuoteTender's existing behavior:
     * - Private: sections linked to the selected department.
     * - Other departments: common sections where department_id = 0.
     */
    if (strtolower(trim((string)$department["department_name"])) === "private") {
        $stmt = $db->prepare("
            SELECT section_id, section_name
            FROM section
            WHERE department_id = ? AND status = 1
            ORDER BY section_name
        ");
        $stmt->bind_param("i", $departmentId);
    } else {
        $stmt = $db->prepare("
            SELECT section_id, section_name
            FROM section
            WHERE department_id = 0 AND status = 1
            ORDER BY section_name
        ");
    }

    $stmt->execute();
    jsonResponse(200, [
        "status" => 200,
        "data" => $stmt->get_result()->fetch_all(MYSQLI_ASSOC),
    ]);
}

/* AJAX: section -> divisions */
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "get_divisions"
) {
    $sectionId = (int)($_POST["section_id"] ?? 0);

    if ($sectionId <= 0) {
        jsonResponse(400, ["status" => 400, "error" => "Invalid section."]);
    }

    $stmt = $db->prepare("
        SELECT division_id, division_name
        FROM division
        WHERE section_id = ?
        ORDER BY division_name
    ");
    $stmt->bind_param("i", $sectionId);
    $stmt->execute();

    jsonResponse(200, [
        "status" => 200,
        "data" => $stmt->get_result()->fetch_all(MYSQLI_ASSOC),
    ]);
}

/* AJAX: division -> subdivisions */
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "get_subdivisions"
) {
    $divisionId = (int)($_POST["division_id"] ?? 0);

    if ($divisionId <= 0) {
        jsonResponse(400, ["status" => 400, "error" => "Invalid division."]);
    }

    $stmt = $db->prepare("
        SELECT id, subdivision
        FROM sub_division
        WHERE division_id = ?
        ORDER BY subdivision
    ");
    $stmt->bind_param("i", $divisionId);
    $stmt->execute();

    jsonResponse(200, [
        "status" => 200,
        "data" => $stmt->get_result()->fetch_all(MYSQLI_ASSOC),
    ]);
}

/* Fetch the selected Sent Tender row. */
$stmtTender = $db->prepare("
    SELECT
        ur.id,
        ur.tenderID,
        ur.tender_no,
        ur.reference_code,
        ur.name_of_work,
        ur.tentative_cost,
        ur.department_id,
        ur.section_id,
        ur.division_id,
        ur.sub_division_id,
        ur.due_date,
        ur.project_name,
        ur.project_location,
        ur.status,
        ur.updated_by,
        d.department_name,
        s.section_name,
        dv.division_name,
        sd.subdivision
    FROM user_tender_requests ur
    LEFT JOIN department d ON d.department_id = ur.department_id
    LEFT JOIN section s ON s.section_id = ur.section_id
    LEFT JOIN division dv ON dv.division_id = ur.division_id
    LEFT JOIN sub_division sd ON sd.id = ur.sub_division_id
    WHERE ur.id = ?
      AND ur.delete_tender = '0'
    LIMIT 1
");
$stmtTender->bind_param("i", $tenderRowId);
$stmtTender->execute();
$tenderData = $stmtTender->get_result()->fetch_assoc();

if (!$tenderData) {
    $_SESSION["error"] = "Tender not found.";
    header("Location: sent-tender2.php");
    exit();
}

/*
 * This page is for already-Sent tender data modification only.
 * It never changes the status and never sends quotation email.
 */
if (strcasecmp((string)$tenderData["status"], "Sent") !== 0) {
    $_SESSION["error"] = "Only Sent Tender records can be modified from this page.";
    header("Location: sent-tender2.php");
    exit();
}

$originalTenderId = (string)$tenderData["tenderID"];

/* Save modified tender data. */
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "modify_tender"
) {
    try {
        $tenderNo = trim((string)($_POST["tender_no"] ?? ""));
        $referenceCode = trim((string)($_POST["reference_code"] ?? ""));
        $nameOfWork = trim((string)($_POST["name_of_work"] ?? ""));
        $newTenderId = trim((string)($_POST["tender_id"] ?? ""));
        $tentativeCostRaw = trim((string)($_POST["tentative_cost"] ?? ""));
        $departmentId = (int)($_POST["department_id"] ?? 0);
        $sectionId = (int)($_POST["section_id"] ?? 0);
        $divisionId = (int)($_POST["division_id"] ?? 0);
        $subDivisionId = (int)($_POST["sub_division_id"] ?? 0);
        $dueDate = trim((string)($_POST["due_date"] ?? ""));
        $projectName = trim((string)($_POST["project_name"] ?? ""));
        $projectLocation = trim((string)($_POST["project_location"] ?? ""));
        $updatedBy = (string)($_SESSION["login_user"] ?? "");

        if (
            $tenderNo === "" ||
            $referenceCode === "" ||
            $nameOfWork === "" ||
            $newTenderId === "" ||
            $tentativeCostRaw === "" ||
            $departmentId <= 0 ||
            $sectionId <= 0 ||
            $dueDate === ""
        ) {
            jsonResponse(400, [
                "status" => 400,
                "error" => "Please fill all required tender details.",
            ]);
        }

        if (!is_numeric($tentativeCostRaw) || (float)$tentativeCostRaw < 0) {
            jsonResponse(400, [
                "status" => 400,
                "error" => "Tentative cost must be a valid non-negative number.",
            ]);
        }

        $dateObj = DateTime::createFromFormat("Y-m-d", $dueDate);
        if (!$dateObj || $dateObj->format("Y-m-d") !== $dueDate) {
            jsonResponse(400, [
                "status" => 400,
                "error" => "Invalid due date.",
            ]);
        }

        // Private department does not require division/subdivision.
        $stmtDeptName = $db->prepare("SELECT department_name FROM department WHERE department_id = ? LIMIT 1");
        $stmtDeptName->bind_param("i", $departmentId);
        $stmtDeptName->execute();
        $deptRow = $stmtDeptName->get_result()->fetch_assoc();

        if (!$deptRow) {
            jsonResponse(400, ["status" => 400, "error" => "Invalid department."]);
        }

        $isPrivate = strtolower(trim((string)$deptRow["department_name"])) === "private";
        if ($isPrivate) {
            $divisionId = 0;
            $subDivisionId = 0;
        } elseif ($divisionId <= 0 || $subDivisionId <= 0) {
            jsonResponse(400, [
                "status" => 400,
                "error" => "Division and Sub Division are required.",
            ]);
        }

        /*
         * Sent Tender is represented by multiple member rows sharing tenderID.
         * Update the shared tender details for every row with the original tenderID
         * so members do not end up with different versions of the same tender.
         *
         * Intentionally NOT updated:
         * - status
         * - sent_at
         * - auto_quotation
         * - email_sent_date
         * - additional_files / file_name / file_name2
         */
        $db->begin_transaction();

        $tentativeCost = (string)$tentativeCostRaw;

        $stmtUpdate = $db->prepare("
            UPDATE user_tender_requests
            SET
                tender_no = ?,
                reference_code = ?,
                name_of_work = ?,
                tenderID = ?,
                tentative_cost = ?,
                department_id = ?,
                section_id = ?,
                division_id = NULLIF(?, 0),
                sub_division_id = NULLIF(?, 0),
                due_date = ?,
                project_name = ?,
                project_location = ?,
                updated_by = ?
            WHERE tenderID = ?
              AND delete_tender = '0'
              AND status = 'Sent'
        ");
        $stmtUpdate->bind_param(
            "sssssiiiisssss",
            $tenderNo,
            $referenceCode,
            $nameOfWork,
            $newTenderId,
            $tentativeCost,
            $departmentId,
            $sectionId,
            $divisionId,
            $subDivisionId,
            $dueDate,
            $projectName,
            $projectLocation,
            $updatedBy,
            $originalTenderId
        );

        if (!$stmtUpdate->execute()) {
            throw new RuntimeException("Unable to update tender details.");
        }

        $affectedRows = $stmtUpdate->affected_rows;
        $db->commit();

        jsonResponse(200, [
            "status" => 200,
            "message" => "Tender details updated successfully.",
            "affected_rows" => $affectedRows,
        ]);
    } catch (Throwable $e) {
        try {
            $db->rollback();
        } catch (Throwable $ignore) {
        }

        jsonResponse(500, [
            "status" => 500,
            "error" => $e->getMessage(),
        ]);
    }
}

/* Dropdown data for initial render. */
$departments = [];
$resDepartments = $db->query("SELECT department_id, department_name FROM department WHERE status = 1 ORDER BY department_name");
while ($row = $resDepartments->fetch_assoc()) {
    $departments[] = $row;
}

$sections = [];
$resSections = $db->query("SELECT section_id, section_name, department_id FROM section WHERE status = 1 ORDER BY section_name");
while ($row = $resSections->fetch_assoc()) {
    $sections[] = $row;
}

$divisions = [];
$resDivisions = $db->query("SELECT division_id, division_name, section_id FROM division ORDER BY division_name");
while ($row = $resDivisions->fetch_assoc()) {
    $divisions[] = $row;
}

$subDivisions = [];
$resSubDivisions = $db->query("SELECT id, subdivision, division_id FROM sub_division ORDER BY subdivision");
while ($row = $resSubDivisions->fetch_assoc()) {
    $subDivisions[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <title>Modify Sent Tender</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .tender-modify-page { padding: 16px; }
        .tender-modify-page .tm-card {
            background: #fff;
            border: 0;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(16,24,40,.05), 0 1px 3px rgba(16,24,40,.08);
            margin-bottom: 16px;
        }
        .tender-modify-page .tm-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        .tender-modify-page .tm-title {
            margin: 0;
            color: #0f172a;
            font-size: 18px;
            font-weight: 650;
        }
        .tender-modify-page .tm-subtitle {
            margin-top: 3px;
            color: #64748b;
            font-size: 12.5px;
        }
        .tender-modify-page .tm-body { padding: 20px; }
        .tender-modify-page .form-group { margin-bottom: 14px; }
        .tender-modify-page label {
            display: block;
            margin-bottom: 6px;
            color: #344054;
            font-size: 13px;
            font-weight: 600;
        }
        .tender-modify-page .form-control {
            min-height: 40px;
            border: 1px solid #d0d5dd;
            border-radius: 7px;
            font-size: 13.5px;
        }
        .tender-modify-page .form-control:focus {
            border-color: #33cc33;
            box-shadow: 0 0 0 3px rgba(51,204,51,.10);
        }
        .tender-modify-page .tm-note {
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            background: #eff6ff;
            color: #1e40af;
            font-size: 13px;
        }
        .tender-modify-page .tm-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            padding-top: 6px;
        }
        .tender-modify-page .tm-actions .btn {
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 7px;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="loader-bg">
        <div class="loader-track"><div class="loader-fill"></div></div>
    </div>

    <?php include "navbar.php"; ?>

    <header class="navbar pcoded-header navbar-expand-lg navbar-light headerpos-fixed header-blue">
        <div class="m-header">
            <a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>
            <a href="#!" class="b-brand" style="font-size:24px;">ADMIN PANEL</a>
            <a href="#!" class="mob-toggler"><i class="feather icon-more-vertical"></i></a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a href="#!" class="full-screen" onclick="javascript:toggleFullScreen()">
                        <i class="feather icon-maximize"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="dropdown drp-user">
            <a href="#!" class="dropdown-toggle" data-toggle="dropdown">
                <img src="assets/images/user.png" class="img-radius wid-40" alt="User-Profile-Image">
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-notification">
                <div class="pro-head">
                    <img src="assets/images/user.png" class="img-radius" alt="User-Profile-Image">
                    <span><?php echo htmlspecialchars($name); ?></span>
                    <a href="logout.php" class="dud-logout" title="Logout"><i class="feather icon-log-out"></i></a>
                </div>
            </div>
        </div>
    </header>

    <section class="pcoded-main-container">
        <div class="pcoded-content tender-modify-page">
            <div class="tm-card">
                <div class="tm-header">
                    <div>
                        <h5 class="tm-title">
                            <i class="feather icon-edit-3"></i>
                            Modify Sent Tender
                        </h5>
                        <div class="tm-subtitle">
                            Tender ID: <?php echo htmlspecialchars((string)$tenderData["tenderID"]); ?>
                            &nbsp;•&nbsp;
                            Ref: <?php echo htmlspecialchars((string)$tenderData["reference_code"]); ?>
                        </div>
                    </div>
                    <a href="sent-tender2.php" class="btn btn-outline-secondary">
                        <i class="feather icon-arrow-left"></i> Back to Sent Tender
                    </a>
                </div>

                <div class="tm-body">
                    <div class="tm-note">
                        <i class="feather icon-info"></i>
                        This page only modifies tender data. It does not upload quotation files,
                        change tender status, update sent time, or send customer emails.
                    </div>

                    <form id="tender-modify-form" autocomplete="off">
                        <input type="hidden" name="action" value="modify_tender">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>CA No / Tender No <span class="text-danger">*</span></label>
                                    <input type="text" name="tender_no" class="form-control"
                                           value="<?php echo htmlspecialchars((string)($tenderData["tender_no"] ?? "")); ?>"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Reference Code <span class="text-danger">*</span></label>
                                    <input type="text" name="reference_code" class="form-control"
                                           value="<?php echo htmlspecialchars((string)($tenderData["reference_code"] ?? "")); ?>"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tender ID <span class="text-danger">*</span></label>
                                    <input type="text" name="tender_id" class="form-control"
                                           value="<?php echo htmlspecialchars((string)$tenderData["tenderID"]); ?>"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name of Work <span class="text-danger">*</span></label>
                                    <input type="text" name="name_of_work" class="form-control"
                                           value="<?php echo htmlspecialchars((string)($tenderData["name_of_work"] ?? "")); ?>"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tentative Cost <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" name="tentative_cost" class="form-control"
                                           value="<?php echo htmlspecialchars((string)($tenderData["tentative_cost"] ?? "")); ?>"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Due Date <span class="text-danger">*</span></label>
                                    <input type="date" name="due_date" class="form-control"
                                           value="<?php echo htmlspecialchars(substr((string)($tenderData["due_date"] ?? ""), 0, 10)); ?>"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Department <span class="text-danger">*</span></label>
                                    <select name="department_id" id="modify-department" class="form-control" required>
                                        <option value="">Select Department</option>
                                        <?php foreach ($departments as $department): ?>
                                            <option value="<?php echo (int)$department["department_id"]; ?>"
                                                <?php echo ((int)$tenderData["department_id"] === (int)$department["department_id"]) ? "selected" : ""; ?>>
                                                <?php echo htmlspecialchars($department["department_name"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Section <span class="text-danger">*</span></label>
                                    <select name="section_id" id="modify-section" class="form-control" required>
                                        <option value="">Select Section</option>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?php echo (int)$section["section_id"]; ?>"
                                                <?php echo ((int)$tenderData["section_id"] === (int)$section["section_id"]) ? "selected" : ""; ?>>
                                                <?php echo htmlspecialchars($section["section_name"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6" id="modify-division-wrapper">
                                <div class="form-group">
                                    <label>Division <span class="text-danger">*</span></label>
                                    <select name="division_id" id="modify-division" class="form-control">
                                        <option value="">Select Division</option>
                                        <?php foreach ($divisions as $division): ?>
                                            <option value="<?php echo (int)$division["division_id"]; ?>"
                                                <?php echo ((int)$tenderData["division_id"] === (int)$division["division_id"]) ? "selected" : ""; ?>>
                                                <?php echo htmlspecialchars($division["division_name"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6" id="modify-subdivision-wrapper">
                                <div class="form-group">
                                    <label>Sub Division <span class="text-danger">*</span></label>
                                    <select name="sub_division_id" id="modify-subdivision" class="form-control">
                                        <option value="">Select Sub Division</option>
                                        <?php foreach ($subDivisions as $subDivision): ?>
                                            <option value="<?php echo (int)$subDivision["id"]; ?>"
                                                <?php echo ((int)$tenderData["sub_division_id"] === (int)$subDivision["id"]) ? "selected" : ""; ?>>
                                                <?php echo htmlspecialchars($subDivision["subdivision"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Project Name</label>
                                    <input type="text" name="project_name" class="form-control"
                                           value="<?php echo htmlspecialchars((string)($tenderData["project_name"] ?? "")); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Project Location</label>
                                    <input type="text" name="project_location" class="form-control"
                                           value="<?php echo htmlspecialchars((string)($tenderData["project_location"] ?? "")); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="tm-actions">
                            <a href="sent-tender2.php" class="btn btn-light">
                                <i class="feather icon-x"></i> Cancel
                            </a>
                            <button type="submit" id="modify-submit" class="btn btn-success">
                                <i class="feather icon-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <script>
    $(document).ready(function () {
        var $form = $('#tender-modify-form');
        var $submit = $('#modify-submit');
        var originalButton = $submit.html();

        function isPrivateDepartment() {
            return $.trim($('#modify-department option:selected').text()).toLowerCase() === 'private';
        }

        function refreshDivisionVisibility() {
            var isPrivate = isPrivateDepartment();

            $('#modify-division-wrapper, #modify-subdivision-wrapper').toggle(!isPrivate);
            $('#modify-division, #modify-subdivision').prop('required', !isPrivate);

            if (isPrivate) {
                $('#modify-division, #modify-subdivision').val('');
            }
        }

        $('#modify-department').on('change', function () {
            var departmentId = $(this).val();

            $('#modify-section').html('<option value="">Loading...</option>');
            $('#modify-division').html('<option value="">Select Division</option>');
            $('#modify-subdivision').html('<option value="">Select Sub Division</option>');

            refreshDivisionVisibility();

            if (!departmentId) {
                $('#modify-section').html('<option value="">Select Section</option>');
                return;
            }

            $.ajax({
                url: window.location.pathname + window.location.search,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_sections',
                    department_id: departmentId
                }
            }).done(function (response) {
                var $section = $('#modify-section').empty().append('<option value="">Select Section</option>');

                if (response && response.status === 200) {
                    $.each(response.data || [], function (_, item) {
                        $section.append(
                            $('<option>', { value: item.section_id, text: item.section_name })
                        );
                    });
                }
            }).fail(function () {
                Swal.fire('Error', 'Unable to load sections.', 'error');
            });
        });

        $('#modify-section').on('change', function () {
            var sectionId = $(this).val();
            $('#modify-division').html('<option value="">Select Division</option>');
            $('#modify-subdivision').html('<option value="">Select Sub Division</option>');

            if (!sectionId || isPrivateDepartment()) return;

            $.ajax({
                url: window.location.pathname + window.location.search,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_divisions',
                    section_id: sectionId
                }
            }).done(function (response) {
                if (response && response.status === 200) {
                    $.each(response.data || [], function (_, item) {
                        $('#modify-division').append(
                            $('<option>', { value: item.division_id, text: item.division_name })
                        );
                    });
                }
            }).fail(function () {
                Swal.fire('Error', 'Unable to load divisions.', 'error');
            });
        });

        $('#modify-division').on('change', function () {
            var divisionId = $(this).val();
            $('#modify-subdivision').html('<option value="">Select Sub Division</option>');

            if (!divisionId) return;

            $.ajax({
                url: window.location.pathname + window.location.search,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_subdivisions',
                    division_id: divisionId
                }
            }).done(function (response) {
                if (response && response.status === 200) {
                    $.each(response.data || [], function (_, item) {
                        $('#modify-subdivision').append(
                            $('<option>', { value: item.id, text: item.subdivision })
                        );
                    });
                }
            }).fail(function () {
                Swal.fire('Error', 'Unable to load sub divisions.', 'error');
            });
        });

        refreshDivisionVisibility();

        $form.on('submit', function (event) {
            event.preventDefault();

            if ($form.data('submitting')) return;

            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }

            $form.data('submitting', true);
            $submit.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm"></span> Saving...');

            $.ajax({
                url: window.location.pathname + window.location.search,
                type: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                timeout: 30000
            }).done(function (response) {
                if (!response || Number(response.status) !== 200) {
                    Swal.fire(
                        'Unable to Update',
                        (response && (response.error || response.message)) || 'Unable to update tender details.',
                        'error'
                    );
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Updated',
                    text: response.message || 'Tender details updated successfully.',
                    confirmButtonColor: '#33cc33'
                }).then(function () {
                    window.location.href = 'sent-tender2.php';
                });
            }).fail(function (xhr, textStatus) {
                var message = textStatus === 'timeout'
                    ? 'The request timed out. Please try again.'
                    : 'Unable to update tender details.';

                if (xhr.responseJSON && xhr.responseJSON.error) {
                    message = xhr.responseJSON.error;
                }

                Swal.fire('Unable to Update', message, 'error');
            }).always(function () {
                $form.data('submitting', false);
                $submit.prop('disabled', false).html(originalButton);
            });
        });
    });
    </script>
</body>
</html>
