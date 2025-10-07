<?php
session_start();

error_reporting(0);
if (!isset($_SESSION["login_register"])) {
    header("location: ../index.php");
}
$name = $_SESSION['login_register'];

include("../login/db/config.php");

$memberQuery = "SELECT member_id FROM members WHERE email_id='" . $_SESSION["login_register"] . "'";
$memberData = mysqli_query($db, $memberQuery);
$member = mysqli_fetch_row($memberData);

$query = "SELECT department.department_name,  ur.tenderID, ur.created_at,
ur.due_date, ur.file_name,  ur.status, ur.id, ur.remark, ur.project_status, ur.file_name2 , ur.tentative_cost,ur.additional_files FROM user_tender_requests ur 
inner join department on ur.department_id = department.department_id WHERE ur.member_id='" . $member[0] . "'";

$result = mysqli_query($db, $query);

$memberQuery1 = "SELECT name FROM members WHERE email_id='" . $_SESSION["login_register"] . "'";
$memberData1 = mysqli_query($db, $memberQuery1);
$member1 = mysqli_fetch_row($memberData1);
?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Your Tender Requests</title>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="" />

    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
</head>

<body class="">

    <?php if (isset($_SESSION['success'])) { ?>
        <script>
            const notyf = new Notyf({
                position: {
                    x: 'center',
                    y: 'top'
                },
                types: [
                    {
                        type: 'success',
                        background: '#26c975', // Change background color
                        textColor: '#FFFFFF',  // Change text color
                        dismissible: true,
                        duration: 10000
                    }
                ]
            });
            notyf.success("<?php echo $_SESSION['success']; ?>");
        </script>
        <?php
        unset($_SESSION['success']);
        ?>
    <?php } ?>

    <?php if (isset($_SESSION['error'])) { ?>
        <script>
            const notyf = new Notyf({
                position: {
                    x: 'center',
                    y: 'top'
                },
                types: [
                    {
                        type: 'error',
                        background: '#ff1916',
                        textColor: '#FFFFFF',
                        dismissible: true,
                        duration: 10000
                    }
                ]
            });
            notyf.error("<?php echo $_SESSION['error']; ?>");
        </script>
        <?php
        unset($_SESSION['error']);
        ?>
    <?php } ?>


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
                        <a href="home.php" class="nav-link " style="background:#33cc33; color:#fff;"><span
                                class="pcoded-micon"><i class="feather icon-home"></i></span><span
                                class="">Dashboard</span></a>
                    </li>



                    <li class="nav-item">
                        <a href="tender-request.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-edit"></i></span><span class="pcoded-mtext">Tender
                                Requests</span></a>

                    </li>






                    <li class="nav-item">
                        <a href="edit-profile.php" class="nav-link"><span class="pcoded-micon"><i
                                    class="feather icon-edit"></i></span><span class="">
                                Edit Profile</span></a>
                    </li>





                    <li class="nav-item">
                        <a href="changepass.php" class="nav-link"><span class="pcoded-micon"><i
                                    class="feather icon-command"></i></span><span class="">Change
                                Password</span></a>
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
                USER PANEL

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
                    <span><?php echo $member1[0]; ?></span>
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
                            <a href="../index.php" class="btn btn-primary">
                                << Back to Main Website</a>
                                    <div class="page-header-title">
                                        <h5 class="m-b-10">Your Tender Requests
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
                                $count = 1;
                                ?>

                                <table id="basic-btn" class="table table-striped table-bordered nowrap">
                                    <thead>
                                        <tr>
                                            <th>SNO</th>
                                            <th>Department</th>
                                            <th>Tender No</th>
                                            <th>Tentative Cost</th>
                                            <th>Date Add</th>
                                            <th>Start Date</th>
                                            <th>File</th>
                                            <th>Award Status</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_row($result)): ?>
                                            <tr class='record'>
                                                <td><?php echo $count; ?></td>
                                                <td><?php echo htmlspecialchars($row[0]); ?></td>
                                                <td><?php echo htmlspecialchars($row[1]); ?></td>
                                                <td><?php echo htmlspecialchars($row[6]); ?></td>
                                                <td><?php echo date_format(date_create($row[2]), "Y-m-d"); ?></td>
                                                <td><?php echo date_format(date_create($row[3]), "Y-m-d"); ?></td>

                                                <?php
                                                $res = base64_encode($row[6]);

                                                if ($row[5] == 'Requested') {
                                                    ?>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>
                                                        <button type='button' class='btn btn-warning'>
                                                            <i class='feather icon-edit'></i> &nbsp;Pending
                                                        </button>
                                                    </td>
                                                    <?php
                                                } else {
                                                    if ($row[5] == 'Allotted' && (empty($row[7])) || $row[5] == 'Sent') {
                                                        ?>
                                                        <td>

                                                            <?php if (isset($row[4]) && $row[4] == null) { ?>
                                                                <a href="<?= '../login/tender/' . $row[4] ?>" target="_blank" style="padding:6px 15.2px;">
                                                                    View file 1
                                                                </a> </br>
                                                            <?php } ?>

                                                             <?php if (isset($row[9]) && $row[9] == null) { ?>
                                                                <a href="<?= '../login/tender/' . $row[9] ?>" target="_blank" style="padding:6px 15.2px;">
                                                                    View file 2
                                                                </a> </br>
                                                            <?php } ?>

                                                             <?php if (!empty($row[11])) {
                                            $extraFiles = json_decode($row[11], true);
                                            ?>
                                            <?php if (is_array($extraFiles)) {
                                                $count = 1;
                                                ?>
                                                <?php foreach ($extraFiles as $index => $filePath) { ?>
                                                    <a href="<?= '../login/' . $filePath ?>" target="_blank">View
                                                        File <?= $count ?>
                                                    </a><br />
                                                    <?php
                                                    $count++;
                                                } ?>
                                            <?php } ?>
                                        <?php } ?>

                                                            

                                                        </td>
                                                        <td>
                                                            <a href='reward-tender-edit.php?id=<?php echo $res; ?>'>
                                                                <button type='button' class='btn btn-warning'>
                                                                    <i class='feather icon-edit'></i> &nbsp; Make Award
                                                                </button>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <button type='button' class='btn btn-success'>
                                                                <i class='feather icon-edit'></i> &nbsp;Approved
                                                            </button>
                                                        </td>
                                                        <?php
                                                    }

                                                    if ($row[7] == 'accepted' || $row[7] == 'denied') {
                                                        $projectStatus = !empty($row[8]) ? $row[8] : $row[7] . " by you";
                                                        ?>
                                                        <td>
                                                            <?php if (isset($row[4]) && $row[4] == null) { ?>
                                                                <a href="<?= '../login/tender/' . $row[4] ?>" target="_blank" style="padding:6px 15.2px;">
                                                                    View file 1
                                                                </a> </br>
                                                            <?php } ?>

                                                             <?php if (isset($row[9]) && $row[9] == null) { ?>
                                                                <a href="<?= '../login/tender/' . $row[9] ?>" target="_blank" style="padding:6px 15.2px;">
                                                                    View file 2
                                                                </a> </br>
                                                            <?php } ?>

                                                             <?php if (!empty($row[11])) {
                                                                    $extraFiles = json_decode($row[11], true);
                                                                    ?>
                                                                    <?php if (is_array($extraFiles)) {
                                                                        $count = 1;
                                                                        ?>
                                                                        <?php foreach ($extraFiles as $index => $filePath) { ?>
                                                                            <a href="<?= '../login/' . $filePath ?>" target="_blank">View
                                                                                File <?= $count ?>
                                                                            </a><br />
                                                                            <?php
                                                                            $count++;
                                                                        } ?>
                                                                    <?php } ?>
                                                                <?php } ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($projectStatus); ?></td>
                                                        <td>
                                                            <a href='#'>
                                                                <button type='button' class='btn btn-danger'>
                                                                    <i class='feather icon-edit'></i> &nbsp; Closed
                                                                </button>
                                                            </a>
                                                        </td>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </tr>
                                            <?php $count++; ?>
                                        <?php endwhile; ?>
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