<?php
session_start();

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

include("db/config.php");
$name = $_SESSION['login_user'];

$msg = null;

// Register user
if (isset($_POST['submit'])) {
    
    
    $state= $_POST['statelist'];
    $subdiv= $_POST['subdiv'];

    $query = "insert into  sub_division (subdivision, division_id  ) values('$subdiv','$state')";
    mysqli_query($db, $query);
    if ($query) {
        $msg = "
            <div class='alert alert-success alert-dismissible fade show' role='alert' style='font-size:16px;'>
            <strong><i class='feather icon-check'></i>Thanks!</strong>Add Sub Division Successfully
            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
              <span aria-hidden='true'>&times;</span>
            </button>
          </div>
            ";
    }
}

$ct = "SELECT * FROM section";
$result = mysqli_query($db, $ct);



?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Add Sub Division</title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="Codedthemes" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
    
    		  <script>
function getstate(val) {
	//alert(val);
	$.ajax({
	type: "POST",
	url: "get_state.php",
	data:'coutrycode='+val,
	success: function(data){
		$("#statelist").html(data);
	}
	});
}

function getcity(val) {
	//alert(val);
	$.ajax({
	type: "POST",
	url: "get_city.php",
	data:'statecode='+val,
	success: function(data){
		$("#city").html(data);
	}
	});
}

</script>
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
                                <h5 class="m-b-10">Add Sub Division
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

                            <?php
                            echo $msg;
                            ?>

                            <br />

                            <form class="contact-us" method="post" action="" enctype="multipart/form-data"
                                autocomplete="off">
                                <div class=" ">
                                    <!-- Text input-->
                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group"> Section
                                                <label class="sr-only control-label" for="name">Section<span class=" ">
                                                    </span></label>
                                                    
                                                    <select onChange="getstate(this.value);"  name="country" id="country" class="form-control" >
                    <option value="">Select</option>
                                              <?php

                                             
                                                while ($row = mysqli_fetch_row($result)) {


                                                    echo "<option value='" . $row['0'] . "'>" . $row['1'] . "</option>";
                                                }
                                             
                                                ?>
                                                
                                                </select>
                                            </div>
                                        </div>
                                        
                                         <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                        
                                         <div class="form-group"> Division
                                                <label class="sr-only control-label" for="name">Section<span class=" ">
                                                    </span></label>
                                             

                                             <select class='form-control' required name="statelist" id="statelist">
                                                


                                                <option value=''>Select State </option>
                                              
                                            </select>
                                             
                                            </div>
                                        </div>



   <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group"> Sub Division
                                                <label class="sr-only control-label" for="name">Section<span class=" ">
                                                    </span></label>
                                                <input id="department" name="subdiv" type="text"
                                                    placeholder=" Enter Sub Division Name" class="form-control input-md"
                                                    required
                                                    oninvalid="this.setCustomValidity('Please Enter Sub Division Name')"
                                                    oninput="setCustomValidity('')">
                                            </div>
                                        </div>


                                        <!-- Button -->
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">


                                            <button type="submit" class="btn btn-secondary" name="submit" id="submit">
                                                <i class="feather icon-save"></i>&nbsp; Add Sub Division
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
 

</script>

</body>

</html>