<?php

session_start();
require "./db/config.php";

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

$name = $_SESSION['login_user'];
$adminID = $_SESSION['login_user_id'];
$adminPermissionQuery = "SELECT nm.title FROM admin_permissions ap 
INNER JOIN navigation_menus nm ON ap.navigation_menu_id = nm.id WHERE ap.admin_id='" . $adminID . "'";
$adminPermissionResult = mysqli_query($db, $adminPermissionQuery);

$tenderID = $_GET['tender_id'];

while ($row = mysqli_fetch_row($adminPermissionResult)) {
    $userPermissions[] = $row[0];
}
$allowedAction = !in_array('All', $userPermissions) && in_array('Update Tenders', $userPermissions) ? 'update' :
    (!in_array('All', $userPermissions) && in_array('View Tenders', $userPermissions) ? 'view' : 'all');

$query = "SELECT DISTINCT
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
ur.id,
ur.file_name2 
FROM 
    user_tender_requests ur 
INNER JOIN 
    members m ON ur.member_id= m.member_id
INNER JOIN 
    department ON ur.department_id = department.department_id 
WHERE 
    ur.status= 'Requested' AND ur.delete_tender = '0' AND ur.tenderID = '$tenderID'
GROUP BY 
    ur.id
ORDER BY 
    NOW() >= CAST(ur.created_at AS DATE), 
    CAST(ur.created_at AS DATE) ASC, 
    ABS(DATEDIFF(NOW(), CAST(ur.created_at AS DATE)))";

$result = mysqli_query($db, $query);

// $tenders = [];
// while ($row = mysqli_fetch_assoc($result)) {
//     $tenders[$row['tenderID']][] = $row;
// }

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
    <title>Tender Request 3</title>
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

        
        .center-text {
            text-align: center;

        }

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
                                <h5 class="m-b-10">Tender Request</h5>
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

            <!-- <div class="row"> -->
            <!-- <div class="col-md-6 col-xl-3">
                    <div class="card bg-c-blue order-card">
                        <div class="card-body">
                            <h6 class="text-white">Tender Request</h6>
                            <h2 class="text-right text-white"><i class="feather icon-message-square float-left"></i><span id="new">54</span></h2>

                        </div>
                    </div>
                </div> -->
            <!-- </div> -->



            <?php if (isset($_GET['status'])) {
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
            // if($allowedAction=='all' || $allowedAction=='update' || $allowedAction=='recycle bin'  ){
            echo '<div class="row">';
            echo '<div class="col-sm-12">';
            echo '<div class="card">';
            echo '<div class="card-body">';
            echo '<div class="col-md row">';

            // Action Buttons
            if ((in_array('All', $permissions)) || (in_array('Tender Request', $permissions)) || (in_array('Recycle Bin', $permissions))) {
                echo "<a href='#' id='recycle_records' class='btn btn-danger me-3 rounded-sm'> 
                    <i class='feather icon-trash'></i> &nbsp; Move to Bin Selected Items
                  </a>&nbsp&nbsp&nbsp&nbsp";
            }
            if ((in_array('All', $permissions)) || (in_array('Update Tenders', $permissions)) || (in_array('Tender Request', $permissions))) {
                echo "<a href='#' class='update_records'><button type='button' class='btn btn-warning me-3 rounded-sm'>
                    <i class='feather icon-edit'></i> &nbsp; Update Selected Items
                  </button></a>
                  ";
            }

            // Search Bar Section with Dynamic Filter Functionality
            // echo "<div class='col-md-3 col-sm-6 ms-auto'> <!-- Add offset for alignment -->
            //         <div class='input-group'>
            //             <input type='text' class='form-control' id='searchInput' placeholder='Search on this page' aria-label='Search'>
            //             <button class='btn btn-success rounded-sms' type='button' onclick='clearSearch()'>
            //                 <i class='feather icon-search'></i> &nbsp; Search
            //             </button>
            //         </div>
            //   </div>";

            echo '</div><br />';

            echo '<div id="contentArea">';
            //Something Special
            echo '</div>';

            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';


            // }
            ?>

            <?php
            // $_count = 1;
            // foreach ($tenders as $tenderID => $tenderRequests) {
                echo '<div class="row">';
                echo '<div class="col-sm-12">';
                echo '<div class="card">';
                echo '<div class="card-header table-card-header">';
                echo '</div>';

                echo '<div class="card-body">';
                echo '<div class="dt-responsive table-responsive">';
                echo "<br />";
                // echo "<div class='col-md row'>";
            
                echo '<table  id="basic-btn" class="table table-striped table-bordered nowrap">';

                echo "<thead>";
                echo "<tr class='table-success thead-light'>";
                echo "<th colspan='12' class='text-center'><h4 style='color:#fff;' class=''>" . " Tender ID : <span class=''>" . $tenderID . "</span></h4></th>";

                echo "</tr>";

                echo "<tr>";
                echo "<th>SNO</th>";
                echo "<th>User</th>";
                echo "<th>Firm Name</th>";
                echo "<th>Mobile</th>";
                echo "<th>Email</th>";

                echo "<th>Department</th>";
                echo "<th>Add Date</th>";
                echo "<th>Add Time</th>";

                echo "<th>Due Date</th>";
                echo "<th>File Names </th>";
                if ((in_array('All', $permissions)) || (in_array('Tender Request', $permissions)) || (in_array('Update Tenders', $permissions)) || (in_array('Recycle Bin', $permissions)) || (in_array('View Tenders', $permissions))) {
                    echo "<th>Edit</th>";
                }
                echo "</tr>";
                echo "</thead>";

                $count = 1;
                // foreach ($tenderRequests as $row) {
                    echo "<tbody>";
                    while ($row = mysqli_fetch_assoc($result) ) {

                    echo "<tr class='record'>";
                    echo "<td><div class='custom-control custom-checkbox'>
                                    <input type='checkbox' class='custom-control-input request_checkbox' id='customCheck" . $row['id'] . "' data-request-id='" . $row['id'] . "'>
                                    <label class='custom-control-label' for='customCheck" . $row['id'] . "'>" . $count . "</label>
                                    </div>
                                    </td>";



                    echo "<td>" . "<span style='color:red;'> " . $row['name'] . "</td>";

                    echo "<td>" . $row['firm_name'] . "</td>";

                    echo "<td>" . $row['mobile'] . "</td>";
                    echo "<td>" . $row['email_id'] . "</td>";

                    echo "<td>" . $row['department_name'] . "</td>";

                    // Convert and display the date in 'd-m-Y' format
                    $originalDate = $row['created_at'];
                    $timestamp = strtotime($originalDate);
                    $istDate = date('d-m-Y', $timestamp);
                    $istTime = date('h:i A', $timestamp + 5.5 * 3600);
                    echo "<td>" . $istDate . "</td>";
                    echo "<td>" . $istTime . "</td>";



                    echo "<td>" . date_format(date_create($row['due_date']), "d-m-Y") . "</td>";

                    if (!empty($row['file_name'])) {
                        echo "<td>" . '<a href="../login/tender/' . $row['file_name'] . '" target="_blank" style="padding:6px 15.2px;" />View </a>' . "</br>";
                    } else {
                        echo "<td>" . '<a href="../login/tender/' . $row['file_name'] . '" class="btn disabled" target="_blank"/>No file </a>' . "</br>";
                    }
                    if (!empty($row['file_name2'])) {
                        echo '<a href="../login/tender/' . $row['file_name2'] . '" target="_blank" style="padding:6px 15.2px;" />View </a>' . "</td>";
                    } else {
                        echo '<a href="../login/tender/' . $row['file_name2'] . '" class="btn disabled" target="_blank"/>No file </a>' . "</td>";
                    }
                    $res = $row['id'];
                    $res = base64_encode($res);

                    echo "<td>";
                    if ((in_array('All', $permissions)) || (in_array('Tender Request', $permissions)) || (in_array('Update Tenders', $permissions))) {
                        echo "<a href='tender-edit.php?id=$res'>
                                    <button type='button' class='btn btn-warning'>
                                    <i class='feather icon-edit'></i> &nbsp;Update</button>
                                    </a>";
                    }

                    echo "<br/>";
                    echo "<br/>";
                    if ((in_array('All', $permissions)) || (in_array('Tender Request', $permissions)) || (in_array('Recycle Bin', $permissions))) {
                        echo "<a href='#' id='" . $row['id'] . "' class='recyclebutton btn btn-danger' title='Click To Delete'> 
                                        <i class='feather icon-trash'></i>  &nbsp; Move to Bin
                                        </a>";
                    }
                    echo "</td>";

                    echo "</tr>";
                    $count++;
                }
                echo "</tfoot>";
                echo "</table>";

                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            //     $_count++;
            // }
            ?>
        </div>;
    </section>





    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <!-- <script src="assets/js/menu-setting.min.js"></script> -->

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



    <script>
        $(document).ready(function () {
            $("#updateuser").delay(5000).slideUp(300);
        });
    </script>

    <script type="text/javascript">
        $(function () {

            $(".recyclebutton").click(function () {
                let element = $(this);

                let res_id = element.attr("id");

                let info = 'id=' + res_id;
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
                        window.location.reload();
                    }, 2000);
                }
                return false;
            });

            $('#recycle_records').on('click', function (e) {
                let requestIDs = [];

                $(".request_checkbox:checked").each(function () {
                    requestIDs.push($(this).data('request-id'));
                });

                if (requestIDs.length <= 0) {
                    alert("Please select records.");
                }
                else {
                    const WRN_PROFILE_DELETE = "Are you sure you want to delete " + (requestIDs.length > 1 ? "these" : "this") + " Record" + (requestIDs.length > 1 ? "s?" : "?");
                    let checked = confirm(WRN_PROFILE_DELETE);
                    if (checked == true) {
                        let selected_values = requestIDs.join(",");
                        $.ajax({
                            type: "POST",
                            url: "recycleuser.php",
                            cache: false,
                            data: { tender_request_ids: selected_values },
                            success: function (response) {
                                $(".request_checkbox:checked").each(function () {
                                    $(this).closest(".record").animate({
                                        backgroundColor: "#FF3"
                                    }, "fast").animate({
                                        opacity: "hide"
                                    }, "slow", function () {
                                        $(this).remove();
                                    });
                                });
                                setInterval(function () {
                                    window.location.reload();
                                }, 2000);

                            }
                        });
                    }
                }
            });

            $('.update_records').on('click', function () {
                let updateIDs = [];
                if ($(".request_checkbox:checked").length == 0) {
                    alert("Please select records.");
                    return;
                }

                $(".request_checkbox:checked").each(function () {
                    updateIDs.push($(this).data('request-id'));

                    $('.update_records').attr('href', "update-tender-requests.php?tenderIds=" + btoa(updateIDs));
                    console.log(updateIDs);
                });

                let selected_values = requestIDs.join(",");
                $.ajax({
                    type: "GET",
                    url: "update-tender-requests.php",
                    cache: false,
                    data: "tenderIds" + selected_values,
                })


            });
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