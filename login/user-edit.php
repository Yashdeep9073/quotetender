<?php

session_start();


if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}


$name = $_SESSION['login_user'];

include("db/config.php");

$en = $_GET["id"];

$de = base64_decode($en);

$result = mysqli_query($db, "SELECT * FROM admin WHERE email='" . $de . "'");
$row = mysqli_fetch_row($result);

$editUserPermissionQuery = "SELECT nm.title FROM admin_permissions ap 
inner join navigation_menus nm on ap.navigation_menu_id = nm.id where ap.admin_id='" .$row[9] . "' ";
$editUserPermissionResult = mysqli_query($db, $editUserPermissionQuery);

$selectedPermissions=[];
while ($permission = mysqli_fetch_row($editUserPermissionResult)) {
    array_push($selectedPermissions,$permission[0]);
}

/* Attempt to connect to MySQL database */
if (isset($_POST['submit'])) {


    if (!empty($_POST['type'])) {
        $types = array_values($_POST['type']);
        foreach ($types as $type) {

            $adminPermissionExists = mysqli_query($db, "SELECT * FROM admin_permissions WHERE admin_id='" . $row[9] . "'
            and navigation_menu_id='" . $type . "'");
            $count = mysqli_num_rows($adminPermissionExists);

            if ($count == 0) {
                $query = "insert into admin_permissions (admin_id, navigation_menu_id ) 
                values('$row[9]','$type')";
                mysqli_query($db, $query);
            }

        }

        mysqli_query($db, "DELETE FROM admin_permissions WHERE admin_id='" . $row[9] . "' 
        and navigation_menu_id not in ( '" . implode( "', '" , $types ) . "' )" );

    }

    if (!empty($_POST['password'])) {

        if (count($_POST) > 0) {
            mysqli_query($db, "UPDATE admin set username ='" . $_POST["username"] . "',password ='" . md5($_POST["password"]) . "',
        email ='" . $_POST["email"] . "',status ='" . $_POST["status"] . "' ,
        mobile ='" . $_POST["mobile"] . "' WHERE email='"  . $de . "'");

            $staus = 1;

            $re = base64_encode($staus);

            echo ("<SCRIPT LANGUAGE='JavaScript'>window.location.href='view-user.php?status=$re';</SCRIPT>");
        }
    } else {

        if (count($_POST) > 0) {
            mysqli_query($db, "UPDATE admin set username ='" . $_POST["username"] . "',email ='" . $_POST["email"] . "',
        status ='" . $_POST["status"] . "' ,mobile ='" . $_POST["mobile"] . "'
         WHERE email='"  . $de . "'");

            $staus = 1;

            $re = base64_encode($staus);

            echo ("<SCRIPT LANGUAGE='JavaScript'>window.location.href='view-user.php?status=$re';</SCRIPT>");
        }
    }
}


$typesQuery = "SELECT * FROM navigation_menus ";
$types = mysqli_query($db, $typesQuery);


?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Update User </title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="Codedthemes" />

    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">

    <Style>
        .select2-container--default .select2-selection--multiple .select2-selection__rendered li {
            list-style: none;
            color: #33cc33;
            ;
            background: #fff;
        }
    </Style>

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
                    <a href="#!" class="full-screen" onClick="javascript:toggleFullScreen()"><i class="feather icon-maximize"></i></a>
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
                                <h5 class="m-b-10">Update User
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
                            <form class="contact-us" method="post" action="" enctype="multipart/form-data" autocomplete="off">
                                <div class=" ">
                                    <!-- Text input-->
                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Enter Username*
                                                <label class="sr-only control-label" for="name">Username<span class=" ">
                                                    </span></label>
                                                <input id="name" name="username" type="text" placeholder=" Username" class="form-control input-md" required value="<?php echo $row[0]; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Enter Password*
                                                <label class="sr-only control-label" for="name">Password<span class=" ">
                                                    </span></label>
                                                <input id="name" name="password" type="password" placeholder="Enter new password,if you want to change current password" class="form-control input-md" value="">
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Mobile No*
                                                <label class="sr-only control-label" for="name">Mobile No<span class=" ">
                                                    </span></label>
                                                <input id="name" name="mobile" type="number" placeholder=" Enter Mobile No *" class="form-control input-md" required oninvalid="this.setCustomValidity('Please Enter Mobile Number')" oninput="setCustomValidity('')" value="<?php echo $row[6]; ?>">
                                            </div>
                                        </div>


                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Email*
                                                <label class="sr-only control-label" for="name">Email<span class=" ">
                                                    </span></label>
                                                <input id="name" name="email" type="email" class="form-control input-md" required placeholder="Enter Email" value="<?php echo $row[2]; ?>">
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">Status*
                                                <label class="sr-only control-label" for="name">Status<span class=" ">
                                                    </span></label>
                                                <select id="" name="status" class="form-control" required>
                                                    <option value="<?php echo $row['3']; ?>">
                                                        <?php if ($row['3'] == 1) {
                                                            echo "Enable";
                                                        } else {
                                                            echo "Disable";
                                                        } ?>
                                                    </option>
                                                    <option value="1">Enable</option>
                                                    <option value="0">Disabe</option>

                                                </select>
                                            </div>
                                        </div>

                                        <!-- Text input-->
                                        <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                            <div class="form-group">User Type*

                                                <select id="multiSelect" name="type[]" multiple="multiple" required>
                                                    <?php while ($row = mysqli_fetch_row($types)) {
                                                        $selected = in_array($row['1'], $selectedPermissions) ? "selected=''" : '';

                                                        echo "<option value='" . $row['0'] . "'" . $selected . ">" . $row['1'] . "</option>";
                                                    } ?>

                                                </select>

                                            </div>
                                        </div>


                                        <!-- Button -->
                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">


                                            <button type="submit" class="btn btn-secondary" name="submit" id="submit">
                                                <i class="feather icon-save lg"></i>&nbsp; Update
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#multiSelect').select2({
                width: '100%',
                closeOnSelect: false,
                templateSelection: function(data, container) {
                    // Add a cross icon to selected items    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">

                    var $option = $(data.element);
                    var text = $option.text();
                    container.text(text);
                    container.append('<span class="remove-item" data-value="' + data.id + '">&times;</span>');
                }
            });

            // Handle removal of selected items
            $('#multiSelect').on('click', '.remove-item', function() {
                var valueToRemove = $(this).data('value');
                var $select = $('#multiSelect');
                $select.find('option[value="' + valueToRemove + '"]').prop('selected', false);
                $select.trigger('change.select2');
            });
        });
    </script>
</body>

</html>