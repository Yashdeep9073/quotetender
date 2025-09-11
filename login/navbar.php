<?php
$adminID = $_SESSION['login_user_id'];
$adminPermissionQuery = "SELECT nm.title FROM admin_permissions ap 
inner join navigation_menus nm on ap.navigation_menu_id = nm.id where ap.admin_id='" . $adminID . "' ";
$adminPermissionResult = mysqli_query($db, $adminPermissionQuery);

$permissions = [];
while ($item = mysqli_fetch_row($adminPermissionResult)) {
    array_push($permissions, $item[0]);
}
?>

<style>
.logout_btn:hover .logout_btn{
        color: rgb(255, 255, 255) !important; /* Set text color to white */
        background-color: #ff2046 !important; /* Set background color */
    }
</style>


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

                <?php if (
                    (in_array('Add Section', $permissions)) || (in_array('Manage Section', $permissions)) || (in_array('Add Division', $permissions))
                    || (in_array('Manage Division', $permissions)) || (in_array('Sub Division', $permissions)) || (in_array('Manage Sub Section', $permissions))
                    || (in_array('All', $permissions))
                ) {
                    ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-file"></i></span><span class="pcoded-mtext">Section</span></a>
                        <ul class="pcoded-submenu">
                            <?php if ((in_array('Add Section', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='add-section.php'>Add Section</a></li>";
                            }
                            if ((in_array('Manage Section', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='view-section.php'>Manage Section</a></li>";
                            }
                            if ((in_array('Add Division', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='add-division.php'>Add Division</a></li>";
                            }
                            if ((in_array('Manage Division', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='view-division.php'>Manage Division</a></li>";
                            }
                            if ((in_array('Sub Division', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='add-sub-section.php'>Sub Division</a></li>";
                            }
                            if ((in_array('Manage Sub Section', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='view-sub-section.php'>Manage Sub Section</a></li>";
                            }
                            if ((in_array('Manage Sub Section', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='view-section-tree.php'>Section Tree</a></li>";
                            }
                            ?>

                        </ul>
                    </li>
                <?php } ?>

                <?php if (
                    (in_array('Add Category', $permissions)) || (in_array('Manage Category', $permissions))
                    || (in_array('All', $permissions))
                ) {
                    ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-edit"></i></span><span class="pcoded-mtext">Category</span></a>
                        <ul class="pcoded-submenu">
                            <?php if ((in_array('Add Category', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='add-category.php'>Add Category</a></li>";
                            }
                            if ((in_array('Manage Category', $permissions)) || (in_array('All', $permissions))) {
                                echo " <li><a href='view-category.php'>Manage Category</a></li>";
                            } ?>
                        </ul>
                    </li>
                <?php } ?>

                <?php if (
                    (in_array('Add Brands', $permissions)) || (in_array('Manage Brands', $permissions))
                    || (in_array('All', $permissions))
                ) {
                    ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-file"></i></span><span class="pcoded-mtext">Brands</span></a>
                        <ul class="pcoded-submenu">
                            <?php if ((in_array('Add Brands', $permissions)) || (in_array('All', $permissions))) {
                                echo " <li><a href='add-brand.php'>Add Brands</a></li>";
                            }
                            if ((in_array('Manage Brands', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='manage-brand.php'>Manage Brands</a></li>";
                            } ?>
                        </ul>
                    </li>
                <?php } ?>

                <?php if (
                    (in_array('Add Departments', $permissions)) || (in_array('Manage Departments', $permissions))
                    || (in_array('All', $permissions))
                ) {
                    ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-home"></i></span><span class="pcoded-mtext">Departments</span></a>
                        <ul class="pcoded-submenu">
                            <?php if ((in_array('Add Departments', $permissions)) || (in_array('All', $permissions))) {
                                echo " <li><a href='add-department.php'>Add Departments</a></li>";
                            }
                            if ((in_array('Manage Departments', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='manage-department.php'>Manage Departments</a></li>";
                            } ?>

                        </ul>
                    </li>
                <?php } ?>

                <?php if (
                    (in_array('Add Price List', $permissions)) || (in_array('Manage Price List', $permissions))
                    || (in_array('All', $permissions))
                ) {
                    ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-credit-card"></i></span><span class="pcoded-mtext">Price
                                List</span></a>
                        <ul class="pcoded-submenu">
                            <?php if ((in_array('Add Price List', $permissions)) || (in_array('All', $permissions))) {
                                echo " <li><a href='add-price.php'>Add Price List</a></li>";
                            }
                            if ((in_array('Manage Price List', $permissions)) || (in_array('All', $permissions))) {
                                echo " <li><a href='manage-price.php'>Manage Price List</a></li>";
                            } ?>

                        </ul>
                    </li>
                <?php } ?>

                <?php if (
                    (in_array('Add Staff User', $permissions)) || (in_array('Manage Staff User', $permissions))
                    || (in_array('All', $permissions))
                ) {
                    ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-users"></i></span><span class="pcoded-mtext">Staff User</span></a>
                        <ul class="pcoded-submenu">
                            <?php if ((in_array('Add Staff User', $permissions)) || (in_array('All', $permissions))) {
                                echo " <li><a href='add-user.php'>Add Staff User</a></li>";
                            }
                            if ((in_array('Manage Staff User', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='view-user.php'>Manage Staff User</a></li>";
                            } ?>

                        </ul>
                    </li>
                <?php } ?>

                <?php if (
                    (in_array('Tender Request', $permissions)) || (in_array('Sent Tender', $permissions))
                    || (in_array('Alot Tender', $permissions)) || (in_array('Award Tender', $permissions))
                    || (in_array('All', $permissions)) || (in_array('View Tenders', $permissions))
                ) {
                    ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-briefcase"></i></span><span class="pcoded-mtext">Tenders
                            </span></a>
                        <ul class="pcoded-submenu">
                            <?php if ((in_array('Tender Request', $permissions)) || (in_array('All', $permissions)) || (in_array('View Tenders', $permissions))) {
                                echo "<li><a href='tender-request2.php'>Tender Request</a></li>";
                            }
                            if ((in_array('Sent Tender', $permissions)) || (in_array('All', $permissions))) {
                                echo " <li><a href='sent-tender2.php'> Sent Tender</a></li>";
                            }
                            if ((in_array('Alot Tender', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='alot-tender.php'> Alot Tender</a></li>";
                            }
                            if ((in_array('Award Tender', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='award-tender.php'> Award Tender</a></li>";
                            } 
                            if ((in_array('Award Tender', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='all-tender-request.php'>View All Tenders</a></li>";
                            } 
                            ?>

                        </ul>
                    </li>
                <?php } ?>


                <?php if (
                    (in_array('Recycle Bin', $permissions)) || (in_array('All', $permissions))
                ) {
                    ?>
                    <li class="nav-item">
                        <a href="recyclebin.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-trash-2"></i></span><span class="">Recycle Bin</span></a>
                    </li>

                <?php } ?>
                <?php if (
                    (in_array('Registered Users', $permissions)) || (in_array('All', $permissions))
                ) {
                    ?>
                    <li class="nav-item">
                        <a href="registered-users.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-globe"></i></span><span class="">Registered Users</span></a>
                    </li>

                <?php } ?>

                <?php if (
                    (in_array('Add Banner', $permissions)) || (in_array('Manage Banner', $permissions))
                    || (in_array('Add Content', $permissions)) || (in_array('Manage Content', $permissions))
                    || (in_array('All', $permissions))
                ) {
                    ?>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-credit-card"></i></span><span class="pcoded-mtext">Website
                            </span></a>
                        <ul class="pcoded-submenu">
                            <?php if ((in_array('Add Banner', $permissions)) || (in_array('All', $permissions))) {
                                echo " <li><a href='add-banner.php'>Add Banner</a></li>";
                            }
                            if ((in_array('Manage Banner', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='manage-banner.php'> Manage Banner</a></li>";
                            }
                            if ((in_array('Add Content', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='add-content.php'>Add Content</a></li>";
                            }
                            if ((in_array('Manage Content', $permissions)) || (in_array('All', $permissions))) {
                                echo "<li><a href='manage-content.php'> Manage Content</a></li>";
                            } ?>
                        </ul>
                    </li>
                <?php } ?>

                <?php if (
                    (in_array('Setting', $permissions)) || (in_array('All', $permissions))
                ) {
                    ?>
                    <li class="nav-item">
                        <a href="configuration.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-server"></i></span><span class="">Setting</span></a>
                    </li>
                <?php } ?>

                <?php if (
                    (in_array('Email Services', $permissions)) || (in_array('All', $permissions))
                ) {
                    ?>
                    <li class="nav-item">
                        <a href="email-services.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-server"></i></span><span class="">Email Services</span></a>
                    </li>
                <?php } ?>

                <li class="nav-item">
                    <a href="changepass.php" class="nav-link"><span class="pcoded-micon"><i
                                class="feather icon-command"></i></span><span class="">Change
                            Password</span></a>
                </li>

                <?php if (
                    (in_array('Logs Report', $permissions)) || (in_array('All', $permissions))
                ) {
                    ?>
                    <li class="nav-item">
                        <a href="reports.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-file-plus"></i></span><span class="">Logs Report</span></a>
                    </li>
                <?php } ?>

                <li class="nav-item logout_btn">
                    <a href="logout.php" class="nav-link "><span class="pcoded-micon logout_btn"><i
                                class="feather icon-power"></i></span><span class="">Log
                            out</span></a>
                </li>

                <li class="nav-item">
                    <a href="javascript:void(0);" class="nav-link "><span class="pcoded-micon"><i
                                class="feather icon-layers"></i></span><span class="">Version 1.2.1</span></a>
                </li>

            </ul>

        </div>
    </div>
</nav>