<?php

session_start();

require_once "../vendor/autoload.php";
require "../env.php";


include("db/config.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// error_reporting(0);

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}


$name = $_SESSION['login_user'];


$en = $_GET["id"];


$d = base64_decode($en);

if (isset($_POST['submit'])) {

    try {



        $user = $_POST['user'];
        $days = $_POST['day'];

        // ---------- ADD MEMBER IF OTHER ----------
        if ($user === 'other') {
            $name = mysqli_real_escape_string($db, $_POST['name']);
            $firmname = mysqli_real_escape_string($db, $_POST['company']);
            $newUserEmail = mysqli_real_escape_string($db, $_POST['email']);
            $phone = mysqli_real_escape_string($db, $_POST['phone']);
            $state = mysqli_real_escape_string($db, trim($_POST['state']));
            $city = mysqli_real_escape_string($db, trim($_POST['city']));
            $created_date = date('Y-m-d H:i:s'); // ✅ MySQL safe

            $addMember = "
                INSERT INTO members
                (name, firm_name, city_state, state_code, email_id, mobile, created_date)
                VALUES
                ('$name', '$firmname', '$city', '$state', '$newUserEmail', '$phone', '$created_date')
            ";

            if (!mysqli_query($db, $addMember)) {
                $_SESSION['error'] = "Failed to add new member. Please try again.";
                header("Location: alot-tender.php");
                exit;
            }

            $user = mysqli_insert_id($db);
        }

        // ---------- UPDATE TENDER ----------
        date_default_timezone_set('Asia/Kolkata');
        $allotted_at = date('Y-m-d H:i:s');

        if (
            !mysqli_query(
                $db,
                "UPDATE user_tender_requests 
             SET status='Allotted',
                 selected_user_id='$user',
                 reminder_days='$days',
                 allotted_at='$allotted_at'
             WHERE id='$d'"
            )
        ) {
            $_SESSION['error'] = "Failed to allot tender. Please try again.";
            header("Location: alot-tender.php");
            exit;
        }

        // ---------- FETCH EMAIL ----------
        $result = mysqli_query($db, "SELECT email_id FROM members WHERE member_id='$user'");
        $row = mysqli_fetch_row($result);
        $email = $row[0] ?? null;

        if (!$email) {
            $_SESSION['error'] = "User email not found.";
            header("Location: alot-tender.php");
            exit;
        }

        // ---------- SEND EMAIL ----------
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = getenv('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USER_NAME');
        $mail->Password = getenv('SMTP_PASSCODE');
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = getenv('SMTP_PORT');

        $mail->setFrom(getenv('SMTP_USER_NAME'), 'Quote Tender');
        $mail->addAddress($email);
        $mail->addAddress(getenv('SMTP_USER_NAME'));
        $mail->addAddress('enquiry@dvepl.com');

        $mail->isHTML(true);
        $mail->Subject = "Tender Allotted";

        $qt = mysqli_query(
            $db,
            "SELECT ur.tenderID, m.name
             FROM user_tender_requests ur
             INNER JOIN members m ON ur.member_id = m.member_id
             WHERE ur.id='$d'"
        );
        $qt = mysqli_fetch_row($qt);

        $mail->Body = "
            <p>Dear <strong>{$qt[1]}</strong>,</p>
            <p>Your Tender ID <strong>{$qt[0]}</strong> has been allotted successfully.</p>
            <p>Thanks & Regards,<br/>Admin, DVEPL</p>
        ";

        if (!$mail->send()) {
            $_SESSION['error'] = "Tender allotted but email failed to send.";
            header("Location: alot-tender.php");
            exit;
        }

        // ---------- SUCCESS ----------
        $_SESSION['success'] = "Tender allotted successfully and email sent.";
        header("Location: alot-tender.php");
        exit;

    } catch (Throwable $th) {
        $_SESSION['error'] = "Something went wrong. Please try again.";
        header("Location: alot-tender.php");
        exit;
    }
}


try {
    // Fetch unique, non-empty cities only
    $stmtFetchStates = $db->prepare("SELECT * FROM state WHERE is_active = 1 ");
    $stmtFetchStates->execute();
    $states = $stmtFetchStates->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (\Throwable $th) {
    //throw $th;
}


// fetch city by state code with ajax
if (isset($_POST['stateCode']) && $_SERVER['REQUEST_METHOD'] == "POST") {
    try {

        $stateCode = $_POST['stateCode'];

        if (empty($stateCode)) {
            echo json_encode([
                "status" => 400,
                "error" => "Invalid state",
            ]);
            exit;
        }

        $db->begin_transaction();

        // Fetch unique, non-empty cities only
        $stmtFetchCities = $db->prepare("SELECT * FROM cities WHERE state_code = ? AND is_active = 1");
        $stmtFetchCities->bind_param("s", $stateCode);
        $stmtFetchCities->execute();
        $cities = $stmtFetchCities->get_result()->fetch_all(MYSQLI_ASSOC);


        echo json_encode([
            "status" => 200,
            "data" => $cities,
        ]);
        exit;

    } catch (\Throwable $th) {
        //throw $th;
        echo json_encode([
            "status" => 500,
            "error" => $th->getMessage(),
        ]);
        exit;
    }
}

$requestQuery = mysqli_query($db, "SELECT ur.tenderID, ur.tender_no, ur.reference_code, ur.name_of_work,
department.department_name, s.section_name, ur.selected_user_id , ur.reminder_days, sm.name, sm.email_id, sm.mobile
FROM user_tender_requests ur 
inner join members sm on ur.selected_user_id= sm.member_id
inner join section s on ur.section_id=s.section_id
inner join department on ur.department_id = department.department_id where ur.id='" . $d . "'");

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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>



    <script>
        $(document).ready(function () {
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
            $dropdown.on('change', function () {
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

    <?php if (isset($_SESSION['success'])) { ?>
        <script>
            const notyf = new Notyf({
                position: {
                    x: 'center',
                    y: 'top'
                },
                types: [
                    {
                        type: 'success',
                        background: '#26c975', // Change background color
                        textColor: '#FFFFFF',  // Change text color
                        dismissible: true,
                        duration: 10000
                    }
                ]
            });
            notyf.success("<?php echo $_SESSION['success']; ?>");
        </script>
        <?php
        unset($_SESSION['success']);
        ?>
    <?php } ?>

    <?php if (isset($_SESSION['error'])) { ?>
        <script>
            const notyf = new Notyf({
                position: {
                    x: 'center',
                    y: 'top'
                },
                types: [
                    {
                        type: 'error',
                        background: '#ff1916',
                        textColor: '#FFFFFF',
                        dismissible: true,
                        duration: 10000
                    }
                ]
            });
            notyf.error("<?php echo $_SESSION['error']; ?>");
        </script>
        <?php
        unset($_SESSION['error']);
        ?>
    <?php } ?>
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

                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="index.php"><i class="feather icon-home"></i> Home</a>
                                </li>
                                <li class="breadcrumb-item active"><a href="alot-tender.php">Alot Tender</a>
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
                                                    value="<?php echo $requestData[8] . "-" . $requestData[9] . "-" . $requestData[10]; ?>"
                                                    readonly="">
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
                                                        $selected = $day == $requestData[7] ? "selected=''" : '';
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

                                                // echo '<option value="other">Other</option>';

                                                echo "</select>";
                                                ?>
                                            </div>
                                        </div>
                                        <!-- Text input-->

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12" id="otherFields"
                                            style="display: none;">
                                            <div class="form-group">Name <span style="color:red;"> *</span>
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="name" name="name" type="text" class="form-control input-md"
                                                    placeholder="Enter Name">
                                            </div>



                                            <div class="form-group">Email<span style="color:red;"> *</span>
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="email" name="email" type="text" class="form-control input-md"
                                                    placeholder="Enter Email Id">
                                            </div>



                                            <div class="form-group">Phone <span style="color:red;"> *</span>
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="phone" name="phone" type="text" class="form-control input-md"
                                                    placeholder="Enter Phone No">
                                            </div>


                                            <div class="form-group">State <span style="color:red;"> *</span>
                                                <select name="state" class="js-example-basic-single select-state">
                                                    <option>State</option>
                                                    <?php foreach ($states as $state) { ?>
                                                        <option value="<?= $state['state_code'] ?>">
                                                            <?= $state['state_name'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group">City <span style="color:red;"> *</span>
                                                <select name="city" class="js-example-basic-single select-city">
                                                    <option>City</option>
                                                </select>
                                            </div>

                                            <div class="form-group">Company Name
                                                <label class="sr-only control-label" for="name">City<span class=" ">
                                                    </span></label>
                                                <input id="company" name="company" type="text"
                                                    class="form-control input-md" placeholder="Enter Company Name">
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

<script>
    $(document).ready(function () {
        $(document).on("change", ".select-state", async function (e) {
            let stateCode = $(this).val();
            await $.ajax({
                url: window.location.href,
                type: 'POST',
                data: { stateCode: stateCode },
                dataType: 'json',
                success: function (response) {
                    if (response.status == 200) {
                        let citySelect = $(".select-city");
                        citySelect.empty(); // clear old options
                        citySelect.append('<option value="">Select City</option>');
                        $.each(response.data, function (index, city) {
                            citySelect.append(
                                `<option value="${city.city_id}">${city.city_name}</option>`
                            );
                        });
                    } else {
                        Swal.fire("No Data", "No cities found.", "warning");
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    console.error("Raw Response:", xhr.responseText);
                    Swal.fire("Error", "An error occurred while processing your request. Please try again.", "error");
                }
            });
        });

        $('.js-example-basic-single').select2({
            width: '100%',
        });

    })
</script>

</html>