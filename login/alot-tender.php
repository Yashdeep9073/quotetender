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

$queryMain = "SELECT DISTINCT
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
    ur.id,
    ur.reference_code,
    ur.tenderID,
    ur.file_name2,
    MAX(dv.division_name) AS division_name,
    MAX(se.section_name) AS section_name,
    MAX(sd.subdivision) AS subdivision,
    ur.tentative_cost
FROM
    user_tender_requests ur
INNER JOIN
    members m ON ur.member_id = m.member_id
INNER JOIN
    department ON ur.department_id = department.department_id
INNER JOIN
    section se ON ur.section_id = se.section_id
INNER JOIN
    members sm ON ur.selected_user_id = sm.member_id
INNER JOIN
    division dv ON dv.section_id = ur.section_id
INNER JOIN
    sub_division sd ON ur.division_id = sd.division_id
WHERE
    ur.status = 'Allotted' AND ur.delete_tender = '0'
GROUP BY
    ur.id
ORDER BY
    NOW() >= CAST(ur.due_date AS DATE),
    CAST(ur.allotted_at AS DATE) ASC,
    ABS(DATEDIFF(NOW(), CAST(ur.due_date AS DATE)))";

$resultMain = mysqli_query($db, $queryMain);


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
                                echo "<th>Mobile</th>";
                                echo "<th>Tender ID</th>";
                                echo "<th>Ref. Code </th>";
                                echo "<th>Tender No</th>";
                                echo "<th>Department</th>";
                                echo "<th>Section</th>";
                                echo "<th>Division</th>";
                                echo "<th>Sub-division</th>";
                                echo "<th>Work Name</th>";
                                echo "<th>Tentative Cost</th>";
                                echo "<th>Reminder</th>";
                                echo "<th>Edit</th>";
                                echo "</tr>";
                                echo "</thead>";
                                ?>
                                <?php
                                $count = 1;
                                echo "<tbody>";
                                while ($row = mysqli_fetch_row($resultMain)) {

                                    echo "<tr class='record'>";
                                    echo "<td><div class='custom-control custom-checkbox'>
                                    <input type='checkbox' class='custom-control-input request_checkbox' id='customCheck" . $count . "' data-request-id='" . $row[10] . "'>
                                    <label class='custom-control-label' for='customCheck" . $count . "'>" . $count . "</label>
                                    </div>
                                    </td>";

                                    echo "<td><span style='color:red;'> " . $row['0'] . " </span></td>";
                                    echo "<td>  <span style='color:green;'>" . $row['1'] . " </span></td>";
                                    echo "<td>" . $row['2'] . "</td>";
                                    echo "<td>" . $row['3'] . "</td>";
                                    echo "<td>" . $row['12'] . "</td>";
                                    echo "<td>" . $row['11'] . "</td>";
                                    echo "<td>" . $row['4'] . "</td>";
                                    echo "<td>" . $row['5'] . "</td>";
                                    echo "<td>" . $row['15'] . "</td>";
                                    echo "<td>" . $row['14'] . "</td>";
                                    echo "<td>" . $row['16'] . "</td>";
                                    echo "<td style='white-space:pre-wrap; word-wrap:break-word; max-width:20rem;'>" . $row['6'] . "</td>";
                                    echo "<td style='white-space:pre-wrap; word-wrap:break-word; max-width:20rem;'>" . $row['17'] . " rupees /-</td>";

                                    echo "<td>" . "<span class='btn btn-success'>" . $row[7] . " days</span>" . "<br/><br/>" .
                                        "Aloted Date :" . "<br/>" . date_format(date_create($row['8']), "d-m-Y ") . "<br/>" . '<a href="../login/tender/' . $row['9'] .
                                        '"  target="_blank"/>View file 1</a> </br>';
                                    if (!empty($row['13'])) {
                                        echo '<a href="../login/tender/' . $row['13'] . '" target="_blank"/>View File 2 </a>' . "</td>";
                                    } else {
                                        echo "</td>";
                                    }

                                    $res = $row[10];
                                    $res = base64_encode($res);

                                    echo "<td>  <a href='alot-tender-update.php?id=$res'><button type='button' class='btn btn-warning'>
                                    <i class='feather icon-edit'></i> &nbsp;Re-Alot</button></a>  &nbsp;";
                                    echo "<br/>";
                                    echo "<br/>";
                                    if ($allowDelete == true || (in_array('All', $permissions)) || (in_array('Recycle Bin', $permissions))) {
                                        echo "<a href='#' id='" . $row['10'] . "'class='recyclebutton btn btn-danger' title='Click To Delete'> 
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

            $('#basic-btn2 thead tr').clone(true).appendTo('#basic-btn2 thead');

            var columnsWithSearch = [5, 8, 9, 10, 11, 13];

            $('#basic-btn2 thead tr:eq(1) th').each(function (i) {

                if (columnsWithSearch.includes(i) && !$(this).hasClass("noFilter")) {
                    var title = $(this).text();
                    $(this).html('<input type="text" class="form-control" placeholder="Search ' + title + '" />');

                    $('input', this).on('keyup change', function () {
                        if (table.column(i).search() !== this.value) {
                            table
                                .column(i)
                                .search(this.value)
                                .draw();
                        }
                    });
                } else {
                    $(this).html('<span></span>');
                }
            });

            var table = $('#basic-btn2').DataTable({
                orderCellsTop: true,
                fixedHeader: true,
                columnDefs: [
                    { targets: 0, visible: true }
                ]
            });


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
        });

    </script>


</body>

</html>