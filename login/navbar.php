<?php
$adminId = $_SESSION['login_user_id'] ?? null; // Use null coalescing operator for safety

try {
    $roleId = null; // Initialize role ID


    if (isset($adminId)) {
        // Fetch admin role
        $stmtAdmin = $db->prepare("SELECT role_id FROM admin WHERE id = ?");
        $stmtAdmin->bind_param('i', $adminId);
        $stmtAdmin->execute();
        $adminResponse = $stmtAdmin->get_result()->fetch_array(MYSQLI_ASSOC);

        if ($adminResponse) {
            $roleId = $adminResponse['role_id']; // Set role ID for admin
        } else {
            $_SESSION['error'] = "Admin not found for ID: " . $adminId;
            header('Location: index.php');
            exit();
        }
    }
    // else {
    //     // // No session ID found, redirect to login
    //     $_SESSION['error'] = "You must be logged in to access this page.";
    //     header('Location: index.php');
    //     exit();
    // }


    // Fetch role details
    $stmtRolesData = $db->prepare("SELECT * FROM roles WHERE role_id = ?");
    $stmtRolesData->bind_param('i', $roleId);
    $stmtRolesData->execute();
    $roleData = $stmtRolesData->get_result()->fetch_array(MYSQLI_ASSOC);

    if (!$roleData) {
        $_SESSION['error'] = "Role not found for ID: " . $roleId;
        header('Location: index.php');
        exit();
    }

    // Fetch permissions for the role
    $stmtPrivileges = $db->prepare("
        SELECT p.permission_name 
        FROM permissions p
        JOIN role_permissions rp ON p.permission_id = rp.permission_id
        WHERE rp.role_id = ?
    ");
    $stmtPrivileges->bind_param('i', $roleId);
    $stmtPrivileges->execute();
    $privileges = $stmtPrivileges->get_result()->fetch_all(MYSQLI_ASSOC);
    $privileges = array_column($privileges, 'permission_name');

} catch (Exception $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header('Location: index.php');
    exit();
}

// Function to check if admin has a specific permission
function hasPermission($privilege, $privileges, $roleName)
{
    // If role is admin, grant all permissions
    if (strtolower($roleName) === 'admin') {
        return true;
    }
    // For other roles, check specific permissions
    return in_array($privilege, $privileges);
}

// Check if user is admin
$isAdmin = strtolower($roleData['role_name']) === 'admin';
?>


<style>
    .logout_btn:hover .logout_btn {
        color: rgb(255, 255, 255) !important;
        /* Set text color to white */
        background-color: #ff2046 !important;
        /* Set background color */
    }
</style>

<nav class="pcoded-navbar menupos-fixed menu-light ">
    <div class="navbar-wrapper  ">
        <div class="navbar-content scroll-div ">
            <ul class="nav pcoded-inner-navbar ">
                <li class="nav-item pcoded-menu-caption">
                    <label></label>
                </li>
                <?php if ($isAdmin || hasPermission('Dashboard', $privileges, $roleData['role_name'])): ?>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link " style="background:#33cc33; color:#fff;"><span
                                class="pcoded-micon"><i class="feather icon-home"></i></span><span
                                class="">Dashboard</span></a>
                    </li>
                <?php endif; ?>


                <?php if ($isAdmin || hasPermission('Section', $privileges, $roleData['role_name'])): ?>
                    <!-- Section Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0);" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-file"></i></span><span class="pcoded-mtext">Section</span></a>
                        <ul class="pcoded-submenu">
                            <?php if ($isAdmin || hasPermission('Add Section', $privileges, $roleData['role_name'])): ?>
                                <li><a href='add-section.php'>Add Section</a></li>
                            <?php endif; ?>
                            <?php if ($isAdmin || hasPermission('Manage Section', $privileges, $roleData['role_name'])): ?>
                                <li><a href='view-section.php'>Manage Section</a></li>
                            <?php endif; ?>
                            <?php if ($isAdmin || hasPermission('Add Division', $privileges, $roleData['role_name'])): ?>
                                <li><a href='add-division.php'>Add Division</a></li>
                            <?php endif; ?>

                            <?php if ($isAdmin || hasPermission('Manage Division', $privileges, $roleData['role_name'])): ?>
                                <li><a href='view-division.php'>Manage Division</a></li>
                            <?php endif; ?>

                            <?php if ($isAdmin || hasPermission('Add Sub Division', $privileges, $roleData['role_name'])): ?>
                                <li><a href='add-sub-section.php'>Add Sub Division</a></li>
                            <?php endif; ?>

                            <?php if ($isAdmin || hasPermission('Manage Sub Division', $privileges, $roleData['role_name'])): ?>
                                <li><a href='view-sub-section.php'>Manage Sub Division</a></li>
                            <?php endif; ?>

                            <?php if ($isAdmin || hasPermission('Section Tree', $privileges, $roleData['role_name'])): ?>
                                <li><a href='view-section-tree.php'>Section Tree</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Category', $privileges, $roleData['role_name'])): ?>
                    <!-- Category Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0);" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-edit"></i></span><span class="pcoded-mtext">Category</span></a>
                        <ul class="pcoded-submenu">
                            <?php if ($isAdmin || hasPermission('Add Category', $privileges, $roleData['role_name'])): ?>
                                <li><a href='add-category.php'>Add Category</a></li>
                            <?php endif; ?>
                            <?php if ($isAdmin || hasPermission('Manage Category', $privileges, $roleData['role_name'])): ?>
                                <li><a href='view-category.php'>Manage Category</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Brands', $privileges, $roleData['role_name'])): ?>
                    <!-- Brands Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0);" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-file"></i></span><span class="pcoded-mtext">Brands</span></a>
                        <ul class="pcoded-submenu">

                            <?php if ($isAdmin || hasPermission('Add Brands', $privileges, $roleData['role_name'])): ?>
                                <li><a href='add-brand.php'>Add Brands</a></li>
                            <?php endif; ?>
                            <?php if ($isAdmin || hasPermission('Manage Brands', $privileges, $roleData['role_name'])): ?>
                                <li><a href='manage-brand.php'>Manage Brands</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Departments', $privileges, $roleData['role_name'])): ?>
                    <!-- Departments Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0);" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-home"></i></span><span class="pcoded-mtext">Departments</span></a>
                        <ul class="pcoded-submenu">
                            <?php if ($isAdmin || hasPermission('Add Department', $privileges, $roleData['role_name'])): ?>
                                <li><a href='add-department.php'>Add Department</a></li>
                            <?php endif; ?>
                            <?php if ($isAdmin || hasPermission('Manage Departments', $privileges, $roleData['role_name'])): ?>
                                <li><a href='manage-department.php'>Manage Departments</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>


                <?php if ($isAdmin || hasPermission('Price', $privileges, $roleData['role_name'])): ?>
                    <!-- Price List Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0);" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-credit-card"></i></span><span class="pcoded-mtext">Price
                                List</span></a>
                        <ul class="pcoded-submenu">
                            <?php if ($isAdmin || hasPermission('Add Price', $privileges, $roleData['role_name'])): ?>
                                <li><a href='add-price.php'>Add Price List</a></li>
                            <?php endif; ?>

                            <?php if ($isAdmin || hasPermission('Manage Price', $privileges, $roleData['role_name'])): ?>
                                <li><a href='manage-price.php'>Manage Price List</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Locations', $privileges, $roleData['role_name'])): ?>
                    <!-- Location List Menu -->
                    <li class="nav-item pcoded-hasmenu location-menu">
                        <a href="javascript:void(0);" class="nav-link">
                            <span class="pcoded-micon"><i class="feather icon-map-pin"></i></span>
                            <span class="pcoded-mtext">Location</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <?php if ($isAdmin || hasPermission('State', $privileges, $roleData['role_name'])): ?>
                                <li><a href='manage-state.php'>State</a></li>
                            <?php endif; ?>

                            <?php if ($isAdmin || hasPermission('City', $privileges, $roleData['role_name'])): ?>
                                <li><a href='manage-city.php'>City</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>


                <?php if ($isAdmin || hasPermission('User Management', $privileges, $roleData['role_name'])): ?>
                    <!-- Staff User Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0);" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-users"></i></span><span class="pcoded-mtext">User
                                Management</span></a>
                        <ul class="pcoded-submenu">
                            <?php if ($isAdmin || hasPermission('User Management', $privileges, $roleData['role_name'])): ?>
                                <li><a href='manage-permissions.php'>Manage Permissions </a></li>
                            <?php endif; ?>

                            <?php if ($isAdmin || hasPermission('Manage Roles', $privileges, $roleData['role_name'])): ?>
                                <li><a href='manage-roles.php'>Manage Roles </a></li>
                            <?php endif; ?>
                            <?php if ($isAdmin || hasPermission('Manage Roles', $privileges, $roleData['role_name'])): ?>
                                <li><a href='view-user.php'>Manage Staff Users</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>


                <?php if ($isAdmin || hasPermission('Tenders', $privileges, $roleData['role_name'])): ?>
                    <!-- Tenders Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0);" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-briefcase"></i></span><span class="pcoded-mtext">Tenders
                            </span></a>
                        <ul class="pcoded-submenu">
                            <?php if ($isAdmin || hasPermission('Tender Requests', $privileges, $roleData['role_name'])): ?>
                                <li><a href='tender-request2.php'>Tender Requests</a></li>
                            <?php endif; ?>

                            <?php if ($isAdmin || hasPermission('Sent Tenders', $privileges, $roleData['role_name'])): ?>
                                <li><a href='sent-tender2.php'> Sent Tenders</a></li>
                            <?php endif; ?>

                            <?php if ($isAdmin || hasPermission('Alot Tenders', $privileges, $roleData['role_name'])): ?>
                                <li><a href='alot-tender.php'> Alot Tenders</a></li>
                            <?php endif; ?>
                            <?php if ($isAdmin || hasPermission('Award Tenders', $privileges, $roleData['role_name'])): ?>
                                <li><a href='award-tender.php'> Award Tenders</a></li>
                            <?php endif; ?>
                            <?php if ($isAdmin || hasPermission('View All Tenders', $privileges, $roleData['role_name'])): ?>
                                <li><a href='all-tender-request.php'>View All Tenders</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>


                <?php if ($isAdmin || hasPermission('Recycle Bin', $privileges, $roleData['role_name'])): ?>
                    <!-- Recycle Bin -->
                    <li class="nav-item">
                        <a href="recyclebin.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-trash-2"></i></span><span class="">Recycle Bin</span></a>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Registered Users', $privileges, $roleData['role_name'])): ?>
                    <!-- Registered Users -->
                    <li class="nav-item">
                        <a href="registered-users.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-globe"></i></span><span class="">Registered Users</span></a>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Website', $privileges, $roleData['role_name'])): ?>
                    <!-- Website Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0);" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-credit-card"></i></span><span class="pcoded-mtext">Website
                            </span></a>
                        <ul class="pcoded-submenu">
                            <?php if ($isAdmin || hasPermission('Add Banner', $privileges, $roleData['role_name'])): ?>
                                <li><a href='add-banner.php'>Add Banner</a></li>
                            <?php endif; ?>

                            <?php if ($isAdmin || hasPermission('Manage Banner', $privileges, $roleData['role_name'])): ?>
                                <li><a href='manage-banner.php'> Manage Banner</a></li>
                            <?php endif; ?>

                            <?php if ($isAdmin || hasPermission('Add Content', $privileges, $roleData['role_name'])): ?>
                                <li><a href='add-content.php'>Add Content</a></li>
                            <?php endif; ?>

                            <?php if ($isAdmin || hasPermission('Manage Content', $privileges, $roleData['role_name'])): ?>
                                <li><a href='manage-content.php'> Manage Content</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Settings', $privileges, $roleData['role_name'])): ?>
                    <!-- Setting -->
                    <li class="nav-item">
                        <a href="configuration.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-server"></i></span><span class="">Settings</span></a>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Email Services', $privileges, $roleData['role_name'])): ?>
                    <!-- Email Services -->
                    <li class="nav-item">
                        <a href="email-services.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-server"></i></span><span class="">Email Services</span></a>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Change Password', $privileges, $roleData['role_name'])): ?>
                    <li class="nav-item">
                        <a href="changepass.php" class="nav-link"><span class="pcoded-micon"><i
                                    class="feather icon-command"></i></span><span class="">Change
                                Password</span></a>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Logs Report', $privileges, $roleData['role_name'])): ?>
                    <!-- Logs Report -->
                    <li class="nav-item">
                        <a href="reports.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-file-plus"></i></span><span class="">Logs Report</span></a>
                    </li>
                <?php endif; ?>

                <li class="nav-item logout_btn">
                    <a href="logout.php" class="nav-link "><span class="pcoded-micon logout_btn"><i
                                class="feather icon-power"></i></span><span class="">Log
                            out</span></a>
                </li>

                <li class="nav-item">
                    <a href="javascript:void(0);" class="nav-link "><span class="pcoded-micon"><i
                                class="feather icon-layers"></i></span><span class="">Version 1.4.5</span></a>
                </li>

            </ul>

        </div>
    </div>
</nav>