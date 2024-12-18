<?php
session_start();
include("db/config.php");

require_once "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

$name = $_SESSION['login_user'];
// Register user


if (isset($_POST['submit'])) {
    $admin_email = $_POST['email'];
    $cc = $_POST['cc'];
    $address = $_POST['address'];


    $mail = new PHPMailer(true);

    //Enable SMTP debugging.

    $mail->SMTPDebug = 0;


    //Set PHPMailer to use SMTP.

    $mail->isSMTP();

    //Set SMTP host name                      
    $mail->Host = "smtp.hostinger.com";

    //Set this to true if SMTP host requires authentication to send email

    $mail->SMTPAuth = true;

    //Provide username and password

    $mail->Username = "info@quotetender.in";

    $mail->Password = "Zxcv@123";

    //If SMTP requires TLS encryption then set it

    $mail->SMTPSecure = "ssl";

    //Set TCP port to connect to

    $mail->Port = 465;

    $mail->From = "info@quotetender.in";

    $mail->FromName = "Quote Tender  ";

    $mail->addAddress($admin_email, "Recepient Name");
    $mail->addAddress($cc);

    $mail->isHTML(true);

    $mail->Subject = "Regarding Tender Information";

    $mail->Body =  " $address";



    if (!$mail->send()) {

        echo "Mailer Error: " . $mail->ErrorInfo;
    }
    $msg = "
            <div class='alert alert-success alert-dismissible fade show' role='alert' style='font-size:16px;' id='goldmessage'>
            <strong><i class='feather icon-check'></i>Thanks!</strong>Email has been send Successfully
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
              <span aria-hidden='true'>&times;</span>
            </button>
          </div>
            ";
}



?>


<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Send Email</title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="Codedthemes" />

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
                    <a href="#!" class="full-screen" onClick="javascript:toggleFullScreen()"><i class="feather icon-maximize"></i></a>
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
                                <h5 class="m-b-10">Send Email to user


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


                            <?php
                            if(!empty($msg)){
                                echo $msg;
                            }
                            ?>

                            <div class="row">


                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-header table-card-header">

                                            <h4>Send Email to user</h4>
                                            <hr />
                                            <form action="" method="post">
                                                <div class="row">
                                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-6">
                                                        <div class="form-group">TO *
                                                            <label class="sr-only control-label" for="name">To *
                                                                *<span class=" ">
                                                                </span></label>
                                                            <input id="name" name="email" type="email" placeholder=" Enter Email to Address   *" class="form-control input-md" required oninvalid="this.setCustomValidity('Please Enter Email  *')" oninput="setCustomValidity('')">
                                                        </div>
                                                    </div>



                                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-6">
                                                        <div class="form-group">CC*
                                                            <label class="sr-only control-label" for="name">Email Hosts *
                                                                *<span class=" ">
                                                                </span></label>
                                                            <input id="name" name="cc" type="email" placeholder=" Enter Email Hosts  *" class="form-control input-md" required oninvalid="this.setCustomValidity('Please Enter CC Email *')" oninput="setCustomValidity('')">
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-6">
                                                        <div class="form-group">Message*



                                                            <textarea name="address" class="form-control" placeholder="Enter Address" required> </textarea>

                                                        </div>
                                                    </div>
                                                    
                                                    
                                                     <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-6">
                                                        <div class="form-group">Upload Quotation*



                                        <input name="uploaded_file" type="file" class="form-control input-md" accept="application/pdf" >

                                                        </div>
                                                    </div>

                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">

                                                        <button type="submit" class="btn btn-secondary" name="submit" id="submit">
                                                            <i class="feather icon-save lg"></i>&nbsp; Sent Email
                                                        </button>


                                                    </div>

                                            </form>
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
    <script src="ckeditor/ckeditor.js"></script>
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
        $(document).ready(function() {
            $("#goldmessage").delay(5000).slideUp(300);
        });
    </script>

    <script>
        CKEDITOR.replace('address');
    </script>

</body>

</html>