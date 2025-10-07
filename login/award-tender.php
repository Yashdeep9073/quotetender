<?php

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
    $conditions[] = "ur.remark = 'accepted'";
    $conditions[] = "ur.delete_tender = '0'";

    // Construct the WHERE clause dynamically
    $whereClause = "WHERE " . implode(' AND ', $conditions);

    $query = "SELECT DISTINCT
    sm.name, 
    m.email_id, 
    m.mobile, 
    m.firm_name, 
    ur.tender_no, 
    department.department_name,
    ur.name_of_work,
    ur.remarked_at, 
    ur.file_name, 
    ur.id as t_id, 
    se.section_name,
    dv.division_name,
    sd.subdivision,
    ur.tenderID,
    ur.remark,
    ur.reference_code,
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
    ur.id, 
    sm.name, 
    m.email_id, 
    m.mobile, 
    m.firm_name, 
    ur.tender_no, 
    department.department_name,
    ur.name_of_work,
    ur.remarked_at, 
    ur.file_name, 
    se.section_name,
    dv.division_name,
    sd.subdivision,
    ur.tenderID
ORDER BY 
    NOW() >= CAST(ur.due_date AS DATE), 
    CAST(ur.remarked_at AS DATE) ASC, 
    ABS(DATEDIFF(NOW(), CAST(ur.due_date AS DATE)));

 ";

    $result = mysqli_query($db, $query);
    if (!$result) {
        die("Query Error: " . mysqli_error($db));
    }

} else {
    $query = "SELECT DISTINCT
    sm.name, 
    m.email_id, 
    m.mobile, 
    m.firm_name, 
    ur.tender_no, 
    department.department_name,
    ur.name_of_work,
    ur.remarked_at, 
    ur.file_name, 
    ur.id as t_id,
    se.section_name,
    dv.division_name,
    sd.subdivision,
    ur.tenderID,
    ur.remark,
    ur.reference_code,
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
    ur.remark = 'accepted' AND ur.delete_tender = '0'
GROUP BY 
    ur.id, 
    sm.name, 
    m.email_id, 
    m.mobile, 
    m.firm_name, 
    ur.tender_no, 
    department.department_name,
    ur.name_of_work,
    ur.remarked_at, 
    ur.file_name, 
    se.section_name,
    dv.division_name,
    sd.subdivision,
    ur.tenderID
ORDER BY 
    NOW() >= CAST(ur.due_date AS DATE), 
    CAST(ur.remarked_at AS DATE) ASC, 
    ABS(DATEDIFF(NOW(), CAST(ur.due_date AS DATE)));

 ";

    $result = mysqli_query($db, $query);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Remove specific session variables
    unset($_SESSION['departmentIdAwardTender']);
    unset($_SESSION['sectionIdAwardTender']);
    unset($_SESSION['divisionIdAwardTender']);
    unset($_SESSION['subDivisionIdAwardTender']);

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

try {

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

} catch (\Throwable $th) {
    //throw $th;
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


?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Award </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
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
                                <h5 class="m-b-10"> List of Award Tender
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
                    <div class="card bg-c-yellow order-card">
                        <div class="card-body">
                            <h6 class="text-white">Award Tender</h6>
                            <h2 class="text-right text-white"><i class="feather icon-award float-left"></i><span
                                    id="category"></span></h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <!-- Filters Section -->
                            <form method="get" id="filterForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="faculty">Department <span class="text-danger">*</span></label>
                                            <select class="form-control" name="department-search"
                                                id="department-search">
                                                <option value="0">All</option>
                                                <?php foreach ($departments as $department) { ?>
                                                    <option value="<?php echo $department['department_id']; ?>" <?php echo isset($_GET['department-search']) && $_GET['department-search'] == $department['department_id'] ? 'selected' : ''; ?>>
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
                                                <?php foreach ($sections as $section) {
                                                    $selectedSection = (isset($_GET['section-search']) && urldecode($_GET['section-search']) == $section['section_id']) ? 'selected' : '';

                                                    ?>
                                                    <option <?= $selectedSection ?>
                                                        value="<?php echo $section['section_id']; ?>">
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
                                                <?php foreach ($divisions as $division) { ?>
                                                    <option value="<?php echo $division['division_id']; ?>" <?php echo isset($_GET['division-search']) && $_GET['division-search'] == $division['division_id'] ? 'selected' : ''; ?>>
                                                        <?php echo $division['division_name']; ?>
                                                    </option>
                                                <?php } ?>
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

                                            </select>
                                            <div class="invalid-feedback">Please select a semester.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="semester">Firm <span class="text-danger">*</span></label>
                                            <select class="form-control select-firm" name="firm" required>
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
                                            <div class="invalid-feedback">Please select a semester.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="semester">State <span class="text-danger">*</span></label>
                                            <select class="form-control select-state" name="state" required>
                                                <option value="0">All</option>
                                                <?php foreach ($states as $state) {
                                                    $selectedState = (isset($_GET['state']) && urldecode($_GET['state']) == $state['state_code']) ? 'selected' : '';
                                                    ?>
                                                    <option value="<?= $state['state_code'] ?>" <?= $selectedState ?>>
                                                        <?= $state['state_name'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <div class="invalid-feedback">Please select a semester.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="semester">City <span class="text-danger">*</span></label>
                                            <select class="form-control select-city" name="city">
                                                <option value="0">All</option>
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
                                        <a href="award-tender.php"
                                            class="btn btn-primary btn-md d-flex align-items-center"
                                            id="filterResetButton">
                                            <i class="fas fa-undo" style="margin-right: 8px;"></i>
                                            Reset
                                        </a>
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
                                if ($allowDelete == true || (in_array('All', $permissions)) || (in_array('Recycle Bin', $permissions))) {
                                    echo "
                                <a href='#' id='recycle_records' class='btn btn-danger me-3 rounded-sm'> <i class='feather icon-trash'></i>  &nbsp;
                                Move to Bin</a>&nbsp&nbsp&nbsp&nbsp
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

                                echo '<table id="basic-btn3" class="table table-striped table-bordered nowrap">';
                                echo "<thead>";
                                echo "<tr>";
                                echo '<th><label class="checkboxs">
                                    <input type="checkbox" id="select-all">
                                    <span class="checkmarks"></span>
                                </label>  SNO</th>';
                                echo "<th>User</th>";
                                echo "<th>State & City</th>";
                                echo "<th>Tender No</th>";
                                echo "<th>Tender ID</th>";
                                echo "<th>Reference No</th>";
                                echo "<th>Department</th>";
                                echo "<th>Section</th>";
                                echo "<th>Division</th>";
                                echo "<th>Sub-Division</th>";
                                echo "<th>Work Name</th>";

                                echo "<th>Awarded At</th>";


                                echo "<th>Status</th>";
                                echo "<th>Action</th>";


                                echo "</tr>";
                                echo "</thead>";


                                ?>
                                <?php



                                $count = 1;

                                echo "<tbody>";

                                while ($row = mysqli_fetch_row($result)) {

                                    echo "<tr class='record'>";
                                    echo "<td><div class='custom-control custom-checkbox'>
                                    <input type='checkbox' class='custom-control-input request_checkbox' id='customCheck" . $count . "' data-request-id='" . $row['9'] . "'>
                                    <label class='custom-control-label' for='customCheck" . $count . "'>" . $count . "</label>
                                    </div>
                                    </td>";

                                    echo "<td>Name -" . $row['0'] . "<br/> " . "<span style=''>Mail - " . $row['1'] . "</span>" . "<br/>"
                                        . "<span style=''>M.No - " . $row['2'] . "</span>" . "<br/>" . "<span style=''>Firm - "
                                        . $row['3'] . "</span>" . "</td>";
                                    echo "<td>State - " . $row['16'] . "<br/> " . "<span style=''>City -  " . $row['17'] . "</span>" . "<br/>" . "</td>";
                                    echo "<td>" . $row['4'] . "</td>";
                                    echo "<td>" . $row['13'] . "</td>";
                                    echo "<td>" . $row['15'] . "</td>";
                                    echo "<td>" . $row['5'] . "</td>";
                                    echo "<td>" . $row['10'] . "</td>";
                                    echo "<td>" . $row['11'] . "</td>";
                                    echo "<td>" . $row['12'] . "</td>";

                                    echo "<td>" . $row['6'] . "</td>";


                                    echo "<td>" . "Award Date :" . "<br/>" . date_format(date_create($row['7']), "d-m-Y h:i A") . "<br/>" . '<a href="../login/tender/' . $row['8'] . '"  target="_blank"/>View file </a>' . "</td>";

                                    echo "<td>" . $row['14'] . "</td>";
                                    $res = $row[9];
                                    $res = base64_encode($res);
                                    echo "<td>  <a href='award-edit.php?award=$res'><button type='button' class='btn btn-warning'>
                                    <i class='feather icon-edit'></i> &nbsp;Edit Status</button></a><br/></br/> <a href='#'>
                                    <button type='button' class='btn btn-success'><i class='feather icon-edit'></i> &nbsp;Awarded
                                    </button></a> </td>";




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
            console.log(`Data : ${info}`);
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

        $('#recycle_records').on('click', function (e) {
            let requestIDs = [];
            $(".request_checkbox:checked").each(function () {
                requestIDs.push($(this).data('request-id'));
            });
            // console.log(`TenderId - ${requestIDs}`);
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
                            data: 'award_request_ids=' + selected_values,
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

    <script type="text/javascript">
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


            // Initialize the DataTable with buttons
            var table = $('#basic-btn3').DataTable({
                pageLength: 100,
                lengthMenu: [25, 50, 100, 200, 500, 1000], // Custom dropdown options
                responsive: true,
                ordering: true,
                searching: true
            });

            // Fetch the number of entries
            var info = table.page.info();
            var totalEntries = info.recordsTotal;

            // Display the number of entries
            $('#category').text(totalEntries);

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
                            }, 700);


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
            const tableClone = document.getElementById("basic-btn3").cloneNode(true);

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
            const table = document.getElementById("basic-btn3");
            const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
            XLSX.writeFile(wb, filename);
        }
    </script>

    <script>
        function exportTableToCSV(tableId, filename = 'table.csv') {
            const table = document.getElementById("basic-btn3");
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