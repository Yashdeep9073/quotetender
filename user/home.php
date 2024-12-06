<?php

session_start();
error_reporting(0);

if (!isset($_SESSION["login_register"])) {
    header("location: ../index.php");
}

include("../login/db/config.php");


$name = $_SESSION['login_register'];

$query = "SELECT * FROM tender WHERE email='" . $_SESSION["login_register"] . "'";

$result = mysqli_query($db, $query);

$memberQuery1 = "SELECT name FROM members WHERE email_id='" . $_SESSION["login_register"] . "'";
$memberData1 = mysqli_query($db, $memberQuery1);
$member1 = mysqli_fetch_row($memberData1);



?>



<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>User Dashboard </title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="#" />

    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">


</head>

<body class="">

    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>


    <nav class="pcoded-navbar menupos-fixed menu-light ">
        <div class="navbar-wrapper  ">
            <div class="navbar-content scroll-div ">
                <ul class="nav pcoded-inner-navbar ">
                    <li class="nav-item pcoded-menu-caption">
                        <label>Navigation</label>
                    </li>
                    <li class="nav-item">
                        <a href="home.php" class="nav-link " style="background:#33cc33; color:#fff;"><span
                                class="pcoded-micon"><i class="feather icon-home"></i></span><span
                                class="">Dashboard</span></a>
                    </li>


 <li class="nav-item">
                        <a href="tender-request.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-edit"></i></span><span class="pcoded-mtext">Tender
                                Requests</span></a>

                    </li>






                    <li class="nav-item">
                        <a href="edit-profile.php" class="nav-link"><span class="pcoded-micon"><i
                                    class="feather icon-edit"></i></span><span class="">
                                Edit Profile</span></a>
                    </li>





                    <li class="nav-item">
                        <a href="changepass.php" class="nav-link"><span class="pcoded-micon"><i
                                    class="feather icon-command"></i></span><span class="">Change
                                Password</span></a>
                    </li>



                    <li class="nav-item">
                        <a href="logout.php" class="nav-link " style="background:#33cc33; color:#fff;"><span
                                class="pcoded-micon"><i class="feather icon-power"></i></span><span class="">Log
                                out</span></a>
                    </li>

                </ul>

            </div>
        </div>
    </nav>


    <header class="navbar pcoded-header navbar-expand-lg navbar-light headerpos-fixed header-blue">
        <div class="m-header">
            <a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>
            <a href="#!" class="b-brand" style="font-size:24px;">
                USER PANEL

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
                    <span><?php echo $member1[0]; ?></span>
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






                            <ul class="breadcrumb">

                                <li><a href="../index.php" class="btn btn-primary"> Back to Main Website</a></li>



                            </ul>


                        </div>
                    </div>

                </div>


            </div>

            <?php
            if (isset($_GET['loginin'])) {
                $st = $_GET['loginin'];
                $st1 = base64_decode($st);

                if ($st1 > 0) {
                    echo " <div class='alert alert-success alert-dismissible fade show' role='alert' style='font-size:16px;' id='logged'>
  <strong><i class='feather icon-check'></i>Welcome!</strong> User has been Login Successfully.
  <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
    <span aria-hidden='true'>&times;</span>
  </button>
</div> ";
                }
            }

            ?>






            <div class="row">

                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header table-card-header">

                        </div>
                        <div class="card-body">
                            <div class="dt-responsive table-responsive">

                                <?php

                                echo '<table id="basic-btn" class="table table-striped table-bordered nowrap">';
                                echo "<thead>";
                                echo "<tr>";
                                echo "<th>SNO</th>";
                                echo "<th>Department</th>";
                                echo "<th>Tender No</th>";
                                echo "<th>Date Add</th>";
                                echo "<th>Due Date</th>";

                                echo "<th>Status</th>";


                                echo "</tr>";
                                echo "</thead>";


                                ?>
                                <?php



                                $count = 1;

                                echo "<tbody>";
                                while ($row = mysqli_fetch_row($result)) {

                                    echo "<tr class='record'>";
                                    echo "<td> $count</td>";

                                    echo "<td>" . $row['6'] . "</td>";
                                    echo "<td>" . $row['11'] . "</td>";
                                    echo "<td>" . $row['7'] . "</td>";
                                    echo "<td>" . $row['8'] . "</td>";




                                    $res = $row[10];
                                    if ($res == 0) {

                                        echo "<td> <button type='button' class='btn btn-warning'><i class='feather icon-edit'></i> &nbsp;Pending</button>  </td>";
                                    } else {
                                        echo "<td> <button type='button' class='btn btn-success'><i class='feather icon-edit'></i> &nbsp;Apporved</button>  </td>";
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






                <!-- social statustic end -->







            </div>
    </section>





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
</body>
<script>
$(document).ready(function() {
    setInterval(function() {


        $("#autodata").load("load.php");
        refresh();

    }, 100);
});
</script>

<script>
$(document).ready(function() {
    setInterval(function() {


        $("#gold").load("loadgold.php");
        refresh();

    }, 100);
});
</script>

<script>
$(document).ready(function() {
    setInterval(function() {


        $("#member").load("loadmembers.php");
        refresh();

    }, 100);
});
</script>

<script>
$(document).ready(function() {
    $("#logged").delay(5000).slideUp(300);
});
</script>

<script>
function display_ct6() {
    var x = new Date()
    var ampm = x.getHours() >= 12 ? ' PM' : ' AM';
    hours = x.getHours() % 12;
    hours = hours ? hours : 12;
    var x1 = x.getMonth() + 1 + "-" + x.getDate() + "-" + x.getFullYear();
    x1 = x1 + " - " + hours + ":" + x.getMinutes() + ":" + x.getSeconds() + ":" + ampm;
    document.getElementById('ct6').innerHTML = x1;
    display_c6();
}

function display_c6() {
    var refresh = 1000; // Refresh rate in milli seconds
    mytime = setTimeout('display_ct6()', refresh)
}
display_c6()
</script>


</html>