<?php

session_start();


if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}
$name = $_SESSION['login_user'];

include("db/config.php");

$query = "SELECT DISTINCT
    sm.name, 
    m.email_id, 
    m.mobile, 
    m.firm_name, 
    ur.tender_no, 
    department.department_name,
    ur.name_of_work,
    ur.remarked_at, 
    ur.file_name, 
    ur.id,
    se.section_name,
    dv.division_name,
    sd.subdivision,
    ur.tenderID
FROM 
    user_tender_requests ur 
INNER JOIN 
    members m ON ur.member_id = m.member_id
INNER JOIN 
    members sm ON ur.selected_user_id = sm.member_id
INNER JOIN 
    department ON ur.department_id = department.department_id 
INNER JOIN 
    section se ON ur.section_id = se.section_id
INNER JOIN
    division dv ON dv.section_id = ur.section_id
INNER JOIN
    sub_division sd ON ur.division_id = sd.division_id
WHERE 
    ur.remark = 'accepted' AND ur.delete_tender = '0'
GROUP BY 
    ur.id, 
    sm.name, 
    m.email_id, 
    m.mobile, 
    m.firm_name, 
    ur.tender_no, 
    department.department_name,
    ur.name_of_work,
    ur.remarked_at, 
    ur.file_name, 
    se.section_name,
    dv.division_name,
    sd.subdivision,
    ur.tenderID
ORDER BY 
    NOW() >= CAST(ur.due_date AS DATE), 
    CAST(ur.remarked_at AS DATE) ASC, 
    ABS(DATEDIFF(NOW(), CAST(ur.due_date AS DATE)));

 ";

$result = mysqli_query($db, $query);

?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Award </title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
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
                                <h5 class="m-b-10"> List of Award Tender
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

                                ?>
                                <br />


                                <?php

                                echo '<table id="basic-btn3" class="table table-striped table-bordered nowrap">';
                                echo "<thead>";
                                echo "<tr>";
                                echo "<th>SNO</th>";
                                echo "<th>User</th>";
                                echo "<th>Tender No</th>";
                                echo "<th>Tender ID</th>";
                                echo "<th>Department</th>";
                                echo "<th>Section</th>";
                                echo "<th>Division</th>";
                                echo "<th>Sub-Division</th>";
                                echo "<th>Work Name</th>";

                                echo "<th>Awarded At</th>";


                                echo "<th>Status</th>";


                                echo "</tr>";
                                echo "</thead>";


                                ?>
                                <?php



                                $count = 1;

                                echo "<tbody>";

                                while ($row = mysqli_fetch_row($result)) {

                                    echo "<tr class='record'>";
                                    echo "<td> $count</td>";

                                    echo "<td>" . $row['0'] . "<br/> " . "<span style='color:red;'> " . $row['1'] . "</span>" . "<br/>"
                                        . "<span style='color:green;'> " . $row['2'] . "</span>" . "<br/>" . "<span style='color:orange;'> "
                                        . $row['3'] . "</span>" . "</td>";
                                    echo "<td>" . $row['4'] . "</td>";
                                    echo "<td>" . $row['13'] . "</td>";
                                    echo "<td>" . $row['5'] . "</td>";
                                    echo "<td>" . $row['10'] . "</td>";
                                    echo "<td>" . $row['11'] . "</td>";
                                    echo "<td>" . $row['12'] . "</td>";

                                    echo "<td>" . $row['6'] . "</td>";


                                    echo "<td>" . "Award Date :" . "<br/>" . date_format(date_create($row['7']), "d-m-Y h:i A") . "<br/>" . '<a href="../login/tender/' . $row['8'] . '"  target="_blank"/>View file </a>' . "</td>";


                                    $res = $row[9];
                                    $res = base64_encode($res);
                                    echo "<td>  <a href='award-edit.php?award=$res'><button type='button' class='btn btn-warning'>
                                    <i class='feather icon-edit'></i> &nbsp;Edit Status</button></a><br/></br/> <a href='#'>
                                    <button type='button' class='btn btn-success'><i class='feather icon-edit'></i> &nbsp;Awarded
                                    </button></a> ";



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
            //     if ($.fn.DataTable.isDataTable('#basic-btn3')) {
            //     $('#basic-btn3').DataTable().clear().destroy();
            // }
            //     let myTable = $("#basic-btn3").DataTable();
            //     let columnsToFilter = [4,5,6];

            //     columnsToFilter.forEach(function(colID){
            //         let mySelectList = $("<br><select class='form-control'/>")
            //         .appendTo(myTable.column(colID).header())
            //         .on("change",function(){
            //             myTable.column(colID).search($(this).val());

            //             myTable.column(colID).draw();
            //         })

            //         myTable
            //         .column(colID)
            //         .cache("search")
            //         .sort()
            //         .each(function(param){
            //             mySelectList.append(
            //                 $('<option value="' + param + '">'
            //                 + param + "</option>")
            //             );
            //         });
            //     });

                $('#basic-btn3 thead tr').clone(true).appendTo('#basic-btn3 thead');
                var columnsWithSearch = [4, 5, 6];

                $('#basic-btn3 thead tr:eq(1) th').each(function (i) {
                    if (columnsWithSearch.includes(i) && !$(this).hasClass("noFilter")) {
                        var title = $(this).text();
                        $(this).html('<input type="text" class="form-control" placeholder="Search ' + title + '" />');

                        $('input', this).on('keyup change', function () {
                            if (table.column(i).search() !== this.value) {
                                table
                                    .column(i)
                                    .search(this.value)
                                    .draw();
                            }
                        });

                    } else {
                        $(this).html('<span></span>');
                    }
                });

                var table = $('#basic-btn3').DataTable({
                    orderCellsTop: true,
                    fixedHeader: true,
                    columnDefs: [
                        { targets: 0, visible: true }
                    ]
                });


                $("#updateuser").delay(5000).slideUp(300);
            // });

            // Clone the header row for filtering
            // $('#basic-btn3 thead tr').clone(true).appendTo('#basic-btn3 thead');
            // var columnsWithSearch = [5, 8, 9, 10, 11, 13]; // Columns for filtering

            // // Add filters to the cloned header
            // $('#basic-btn3 thead tr:eq(1) th').each(function (i) {
            //     if (columnsWithSearch.includes(i) && !$(this).hasClass("noFilter")) {
            //         var column = table.column(i); // Use the existing DataTable instance
            //         var select = $('<select class="form-control"><option value="">Select</option></select>')
            //             .appendTo($(this).empty())
            //             .on('change', function () {
            //                 var val = $.fn.dataTable.util.escapeRegex($(this).val());
            //                 column
            //                     .search(val ? '^' + val + '$' : '', true, false)
            //                     .draw();
            //             });

            //         // Populate the select dropdown with unique values from the column
            //         column.data().unique().sort().each(function (d, j) {
            //             if (d) {
            //                 select.append('<option value="' + d + '">' + d + '</option>');
            //             }
            //         });
            //     } else {
            //         $(this).html('<span></span>');
            //     }
            // });
            
            // // Optional: Hide update message after 5 seconds
            // $("#updateuser").delay(5000).slideUp(300);
        });
    </script>

    <script type="text/javascript">
            $(function () {
                $(".delbutton").click(function () {

                    var element = $(this);

                    var del_id = element.attr("id");

                    var info = 'id=' + del_id;
                    if (confirm("Are you sure you want to delete this Record?")) {
                        $.ajax({
                            type: "GET",
                            url: "deleteuser.php",
                            data: info,
                            success: function () { }
                        });
                        $(this).parents(".record").animate({
                            backgroundColor: "#FF3"
                        }, "fast")
                            .animate({
                                opacity: "hide"
                            }, "slow");
                    }
                    return false;
                });
            });
    </script>


</body>

</html>