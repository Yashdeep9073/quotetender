<?php

session_start();


if (!isset($_SESSION["login_user"])) {
  header("location: index.php");
}
$name = $_SESSION['login_user'];

include("db/config.php");

$query = "SELECT * FROM gold";
$result = mysqli_query($db, $query);

?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>View Gold Price </title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="Codedthemes" />

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




    <nav class="pcoded-navbar menupos-fixed menu-light ">
        <div class="navbar-wrapper  ">
            <div class="navbar-content scroll-div ">
                <ul class="nav pcoded-inner-navbar ">
                    <li class="nav-item pcoded-menu-caption">
                        <label>Navigation</label>
                    </li>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link " style="background:#33cc33; color:#fff;"><span
                                class="pcoded-micon"><i class="feather icon-home"></i></span><span
                                class="">Dashboard</span></a>
                    </li>


                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-edit"></i></span><span class="pcoded-mtext">Category</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href="add-category.php">Add Category</a></li>
                            <li><a href="view-category.php">Manage Category</a></li>

                        </ul>
                    </li>


                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-file"></i></span><span class="pcoded-mtext">Brands</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href="add-brand.php">Add Brands</a></li>
                            <li><a href="manage-brand.php">Manage Brands</a></li>

                        </ul>
                    </li>

                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-home"></i></span><span
                                class="pcoded-mtext">Departments</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href="add-department.php">Add Departments</a></li>
                            <li><a href="manage-department.php">Manage Departments</a></li>


                        </ul>
                    </li>



                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-credit-card"></i></span><span class="pcoded-mtext">Price
                                List</span></a>
                        <ul class="pcoded-submenu">

                            <li><a href="add-price.php">Add Price List</a></li>
                            <li><a href="manage-price.php">Manage Price List</a></li>



                        </ul>
                    </li>


                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-users"></i></span><span class="pcoded-mtext">Users</span></a>
                        <ul class="pcoded-submenu">

                            <li><a href="add-user.php">Add User</a></li>
                            <li><a href="view-user.php">Manage User</a></li>



                        </ul>
                    </li>


                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-briefcase"></i></span><span class="pcoded-mtext">Tenders
                            </span></a>
                        <ul class="pcoded-submenu">

                            <li><a href="tender-request.php">Tender Request</a></li>
                            <li><a href="sent-tender.php"> Sent Tender</a></li>
                            <li><a href="alot-tender.php"> Alot Tender</a></li>


                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="registered-users.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-globe"></i></span><span class="">Registered users</span></a>
                    </li>

                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-credit-card"></i></span><span class="pcoded-mtext">Website
                            </span></a>
                        <ul class="pcoded-submenu">

                            <li><a href="add-banner.php">Add Banner</a></li>
                            <li><a href="manage-banner.php"> Manage Banner</a></li>

                            <li><a href="add-content.php">Add Content</a></li>
                            <li><a href="manage-content.php"> Manage Content</a></li>



                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="configuration.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-server"></i></span><span class="">Setting</span></a>
                    </li>

                    <li class="nav-item">
                        <a href="changepass.php" class="nav-link"><span class="pcoded-micon"><i
                                    class="feather icon-command"></i></span><span class="">Change
                                Password</span></a>
                    </li>

                    <li class="nav-item">
                        <a href="reports.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-file-plus"></i></span><span class="">Logs Report</span></a>
                    </li>

                    <li class="nav-item">
                        <a href="logout.php" class="nav-link " style="background:#33cc33; color:#fff;"><span
                                class="pcoded-micon"><i class="feather icon-power"></i></span><span class="">Log
                                out</span></a>
                    </li>

                </ul>

            </div>
        </div>
    </nav>



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
                                <h5 class="m-b-10">View/Edit Gold Price
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



                        </div>
                        <div class="card-body">
                            <div class="dt-responsive table-responsive">

                                <?php

                if (isset($_GET['status'])) {
                  $st = $_GET['status'];
                  $st1 = base64_decode($st);

                  if ($st1 > 0) {
                    echo " <div class='alert alert-success alert-dismissible fade show' role='alert' style='font-size:16px;' id='gold'>
  <strong><i class='feather icon-check'></i>Thanks!</strong> Gold rate  has been Updated Successfully.
  <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
    <span aria-hidden='true'>&times;</span>
  </button>
</div> ";
                  } else {

                    echo " <div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='gold'>
  <strong>Error!</strong> Gold Rate  not Updated
  <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
    <span aria-hidden='true'>&times;</span>
  </button>
</div> ";
                  }
                }

                ?>
                                <br />
                                <?php

                echo '<table id="basic-btn" class="table table-striped table-bordered nowrap">';
                echo "<thead>";
                echo "<tr>";
                echo "<th>SNO</th>";
                echo "<th>Gold Price</th>";
                echo "<th>Date</th>";
                echo "<th>Edit</th>";


                echo "</tr>";
                echo "</thead>";


                ?>
                                <?php

                $count = 1;
                echo "<tbody>";
                while ($row = mysqli_fetch_row($result)) {

                  echo "<tr class='record'>";
                  echo "<td> $count </td>";
                  echo "<td>" . $row['1'] . "</td>";
                  echo "<td>" . $row['2'] . "</td>";
                  $res = $row[0];
                  $res = base64_encode($res);


                  echo "<td>  <a href='gold-edit.php?price=$res'><button type='button' class='btn btn-warning'><i class='feather icon-edit'></i> &nbsp;Edit</button></a>  &nbsp;   <a href='#' id='" . $row['1'] . "'class='delbutton btn btn-danger' title='Click To Delete'> <i class='feather icon-trash'></i>  &nbsp; delete</a></td>";


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
    $(document).ready(function() {
        $("#gold").delay(5000).slideUp(300);
    });
    </script>


    <script type="text/javascript">
    $(function() {
        $(".delbutton").click(function() {

            var element = $(this);

            var del_id = element.attr("id");

            var info = 'id=' + del_id;
            if (confirm("Are you sure you want to delete this Record?")) {
                $.ajax({
                    type: "GET",
                    url: "deletegold.php",
                    data: info,
                    success: function() {}
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