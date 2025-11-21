<?php

session_start();
error_reporting(0);
$upload_directory = "brand/";
if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

$name = $_SESSION['login_user'];
include("db/config.php");
$en = $_GET["sect"];
$d = base64_decode($en);
// Register user
if (isset($_POST['submit'])) {
    $name = $_POST['sect'];
    $department = $_POST['department'];
    $status = $_POST['status'];



    mysqli_query($db, "UPDATE  section set section_name ='$name', department_id='$department', status='$status' WHERE section_id='" . $d . "'");
    $stat = 1;
    $re = base64_encode($stat);
    echo ("<SCRIPT LANGUAGE='JavaScript'>
    window.location.href='view-section.php?status=$re';
    </SCRIPT>");
}

try {
    //code...

    $stmtFetchDepartment = $db->prepare("Select * From department Where status = 1");
    $stmtFetchDepartment->execute();
    $departments = $stmtFetchDepartment->get_result()->fetch_all(MYSQLI_ASSOC);


} catch (\Throwable $th) {
    //throw $th;
}

$result = mysqli_query($db, "SELECT * FROM  section WHERE section_id='" . $d . "'");
$row = mysqli_fetch_row($result);

?>


<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Edit Section </title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="Codedthemes" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
            color: black !important;
        }

        .select2-container--default .select2-selection--single {
            height: auto !important;
            min-height: 35px;
            border: 1px solid #d8d8d8 !important;
            /* border-radius: 2px !important; */
            width: 100% !important;
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
                                <h5 class="m-b-10"> Edit Section
                                </h5>
                            </div>

                        </div>
                    </div>
                </div>
            </div>


            <div class="row">

                <div class="col-sm-12">
                    <div class="card">
                        <?php
                        if ($msg) {
                            echo $msg;
                        }
                        ?>

                        <div class="card-header table-card-header">
                            <form class="contact-us" method="post" action="" enctype="multipart/form-data"
                                autocomplete="off">
                                <div class=" ">
                                    <!-- Text input-->
                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group">Section Name
                                                <label class="sr-only control-label" for="name"> Section Name<span
                                                        class=" ">
                                                    </span></label>
                                                <input id="category" name="sect" type="text"
                                                    placeholder=" Enter the Section Name" value="<?php echo $row[1]; ?>"
                                                    class="form-control input-md" required
                                                    oninvalid="this.setCustomValidity('Please Enter Section Name')"
                                                    oninput="setCustomValidity('')">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group"> Department
                                                <label class="sr-only control-label" for="name">Department<span
                                                        class=" ">
                                                    </span></label>
                                                <select name="department" id="department-input"
                                                    value="<?php echo $row[3]; ?>">
                                                    <option value=""></option>
                                                    <?php foreach ($departments as $department) { ?>
                                                        <option value="<?= $department['department_id'] ?>"
                                                            <?= $row[3] == $department['department_id'] ? "selected" : "" ?>>
                                                            <?= $department['department_name'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group">Status*
                                                <label class="sr-only control-label" for="name"> Status<span class=" ">
                                                    </span></label>
                                                <select id="" name="status" class="form-control" required>
                                                    <option value="<?php echo $row['2']; ?>">
                                                        <?php if ($row['2'] == 1) {
                                                            echo "Enable";
                                                        } else {
                                                            echo "Disable";
                                                        } ?>
                                                    </option>
                                                    <?php if ($row['2'] == 1) { ?>
                                                        <option value="0">Disable</option>
                                                    <?php } ?>
                                                    <?php if ($row['2'] == 0) { ?>
                                                        <option value="1">Enable</option>
                                                    <?php } ?>


                                                </select>
                                            </div>
                                        </div>
                                        <!-- Text input-->



                                        <!-- Button -->
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">


                                            <button type="submit" class="btn btn-secondary" name="submit" id="submit">
                                                <i class="feather icon-save"></i>&nbsp; Update Section
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



    <!-- jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 (must come AFTER jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $("#goldmessage").delay(5000).slideUp(300);

            $('#department-input').select2({
                placeholder: "Select Department",
                width: "100%"
            });
        });
    </script>
</body>

</html>