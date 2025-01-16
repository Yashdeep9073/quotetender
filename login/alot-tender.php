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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['department-search']) || isset($_POST['section-search']) || isset($_POST['division-search']) || isset($_POST['sub-division-search  '])) {
    // Initialize $conditions as an empty array
    $conditions = [];

    // Sanitize inputs
    $departmentId = filter_input(INPUT_POST, 'department-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $sectionId = filter_input(INPUT_POST, 'section-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $divisionId = filter_input(INPUT_POST, 'division-search', FILTER_SANITIZE_SPECIAL_CHARS);
    $subDivisionId = filter_input(INPUT_POST, 'sub-division-search', FILTER_SANITIZE_SPECIAL_CHARS);

    // Set the sanitized data in the session
    $_SESSION['departmentId'] = $departmentId;
    $_SESSION['sectionId'] = $sectionId;
    $_SESSION['divisionId'] = $divisionId;
    $_SESSION['subDivisionId'] = $subDivisionId;

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
            sm.city_state
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
    MAX(dv.division_name) AS division_name,
    MAX(se.section_name) AS section_name,
    MAX(sd.subdivision) AS subdivision,
    ur.tentative_cost,
    sm.city_state
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



    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

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
                                <h5 class="m-b-10">Orders
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
                    <div class="card bg-c-red order-card">
                        <div class="card-body">
                            <h6 class="text-white">Alot Tender</h6>
                            <h2 class="text-right text-white"><i class="feather icon-home float-left"></i><span
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
                            <form method="post" id="filterForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="faculty">Department <span class="text-danger">*</span></label>
                                            <select class="form-control" name="department-search" id="department-search">
                                                <option value="0">All</option>
                                                <?php foreach ($departments as $department) { ?>
                                                    <option value="<?php echo $department['department_id']; ?>" 
                                                        <?php echo isset($_SESSION['departmentId']) && $_SESSION['departmentId'] == $department['department_id'] ? 'selected' : ''; ?>>
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
                                                    <option value="<?php echo $section['section_id']; ?>" 
                                                        <?php echo isset($_SESSION['sectionId']) && $_SESSION['sectionId'] == $section['section_id'] ? 'selected' : ''; ?>>
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
                                                    <option value="<?php echo $division['division_id']; ?>" 
                                                        <?php echo isset($_SESSION['divisionId']) && $_SESSION['divisionId'] == $division['division_id'] ? 'selected' : ''; ?>>
                                                        <?php echo $division['division_name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <div class="invalid-feedback">Please select a session.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="semester">Sub Division <span class="text-danger">*</span></label>
                                            <select class="form-control" name="sub-division-search" id="sub-division-search" required>
                                                <option value="0">All</option>
                                                <?php foreach ($subDivisions as $subDivision) { ?>
                                                    <option value="<?php echo $subDivision['id']; ?>" 
                                                        <?php echo isset($_SESSION['subDivisionId']) && $_SESSION['subDivisionId'] == $subDivision['id'] ? 'selected' : ''; ?>>
                                                        <?php echo $subDivision['name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <div class="invalid-feedback">Please select a semester.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label> <!-- Empty label for spacing -->
                                            <button type="submit" class="btn btn-primary btn-md d-flex align-items-center">
                                                <i class="fas fa-search" style="margin-right: 8px;"></i> Search
                                            </button>
                                        </div>
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
                                    echo "<div class='col-md row'>
                                <a href='#' id='recycle_records' class='btn btn-danger rounded-sm'> <i class='feather icon-trash'></i>  &nbsp;
                                Move to Bin Selected Items</a>
                                </div> <br />";
                                }

                                echo '<table id="basic-btn2" class="table table-striped table-bordered">';
                                echo "<thead>";
                                echo "<tr>";
                                echo "<th>SNO</th>";
                                echo "<th>User</th>";
                                echo "<th>Email</th>";
                                echo "<th>Firm</th>";
                                echo "<th>City</th>";
                                echo "<th>Mobile</th>";
                                echo "<th>Tender ID</th>";
                                echo "<th>Ref. Code </th>";
                                echo "<th>Tender No</th>";
                                echo "<th>Department</th>";
                                echo "<th>Section</th>";
                                echo "<th>Division</th>";
                                echo "<th>Sub-division</th>";
                                echo "<th class='work-name'>Work Name</th>";
                                echo "<th>Tentative Cost</th>";
                                echo "<th>Reminder</th>";
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
                                    <input type='checkbox' class='custom-control-input request_checkbox' id='customCheck" . $count . "' data-request-id='" . $row['t_id'] . "'>
                                    <label class='custom-control-label' for='customCheck" . $count . "'>" . $count . "</label>
                                    </div>
                                    </td>";

                                    echo "<td><span style='color:red;'> " . $row['name'] . " </span></td>";
                                    echo "<td>  <span style='color:green;'>" . $row['email_id'] . " </span></td>";
                                    echo "<td>" . $row['firm_name'] . "</td>";
                                    echo "<td>" . $row['city_state'] . "</td>";
                                    echo "<td>" . $row['mobile'] . "</td>";
                                    echo "<td>" . $row['tenderID'] . "</td>";
                                    echo "<td>" . $row['reference_code'] . "</td>";
                                    echo "<td>" . $row['tender_no'] . "</td>";
                                    echo "<td>" . $row['department_name'] . "</td>";
                                    echo "<td>" . $row['section_name'] . "</td>";
                                    echo "<td>" . $row['division_name'] . "</td>";
                                    echo "<td>" . $row['subdivision'] . "</td>";
                                    echo "<td style='white-space:pre-wrap; word-wrap:break-word; max-width:20rem;'>" . $row['name_of_work'] . "</td>";
                                    echo "<td style='white-space:pre-wrap; word-wrap:break-word; max-width:20rem;'>" . $row['tentative_cost'] . " rupees /-</td>";

                                    echo "<td>" . "<span class='btn btn-success'>" . $row['reminder_days'] . " days</span>" . "<br/><br/>" .
                                        "Aloted Date :" . "<br/>" . date_format(date_create($row['allotted_at']), "d-m-Y ") . "<br/>" . '<a href="../login/tender/' . $row['file_name'] .
                                        '"  target="_blank"/>View file 1</a> </br>';
                                    if (!empty($row['file_name2'])) {
                                        echo '<a href="../login/tender/' . $row['file_name2'] . '" target="_blank"/>View File 2 </a>' . "</td>";
                                    } else {
                                        echo "</td>";
                                    }

                                    $res = $row['t_id'];
                                    $res = base64_encode($res);

                                    echo "<td>  <a href='alot-tender-update.php?id=$res'><button type='button' class='btn btn-warning'>
                                    <i class='feather icon-edit'></i> &nbsp;Re-Alot</button></a>  &nbsp;";
                                    echo "<br/>";
                                    echo "<br/>";
                                    if ($allowDelete == true || (in_array('All', $permissions)) || (in_array('Recycle Bin', $permissions))) {
                                        echo "<a href='javascript:void(0);' id='" . $row['t_id'] . "'class='recyclebutton btn btn-danger' title='Click To Delete'> 
                                    <i class='feather icon-trash'></i>  &nbsp; Move Bin</a></td>";
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


    <script type="text/javascript">
        $(".recyclebutton").on('click', function () {

            var element = $(this);

            var del_id = element.attr("id");

            var info = 'id=' + del_id;
            console.log(`Data : ${info}`);

            if (confirm("Are you sure you want to delete this Record?")) {
                $.ajax({
                    type: "GET",
                    url: "recycleuser.php",
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
        });

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
            var table = $('#basic-btn2').DataTable({
                dom: 'Bfrtip', // Enable the buttons layout
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-primary rounded-sm',
                        titleAttr: 'Export to Excel'
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-primary rounded-sm',
                        titleAttr: 'Export to CSV'
                    },
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> Copy',
                        className: 'btn btn-primary rounded-sm',
                        titleAttr: 'Copy to clipboard'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-primary rounded-sm',
                        titleAttr: 'Print'
                    }
                ]


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

             // Fetch session values from the `sessionData` variable
             var departmentId = sessionData.departmentId;
            var sectionId = sessionData.sectionId;
            var divisionId = sessionData.divisionId;
            var subDivisionId = sessionData.subDivisionId;

            // Check if the values are available and handle them as needed
            if (departmentId) {
                // Example: Set the value of a dropdown or input
                $('#department-search').val(departmentId);
            }

            if (sectionId) {
                $('#section-search').val(sectionId);
            }

            if (divisionId) {
                $('#division-search').val(divisionId);
            }

            if (subDivisionId) {
                $('#sub-division-search').val(subDivisionId);
            }

        });
    </script>

        <script>
                // PHP exposes session values to JavaScript
                var sessionData = <?php echo json_encode([
                    'departmentId' => $_SESSION['departmentId'] ?? null,
                    'sectionId' => $_SESSION['sectionId'] ?? null,
                    'divisionId' => $_SESSION['divisionId'] ?? null,
                    'subDivisionId' => $_SESSION['subDivisionId'] ?? null
                ]); ?>;
            </script>
        <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>

</body>

</html>