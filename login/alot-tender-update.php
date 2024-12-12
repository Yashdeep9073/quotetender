<?php

session_start();

require_once "../vendor/autoload.php";
require "../env.php";



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(0);

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}


$name = $_SESSION['login_user'];

include("db/config.php");

$en = $_GET["id"];


$d = base64_decode($en);

if (isset($_POST['submit'])) {
    $user = $_POST['user'];
    $days = $_POST['day'];

   

    if($user=='other'){
        $name = $_POST['name'];
        $firmname = $_POST['company'];
        $newUserEmail = $_POST['email'];
        $phone = $_POST['phone'];
        $created_date = date('Y-m-d H:i:s A');

        $addMember = "insert into members (name, firm_name, email_id,mobile,created_date) values ('$name', '$firmname','$newUserEmail',
        '$phone','$created_date')";

        mysqli_query($db, $addMember);

        $newMemberQuery = "SELECT member_id FROM members where email_id='"  . $newUserEmail . "' order by member_id desc limit 1";
        $newMemberQueryResult = mysqli_query($db, $newMemberQuery);

         $addedUser = mysqli_fetch_row($newMemberQueryResult);
         $user=$addedUser[0];
    }

    $status = 2;
    date_default_timezone_set('Asia/Kolkata');
    $allotted_at = date('Y-m-d H:i:s');
 
    mysqli_query($db, "UPDATE user_tender_requests set `status`='Allotted',`selected_user_id`='$user',
    `reminder_days`='$days', `allotted_at`='$allotted_at'  WHERE id='"  . $d . "'");
    
    $query = "SELECT email_id FROM members WHERE member_id='" . $user . "'";
    
    $result = mysqli_query($db, $query);

    $row = mysqli_fetch_row($result);

    $email = $row[0];
    
    $mail = new PHPMailer(true);

    //Enable SMTP debugging.

    $mail->SMTPDebug = 0;


    //Set PHPMailer to use SMTP.

    $mail->isSMTP();

    //Set SMTP host name                      

     $mail->Host = getenv('SMTP_HOST');

                //Set this to true if SMTP host requires authentication to send email

                $mail->SMTPAuth = true;

                //Provide username and password

                $mail->Username = getenv('SMTP_USER_NAME');

                $mail->Password = getenv('SMTP_PASSCODE');

                //If SMTP requires TLS encryption then set it

                $mail->SMTPSecure = "ssl";

                //Set TCP port to connect to

                $mail->Port = getenv('SMTP_PORT');

                $mail->From = getenv('SMTP_USER_NAME');


    $mail->FromName = "Quote Tender  ";

  $adminEmail=getenv('SMTP_USER_NAME');

    $mail->addAddress($email, "Recepient Name");
$mail->addAddress($adminEmail);
    $mail->isHTML(true);

    $mail->Subject = "Alot Tender";
  $qt1="SELECT user_tender_requests.tenderID,members.name
FROM user_tender_requests
INNER JOIN members ON user_tender_requests.member_id =members.member_id WHERE id='" . $d . "'";
$qty= mysqli_query($db, $qt1);
        $qty = mysqli_fetch_row($qty);
        $uname=$qty[0];

    $mail->Body = "
<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
    <div style='text-align: center; margin-bottom: 20px;'>
        <img src='https://www.quotetender.in/assets/images/logo/logo.png' alt='Quote Tender Logo' style='max-width: 200px; height: auto;'>
    </div>
    <p style='font-size: 18px; color: #555;'>Dear <strong>". $qty[1] ."</strong>,</p>
    <p>We are pleased to inform you that the <strong>Tender ID:</strong> " . htmlspecialchars($uname) . " has been allotted to you. For any further assistance or queries regarding the process, please feel free to contact us. We are here to help!</p>
    
    <p style='margin-top: 20px;'>
        <strong>Thanks & Regards,</strong><br/>
        <span style='color: #4CBB17;'>Admin, Quote Tender</span><br/>
        <span>Mobile: <a href='tel:+919417601244' style='color: #4CBB17; text-decoration: none;'>+91-9417601244</a></span><br/>
        <span>Email: <a href='mailto:info@quotender.com' style='color: #4CBB17; text-decoration: none;'>info@quotender.com</a></span>
    </p>

    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>

    <p style='text-align: center; font-size: 12px; color: #888;'>
        © 2024 Quote Tender. All Rights Reserved.
    </p>
</div>";


    if (!$mail->send()) {

        echo "Mailer Error: " . $mail->ErrorInfo;
    }


    $stat = 1;
    $re = base64_encode($stat);
    echo ("<SCRIPT LANGUAGE='JavaScript'>
    window.location.href='alot-tender.php?status=$re';
    </SCRIPT>");
}



$requestQuery = mysqli_query($db, "SELECT ur.tenderID, ur.tender_no, ur.reference_code, ur.name_of_work,
department.department_name, s.section_name, ur.selected_user_id , ur.reminder_days, sm.name, sm.email_id, sm.mobile
FROM user_tender_requests ur 
inner join members sm on ur.selected_user_id= sm.member_id
inner join section s on ur.section_id=s.section_id
inner join department on ur.department_id = department.department_id where ur.id='"  . $d . "'");

$requestData = mysqli_fetch_row($requestQuery);


$memberQuery = "SELECT * FROM members";
$members = mysqli_query($db, $memberQuery);



?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Update Alot Tender </title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="#" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

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

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" /> -->
    
    <!-- Include Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Include Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script>
  $(document).ready(function() {
        //     $('#myDropdown').selectize({
        //     sortField: 'text'
        // });

        $('#myDropdown').select2({
        placeholder: "Select User",
        allowClear: true
        });
    
    // Select the dropdown and the other fields
    var $dropdown = $('#myDropdown');
    var $otherFields = $('#otherFields');

    // Listen for changes in the dropdown selection
    $dropdown.on('change', function() {
      var selectedValue = $dropdown.val();

      // If "Other" is selected, show the text boxes; otherwise, hide them
      if (selectedValue === 'other') {
        $otherFields.show();
      } else {
        $otherFields.hide();
      }
    });
  });
</script>
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
                                <h5 class="m-b-10">Update Alot Tender
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
                                                    required value="<?php echo $requestData[0]; ?>">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Tender No :
                                                <label class="sr-only control-label" for="name">Tender No *<span
                                                        class=" ">
                                                    </span></label>
                                                <input id="name" name="code" type="text" placeholder=" Enter Code *"
                                                    class="form-control input-md" required
                                                    value="<?php echo $requestData[1]; ?>" readonly="">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Ref No :
                                                <label class="sr-only control-label" for="name">Email<span class=" ">
                                                    </span></label>
                                                <input id="name" name="work" type="work" class="form-control input-md"
                                                    required placeholder="Name of the work"
                                                    value="<?php echo $requestData[2]; ?>" readonly="">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Work Name :
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="name" name="tender" type="text" class="form-control input-md"
                                                    required placeholder="Enter tender id"
                                                    value="<?php echo $requestData[3]; ?>" readonly="">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Department :
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="name" name="tender" type="text" class="form-control input-md"
                                                    required placeholder="Enter tender id"
                                                    value="<?php echo $requestData[4]; ?>" readonly="">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Section :
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="name" name="tender" type="text" class="form-control input-md"
                                                    required placeholder="Enter tender id"
                                                    value="<?php echo $requestData[5]; ?>" readonly="">
                                            </div>
                                        </div>
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                            <h5>Update Alot Tender</h5>
                                            <hr />
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Selected User*
                                            <input id="name" name="tender" type="text" class="form-control input-md"
                                                    required placeholder="Enter tender id"
                                                    value="<?php echo $requestData[8] . "-" . $requestData[9] . "-" . $requestData[10]; ?>" readonly="">
                                            </div>
                                        </div>
                                       
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Set Reminder
                                                <label class="sr-only control-label" for="name">Set Reminder<span
                                                        class=" ">
                                                    </span></label>
                                                <select name="day" id="day" class="form-control">
                                                    <?php
                                                    for ($day = 0; $day <= 365; $day++) {
                                                        $selected=$day==$requestData[7] ? "selected=''" : '';
                                                        echo "<option value=\"$day\" $selected>$day Days</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Edit User*
                                                <label class="sr-only control-label" for="name">Departments*<span
                                                        class=" ">
                                                    </span></label>
                                                <?php

                                                   echo "<select class='form-control' name='user' required id='myDropdown'>
                                                <option value=''>Select</option>";
                                                while ($row = mysqli_fetch_row($members)) {
                                                  
                                                    echo "<option value='" . $row['0'] . "'>" . $row['1'] . "-" . $row['4'] . "-" . $row['3'] . "</option>";
                                                }
                                                
                                                 echo '<option value="other">Other</option>';
                                                
                                                echo "</select>";
                                                ?>
                                            </div>
                                        </div>
                                        <!-- Text input-->
                                        
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12" id="otherFields" style="display: none;">
                                            <div class="form-group">Name <span style="color:red;"> *</span>
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="name" name="name" type="text" class="form-control input-md"
                                              placeholder="Enter Name"
                                                  >
                                            </div>
                                            
                                            
                                            
                                             <div class="form-group">Email<span style="color:red;"> *</span>
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="email" name="email" type="text" class="form-control input-md"
                                                   placeholder="Enter Email Id"
                                                  >
                                            </div>
                                            
                                            
                                            
                                             <div class="form-group">Phone <span style="color:red;"> *</span>
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="phone" name="phone" type="text" class="form-control input-md"
                                                  placeholder="Enter Phone No"
                                                  >
                                            </div>
                                            
                                            
                                            
                                            
                                             <div class="form-group">Company Name
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="company" name="company" type="text" class="form-control input-md"
                                                    placeholder="Enter Company Name"
                                                  >
                                            </div>
                                        </div>

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
</body>

</html>