<?php

session_start();

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
    exit; // Ensure no further script runs after the redirect
}

$name = $_SESSION['login_user'];

include("db/config.php");

$query = "SELECT * FROM department WHERE status = 1";
$result = mysqli_query($db, $query);
$departments = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $departments[] = $row;
    }
}
// echo "<pre>";
// print_r($departments);

$query = "SELECT sc.section_name, dv.division_name, sdv.subdivision 
          FROM section sc 
          INNER JOIN division dv ON sc.section_id = dv.section_id 
          INNER JOIN sub_division sdv ON dv.division_id = sdv.division_id 
          WHERE sc.status = 1 
          ORDER BY sc.section_name, dv.division_name";

$result = mysqli_query($db, $query);

$current_section = '';
$current_division = '';


?>


<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Manage Sub-Division</title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<style>
    * {margin: 0; padding: 0;}


    .page-header-title h5 {
        font-family: 'Arial', sans-serif;
        font-size: 1.25rem;
        color: #333; /* Dark gray color for the header */
        margin-bottom: 1rem;
    }

    .section-title {
        font-family: 'Verdana', sans-serif;
        font-size: 1.75rem;
        font-weight: bold;
        color:#33cc33; /* Dark blue color for section titles */
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #33cc33; /* Add a border for emphasis */
        padding-bottom: 0.5rem;
    }

    .division-list,
    .subdivision-list {
        list-style-type: none;
        padding-left: 0;
    }

    .division-item {
        font-family: 'Arial', sans-serif;
        font-size: 1.25rem;
        font-weight: bold;
        margin-top: 1.5rem;
        color: #007bff; /* Blue color for division names */
    }

    .subdivision-item {
        font-family: 'Arial', sans-serif;
        font-size: 1rem;
        margin-left: 1rem;
        color: #6c757d; /* Gray color for subdivisions */
    }

    .breadcrumb-item a {
        color: #007bff; /* Blue color for breadcrumb links */
        font-weight: bold;
    }

    .breadcrumb-item a:hover {
        text-decoration: underline;
    }

    .card-body {
        padding: 2rem;
        font-family: 'Arial', sans-serif;
        font-size: 1rem;
        color: #333; /* Dark gray color for text */
    }

    .dt-responsive {
        margin-top: 1rem;
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
                            <h5 class="m-b-10">Manage Sub-Division</h5>
                        </div>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="index.php"><i class="feather icon-home"></i> Home</a>
                            </li>
                            <li class="breadcrumb-item"><a href="#!">Section-Tree</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dt-responsive table-responsive">
                            <?php 
                                // Initialize variables
                                $current_section = '';
                                $current_division = '';

                                while ($row = mysqli_fetch_assoc($result)) {
                                    if ($current_section != $row['section_name']) {
                                        // Display the section name only if it's different from the last one
                                        if ($current_section !== '') {
                                            echo '</ul>'; // Close previous section's list
                                        }
                                        echo '<h3 class="section-title">' . $row['section_name'] . '</h3>';
                                        $current_section = $row['section_name'];
                                        $current_division = ''; // Reset division when the section changes
                                        echo '<ul class="division-list">'; // Start a new section's list
                                    }
                                
                                    if ($current_division != $row['division_name']) {
                                        // Display the division name only if it's different from the last one
                                        if ($current_division !== '') {
                                            echo '</ul>'; // Close previous division's list
                                        }
                                        echo '<li class="division-item"><h5>' . $row['division_name'] . '</h5></li>';
                                        $current_division = $row['division_name'];
                                        echo '<ul class="subdivision-list">'; // Start a new division's list
                                    }
                                
                                    // Display the subdivision for every row
                                    echo '<li class="subdivision-item">' . $row['subdivision'] . '</li>';
                                }
                                if ($current_division !== '') {
                                    echo '</ul>'; // Close last division's list
                                }
                                if ($current_section !== '') {
                                    echo '</ul>'; // Close last section's list
                                }
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