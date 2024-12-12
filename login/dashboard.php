<?php

session_start();
error_reporting(0);

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

include("db/config.php");


$name = $_SESSION['login_user'];

$query = "SELECT * FROM user_tender_requests";

$result = mysqli_query($db, $query);

$query1 = "SELECT * FROM admin";

$result1 = mysqli_query($db, $query1);
$row11 = mysqli_fetch_row($result1);
$type = $row11[5];


?>



<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Quote Tender</title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="Codedthemes" />

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
                    <a href="t.php" class="dud-logout" title="Logout">
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
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5 style=" color:#006666; font-size:24px; font-weight:500; "> <i
                                                class="feather icon-clock"></i> &nbsp; <span id='ct6'
                                                style=" color:#006666; font-size:24px; font-weight:500; letter-spacing:2px;"></span>
                                        </h5>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 style=" color:#006666; font-size:22px; font-weight:500; "> Welcome : &nbsp;
                                            <?php echo $name ?>
                                        </h6>
                                    </div>
                                </div>
                            </div>


                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Dashbaord</a></li>
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
                <!-- order-card start -->
                <div class="col-md-6 col-xl-3">
                    <div class="card bg-c-blue order-card">
                        <div class="card-body">
                            <h6 class="text-white">New Request</h6>
                            <h2 class="text-right text-white"><i
                                    class="feather icon-message-square float-left"></i><span id="new"></span></h2>

                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card bg-c-green order-card">
                        <div class="card-body">
                            <h6 class="text-white">Quoted Request</h6>
                            <h2 class="text-right text-white"><i
                                    class="feather icon-message-square float-left"></i><span id="total"></span></h2>

                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card bg-c-yellow order-card">
                        <div class="card-body">
                            <h6 class="text-white">Registered Members</h6>
                            <h2 class="text-right text-white"><i class="feather icon-users float-left"></i><span
                                    id="user"></span></h2>

                        </div>
                    </div>
                </div>
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


            <div class="row">
                <div class="col-sm-12">
                </div>
                <div class="col-md-12 col-lg-4">
                    <div class="card">
                        <div class="card-block text-center">
                            <i class="fa fa-envelope-open text-c-blue d-block f-40"></i>
                            <h4 class="m-t-20"><span class="text-c-blue">8.62k</span> Subscribers</h4>
                            <p class="m-b-20">Your main list is growing</p>
                            <button class="btn btn-primary btn-sm btn-round">Manage List</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-block text-center">
                            <i class="fa fa-twitter text-c-green d-block f-40"></i>
                            <h4 class="m-t-20"><span class="text-c-blgreenue">+40</span> Followers</h4>
                            <p class="m-b-20">Your main list is growing</p>
                            <button class="btn btn-success btn-sm btn-round">Check them out</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-block text-center">
                            <i class="fa fa-puzzle-piece text-c-pink d-block f-40"></i>
                            <h4 class="m-t-20">Business Plan</h4>
                            <p class="m-b-20">This is your current active plan</p>
                            <button class="btn btn-danger btn-sm btn-round">Upgrade to VIP</button>
                        </div>
                    </div>
                </div>
                <!-- social statustic end -->
            </div>
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
    $(document).ready(function () {
        setInterval(function () {


            $("#new").load("load.php");
            refresh();

        }, 100);
    });
</script>

<script>
    $(document).ready(function () {
        setInterval(function () {


            $("#total").load("load-total.php");
            refresh();

        }, 100);
    });
</script>

<script>
    $(document).ready(function () {
        setInterval(function () {


            $("#user").load("loadgold.php");
            refresh();

        }, 100);
    });
</script>

<script>
    $(document).ready(function () {
        setInterval(function () {


            $("#category").load("loadmembers.php");
            refresh();

        }, 100);
    });
</script>

<script>
    $(document).ready(function () {
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