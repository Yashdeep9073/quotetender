<?php

session_start();
require_once "../vendor/autoload.php";
require_once "../env.php";


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$upload_directory = "tender/";
if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}


$name = $_SESSION['login_user'];

include("db/config.php");

$en = $_GET["id"];

$d = base64_decode($en);

if (isset($_POST['submit'])) {

    //DEBUG
    // echo"<pre>";
    // print_r($_POST);


    $tender = $_POST['tenderno'];
    $code = $_POST['code'];
    $work = $_POST['work'];
    $tender1 = $_POST['tender'];
    $dept = $_POST['department'];

    $section = $_POST['coutrycode'];
    $division_id = $_POST['statelist'];
    $sub_division_id = $_POST['city'];
    $tentative_cost=$_POST['tentative_cost'];
    $autoEmail = $_POST['autoEmail'];

    $unique_filename1=$unique_filename2=null;
    if (!empty($_FILES["uploaded_file2"]["tmp_name"])) {
        $file_size2 = $_FILES["uploaded_file2"]["size"];
        if (isset($file_size2) && $file_size2 < 8 * 1024 * 1024) {
            $temp_name2 = $_FILES["uploaded_file2"]["tmp_name"];
            $original_name2 = $_FILES["uploaded_file2"]["name"];
            $unique_filename2 = uniqid() . '_' . $original_name2;
            move_uploaded_file($temp_name2, $upload_directory . $unique_filename2);
            $fileUploaded2 = true;
        } else {
            $fileUploaded2 = false;
        }
    }

    if (!empty($_FILES["uploaded_file1"]["tmp_name"])) {
        $file_size1 = $_FILES["uploaded_file1"]["size"];
        if (isset($file_size1) && $file_size1 < 8 * 1024 * 1024) {
            $temp_name1 = $_FILES["uploaded_file1"]["tmp_name"];
            $original_name1 = $_FILES["uploaded_file1"]["name"];
            $unique_filename1 = uniqid() . '_' . $original_name1;
            move_uploaded_file($temp_name1, $upload_directory . $unique_filename1);
            $fileUploaded1 = true;
        } else {
            $fileUploaded1 = false;
        }
    }

    mysqli_select_db($db, DB_NAME);
    if($fileUploaded1 == true || $fileUploaded2 == true){
           
            // Delete the old file
            $query = "SELECT user_tender_requests.file_name , user_tender_requests.file_name2,user_tender_requests.tenderID  FROM user_tender_requests WHERE id='"  . $d . "'";
            $result = mysqli_query($db, $query);
            $row = mysqli_fetch_row($result);
            if (!empty($row['0'])) {
                $old = "tender/" . $row['0']; // Path to the old file
                unlink($old);
            }

            if (!empty($row['1'])) {
                $old = "tender/" . $row['1']; // Path to the old file
                unlink($old);
            }
   
            date_default_timezone_set('Asia/Kolkata');

            $sent_at = date('Y-m-d H:i:s A');

            $tender2 = $row['2'];

            mysqli_query($db, "UPDATE user_tender_requests set `tender_no` ='$tender', `reference_code`='$code',`tenderID`='$tender1', 
            `tentative_cost`='$tentative_cost', `department_id`='$dept',`section_id`='$section',`sub_division_id`='$sub_division_id',`division_id`='$division_id',`name_of_work`='$work',
            `file_name`='$unique_filename1',`file_name2`='$unique_filename2',`status`='Sent', `sent_at`='$sent_at' , `auto_quotation`='$autoEmail' WHERE `tenderID`='" . $tender2 ."' ");

            //auto quotation 
            $autoEmailQuery = mysqli_query($db,"SELECT `auto_quotation` FROM user_tender_requests WHERE `id`= '"  . $d . "' ");
            $autoEmailResult = mysqli_fetch_assoc($autoEmailQuery); 
            $autoEmailResponse = $autoEmailResult["auto_quotation"];

            $stat = 1;
            $re = base64_encode($stat);
            
            if($autoEmailResponse == 1){
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
                    $adminEmail = getenv('SMTP_USER_NAME');
    
                    $mail->addAddress($adminEmail);
                    $mail->IsHTML(true);
    
                    // $membersQuery = "SELECT m.email_id,  m.name, ur.file_name, ur.file_name2, ur.tenderID, ur.id FROM user_tender_requests ur 
                    // inner join members m on ur.member_id= m.member_id  WHERE ur.id='"  . $d . "'";
    
                    $membersQuery = "SELECT m.email_id,  m.name, ur.file_name, ur.file_name2, ur.tenderID, ur.id FROM user_tender_requests ur 
                    inner join members m on ur.member_id= m.member_id  WHERE ur.auto_quotation = '1' AND ur.tenderID='" . $tender2 ."' ";
                    $membersResult = mysqli_query($db, $membersQuery);
                    while($memberData = mysqli_fetch_row($membersResult)){
                        $mail->addAddress($memberData[0], "Recepient Name");
                    
                    
                    $mail->Subject = "Tender Request Approved";
                    
                 // Adding primary attachment
$mail->addAttachment($upload_directory . $memberData[2]);

// Adding secondary attachment if available
if (!empty($memberData[3])) {
    $mail->addAttachment($upload_directory . $memberData[3]);
}

// Email body
$mail->Body = "
<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
    <div style='text-align: center; margin-bottom: 20px;'>
        <img src='https://www.quotetender.in/assets/images/logo/logo.png' alt='Quote Tender Logo' style='max-width: 200px; height: auto;'>
    </div>
    <p style='font-size: 18px; color: #555;'>Dear <strong>" . htmlspecialchars($memberData[1]) . "</strong>,</p>
    <p>We are pleased to inform you that the <strong>Tender ID:</strong> " . htmlspecialchars($memberData[4]) . " has been approved for you. The quotation file is attached below for your reference.</p>
    
    <p>If you have any questions or need further assistance regarding the process, feel free to reach out to us. We are here to help!</p>
    
    <p style='margin-top: 20px;'>
        <strong>Thanks & Regards,</strong><br/>
        <span style='color: #4CBB17;'>Admin, Quote Tender</span><br/>
        <span>Mobile: <a href='tel:+919417601244' style='color: #4CBB17; text-decoration: none;'>+91-9417601244</a></span><br/>
        <span>Email: <a href='mailto:help@quotetender.in' style='color: #4CBB17; text-decoration: none;'>help@quotetender.in</a></span>
    </p>

    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>

    <p style='text-align: center; font-size: 12px; color: #888;'>
        © 2024 Quote Tender. All Rights Reserved.
    </p>
</div>";


                }
                    if (!$mail->send()) {
    
                        echo "Mailer Error: " . $mail->ErrorInfo;
                    }
    
                    echo ("<SCRIPT LANGUAGE='JavaScript'>
                    window.location.href='tender-request.php?status=$re';
                    </SCRIPT>");
            }else{
                echo ("<SCRIPT LANGUAGE='JavaScript'>
                    window.location.href='tender-request.php?status=$re';
                    </SCRIPT>");
            }


        } else {
            $msg = "<div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='goldmessage'>
        <strong><i class='feather icon-check'></i>Error !</strong> File size exceeds the limit of 3MB.
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
          <span aria-hidden='true'>&times;</span>
        </button>
      </div>";
        }
    }



$requestQuery = mysqli_query($db, "SELECT department.department_name, ur.file_name, ur.tenderID, ur.id 
FROM user_tender_requests ur 
inner join members m on ur.member_id= m.member_id
inner join department on ur.department_id = department.department_id where ur.id='"  . $d . "'");

$requestData = mysqli_fetch_row($requestQuery);


$departmentQuery = "SELECT * FROM department ";
$departments = mysqli_query($db, $departmentQuery);

$sectionQuery = "SELECT * FROM section where status=1";
$sections = mysqli_query($db, $sectionQuery);

?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Tender Update </title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="#" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        function getstate(val) {
            //alert(val);
            $.ajax({
                type: "POST",
                url: "get_state.php",
                data: 'coutrycode=' + val,
                success: function(data) {
                    $("#statelist").html(data);
                }
            });
        }

        function getcity(val) {
            //alert(val);
            $.ajax({
                type: "POST",
                url: "get_city.php",
                data: 'statecode=' + val,
                success: function(data) {
                    $("#city").html(data);
                }
            });
        }
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
                                <h5 class="m-b-10">Tender Update - Tender ID : <?php echo $requestData[2]; ?>
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
                                if (isset($msg)) {
                                    echo $msg;
                                }
                                ?>

                        <div class="card-header table-card-header">
                            <form class="contact-us" method="post" action="" enctype="multipart/form-data" autocomplete="off">
                                <div class=" ">
                                    <!-- Text input-->
                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">File1*
                                                <label class="sr-only control-label" for="name">File1*</label>
                                                <input name="uploaded_file1" type="file" class="form-control input-md" required accept="application/pdf,application/vnd.ms-excel">
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">File2*
                                                <label class="sr-only control-label" for="name">File2*</label>
                                                <input name="uploaded_file2" type="file" class="form-control input-md" accept="application/pdf,application/vnd.ms-excel">
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">CA No*
                                                <label class="sr-only control-label" for="name">Firm Name<span class=" ">
                                                    </span></label>
                                                <input id="tenderno" name="tenderno" type="text" placeholder=" Enter CA No *" class="form-control input-md" required value="">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Reference Code*
                                                <label class="sr-only control-label" for="name">Reference Code*<span class=" ">
                                                    </span></label>
                                                <input id="code" name="code" type="text" placeholder=" Enter Code *" class="form-control input-md" required value="">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Name of Work*
                                                <label class="sr-only control-label" for="name">Email<span class=" ">
                                                    </span></label>
                                                <input id="work" name="work" type="work" class="form-control input-md" required placeholder="Name of the work" value="">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Tender ID*
                                                <label class="sr-only control-label" for="name">Tender ID<span class=" ">
                                                    </span></label>
                                                <input id="tender" name="tender" type="text" class="form-control input-md" required placeholder="Enter tender id" value="<?php echo $requestData[2]; ?>">
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Tentative Cost*
                                                <label class="sr-only control-label" for="name">Tentative Cost<span class=" ">
                                                    </span></label>
                                                <input id="tentative_cost" name="tentative_cost" type="number" min="0" class="form-control input-md" required placeholder="Enter Tentative Cost" value="">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Departments*
                                                <label class="sr-only control-label" for="name">Departments*<span class=" ">
                                                    </span></label>
                                                <?php

                                                echo "<select class='form-control' name='department' required>";
                                                while ($row = mysqli_fetch_row($departments)) {
                                                    $selected = $requestData['0'] ==  $row['1'] ? "selected=''" : '';

                                                    echo "<option value='" . $row['0'] . "' " . $selected . ">" . $row['1'] . "</option>";
                                                }
                                                echo "</select>";
                                                ?>
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group"> Section
                                                <label class="sr-only control-label" for="name">Section<span class=" ">
                                                    </span></label>
                                                <select onChange="getstate(this.value);" name="coutrycode" id="section" class="form-control" required>
                                                    <option value="">Select Section</option>
                                                    <?php
                                                    while ($row = mysqli_fetch_row($sections)) {
                                                        echo "<option value='" . $row['0'] . "'>" . $row['1'] . "</option>";
                                                    }

                                                    ?>

                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">

                                            <div class="form-group"> Division
                                                <label class="sr-only control-label" for="name">Section<span class=" ">
                                                    </span></label>

                                                <select class='form-control' name="statelist" id="statelist" onChange="getcity(this.value);">
                                                    <option value=''>Select Division </option>

                                                </select>

                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">

                                            <div class="form-group"> Sub Division

                                                <select name="city" id="city" class="form-control">
                                                    <option value="">Select Sub Division</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                            <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">

                                            <div class="form-group"> Send Quotation Auotmatically
                                                <select name="autoEmail" id="auto-email" class="form-control" >
                                                    
                                                    <option value="">Select</option>
                                                    <option value="1" required>Yes</option>
                                                    <option value="0"required>No</option>
                                                    
                                                </select>
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
    <!-- <script type="text/javascript">
        $(document).ready(function() {
            $('#choose-file').next('label').html('Select a file');
        });
    </script> -->
</body>

</html>