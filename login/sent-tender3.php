<?php

session_start();

require "db/config.php";
require "./utility/referenceCodeGenerator.php";
if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

$name = $_SESSION['login_user'];
$adminID = $_SESSION['login_user_id'];



$tenderID = base64_decode($_GET['tender_id']);

$query = "SELECT 
 ur.id as t_id, 
    m.name, 
    m.firm_name, 
    m.mobile, 
    ur.tender_no, 
    dept.department_name, 
    ur.name_of_work,
    ur.due_date, 
    ur.created_at, 
    ur.sent_at, 
    ur.file_name, 
    ur.tenderID, 
    ur.id, 
    ur.reference_code, 
    ur.file_name2, 
    ur.tentative_cost, 
    ur.section_id, 
    ur.division_id,
    ur.additional_files,
    MAX(s.section_name) AS section_name,
    MAX(dv.division_name) AS division_name,
    MAX(sd.subdivision) AS subdivision,
    ur.auto_quotation,
    ur.email_sent_date
FROM 
    user_tender_requests ur
INNER JOIN 
    members m ON ur.member_id = m.member_id
LEFT JOIN 
    department dept ON ur.department_id = dept.department_id
LEFT JOIN 
    section s ON ur.section_id = s.section_id
LEFT JOIN 
    division dv ON ur.division_id = dv.division_id
LEFT JOIN
    sub_division sd ON ur.sub_division_id = sd.id
WHERE 
    ur.status = 'Sent' AND ur.delete_tender = '0' ANd ur.tenderID = '$tenderID'
GROUP BY 
    ur.id
ORDER BY 
    NOW() >= CAST(ur.sent_at AS DATE), 
    CAST(ur.sent_at AS DATE) ASC, 
    ABS(DATEDIFF(NOW(), CAST(ur.sent_at AS DATE)));

";

$result2 = mysqli_query($db, $query);

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['refCode'])) {

    // Use a transaction to ensure atomicity
    try {
        $prefix = "REF";
        $response = referenceCode($db, $prefix);
        $refNumber = $response['data'];
        echo json_encode([
            "status" => 201,
            "data" => $refNumber
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode([
            "status" => 500,
            "error" => $e->getMessage()
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['tender_id']) && isset($_POST['reference_code'])) {
    try {
        $tenderId = $_POST['tender_id'];
        $referenceCode = $_POST['reference_code'];

        $db->begin_transaction();

        $stmtExistingTenderId = $db->prepare("SELECT * FROM user_tender_requests WHERE id = ?");
        $stmtExistingTenderId->bind_param("i", $tenderId);
        $stmtExistingTenderId->execute();

        $result = $stmtExistingTenderId->get_result();

        if ($result->num_rows == 0) {  // Fixed: should be == 0, not < 0
            echo json_encode([
                "status" => 400,
                "error" => "Tender id is invalid",
            ]);
            $db->rollback(); // Add rollback
            exit;
        }

        // Fixed: bind parameters and execute the update statement
        $stmtUpdateReference = $db->prepare("UPDATE user_tender_requests SET reference_code = ? WHERE id = ?");
        $stmtUpdateReference->bind_param("si", $referenceCode, $tenderId); // Fixed: added bind_param
        $stmtUpdateReference->execute(); // Fixed: added execute

        $db->commit(); // Commit the transaction

        echo json_encode([
            "status" => 200,
            "message" => "Reference code updated successfully",
        ]);
        exit;

    } catch (\Throwable $th) {
        $db->rollback(); // Rollback on error
        echo json_encode([
            "status" => 500,
            "error" => "Database error: " . $th->getMessage(),
        ]);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title><?php echo base64_decode($_GET['tender_id']); ?></title>
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
        /* Change the background color of the table header */
        #basic-btn thead th {
            background-color: #33cc33;
            /* Change this color as per your preference */
            color: white;
            /* This will make the text color white */
        }

        /* Optional: Add border or other styles */
        #basic-btn thead th {
            border: 1px solid #ddd;
            /* Adds a border to the table header */
            padding: 10px;
            /* Adds some padding */
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
            /* Initially hidden */
        }

        .loading-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            z-index: 1000;
        }

        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }


        .dt-buttons {
            margin-top: 5px !important;
        }

        .btn-group {
            display: inline-block;
            /* margin: 0 5px; */
            padding: 8px 16px;
            border-radius: 10px;
            color: white;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;

        }

        .dt-buttons .dt-button:hover {
            background-color: #0056b3;
            /* Darker blue on hover */
            transform: scale(1.05);
            /* Slight zoom effect */
        }

        .dt-buttons .buttons-copy {
            background-color: #ff9f43;
            /* Grey for Copy */
        }

        .dt-buttons .buttons-copy:hover {
            background-color: #ff9f43;
        }

        .dt-buttons .buttons-excel {
            background-color: #28c76f;
            /* Green for Excel */
        }

        .dt-buttons .buttons-excel:hover {
            background-color: #218838;
        }

        .dt-buttons .buttons-csv {
            background-color: #00cfe8;
            /* Teal for CSV */
        }

        .dt-buttons .buttons-csv:hover {
            background-color: #138496;
        }

        .dt-buttons .buttons-print {
            background-color: #ff4560;
        }

        .dt-buttons .buttons-print:hover {
            background-color: #c82333;
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
            <a href="#!d" class="b-brand" style="font-size:24px;">
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
                                <h5 class="m-b-10"><?php echo base64_decode($_GET['tender_id']); ?>
                                </h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a id="link"
                                        style="color:#ff5370; font-size:15px;font-weight:bold"
                                        href="sent-tender2.php">Back To Sent Tender</a></li>
                                <li class="breadcrumb-item"><a style="color:#33cc33"
                                        href=""><?php echo base64_decode($_GET['tender_id']); ?></a></li>

                            </ul>

                        </div>
                    </div>
                </div>
            </div>

            <?php
            if (isset($_GET['status'])) {
                $st = $_GET['status'];
                $st1 = base64_decode($st);

                if ($st1 > 0) {
                    echo " <div class='alert alert-success alert-dismissible fade show' role='alert' style='font-size:16px;' id='updateuser'>
                        <strong><i class='feather icon-check'></i>Thanks!</strong> Tender has been Updated Successfully.
                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                        </div> ";
                } else {
                    echo " <div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='updateuser'>
                    <strong>Error!</strong> Tender has been not Updated
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                    </div> ";
                }
            }
            // if($allowedAction=='all'){
            
            echo '<div class="row">';
            // echo '
            // <div class="col-md-6 col-xl-3">
            //         <div class="card bg-c-green order-card">
            //             <div class="card-body">
            //                 <h6 class="text-white">Sent Tenders</h6>
            //                 <h2 class="text-right text-white"><i
            //                         class="feather icon-message-square float-left"></i><span id="total"></span></h2>
            
            //             </div>
            //         </div>
            //     </div>
            // ';
            echo '<div class="col-sm-12">';
            echo '<div class="card">';
            echo '<div class="card-body">';

            echo "<div class=''>";
            if ($isAdmin || hasPermission('Dashboard', $privileges, $roleData['role_name'])) {
                echo "<a href='#' id='recycle_records' class='btn btn-danger btn-md rounded-sm'> <i class='feather icon-trash'></i>  &nbsp;
            Move to Bin Selected Items</a>";
            }
            echo "&nbsp; &nbsp; &nbsp";
            echo "<a href='#' id='email_records' class='btn btn-primary rounded-sm'> <i class='feather icon-mail'></i>   
                                    Send Email To Selected Items</a>";

            // Search Bar Section with Dynamic Filter Functionality
            // echo "<div class='col-md-4 ms-auto'> <!-- Add offset for alignment -->
            //    <div class='input-group'>
            //        <input type='text' class='form-control' id='searchInput' placeholder='Search on this page' aria-label='Search'>
            //        <button class='btn btn-success rounded-sm' type='button' onclick='clearSearch()'>
            //            <i class='feather icon-search'></i> &nbsp; Search
            //        </button>
            //    </div>
            // </div>";
            
            echo "</div> <br />";
            // echo '<div id="contentArea">';
            // // Example Content to Search
            // // echo '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>';
            // // echo '<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>';
            // // echo '<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia.</p>';
            // echo '</div>';
            
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            ?>

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
                                            <th>
                                                <label class="checkboxs">
                                                    <input type="checkbox" id="select-all">
                                                    <span class="checkmarks"></span>
                                                </label>
                                                SNO
                                            </th>
                                            <th>User</th>
                                            <th>Firm Name</th>
                                            <th>Mobile</th>
                                            <th>Ref. Code</th>
                                            <th>Tender No</th>
                                            <th>Department</th>
                                            <th>Section</th>
                                            <th>Division</th>
                                            <th>Sub-division</th>
                                            <th>Work Name</th>
                                            <th>Tentative Cost</th>
                                            <th>Due Date</th>
                                            <th>Date Added</th>
                                            <th>Time Added</th>
                                            <th>Sent Date</th>

                                            <?php if ($isAdmin || hasPermission('Dashboard', $privileges, $roleData['role_name'])): ?>
                                                <th>Email Status</th>
                                                <th>Action</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php $count = 1; ?>
                                        <?php while ($row = mysqli_fetch_assoc($result2)): ?>
                                            <tr>
                                                <td>
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input request_checkbox"
                                                            id="row<?= $row['id'] ?>" data-request-id="<?= $row['id'] ?>">
                                                        <label class="custom-control-label" for="row<?= $row['id'] ?>">
                                                            <?= $count ?>
                                                        </label>
                                                    </div>
                                                </td>

                                                <td class="text-danger"><?= $row['name'] ?></td>
                                                <td class="text-success"><?= $row['firm_name'] ?></td>
                                                <td><?= $row['mobile'] ?></td>
                                                <td><?= $row['reference_code'] ?></td>
                                                <td><?= $row['tender_no'] ?></td>
                                                <td><?= $row['department_name'] ?></td>
                                                <td><?= $row['section_name'] ?></td>
                                                <td><?= $row['division_name'] ?></td>
                                                <td><?= $row['subdivision'] ?></td>

                                                <td style="white-space:pre-wrap; max-width:100rem;">
                                                    <?= $row['name_of_work'] ?>
                                                </td>

                                                <td><?= $row['tentative_cost'] ?: "-" ?></td>

                                                <td><?= date("d-m-Y", strtotime($row['due_date'])) ?></td>

                                                <?php
                                                $created = strtotime($row['created_at']) + 5.5 * 3600;
                                                ?>
                                                <td><?= date("d-m-Y", $created) ?></td>
                                                <td><?= date("h:i A", $created) ?></td>

                                                <td>
                                                    <?= date("d-m-Y", strtotime($row['sent_at'])) ?>
                                                    <br>

                                                    <?php if (!isset($row['file_name'])): ?>
                                                        <a href="../login/tender/<?= $row['file_name'] ?>" target="_blank">View
                                                            File 1</a><br>
                                                    <?php endif; ?>

                                                    <?php if (!isset($row['file_name2'])): ?>
                                                        <a href="../login/tender/<?= $row['file_name2'] ?>" target="_blank">View
                                                            File 2</a><br>
                                                    <?php endif; ?>

                                                    <?php if (!empty($row['additional_files'])):
                                                        $extraFiles = json_decode($row['additional_files'], true);
                                                        if (is_array($extraFiles)):
                                                            $i = 1;
                                                            foreach ($extraFiles as $file): ?>
                                                                <a href="../login/<?= $file ?>" target="_blank">View File
                                                                    <?= $i ?></a><br>
                                                                <?php $i++; endforeach;
                                                        endif;
                                                    endif; ?>
                                                </td>



                                                <td>
                                                    <?php if (!empty($row['email_sent_date'])): ?>
                                                        <?= date("d-m-Y h:i A", strtotime($row['email_sent_date'])) ?>
                                                    <?php else: ?>
                                                        <span class="text-warning">*Please Send Email</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary" type="button"
                                                            id="actionMenu<?php echo $row['id']; ?>"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="feather icon-more-vertical"></i>
                                                        </button>

                                                        <ul class="dropdown-menu"
                                                            aria-labelledby="actionMenu<?php echo $row['id']; ?>">

                                                            <?php if ($isAdmin || hasPermission('Dashboard', $privileges, $roleData['role_name'])) { ?>
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="sent-edit.php?id=<?= base64_encode($row['id']) ?>">
                                                                        <i class="feather icon-edit me-2"></i>Alot
                                                                    </a>
                                                                </li>
                                                            <?php } ?>

                                                            <?php if ($isAdmin || hasPermission('Dashboard', $privileges, $roleData['role_name'])) { ?>
                                                                <li>
                                                                    <a class="dropdown-item recyclebutton" href="#"
                                                                        data-id="<?php echo $row['id']; ?>" title="Move to Bin">
                                                                        <i class="feather icon-trash me-2"></i>Move to Bin
                                                                    </a>
                                                                </li>
                                                            <?php } ?>

                                                            <?php if ($isAdmin || hasPermission('Dashboard', $privileges, $roleData['role_name'])) { ?>
                                                                <li>
                                                                    <a class="dropdown-item update-Reference"
                                                                        href="javascript:void(0);"
                                                                        data-tender-id="<?php echo $row['t_id']; ?>"
                                                                        data-reference-code="<?php echo $row['reference_code']; ?>"
                                                                        data-bs-toggle="modal" data-bs-target="#reference-code"
                                                                        title="Change Reference Number">
                                                                        <i class="feather icon-book me-2"></i>Reference No
                                                                    </a>
                                                                </li>
                                                            <?php } ?>

                                                            <li>
                                                                <a class="dropdown-item mail" href="javascript:void(0);"
                                                                    id="<?php echo $row['id']; ?>">
                                                                    <i class="feather icon-mail me-2"></i>Send Mail
                                                                </a>
                                                            </li>

                                                        </ul>
                                                    </div>
                                                </td>

                                            </tr>

                                            <?php $count++; ?>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>



                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <div class="loading-overlay">

        <div class="loading-message">
            <div class="spinner"></div>
            Sending email, please wait...
        </div>
    </div>


    <div class="modal fade" id="reference-code" tabindex="-1" aria-labelledby="editUnitsLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUnitsLabel">Update Reference Number</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="update-reference-code">
                    <div class="modal-body">
                        <input type="hidden" class="form-control" name="editTenderId" id="editTenderId">
                        <div class="row">
                            <div class="col-12 col-md-12 mb-3">
                                <label for="editReferenceCode" class="form-label">Reference Number</label>
                                <div class="input-group">

                                    <input type="text" class="form-control" id="editReferenceCode"
                                        name="editReferenceCode">
                                    <button type="button" name="updateReferenceCode"
                                        class="btn btn-primary refNumber">Generate</button>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <!--<script src="assets/js/menu-setting.min.js"></script>-->

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



    <script>
        $(document).ready(function () {
            // Optional: Hide update message after 5 seconds
            $("#updateuser").delay(5000).slideUp(300);

        });
    </script>

    <script type="text/javascript">
        $(function () {

            $(".recyclebutton").click(function () {

                let element = $(this);

                let del_id = element.attr("id");
                let info = 'id=' + del_id;

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
                            url: "recycleuser.php",
                            data: info,
                            success: function () {
                                // Show success message
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'The record has been moved to recycle bin.',
                                    icon: 'success',
                                    confirmButtonColor: "#33cc33",
                                    timer: 1500,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                });
                            },
                            error: function (error) {
                                console.log(error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Something went wrong while moving the record to recycle bin.',
                                    icon: 'error',
                                    confirmButtonColor: "#33cc33"
                                });
                            }
                        });

                        // Animate and remove the record
                        $(this).parents(".record").animate({
                            backgroundColor: "#FF3"
                        }, "fast")
                            .animate({
                                opacity: "hide"
                            }, "slow");

                        // Reload page after animation
                        setTimeout(function () {
                            window.location.reload();
                        }, 2000);
                    }
                });



                return false;
            });

            function showLoadingMessage() {
                $(".loading-overlay").show();
                $(".loading-message").show();
            }

            function hideLoadingMessage() {
                $(".loading-overlay").hide();
                $(".loading-message").hide();
            }

            $(".mail").on("click", function (e) {
                e.preventDefault();

                let element = $(this);
                let email_id = element.attr("id");
                let info = 'id=' + email_id;

                Swal.fire({
                    title: "Are you sure?",
                    text: "You want to send an email to this record?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#33cc33",
                    cancelButtonColor: "#ff5471",
                    confirmButtonText: "Yes, send it!",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {

                        // Optional: show a loading message while sending
                        Swal.fire({
                            title: "Sending...",
                            text: "Please wait while we send the email.",
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            type: "GET",
                            url: "mail.php",
                            data: info,
                            success: function () {
                                setTimeout(function () {
                                    Swal.fire({
                                        icon: "success",
                                        title: "Email Sent!",
                                        text: "The email has been sent successfully.",
                                        confirmButtonColor: "#33cc33",
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    setTimeout(() => {
                                        location.reload();
                                    }, 2000);
                                }, 1000);
                            },
                            error: function () {
                                Swal.fire({
                                    icon: "error",
                                    title: "Failed!",
                                    text: "Something went wrong while sending the email.",
                                    confirmButtonColor: "#ff5471"
                                });
                            }
                        });
                    }
                });
            });


            $('#recycle_records').on('click', function (e) {
                e.preventDefault();
                let requestIDs = [];
                $(".request_checkbox:checked").each(function () {
                    requestIDs.push($(this).data('request-id'));
                });
                if (requestIDs.length <= 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Please select records!",
                        confirmButtonColor: "#33cc33"
                    });
                    return;
                } else {

                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert " + (requestIDs.length > 1 ? "these" : "this") + " Record" + (requestIDs.length > 1 ? "s" : "") + "!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#33cc33",
                        cancelButtonColor: "#ff5471",
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "Cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let selected_values = requestIDs.join(",");
                            $.ajax({
                                type: "POST",
                                url: "recycleuser.php",
                                cache: false,
                                data: 'tender_sent_ids=' + selected_values,
                                success: function () {

                                    $(".request_checkbox:checked").each(function () {
                                        $(this).closest(".record").animate({
                                            backgroundColor: "#FF3"
                                        }, "fast").animate({
                                            opacity: "hide"
                                        }, "slow", function () {
                                            $(this).remove();
                                        });
                                    });
                                    setTimeout(function () {
                                        window.location.reload();
                                    },
                                        2000);

                                }
                            });
                        }
                    });
                }
            });

            $('#email_records').on('click', function (e) {
                let requestIDs = [];

                $(".request_checkbox:checked").each(function () {
                    requestIDs.push($(this).data('request-id'));
                });

                if (requestIDs.length <= 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Please select records!",
                        confirmButtonColor: "#33cc33"
                    });
                    return;
                } else {
                    WRN_MAIL_SEND = "Are you sure you want to send email to " + (requestIDs.length > 1 ? "these" : "this") + " Record?";
                    let checked = confirm(WRN_MAIL_SEND);
                    if (checked == true) {
                        showLoadingMessage();//show loading message
                        let selected_values = requestIDs.join(",");
                        $.ajax({
                            type: "POST",
                            url: "mail.php",
                            cache: false,
                            data: 'tender_sent_ids=' + selected_values,
                            success: function () {
                                setTimeout(function () {
                                    hideLoadingMessage();
                                    location.reload();
                                }, 2000);
                            }
                        })
                    }
                }
            })
        });
    </script>

    <script>
        // jQuery for searching and auto-focusing on <th> tags
        $(document).ready(function () {
            $('#searchInput').on('input', function () {
                const query = $(this).val().toLowerCase();
                let found = false;

                $('th h4').each(function () {
                    const th = $(this);
                    if (th.text().toLowerCase().includes(query)) {
                        th.css('color', '#F1F5F8'); // Highlight with the desired color
                        if (!found) {
                            th[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); // Scroll to the first match
                            found = true;
                        }
                    } else {
                        th.css('background-color', ''); // Reset non-matching <th>
                    }
                });
            });

            // Function to clear the search
            $('#clearSearch').on('click', function () {
                $('#searchInput').val('');
                $('th').css('background-color', ''); // Reset all highlights
            });
        });
    </script>


    <script type="text/javascript">
        $(document).ready(function () {
            // Initialize the DataTable with buttons
            var table = $('#basic-btn2').DataTable({
                dom: 'Bfrtip', // Enable the buttons layout
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-primary rounded-sm',
                        titleAttr: 'Export to Excel'
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-primary rounded-sm',
                        titleAttr: 'Export to CSV'
                    },
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> Copy',
                        className: 'btn btn-primary rounded-sm',
                        titleAttr: 'Copy to clipboard'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-primary rounded-sm',
                        titleAttr: 'Print'
                    }
                ]


            }); s
        });
    </script>

    <script>
        $(document).ready(function () {


            function generateReferenceNumber() {
                return $.ajax({
                    url: window.location.href,
                    method: "POST",
                    data: { refCode: true },
                    dataType: "json"
                }).then(function (data) {
                    return data.data; // This matches your API response structure
                });
            }

            $(document).on('click', ".update-Reference", function (event) {
                let tenderId = $(this).data('tender-id');
                let referenceCode = $(this).data('reference-code');

                // Set values in modal form
                $('#editTenderId').val(tenderId);
                $('#editReferenceCode').val(referenceCode);
            });


            $('.refNumber').on('click', async function (e) {
                e.preventDefault();

                const $codeInput = $("#editReferenceCode");
                if ($codeInput.length) {
                    try {
                        // Clear the existing value first
                        $codeInput.val('');



                        // Generate and set the new reference number
                        const refNumber = await generateReferenceNumber();
                        $codeInput.val(refNumber);

                    } catch (error) {
                        console.error('Error generating reference number:', error);
                    }
                }
            });

            $(document).on("submit", ".update-reference-code", function (e) {
                e.preventDefault();

                // Get values correctly using the name attributes
                let tenderId = $("input[name='editTenderId']").val();
                let referenceCode = $("input[name='editReferenceCode']").val();


                // Your AJAX submission logic here
                $.ajax({
                    url: window.location.href, // Change to your actual endpoint
                    method: 'POST',
                    data: {
                        tender_id: tenderId,
                        reference_code: referenceCode
                    },

                    success: function (response) {
                        $('#reference-code').modal('hide');

                        let result = JSON.parse(response);
                        if (result.status == 200) {

                            // Show success message
                            Swal.fire({
                                title: 'Updated!',
                                text: result.message,
                                icon: 'success',
                                confirmButtonColor: "#33cc33",
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                // Reload page after animation
                                setTimeout(function () {
                                    window.location.reload();
                                }, 2000);
                            });

                        } else {
                            // Show error message
                            Swal.fire({
                                title: 'Error!',
                                text: result.error || 'Something went wrong',
                                icon: 'error',
                                confirmButtonColor: "#dc3545",
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                        }

                        console.log(response);

                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                        // Show error message
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to update reference code',
                            icon: 'error',
                            confirmButtonColor: "#dc3545",
                            timer: 1500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    }
                });
            });



            $(document).on('change', '#select-all', function (e) {
                var isChecked = $(this).prop('checked');

                // Select/Deselect all checkboxes with class 'member_checkbox'
                $('.request_checkbox').prop('checked', isChecked);

                // Stop propagation
                e.stopPropagation();
            });

            // Prevent sorting when clicking on checkbox area in header
            $('.checkboxs').on('click', function (e) {
                e.stopPropagation();
            });

            // Handle individual checkbox clicks to update select-all state
            $(document).on('click', '.request_checkbox', function () {
                updateSelectAllState();
            });

            // Function to update select-all checkbox state
            function updateSelectAllState() {
                var totalCheckboxes = $('.request_checkbox').length;
                var checkedCheckboxes = $('.request_checkbox:checked').length;

                // Update select all checkbox state
                $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
            }
        });
    </script>


</body>

</html>