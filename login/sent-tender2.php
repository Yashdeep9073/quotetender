<?php

ini_set('display_errors', 1);

session_start();


if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}
$name = $_SESSION['login_user'];

include("db/config.php");



$adminID = $_SESSION['login_user_id'];
$adminPermissionQuery = "SELECT nm.title FROM admin_permissions ap 
inner join navigation_menus nm on ap.navigation_menu_id = nm.id where ap.admin_id='" . $adminID . "' and ap.navigation_menu_id=1 ";
$adminPermissionResult = mysqli_query($db, $adminPermissionQuery);
$allowDelete = mysqli_num_rows($adminPermissionResult) > 0 ? true : false;

$adminID = $_SESSION['login_user_id'];
$adminPermissionQuery = "SELECT nm.title FROM admin_permissions ap 
inner join navigation_menus nm on ap.navigation_menu_id = nm.id where ap.admin_id='" . $adminID . "'";
$adminPermissionResult = mysqli_query($db, $adminPermissionQuery);
$userPermissions2 = [];
while ($row = mysqli_fetch_row($adminPermissionResult)) {
    $userPermissions2[] = $row[0];
}
$allowedAction = !in_array('All', $userPermissions2) && in_array('Update Tenders', $userPermissions2) ? 'update' :
    (!in_array('All', $userPermissions2) && in_array('View Tenders', $userPermissions2) ? 'view' : 'all');


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['department-search']) || isset($_POST['section-search']) || isset($_POST['division-search']) || isset($_POST['sub-division-search  '])) {
    // Initialize $conditions as an empty array
    $conditions = [];

    // Sanitize inputs
    $departmentId = filter_input(INPUT_POST, 'department-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $sectionId = filter_input(INPUT_POST, 'section-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $divisionId = filter_input(INPUT_POST, 'division-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $subDivisionId = filter_input(INPUT_POST, 'sub-division-search', FILTER_SANITIZE_SPECIAL_CHARS);

    // Set the sanitized data in the session
    $_SESSION['departmentIdSentTender'] = $departmentId;
    $_SESSION['sectionIdSentTender'] = $sectionId;
    $_SESSION['divisionIdSentTender'] = $divisionId;
    $_SESSION['subDivisionIdSentTender'] = $subDivisionId;

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
        ur.created_at, 
        ur.file_name2,
        ur.reference_code,
        ur.tentative_cost,
        ur.tender_no, 
        s.*, 
        dv.*, 
        sd.*
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
        department.department_name, 
        ur.due_date, 
        ur.file_name, 
        ur.tenderID, 
        ur.created_at, 
        ur.file_name2,
        ur.reference_code,
        ur.tentative_cost,
        ur.tender_no, 
        s.*, 
        dv.*, 
        sd.*
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
    ORDER BY ur.created_at ASC;
";

    $resultMain = mysqli_query($db, $queryMain);
}

// Check if the page reload condition is met
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Remove specific session variables
    unset($_SESSION['departmentIdSentTender']);
    unset($_SESSION['sectionIdSentTender']);
    unset($_SESSION['divisionIdSentTender']);
    unset($_SESSION['subDivisionIdSentTender']);

}





$adminID = $_SESSION['login_user_id'];
$adminPermissionQuery = "SELECT nm.title FROM admin_permissions ap 
inner join navigation_menus nm on ap.navigation_menu_id = nm.id where ap.admin_id='" . $adminID . "' ";
$adminPermissionResult = mysqli_query($db, $adminPermissionQuery);

$permissions = [];
while ($item = mysqli_fetch_row($adminPermissionResult)) {
    array_push($permissions, $item[0]);
}

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
                                <h5 class="m-b-10">Sent Tender</h5>
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
            </div>
            <div class="row">
                <div class="col-md-6 col-xl-3">
                    <div class="card bg-c-green order-card">
                        <div class="card-body">
                            <h6 class="text-white">Sent Tenders</h6>
                            <h2 class="text-right text-white"><i
                                    class="feather icon-message-square float-left"></i><span id="total"></span></h2>

                        </div>
                    </div>
                </div>
            </div>
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <!-- Filters Section -->
                            <form method="post" id="filterForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="faculty">Department <span class="text-danger">*</span></label>
                                            <select class="form-control" name="department-search"
                                                id="department-search">
                                                <option value="0">All</option>
                                                <?php foreach ($departments as $department) { ?>
                                                    <option value="<?php echo $department['department_id']; ?>" <?php echo isset($_SESSION['departmentIdSentTender']) && $_SESSION['departmentIdSentTender'] == $department['department_id'] ? 'selected' : ''; ?>>
                                                        <?php echo $department['department_name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <div class="invalid-feedback">Please select a faculty.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="program">Section <span class="text-danger">*</span></label>
                                            <select class="form-control" name="section-search" id="section-search">
                                                <option value="0">All</option>
                                                <?php foreach ($sections as $section) { ?>
                                                    <option value="<?php echo $section['section_id']; ?>" <?php echo isset($_SESSION['sectionIdSentTender']) && $_SESSION['sectionIdSentTender'] == $section['section_id'] ? 'selected' : ''; ?>>
                                                        <?php echo $section['section_name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <div class="invalid-feedback">Please select a program.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="session">Division <span class="text-danger">*</span></label>
                                            <select class="form-control" name="division-search" id="division-search">
                                                <option value="0">All</option>
                                                <option
                                                    value="<?php echo isset($_SESSION['divisionIdSentTender']) && $_SESSION['divisionIdSentTender'] ? $_SESSION['divisionIdSentTender'] : ''; ?>"
                                                    <?php echo isset($_SESSION['divisionIdSentTender']) ? 'selected' : ''; ?>>
                                                </option>
                                            </select>
                                            <div class="invalid-feedback">Please select a session.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="semester">Sub Division <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control" name="sub-division-search"
                                                id="sub-division-search" required>
                                                <option value="0">All</option>
                                                <option
                                                    value="<?php echo isset($_SESSION['subDivisionIdSentTender']) && $_SESSION['subDivisionIdSentTender'] == $_SESSION['subDivisionIdSentTender'] ? 'selected' : ''; ?> ?>"
                                                    <?php echo isset($_SESSION['subDivisionIdSentTender']) ? 'selected' : ''; ?>>
                                                </option>
                                            </select>
                                            <div class="invalid-feedback">Please select a semester.</div>
                                        </div>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="col-md-6 col-sm-12 d-flex align-items-center mt-3">
                                        <!-- Submit Button -->
                                        <button type="submit" class="btn btn-primary btn-md d-flex align-items-center">
                                            <i class="fas fa-search" style="margin-right: 8px;"></i> Search
                                        </button>
                                        &nbsp;
                                        <!-- Reset Button -->
                                        <button type="reset" class="btn btn-primary btn-md d-flex align-items-center"
                                            id="filterResetButton">
                                            <i class="fas fa-undo" style="margin-right: 8px;"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </form>


                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header table-card-header">
                        </div>
                        <div class="card-body">
                            <div class="dt-responsive table-responsive">

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
                                <br />
                                <?php
                                if ((in_array('All', $permissions)) || (in_array('Tender Request', $permissions)) || (in_array('Recycle Bin', $permissions))) {
                                    echo "<a href='javascript:void(0);' id='recycle_records' class='btn btn-danger me-3 rounded-sm'> 
                                    <i class='feather icon-trash'></i> &nbsp; Move to Bin
                                    </a>&nbsp&nbsp";
                                }
                                if ((in_array('All', $permissions)) || (in_array('Update Tenders', $permissions)) || (in_array('Tender Request', $permissions))) {
                                    echo "<a href='javascript:void(0);' class='update_records'><button type='button' class='btn btn-warning me-3 rounded-sm'>
                                    <i class='feather icon-edit'></i> &nbsp; Update
                                    </button></a>
                                    ";
                                } ?>
                                <div class="dt-buttons btn-group">
                                    <button class="btn btn-secondary buttons-excel buttons-html5 btn-primary rounded-sm"
                                        tabindex="0" aria-controls="basic-btn2" type="button"
                                        onclick="exportTableToExcel()" title="Export to Excel"><span><i
                                                class="fas fa-file-excel"></i>
                                            Excel</span></button>
                                    <button class="btn btn-secondary buttons-csv buttons-html5 btn-primary rounded-sm"
                                        tabindex="0" aria-controls="basic-btn2" type="button"
                                        onclick="exportTableToCSV()" title="Export to CSV"><span><i
                                                class="fas fa-file-csv"></i> CSV</span></button>
                                    <button class="btn btn-secondary buttons-copy buttons-html5 btn-primary rounded-sm"
                                        tabindex="0" aria-controls="basic-btn2" type="button"
                                        title="Copy to clipboard"><span><i class="fas fa-copy"></i> Copy</span></button>
                                    <button class="btn btn-secondary buttons-print btn-primary rounded-sm" tabindex="0"
                                        onclick="printTable()" aria-controls="basic-btn2" type="button"
                                        title="Print"><span><i class="fas fa-print"></i> Print</span></button>
                                </div>
                                <?php

                                echo '<table id="basic-btn2" class="table table-striped table-bordered">';
                                echo "<thead>";
                                echo "<tr>";
                                echo "<th>SNO</th>";
                                echo "<th>Tender ID</th>";
                                echo "<th>Tender No</th>";
                                echo "<th>Department</th>";
                                echo "<th>Section</th>";
                                echo "<th>Division</th>";
                                echo "<th>Sub-Division</th>";
                                echo "<th>Tentative Cost</th>";
                                echo "<th>REF.Code</th>";
                                echo "<th>Due Date</th>";
                                echo "<th>Add Date </th>";

                                echo "<th>Edit</th>";
                                echo "</tr>";
                                echo "</thead>";
                                ?>
                                <?php
                                $count = 1;
                                echo "<tbody>";
                                while ($row = mysqli_fetch_assoc($resultMain)) {


                                    echo "<tr class='record'>";
                                    echo "<td><div class='custom-control custom-checkbox'>
                                    <input type='checkbox' class='custom-control-input request_checkbox' id='customCheck" . $row['sno'] . "' data-request-id='" . $row['t_id'] . "'>
                                    <label class='custom-control-label' for='customCheck" . $row['sno'] . "'>" . $row['sno'] . "</label>
                                    </div>
                                    </td>";

                                    echo "<td><a class='tender_id' href='sent-tender3.php?tender_id=" . base64_encode($row['tenderID']) . "'>" . $row['tenderID'] . "</a></td>";
                                    echo "<td>" . $row['tender_no'] . "</td>";
                                    echo "<td>" . $row['department_name'] . "</td>";
                                    echo "<td>" . $row['section_name'] . "</td>";
                                    echo "<td>" . $row['division_name'] . "</td>";
                                    echo "<td>" . $row['subdivision'] . "</td>";
                                    echo "<td>" . $row['tentative_cost'] . "</td>";
                                    echo "<td>" . $row['reference_code'] . "</td>";
                                    $dueDate = new DateTime($row['due_date']);
                                    $formattedDueDate = $dueDate->format('d-m-Y');
                                    echo "<td>" . $row['due_date'] . "</td>";
                                    $createdDate = new DateTime($row['created_at']);
                                    $formattedCreatedDate = $createdDate->format('d-m-Y H:i:s');
                                    echo "<td>" . $row['created_at'] . "</td>";

                                    $res = $row['t_id'];
                                    $res = base64_encode($res);

                                    if ($allowedAction == 'all' || $allowedAction == 'update') {
                                        echo "<td>  <a href='sent-edit.php?id=$res'><button type='button' class='btn btn-warning rounded-sm'><i class='feather icon-edit'></i>
                                    &nbsp;Alot</button></a>  &nbsp;";
                                    }

                                    echo "<br/>";
                                    echo "<br/>";

                                    if ((in_array('All', $permissions)) || in_array('Recycle Bin', $permissions)) {
                                        echo "<a href='javascript:void(0);' id='" . $row['t_id'] . "' data-tender-id='" . $row['t_id'] . "' class='recyclebutton  btn btn-danger rounded-sm' title='Click To Delete'> 
                                    <i class='feather icon-trash'></i>  &nbsp; Move to Bin</a>
                                    </td>";
                                    }
                                    echo "</tr>";
                                    $count++;
                                }
                                echo "</tfoot>";
                                echo "</table>";
                                ?>
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

    <script>
        $(document).ready(function () {

            $(".recyclebutton").on('click', function (e) {

                let element = $(this);
                let del_id = element.attr("id");
                let info = 'id=' + del_id;

                if (confirm("Are you sure you want to delete this Record?")) {
                    $.ajax({
                        type: "GET",
                        url: "recycleuser.php",
                        data: info,
                        success: function (response) {
                            $(this).parents(".record").animate({
                                backgroundColor: "#FF3"
                            }, "fast")
                                .animate({
                                    opacity: "hide"
                                }, "slow");
                            setTimeout(function () { window.location.reload(); }, 2000);
                        }
                    });
                }
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
                alert("Please select records.");
            } else {
                WRN_PROFILE_DELETE = "Are you sure you want to delete " + (requestIDs.length > 1 ? "these" : "this") + " Record?";
                var checked = confirm(WRN_PROFILE_DELETE);
                if (checked == true) {
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
            }
        });

    </script>

    <script type="text/javascript">
        $(document).ready(function () {
            // Initialize the DataTable with buttons
            var table = $('#basic-btn2').DataTable();
            // Fetch the number of entries
            var info = table.page.info();
            var totalEntries = info.recordsTotal;
            // Display the number of entries
            $('#total').text(totalEntries);
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



            // Fetch session values from the `sessionData` variable
            var departmentId = sessionData.departmentId;
            var sectionId = sessionData.sectionId;
            var divisionId = sessionData.divisionId;
            var subDivisionId = sessionData.subDivisionId;

            // Check if the values are available and handle them as needed
            if (departmentId) {
                console.log("Department ID:", departmentId);
                // Example: Set the value of a dropdown or input
                $('#department-search').val(departmentId);
            }

            if (sectionId) {
                console.log("Section ID:", sectionId);
                $('#section-search').val(sectionId);
            }

            if (divisionId) {

                console.log("Division ID:", divisionId);
                $.ajax({
                    url: 'fetch-division-data-sent-tender.php',
                    type: 'POST',
                    data: { divisionIdSentTender: divisionId },
                    success: function (response) {
                        if (response.success) {
                            console.log(response.success);
                            // console.log(response.divisionName);

                            // Clear existing options except the default "All" option
                            $('#division-search').empty();

                            // Add new options based on the response.divisionId and response.divisionName arrays
                            response.divisionId.forEach((id, index) => {
                                let divisionName = response.divisionName[index];
                                $('#division-search').append(new Option(divisionName, id));
                            });
                            // Select the fetched division
                            $('#division-search').val(divisionId);

                        } else {
                            console.error(response.error);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
                // $('#division-search').val(divisionId);

            }

            if (subDivisionId) {
                console.log("Sub-Division ID:", subDivisionId);
                $.ajax({
                    url: 'fetch-sub-division-data-sent-tender.php',
                    type: 'POST',
                    data: { subDivisionIdSentTender: subDivisionId },
                    success: function (response) {
                        if (response.success) {
                            console.log(response.subDivisionName);

                            // Clear existing options except the default "All" option
                            $('#sub-division-search').empty();

                            // Add new options based on the response.divisionId and response.divisionName arrays
                            response.subDivisionId.forEach((id, index) => {
                                let subDivisionName = response.subDivisionName[index];
                                $('#sub-division-search').append(new Option(subDivisionName, id));
                            });
                            // Select the fetched division
                            $('#sub-division-search').val(subDivisionId);

                        } else {
                            console.error(response.error);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
                // $('#sub-division-search').val(subDivisionId);
            }

            // Reset Filter
            $('#filterResetButton').click(function () {
                sessionStorage.clear();
                location.reload();
            });

        });
    </script>

    <script>
        // PHP exposes session values to JavaScript
        let sessionData = <?php echo json_encode([
            'departmentId' => $_SESSION['departmentIdSentTender'] ?? null,
            'sectionId' => $_SESSION['sectionIdSentTender'] ?? null,
            'divisionId' => $_SESSION['divisionIdSentTender'] ?? null,
            'subDivisionId' => $_SESSION['subDivisionIdSentTender'] ?? null
        ]); ?>;

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
</body>

</html>