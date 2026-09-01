<?php

ini_set('display_errors', 0);
include("db/config.php");
session_start();
require "./utility/referenceCodeGenerator.php";

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}
$name = $_SESSION['login_user'];



$adminID = $_SESSION['login_user_id'];

// --- AJAX: Assign Task ---
if (isset($_POST['action']) && $_POST['action'] === 'assign_tender_task') {
    header('Content-Type: application/json');
    $tenderId = (int)($_POST['tender_request_id'] ?? 0);
    $employeeId = (int)($_POST['employee_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'Medium';
    $dueDate = $_POST['due_date'] ?? null;
    
    if (empty($dueDate)) $dueDate = null;

    if ($tenderId <= 0 || $employeeId <= 0 || empty($title)) {
        echo json_encode(['status' => 400, 'error' => 'Invalid input data.']);
        exit;
    }
    
    // Resolve adminID if not set in session
    if (empty($adminID) && !empty($_SESSION['login_user'])) {
        $stmtFind = $db->prepare("SELECT id FROM admin WHERE username = ? LIMIT 1");
        $stmtFind->bind_param('s', $_SESSION['login_user']);
        $stmtFind->execute();
        $adminID = $stmtFind->get_result()->fetch_assoc()['id'] ?? 0;
    }

    // Check permission
    $stmtAdminRole = $db->prepare("SELECT role_id FROM admin WHERE id = ?");
    $stmtAdminRole->bind_param('i', $adminID);
    $stmtAdminRole->execute();
    $roleId = $stmtAdminRole->get_result()->fetch_assoc()['role_id'] ?? null;

    $isAdminRole = false;
    $hasTaskPerm = false;

    if ($roleId) {
        $stmtRolesData = $db->prepare("SELECT role_name FROM roles WHERE role_id = ?");
        $stmtRolesData->bind_param('i', $roleId);
        $stmtRolesData->execute();
        $roleName = $stmtRolesData->get_result()->fetch_assoc()['role_name'] ?? '';
        
        if (in_array(strtolower($roleName), ['admin', 'super admin'], true)) {
            $isAdminRole = true;
        }
        
        $allowedPermissions = [
            'Task Management',
            'Add Task',
            'Sent Tenders',
            'Alot Sent Tender',
            'Edit Sent Tender',
            'Sent Tender View'
        ];
        $placeholders = implode(',', array_fill(0, count($allowedPermissions), '?'));
        $types = 'i' . str_repeat('s', count($allowedPermissions));
        $params = array_merge([$roleId], $allowedPermissions);

        $stmtPriv = $db->prepare("
            SELECT p.permission_name 
            FROM permissions p
            JOIN role_permissions rp ON p.permission_id = rp.permission_id
            WHERE rp.role_id = ? AND p.permission_name IN ($placeholders)
        ");
        $stmtPriv->bind_param($types, ...$params);
        $stmtPriv->execute();
        if ($stmtPriv->get_result()->fetch_assoc()) {
            $hasTaskPerm = true;
        }
    }

    if (!$isAdminRole && !$hasTaskPerm) {
        echo json_encode(['status' => 403, 'error' => 'Permission denied. You do not have rights to assign tasks.']);
        exit;
    }
    
    // Check if employee is active
    $stmtEmp = $db->prepare("SELECT username FROM admin WHERE id = ? AND status = 1");
    $stmtEmp->bind_param('i', $employeeId);
    $stmtEmp->execute();
    $empRes = $stmtEmp->get_result()->fetch_assoc();
    if (!$empRes) {
        echo json_encode(['status' => 400, 'error' => 'Selected employee does not exist or is inactive.']);
        exit;
    }
    
    // Check duplicate
    $stmtDup = $db->prepare("SELECT id FROM tasks WHERE tender_request_id = ? AND assigned_to = ?");
    $stmtDup->bind_param('ii', $tenderId, $employeeId);
    $stmtDup->execute();
    if ($stmtDup->get_result()->fetch_assoc()) {
        echo json_encode(['status' => 400, 'error' => 'This tender request is already assigned to this employee.']);
        exit;
    }
    
    // Insert task
    $taskType = 'Tender/Query';
    $status = 'Pending';
    $startDate = date('Y-m-d');
    
    $stmtIns = $db->prepare("INSERT INTO tasks (title, description, task_type, tender_request_id, created_by, assigned_to, priority, status, start_date, due_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmtIns->bind_param('sssiisssss', $title, $description, $taskType, $tenderId, $adminID, $employeeId, $priority, $status, $startDate, $dueDate);
    
    try {
        if ($stmtIns->execute()) {
            $newTaskId = $stmtIns->insert_id;
            
            // Notify
            require_once __DIR__ . '/service/NotificationService.php';
            $ns = new NotificationService($db);
            $ns->notifyTaskAssigned($newTaskId, $employeeId, $title);
            
            echo json_encode(['status' => 200, 'message' => 'Task assigned successfully']);
        } else {
            echo json_encode(['status' => 500, 'error' => 'Database error during task creation.']);
        }
    } catch (\mysqli_sql_exception $e) {
        echo json_encode(['status' => 500, 'error' => 'Invalid Tender Request ID or Database Error']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['refCode'])) {

    // Use a transaction to ensure atomicity
    try {
        $prefix = "REF";
        $response = referenceCode($db, $prefix);
        $refNumber = $response['data'];
        echo json_encode([
            "status" => 201,
            "data" => $refNumber
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode([
            "status" => 500,
            "error" => $e->getMessage()
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['tender_id']) && isset($_POST['reference_code'])) {
    try {
        $tenderId = $_POST['tender_id'];
        $referenceCode = $_POST['reference_code'];

        $db->begin_transaction();

        $stmtExistingTenderId = $db->prepare("SELECT * FROM user_tender_requests WHERE id = ?");
        $stmtExistingTenderId->bind_param("i", $tenderId);
        $stmtExistingTenderId->execute();

        $result = $stmtExistingTenderId->get_result();

        if ($result->num_rows == 0) {  // Fixed: should be == 0, not < 0
            echo json_encode([
                "status" => 400,
                "error" => "Tender id is invalid",
            ]);
            $db->rollback(); // Add rollback
            exit;
        }

        // Fixed: bind parameters and execute the update statement
        $stmtUpdateReference = $db->prepare("UPDATE user_tender_requests SET reference_code = ? WHERE id = ?");
        $stmtUpdateReference->bind_param("si", $referenceCode, $tenderId); // Fixed: added bind_param
        $stmtUpdateReference->execute(); // Fixed: added execute

        $db->commit(); // Commit the transaction

        echo json_encode([
            "status" => 200,
            "message" => "Reference code updated successfully",
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

if (
    $_SERVER['REQUEST_METHOD'] == 'GET' &&
    isset($_GET['department-search']) ||
    isset($_GET['section-search']) ||
    isset($_GET['division-search']) ||
    isset($_GET['sub-division-search']) ||
    isset($_GET['firm']) ||
    isset($_GET['state']) ||
    isset($_GET['city'])

) {    // Initialize $conditions as an empty array
    $conditions = [];

    // Sanitize inputs
    $departmentId = filter_input(INPUT_GET, 'department-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $sectionId = filter_input(INPUT_GET, 'section-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $divisionId = filter_input(INPUT_GET, 'division-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $subDivisionId = filter_input(INPUT_GET, 'sub-division-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $firm = filter_input(INPUT_GET, 'firm', FILTER_SANITIZE_SPECIAL_CHARS);
    $state = filter_input(INPUT_GET, 'state', FILTER_SANITIZE_SPECIAL_CHARS);
    $city = filter_input(INPUT_GET, 'city', FILTER_SANITIZE_SPECIAL_CHARS);



    // Add conditions only if a valid filter is selected
    if ($departmentId && $departmentId !== '0') {
        $conditions[] = "ur.department_id = '$departmentId'";
    }
    if ($sectionId && $sectionId !== '0') {
        $conditions[] = "ur.section_id = '$sectionId'";
    }
    if ($divisionId && $divisionId !== '0') {
        $conditions[] = "ur.division_id = '$divisionId'";
    }
    if ($subDivisionId && $subDivisionId !== '0') {
        $conditions[] = "ur.sub_division_id = '$subDivisionId'";
    }

    if ($firm && $firm !== '0') {
        $conditions[] = "m.firm_name = '$firm'";
    }

    if ($state && $state !== '0') {
        $conditions[] = "st.state_code = '$state'";
    }

    if ($city && $city !== '0') {
        $conditions[] = "ct.city_id = '$city'";
    }

    // Construct the WHERE clause
    $whereClause = empty($conditions) ? "" : "WHERE " . implode(' AND ', $conditions);

    $queryMain = "
    SELECT 
        ROW_NUMBER() OVER (ORDER BY ur.created_at) AS sno,
        ur.id as t_id, 
        m.name, 
        m.member_id, 
        m.firm_name, 
        m.mobile, 
        m.email_id, 
        department.department_name, 
        ur.due_date, 
        ur.file_name, 
        ur.tenderID, 
        ur.created_at AS tender_created_at, 
        ur.file_name2,
        ur.additional_files,
        ur.reference_code,
        ur.tentative_cost,
        ur.tender_no, 
        s.section_name, 
        dv.division_name, 
        sd.subdivision,
        st.state_name, 
        ct.city_name  
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
    LEFT JOIN
        sub_division sd ON ur.sub_division_id = sd.id
 LEFT JOIN
        state st ON CONVERT(m.state_code USING utf8mb4) = CONVERT(st.state_code USING utf8mb4)
    LEFT JOIN   
        cities ct ON CAST(m.city_state AS UNSIGNED) = ct.city_id    INNER JOIN 
        (
            SELECT MIN(id) AS min_id
            FROM user_tender_requests sent
            WHERE sent.status = 'Sent' AND sent.delete_tender = '0'
            AND NOT EXISTS (
                SELECT 1 FROM user_tender_requests a
                WHERE a.tenderID = sent.tenderID
                AND a.status = 'Allotted'
                AND a.delete_tender = '0'
            )
            GROUP BY sent.tenderID
        ) AS unique_sent_only ON ur.id = unique_sent_only.min_id
    $whereClause
    ORDER BY ur.created_at ASC;
";
    // Execute the query
    $resultMain = mysqli_query($db, $queryMain);
    if (!$resultMain) {
        die("Query Error: " . mysqli_error($db));
    }
} else {
    // Initialize the row number variable
    mysqli_query($db, "SET @row_number = 0;");
    $queryMain = "
    SELECT 
        ROW_NUMBER() OVER (ORDER BY ur.created_at) AS sno,
        ur.id as t_id, 
        m.name, 
        m.member_id, 
        m.firm_name, 
        m.mobile, 
        m.email_id, 
        m.state_code,  -- Include state code from members table
        m.city_state,  -- Include city state from members table
        department.department_name, 
        ur.due_date, 
        ur.file_name, 
        ur.tenderID, 
        ur.created_at AS tender_created_at, 
        ur.file_name2,
        ur.additional_files,
        ur.reference_code,
        ur.tentative_cost,
        ur.tender_no, 
        s.section_name, 
        dv.division_name, 
        sd.subdivision,
        st.state_name,  -- Select state name
        ct.city_name   -- Select city name
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
    LEFT JOIN
        sub_division sd ON ur.sub_division_id = sd.id
    LEFT JOIN
        state st ON CONVERT(m.state_code USING utf8mb4) = CONVERT(st.state_code USING utf8mb4)
    LEFT JOIN   
        cities ct ON CAST(m.city_state AS UNSIGNED) = ct.city_id
    INNER JOIN 
        (
            SELECT MIN(id) AS min_id
            FROM user_tender_requests sent
            WHERE sent.status = 'Sent' AND sent.delete_tender = '0'
            AND NOT EXISTS (
                SELECT 1 FROM user_tender_requests a
                WHERE a.tenderID = sent.tenderID
                AND a.status = 'Allotted'
                AND a.delete_tender = '0'
            )
            GROUP BY sent.tenderID
        ) AS unique_sent_only ON ur.id = unique_sent_only.min_id
    $whereClause
    ORDER BY ur.created_at ASC
";

    $resultMain = mysqli_query($db, $queryMain);
}



// Fetch unique, non-empty cities only
$stmtFetchCities = $db->prepare("SELECT * FROM cities ");
$stmtFetchCities->execute();
$cities = $stmtFetchCities->get_result()->fetch_all(MYSQLI_ASSOC);



// Fetch unique, non-empty cities only
$stmtFetchStates = $db->prepare("SELECT * FROM state ");
$stmtFetchStates->execute();
$states = $stmtFetchStates->get_result()->fetch_all(MYSQLI_ASSOC);

// firms
$stmtFetchFirm = $db->prepare("SELECT firm_name FROM members");
$stmtFetchFirm->execute();
$firms = $stmtFetchFirm->get_result()->fetch_all(MYSQLI_ASSOC);

// Remove duplicates and empty firm names
$unique_firms = [];
$seen_firms = [];

foreach ($firms as $firm) {
    $firm_name = trim($firm['firm_name']); // Remove whitespace

    // Check if firm_name is not empty and not already seen
    if (!empty($firm_name) && !in_array($firm_name, $seen_firms)) {
        $unique_firms[] = ['firm_name' => $firm_name];
        $seen_firms[] = $firm_name;
    }
}

$firms = $unique_firms;

//fecth Department
$queryDepartment = "SELECT * FROM department WHERE status = 1";
$resultDepartment = mysqli_query($db, $queryDepartment);
$departments = [];

if ($resultDepartment) {
    while ($row = mysqli_fetch_assoc($resultDepartment)) {
        $departments[] = $row;
    }
}

//fecth Sections

$querySection = "SELECT * FROM section WHERE status = 1";
$resultSection = mysqli_query($db, $querySection);
$sections = [];

if ($resultSection) {
    while ($row = mysqli_fetch_assoc($resultSection)) {
        $sections[] = $row;
    }
}

$query = "SELECT sc.section_name, dv.division_name, sdv.subdivision 
          FROM section sc 
          INNER JOIN division dv ON sc.section_id = dv.section_id 
          INNER JOIN sub_division sdv ON dv.division_id = sdv.division_id 
          WHERE sc.status = 1 
          ORDER BY sc.section_name, dv.division_name";

$result = mysqli_query($db, $query);


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
        $stmtFetchCities = $db->prepare("SELECT * FROM cities WHERE state_code = ?");
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


try {
    $stmtFetchTenderSent = $db->prepare("  SELECT 
        ROW_NUMBER() OVER (ORDER BY ur.created_at) AS sno,
        ur.id as t_id, 
		COUNT(ur.id) OVER() as COUNT,  -- Window function instead of aggregate
        m.name, 
        m.member_id, 
        m.firm_name, 
        m.mobile, 
        m.email_id, 
        department.department_name, 
        ur.due_date, 
        ur.file_name, 
        ur.tenderID, 
        ur.created_at AS tender_created_at, 
        ur.file_name2,
        ur.reference_code,
        ur.tentative_cost,
        ur.tender_no, 
        s.section_name, 
        dv.division_name, 
        sd.subdivision,
        st.state_name, 
        ct.city_name  
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
    LEFT JOIN
        sub_division sd ON ur.sub_division_id = sd.id
 LEFT JOIN
        state st ON CONVERT(m.state_code USING utf8mb4) = CONVERT(st.state_code USING utf8mb4)
    LEFT JOIN   
        cities ct ON CAST(m.city_state AS UNSIGNED) = ct.city_id    INNER JOIN 
        (
            SELECT MIN(id) AS min_id
            FROM user_tender_requests sent
            WHERE sent.status = 'Sent' AND sent.delete_tender = '0'
            AND NOT EXISTS (
                SELECT 1 FROM user_tender_requests a
                WHERE a.tenderID = sent.tenderID
                AND a.status = 'Allotted'
                AND a.delete_tender = '0'
            )
            GROUP BY sent.tenderID
        ) AS unique_sent_only ON ur.id = unique_sent_only.min_id
    ORDER BY ur.created_at ASC");
    $stmtFetchTenderSent->execute();
    $tenderSentCount = $stmtFetchTenderSent->get_result()->fetch_array(MYSQLI_ASSOC);
} catch (\Throwable $th) {
    //throw $th;
}
?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Sent Tender </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">



    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/plugins/fixedHeader.bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ==========================================================
           Sent Tender page — scoped UI modernization.
           All rules are page-scoped (.sent-tender-page) so the
           rest of the admin panel is unaffected.
           ========================================================== */
        .sent-tender-page {
            padding: 16px;
        }

        /* ---------- shared card base ---------- */
        .sent-tender-page .card {
            border: 0;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .05), 0 1px 3px rgba(16, 24, 40, .08);
            margin-bottom: 16px;
        }

        /* ---------- page header ---------- */
        .sent-tender-page .st-page-header {
            padding: 12px 20px;
            margin-bottom: 16px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .05), 0 1px 3px rgba(16, 24, 40, .08);
        }

        .sent-tender-page .st-page-header .page-block {
            padding: 0;
        }

        .sent-tender-page .st-page-header .col-md-12 {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .sent-tender-page .st-page-header .page-header-title {
            margin: 0;
        }

        .sent-tender-page .st-page-header .page-header-title h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
        }

        .sent-tender-page .st-page-header .breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            padding: 0;
            background: transparent;
            font-size: 12.5px;
        }

        .sent-tender-page .st-page-header .breadcrumb a {
            color: #64748b;
        }

        .sent-tender-page .st-page-header .breadcrumb .breadcrumb-item.active {
            color: #94a3b8;
        }

        /* ---------- KPI card ---------- */
        .sent-tender-page .st-kpi-card .st-kpi-body {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            text-align: left;
        }

        .sent-tender-page .st-kpi-icon {
            flex: 0 0 auto;
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .sent-tender-page .st-kpi-meta {
            display: flex;
            flex-direction: column;
        }

        .sent-tender-page .st-kpi-label {
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
        }

        .sent-tender-page .st-kpi-value {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        /* ---------- filter panel ---------- */
        .sent-tender-page .st-filter-panel {
            padding: 16px;
            margin-bottom: 14px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .sent-tender-page .st-filter-panel .st-filter-body {
            padding: 0;
            text-align: left;
        }

        .sent-tender-page .st-filter-head {
            margin-bottom: 16px;
        }

        .sent-tender-page .st-filter-title {
            margin: 0 0 2px;
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
        }

        .sent-tender-page .st-filter-sub {
            margin: 0;
            font-size: 13px;
            color: #94a3b8;
        }

        .sent-tender-page .st-filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .sent-tender-page .st-filter-field label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
        }

        .sent-tender-page .st-filter-grid .select2-container {
            width: 100% !important;
        }

        .sent-tender-page .st-filter-grid .select2-container .select2-selection--single {
            height: 42px;
            border: 1px solid #d0d5dd;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }

        .sent-tender-page .st-filter-grid .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
            padding-left: 12px;
            padding-right: 28px;
            font-size: 14px;
            color: #1e293b;
        }

        .sent-tender-page .st-filter-grid .select2-container .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 8px;
        }

        .sent-tender-page .st-filter-grid .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8;
        }

        .sent-tender-page .st-filter-grid .select2-container--default.select2-container--focus .select2-selection--single,
        .sent-tender-page .st-filter-grid .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
        }

        .sent-tender-page .select2-container--default .select2-dropdown {
            border-color: #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(16, 24, 40, .12);
        }

        .sent-tender-page .st-filter-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 20px;
        }

        .sent-tender-page .st-filter-actions .btn {
            height: 40px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .sent-tender-page .st-filter-actions .btn-outline-secondary {
            background: #fff;
            border: 1px solid #d0d5dd;
            color: #475569;
        }

        .sent-tender-page .st-filter-actions .btn-outline-secondary:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        /* ---------- table toolbar ---------- */
        .sent-tender-page .st-table-card .st-table-body {
            padding: 20px;
            text-align: left;
        }

        .sent-tender-page .st-table-body .alert {
            margin-bottom: 16px;
            border-radius: 8px;
        }

        .sent-tender-page .st-table-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 12px 16px;
            margin-bottom: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .sent-tender-page .st-toolbar-left,
        .sent-tender-page .st-toolbar-right {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .sent-tender-page .st-toolbar-left {
            flex: 1 1 auto;
        }

        .sent-tender-page .st-toolbar-right {
            flex: 0 0 auto;
            margin-left: auto;
        }

        .sent-tender-page .st-table-title {
            margin: 0 8px 0 0;
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
        }

        .sent-tender-page .st-table-toolbar .btn {
            height: 36px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
        }

        .sent-tender-page .st-table-toolbar .btn-danger {
            background: #dc2626;
            border-color: #dc2626;
        }

        .sent-tender-page .st-table-toolbar .btn-danger:hover {
            background: #b91c1c;
            border-color: #b91c1c;
        }

        .sent-tender-page .st-toolbar-left .buttons-excel,
        .sent-tender-page .st-toolbar-left .buttons-csv,
        .sent-tender-page .st-toolbar-left .buttons-print,
        .sent-tender-page .st-toolbar-right .btn {
            background: #fff;
            border: 1px solid #d0d5dd;
            color: #475569;
        }

        .sent-tender-page .st-toolbar-left .buttons-excel:hover,
        .sent-tender-page .st-toolbar-left .buttons-csv:hover,
        .sent-tender-page .st-toolbar-left .buttons-print:hover,
        .sent-tender-page .st-toolbar-right .btn:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .sent-tender-page .st-toolbar-left .buttons-excel i {
            color: #16a34a;
        }

        .sent-tender-page .st-toolbar-left .buttons-csv i {
            color: #0891b2;
        }

        .sent-tender-page .st-toolbar-left .buttons-print i {
            color: #64748b;
        }

        /* ---------- DataTable wrapper controls ---------- */
        .sent-tender-page .st-table-card .dataTables_wrapper > .row:first-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: nowrap;
            gap: 8px;
            margin: 0;
        }

        .sent-tender-page .st-table-card .dataTables_wrapper > .row:first-child > div {
            width: auto !important;
            padding: 0;
            max-width: none;
        }

        .sent-tender-page .st-table-card .dataTables_wrapper > .row:first-child > div:first-child {
            flex: 0 0 auto;
        }

        .sent-tender-page .st-table-card .dataTables_wrapper > .row:first-child > div:last-child {
            flex: 0 0 auto;
            margin-left: auto;
        }

        .sent-tender-page .st-table-card .dataTables_length,
        .sent-tender-page .st-table-card .dataTables_filter {
            padding: 4px 0 12px;
        }

        .sent-tender-page .st-table-card .dataTables_length label,
        .sent-tender-page .st-table-card .dataTables_filter label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            white-space: nowrap;
        }

        .sent-tender-page .st-table-card .dataTables_filter {
            text-align: right;
        }

        .sent-tender-page .st-table-card .dataTables_length select,
        .sent-tender-page .st-table-card .dataTables_filter input {
            height: 34px;
            border: 1px solid #d0d5dd;
            border-radius: 6px;
            background: #fff;
            font-size: 13px;
            color: #1e293b;
        }

        .sent-tender-page .st-table-card .dataTables_length select {
            padding: 0 8px;
        }

        .sent-tender-page .st-table-card .dataTables_filter input {
            padding: 0 10px;
            width: clamp(140px, 24vw, 260px);
            margin-left: 0;
        }

        .sent-tender-page .st-table-card .dataTables_info {
            padding-top: 12px;
            font-size: 13px;
            color: #64748b;
        }

        .sent-tender-page .st-table-card .dataTables_paginate {
            padding-top: 8px;
        }

        /* ---------- table ---------- */
        .sent-tender-page .st-table-card .table {
            margin-bottom: 0;
            border-color: #e2e8f0;
        }

        .sent-tender-page .st-table-card .table thead th {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 12px;
            font-size: 12.5px;
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
            vertical-align: middle;
        }

        .sent-tender-page .st-table-card .table tbody td {
            padding: 8px 12px;
            font-size: 13px;
            color: #334155;
            vertical-align: middle;
            white-space: nowrap;
        }

        .sent-tender-page .st-table-card .table tbody tr:hover td {
            background-color: #f1f5f9;
        }

        .sent-tender-page .st-table-card .table .tender_id {
            color: #2563eb;
            font-weight: 600;
        }

        /* ---------- action dropdown ---------- */
        .sent-tender-page .st-table-card .dropdown .st-action-btn {
            height: 32px;
            width: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid #d0d5dd;
            background: #fff;
            color: #475569;
        }

        .sent-tender-page .st-table-card .dropdown .st-action-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .sent-tender-page .st-table-card .dropdown-menu {
            min-width: 170px;
            padding: 6px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(16, 24, 40, .12);
        }

        .sent-tender-page .st-table-card .dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 6px;
            font-size: 13.5px;
            color: #334155;
        }

        .sent-tender-page .st-table-card .dropdown-item:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .sent-tender-page .st-table-card .dropdown-item i {
            width: 16px;
            text-align: center;
            color: #64748b;
        }

        /* ──────────────────────────────────────────────
           Modern scrollable table + custom scrollbars
           ────────────────────────────────────────────── */
        .dataTables_scrollBody {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .dataTables_scrollBody::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .dataTables_scrollBody::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .dataTables_scrollBody::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
            border: 1px solid transparent;
            background-clip: padding-box;
        }

        .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .dataTables_scrollBody::-webkit-scrollbar-corner {
            background: #f1f1f1;
        }

        .dataTables_scrollBody {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        .dataTables_scroll {
            max-height: 70vh;
        }

        .dt-responsive.table-responsive {
            overflow: visible !important;
        }

        /* ---------- responsive ---------- */
        @media (max-width: 991.98px) {
            .sent-tender-page .st-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .sent-tender-page .st-filter-grid {
                grid-template-columns: 1fr;
            }

            .sent-tender-page .st-filter-actions {
                justify-content: stretch;
            }

            .sent-tender-page .st-filter-actions .btn {
                flex: 1 1 auto;
                justify-content: center;
            }

            .sent-tender-page .st-table-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .sent-tender-page .st-toolbar-left,
            .sent-tender-page .st-toolbar-right {
                width: 100%;
            }

            .sent-tender-page .st-toolbar-right {
                margin-left: 0;
                justify-content: flex-end;
            }
        }

        /* =========================================================
           FINAL TABLE THEME FIX
           ========================================================= */
        
        /* Table header */
        .sent-tender-page .st-table-card .table thead th {
            background-color: #33cc33 !important;
            color: #ffffff !important;
            border-color: #33cc33 !important;
        }
        
        /* First header cell */
        .sent-tender-page .dataTables_scrollHead .table thead th:first-child {
            background-color: #33cc33 !important;
            color: #ffffff !important;
            border-color: #33cc33 !important;
        }
        
        /* Tender ID links */
        .sent-tender-page .st-table-card .table tbody .tender_id,
        .sent-tender-page .st-table-card .table tbody a.tender_id {
            color: #33cc33 !important;
            font-weight: 600;
            text-decoration: none;
        }
        
        .sent-tender-page .st-table-card .table tbody .tender_id:hover,
        .sent-tender-page .st-table-card .table tbody a.tender_id:hover {
            color: #28a428 !important;
            text-decoration: underline;
        }
        
        
        /* =========================================================
           DATATABLE INFO
           Keep "Showing 1 to 100..." OUTSIDE the scrollable table
           ========================================================= */
        
        .sent-tender-page .st-table-card .dataTables_info {
            position: relative !important;
            z-index: 10;
            display: block !important;
            width: 100% !important;
            padding: 12px 0 4px !important;
            margin: 0 !important;
            background: #fff !important;
            color: #64748b !important;
            font-size: 13px;
            line-height: 20px;
            clear: both;
        }
        
        /* DataTables bottom row */
        .sent-tender-page .st-table-card .dataTables_wrapper > .row:last-child {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            margin: 0 !important;
            padding-top: 8px;
            background: #fff;
        }
        
        /* Prevent the info/pagination area from being clipped */
        .sent-tender-page .st-table-card .dataTables_wrapper {
            overflow: visible !important;
        }
        
        /* Scroll only the actual table body */
        .sent-tender-page .st-table-card .dataTables_scroll {
            overflow: visible !important;
        }
        
        .sent-tender-page .st-table-card .dataTables_scrollBody {
            overflow-x: auto !important;
            overflow-y: auto !important;
        }

        /* ==========================================
           KPI + BREADCRUMB COMBINED
           ========================================== */
        
        .sent-tender-page .st-kpi-card {
            /* Sent Tender — Yellow (matches dashboard bg-c-yellow) */
            background: linear-gradient(45deg, #FFB64D, #ffcb80) !important;
            margin-bottom: 14px !important;
            overflow: hidden;
        }

        /* KPI */
        .sent-tender-page .st-kpi-card .st-kpi-body {
            padding: 14px 20px !important;
            margin: 0;
        }

        .sent-tender-page .st-kpi-card .st-kpi-icon {
            background: rgba(255, 255, 255, .22);
            color: #ffffff;
        }

        .sent-tender-page .st-kpi-card .st-kpi-value {
            color: #ffffff;
        }

        /* Breadcrumb section */
        .sent-tender-page .st-kpi-breadcrumb {
            padding: 0 20px 12px;
        }
        
        /* Breadcrumb itself */
        .sent-tender-page .st-kpi-breadcrumb .breadcrumb {
            display: flex;
            align-items: center;
            gap: 5px;
        
            margin: 0;
            padding: 0;
        
            background: transparent;
            font-size: 12px;
        }
        
        /* Home link */
        .sent-tender-page .st-kpi-breadcrumb .breadcrumb a {
            color: #ffffff !important;
            text-decoration: none;
        }

        .sent-tender-page .st-kpi-breadcrumb .breadcrumb a:hover {
            color: rgba(255, 255, 255, .88) !important;
        }

        /* Current page */
        .sent-tender-page .st-kpi-breadcrumb .breadcrumb-item.active {
            color: rgba(255, 255, 255, .9);
        }

        /* Small separator */
        .sent-tender-page .st-kpi-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255, 255, 255, .65);
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
        <div class="pcoded-content sent-tender-page">
            <!--<div class="page-header st-page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h5>Sent Tender</h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="index.php"><i class="feather icon-home"></i> Home</a>
                                </li>
                                <li class="breadcrumb-item active">Sent Tender</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>-->
            <div class="row">
                <div class="col-12">
            
                    <div class="card st-kpi-card">
            
                        <!-- KPI -->
                        <div class="card-body st-kpi-body">
                            <div class="st-kpi-icon">
                                <i class="feather icon-message-square"></i>
                            </div>
            
                            <div class="st-kpi-meta">
                                <!--<span class="st-kpi-label">Sent Tenders</span>-->

                                <span class="st-kpi-value" id="total">
                                    <?php
                                    $sentTendersCountValue = 0;
            
                                    if (
                                        $isAdmin ||
                                        hasPermission(
                                            'Sent Tenders Count',
                                            $privileges,
                                            $roleData['role_name']
                                        )
                                    ) {
                                        $sentTendersCountValue =
                                            $tenderSentCount['COUNT'] ?? 0;
                                    }
            
                                    echo $sentTendersCountValue;
                                    ?>
                                </span>
                            </div>
                        </div>
            
                        <!-- Navigation -->
                        <div class="st-kpi-breadcrumb">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="index.php">
                                        <i class="feather icon-home"></i> Home
                                    </a>
                                </li>
            
                                <li class="breadcrumb-item active">
                                    Sent Tender
                                </li>
                            </ul>
                        </div>
            
                    </div>
            
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card st-table-card">
                        <div class="card-body st-table-body">

                            <?php
                            if (isset($_GET['status'])) {
                                $st = $_GET['status'];
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
                            }
                            ?>

                            <div class="st-table-toolbar">
                                <div class="st-toolbar-left">
                                    <h6 class="st-table-title">Sent Tenders</h6>
                                    <?php
                                    if ($isAdmin || hasPermission('Bulk Delete Sent Tender', $privileges, $roleData['role_name'])) {
                                        echo "<a href='javascript:void(0);' id='recycle_records' class='btn btn-danger'>
                                        <i class='feather icon-trash'></i> Move to Bin
                                        </a>";
                                    }
                                    ?>
                                    <?php if ($isAdmin || hasPermission('Sent Tender Excel', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn buttons-excel" tabindex="0" aria-controls="basic-btn2"
                                            type="button" onclick="exportTableToExcel()" title="Export to Excel">
                                            <span><i class="fas fa-file-excel"></i> Excel</span>
                                        </button>
                                    <?php } ?>
                                    <?php if ($isAdmin || hasPermission('Sent Tender CSV', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn buttons-csv" tabindex="0" aria-controls="basic-btn2"
                                            type="button" onclick="exportTableToCSV()" title="Export to CSV">
                                            <span><i class="fas fa-file-csv"></i> CSV</span>
                                        </button>
                                    <?php } ?>
                                    <?php if ($isAdmin || hasPermission('Sent Tender Print', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn buttons-print" tabindex="0" onclick="printTable()"
                                            aria-controls="basic-btn2" type="button" title="Print">
                                            <span><i class="fas fa-print"></i> Print</span>
                                        </button>
                                    <?php } ?>
                                </div>
                                <?php if ($isAdmin || hasPermission('Sent Tenders Filter', $privileges, $roleData['role_name'])) { ?>
                                    <div class="st-toolbar-right">
                                        <button class="btn" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#sentTenderFilters" aria-expanded="false"
                                            aria-controls="sentTenderFilters" title="Filters">
                                            <i class="feather icon-filter"></i> Filters
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>

                            <?php if ($isAdmin || hasPermission('Sent Tenders Filter', $privileges, $roleData['role_name'])) { ?>
                                <div class="collapse st-filter-collapse" id="sentTenderFilters">
                                    <div class="st-filter-panel">
                                        <div class="st-filter-body">
                                            <div class="st-filter-head">
                                                <h6 class="st-filter-title">Filters</h6>
                                                <p class="st-filter-sub">Narrow down sent tenders</p>
                                            </div>
                                            <form method="get" id="filterForm">
                                                <div class="st-filter-grid">
                                                    <div class="st-filter-field">
                                                        <label for="department-search">Department</label>
                                                        <select class="form-control" name="department-search"
                                                            id="department-search">
                                                            <option value="0">All</option>
                                                            <?php foreach ($departments as $department) { ?>
                                                                <option value="<?php echo $department['department_id']; ?>" <?php echo isset($_GET['department-search']) && $_GET['department-search'] == $department['department_id'] ? 'selected' : ''; ?>>
                                                                    <?php echo $department['department_name']; ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="st-filter-field">
                                                        <label for="section-search">Section</label>
                                                        <select class="form-control" name="section-search" id="section-search">
                                                            <option value="0">All</option>
                                                            <?php foreach ($sections as $section) {
                                                                $selectedSection = (isset($_GET['section-search']) && urldecode($_GET['section-search']) == $section['section_id']) ? 'selected' : '';

                                                                ?>
                                                                <option <?= $selectedSection ?>
                                                                    value="<?php echo $section['section_id']; ?>">
                                                                    <?php echo $section['section_name']; ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="st-filter-field">
                                                        <label for="division-search">Division</label>
                                                        <select class="form-control" name="division-search" id="division-search">
                                                            <option value="0">All</option>
                                                            <?php foreach ($divisions as $division) { ?>
                                                                <option value="<?php echo $division['division_id']; ?>" <?php echo isset($_GET['division-search']) && $_GET['division-search'] == $division['division_id'] ? 'selected' : ''; ?>>
                                                                    <?php echo $division['division_name']; ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="st-filter-field">
                                                        <label for="sub-division-search">Sub Division</label>
                                                        <select class="form-control" name="sub-division-search"
                                                            id="sub-division-search">
                                                            <option value="0">All</option>
                                                        </select>
                                                    </div>
                                                    <div class="st-filter-field">
                                                        <label for="firm">Firm</label>
                                                        <select class="form-control select-firm" name="firm" id="firm">
                                                            <option value="0">All</option>
                                                            <?php foreach ($firms as $firm) {
                                                                $selectedFirm = (isset($_GET['firm']) && urldecode($_GET['firm']) == $firm['firm_name']) ? 'selected' : '';
                                                                ?>
                                                                <option value="<?= htmlspecialchars($firm['firm_name']) ?>"
                                                                    <?= $selectedFirm ?>>
                                                                    <?= htmlspecialchars($firm['firm_name']) ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="st-filter-field">
                                                        <label for="state">State</label>
                                                        <select class="form-control select-state" name="state" id="state">
                                                            <option value="0">All</option>
                                                            <?php foreach ($states as $state) {
                                                                $selectedState = (isset($_GET['state']) && urldecode($_GET['state']) == $state['state_code']) ? 'selected' : '';
                                                                ?>
                                                                <option value="<?= $state['state_code'] ?>" <?= $selectedState ?>>
                                                                    <?= $state['state_name'] ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="st-filter-field">
                                                        <label for="city">City</label>
                                                        <select class="form-control select-city" name="city" id="city">
                                                            <option value="0">All</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="st-filter-actions">
                                                    <a href="sent-tender2.php" class="btn btn-outline-secondary"
                                                        id="filterResetButton">
                                                        <i class="feather icon-refresh-ccw"></i> Reset
                                                    </a>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="feather icon-search"></i> Search
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="dt-responsive">

                                <table id="basic-btn2" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>
                                                <label class="checkboxs">
                                                    <input type="checkbox" id="select-all">
                                                    <span class="checkmarks"></span>
                                                </label> SNO
                                            </th>
                                            <th>Tender ID</th>
                                            <th>Tender No</th>
                                            <th>Department</th>
                                            <th>Section</th>
                                            <th>Division</th>
                                            <th>Sub-Division</th>
                                            <th>Tentative Cost</th>
                                            <th>REF.Code</th>
                                            <th>Due Date</th>
                                            <th>Add Date</th>
                                            <th>Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 1;
                                        while ($row = mysqli_fetch_assoc($resultMain)) {
                                            $res = base64_encode($row['t_id']);

                                            $dueDate = new DateTime($row['due_date']);
                                            $formattedDueDate = $dueDate->format('d-m-Y');

                                            $createdDate = new DateTime($row['tender_created_at']);
                                            $formattedCreatedDate = $createdDate->format('d-m-Y H:i:s');
                                            ?>
                                            <tr class="record">
                                                <td>
                                                    <div class='custom-control custom-checkbox'>
                                                        <input type='checkbox' class='custom-control-input request_checkbox'
                                                            id='customCheck<?= $row['sno'] ?>'
                                                            data-request-id='<?= htmlspecialchars($row['t_id']) ?>'>
                                                        <label class='custom-control-label'
                                                            for='customCheck<?= $row['sno'] ?>'>
                                                            <?= htmlspecialchars($row['sno']) ?>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong>
                                                        <?php if ($isAdmin || hasPermission('Sent Tender View', $privileges, $roleData['role_name'])) { ?>
                                                            <a class='tender_id' target='_blank'
                                                                href='sent-tender3.php?tender_id=<?= base64_encode($row['tenderID']) ?>'>
                                                                <?= htmlspecialchars($row['tenderID']) ?>
                                                            </a>
                                                        <?php } else { ?>
                                                            <?= htmlspecialchars($row['tenderID']) ?>
                                                        <?php } ?>
                                                    </strong>
                                                </td>
                                                <td><?= htmlspecialchars($row['tender_no']) ?></td>
                                                <td><?= htmlspecialchars($row['department_name']) ?></td>
                                                <td><?= htmlspecialchars($row['section_name']) ?></td>
                                                <td><?= htmlspecialchars($row['division_name']) ?></td>
                                                <td><?= htmlspecialchars($row['subdivision']) ?></td>
                                                <td><?= htmlspecialchars($row['tentative_cost']) ?></td>
                                                <td><?= htmlspecialchars($row['reference_code']) ?></td>
                                                <td><?= htmlspecialchars($formattedDueDate) ?></td>
                                                <td><?= htmlspecialchars($formattedCreatedDate) ?></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary st-action-btn" type="button"
                                                            id="actionMenu<?php echo $row['t_id']; ?>"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="feather icon-more-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu"
                                                            aria-labelledby="actionMenu<?php echo $row['t_id']; ?>">

                                                            <?php if ($isAdmin || hasPermission('Alot Sent Tender', $privileges, $roleData['role_name'])) { ?>
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href='sent-edit.php?id=<?= urlencode($res) ?>'>
                                                                        <i class="feather icon-edit me-2"></i>Alot
                                                                    </a>
                                                                </li>
                                                            <?php } ?>


                                                            <?php if ($isAdmin || hasPermission('Edit Sent Tender', $privileges, $roleData['role_name'])) { ?>

                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="tender-edit.php?id=<?php echo $res . "&is_update=" . 1; ?>">
                                                                        <i class="feather icon-edit me-2"></i>Update
                                                                    </a>
                                                                </li>
                                                            <?php } ?>

                                                            <?php if ($isAdmin || hasPermission('Delete Sent Tender', $privileges, $roleData['role_name'])) { ?>
                                                                <!-- <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li> -->
                                                                <li>
                                                                    <a class="dropdown-item recyclebutton"
                                                                        href='javascript:void(0);'
                                                                        id='<?= htmlspecialchars($row['t_id']) ?>'
                                                                        data-tender-id='<?= htmlspecialchars($row['t_id']) ?>'
                                                                        title="Move to Bin">
                                                                        <i class="feather icon-trash me-2"></i>Move to Bin
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if ($isAdmin || hasPermission('Files Sent Tender', $privileges, $roleData['role_name'])) { ?>

                                                                <li>
                                                                    <a class="dropdown-item tender-files"
                                                                        href="javascript:void(0);"
                                                                        data-tender-id="<?php echo $row['t_id']; ?>"
                                                                        data-reference-code="<?php echo $row['reference_code']; ?>"
                                                                        data-tender-files='<?php echo $row['additional_files']; ?>'
                                                                        data-bs-toggle="modal" data-bs-target="#edit-units"
                                                                        title="Change Reference Number">
                                                                        <i class="feather icon-file me-2"></i>Files
                                                                    </a>
                                                                </li>
                                                            <?php } ?>

                                                            <?php if ($isAdmin || hasPermission('Reference Sent Tender', $privileges, $roleData['role_name'])) { ?>
                                                                <li>
                                                                    <a class="dropdown-item update-Reference"
                                                                        href="javascript:void(0);"
                                                                        data-tender-id="<?php echo $row['t_id']; ?>"
                                                                        data-reference-code="<?php echo $row['reference_code']; ?>"
                                                                        data-bs-toggle="modal" data-bs-target="#reference-code"
                                                                        title="Change Reference Number">
                                                                        <i class="feather icon-book me-2"></i>Reference No
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <!-- Assign Task -->
                                                            <li>
                                                                <a class="dropdown-item assign-task-dropdown-btn" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#assign-task-modal" 
                                                                   data-tender-id="<?php echo htmlspecialchars($row['t_id'] ?? ''); ?>" 
                                                                   data-tender-no="<?php echo htmlspecialchars($row['tenderID'] ?? ''); ?>" 
                                                                   data-ref-code="<?php echo htmlspecialchars($row['reference_code'] ?? ''); ?>" 
                                                                   data-department="<?php echo htmlspecialchars($row['department_name'] ?? ''); ?>" 
                                                                   data-section="<?php echo htmlspecialchars($row['section_name'] ?? ''); ?>" 
                                                                   data-division="<?php echo htmlspecialchars($row['division_name'] ?? ''); ?>" 
                                                                   data-due-date="<?php echo htmlspecialchars($row['due_date'] ?? ''); ?>" 
                                                                   title="Assign Task">
                                                                    <i class="feather icon-check-square me-2"></i>Assign Task
                                                                </a>
                                                            </li>

                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            $count++;
                                        }
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

    <div class="modal fade" id="edit-units" tabindex="-1" aria-labelledby="editUnitsLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUnitsLabel">Tender Files</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table id="tenderFilesTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Sno</th>
                                <th>File Name</th>
                                <th>Uploaded Date</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reference-code" tabindex="-1" aria-labelledby="editUnitsLabel" aria-hidden="true">
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


    <!-- jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <!--<script src="assets/js/menu-setting.min.js"></script>-->

    <script src="assets/js/plugins/jquery.dataTables.min.js"></script>
    <script src="assets/js/plugins/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/plugins/dataTables.fixedHeader.min.js"></script>
    <script src="assets/js/plugins/dataTables.buttons.min.js"></script>
    <script src="assets/js/plugins/buttons.colVis.min.js"></script>
    <script src="assets/js/plugins/buttons.print.min.js"></script>
    <script src="assets/js/plugins/pdfmake.min.js"></script>
    <script src="assets/js/plugins/jszip.min.js"></script>
    <script src="assets/js/plugins/buttons.html5.min.js"></script>
    <script src="assets/js/plugins/buttons.bootstrap4.min.js"></script>
    <script src="assets/js/pages/data-export-custom.js"></script>

    <!-- Excel Generate  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 (must come AFTER jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {

            // Handle tender files click
            $(document).on("click", ".tender-files", async function (e) {
                e.preventDefault();

                let files = $(this).data("tender-files");
                let tenderId = $(this).data("tender-id");
                let referenceCode = $(this).data("reference-code");

                // Clear existing table body
                $('#tenderFilesTable tbody').empty();

                if (files && Array.isArray(files)) {
                    // Populate table with dynamic data
                    files.forEach((file, index) => {
                        let fileName = file.split('/').pop(); // Get filename from path
                        let uploadDate = new Date().toLocaleDateString('en-GB'); // Current date as example

                        console.log(file);
                        console.log(uploadDate);

                        let row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td><a target='_blank' href=${file}>${fileName}</a></td>
                                <td>${uploadDate}</td>
                            </tr>
                        `;

                        $('#tenderFilesTable tbody').append(row);
                    });
                } else if (typeof files === 'string') {
                    // If files is a JSON string, parse it
                    try {
                        let parsedFiles = JSON.parse(files);
                        if (Array.isArray(parsedFiles)) {
                            parsedFiles.forEach((file, index) => {
                                let fileName = file.split('/').pop();
                                let uploadDate = new Date().toLocaleDateString('en-GB');

                                console.log(file);

                                let row = `
                                <tr>
                                    <td>${index + 1}</td>
                                   <td><a target='_blank' href=${file}>${fileName}</a></td>
                                    <td>${uploadDate}</td>
                                </tr>
                            `;

                                $('#tenderFilesTable tbody').append(row);
                            });
                        }
                    } catch (error) {
                        console.error("Error parsing files:", error);
                    }
                }
            });


            $('#department-search').select2({
                placeholder: "Select Department"
            });
            $('#section-search').select2({
                placeholder: "Select Section"
            });
            $('#division-search').select2({
                placeholder: "Select Division"
            });
            $('#sub-division-search').select2({
                placeholder: "Select Sub Division"
            });

            $('.select-firm').select2({
                placeholder: "Select State"
            });
            $('.select-state').select2({
                placeholder: "Select State"
            });
            $('.select-city').select2({
                placeholder: "Select City"
            });

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

            $(".recyclebutton").on('click', function (e) {

                let element = $(this);
                let del_id = element.attr("id");
                let info = 'id=' + del_id;

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
                            url: "recycleuser.php",
                            data: info,
                            success: function () {
                                // Show success message
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'The record has been moved to recycle bin.',
                                    icon: 'success',
                                    confirmButtonColor: "#33cc33",
                                    timer: 1500,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                });
                            },
                            error: function (error) {
                                console.log(error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Something went wrong while moving the record to recycle bin.',
                                    icon: 'error',
                                    confirmButtonColor: "#33cc33"
                                });
                            }
                        });

                        // Animate and remove the record
                        $(this).parents(".record").animate({
                            backgroundColor: "#FF3"
                        }, "fast")
                            .animate({
                                opacity: "hide"
                            }, "slow");

                        // Reload page after animation
                        setTimeout(function () {
                            window.location.reload();
                        }, 2000);
                    }
                });

                return false;
            });

        });
    </script>

    <script type="text/javascript">

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
                            success: function () {
                                $(".request_checkbox:checked").each(function () {
                                    $(this).closest(".record").animate({
                                        backgroundColor: "#FF3"
                                    }, "fast").animate({
                                        opacity: "hide"
                                    }, "slow", function () {
                                        $(this).remove();
                                    });
                                });
                                setTimeout(function () {
                                    window.location.reload();
                                },
                                    2000);
                            }
                        });
                    }
                });

            }
        });

    </script>

    <script type="text/javascript">
        $(document).ready(function () {
            // Initialize the DataTable with buttons
            var table = $('#basic-btn2').DataTable({
                pageLength: 100,
                lengthMenu: [25, 50, 100, 200, 500, 1000], // Custom dropdown options
                /*
                 * scrollX — enables horizontal scrollbar when columns overflow.
                 * scrollY — constrains table body to ~70vh with vertical scroll.
                 * fixedHeader — keeps the <thead> pinned to top during vertical scroll.
                 */
                scrollX: true,
                scrollY: '70vh',
                scrollCollapse: false,
                fixedHeader: true,
                ordering: true,
                searching: true
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#section-search').on('change', function () {
                let sectionId = $('#section-search').val();

                $.ajax({
                    url: 'fetch-section-data.php',
                    type: 'POST',
                    data: { sectionId: sectionId },
                    success: function (response) {
                        if (response.success) {
                            // console.log(response.divisionName);

                            // Clear existing options except the default "All" option
                            $('#division-search').find('option').not(':first').remove();

                            // Add new options based on the response.divisionId and response.divisionName arrays
                            response.divisionId.forEach((id, index) => {
                                let divisionName = response.divisionName[index];
                                $('#division-search').append(new Option(divisionName, id));
                            });

                        } else {
                            console.error(response.error);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            });


            $('#division-search').on('change', function () {
                let divisionId = $('#division-search').val();
                console.log(`divisionId: ${divisionId}`);

                $.ajax({
                    url: 'fetch-division-data.php',
                    type: 'POST',
                    data: { divisionId: divisionId },
                    success: function (response) {
                        if (response.success) {
                            console.log(response.subDivisionName);

                            // Clear existing options except the default "All" option
                            $('#sub-division-search').find('option').not(':first').remove();

                            // Add new options based on the response.divisionId and response.divisionName arrays
                            response.subDivisionId.forEach((id, index) => {
                                let subDivisionName = response.subDivisionName[index];
                                $('#sub-division-search').append(new Option(subDivisionName, id));
                            });

                        } else {
                            console.error(response.error);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            });



            const urlParams = new URLSearchParams(window.location.search);
            const sectionSearch = urlParams.get('section-search');
            const divisionSearch = urlParams.get('division-search');
            const subDivisionSearch = urlParams.get('sub-division-search');
            const state = urlParams.get('state');
            const city = urlParams.get('city');


            if (sectionSearch) {
                $.ajax({
                    url: 'fetch-section-data.php',
                    type: 'POST',
                    data: { sectionId: sectionSearch },
                    success: function (response) {
                        if (response.success) {
                            // console.log(response.divisionName);

                            // Clear existing options except the default "All" option
                            // $('#division-search').find('option').not(':first').remove();

                            // Add new options based on the response.divisionId and response.divisionName arrays
                            response.divisionId.forEach((id, index) => {
                                let divisionName = response.divisionName[index];
                                $('#division-search').append(new Option(divisionName, id));
                            });

                            if (divisionSearch) {
                                $('#division-search').val(divisionSearch).trigger('change');
                            }



                        } else {
                            console.error(response.error);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            }

            if (divisionSearch) {
                $.ajax({
                    url: 'fetch-division-data.php',
                    type: 'POST',
                    data: { divisionId: divisionSearch },
                    success: function (response) {
                        if (response.success) {

                            // Clear existing options except the default "All" option
                            $('#sub-division-search').find('option').not(':first').remove();

                            // Add new options based on the response.divisionId and response.divisionName arrays
                            response.subDivisionId.forEach((id, index) => {
                                let subDivisionName = response.subDivisionName[index];
                                $('#sub-division-search').append(new Option(subDivisionName, id));
                            });

                            setTimeout(() => {
                                if (subDivisionSearch) {
                                    $('#sub-division-search').val(subDivisionSearch).trigger('change');
                                }
                            }, 1000);


                        } else {
                            console.error(response.error);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            }


            if (state) {
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { stateCode: state },
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

                            setTimeout(() => {
                                if (city) {
                                    citySelect.val(city).trigger('change');
                                }
                            }, 1000);

                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        Swal.fire("Error", "An error occurred while processing your request. Please try again.", "error");
                    }
                });
            }




            function generateReferenceNumber() {
                return $.ajax({
                    url: window.location.href,
                    method: "POST",
                    data: { refCode: true },
                    dataType: "json"
                }).then(function (data) {
                    return data.data; // This matches your API response structure
                });
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
                if ($codeInput.length) {
                    try {
                        // Clear the existing value first
                        $codeInput.val('');



                        // Generate and set the new reference number
                        const refNumber = await generateReferenceNumber();
                        $codeInput.val(refNumber);

                    } catch (error) {
                        console.error('Error generating reference number:', error);
                    }
                }
            });

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
                        $('#reference-code').modal('hide');

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
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
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
        });
    </script>

<?php
// Fetch active employees for Assign Task modal
$empResult = mysqli_query($db, "SELECT id, username, email FROM admin WHERE status = 1 ORDER BY username ASC");
$activeEmployees = [];
while ($rowEmp = mysqli_fetch_assoc($empResult)) {
    $activeEmployees[] = $rowEmp;
}
?>
<!-- Assign Task Modal -->
<div class="modal fade" id="assign-task-modal" tabindex="-1" aria-labelledby="assignTaskLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignTaskLabel">Assign Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assign-task-form">
                <div class="modal-body">
                    <input type="hidden" name="action" value="assign_tender_task">
                    <input type="hidden" name="tender_request_id" id="assign-tender-id">
                    
                    <!-- Informational summary -->
                    <div class="alert alert-info py-2 mb-3">
                        <small>
                            <strong>Tender ID:</strong> <span id="assign-tender-no"></span> | 
                            <strong>Ref:</strong> <span id="assign-ref-code"></span><br>
                            <strong>Dept:</strong> <span id="assign-dept"></span> | 
                            <strong>Sec:</strong> <span id="assign-section"></span> | 
                            <strong>Div:</strong> <span id="assign-division"></span>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select form-control" required>
                            <option value="">Select Employee ▼</option>
                            <?php foreach ($activeEmployees as $emp): ?>
                                <option value="<?php echo htmlspecialchars($emp['id']); ?>">
                                    <?php echo htmlspecialchars($emp['username']) . (!empty($emp['email']) ? ' (' . htmlspecialchars($emp['email']) . ')' : ''); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Task Title</label>
                        <input type="text" name="title" id="assign-title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="assign-description" class="form-control" rows="5"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select form-control">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" id="assign-due-date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="assign-task-submit-btn" class="btn btn-primary">Assign Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Open Modal and populate data
    $(document).on('click', '.assign-task-dropdown-btn', function(e) {
        var tenderId = $(this).data('tender-id');
        var tenderNo = $(this).data('tender-no');
        var refCode = $(this).data('ref-code');
        var dept = $(this).data('department');
        var sec = $(this).data('section');
        var div = $(this).data('division');
        var dueDate = $(this).data('due-date');

        $('#assign-tender-id').val(tenderId);
        $('#assign-tender-no').text(tenderNo || 'N/A');
        $('#assign-ref-code').text(refCode || 'N/A');
        $('#assign-dept').text(dept || 'N/A');
        $('#assign-section').text(sec || 'N/A');
        $('#assign-division').text(div || 'N/A');

        if (dueDate) {
            var cleanDate = String(dueDate).trim().split(' ')[0];
            $('#assign-due-date').val(cleanDate);
        } else {
            $('#assign-due-date').val('');
        }

        var title = 'Tender Request - ' + (tenderNo ? tenderNo : tenderId);
        $('#assign-title').val(title);

        var desc = "Tender ID: " + (tenderNo || 'N/A') + "\n" +
                   "Reference Code: " + (refCode || 'N/A') + "\n" +
                   "Department: " + (dept || 'N/A') + "\n" +
                   "Section: " + (sec || 'N/A') + "\n" +
                   "Division: " + (div || 'N/A') + "\n" +
                   "Due Date: " + (dueDate || 'N/A');
        $('#assign-description').val(desc);

        // Fallback open if data-bs-toggle didn't trigger
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modalEl = document.getElementById('assign-task-modal');
                var modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modalInstance.show();
            } else if (typeof $.fn.modal !== 'undefined') {
                $('#assign-task-modal').modal('show');
            }
        } catch (err) {}
    });

    // Form submission via AJAX
    $('#assign-task-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        var $submitBtn = $('#assign-task-submit-btn');
        var originalBtnText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Assigning...');
        
        function notifyUser(type, message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'success' ? 'Success!' : 'Error',
                    text: message,
                    icon: type,
                    confirmButtonColor: type === 'success' ? '#33cc33' : '#d33',
                    timer: type === 'success' ? 2000 : undefined,
                    timerProgressBar: type === 'success',
                    showConfirmButton: type !== 'success'
                });
            } else if (typeof Notyf !== 'undefined') {
                var notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' }, dismissible: true });
                if (type === 'success') notyf.success(message);
                else notyf.error(message);
            } else {
                alert(message);
            }
        }

        $.ajax({
            url: 'sent-tender2.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 200) {
                    try {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            var modalEl = document.getElementById('assign-task-modal');
                            var modalInstance = bootstrap.Modal.getInstance(modalEl);
                            if (modalInstance) modalInstance.hide();
                        }
                    } catch (e) {}
                    try {
                        $('#assign-task-modal').modal('hide');
                    } catch (e) {}
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');

                    notifyUser('success', response.message || "Task assigned successfully.");
                    $('#assign-task-form')[0].reset();
                } else {
                    notifyUser('error', response.error || "Error assigning task.");
                }
            },
            error: function(xhr) {
                var errorMsg = "An unexpected error occurred while assigning the task.";
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res && res.error) errorMsg = res.error;
                } catch(e) {}
                notifyUser('error', errorMsg);
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });
});
</script>
</body>

</html>
