<?php

session_start();
error_reporting(0);
$upload_directory = "category/";
if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

$name = $_SESSION['login_user'];
include("db/config.php");
$en = $_GET["id"];
$de = base64_decode($en);
// Register user
if (isset($_POST['submit'])) {
    
   if (!empty($_FILES["uploaded_file"]["name"])) {
    
    $category = $_POST['category'];
    $parent = $_POST['parent'];
    $menu = $_POST['menu'];
    $papular = $_POST['popular'];
    $status = $_POST['status'];
    $temp_name = $_FILES["uploaded_file"]["tmp_name"];
    $original_name = $_FILES["uploaded_file"]["name"];
    $file_size = $_FILES["uploaded_file"]["size"];
    mysqli_select_db($db, DB_NAME);
    // Move the uploaded file to the desired directory
    $allowed_types = ["image/jpeg", "image/png", "image/gif"];
    $file_type = mime_content_type($temp_name);
    if (!in_array($file_type, $allowed_types)) {

        $msg = "<div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='goldmessage'>
        <strong><i class='feather icon-check'></i>Error !</strong> Please Upload Image File.
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
          <span aria-hidden='true'>&times;</span>
        </button>
      </div>";
    } else {

        if ($file_size < 2 * 1024 * 1024) {
            $unique_filename = uniqid() . '_' . $original_name;
            // Delete the old image
            $query = "SELECT * FROM category WHERE 	category_id='"  . $de . "'";
            $result = mysqli_query($db, $query);
            $row = mysqli_fetch_row($result);
            $f = $row['5'];
            $old = "category/" . $f; // Path to the old image
            unlink($old);
            move_uploaded_file($temp_name, $upload_directory . $unique_filename);
            mysqli_query($db, "UPDATE category set Category_Name ='$category', parent_category='$parent',show_in_menu='$menu',show_popular_list='$papular',image='$unique_filename',status='$status' WHERE category_id='"  . $de . "'");
            $stat = 1;
            $re = base64_encode($stat);
            echo ("<SCRIPT LANGUAGE='JavaScript'>
    window.location.href='view-category.php?status=$re';
    </SCRIPT>");
        } else {
            $msg = "<div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='goldmessage'>
        <strong><i class='feather icon-check'></i>Error !</strong> File size exceeds the limit of 2MB.
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
          <span aria-hidden='true'>&times;</span>
        </button>
      </div>";
        }
    }
    
   }
   else
   {
       
         $category = $_POST['category'];
    $parent = $_POST['parent'];
    $menu = $_POST['menu'];
    $papular = $_POST['popular'];
    $status = $_POST['status'];
    
      mysqli_query($db, "UPDATE category set Category_Name ='$category', parent_category='$parent',show_in_menu='$menu',show_popular_list='$papular',status='$status' WHERE category_id='"  . $de . "'");
            $stat = 1;
            $re = base64_encode($stat);
            echo ("<SCRIPT LANGUAGE='JavaScript'>
    window.location.href='view-category.php?status=$re';
    </SCRIPT>");
       
   }
    
}

$result = mysqli_query($db, "SELECT * FROM category WHERE 	category_id='"  . $de . "'");
$row = mysqli_fetch_row($result);

?>


<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Edit Category </title>



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
                                <h5 class="m-b-10">Edit Category
                                </h5>
                            </div>

                        </div>
                    </div>
                </div>
            </div>


            <div class="row">

                <div class="col-sm-12">
                    <div class="card">
                        <?php
                        if ($msg) {
                            echo $msg;
                        }
                        ?>

                        <div class="card-header table-card-header">
                            <form class="contact-us" method="post" action="" enctype="multipart/form-data"
                                autocomplete="off">
                                <div class=" ">
                                    <!-- Text input-->
                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group">Category Name
                                                <label class="sr-only control-label" for="name">Category Name<span
                                                        class=" ">
                                                    </span></label>
                                                <input id="category" name="category" type="text"
                                                    placeholder=" Enter the Category Name"
                                                    value="<?php echo$row[1]; ?>" class="form-control input-md"
                                                    required
                                                    oninvalid="this.setCustomValidity('Please Enter Category Name')"
                                                    oninput="setCustomValidity('')">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group">Parent Category*
                                                <label class="sr-only control-label" for="name">name<span class=" ">
                                                    </span></label>
                                                <select class="form-control" name="parent" required>
                                                    <option value="<?php echo $row[2]; ?>"><?php echo $row[2]; ?>
                                                    </option>
                                                   
                                                       <option value=""> Select Parent Category</option>
                                                      <option value="Legrand"> Legrand</option>
                                                    <option value="Havells"> Havells</option>
                                                    <option value="Indoasian">Indoasian</option>
                                                    <option value="Control & Switchgear"> Control &amp; Switchgear</option>
                                                    <option value="Schneider">Schneider</option>
                                                    <option value="Switchgear">Switchgear</option>
                                                    <option value="L & T">L&amp;T</option>
                                                </select>
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group">Show On Top Menu*
                                                <label class="sr-only control-label" for="name">Show On Top Menu<span
                                                        class=" ">
                                                    </span></label>
                                                <select class="form-control" name="menu" required>

                                                    <option value="<?php echo $row[3]; ?>"><?php echo $row[3]; ?>
                                                    </option>
                                                    <option value="yes">Yes</option>
                                                    <option value="no">No</option>

                                                </select>
                                            </div>
                                        </div>




                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group">Show In Popular List*
                                                <label class="sr-only control-label" for="name">Show In Popular
                                                    List<span class=" ">
                                                    </span></label>
                                                <select class="form-control" name="popular" required>


                                                    <option value="<?php echo $row[4]; ?>"><?php echo $row[4]; ?>
                                                    </option>
                                                    <option value="yes">Yes</option>
                                                    <option value="no">No</option>

                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group">Image
                                                <label class="sr-only control-label" for="name">Image<span class=" ">
                                                    </span></label>
                                                <input name="uploaded_file" type="file" class="form-control input-md"
                                                     accept="image/*">
                                            </div>


                                        </div>
                                        
                                        
                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">


                                            <div class="form-group">

                                                <?php echo  '<img src="category/' . $row['5'] . '" style="width:80px;height:80px;" class="img" />'; ?>

                                            </div>


                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                            <div class="form-group">Status*
                                                <label class="sr-only control-label" for="name"> Status<span class=" ">
                                                    </span></label>
                                                <select id="" name="status" class="form-control" required>
                                                    <option value="<?php echo $row['6']; ?>">
                                                        <?php if($row['6']==1) { echo Enable;  } else {echo Disable;}?>
                                                    </option>
                                                    <option value="1">Enable</option>
                                                    <option value="0">Disabe</option>

                                                </select>
                                            </div>
                                        </div>
                                        <!-- Text input-->



                                        <!-- Button -->
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">


                                            <button type="submit" class="btn btn-secondary" name="submit" id="submit">
                                                <i class="feather icon-save"></i>&nbsp; Update Category
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
    <script>
    $(document).ready(function() {
        $("#goldmessage").delay(5000).slideUp(300);
    });
    </script>
</body>

</html>