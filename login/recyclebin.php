x
<?php

session_start();


if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}
$name = $_SESSION['login_user'];

include("db/config.php");

$adminID = $_SESSION['login_user_id'];
$adminPermissionQuery = "SELECT nm.title FROM admin_permissions ap 
INNER JOIN navigation_menus nm ON ap.navigation_menu_id = nm.id WHERE ap.admin_id='" . $adminID . "'";
$adminPermissionResult = mysqli_query($db, $adminPermissionQuery);

while ($row = mysqli_fetch_row($adminPermissionResult)) {
    $userPermissions[] = $row[0];
}
$allowedAction = !in_array('All', $userPermissions) && in_array('Update Tenders', $userPermissions) ? 'update' :
    (!in_array('All', $userPermissions) && in_array('View Tenders', $userPermissions) ? 'view' : 'all');

$query = "SELECT 
m.member_id,
utr.tenderID,
m.name,
m.firm_name,
m.mobile,
utr.status,
utr.id
FROM user_tender_requests utr
LEFT JOIN members m
ON utr.member_id = m.member_id
WHERE utr.delete_tender = '1'
";
$result = mysqli_query($db, $query);

?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Recycle Bin
    </title>
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
                                <h5 class="m-b-10">Recycle Bin
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
                                if ($allowedAction == 'all') {
                                    echo "<a href='#' id='delete_records' class='btn btn-danger'> <i class='feather icon-trash'></i>  &nbsp;
                                    Delete Selected Items</a>";
                                }

                                if ($allowedAction == 'all' || $allowedAction == 'update') {
                                    echo "<a href='#' class='restore_records px-1'><button type='button' class='btn btn-warning'>
                                    <i class='feather icon-refresh-ccw'></i> &nbsp;Restore Selected Items</button>
                                    </a>   
                                    </div> <br />";
                                }

                                if (isset($_GET['status'])) {
                                    $st = $_GET['status'];
                                    $st1 = base64_decode($st);

                                    if ($st1 > 0) {
                                        echo " <div class='alert alert-success alert-dismissible fade show' role='alert' style='font-size:16px;' id='updateuser'>
                                    <strong><i class='feather icon-check'></i>Thanks!</strong> Members details has been Updated Successfully.
                                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                        <span aria-hidden='true'>&times;</span>
                                    </button>
                                    </div> ";
                                    } else {
                                        echo " <div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='updateuser'>
                                    <strong>Error!</strong> Members details been not Updated
                                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                    <span aria-hidden='true'>&times;</span>
                                    </button>
                                    </div> ";
                                    }
                                }
                                ?>
                                <br />


                                <?php
                                echo '<table id="basic-btn" class="table table-striped table-bordered ">';
                                echo "<thead>";
                                echo "<tr>";
                                echo "<th>SNO</th>";
                                echo "<th> Tender Id</th>";
                                echo "<th>Name</th>";
                                echo "<th>Firm Name</th>";
                                echo "<th>Mobile</th>";
                                echo "<th>Status</th>";

                                echo "<th>Edit</th>";


                                echo "</tr>";
                                echo "</thead>";


                                ?>
                                <?php
                                $count = 1;

                                echo "<tbody>";
                                while ($row = mysqli_fetch_row($result)) {

                                    echo "<tr class='record'>";
                                    echo "<td><div class='custom-control custom-checkbox'>
                                    <input type='checkbox' class='custom-control-input request_checkbox' id='customCheck" . $row[6] . "' data-request-id='" . $row[6] . "'>
                                    <label class='custom-control-label' for= 'customCheck" . $row[6] . "'>" . $count . "</label>
                                </div>
                                </td>";
                                    echo "<td>" . $row[1] . "</td>";
                                    echo "<td>" . $row[2] . "</td>";
                                    echo "<td>" . $row[3] . "</td>";
                                    echo "<td>" . $row[4] . "</td>";
                                    echo "<td>" . $row[5] . "</td>";
                                    $res = $row[6];
                                    $ree = base64_encode($res);
                                    echo "<td>
                                    <a href='#' id='" . $row[6] . "' class='restorebutton btn btn-warning'>
                                    <i class='feather icon-refresh-ccw'></i> &nbsp;Restore</a>
                                    &nbsp;   
                                    <a href='#' id='" . $row[6] . "' class='delbutton btn btn-danger' title='Click To Delete'> 
                                    <i class='feather icon-trash'></i>  &nbsp; Permanent Delete</a>
                                    </td>";
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

    <script>
        $(document).ready(function () {
            //single delete Button
            $("#basic-btn").on('click', '.delbutton', function () {

                let element = $(this);

                let del_id = element.attr("id");

                let info = 'id=' + del_id;
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
                        window.location.reload();
                    },
                        2000);
                }
                return false;
            });
            //single delete restore
            $("#basic-btn").on('click', '.restorebutton', function () {

                let element = $(this);

                let res_id = element.attr("id");

                let info = 'id=' + res_id;
                if (confirm("Are you sure you want to restore this Record?")) {
                    $.ajax({
                        type: "GET",
                        url: "restoreuser.php",
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
                    },
                        2000);
                }
                return false;
            });

            //multiple delete Button
            $('#delete_records').on('click', function (e) {
                let requestIDs = [];

                $(".request_checkbox:checked").each(function () {
                    requestIDs.push($(this).data('request-id'));
                });

                if (requestIDs.length <= 0) {
                    alert("Please select records.");
                }
                else {
                    WRN_PROFILE_DELETE = "Are you sure you want to delete " + (requestIDs.length > 1 ? "these" : "this") + " Record" + (requestIDs.length > 1 ? "s?" : "?");
                    let checked = confirm(WRN_PROFILE_DELETE);
                    if (checked == true) {
                        let selected_values = requestIDs.join(",");
                        $.ajax({
                            type: "POST",
                            url: "deleteuser.php",
                            cache: false,
                            data: 'tender_recycle_ids=' + selected_values,
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

            //multiple restore Button
            $('.restore_records').on('click', function (e) {
                let requestIDs = [];

                $(".request_checkbox:checked").each(function () {
                    requestIDs.push($(this).data('request-id'));
                });

                if (requestIDs.length <= 0) {
                    alert("Please select records.");
                }
                else {
                    WRN_PROFILE_DELETE = "Are you sure you want to restore " + (requestIDs.length > 1 ? "these" : "this") + " Record" + (requestIDs.length > 1 ? "s?" : "?");
                    let checked = confirm(WRN_PROFILE_DELETE);
                    if (checked == true) {
                        let selected_values = requestIDs.join(",");
                        $.ajax({
                            type: "POST",
                            url: "restoreuser.php",
                            cache: false,
                            data: 'tender_recycle_ids=' + selected_values,
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


        });
    </script>





</body>

</html>