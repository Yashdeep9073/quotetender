<?php

// declare(strict_types=1);
session_start();


if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}


$name = $_SESSION['login_user'];

include("db/config.php");

$en = $_GET["id"];

$d = base64_decode($en);


$result = mysqli_query($db, "SELECT * FROM members WHERE member_id='" . $d . "'");
$row = mysqli_fetch_row($result);

/* Attempt to connect to MySQL database */
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $fname = $_POST['firmname'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $city = $_POST['city'];

    $status = $_POST['status'];
    $tender = (int) $_POST['tender'];
    $oldRequests = empty($row[11]) ? 0 : $row[11];

    $pending_request = ($tender - $oldRequests) + (int) $row[12];

    if (!empty($_POST['password'])) {

        $password = md5($_POST['password']);
        mysqli_query($db, "UPDATE members set name=' $name', firm_name='$fname',mobile='$mobile',email_id='$email',city_state='$city',
    password='$password',status='$status', max_request='$tender',pending_request='$pending_request' WHERE member_id='" . $d . "'");
        $stat = 1;
        $re = base64_encode($stat);
        echo ("<SCRIPT LANGUAGE='JavaScript'>
window.location.href='registered-users.php?status=$re';
</SCRIPT>");

    } else {
        mysqli_query($db, "UPDATE members set name='$name', firm_name='$fname',mobile='$mobile',email_id='$email',city_state='$city',
      status='$status', max_request='$tender',pending_request='$pending_request' WHERE member_id='" . $d . "'");
        $stat = 1;
        $re = base64_encode($stat);
        echo ("<SCRIPT LANGUAGE='JavaScript'>
window.location.href='registered-users.php?status=$re';
</SCRIPT>");

    }
}



?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Update Members </title>



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
                                <h5 class="m-b-10">Update Registered Members
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
                                            <div class="form-group">Name*
                                                <label class="sr-only control-label" for="name">Name<span class=" ">
                                                    </span></label>
                                                <input id="name" name="name" type="text" placeholder="Username"
                                                    class="form-control input-md" required
                                                    value="<?php echo $row[1]; ?>">
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Firm Name*
                                                <label class="sr-only control-label" for="name">Firm Name<span
                                                        class=" ">
                                                    </span></label>
                                                <input id="name" name="firmname" type="text" placeholder=" Firm name *"
                                                    class="form-control input-md" required
                                                    value="<?php echo $row[2]; ?>">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Mobile*
                                                <label class="sr-only control-label" for="name">Mobile<span class=" ">
                                                    </span></label>
                                                <input id="name" name="mobile" type="number" placeholder=" Mobile *"
                                                    class="form-control input-md" required
                                                    value="<?php echo $row[3]; ?>">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Email*
                                                <label class="sr-only control-label" for="name">Email<span class=" ">
                                                    </span></label>
                                                <input id="name" name="email" type="email" class="form-control input-md"
                                                    required placeholder="Enter Email" value="<?php echo $row[4]; ?>">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">city*
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="name" name="city" type="text" class="form-control input-md"
                                                    required placeholder="City" value="<?php echo $row[5]; ?>">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Password*
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="name" name="password" type="password"
                                                    class="form-control input-md"
                                                    placeholder="If you want to chage the current password" value="">
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Status*
                                                <label class="sr-only control-label" for="name">Status<span class=" ">
                                                    </span></label>
                                                <select name="status" class="form-control" required>
                                                    <option value="1" <?= ($row['8'] == 1) ? 'selected' : ''; ?>>
                                                        Enable</option>
                                                    <option value="0" <?= ($row['8'] == 0) ? 'selected' : ''; ?>>
                                                        Disabled</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Free Tender*
                                                <label class="sr-only control-label" for="name">Status<span class=" ">
                                                    </span></label>
                                                <input id="name" name="tender" type="number"
                                                    placeholder=" Free Tender *" class="form-control input-md"
                                                    value="<?php echo $row[11]; ?>">
                                            </div>
                                        </div>


                                        <!-- Text input-->



                                        <!-- Button -->
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">


                                            <button type="submit" class="btn btn-secondary" name="submit" id="submit">
                                                <i class="feather icon-save lg"></i>&nbsp; Update
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