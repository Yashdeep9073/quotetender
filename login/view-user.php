<?php

session_start();


if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}
$name = $_SESSION['login_user'];

include("db/config.php");

$query = "SELECT * FROM admin";
$result = mysqli_query($db, $query);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['memberIds'])) {


    $memberIds = $_POST['memberIds'];

    // Validate: Must be an array of integers
    if (!is_array($memberIds)) {
        echo json_encode([
            'status' => 400,
            'message' => 'Invalid data format.'
        ]);
        exit;
    }

    try {
        // Prepare the SQL dynamically
        $placeholders = implode(',', array_fill(0, count($memberIds), '?'));
        $types = str_repeat('i', count($memberIds)); // All integers

        $stmt = $db->prepare("DELETE FROM admin WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$memberIds);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 200,
                'message' => 'Selected records deleted successfully.',
                'deleted_ids' => $memberIds
            ]);
        } else {
            echo json_encode([
                'status' => 400,
                'message' => $stmt->error
            ]);
        }

        exit;
    } catch (Exception $e) {
        echo json_encode([
            'status' => 500,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>View User</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="Codedthemes" />
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
                                <h5 class="m-b-10">View/Edit User
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



                        </div>
                        <div class="card-body">
                            <div class="dt-responsive table-responsive">

                                <?php

                                if (isset($_GET['status'])) {
                                    $st = $_GET['status'];
                                    $st1 = base64_decode($st);

                                    if ($st1 > 0) {
                                        echo " <div class='alert alert-success alert-dismissible fade show' role='alert' style='font-size:16px;' id='updateuser'>
  <strong><i class='feather icon-check'></i>Thanks!</strong> User has been Updated Successfully.
  <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
    <span aria-hidden='true'>&times;</span>
  </button>
</div> ";
                                    } else {

                                        echo " <div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='updateuser'>
  <strong>Error!</strong> User has been not Updated
  <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
    <span aria-hidden='true'>&times;</span>
  </button>
</div> ";
                                    }
                                }

                                ?>
                                <br />


                                <?php

                                echo "<div class='col-md row'>
                                <a href='#' id='delete_records' class='btn btn-danger'> <i class='feather icon-trash'></i>  &nbsp;
                                Delete Selected User</a>
                                </div> <br />";

                                echo '<table id="basic-btn2" class="table table-striped table-bordered nowrap">';
                                echo "<thead>";
                                echo "<tr>";
                                echo '<th> <label class="checkboxs">
                                        <input type="checkbox" id="select-all">
                                        <span class="checkmarks"></span>
                                    </label>&nbsp SNO</th>';
                                echo "<th>Username</th>";
                                echo "<th>Email</th>";
                                echo "<th>Mobile No</th>";
                                echo "<th>Status</th>";

                                echo "<th>Edit</th>";


                                echo "</tr>";
                                echo "</thead>";


                                ?>
                                <?php



                                $count = 1;

                                echo "<tbody>";
                                while ($row = mysqli_fetch_row($result)) {

                                    echo "<tr class='record'>";
                                    echo "<td><div class='custom-control custom-checkbox'>
                                    <input type='checkbox' class='custom-control-input member_checkbox' id='customCheck" . $count . "' data-member-id='" . $row['9'] . "'>
                                    <label class='custom-control-label' for='customCheck" . $count . "'>" . $count . "</label>
                                </div></td>";

                                    echo "<td>" . $row['0'] . "</td>";
                                    echo "<td>" . $row['2'] . "</td>";
                                    echo "<td>" . $row['6'] . "</td>";

                                    if ($row['3'] == 1) {
                                        echo "<td> Enable</td>";


                                    } else {
                                        echo "<td> Disable</td>";
                                    }

                                    $res = $row[2];
                                    $res = base64_encode($res);
                                    echo "<td>  <a href='user-edit.php?id=$res'><button type='button' class='btn btn-warning'><i class='feather icon-edit'></i> &nbsp;Edit</button></a>  &nbsp;   <a href='#' id='" . $row['0'] . "'class='delbutton btn btn-danger' title='Click To Delete'> <i class='feather icon-trash'></i>  &nbsp; delete</a></td>";



                                    echo "</tr>";
                                    $count++;
                                }


                                echo "</tfoot>";
                                echo "</table>";
                                ?>

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
        $(document).ready(function () {
            $("#updateuser").delay(5000).slideUp(300);
        });
    </script>

    <script type="text/javascript">
        $(function () {
            $(".delbutton").click(function () {

                var element = $(this);

                var del_id = element.attr("id");

                var info = 'id=' + del_id;

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
                            url: "deleteuser.php",
                            data: info,
                            success: function () {
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


            $(document).on('change', '#select-all', function (e) {
                var isChecked = $(this).prop('checked');

                // Select/Deselect all checkboxes with class 'member_checkbox'
                $('.member_checkbox').prop('checked', isChecked);

                // Stop propagation
                e.stopPropagation();
            });

            // Prevent sorting when clicking on checkbox area in header
            $('.checkboxs').on('click', function (e) {
                e.stopPropagation();
            });

            // Handle individual checkbox clicks to update select-all state
            $(document).on('click', '.member_checkbox', function () {
                updateSelectAllState();
            });

            // Function to update select-all checkbox state
            function updateSelectAllState() {
                var totalCheckboxes = $('.member_checkbox').length;
                var checkedCheckboxes = $('.member_checkbox:checked').length;

                // Update select all checkbox state
                $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
            }

            $(document).on('click', '#delete_records', function (e) {
                e.preventDefault();

                let members = [];
                $(".member_checkbox:checked").each(function () {
                    members.push($(this).data('member-id'));
                });

                if (members.length == 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Please select record!",
                        confirmButtonColor: "#33cc33"
                    });
                    return;
                }


                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    showCancelButton: true,
                    confirmButtonColor: "#33cc33",
                    cancelButtonColor: "#ff5471",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: window.location.href,
                            type: "post",
                            data: { memberIds: members },
                            success: function (response) {
                                let result = JSON.parse(response);

                                if (result.status == 200) {
                                    Swal.fire(
                                        'Deleted!',
                                        result.message,
                                        'success'
                                    ).then(() => {
                                        // Reload the page
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Deleted!',
                                        result.message,
                                        'error'
                                    ).then(() => {
                                        // Reload the page
                                        location.reload();
                                    });
                                }


                            },
                            error: function (error) {
                                console.log(error);
                            },
                        });

                    }
                })
            });
        });
    </script>



    <script type="text/javascript">
        $(document).ready(function () {
            // Initialize the DataTable with buttons
            var table = $('#basic-btn2').DataTable({
                pageLength: 100,
                lengthMenu: [25, 50, 100, 200, 500, 1000], // Custom dropdown options
                responsive: true,
                ordering: true,
                searching: true
            });

            // Fetch the number of entries
            var info = table.page.info();
            var totalEntries = info.recordsTotal;

            $('#new').text(totalEntries);
        });
    </script>

</body>

</html>