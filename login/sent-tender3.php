<?php

session_start();

require "db/config.php";

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

$name = $_SESSION['login_user'];
$adminID = $_SESSION['login_user_id'];



$tenderID = base64_decode($_GET['tender_id']);

$query = "SELECT 
    m.name, 
    m.firm_name, 
    m.mobile, 
    ur.tender_no, 
    dept.department_name, 
    ur.name_of_work,
    ur.due_date, 
    ur.created_at, 
    ur.sent_at, 
    ur.file_name, 
    ur.tenderID, 
    ur.id, 
    ur.reference_code, 
    ur.file_name2, 
    ur.tentative_cost, 
    ur.section_id, 
    ur.division_id,
    ur.additional_files,
    MAX(s.section_name) AS section_name,
    MAX(dv.division_name) AS division_name,
    MAX(sd.subdivision) AS subdivision,
    ur.auto_quotation,
    ur.email_sent_date
FROM 
    user_tender_requests ur
INNER JOIN 
    members m ON ur.member_id = m.member_id
LEFT JOIN 
    department dept ON ur.department_id = dept.department_id
LEFT JOIN 
    section s ON ur.section_id = s.section_id
LEFT JOIN 
    division dv ON ur.division_id = dv.division_id
LEFT JOIN
    sub_division sd ON ur.sub_division_id = sd.id
WHERE 
    ur.status = 'Sent' AND ur.delete_tender = '0' ANd ur.tenderID = '$tenderID'
GROUP BY 
    ur.id
ORDER BY 
    NOW() >= CAST(ur.sent_at AS DATE), 
    CAST(ur.sent_at AS DATE) ASC, 
    ABS(DATEDIFF(NOW(), CAST(ur.sent_at AS DATE)));

";

$result2 = mysqli_query($db, $query);

?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title><?php echo base64_decode($_GET['tender_id']); ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">


    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Change the background color of the table header */
        #basic-btn thead th {
            background-color: #33cc33;
            /* Change this color as per your preference */
            color: white;
            /* This will make the text color white */
        }

        /* Optional: Add border or other styles */
        #basic-btn thead th {
            border: 1px solid #ddd;
            /* Adds a border to the table header */
            padding: 10px;
            /* Adds some padding */
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
            /* Initially hidden */
        }

        .loading-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            z-index: 1000;
        }

        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
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
            <a href="#!d" class="b-brand" style="font-size:24px;">
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
                                <h5 class="m-b-10"><?php echo base64_decode($_GET['tender_id']); ?>
                                </h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a id="link"
                                        style="color:#ff5370; font-size:15px;font-weight:bold"
                                        href="sent-tender2.php">Back To Sent Tender</a></li>
                                <li class="breadcrumb-item"><a style="color:#33cc33"
                                        href=""><?php echo base64_decode($_GET['tender_id']); ?></a></li>

                            </ul>

                        </div>
                    </div>
                </div>
            </div>

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
            // if($allowedAction=='all'){
            
            echo '<div class="row">';
            // echo '
            // <div class="col-md-6 col-xl-3">
            //         <div class="card bg-c-green order-card">
            //             <div class="card-body">
            //                 <h6 class="text-white">Sent Tenders</h6>
            //                 <h2 class="text-right text-white"><i
            //                         class="feather icon-message-square float-left"></i><span id="total"></span></h2>
            
            //             </div>
            //         </div>
            //     </div>
            // ';
            echo '<div class="col-sm-12">';
            echo '<div class="card">';
            echo '<div class="card-body">';

            echo "<div class='col-md row'>";
            if ($isAdmin || hasPermission('Dashboard', $privileges, $roleData['role_name'])) {
                echo "<a href='#' id='recycle_records' class='btn btn-danger rounded-sm'> <i class='feather icon-trash'></i>  &nbsp;
            Move to Bin Selected Items</a>";
            }
            echo "&nbsp; &nbsp; &nbsp";
            echo "<a href='#' id='email_records' class='btn btn-primary rounded-sm'> <i class='feather icon-mail'></i>   
                                    Send Email To Selected Items</a>";

            // Search Bar Section with Dynamic Filter Functionality
            // echo "<div class='col-md-4 ms-auto'> <!-- Add offset for alignment -->
            //    <div class='input-group'>
            //        <input type='text' class='form-control' id='searchInput' placeholder='Search on this page' aria-label='Search'>
            //        <button class='btn btn-success rounded-sm' type='button' onclick='clearSearch()'>
            //            <i class='feather icon-search'></i> &nbsp; Search
            //        </button>
            //    </div>
            // </div>";
            
            echo "</div> <br />";
            // echo '<div id="contentArea">';
            // // Example Content to Search
            // // echo '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>';
            // // echo '<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>';
            // // echo '<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia.</p>';
            // echo '</div>';
            
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            ?>

            <?php
            // $_count = 1;
            // foreach ($tenders2 as $tenderID => $tenderRequests) { ?>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header table-card-header">
                        </div>
                        <div class="card-body">
                            <div class="dt-responsive table-responsive">
                                <br />

                                <?php


                                echo '<table id="basic-btn2" class="table table-striped table-bordered nowrap">';
                                echo "<thead>";
                                // echo "<tr class='table-success thead-light'>";
                                // echo "<th colspan='20' class='text-center'><h4 class='text-light'>S.NO : " . "   Tender ID : <span class='text-light'>" . $tenderID . "</span></h4></th>";
                                // echo "</tr>";
                                // echo "<tr>";
                                echo '<th><label class="checkboxs">
                                    <input type="checkbox" id="select-all">
                                    <span class="checkmarks"></span>
                                </label>  SNO</th>';
                                echo "<th>User</th>";
                                echo "<th>Firm Name</th>";
                                echo "<th>Mobile</th>";
                                echo "<th>Ref. Code </th>";
                                echo "<th>Tender No</th>";
                                echo "<th>Department</th>";
                                echo "<th>Section</th>";
                                echo "<th>Division</th>";
                                echo "<th>Sub-division</th>";
                                echo "<th>Work Name</th>";
                                echo "<th>Tentative Cost</th>";
                                echo "<th>Due Date</th>";
                                echo "<th>Date Added</th>";
                                echo "<th>TIME Added</th>";
                                echo "<th>Sent Date</th>";
                                if ($isAdmin || hasPermission('Dashboard', $privileges, $roleData['role_name'])) {
                                    echo "<th>Edit</th>";
                                    echo "<th>Email Sent Status</th>";
                                    echo "<th>Action</th>";
                                }

                                echo "</tr>";
                                echo "</thead>";
                                ?>

                                <?php
                                $count = 1;
                                // foreach ($tenderRequests as $row) {
                                echo "<tbody>";
                                while ($row = mysqli_fetch_assoc($result2)) {



                                    echo "<tr class='record'>";
                                    echo "<td>
                                    <div class='custom-control custom-checkbox'>
                                    <input type='checkbox' class='custom-control-input request_checkbox' id='customCheck" . $row['id'] . "'  data-request-id='" . $row['id'] . "'>
                                    <label class='custom-control-label' for='customCheck" . $row['id'] . "'>" . $count . "</label>
                                    </div>
                                    </td>";
                                    echo "<td> <span style='color:red;'>" . $row['name'] . "</td>";
                                    echo "<td> <span style='color:green;'> " . $row['firm_name'] . "</td>";
                                    echo "<td>" . $row['mobile'] . "</td>";
                                    // echo "<td>" . $row['tenderID'] . "</td>";
                                    echo "<td>" . $row['reference_code'] . "</td>";
                                    echo "<td>" . $row['tender_no'] . "</td>";
                                    echo "<td>" . $row['department_name'] . "</td>";
                                    echo "<td>" . $row['section_name'] . "</td>";
                                    echo "<td>" . $row['division_name'] . "</td>";
                                    echo "<td>" . $row['subdivision'] . "</td>";
                                    echo "<td style='white-space:pre-wrap; word-wrap:break-word; max-width:100rem;'>" . $row['name_of_work'] . "</td>";

                                    if ($row['tentative_cost']) {
                                        echo "<td>" . $row['tentative_cost'] . "</td>";
                                    } else {
                                        echo "<td>-</td>";
                                    }

                                    echo "<td>" . date_format(date_create($row['due_date']), "d-m-Y ") . "</td>";
                                    $originalDate = $row['created_at'];
                                    $timestamp = strtotime($originalDate);
                                    $istDate = date('d-m-Y', $timestamp);
                                    $istTime = date('h:i A', $timestamp + 5.5 * 3600);
                                    echo "<td>" . $istDate . "</td>";
                                    echo "<td>" . $istTime . "</td>";
                                    ?>

                                    <td><?= date_format(date_create($row['sent_at']), "d-m-Y ") ?><br />
                                        <?php if (isset($row['file_name']) && $row['file_name'] == null) { ?>
                                            <a href="<?= '../login/tender/' . $row['file_name'] ?>" target="_blank">
                                                View file 1
                                            </a> </br>
                                        <?php } ?>

                                        <?php if (isset($row['file_name2']) && $row['file_name2'] == null) { ?>
                                            <a href="<?= '../login/tender/' . $row['file_name2'] ?>" target="_blank">View
                                                File 2
                                            </a>
                                        <?php } ?>

                                        <?php if (!empty($row['additional_files'])) {
                                            $extraFiles = json_decode($row['additional_files'], true);
                                            ?>
                                            <?php if (is_array($extraFiles)) {
                                                $count = 1;
                                                ?>
                                                <?php foreach ($extraFiles as $index => $filePath) { ?>
                                                    <a href="<?= '../login/' . $filePath ?>" target="_blank">View
                                                        File <?= $count ?>
                                                    </a><br />
                                                    <?php
                                                    $count++;
                                                } ?>
                                            <?php } ?>
                                        <?php } ?>

                                    </td>

                                    <?php
                                    $res = $row["id"];
                                    $res = base64_encode($res);


                                    if ($isAdmin || hasPermission('Dashboard', $privileges, $roleData['role_name'])) {
                                        echo "<td>  <a href='sent-edit.php?id=$res'><button type='button' class='btn btn-warning rounded-sm'><i class='feather icon-edit'></i>
                                        &nbsp;Alot</button></a>  &nbsp;";
                                    }

                                    echo "<br/>";
                                    echo "<br/>";

                                    if ($isAdmin || hasPermission('Dashboard', $privileges, $roleData['role_name'])) {
                                        echo "<a href='#' id='" . $row['id'] . "'class='recyclebutton btn btn-danger rounded-sm' title='Click To Delete'> 
                                        <i class='feather icon-trash'></i>  &nbsp; Move to Bin</a></td>";
                                    }
                                    if ($row['auto_quotation'] != 1) {
                                        if (!empty($row['email_sent_date']) && strtotime($row['email_sent_date'])) {
                                            $originalDate2 = $row['email_sent_date'];
                                            $timestamp2 = strtotime($originalDate2);
                                            $istDate2 = date('d-m-Y', $timestamp2);
                                            $istTime2 = date('h:i A', $timestamp2);

                                            echo "<td><p>" . $istDate2 . " " . $istTime2 . "</p></td>";
                                        } else {
                                            // Placeholder if the email_sent_date is not set or invalid
                                            echo "<td><p class='text-warning'>*Please Send Email</p></td>";
                                        }

                                        echo "<td>  
                                            <a href='#'><button type='button' id='" . $row['id'] . "' class= 'mail btn btn-success rounded-sm'>
                                            <i class='feather icon-mail'></i>&nbsp;Mail Send</button></a>  &nbsp;";
                                        echo "<br/><br/>";
                                        echo "</td>";
                                    } else {
                                        echo "<td><p class=''><i class='feather icon-repeat'></i>&nbsp;Auto Email ON</p></td>";
                                        echo "<td>  
                                            <a href='#'><button type='button' id='" . $row['id'] . "' class= 'mail btn btn-success' rounded-sm >
                                            <i class='feather icon-mail'></i>&nbsp;Mail Send</button></a>  &nbsp;";
                                        echo "<br/><br/>";
                                        echo "</td>";
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
            <?php
            // $_count++;
            //} ?>
        </div>
    </section>

    <div class="loading-overlay">

        <div class="loading-message">
            <div class="spinner"></div>
            Sending email, please wait...
        </div>
    </div>



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
    <!-- <script src="assets/js/pages/data-export-custom.js"></script> -->



    <script>
        $(document).ready(function () {
            // Optional: Hide update message after 5 seconds
            $("#updateuser").delay(5000).slideUp(300);

        });
    </script>

    <script type="text/javascript">
        $(function () {

            $(".recyclebutton").click(function () {

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

            function showLoadingMessage() {
                $(".loading-overlay").show();
                $(".loading-message").show();
            }

            function hideLoadingMessage() {
                $(".loading-overlay").hide();
                $(".loading-message").hide();
            }

            $(".mail").on("click", function (e) {
                e.preventDefault();

                let element = $(this);
                let email_id = element.attr("id");
                let info = 'id=' + email_id;

                Swal.fire({
                    title: "Are you sure?",
                    text: "You want to send an email to this record?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#33cc33",
                    cancelButtonColor: "#ff5471",
                    confirmButtonText: "Yes, send it!",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {

                        // Optional: show a loading message while sending
                        Swal.fire({
                            title: "Sending...",
                            text: "Please wait while we send the email.",
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            type: "GET",
                            url: "mail.php",
                            data: info,
                            success: function () {
                                setTimeout(function () {
                                    Swal.fire({
                                        icon: "success",
                                        title: "Email Sent!",
                                        text: "The email has been sent successfully.",
                                        confirmButtonColor: "#33cc33",
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    setTimeout(() => {
                                        location.reload();
                                    }, 2000);
                                }, 1000);
                            },
                            error: function () {
                                Swal.fire({
                                    icon: "error",
                                    title: "Failed!",
                                    text: "Something went wrong while sending the email.",
                                    confirmButtonColor: "#ff5471"
                                });
                            }
                        });
                    }
                });
            });


            $('#recycle_records').on('click', function (e) {
                e.preventDefault();
                let requestIDs = [];
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
                            let selected_values = requestIDs.join(",");
                            $.ajax({
                                type: "POST",
                                url: "recycleuser.php",
                                cache: false,
                                data: 'tender_sent_ids=' + selected_values,
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

            $('#email_records').on('click', function (e) {
                let requestIDs = [];

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
                    WRN_MAIL_SEND = "Are you sure you want to send email to " + (requestIDs.length > 1 ? "these" : "this") + " Record?";
                    let checked = confirm(WRN_MAIL_SEND);
                    if (checked == true) {
                        showLoadingMessage();//show loading message
                        let selected_values = requestIDs.join(",");
                        $.ajax({
                            type: "POST",
                            url: "mail.php",
                            cache: false,
                            data: 'tender_sent_ids=' + selected_values,
                            success: function () {
                                setTimeout(function () {
                                    hideLoadingMessage();
                                    location.reload();
                                }, 2000);
                            }
                        })
                    }
                }
            })
        });
    </script>

    <script>
        // jQuery for searching and auto-focusing on <th> tags
        $(document).ready(function () {
            $('#searchInput').on('input', function () {
                const query = $(this).val().toLowerCase();
                let found = false;

                $('th h4').each(function () {
                    const th = $(this);
                    if (th.text().toLowerCase().includes(query)) {
                        th.css('color', '#F1F5F8'); // Highlight with the desired color
                        if (!found) {
                            th[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); // Scroll to the first match
                            found = true;
                        }
                    } else {
                        th.css('background-color', ''); // Reset non-matching <th>
                    }
                });
            });

            // Function to clear the search
            $('#clearSearch').on('click', function () {
                $('#searchInput').val('');
                $('th').css('background-color', ''); // Reset all highlights
            });
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


            }); s
        });
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