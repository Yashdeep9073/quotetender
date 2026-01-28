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
        ur.created_at, 
        ur.file_name2,
        ur.additional_files,
        ur.reference_code,
        ur.tentative_cost,
        ur.tender_no, 
        s.*, 
        dv.*, 
        sd.*,
         st.*, 
        ct.*  
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
        ur.created_at, 
        ur.file_name2,
        ur.additional_files,
        ur.reference_code,
        ur.tentative_cost,
        ur.tender_no, 
        s.*, 
        dv.*, 
        sd.*,
        st.*,  -- Select state name
        ct.*   -- Select city name
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
        ur.created_at, 
        ur.file_name2,
        ur.reference_code,
        ur.tentative_cost,
        ur.tender_no, 
        s.*, 
        dv.*, 
        sd.*,
         st.*, 
        ct.*  
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                                    class="feather icon-message-square float-left"></i><span id="total">


                                    <?php
                                    $sentTendersCountValue = 0; // Default value
                                    
                                    if ($isAdmin || hasPermission('Sent Tenders Count', $privileges, $roleData['role_name'])) {
                                        $sentTendersCountValue = $tenderSentCount['COUNT'] ?? 0;
                                    } else {
                                        $sentTendersCountValue = 0;
                                    }
                                    echo $sentTendersCountValue;
                                    ?>
                                </span></h2>

                        </div>
                    </div>
                </div>
            </div>
            <?php if ($isAdmin || hasPermission('Sent Tenders Filter', $privileges, $roleData['role_name'])) { ?>
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
                                            <a href="sent-tender2.php"
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
            <?php } ?>
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
                                if ($isAdmin || hasPermission('Bulk Delete Sent Tender', $privileges, $roleData['role_name'])) {
                                    echo "<a href='javascript:void(0);' id='recycle_records' class='btn btn-danger me-3 rounded-sm'> 
                                    <i class='feather icon-trash'></i> &nbsp; Move to Bin
                                    </a>&nbsp&nbsp";
                                }
                                ?>
                                <div class="dt-buttons btn-group">
                                    <?php if ($isAdmin || hasPermission('Sent Tender Excel', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn btn-secondary buttons-excel buttons-html5 btn-primary rounded-sm"
                                            tabindex="0" aria-controls="basic-btn2" type="button"
                                            onclick="exportTableToExcel()" title="Export to Excel"><span><i
                                                    class="fas fa-file-excel"></i>
                                                Excel</span></button>
                                    <?php } ?>
                                    <?php if ($isAdmin || hasPermission('Sent Tender CSV', $privileges, $roleData['role_name'])) { ?>

                                        <button class="btn btn-secondary buttons-csv buttons-html5 btn-primary rounded-sm"
                                            tabindex="0" aria-controls="basic-btn2" type="button"
                                            onclick="exportTableToCSV()" title="Export to CSV"><span><i
                                                    class="fas fa-file-csv"></i> CSV</span></button>
                                    <?php } ?>

                                    <?php if ($isAdmin || hasPermission('Sent Tender Print', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn btn-secondary buttons-print btn-primary rounded-sm" tabindex="0"
                                            onclick="printTable()" aria-controls="basic-btn2" type="button"
                                            title="Print"><span><i class="fas fa-print"></i> Print</span></button>
                                    <?php } ?>
                                </div>

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

                                            $createdDate = new DateTime($row['created_at']);
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
                                                        <?php if ($isAdmin || hasPermission('Sent Tender Print', $privileges, $roleData['role_name'])) { ?>
                                                            <a class='tender_id'
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
                                                        <button class="btn btn-secondary " type="button"
                                                            id="actionMenu<?php echo $row['t_id']; ?>"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="feather icon-more-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu"
                                                            aria-labelledby="actionMenu<?php echo $row['id']; ?>">

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
                responsive: true,
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
</body>

</html>