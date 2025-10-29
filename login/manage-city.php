<?php

session_start();
include("db/config.php");

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}
try {
    // Fetch unique, non-empty cities only
    $stmtFetchCities = $db->prepare("SELECT * FROM cities ");
    $stmtFetchCities->execute();
    $cities = $stmtFetchCities->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (\Throwable $th) {
    $_SESSION['error'] = $th->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['action'] === "update-city") {

    try {
        // Validate required fields
        $required_fields = ['editCityId', 'editCityName', 'editCityStatus'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || $_POST[$field] === '') {
                echo json_encode([
                    "status" => 400,
                    "error" => "Missing required field: " . $field
                ]);
                exit;
            }
        }

        // Sanitize input data
        $editCityId = (int) $_POST['editCityId'];
        $editCityName = trim($_POST['editCityName']);
        $editCityStatus = trim($_POST['editCityStatus']);

        // Check if state record exists
        $checkQuery = "SELECT * FROM cities WHERE city_id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bind_param("i", $editCityId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows == 0) {
            echo json_encode([
                "status" => 400,
                "error" => "Invalid city id "
            ]);
            exit;
        } else {
            // Update existing record
            $updateQuery = "UPDATE cities SET 
                city_name = ?, 
                is_active = ?
            WHERE city_id = ?";

            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bind_param(
                "sii",
                $editCityName,
                $editCityStatus,
                $editCityId
            );

            if (!$updateStmt->execute()) {
                throw new Exception("Update failed: " . $db->error);
            }
        }


        // Close statements
        if (isset($checkStmt))
            $checkStmt->close();
        if (isset($insertStmt))
            $insertStmt->close();
        if (isset($updateStmt))
            $updateStmt->close();
        $db->close();

        echo json_encode([
            "status" => 201,
            "message" => "City Updated successfully"
        ]);
        exit;

    } catch (Exception $th) {
        // Log the error for debugging (optional)
        error_log("Email settings update error: " . $th->getMessage());

        echo json_encode([
            "status" => 500,
            "error" => $th->getMessage()
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Manage Cities</title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .swal2-container {
            z-index: 20000 !important;
        }
    </style>

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
                        background: '#4dc76f', // Change background color
                        textColor: '#FFFFFF',  // Change text color
                        dismissible: false
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
                        dismissible: false
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
            <a class="mobile-menu" id="mobile-collapse" href="javascript:void(0);"><span></span></a>
            <a href="javascript:void(0);" class="b-brand" style="font-size:24px;">
                ADMIN PANEL

            </a>
            <a href="javascript:void(0);" class="mob-toggler">
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
                    <a href="javascript:void(0);" class="full-screen" onClick="javascript:toggleFullScreen()"><i
                            class="feather icon-maximize"></i></a>
                </li>
            </ul>


        </div>
        </div>
        </li>

        <div class="dropdown drp-user">
            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
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
                                <h5 class="m-b-10">Manage Department
                                </h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Mange Cities</a></li>
                            </ul>

                        </div>
                    </div>
                </div>
            </div>


            <div class="row">

                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header table-card-header">
                        </div>
                        <div class="card-body">
                            <div class="dt-responsive table-responsive">

                                <table id="basic-btn2" class="table table-striped table-bordered nowrap">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>City Name</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 1;
                                        foreach ($cities as $city) {
                                            ?>
                                            <tr>
                                                <td><?= $count ?></td>
                                                <td><?= $city['city_name'] ?></td>
                                                <td><?= $city['is_active'] == 1 ? "Active" : "Inactive" ?></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary " type="button"
                                                            id="actionMenu<?php echo $city['city_id']; ?>"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="feather icon-more-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu"
                                                            aria-labelledby="actionMenu<?= $city['city_id']; ?>">
                                                            <?php if ($isAdmin || hasPermission('Edit City', $privileges, $roleData['role_name'])) { ?>
                                                                <li>
                                                                    <a class="dropdown-item update-city-button"
                                                                        href="javascript:void(0);"
                                                                        data-city-id="<?php echo $city['city_id']; ?>"
                                                                        data-city-name="<?php echo $city['city_name']; ?>"
                                                                        data-city-status="<?php echo $city['is_active']; ?>"
                                                                        data-bs-toggle="modal" data-bs-target="#updateCityModel"
                                                                        title="Edit City">
                                                                        <i class="feather icon-edit me-2"></i>Update
                                                                    </a>
                                                                </li>
                                                            <?php } ?>
                                                        </ul>
                                                    </div>

                                                </td>
                                            </tr>

                                            <?php
                                            $count++;
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot></tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <div class="modal fade" id="updateCityModel" tabindex="-1" aria-labelledby="updateCityLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateCityLabel">Update City</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="update-city-form">
                    <div class="modal-body">
                        <input type="hidden" class="form-control" name="editCityId" id="editCityId">
                        <div class="row">
                            <div class="col-6 col-md-6 mb-3">
                                <label for="editReferenceCode" class="form-label">City Name</label>
                                <div class="input-group">

                                    <input type="text" class="form-control" id="editCityName" name="editCityName">

                                </div>
                            </div>
                            <div class="col-6 col-md-6 mb-3">
                                <label for="editCityStatus" class="form-label">Status</label>
                                <div class="input-group">

                                    <select name="editCityStatus" id="editCityStatus" class="form-control">
                                        <option>Select</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>

                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary editCityButton">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>




    <script src=" assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <script src="assets/js/plugins/jquery.dataTables.min.js"></script>
    <script src="assets/js/plugins/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/plugins/dataTables.buttons.min.js"></script>
    <script src="assets/js/plugins/buttons.colVis.min.js"></script>
    <script src="assets/js/plugins/buttons.print.min.js"></script>
    <script src="assets/js/plugins/pdfmake.min.js"></script>
    <script src="assets/js/plugins/jszip.min.js"></script>
    <script src="assets/js/plugins/buttons.html5.min.js"></script>
    <script src="assets/js/plugins/buttons.bootstrap4.min.js"></script>
    <!-- <script src="assets/js/pages/data-export-custom.js"></script> -->

    <!-- Excel Generate  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


    <script>
        $(document).ready(function () {
            $("#gold").delay(5000).slideUp(300);
        });
    </script>


    <script type="text/javascript">
        $(function () {
            $(".delbutton").click(function () {

                var element = $(this);

                var del_id = element.attr("id");

                var info = 'id=' + del_id;
                // if (confirm("Are you sure you want to delete this Record?")) {
                //     $.ajax({
                //         type: "GET",
                //         url: "deletegold.php",
                //         data: info,
                //         success: function () { }
                //     });
                //     $(this).parents(".record").animate({
                //         backgroundColor: "#FF3"
                //     }, "fast")
                //         .animate({
                //             opacity: "hide"
                //         }, "slow");
                // }



                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this Record!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#33cc33",
                    cancelButtonColor: "#ff5471",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "GET",
                            url: "deletegold.php",
                            data: info,
                            success: function (response) {
                                // Show success message
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'The record has been deleted.',
                                    icon: 'success',
                                    confirmButtonColor: "#33cc33",
                                    timer: 1500,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Animate and remove the record
                                    $(".record").animate({
                                        backgroundColor: "#FF3"
                                    }, "fast")
                                        .animate({
                                            opacity: "hide"
                                        }, "slow");

                                    // Reload page after animation
                                    setTimeout(function () {
                                        window.location.reload();
                                    }, 2000);
                                });
                            },
                            error: function (error) {
                                console.log(error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Something went wrong while deleting the record.',
                                    icon: 'error',
                                    confirmButtonColor: "#33cc33"
                                });
                            }
                        });
                    }
                });

                return false;
            });

            $(document).on("click", ".update-city-button", function (e) {
                e.preventDefault();
                let cityId = $(this).data("city-id");
                let cityName = $(this).data("city-name");
                let cityStatus = $(this).data("city-status");

                $("#editCityId").val(cityId);
                $("#editCityName").val(cityName);
                $("#editCityStatus").val(cityStatus);

            })
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function () {

            $(document).on("submit", ".update-city-form", async function (e) {
                e.preventDefault();

                // Get data from input fields within the form
                let editCityId = $(this).find('input[name="editCityId"]').val().trim();
                let editCityName = $(this).find('input[name="editCityName"]').val().trim();
                let editCityStatus = $(this).find('select[name="editCityStatus"]').val();


                // Basic validation
                if (!editCityId || !editCityName || !editCityStatus) {
                    Swal.fire("Error", "All fields are required. Please fill out the form completely.", "error");
                    return;
                }


                // Store original button text and disable button during processing
                const $submitBtn = $(this).find('#editCityButton');
                const originalBtnText = $submitBtn.html();
                $submitBtn.prop('disabled', true).html('<i class="feather icon-loader"></i>&nbsp;Updating...');

                let formData = {
                    editCityId: editCityId,
                    editCityName: editCityName,
                    editCityStatus: editCityStatus,
                    action: "update-city"
                };


                await $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 201) {
                            // Restore button state
                            $submitBtn.prop('disabled', false).html(originalBtnText);

                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: `${response.message}`,
                                // confirmButtonText: 'OK',
                                confirmButtonColor: "#33cc33",
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                // // ✅ Correct Bootstrap 5 way to hide the modal
                                // const smtpModalEl = document.getElementById('smtpSettingsModal');
                                // const smtpModal = bootstrap.Modal.getInstance(smtpModalEl);
                                // smtpModal.hide();

                                window.location.reload();
                            });
                        }
                        else {
                            $submitBtn.prop('disabled', false).html(originalBtnText);
                            Swal.fire("Error", response.error || "An error occurred", "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        // Restore button state
                        $submitBtn.prop('disabled', false).html(originalBtnText);

                        console.error("AJAX Error:", status, error);
                        console.error("Raw Response:", xhr.responseText);
                        Swal.fire("Error", "An error occurred while processing your request. Please try again.", "error");
                    }
                });
            });

            // Initialize the DataTable with buttons
            var table = $('#basic-btn2').DataTable({
                pageLength: 100,
                lengthMenu: [25, 50, 100, 200, 500, 1000], // Custom dropdown options
                responsive: true,
                ordering: true,
                searching: true
            });

            // Fetch the number of entries

        });
    </script>
</body>

</html>