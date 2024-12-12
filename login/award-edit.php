<?php

session_start();

require_once "../vendor/autoload.php";
require "../env.php";


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(0);
$upload_directory = "tender/";
if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}


$name = $_SESSION['login_user'];

include("db/config.php");

$en = $_GET["award"];


$d = base64_decode($en);

if (isset($_POST['submit'])) {
   

    $project_status = $_POST['project_status'];

    $query = "SELECT members.email_id FROM user_tender_requests ur inner join members on ur.member_id = members.member_id
    WHERE id='" . $d . "'";
    
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_row($result);
    $email = $row[0];

    $status = 2;
    date_default_timezone_set('Asia/Kolkata');

    $sent = date("Y-m-d H:i:s");

    mysqli_query($db, "UPDATE user_tender_requests set `project_status`='$project_status' WHERE id='"  . $d . "'"); 

    $stat = 1;
    $re = base64_encode($stat);
    echo ("<SCRIPT LANGUAGE='JavaScript'>
    window.location.href='award-tender.php?status=$re';
    </SCRIPT>");
}




$result = mysqli_query($db, "SELECT ur.tenderID, ur.tender_no, ur.reference_code, ur.name_of_work,
department.department_name, s.section_name, ur.project_status
FROM user_tender_requests ur 
inner join section s on ur.section_id=s.section_id
inner join department on ur.department_id = department.department_id where ur.id='"  . $d . "'");
$row = mysqli_fetch_row($result);


$ct = "SELECT * FROM members";
$result = mysqli_query($db, $ct);



?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Status of Award Tender </title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="#" />

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
                                <h5 class="m-b-10">Award status Alot Tender
                                </h5>
                            </div>

                        </div>
                    </div>
                </div>
            </div>


            <div class="row">

                <div class="col-sm-12">
                    <div class="card">




                        <div class="card-header table-card-header">
                            <form class="contact-us" method="post" action="" enctype="multipart/form-data"
                                autocomplete="off">
                                <div class=" ">
                                    <!-- Text input-->
                                    <div class="row">

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Tender ID :*
                                                <label class="sr-only control-label" for="name">Firm Name<span
                                                        class=" ">
                                                    </span></label>
                                                <input id="name" name="tender" type="text"
                                                    placeholder=" Enter Tender No *" class="form-control input-md"
                                                    required value="<?php echo $row[0]; ?>">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Tender No :
                                                <label class="sr-only control-label" for="name">Tender No *<span
                                                        class=" ">
                                                    </span></label>
                                                <input id="name" name="code" type="text" placeholder=" Enter Code *"
                                                    class="form-control input-md" required
                                                    value="<?php echo $row[1]; ?>">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Ref No :
                                                <label class="sr-only control-label" for="name">Email<span class=" ">
                                                    </span></label>
                                                <input id="name" name="work" type="work" class="form-control input-md"
                                                    required placeholder="Name of the work"
                                                    value="<?php echo $row[2]; ?>">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Work Name :
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="name" name="tender" type="text" class="form-control input-md"
                                                    required placeholder="Enter tender id"
                                                    value="<?php echo $row[3]; ?>">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Department :
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="name" name="tender" type="text" class="form-control input-md"
                                                    required placeholder="Enter tender id"
                                                    value="<?php echo $row[4]; ?>">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Section :
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="name" name="tender" type="text" class="form-control input-md"
                                                    required placeholder="Enter tender id"
                                                    value="<?php echo $row[5]; ?>">
                                            </div>
                                        </div>
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                            <h5>Update Status</h5>
                                            <hr />
                                        </div>
                                       
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Project Status
                                                <label class="sr-only control-label" for="name">Set Reminder<span
                                                        class=" ">
                                                    </span></label>
                                                <select name="project_status" id="day" class="form-control">
                                                   <option value="on process" <?php if($row['6']=="on process") { echo "selected";} ?> > On Process</option>
                                                    <option value="design" <?php if($row['6']=="design") { echo "selected";} ?> > Design</option>
                                                    <option value="complete" <?php if($row['6']=="complete") { echo "selected";} ?> > Complete</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Text input-->

                                        <hr />

                                        <!-- Button -->
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">


                                            <button type="submit" class="btn btn-secondary" name="submit" id="submit">
                                                <i class="feather icon-save lg"></i>&nbsp; Submit
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="dt-responsive table-responsive">


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
    <script src="assets/js/plugins/buttons.colVis.min.js"></script>
    <script src="assets/js/plugins/buttons.print.min.js"></script>
    <script src="assets/js/plugins/pdfmake.min.js"></script>
    <script src="assets/js/plugins/jszip.min.js"></script>
    <script src="assets/js/plugins/dataTables.buttons.min.js"></script>
    <script src="assets/js/plugins/buttons.html5.min.js"></script>
    <script src="assets/js/plugins/buttons.bootstrap4.min.js"></script>
    <script src="assets/js/pages/data-export-custom.js"></script>
</body>

</html>