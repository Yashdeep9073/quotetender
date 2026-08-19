<?php

ini_set('display_errors', 1);

session_start();


if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}
$name = $_SESSION['login_user'];

include("db/config.php");

$adminID = $_SESSION['login_user_id'];




// Initialize the row number variable
mysqli_query($db, "SET @row_number = 0;");

$queryMain = "
   SELECT 
    ROW_NUMBER() OVER (ORDER BY ur.created_at) AS sno,
    ur.*,
    ur.status AS tenderStatus,
    department.*, 
    s.*,
    dv.*,
    sd.* ,
     ur.id as userTenderId
FROM 
    user_tender_requests ur
LEFT JOIN 
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
    (
        SELECT MIN(id) AS min_id, tenderID
        FROM user_tender_requests
        GROUP BY tenderID
    ) AS unique_tenders ON ur.id = unique_tenders.min_id
WHERE ur.delete_tender = 0
ORDER BY 
    ur.created_at ASC;
    ";

$resultMain = mysqli_query($db, $queryMain);


$stmtFetchAllTender = $db->prepare("SELECT COUNT(*) AS COUNT
FROM (
    SELECT 
        ur.id
    FROM 
        user_tender_requests ur
    LEFT JOIN (
        SELECT MIN(id) AS min_id, tenderID
        FROM user_tender_requests
        GROUP BY tenderID
    ) AS unique_tenders ON ur.id = unique_tenders.min_id
    WHERE ur.delete_tender = 0
) AS subquery;
");
$stmtFetchAllTender->execute();
$allTenderData = $stmtFetchAllTender->get_result()->fetch_array(MYSQLI_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>All Tender Request </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">



    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/plugins/fixedHeader.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <!-- html to excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <style>
        /* ==========================================================
           All Tender Request page — same UI design system as
           Sent Tender / Tender Request.
           ========================================================== */
        .all-tender-request-page {
            padding: 16px;
        }

        /* ---------- shared card base ---------- */
        .all-tender-request-page .card {
            border: 0;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .05), 0 1px 3px rgba(16, 24, 40, .08);
            margin-bottom: 16px;
        }

        /* ---------- KPI card ---------- */
        .all-tender-request-page .st-kpi-card {
            margin-bottom: 14px;
        }

        .all-tender-request-page .st-kpi-card .st-kpi-body {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            text-align: left;
        }

        .all-tender-request-page .st-kpi-icon {
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

        .all-tender-request-page .st-kpi-meta {
            display: flex;
            flex-direction: column;
        }

        .all-tender-request-page .st-kpi-label {
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
        }

        .all-tender-request-page .st-kpi-value {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        /* ---------- breadcrumb inside KPI card ---------- */
        .all-tender-request-page .st-kpi-breadcrumb {
            padding: 0 20px 12px;
        }

        .all-tender-request-page .st-kpi-breadcrumb .breadcrumb {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
            padding: 0;
            background: transparent;
            font-size: 12px;
        }

        .all-tender-request-page .st-kpi-breadcrumb .breadcrumb a {
            color: #64748b;
            text-decoration: none;
        }

        .all-tender-request-page .st-kpi-breadcrumb .breadcrumb a:hover {
            color: #0f172a;
        }

        .all-tender-request-page .st-kpi-breadcrumb .breadcrumb-item.active {
            color: #94a3b8;
        }

        .all-tender-request-page .st-kpi-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
            color: #cbd5e1;
        }

        /* ---------- table toolbar ---------- */
        .all-tender-request-page .st-table-card .st-table-body {
            padding: 20px;
            text-align: left;
        }

        .all-tender-request-page .st-table-body .alert {
            margin-bottom: 16px;
            border-radius: 8px;
        }

        .all-tender-request-page .st-table-toolbar {
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

        .all-tender-request-page .st-toolbar-left,
        .all-tender-request-page .st-toolbar-right {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .all-tender-request-page .st-toolbar-left {
            flex: 1 1 auto;
        }

        .all-tender-request-page .st-toolbar-right {
            flex: 0 0 auto;
            margin-left: auto;
        }

        .all-tender-request-page .st-table-title {
            margin: 0 8px 0 0;
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
        }

        .all-tender-request-page .st-table-toolbar .btn {
            height: 36px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
        }

        .all-tender-request-page .st-table-toolbar .btn-danger {
            background: #dc2626;
            border-color: #dc2626;
        }

        .all-tender-request-page .st-table-toolbar .btn-danger:hover {
            background: #b91c1c;
            border-color: #b91c1c;
        }

        .all-tender-request-page .st-toolbar-left .buttons-excel,
        .all-tender-request-page .st-toolbar-left .buttons-csv,
        .all-tender-request-page .st-toolbar-left .buttons-print,
        .all-tender-request-page .st-toolbar-right .btn {
            background: #fff;
            border: 1px solid #d0d5dd;
            color: #475569;
        }

        .all-tender-request-page .st-toolbar-left .buttons-excel:hover,
        .all-tender-request-page .st-toolbar-left .buttons-csv:hover,
        .all-tender-request-page .st-toolbar-left .buttons-print:hover,
        .all-tender-request-page .st-toolbar-right .btn:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .all-tender-request-page .st-toolbar-left .buttons-excel i {
            color: #16a34a;
        }

        .all-tender-request-page .st-toolbar-left .buttons-csv i {
            color: #0891b2;
        }

        .all-tender-request-page .st-toolbar-left .buttons-print i {
            color: #64748b;
        }

        /* ---------- DataTable wrapper controls ---------- */
        .all-tender-request-page .st-table-card .dataTables_wrapper > .row:first-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: nowrap;
            gap: 8px;
            margin: 0;
        }

        .all-tender-request-page .st-table-card .dataTables_wrapper > .row:first-child > div {
            width: auto !important;
            padding: 0;
            max-width: none;
        }

        .all-tender-request-page .st-table-card .dataTables_wrapper > .row:first-child > div:first-child {
            flex: 0 0 auto;
        }

        .all-tender-request-page .st-table-card .dataTables_wrapper > .row:first-child > div:last-child {
            flex: 0 0 auto;
            margin-left: auto;
        }

        .all-tender-request-page .st-table-card .dataTables_length,
        .all-tender-request-page .st-table-card .dataTables_filter {
            padding: 4px 0 12px;
        }

        .all-tender-request-page .st-table-card .dataTables_length label,
        .all-tender-request-page .st-table-card .dataTables_filter label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            white-space: nowrap;
        }

        .all-tender-request-page .st-table-card .dataTables_filter {
            text-align: right;
        }

        .all-tender-request-page .st-table-card .dataTables_length select,
        .all-tender-request-page .st-table-card .dataTables_filter input {
            height: 34px;
            border: 1px solid #d0d5dd;
            border-radius: 6px;
            background: #fff;
            font-size: 13px;
            color: #1e293b;
        }

        .all-tender-request-page .st-table-card .dataTables_length select {
            padding: 0 8px;
        }

        .all-tender-request-page .st-table-card .dataTables_filter input {
            padding: 0 10px;
            width: clamp(140px, 24vw, 260px);
            margin-left: 0;
        }

        .all-tender-request-page .st-table-card .dataTables_info {
            padding-top: 12px;
            font-size: 13px;
            color: #64748b;
        }

        .all-tender-request-page .st-table-card .dataTables_paginate {
            padding-top: 8px;
        }

        /* ---------- table ---------- */
        .all-tender-request-page .st-table-card .table {
            margin-bottom: 0;
            border-color: #e2e8f0;
        }

        .all-tender-request-page .st-table-card .table thead th {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 12px;
            font-size: 12.5px;
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
            vertical-align: middle;
        }

        .all-tender-request-page .st-table-card .table tbody td {
            padding: 8px 12px;
            font-size: 13px;
            color: #334155;
            vertical-align: middle;
            white-space: nowrap;
        }

        .all-tender-request-page .st-table-card .table tbody tr:hover td {
            background-color: #f1f5f9;
        }

        .all-tender-request-page .st-table-card .table .tender_id {
            color: #2563eb;
            font-weight: 600;
        }

        /* ---------- action links ---------- */
        .all-tender-request-page .st-table-card .st-action-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 32px;
            padding: 0 12px;
            border-radius: 6px;
            border: 1px solid #d0d5dd;
            background: #fff;
            color: #475569;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
        }

        .all-tender-request-page .st-table-card .st-action-link:hover {
            background: #f1f5f9;
            color: #0f172a;
            text-decoration: none;
        }

        .all-tender-request-page .st-table-card .st-action-link i {
            color: #64748b;
        }

        .all-tender-request-page .st-table-card .st-action-link.st-action-danger,
        .all-tender-request-page .st-table-card .st-action-link.st-action-danger i {
            color: #dc2626;
        }

        /* ---------- scrollable table + custom scrollbars ---------- */
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
        @media (max-width: 575.98px) {
            .all-tender-request-page .st-table-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .all-tender-request-page .st-toolbar-left,
            .all-tender-request-page .st-toolbar-right {
                width: 100%;
            }

            .all-tender-request-page .st-toolbar-right {
                margin-left: 0;
                justify-content: flex-end;
            }
        }

        /* ---------- final table theme (green header) ---------- */
        .all-tender-request-page .st-table-card .table thead th {
            background-color: #33cc33 !important;
            color: #ffffff !important;
            border-color: #33cc33 !important;
        }

        .all-tender-request-page .dataTables_scrollHead .table thead th:first-child {
            background-color: #33cc33 !important;
            color: #ffffff !important;
            border-color: #33cc33 !important;
        }

        .all-tender-request-page .st-table-card .table tbody .tender_id,
        .all-tender-request-page .st-table-card .table tbody a.tender_id {
            color: #33cc33 !important;
            font-weight: 600;
            text-decoration: none;
        }

        .all-tender-request-page .st-table-card .table tbody .tender_id:hover,
        .all-tender-request-page .st-table-card .table tbody a.tender_id:hover {
            color: #28a428 !important;
            text-decoration: underline;
        }

        /* Keep "Showing 1 to 100..." OUTSIDE the scrollable table */
        .all-tender-request-page .st-table-card .dataTables_info {
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

        .all-tender-request-page .st-table-card .dataTables_wrapper > .row:last-child {
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

        .all-tender-request-page .st-table-card .dataTables_wrapper {
            overflow: visible !important;
        }

        .all-tender-request-page .st-table-card .dataTables_scroll {
            overflow: visible !important;
        }

        .all-tender-request-page .st-table-card .dataTables_scrollBody {
            overflow-x: auto !important;
            overflow-y: auto !important;
        }

        /* ---------- full-width table / no right-side whitespace ---------- */
        .all-tender-request-page .dt-responsive {
            width: 100% !important;
            max-width: 100% !important;
        }

        .all-tender-request-page .dataTables_wrapper {
            width: 100% !important;
            max-width: 100% !important;
        }

        .all-tender-request-page .dataTables_scroll {
            width: 100% !important;
            max-width: 100% !important;
        }

        .all-tender-request-page .dataTables_scrollHead,
        .all-tender-request-page .dataTables_scrollBody {
            width: 100% !important;
            max-width: 100% !important;
        }

        .all-tender-request-page .dataTables_scrollHeadInner {
            width: 100% !important;
        }

        .all-tender-request-page .dataTables_scrollHeadInner table {
            width: 100% !important;
        }

        .all-tender-request-page .dataTables_scrollBody {
            overflow-x: auto !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        <div class="pcoded-content all-tender-request-page">

            <!-- KPI + Navigation (combined card) -->
            <div class="row">
                <div class="col-12">

                    <div class="card st-kpi-card">

                        <!-- KPI -->
                        <div class="card-body st-kpi-body">

                            <div class="st-kpi-icon">
                                <i class="feather icon-message-square"></i>
                            </div>

                            <div class="st-kpi-meta">

                                <span class="st-kpi-label">All Tender Request</span>

                                <span class="st-kpi-value" id="total">
                                    <?php
                                    $allTenderCountValue = 0; // Default value

                                    if ($isAdmin || hasPermission('View All Tenders Count', $privileges, $roleData['role_name'])) {
                                        $allTenderCountValue = $allTenderData['COUNT'] ?? 0;
                                    } else {
                                        $allTenderCountValue = 0;
                                    }
                                    echo $allTenderCountValue;
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
                                    All Tender Request
                                </li>

                            </ul>
                        </div>

                    </div>

                </div>
            </div>

            <!-- Table -->
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
                                    <h6 class="st-table-title">All Tender Requests</h6>
                                    <?php
                                    if ($isAdmin || hasPermission('Bulk Delete View All Tenders', $privileges, $roleData['role_name'])) {
                                        echo "<a href='javascript:void(0);' id='recycle_records' class='btn btn-danger'>
                                        <i class='feather icon-trash'></i> Move to Bin
                                        </a>";
                                    }
                                    ?>
                                    <?php if ($isAdmin || hasPermission('View All Tender Excel', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn buttons-excel" tabindex="0" aria-controls="basic-btn2"
                                            type="button" onclick="exportTableToExcel()" title="Export to Excel">
                                            <span><i class="fas fa-file-excel"></i> Excel</span>
                                        </button>
                                    <?php } ?>
                                    <?php if ($isAdmin || hasPermission('View All Tender CSV', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn buttons-csv" tabindex="0" aria-controls="basic-btn2"
                                            type="button" onclick="exportTableToCSV()" title="Export to CSV">
                                            <span><i class="fas fa-file-csv"></i> CSV</span>
                                        </button>
                                    <?php } ?>
                                    <?php if ($isAdmin || hasPermission('View All Tender Print', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn buttons-print" tabindex="0" onclick="printTable()"
                                            aria-controls="basic-btn2" type="button" title="Print">
                                            <span><i class="fas fa-print"></i> Print</span>
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="dt-responsive">
                                <table id="basic-btn2" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center">
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <label class="checkboxs mb-0">
                                                        <input type="checkbox" id="select-all">
                                                        <span class="checkmarks"></span>
                                                    </label>
                                                    <span class="sno-number">SNO</span>
                                                </div>
                                            </th>
                                            <th>Status</th>
                                            <th>Tender ID</th>
                                            <th>Tender No</th>
                                            <th>Department</th>
                                            <th>Division</th>
                                            <th>Sub-Division</th>
                                            <th>Section</th>
                                            <th>Tentative Cost</th>
                                            <th>REF.Code</th>
                                            <th>Due Date</th>
                                            <th>Add Date </th>
                                            <th>Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 1;
                                        while ($row = mysqli_fetch_assoc($resultMain)) {
                                            ?>
                                            <tr class='record'>
                                                <td class="text-center">
                                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                                        <label class="checkboxs mb-0">
                                                            <input type='checkbox' class='request_checkbox'
                                                                id='customCheck<?php echo $row['sno']; ?>'
                                                                data-request-id='<?php echo $row['userTenderId']; ?>'>
                                                            <span class="checkmarks"></span>
                                                        </label>
                                                        <span class="sno-number"><?php echo $row['sno']; ?></span>
                                                    </div>
                                                </td>

                                                <td><?php echo $row['tenderStatus']; ?></td>
                                                <td>
                                                    <a class='tender_id'
                                                        href='sent-tender3.php?tender_id=<?php echo base64_encode($row['tenderID']); ?>'>
                                                        <?php echo $row['tenderID']; ?>
                                                    </a>
                                                </td>
                                                <td><?php echo $row['tender_no']; ?></td>
                                                <td><?php echo $row['department_name']; ?></td>
                                                <td><?php echo $row['division_name']; ?></td>
                                                <td><?php echo $row['subdivision']; ?></td>
                                                <td><?php echo $row['section_name']; ?></td>
                                                <td><?php echo $row['tentative_cost']; ?></td>
                                                <td><?php echo $row['reference_code']; ?></td>

                                                <?php
                                                $dueDate = new DateTime($row['due_date']);
                                                $formattedDueDate = $dueDate->format('d-m-Y');
                                                ?>
                                                <td><?php echo $row['due_date']; ?></td>

                                                <?php
                                                $createdDate = new DateTime($row['created_at']);
                                                $formattedCreatedDate = $createdDate->format('d-m-Y H:i:s');
                                                ?>
                                                <td><?php echo $row['created_at']; ?></td>

                                                <td>
                                                    <div class="d-flex flex-column gap-2">
                                                        <?php
                                                        $res = isset($row['id']) ? base64_encode($row['id']) : '';

                                                        if ($isAdmin || hasPermission('Alot View All Tender', $privileges, $roleData['role_name'])) {
                                                            ?>
                                                            <a class="st-action-link" href='sent-edit.php?id=<?php echo $res; ?>'>
                                                                <i class='feather icon-edit'></i> Alot
                                                            </a>
                                                            <?php
                                                        }

                                                        if ($isAdmin || hasPermission('Delete View All Tenders', $privileges, $roleData['role_name'])) {
                                                            ?>
                                                            <a class="st-action-link st-action-danger recyclebutton" href='#'
                                                                id='<?php echo $row['id']; ?>'
                                                                title='Click To Delete'>
                                                                <i class='feather icon-trash'></i> Move to Bin
                                                            </a>
                                                            <?php
                                                        }
                                                        ?>
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
            //         { targets: 0, visible: true }
            //     ]
            // });

            // var table = $('#basic-btn2').DataTable({
            //     orderCellsTop: true,
            //     fixedHeader: true,
            //     columnDefs: [
            //         { targets: 0, visible: true }
            //     ]
            // });

            // // Clone the header row for filtering
            // $('#basic-btn2 thead tr').clone(true).appendTo('#basic-btn2 thead');
            // var columnsWithSearch = [3, 4, 5, 6, 8, 9, 10, 11, 13
            // ]; // Columns for filtering

            // // Add filters to the cloned header
            // $('#basic-btn2 thead tr:eq(1) th').each(function (i) {
            //     if (columnsWithSearch.includes(i) && !$(this).hasClass("noFilter")) {
            //         var title = $(this).text();
            //         var column = table.column(i); // Use the existing DataTable instance
            //         var select = $('<select class="form-control"><option value="">' + title + '</option></select>')
            //             .appendTo($(this).empty())
            //             .on('change', function () {
            //                 var val = $.fn.dataTable.util.escapeRegex($(this).val());
            //                 column
            //                     .search(val ? '^' + val + '$' : '', true, false)
            //                     .draw();
            //             });

            //         // Populate the select dropdown with unique values from the column
            //         column.data().unique().sort().each(function (d, j) {
            //             if (d) {
            //                 select.append('<option value="' + d + '">' + d + '</option>');
            //             }
            //         });
            //     } else {
            //         $(this).html('<span></span>');
            //     }
            // });

            // Optional: Hide update message after 5 seconds

            $("#updateuser").delay(5000).slideUp(300);


        });
    </script>

    <script type="text/javascript">
        $(function () {
            $(".recyclebutton").click(function () {

                var element = $(this);

                var del_id = element.attr("id");

                var info = 'id=' + del_id;
                if (confirm("Are you sure you want to delete this Record?")) {
                    $.ajax({
                        type: "GET",
                        url: "deleteuser.php",
                        data: info,
                        success: function () { }
                    });
                    $(this).parents(".record").animate({
                        backgroundColor: "#FF3"
                    }, "fast")
                        .animate({
                            opacity: "hide"
                        }, "slow");

                    setTimeout(function () {
                        window.location.reload()
                    }, 2000);
                }
                return false;
            });

            $('#recycle_records').on('click', function (e) {
                var requestIDs = [];
                $(".request_checkbox:checked").each(function () {
                    var id = $(this).data('request-id');
                    if (id !== "" && id != null) {
                        requestIDs.push(id);
                    }
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
                                data: 'alot_request_ids_bulk=' + selected_values,
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

                    // WRN_PROFILE_DELETE = "Are you sure you want to delete " + (requestIDs.length > 1 ? "these" : "this") + " Record?";
                    // var checked = confirm(WRN_PROFILE_DELETE);
                    // if (checked == true) {
                    //     var selected_values = requestIDs.join(",");
                    //     $.ajax({
                    //         type: "POST",
                    //         url: "recycleuser.php",
                    //         cache: false,
                    //         data: 'alot_request_ids=' + selected_values,
                    //         success: function () {
                    //             $(".request_checkbox:checked").each(function () {
                    //                 $(this).closest(".record").animate({
                    //                     backgroundColor: "#FF3"
                    //                 }, "fast").animate({
                    //                     opacity: "hide"
                    //                 }, "slow", function () {
                    //                     $(this).remove();
                    //                 });
                    //             });
                    //             setTimeout(function () {
                    //                 window.location.reload();
                    //             },
                    //                 2000);
                    //         }
                    //     });
                    // }
                }
            });
        });

    </script>

    <!-- <script>
    $(document).on('click', '.tender_id', function (e) {
        e.preventDefault();
        const tender_id = $(this).text();

        if (tender_id.trim() !== '') {
            // console.log("Selected Tender ID:", tender_id);
            $.ajax({
                url: 'tender-request3.php', // The PHP file that will handle the deletion
                type: 'POST',
                data: { tender_id: tender_id },
                success: function(response) {
                    // Redirect to tender-request3.php after successful AJAX request
                    window.location.href = 'tender-request3.php';
                },
                error: function(xhr, status, error) {
                    console.error("AJAX request failed:", status, error);
                }
            });
        } else {
            console.log("Tender ID is empty or invalid.");
        }
    }); 
</script>-->



    <script type="text/javascript">
        $(document).ready(function () {
            var table = $('#basic-btn2').DataTable({
                pageLength: 100,
                lengthMenu: [25, 50, 100, 200, 500, 1000], // Custom dropdown options
                /*
                 * scrollX — enables horizontal scrollbar when columns overflow.
                 * scrollY — constrains table body to ~70vh with vertical scroll.
                 * fixedHeader — keeps the <thead> pinned to top during vertical scroll.
                 * First-column freeze during horizontal scroll is handled via CSS
                 *   position:sticky (see <style> block) to avoid DOM duplication issues
                 *   that FixedColumns extension would cause with checkbox event handlers.
                 */
                scrollX: true,
                scrollY: '70vh',
                scrollCollapse: false,
                fixedHeader: true,
                autoWidth: false,
                ordering: true,
                searching: true
            });

            // Fetch the number of entries
            // var info = table.page.info();
            // var totalEntries = info.recordsTotal;

            // Display the number of entries
            // console.log('Total number of entries:', totalEntries);

            // Optionally, you can display the number of entries in an HTML element
            // $('#total').text(totalEntries);
        });
    </script>

    <script>
        function exportTableToExcel() {
            // Initialize Notyf for success and error notifications
            const notyf = new Notyf({
                position: {
                    x: "center",
                    y: "top",
                },
                types: [
                    {
                        type: "success",
                        background: "#4dc76f",
                        textColor: "#FFFFFF",
                        dismissible: false,
                        duration: 3000,
                    },
                    {
                        type: "error",
                        background: "#ff1916",
                        textColor: "#FFFFFF",
                        dismissible: false,
                        duration: 3000,
                    },
                ],
            });

            let table = $("#basic-btn2").DataTable(); // Initialize DataTables API
            let selectedRows = [];

            // Get selected invoice IDs
            let checkboxes = table
                .rows()
                .nodes()
                .to$()
                .find('input[name="invoiceIds"]:checked');
            let selectedInvoiceIds = checkboxes
                .map((i, checkbox) => checkbox.value)
                .get();

            // If no checkboxes are selected, include all rows; otherwise, filter rows
            if (selectedInvoiceIds.length === 0) {
                selectedRows = table.rows().nodes().toArray(); // Get all rows from DataTables
            } else {
                selectedRows = [table.rows().nodes().toArray()[0]]; // Include header row
                table
                    .rows()
                    .nodes()
                    .each(function (row, index) {
                        if (index === 0) return; // Skip header row to avoid duplication
                        let checkbox = $(row).find('input[name="invoiceIds"]');
                        if (checkbox.length && selectedInvoiceIds.includes(checkbox.val())) {
                            selectedRows.push(row);
                        }
                    });
            }

            // Check if there are any rows to export (excluding header)
            if (selectedRows.length <= 1) {
                notyf.error("No rows selected for export!");
                return;
            }

            // Create a new table for export
            let tempTable = document.createElement("table");
            for (let row of selectedRows) {
                tempTable.appendChild(row.cloneNode(true));
            }

            // Remove the Action column
            let actionColumnIndex = -1;
            let headerCells = tempTable.rows[0].cells;
            for (let i = 0; i < headerCells.length; i++) {
                if (headerCells[i].innerText.trim().toLowerCase() === "action") {
                    actionColumnIndex = i;
                    break;
                }
            }
            if (actionColumnIndex !== -1) {
                for (let row of tempTable.rows) {
                    if (row.cells.length > actionColumnIndex) {
                        row.deleteCell(actionColumnIndex);
                    }
                }
            }

            // Remove the checkbox column (first column) and currency symbols
            for (let row of tempTable.rows) {
                if (row.cells.length > 0) {
                    row.deleteCell(0); // Remove checkbox column
                }
                for (let cell of row.cells) {
                    cell.innerText = cell.innerText.replace(/[₹$]/g, "").trim(); // Remove currency symbols
                }
            }

            // Export to Excel
            try {
                let workbook = XLSX.utils.table_to_book(tempTable, { sheet: "Sheet1" });
                XLSX.writeFile(workbook, "tenders.xlsx");
                notyf.success("Excel file exported successfully!");
            } catch (error) {
                notyf.error("Error exporting to Excel: " + error.message);
            }


            // const table = document.getElementById("basic-btn2");
            // const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
            // XLSX.writeFile(wb, filename);
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