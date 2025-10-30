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

try {
    $db->begin_transaction();
    //code...

    $stmtFetchMembers = $db->prepare("SELECT * 
        FROM members m
        LEFT JOIN state s
            ON m.state_code = s.state_code
        LEFT JOIN cities c
            ON m.city_state = c.city_id   
        WHERE m.member_id = ? 
        ");
    $stmtFetchMembers->bind_param("i", $d);
    $stmtFetchMembers->execute();

    $result = $stmtFetchMembers->get_result()->fetch_array(MYSQLI_ASSOC);

    // Fetch unique, non-empty cities only
    $stmtFetchStates = $db->prepare("SELECT * FROM state WHERE is_active = 1 ");
    $stmtFetchStates->execute();
    $states = $stmtFetchStates->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch unique, non-empty cities only
    $stmtFetchCities = $db->prepare("SELECT * FROM cities WHERE is_active = 1");
    $stmtFetchCities->execute();
    $cities = $stmtFetchCities->get_result()->fetch_all(MYSQLI_ASSOC);

    $db->commit();

} catch (\Throwable $th) {
    //throw $th;
}


/* Attempt to connect to MySQL database */
if (isset($_POST['firmName']) && $_SERVER['REQUEST_METHOD'] == "POST") {


    try {
        $name = $_POST['name'];
        $fname = $_POST['firmName'];
        $mobile = $_POST['mobile'];
        $email = $_POST['email'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $status = $_POST['status'];
        $tender = $_POST['tender'];
        $oldRequests = empty($result['max_request']) ? 0 : (int) $result['max_request'];
        $pending_request = ($tender - $oldRequests) + (int) $result['pending_request'];

        // Convert numeric back to string because columns are VARCHAR
        $tenderStr = (string) $tender;
        $pendingStr = (string) $pending_request;
        $memberId = (int) $d;



        if (!empty($_POST['password'])) {

            $password = md5($_POST['password']);
            $stmtUpdate = $db->prepare("UPDATE members SET 
                name=?, firm_name=?, mobile=?, email_id=?, city_state=?, state_code=?, 
                status=?, max_request=?, pending_request=?, password=? 
                WHERE member_id=?");
            $stmtUpdate->bind_param(
                "ssssssssssi",
                $name,
                $fname,
                $mobile,
                $email,
                $city,
                $state,
                $status,
                $tenderStr,
                $pendingStr,
                $password,
                $memberId
            );
        } else {
            $stmtUpdate = $db->prepare("UPDATE members SET 
                name=?, firm_name=?, mobile=?, email_id=?, city_state=?, state_code=?, 
                status=?, max_request=?, pending_request=? 
                WHERE member_id=?");
            $stmtUpdate->bind_param(
                "sssssssssi",
                $name,
                $fname,
                $mobile,
                $email,
                $city,
                $state,
                $status,
                $tenderStr,
                $pendingStr,
                $memberId
            );
        }

        // Execute update
        if (!$stmtUpdate->execute()) {
            throw new Exception("Update failed: " . $stmtUpdate->error);
        }

        echo json_encode([
            "status" => 200,
            "message" => "Updated User Successfully !Password",
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                            <form class="contact-us">
                                <div class=" ">
                                    <!-- Text input-->
                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Name*
                                                <label class="sr-only control-label" for="name">Name<span class=" ">
                                                    </span></label>
                                                <input id="name" name="name" type="text" placeholder="Username"
                                                    class="form-control input-md" required
                                                    value="<?php echo $result["name"]; ?>">
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Firm Name*
                                                <label class="sr-only control-label" for="name">Firm Name<span
                                                        class=" ">
                                                    </span></label>
                                                <input id="name" name="firmname" type="text" placeholder=" Firm name *"
                                                    class="form-control input-md" required
                                                    value="<?php echo $result["firm_name"]; ?>">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Mobile*
                                                <label class="sr-only control-label" for="name">Mobile<span class=" ">
                                                    </span></label>
                                                <input id="name" name="mobile" type="number" placeholder=" Mobile *"
                                                    class="form-control input-md" required
                                                    value="<?php echo $result["mobile"]; ?>">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Email*
                                                <label class="sr-only control-label" for="name">Email<span class=" ">
                                                    </span></label>
                                                <input id="name" name="email" type="email" class="form-control input-md"
                                                    required placeholder="Enter Email"
                                                    value="<?php echo $result["email_id"]; ?>">
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">State*
                                                <select class="form-control select-state" name="state">
                                                    <option value="">State</option>
                                                    <?php foreach ($states as $state) { ?>
                                                        <option value="<?= $state['state_code'] ?>"
                                                            <?= $result["state_code"] == $state['state_code'] ? "Selected" : ""; ?>>
                                                            <?= $state['state_name'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">City*
                                                <select class="form-control select-city" name="city">
                                                    <option value="">City</option>
                                                    <?php foreach ($cities as $city) { ?>
                                                        <option value="<?= $city['city_id'] ?>"
                                                            <?= $result["city_id"] == $city['city_id'] ? "Selected" : ""; ?>>
                                                            <?= $city['city_name'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
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
                                                    <option value="1" <?= ($result['status'] == 1) ? 'selected' : ''; ?>>
                                                        Enable</option>
                                                    <option value="0" <?= ($result['status'] == 0) ? 'selected' : ''; ?>>
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
                                                    value="<?php echo $result['max_request']; ?>">
                                            </div>
                                        </div>
                                        <!-- Button -->
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                            <button type="submit" class="btn btn-secondary" name="submit" id="submit">
                                                <i class="feather icon-save lg"></i>&nbsp; Update
                                            </button>
                                            <a href="registered-users.php" class="btn btn-danger">
                                                <i class="feather icon-x lg"></i>&nbsp; Cancel
                                            </a>
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

    <!-- Bootstrap / other vendor scripts -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <!-- DataTables -->
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

    <!-- Your custom JS -->
    <script>
        $(document).ready(function () {
            $('.select-state').select2({
                placeholder: "Select State"
            });
            $('.select-city').select2({
                placeholder: "Select City"
            });

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


            $(document).on("submit", ".contact-us", async function (e) {
                e.preventDefault();

                // Get data from input fields
                let name = $('input[name="name"]').val().trim();
                let firmName = $('input[name="firmname"]').val().trim();
                let email = $('input[name="email"]').val().trim();
                let mobile = $('input[name="mobile"]').val().trim();
                let state = $('select[name="state"]').val().trim();
                let city = $('select[name="city"]').val().trim();
                let password = $('input[name="password"]').val().trim();
                let status = $('select[name="status"]').val().trim();
                let tender = $('input[name="tender"]').val().trim();


                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    Swal.fire("Error", "Please enter a valid email address", "error");
                    return;
                }

                // Mobile validation (assuming 10 digits for India)
                const mobileRegex = /^[0-9]{10}$/;
                if (!mobileRegex.test(mobile)) {
                    Swal.fire("Error", "Please enter a valid 10-digit mobile number", "error");
                    return;
                }

                // Store original button text and disable button during processing
                const $submitBtn = $('#submit');
                const originalBtnText = $submitBtn.html();
                $submitBtn.prop('disabled', true).html('<i class="feather icon-loader"></i>&nbsp;Updating...');


                let formData = {
                    name: name,
                    firmName: firmName,
                    email: email,
                    mobile: mobile,
                    state: state,
                    city: city,
                    password: password,
                    status: status,
                    tender: parseInt(tender),
                };

                console.log(formData);


                await $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 200) {
                            // Restore button state
                            $submitBtn.prop('disabled', false).val(originalBtnText);
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reload the current page
                                location.reload();
                            });
                        } else {
                            $submitBtn.prop('disabled', false).html(originalBtnText);
                            Swal.fire("Error", response.error, "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        $('#submit').prop('disabled', false); // Enable button - user needs to complete new reCAPTCHA
                        console.error("AJAX Error:", status, error);
                        console.error("Raw Response:", xhr.responseText);
                        Swal.fire("Error", "An error occurred while processing your request. Please try again.", "error");
                    }
                });
            });



        });
    </script>

</body>

</html>