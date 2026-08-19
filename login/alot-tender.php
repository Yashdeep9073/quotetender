<?php

ini_set('display_errors', 1);

session_start();


if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}
$name = $_SESSION['login_user'];

include("db/config.php");

$adminID = $_SESSION['login_user_id'];


if (
    $_SERVER['REQUEST_METHOD'] == 'GET' &&
    isset($_GET['department-search']) ||
    isset($_GET['section-search']) ||
    isset($_GET['division-search']) ||
    isset($_GET['sub-division-search']) ||
    isset($_GET['firm']) ||
    isset($_GET['state']) ||
    isset($_GET['city'])

) {



    // Initialize $conditions as an empty array
    $conditions = [];

    // Sanitize inputs
    $departmentId = filter_input(INPUT_GET, 'department-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $sectionId = filter_input(INPUT_GET, 'section-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $divisionId = filter_input(INPUT_GET, 'division-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $subDivisionId = filter_input(INPUT_GET, 'sub-division-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $firm = filter_input(INPUT_GET, 'firm', FILTER_SANITIZE_SPECIAL_CHARS);
    $state = filter_input(INPUT_GET, 'state', FILTER_SANITIZE_SPECIAL_CHARS);
    $city = filter_input(INPUT_GET, 'city', FILTER_SANITIZE_SPECIAL_CHARS);



    // // Set the sanitized data in the session
    // $_SESSION['departmentIdAlotTender'] = $departmentId;
    // $_SESSION['sectionIdAlotTender'] = $sectionId;
    // $_SESSION['divisionIdAlotTender'] = $divisionId;
    // $_SESSION['firm'] = $firm;
    // $_SESSION['state'] = $state;
    // $_SESSION['city'] = $city;

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
        $conditions[] = "sm.firm_name = '$firm'";
    }

    if ($state && $state !== '0') {
        $conditions[] = "st.state_code = '$state'";
    }

    if ($city && $city !== '0') {
        $conditions[] = "ct.city_id = '$city'";
    }


    // Ensure static conditions are always present
    $conditions[] = "ur.status = 'Allotted'";
    $conditions[] = "ur.delete_tender = '0'";

    // Construct the WHERE clause dynamically
    $whereClause = "WHERE " . implode(' AND ', $conditions);

    // SQL Query with dynamic WHERE clause
    $queryMain = "
        SELECT 
            MAX(sm.name) AS name,
            MAX(sm.email_id) AS email_id,
            MAX(sm.firm_name) AS firm_name,
            MAX(sm.mobile) AS mobile,
            ur.tender_no,
            MAX(department.department_name) AS department_name,
            ur.name_of_work,
            ur.reminder_days,
            ur.allotted_at,
            ur.file_name,
            ur.id AS t_id,
            ur.reference_code,
            ur.tenderID,
            ur.file_name2,
            MAX(dv.division_name) AS division_name,
            MAX(se.section_name) AS section_name,
            MAX(sd.subdivision) AS subdivision,
            ur.tentative_cost,
            sm.city_state,
            ur.updated_by,
            MAX(st.state_name) AS state_name,  -- Get state_name from state table, not members
            MAX(ct.city_name) AS city_name    -- Use MAX() for consistency
        FROM
            user_tender_requests ur
        LEFT JOIN
            members m ON ur.member_id = m.member_id
        LEFT JOIN
            department ON ur.department_id = department.department_id
        LEFT JOIN
            section se ON ur.section_id = se.section_id
        LEFT JOIN
            members sm ON ur.selected_user_id = sm.member_id
        LEFT JOIN
                division dv ON ur.division_id = dv.division_id
        LEFT JOIN
                sub_division sd ON ur.sub_division_id = sd.id
        LEFT JOIN
            state st ON CONVERT(sm.state_code USING utf8mb4) = CONVERT(st.state_code USING utf8mb4)  -- Fix collation
        LEFT JOIN   
            cities ct ON CAST(sm.city_state AS UNSIGNED) = ct.city_id  -- Convert string to number
        $whereClause
        GROUP BY
            ur.id
        ORDER BY
            NOW() >= CAST(ur.due_date AS DATE),
            CAST(ur.allotted_at AS DATE) ASC,
            ABS(DATEDIFF(NOW(), CAST(ur.due_date AS DATE)));
    ";

    // Execute the query
    $resultMain = mysqli_query($db, $queryMain);
    if (!$resultMain) {
        die("Query Error: " . mysqli_error($db));
    }
} else {
    $queryMain = "SELECT 
    MAX(sm.name) AS name,
    MAX(sm.email_id) AS email_id,
    MAX(sm.firm_name) AS firm_name,
    MAX(sm.mobile) AS mobile,
    MAX(sm.city_state) AS city_state,
    MAX(sm.state_code) AS state_code,
    ur.tender_no,
    MAX(department.department_name) AS department_name,
    ur.name_of_work,
    ur.reminder_days,
    ur.allotted_at,
    ur.file_name,
    ur.id as t_id,
    ur.reference_code,
    ur.tenderID,
    ur.file_name2,
    ur.additional_files,
    MAX(dv.division_name) AS division_name,
    MAX(se.section_name) AS section_name,
    MAX(sd.subdivision) AS subdivision,
    ur.tentative_cost,
    ur.updated_by,
    MAX(st.state_name) AS state_name,  -- Get state_name from state table, not members
    MAX(ct.city_name) AS city_name    -- Use MAX() for consistency
FROM
    user_tender_requests ur
LEFT JOIN
    members m ON ur.member_id = m.member_id
LEFT JOIN
    department ON ur.department_id = department.department_id
LEFT JOIN
    section se ON ur.section_id = se.section_id
LEFT JOIN
    members sm ON ur.selected_user_id = sm.member_id
LEFT JOIN
         division dv ON ur.division_id = dv.division_id
LEFT JOIN
         sub_division sd ON ur.sub_division_id = sd.id
LEFT JOIN
         state st ON CONVERT(sm.state_code USING utf8mb4) = CONVERT(st.state_code USING utf8mb4)  -- Fix collation
LEFT JOIN
         cities ct ON CAST(sm.city_state AS UNSIGNED) = ct.city_id  -- Convert string to number
WHERE
    ur.status = 'Allotted' AND ur.delete_tender = '0'
GROUP BY
    ur.id
ORDER BY
    NOW() >= CAST(ur.due_date AS DATE),
    CAST(ur.allotted_at AS DATE) ASC,
    ABS(DATEDIFF(NOW(), CAST(ur.due_date AS DATE)))";

    $resultMain = mysqli_query($db, $queryMain);

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


try {

    $stmtFetchTenderAllotted = $db->prepare("SELECT count(*) AS COUNT FROM user_tender_requests 
    WHERE status = 'Allotted' AND delete_tender = 0;");
    $stmtFetchTenderAllotted->execute();
    $tenderAllottedCount = $stmtFetchTenderAllotted->get_result()->fetch_array(MYSQLI_ASSOC);

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

    $adminID = $_SESSION['login_user_id'];
    $adminPermissionQuery = "SELECT nm.title FROM admin_permissions ap 
inner join navigation_menus nm on ap.navigation_menu_id = nm.id where ap.admin_id='" . $adminID . "' ";
    $adminPermissionResult = mysqli_query($db, $adminPermissionQuery);

    $permissions = [];
    while ($item = mysqli_fetch_row($adminPermissionResult)) {
        array_push($permissions, $item[0]);
    }


    // Fetch unique, non-empty cities only
    $stmtFetchCities = $db->prepare("SELECT * FROM cities WHERE is_active = 1 ");
    $stmtFetchCities->execute();
    $cities = $stmtFetchCities->get_result()->fetch_all(MYSQLI_ASSOC);



    // Fetch unique, non-empty cities only
    $stmtFetchStates = $db->prepare("SELECT * FROM state WHERE is_active = 1 ");
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


    // echo "<pre>";
    // print_r($firms);
    // exit;

} catch (\Throwable $th) {
    //throw $th;
}


?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Orders </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/plugins/fixedHeader.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* ==========================================================
           Alot Tender page — scoped UI modernization.
           All rules are page-scoped (.alot-tender-page) so the
           rest of the admin panel is unaffected.
           ========================================================== */
        .alot-tender-page {
            padding: 10px;
        }

        /* ---------- shared card base ---------- */
        .alot-tender-page .card {
            border: 0;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .05), 0 1px 3px rgba(16, 24, 40, .08);
            margin-bottom: 10px;
        }

        /* ---------- KPI card ---------- */
        .alot-tender-page .st-kpi-card .st-kpi-body {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 20px;
            text-align: left;
            margin: 0;
        }

        .alot-tender-page .st-kpi-icon {
            flex: 0 0 auto;
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .22);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .alot-tender-page .st-kpi-meta {
            display: flex;
            flex-direction: column;
        }

        .alot-tender-page .st-kpi-label {
            font-size: 13px;
            font-weight: 500;
            color: rgba(255, 255, 255, .85);
        }

        .alot-tender-page .st-kpi-value {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.2;
        }

        /* KPI card — RED theme */
        .alot-tender-page .st-kpi-card {
            background: linear-gradient(45deg, #dc2626, #ef4444) !important;
            margin-bottom: 10px !important;
            overflow: hidden;
        }

        /* Breadcrumb inside KPI */
        .alot-tender-page .st-kpi-breadcrumb {
            padding: 0 20px 12px;
        }

        .alot-tender-page .st-kpi-breadcrumb .breadcrumb {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
            padding: 0;
            background: transparent;
            font-size: 12px;
        }

        .alot-tender-page .st-kpi-breadcrumb .breadcrumb a {
            color: #ffffff !important;
            text-decoration: none;
        }

        .alot-tender-page .st-kpi-breadcrumb .breadcrumb a:hover {
            color: rgba(255, 255, 255, .88) !important;
        }

        .alot-tender-page .st-kpi-breadcrumb .breadcrumb-item.active {
            color: rgba(255, 255, 255, .9);
        }

        .alot-tender-page .st-kpi-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255, 255, 255, .65);
        }

        /* ---------- filter panel ---------- */
        .alot-tender-page .st-filter-panel {
            padding: 16px;
            margin-bottom: 14px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .alot-tender-page .st-filter-panel .st-filter-body {
            padding: 0;
            text-align: left;
        }

        .alot-tender-page .st-filter-head {
            margin-bottom: 16px;
        }

        .alot-tender-page .st-filter-title {
            margin: 0 0 2px;
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
        }

        .alot-tender-page .st-filter-sub {
            margin: 0;
            font-size: 13px;
            color: #94a3b8;
        }

        .alot-tender-page .st-filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .alot-tender-page .st-filter-field label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
        }

        .alot-tender-page .st-filter-grid .select2-container {
            width: 100% !important;
        }

        .alot-tender-page .st-filter-grid .select2-container .select2-selection--single {
            height: 42px;
            border: 1px solid #d0d5dd;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }

        .alot-tender-page .st-filter-grid .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
            padding-left: 12px;
            padding-right: 28px;
            font-size: 14px;
            color: #1e293b;
        }

        .alot-tender-page .st-filter-grid .select2-container .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 8px;
        }

        .alot-tender-page .st-filter-grid .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8;
        }

        .alot-tender-page .st-filter-grid .select2-container--default.select2-container--focus .select2-selection--single,
        .alot-tender-page .st-filter-grid .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
        }

        .alot-tender-page .select2-container--default .select2-dropdown {
            border-color: #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(16, 24, 40, .12);
        }

        .alot-tender-page .st-filter-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 20px;
        }

        .alot-tender-page .st-filter-actions .btn {
            height: 40px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .alot-tender-page .st-filter-actions .btn-outline-secondary {
            background: #fff;
            border: 1px solid #d0d5dd;
            color: #475569;
        }

        .alot-tender-page .st-filter-actions .btn-outline-secondary:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        /* ---------- table toolbar ---------- */
        .alot-tender-page .st-table-card .st-table-body {
            padding: 14px;
            text-align: left;
        }

        .alot-tender-page .st-table-body .alert {
            margin-bottom: 16px;
            border-radius: 8px;
        }

        .alot-tender-page .st-table-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            padding: 8px 12px;
            margin-bottom: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .alot-tender-page .st-toolbar-left,
        .alot-tender-page .st-toolbar-right {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .alot-tender-page .st-toolbar-left {
            flex: 1 1 auto;
        }

        .alot-tender-page .st-toolbar-right {
            flex: 0 0 auto;
            margin-left: auto;
        }

        .alot-tender-page .st-table-title {
            margin: 0 8px 0 0;
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
        }

        .alot-tender-page .st-table-toolbar .btn {
            height: 36px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
        }

        .alot-tender-page .st-table-toolbar .btn-danger {
            background: #dc2626;
            border-color: #dc2626;
        }

        .alot-tender-page .st-table-toolbar .btn-danger:hover {
            background: #b91c1c;
            border-color: #b91c1c;
        }

        .alot-tender-page .st-toolbar-left .buttons-excel,
        .alot-tender-page .st-toolbar-left .buttons-csv,
        .alot-tender-page .st-toolbar-left .buttons-print,
        .alot-tender-page .st-toolbar-right .btn {
            background: #fff;
            border: 1px solid #d0d5dd;
            color: #475569;
        }

        .alot-tender-page .st-toolbar-left .buttons-excel:hover,
        .alot-tender-page .st-toolbar-left .buttons-csv:hover,
        .alot-tender-page .st-toolbar-left .buttons-print:hover,
        .alot-tender-page .st-toolbar-right .btn:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .alot-tender-page .st-toolbar-left .buttons-excel i {
            color: #16a34a;
        }

        .alot-tender-page .st-toolbar-left .buttons-csv i {
            color: #0891b2;
        }

        .alot-tender-page .st-toolbar-left .buttons-print i {
            color: #64748b;
        }

        /* ---------- DataTable wrapper controls ---------- */
        .alot-tender-page .st-table-card .dataTables_wrapper > .row:first-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: nowrap;
            gap: 8px;
            margin: 0;
        }

        .alot-tender-page .st-table-card .dataTables_wrapper > .row:first-child > div {
            width: auto !important;
            padding: 0;
            max-width: none;
        }

        .alot-tender-page .st-table-card .dataTables_wrapper > .row:first-child > div:first-child {
            flex: 0 0 auto;
        }

        .alot-tender-page .st-table-card .dataTables_wrapper > .row:first-child > div:last-child {
            flex: 0 0 auto;
            margin-left: auto;
        }

        .alot-tender-page .st-table-card .dataTables_length,
        .alot-tender-page .st-table-card .dataTables_filter {
            padding: 2px 0 6px;
        }

        .alot-tender-page .st-table-card .dataTables_length label,
        .alot-tender-page .st-table-card .dataTables_filter label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            white-space: nowrap;
        }

        .alot-tender-page .st-table-card .dataTables_filter {
            text-align: right;
        }

        .alot-tender-page .st-table-card .dataTables_length select,
        .alot-tender-page .st-table-card .dataTables_filter input {
            height: 34px;
            border: 1px solid #d0d5dd;
            border-radius: 6px;
            background: #fff;
            font-size: 13px;
            color: #1e293b;
        }

        .alot-tender-page .st-table-card .dataTables_length select {
            padding: 0 8px;
        }

        .alot-tender-page .st-table-card .dataTables_filter input {
            padding: 0 10px;
            width: clamp(140px, 24vw, 260px);
            margin-left: 0;
        }

        .alot-tender-page .st-table-card .dataTables_info {
            position: relative !important;
            z-index: 10;
            display: block !important;
            width: 100% !important;
            padding: 6px 0 2px !important;
            margin: 0 !important;
            background: #fff !important;
            color: #64748b !important;
            font-size: 12.5px;
            line-height: 18px;
            clear: both;
        }

        .alot-tender-page .st-table-card .dataTables_paginate {
            padding-top: 4px;
        }

        /* ---------- table ---------- */
        .alot-tender-page .st-table-card .table {
            margin-bottom: 0;
            border-color: #e2e8f0;
        }

        .alot-tender-page .st-table-card .table thead th {
            background-color: #33cc33 !important;
            color: #ffffff !important;
            border-color: #33cc33 !important;
            padding: 7px 10px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            vertical-align: middle;
        }

        .alot-tender-page .st-table-card .table tbody td {
            padding: 5px 10px;
            font-size: 12.5px;
            color: #334155;
            vertical-align: middle;
            white-space: nowrap;
        }

        .alot-tender-page .st-table-card .table tbody tr:hover td {
            background-color: #f1f5f9;
        }

        /* Compact reminder cell */
        .alot-tender-page .st-table-card .table tbody td.td-reminder {
            white-space: normal;
            min-width: 130px;
            max-width: 180px;
            font-size: 11.5px;
            line-height: 1.4;
            padding: 5px 8px;
        }

        .alot-tender-page .st-table-card .table tbody td.td-reminder .badge-days {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            background: #16a34a;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.4;
        }

        /* ---------- action dropdown ---------- */
        .alot-tender-page .st-table-card .dropdown .st-action-btn {
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

        .alot-tender-page .st-table-card .dropdown .st-action-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .alot-tender-page .st-table-card .dropdown-menu {
            min-width: 170px;
            padding: 6px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(16, 24, 40, .12);
        }

        .alot-tender-page .st-table-card .dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 6px;
            font-size: 13.5px;
            color: #334155;
        }

        .alot-tender-page .st-table-card .dropdown-item:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .alot-tender-page .st-table-card .dropdown-item i {
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

        /* DataTables bottom row */
        .alot-tender-page .st-table-card .dataTables_wrapper > .row:last-child {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            margin: 0 !important;
            padding-top: 4px;
            background: #fff;
        }

        /* Prevent the info/pagination area from being clipped */
        .alot-tender-page .st-table-card .dataTables_wrapper {
            overflow: visible !important;
        }

        /* Scroll only the actual table body */
        .alot-tender-page .st-table-card .dataTables_scroll {
            overflow: visible !important;
        }

        .alot-tender-page .st-table-card .dataTables_scrollBody {
            overflow-x: auto !important;
            overflow-y: auto !important;
        }

        /* ---------- responsive ---------- */
        @media (max-width: 991.98px) {
            .alot-tender-page .st-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .alot-tender-page .st-filter-grid {
                grid-template-columns: 1fr;
            }

            .alot-tender-page .st-filter-actions {
                justify-content: stretch;
            }

            .alot-tender-page .st-filter-actions .btn {
                flex: 1 1 auto;
                justify-content: center;
            }

            .alot-tender-page .st-table-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .alot-tender-page .st-toolbar-left,
            .alot-tender-page .st-toolbar-right {
                width: 100%;
            }

            .alot-tender-page .st-toolbar-right {
                margin-left: 0;
                justify-content: flex-end;
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
        <div class="pcoded-content alot-tender-page">
            <div class="row">
                <div class="col-12">

                    <div class="card st-kpi-card">

                        <!-- KPI -->
                        <div class="card-body st-kpi-body">
                            <div class="st-kpi-icon">
                                <i class="feather icon-home"></i>
                            </div>

                            <div class="st-kpi-meta">
                                <span class="st-kpi-label">Alot Tender</span>
                                <span class="st-kpi-value" id="category">
                                    <?php
                                    $alotTendersCountValue = 0; // Default value
                                    if ($isAdmin || hasPermission('Alot Tenders Count', $privileges, $roleData['role_name'])) {
                                        $alotTendersCountValue = $tenderAllottedCount['COUNT'] ?? 0;
                                    } else {
                                        $alotTendersCountValue = 0;
                                    }
                                    echo $alotTendersCountValue;
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
                                    Alot Tender
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
                                    <h6 class="st-table-title">Alot Tenders</h6>
                                    <?php
                                    if ($isAdmin || hasPermission('Bulk Delete Alot Tender', $privileges, $roleData['role_name'])) {
                                        echo "<a href='javascript:void(0);' id='recycle_records' class='btn btn-danger'>
                                        <i class='feather icon-trash'></i> Move to Bin
                                        </a>";
                                    }
                                    ?>
                                    <?php if ($isAdmin || hasPermission('Alot Tender Excel', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn buttons-excel" tabindex="0" aria-controls="basic-btn2"
                                            type="button" onclick="exportTableToExcel()" title="Export to Excel">
                                            <span><i class="fas fa-file-excel"></i> Excel</span>
                                        </button>
                                    <?php } ?>
                                    <?php if ($isAdmin || hasPermission('Alot Tender CSV', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn buttons-csv" tabindex="0" aria-controls="basic-btn2"
                                            type="button" onclick="exportTableToCSV()" title="Export to CSV">
                                            <span><i class="fas fa-file-csv"></i> CSV</span>
                                        </button>
                                    <?php } ?>
                                    <?php if ($isAdmin || hasPermission('Alot Tender Print', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn buttons-print" tabindex="0" onclick="printTable()"
                                            aria-controls="basic-btn2" type="button" title="Print">
                                            <span><i class="fas fa-print"></i> Print</span>
                                        </button>
                                    <?php } ?>
                                </div>
                                <?php if ($isAdmin || hasPermission('Alot Tenders Filter', $privileges, $roleData['role_name'])) { ?>
                                    <div class="st-toolbar-right">
                                        <button class="btn" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#alotTenderFilters" aria-expanded="false"
                                            aria-controls="alotTenderFilters" title="Filters">
                                            <i class="feather icon-filter"></i> Filters
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>

                            <?php if ($isAdmin || hasPermission('Alot Tenders Filter', $privileges, $roleData['role_name'])) { ?>
                                <div class="collapse st-filter-collapse" id="alotTenderFilters">
                                    <div class="st-filter-panel">
                                        <div class="st-filter-body">
                                            <div class="st-filter-head">
                                                <h6 class="st-filter-title">Filters</h6>
                                                <p class="st-filter-sub">Narrow down alot tenders</p>
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
                                                    <a href="alot-tender.php" class="btn btn-outline-secondary"
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
                                            <th>User</th>
                                            <th>Email</th>
                                            <th>Firm</th>
                                            <th>State</th>
                                            <th>City</th>
                                            <th>Mobile</th>
                                            <th>Tender ID</th>
                                            <th>Ref. Code </th>
                                            <th>Tender No</th>
                                            <th>Department</th>
                                            <th>Section</th>
                                            <th>Division</th>
                                            <th>Sub-division</th>
                                            <th class='work-name'>Work Name</th>
                                            <th>Tentative Cost</th>
                                            <th>Reminder</th>
                                            <th>Updated By</th>
                                            <th>Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 1;
                                        while ($row = mysqli_fetch_assoc($resultMain)) {
                                            ?>
                                            <tr class='record'>
                                                <td>
                                                    <div class='custom-control custom-checkbox'>
                                                        <input type='checkbox' class='custom-control-input request_checkbox'
                                                            id='customCheck<?php echo $count; ?>'
                                                            data-request-id='<?php echo $row['t_id']; ?>'>
                                                        <label class='custom-control-label'
                                                            for='customCheck<?php echo $count; ?>'><?php echo $count; ?></label>
                                                    </div>
                                                </td>

                                                <td><span style='color:red;'><?php echo $row['name']; ?></span></td>
                                                <td><span style='color:green;'><?php echo $row['email_id']; ?></span></td>
                                                <td><?php echo $row['firm_name']; ?></td>
                                                <td><?php echo $row['state_name']; ?></td>
                                                <td><?php echo $row['city_name']; ?></td>
                                                <td><?php echo $row['mobile']; ?></td>
                                                <td><?php echo $row['tenderID']; ?></td>
                                                <td><?php echo $row['reference_code']; ?></td>
                                                <td><?php echo $row['tender_no']; ?></td>
                                                <td><?php echo $row['department_name']; ?></td>
                                                <td><?php echo $row['section_name']; ?></td>
                                                <td><?php echo $row['division_name']; ?></td>
                                                <td><?php echo $row['subdivision']; ?></td>
                                                <td style='white-space:pre-wrap; word-wrap:break-word; max-width:20rem;'>
                                                    <?php echo $row['name_of_work']; ?>
                                                </td>
                                                <td style='white-space:pre-wrap; word-wrap:break-word; max-width:20rem;'>
                                                    <?php echo $row['tentative_cost']; ?> rupees /-
                                                </td>

                                                <td class="td-reminder">
                                                    <span class='badge-days'><?php echo $row['reminder_days']; ?> days</span>
                                                    <br />
                                                    Alloted: <?php echo date_format(date_create($row['allotted_at']), "d-m-Y"); ?>
                                                    <br />
                                                    <?php if (isset($row['file_name']) && $row['file_name'] == null) { ?>
                                                        <a href="<?php echo '../login/tender/' . $row['file_name']; ?>"
                                                            target="_blank">
                                                            View file 1
                                                        </a> </br>
                                                    <?php } ?>

                                                    <?php if (isset($row['file_name2']) && $row['file_name2'] == null) { ?>
                                                        <a href="<?php echo '../login/tender/' . $row['file_name2']; ?>"
                                                            target="_blank">View
                                                            File 2
                                                        </a>
                                                    <?php } ?>

                                                    <?php if (!empty($row['additional_files'])) {
                                                        $extraFiles = json_decode($row['additional_files'], true);
                                                        if (is_array($extraFiles)) {
                                                            $fileCount = 1;
                                                            foreach ($extraFiles as $index => $filePath) { ?>
                                                                <a href="<?php echo '../login/' . $filePath; ?>" target="_blank">View
                                                                    File <?php echo $fileCount; ?>
                                                                </a><br />
                                                                <?php
                                                                $fileCount++;
                                                            }
                                                        }
                                                    } ?>
                                                </td>

                                                <td><?php echo $row['updated_by']; ?></td>


                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary st-action-btn" type="button"
                                                            id="dropdownMenu<?php echo $row['t_id']; ?>"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="feather icon-more-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu"
                                                            aria-labelledby="dropdownMenu<?php echo $row['t_id']; ?>">
                                                            <?php if ($isAdmin || hasPermission('Award Alot Tender', $privileges, $roleData['role_name'])) { ?>
                                                                <li>
                                                                    <a class='dropdown-item makeAward'
                                                                        href='javascript:void(0);'
                                                                        id='<?php echo $row['t_id']; ?>'
                                                                        title='Click To Make Award'>
                                                                        <i class='feather icon-award'></i> Award
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if ($isAdmin || hasPermission('Delete Alot Tender', $privileges, $roleData['role_name'])) { ?>
                                                                <li>
                                                                    <a class='dropdown-item recyclebutton'
                                                                        href='javascript:void(0);'
                                                                        id='<?php echo $row['t_id']; ?>'
                                                                        title='Click To Delete'>
                                                                        <i class='feather icon-trash'></i> Move to Bin
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php
                                                            $res = $row['t_id'];
                                                            $res = base64_encode($res);

                                                            if ($isAdmin || hasPermission('Re-Alot Alot Tender', $privileges, $roleData['role_name'])) {
                                                                ?>
                                                                <li>
                                                                    <a class='dropdown-item'
                                                                        href='alot-tender-update.php?id=<?php echo $res; ?>'>
                                                                        <i class='feather icon-repeat'></i> Re-Alot
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                        </ul>
                                                    </div>
                                                </td>

                                            </tr>
                                            <?php
                                            $count++;
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot></tfoot>
                                </table>
                            </div>
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

    <script type="text/javascript">
        $(".recyclebutton").on('click', function () {

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
        });
        $(".makeAward").on('click', function () {

            var element = $(this);

            var del_id = element.attr("id");

            var info = 'makeaward_id=' + del_id;

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this Record!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#33cc33",
                cancelButtonColor: "#ff5471",
                confirmButtonText: "Yes, Award tender!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "GET",
                        url: "recycleuser.php",
                        data: info,
                        success: function (response) {

                            console.log(response);

                            let result = JSON.parse(response);

                            if (result.status == 200) {
                                // Show success message
                                Swal.fire({
                                    title: 'Awarded!',
                                    text: 'Tender awarded to user.',
                                    icon: 'success',
                                    confirmButtonColor: "#33cc33",
                                    timer: 1500,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: `${result.error}`,
                                    icon: 'error',
                                    confirmButtonColor: "#33cc33"
                                });
                            }

                        },
                        error: function (error) {
                            console.log(error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Something went wrong while moving updating.',
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
                })

            }
        });

    </script>

    <script>
        $(document).ready(function () {

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

            // Initialize the DataTable with buttons
            var table = $('#basic-btn2').DataTable({
                pageLength: 100,
                lengthMenu: [25, 50, 100, 200, 500, 1000], // Custom dropdown options
                /*
                 * scrollX — enables horizontal scrollbar when columns overflow.
                 * scrollY — constrains table body to ~70vh with vertical scroll.
                 * fixedHeader — keeps the <thead> pinned to top during vertical scroll.
                 * SNO column is NOT frozen — all columns scroll together.
                 */
                scrollX: true,
                scrollY: '70vh',
                scrollCollapse: false,
                fixedHeader: true,
                ordering: true,
                searching: true
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




            $('#section-search').on('change', function () {
                let sectionId = $('#section-search').val();

                $.ajax({
                    url: 'fetch-section-data.php',
                    type: 'POST',
                    data: { sectionId: sectionId },
                    success: function (response) {
                        if (response.success) {

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

                $.ajax({
                    url: 'fetch-division-data.php',
                    type: 'POST',
                    data: { divisionId: divisionId },
                    success: function (response) {
                        if (response.success) {

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
                        console.error("Raw Response:", xhr.responseText);
                        Swal.fire("Error", "An error occurred while processing your request. Please try again.", "error");
                    }
                });
            }
        });


    </script>


    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
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
        });
    </script>

</body>

</html>