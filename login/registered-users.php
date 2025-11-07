<?php
session_start();
include("db/config.php");

$name = $_SESSION['login_user'];

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

try {
    $db->begin_transaction();
    //code...

    $stmtFetchMembers = $db->prepare("SELECT * 
        FROM members m
        LEFT JOIN state s
            ON m.state_code = s.state_code
        LEFT JOIN cities c
            ON m.city_state = c.city_id    
        ");
    $stmtFetchMembers->execute();

    $result = $stmtFetchMembers->get_result()->fetch_all();
    $db->commit();

    // echo "<pre>";
    // print_r($result );
    // exit;

} catch (\Throwable $th) {
    //throw $th;
}




// $query = "SELECT * FROM members";
// $result = mysqli_query($db, $query);


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

        $stmt = $db->prepare("DELETE FROM members WHERE member_id IN ($placeholders)");
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
    <title>Registered Member</title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        #basic-btn2_length {
            padding: 10px !important;
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
                                <h5 class="m-b-10">Registered Members
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
  <strong><i class='feather icon-check'></i>Thanks!</strong> Members details has been Updated Successfully.
  <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
    <span aria-hidden='true'>&times;</span>
  </button>
</div> ";
                                    } else {

                                        echo " <div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='updateuser'>
  <strong>Error!</strong> Members details been not Updated
  <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
    <span aria-hidden='true'>&times;</span>
  </button>
</div> ";
                                    }
                                }

                                ?>
                                <br />


                                <div class='col-md row'>
                                    <?php if ($isAdmin || hasPermission('Bulk Delete Registered Users', $privileges, $roleData['role_name'])) { ?>
                                        <a href='#' id='delete_records' class='btn btn-danger'>
                                            <i class='feather icon-trash'></i> &nbsp;
                                            Delete Selected Members
                                        </a>
                                    <?php } ?>
                                </div>
                                <br />

                                <div class="dt-buttons btn-group">
                                    <?php if ($isAdmin || hasPermission('Registered User Excel', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn btn-secondary buttons-excel buttons-html5 btn-primary rounded-sm"
                                            tabindex="0" aria-controls="basic-btn2" type="button"
                                            onclick="exportTableToExcel()" title="Export to Excel">
                                            <span>
                                                <i class="fas fa-file-excel"></i> Excel
                                            </span>
                                        </button>
                                    <?php } ?>
                                    <?php if ($isAdmin || hasPermission('Registered User CSV', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn btn-secondary buttons-csv buttons-html5 btn-primary rounded-sm"
                                            tabindex="0" aria-controls="basic-btn2" type="button"
                                            onclick="exportTableToCSV()" title="Export to CSV">
                                            <span>
                                                <i class="fas fa-file-csv"></i> CSV
                                            </span>
                                        </button>
                                    <?php } ?>

                                    <?php if ($isAdmin || hasPermission('Registered User Print', $privileges, $roleData['role_name'])) { ?>
                                        <button class="btn btn-secondary buttons-print btn-primary rounded-sm" tabindex="0"
                                            onclick="printTable()" aria-controls="basic-btn2" type="button" title="Print">
                                            <span>
                                                <i class="fas fa-print"></i> Print
                                            </span>
                                        </button>
                                    <?php } ?>
                                </div>

                                <table id="basic-btn2" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>
                                                <label class="checkboxs">
                                                    <input type="checkbox" id="select-all">
                                                    <span class="checkmarks"></span>
                                                </label>
                                                SNO
                                            </th>
                                            <th>Name</th>
                                            <th>Firm Name</th>
                                            <th>Mobile</th>
                                            <th>Email</th>
                                            <th>State</th>
                                            <th>City</th>
                                            <th>Status</th>
                                            <th>Tender</th>
                                            <th>Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 1;
                                        foreach ($result as $row) {
                                            ?>
                                            <tr class='record' id='<?php echo $row[0]; ?>'>
                                                <td>
                                                    <div class='custom-control custom-checkbox'>
                                                        <input type='checkbox' class='custom-control-input member_checkbox'
                                                            id='customCheck<?php echo $count; ?>'
                                                            data-member-id='<?php echo $row[0]; ?>'>
                                                        <label class='custom-control-label'
                                                            for='customCheck<?php echo $count; ?>'><?php echo $count; ?></label>
                                                    </div>
                                                </td>

                                                <td><?php echo $row['1']; ?></td>
                                                <td><?php echo $row['2']; ?></td>
                                                <td><?php echo $row['3']; ?></td>
                                                <td><?php echo $row['4']; ?></td>
                                                <td><?php echo $row['15']; ?></td>
                                                <td><?php echo $row['20']; ?></td>
                                                <td><?php echo (($row['9'] == 1) ? "Enable" : "Disabled"); ?></td>

                                                <td>
                                                    Free: <?php echo $row['11']; ?><br />
                                                    <span style='color:green;'>Balance: <?php echo $row['12']; ?></span>
                                                </td>

                                                <td>
                                                    <?php
                                                    if ($isAdmin || hasPermission('Edit Registered Users', $privileges, $roleData['role_name'])) {
                                                        $res = $row[0];
                                                        $ree = base64_encode($res);
                                                        ?>
                                                        <a href='registered-users-edit.php?id=<?php echo $ree; ?>'>
                                                            <button type='button' class='btn btn-warning'>
                                                                <i class='feather icon-edit'></i> &nbsp;Activate
                                                            </button>
                                                        </a> &nbsp;
                                                    <?php } ?>
                                                    <?php if ($isAdmin || hasPermission('Delete Registered Users', $privileges, $roleData['role_name'])) { ?>
                                                        <a href='#' id='<?php echo $row['0']; ?>'
                                                            class='delbutton btn btn-danger' title='Click To Delete'>
                                                            <i class='feather icon-trash'></i> &nbsp; delete
                                                        </a>
                                                    <?php } ?>
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

    <!-- Excel Generate  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        function exportTableToExcel(tableId, filename = 'table.xlsx') {
            const table = document.getElementById("basic-btn2");
            const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
            XLSX.writeFile(wb, filename);
        }
    </script>

    <script>
        function exportTableToCSV(tableId, filename = 'table.csv') {
            const table = document.getElementById("basic-btn2");
            const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
            XLSX.writeFile(wb, filename);
        }
    </script>

    <script>
        function printTable() {
            // Clone the table to avoid altering the original
            const tableClone = document.getElementById("basic-btn2").cloneNode(true);

            // Remove the "Action" column and its corresponding cells
            const thElements = tableClone.querySelectorAll("th");
            const actionColumnIndex = Array.from(thElements).findIndex((th) =>
                th.textContent.trim().toLowerCase() === "edit"
            );

            if (actionColumnIndex !== -1) {
                // Remove the "Action" header
                thElements[actionColumnIndex].remove();

                // Remove cells in the "Action" column
                tableClone.querySelectorAll("tr").forEach((row) => {
                    const cells = row.querySelectorAll("td, th");
                    if (cells[actionColumnIndex]) {
                        cells[actionColumnIndex].remove();
                    }
                });
            }

            const pageTitle = document.title; // Get the current page title
            const printWindow = window.open("", "", "height=800,width=1200");

            printWindow.document.write(`
      <html>
        <head>
          <title>${pageTitle}</title>
          <style>
            body {
              font-family: Arial, sans-serif;
              margin: 20px;
              padding: 0;
              background-color: #f9f9f9;
              color: #333;
            }
            h1 {
              text-align: center;
              color: #007bff;
              margin-bottom: 20px;
              font-size: 24px;
              text-transform: uppercase;
            }
            table {
              width: 100%;
              border-collapse: collapse;
              margin-bottom: 20px;
              background-color: #fff;
              box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
              border-radius: 8px;
              overflow: hidden;
            }
            th {
              background-color: #007bff;
              color: white;
              text-align: left;
              padding: 12px 15px;
              font-size: 14px;
              text-transform: uppercase;
            }
            td {
              padding: 10px 15px;
              border-bottom: 1px solid #ddd;
              font-size: 13px;
            }
            tr:nth-child(even) {
              background-color: #f2f2f2;
            }
            tr:hover {
              background-color: #eaf4ff;
            }
            footer {
              text-align: center;
              margin-top: 20px;
              font-size: 12px;
              color: #555;
            }
          </style>
        </head>
        <body>
          <h1>${pageTitle}</h1>
          ${tableClone.outerHTML}
          <footer>
            Printed on: ${new Date().toLocaleString()}
          </footer>
        </body>
      </html>
    `);

            printWindow.document.close();
            printWindow.print();
        }
    </script>


    <script>
        $(document).ready(function () {
            $("#updateuser").delay(5000).slideUp(300);
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#basic-btn2").on('click', '.delbutton', function () {

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

                // Get the DataTable instance
                var table = $('#basic-btn2').DataTable();

                // Only get checkboxes from currently displayed rows
                table.rows({ page: 'current' }).every(function () {
                    var $row = $(this.node());
                    var $checkbox = $row.find('.member_checkbox:checked');

                    if ($checkbox.length > 0) {
                        members.push($checkbox.data('member-id'));
                    }
                });

                console.log(members);
                // return;


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