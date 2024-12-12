<?php

session_start();


if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

$name = $_SESSION['login_user'];

include("db/config.php");

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
    MAX(s.section_name) AS section_name,
    MAX(dv.division_name) AS division_name,
    MAX(sd.subdivision) AS subdivision,
    ur.auto_quotation,
    ur.email_sent_date
FROM 
    user_tender_requests ur
INNER JOIN 
    members m ON ur.member_id = m.member_id
INNER JOIN 
    department dept ON ur.department_id = dept.department_id
INNER JOIN 
    section s ON ur.section_id = s.section_id
INNER JOIN 
    division dv ON ur.section_id = dv.section_id
INNER JOIN
    sub_division sd ON ur.division_id = sd.division_id
WHERE 
    ur.status = 'Sent' AND ur.delete_tender = '0'
GROUP BY 
    ur.id
ORDER BY 
    NOW() >= CAST(ur.sent_at AS DATE), 
    CAST(ur.sent_at AS DATE) ASC, 
    ABS(DATEDIFF(NOW(), CAST(ur.sent_at AS DATE)));

";

$result2 = mysqli_query($db, $query);
$tenders2 = [];
while ($row = mysqli_fetch_assoc($result2)) {
    $tenders2[$row['tenderID']][] = $row;
}


// $adminID= $_SESSION['login_user_id'];
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
    <title>Manage Tender</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">


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
                                <h5 class="m-b-10">Sent Tender
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
            echo '<div class="col-sm-12">';
            echo '<div class="card">';
            echo '<div class="card-body">';

            echo "<div class='col-md row'>";
            if ((in_array('All', $permissions)) || (in_array('Recycle Bin', $permissions))) {
                echo "<a href='#' id='recycle_records' class='btn btn-danger'> <i class='feather icon-trash'></i>  &nbsp;
            Move to Bin Selected Items</a>";
            }
            echo "&nbsp; &nbsp; &nbsp";
            echo "<a href='#' id='email_records' class='btn btn-primary'> <i class='feather icon-mail'></i>   
                                    Send Email To Selected Items</a>";

            // Search Bar Section with Dynamic Filter Functionality
            echo "<div class='col-md-4 ms-auto'> <!-- Add offset for alignment -->
               <div class='input-group'>
                   <input type='text' class='form-control' id='searchInput' placeholder='Search on this page' aria-label='Search'>
                   <button class='btn btn-success' type='button' onclick='clearSearch()'>
                       <i class='feather icon-search'></i> &nbsp; Search
                   </button>
               </div>
            </div>";

            echo "</div> <br />";
            echo '<div id="contentArea">';
            // Example Content to Search
            // echo '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>';
            // echo '<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>';
            // echo '<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia.</p>';
            echo '</div>';

            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            ?>

            <?php
            $_count = 1;
            foreach ($tenders2 as $tenderID => $tenderRequests) { ?>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header table-card-header">
                            </div>
                            <div class="card-body">
                                <div class="dt-responsive table-responsive">
                                    <br />

                                    <?php


                                    echo '<table id="basic-btn" class="table table-striped table-bordered nowrap">';
                                    echo "<thead>";
                                    echo "<tr class='table-success thead-light'>";
                                    echo "<th colspan='20' class='text-center'><h4 class='text-light'>S.NO : " . $_count . ")   Tender ID : <span class='text-light'>" . $tenderID . "</span></h4></th>";
                                    echo "</tr>";
                                    echo "<tr>";
                                    echo "<th>SNO</th>";
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
                                    if ($allowedAction == 'all' || $allowedAction == 'update') {
                                        echo "<th>Edit</th>";
                                        echo "<th>Email Sent Status</th>";
                                        echo "<th>Action</th>";
                                    }

                                    echo "</tr>";
                                    echo "</thead>";
                                    ?>

                                    <?php
                                    $count = 1;
                                    foreach ($tenderRequests as $row) {
                                        echo "<tbody>";
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

                                        echo "<td>" . date_format(date_create($row['sent_at']), "d-m-Y ") . "<br/>" . '<a href="../login/tender/' . $row['file_name'] . '"  target="_blank"/>View file 1 </a> </br> ';

                                        if (!empty($row['file_name2'])) {
                                            echo '<a href="../login/tender/' . $row['file_name2'] . '" target="_blank"/>View File 2 </a>' . "</td>";
                                        } else {
                                            echo "</td>";
                                        }


                                        $res = $row["id"];
                                        $res = base64_encode($res);


                                        if ($allowedAction == 'all' || $allowedAction == 'update') {
                                            echo "<td>  <a href='sent-edit.php?id=$res'><button type='button' class='btn btn-warning'><i class='feather icon-edit'></i>
                                        &nbsp;Alot</button></a>  &nbsp;";
                                        }

                                        echo "<br/>";
                                        echo "<br/>";

                                        if ((in_array('All', $permissions)) || in_array('Recycle Bin', $permissions)) {
                                            echo "<a href='#' id='" . $row['id'] . "'class='recyclebutton btn btn-danger' title='Click To Delete'> 
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
                                            <a href='#'><button type='button' id='" . $row['id'] . "' class= 'mail btn btn-success'>
                                            <i class='feather icon-mail'></i>&nbsp;Mail Send</button></a>  &nbsp;";
                                            echo "<br/><br/>";
                                            echo "</td>";
                                        } else {
                                            echo "<td><p class=''><i class='feather icon-repeat'></i>&nbsp;Auto Email ON</p></td>";
                                            echo "<td>  
                                            <a href='#'><button type='button' id='" . $row['id'] . "' class= 'mail btn btn-success' disabled>
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
                $_count++;
            } ?>
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
            // // Setup - add a text input to each footer cell
            // $('#basic-btn thead tr').clone(true).appendTo('#basic-btn thead');
            // $('#basic-btn thead tr:eq(1) th').each(function (i) {
            //     if (!$(this).hasClass("noFilter")) {
            //         var title = $(this).text();
            //         $(this).html('<input type="text" placeholder="Search ' + title + '" />');

            //         $('input', this).on('keyup change', function () {
            //             if (table.column(i).search() !== this.value) {
            //                 table
            //                     .column(i)
            //                     .search(this.value)
            //                     .draw();
            //             }
            //         });
            //     }
            //     else {
            //         $(this).html('<span></span>');
            //     }

            // });

            // var table = $('#basic-btn').DataTable({
            //     orderCellsTop: true,
            //     fixedHeader: true,
            //     columnDefs: [
            //         { targets: 0, visible: true }
            //     ]
            // });

            $("#updateuser").delay(5000).slideUp(300);
        });
    </script>

    <script type="text/javascript">
        $(function () {

            $(".recyclebutton").click(function () {

                let element = $(this);

                let del_id = element.attr("id");

                let info = 'id=' + del_id;
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

                    setTimeout(function () { window.location.reload(); }, 2000);
                }
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

            $(".mail").click(function () {

                let element = $(this);
                let email_id = element.attr("id");
                let info = 'id=' + email_id;
                if (confirm("Are you sure you want to send Email to this Record?")) {

                    showLoadingMessage();//show loading message
                    $.ajax({
                        type: "GET",
                        url: "mail.php",
                        data: info,
                        success: function () {
                            setTimeout(function () {
                                hideLoadingMessage();
                                location.reload();
                            }, 2000);
                        }
                    });
                }
                return false;
            });

            $('#recycle_records').on('click', function (e) {
                e.preventDefault();
                let requestIDs = [];
                $(".request_checkbox:checked").each(function () {
                    requestIDs.push($(this).data('request-id'));
                });
                if (requestIDs.length <= 0) {
                    alert("Please select records.");
                } else {
                    let WRN_PROFILE_DELETE = "Are you sure you want to delete " + (requestIDs.length > 1 ? "these" : "this") + " Record?";
                    let checked = confirm(WRN_PROFILE_DELETE);
                    if (checked == true) {
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
                    return false;
                }
            });

            $('#email_records').on('click', function (e) {
                let requestIDs = [];

                $(".request_checkbox:checked").each(function () {
                    requestIDs.push($(this).data('request-id'));
                });

                if (requestIDs.length <= 0) {
                    alert("Please select records")
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


</body>

</html>