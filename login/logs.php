<?php

session_start();
include("db/config.php");


if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}


try {
    $stmtFetchLogs = $db->prepare("SELECT logs.*,admin.* FROM logs  
            INNER JOIN admin ON logs.user_id = admin.id
            ORDER BY logs.id DESC  ;
                ");
    if ($stmtFetchLogs->execute()) {
        $logs = $stmtFetchLogs->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $_SESSION['error'] = 'Error for fetching logs';
    }

    // // Fetch customers
    // $stmtFetchCustomers = $db->prepare("SELECT * FROM admin WHERE status = 1");
    // $stmtFetchCustomers->execute();
    // $customers = $stmtFetchCustomers->get_result()->fetch_all(MYSQLI_ASSOC);
    // $stmtFetchCustomers->close();



} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}



?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Reports</title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="Codedthemes" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

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
                                <h5 class="m-b-10">User Logs Report
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
                                <div class="dt-buttons btn-group">
                                    <button class="btn btn-secondary buttons-excel buttons-html5 btn-primary rounded-sm"
                                        tabindex="0" aria-controls="basic-btn2" type="button"
                                        onclick="exportTableToExcel()" title="Export to Excel"><span><i
                                                class="fas fa-file-excel"></i>
                                            Excel</span></button>
                                    <button class="btn btn-secondary buttons-csv buttons-html5 btn-primary rounded-sm"
                                        tabindex="0" aria-controls="basic-btn2" type="button"
                                        onclick="exportTableToCSV()" title="Export to CSV"><span><i
                                                class="fas fa-file-csv"></i> CSV</span></button>
                                    <button class="btn btn-secondary buttons-copy buttons-html5 btn-primary rounded-sm"
                                        tabindex="0" aria-controls="basic-btn2" type="button"
                                        title="Copy to clipboard"><span><i class="fas fa-copy"></i> Copy</span></button>
                                    <button class="btn btn-secondary buttons-print btn-primary rounded-sm" tabindex="0"
                                        onclick="printTable()" aria-controls="basic-btn2" type="button"
                                        title="Print"><span><i class="fas fa-print"></i> Print</span></button>
                                </div>

                                <table id="basic-btn2" class="table table-striped table-bordered nowrap">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Username</th>
                                            <th>User IP</th>
                                            <th>Location</th>
                                            <th>Login Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($log['id']); ?></td>
                                                <td><?php echo htmlspecialchars($log['username']); ?></td>
                                                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                                <td><?php echo htmlspecialchars($log['city'] . ', ' . $log['state'] . ', ' . $log['country']); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <!-- You might want to add footer content here if needed -->
                                    </tfoot>
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


    <script type="text/javascript">
        $(document).ready(function () {
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

            // Display the number of entries
            // console.log('Total number of entries:', totalEntries);

            // Optionally, you can display the number of entries in an HTML element
            $('#total').text(totalEntries);
        });
    </script>
</body>

</html>