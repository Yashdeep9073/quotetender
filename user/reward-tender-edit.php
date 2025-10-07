<?php
session_start();
require_once "../env.php";
require_once "../vendor/autoload.php";
include("../login/db/config.php");


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if (!isset($_SESSION["login_register"])) {
    header("location: ../index.php");
}

$name = $_SESSION['login_register'];


try {

    //code...
    $stmtFetchUser = $db->prepare("SELECT * FROM members WHERE status = 1 AND email_id = ?");
    $stmtFetchUser->bind_param("s",$name);
    $stmtFetchUser->execute();
    $user = $stmtFetchUser->get_result()->fetch_array(MYSQLI_ASSOC);
    
   $userName= $user['name'];
    

} catch (\Throwable $th) {
    //throw $th;
}

$en = $_GET["id"];
$d = base64_decode($en);


$userRequestQuery="SELECT department.department_name,  ur.tenderID,ur.created_at,
ur.due_date, ur.file_name,  ur.status, ur.id FROM user_tender_requests ur 
inner join department on ur.department_id = department.department_id WHERE ur.id='" . $d . "'";
$userRequestData = mysqli_query($db, $userRequestQuery);
$userRequest = mysqli_fetch_row($userRequestData);


if (isset($_POST['submit'])) {

    $award = $_POST['remark'];
    date_default_timezone_set('Asia/Kolkata');

    $date =  date('Y-m-d H:i:s');
    $query1 = mysqli_query($db, "UPDATE user_tender_requests set  remark='$award',remarked_at= '$date' WHERE id ='"  . $d . "'");

    if ($query1 > 0) {
        $msg = "
        <div class='alert alert-success alert-dismissible fade show' role='alert' style='font-size:16px;' id='goldmessage'>
        <strong><i class='feather icon-check'></i>Thanks!</strong>Tender  has been awarded
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
          <span aria-hidden='true'>&times;</span>
        </button>
      </div>
        ";
//         $mail = new PHPMailer(true);

// // Enable SMTP debugging (0 = off, 2 = client/server messages)
// $mail->SMTPDebug = 0;

// // Set PHPMailer to use SMTP
// $mail->isSMTP();

// // Set SMTP host name                      
// $mail->Host = getenv('SMTP_HOST');

// // Set this to true if SMTP host requires authentication to send email
// $mail->SMTPAuth = true;

// // Provide username and password
// $mail->Username = getenv('SMTP_USER_NAME');
// $mail->Password = getenv('SMTP_PASSCODE');

// // Set encryption - use 'tls' instead of 'ssl' for most modern SMTP servers
// $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // or 'tls'

// // Set TCP port to connect to (587 for TLS, 465 for SSL)
// $mail->Port = (int) getenv('SMTP_PORT'); // Convert to integer

// // Set sender
// $mail->From = getenv('SMTP_USER_NAME');
// $mail->FromName = "Award Tender";

// // Add recipient
// $email = $user['email_id'];
// $mail->addAddress($email, "Recipient Name");

// // Set email format to HTML
// $mail->isHTML(true);

// // Set subject and body
// $mail->Subject = "Award Tender";
// $mail->Body = "<p>Dear Admin,<br/>" .
//               "The Tender has been accepted by " . htmlspecialchars($userName) . ". Kindly follow up the same.<br/><br/>" .
//               "<strong>Quote Tender</strong><br/>" .
//               "Mobile: +91-9870443528 | Email: info@quotender.com</p>";

// try {
//     $mail->send();
//     echo "Email sent successfully!";
// } catch (Exception $e) {
//     echo "Mailer Error: " . $mail->ErrorInfo;
//     die();
// }

$_SESSION['success'] = "Tender  has been awarded";



        echo ("<SCRIPT LANGUAGE='JavaScript'>
   
window.location.href='tender-request.php?status=$res';
</SCRIPT>");
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Award Tender</title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

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
                                <h5 class="m-b-10">Award Tender
                                </h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>

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

                            <br />

                            <form class="contact-us" method="post" action="" enctype="multipart/form-data"
                                autocomplete="off">
                                <div class=" ">
                                    <!-- Text input-->
                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group">Department
                                                <label class="sr-only control-label" for="name">Name<span class=" ">
                                                    </span></label>
                                                <input id="name" name="name" type="text"
                                                    placeholder=" Enter the Mobile No" class="form-control input-md"
                                                    required
                                                    oninvalid="this.setCustomValidity('Please Enter Mobile No')"
                                                    oninput="setCustomValidity('')"
                                                     value="<?php echo $userRequest[0]; ?>"
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group">Tender No
                                                <label class="sr-only control-label" for="name">Email<span class=" ">
                                                    </span></label>
                                                <input id="fname" name="fname" type="text" placeholder=" Enter Email"
                                                    class="form-control input-md" required
                                                    oninvalid="this.setCustomValidity('Please Enter Email Id')"
                                                    oninput="setCustomValidity('')" value="<?php echo $userRequest[1]; ?>"
                                                    readonly>
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group">Add Date
                                                <label class="sr-only control-label" for="name">Title<span class=" ">
                                                    </span></label>
                                                <input id="mobile" name="mobile" type="text"
                                                    placeholder=" Enter the title " class="form-control input-md"
                                                    required oninvalid="this.setCustomValidity('Enter the title')"
                                                    oninput="setCustomValidity('')" value="<?php echo date_format(date_create($userRequest[2]),"Y-m-d "); ?>"
                                                    readonly>
                                            </div>


                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group">Start Date
                                                <label class="sr-only control-label" for="name">Title<span class=" ">
                                                    </span></label>
                                                <input id="email" name="email" type="text"
                                                    placeholder=" Enter the title " class="form-control input-md"
                                                    required oninvalid="this.setCustomValidity('Enter the title')"
                                                    oninput="setCustomValidity('')" readonly
                                                    value="<?php echo date_format(date_create($userRequest[3]),"Y-m-d "); ?>">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Remark*
                                                <label class="sr-only control-label" for="name">Username<span class=" ">
                                                    </span></label>
                                                <select id="" name="remark" class="form-control" required>
                                                    <option value="">Select</option>
                                                    <option value="accepted">Accept</option>
                                                    <option value="denied">Deny</option>

                                                </select>

                                            </div>
                                        </div>



                                        <!-- Text input-->



                                        <!-- Button -->
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">


                                            <button type="submit" class="btn btn-secondary" name="submit" id="submit">
                                                <i class="feather icon-save"></i>&nbsp;Award Tender
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
    <script>
    $(document).ready(function() {
        $("#goldmessage").delay(5000).slideUp(300);
    });
    </script>


</body>

</html>