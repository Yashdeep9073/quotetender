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
    } else {
        // // No session ID found, redirect to login
        $_SESSION['error'] = "You must be logged in to access this page.";
        header('Location: index.php');
        exit();
    }


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
                    <label>Navigation</label>
                </li>
                <?php if ($isAdmin || hasPermission('Dashboard', $privileges, $roleData['role_name'])): ?>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link " style="background:#33cc33; color:#fff;"><span
                                class="pcoded-micon"><i class="feather icon-home"></i></span><span
                                class="">Dashboard</span></a>
                    </li>
                <?php endif; ?>


                <?php if ($isAdmin || hasPermission('Section', $privileges, $roleData['0']['role_name'])): ?>
                    <!-- Section Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-file"></i></span><span class="pcoded-mtext">Section</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href='add-section.php'>Add Section</a></li>
                            <li><a href='view-section.php'>Manage Section</a></li>
                            <li><a href='add-division.php'>Add Division</a></li>
                            <li><a href='view-division.php'>Manage Division</a></li>
                            <li><a href='add-sub-section.php'>Sub Division</a></li>
                            <li><a href='view-sub-section.php'>Manage Sub Section</a></li>
                            <li><a href='view-section-tree.php'>Section Tree</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Category', $privileges, $roleData['0']['role_name'])): ?>
                    <!-- Category Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-edit"></i></span><span class="pcoded-mtext">Category</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href='add-category.php'>Add Category</a></li>
                            <li><a href='view-category.php'>Manage Category</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Brands', $privileges, $roleData['0']['role_name'])): ?>
                    <!-- Brands Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-file"></i></span><span class="pcoded-mtext">Brands</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href='add-brand.php'>Add Brands</a></li>
                            <li><a href='manage-brand.php'>Manage Brands</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Departments', $privileges, $roleData['0']['role_name'])): ?>
                    <!-- Departments Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-home"></i></span><span class="pcoded-mtext">Departments</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href='add-department.php'>Add Departments</a></li>
                            <li><a href='manage-department.php'>Manage Departments</a></li>
                        </ul>
                    </li>
                <?php endif; ?>


                <?php if ($isAdmin || hasPermission('Price', $privileges, $roleData['0']['role_name'])): ?>
                    <!-- Price List Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-credit-card"></i></span><span class="pcoded-mtext">Price
                                List</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href='add-price.php'>Add Price List</a></li>
                            <li><a href='manage-price.php'>Manage Price List</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Staff User Management', $privileges, $roleData['0']['role_name'])): ?>
                    <!-- Staff User Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-users"></i></span><span class="pcoded-mtext">Staff User
                                Management</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href='manage-permissions.php'>Manage Permissions </a></li>
                            <li><a href='manage-roles.php'>Manage Roles </a></li>
                            <li><a href='view-user.php'>Manage Staff User</a></li>
                        </ul>
                    </li>
                <?php endif; ?>


                <?php if ($isAdmin || hasPermission('Tenders', $privileges, $roleData['0']['role_name'])): ?>
                    <!-- Tenders Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-briefcase"></i></span><span class="pcoded-mtext">Tenders
                            </span></a>
                        <ul class="pcoded-submenu">
                            <li><a href='tender-request2.php'>Tender Request</a></li>
                            <li><a href='sent-tender2.php'> Sent Tender</a></li>
                            <li><a href='alot-tender.php'> Alot Tender</a></li>
                            <li><a href='award-tender.php'> Award Tender</a></li>
                            <li><a href='all-tender-request.php'>View All Tenders</a></li>
                        </ul>
                    </li>
                <?php endif; ?>


                <?php if ($isAdmin || hasPermission('Recycle Bin', $privileges, $roleData['0']['role_name'])): ?>
                    <!-- Recycle Bin -->
                    <li class="nav-item">
                        <a href="recyclebin.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-trash-2"></i></span><span class="">Recycle Bin</span></a>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Registered Users', $privileges, $roleData['0']['role_name'])): ?>
                    <!-- Registered Users -->
                    <li class="nav-item">
                        <a href="registered-users.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-globe"></i></span><span class="">Registered Users</span></a>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Website', $privileges, $roleData['0']['role_name'])): ?>
                    <!-- Website Menu -->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="#!" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-credit-card"></i></span><span class="pcoded-mtext">Website
                            </span></a>
                        <ul class="pcoded-submenu">
                            <li><a href='add-banner.php'>Add Banner</a></li>
                            <li><a href='manage-banner.php'> Manage Banner</a></li>
                            <li><a href='add-content.php'>Add Content</a></li>
                            <li><a href='manage-content.php'> Manage Content</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Settings', $privileges, $roleData['0']['role_name'])): ?>
                    <!-- Setting -->
                    <li class="nav-item">
                        <a href="configuration.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-server"></i></span><span class="">Settings</span></a>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Email Services', $privileges, $roleData['0']['role_name'])): ?>
                    <!-- Email Services -->
                    <li class="nav-item">
                        <a href="email-services.php" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-server"></i></span><span class="">Email Services</span></a>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Change Password', $privileges, $roleData['0']['role_name'])): ?>
                    <li class="nav-item">
                        <a href="changepass.php" class="nav-link"><span class="pcoded-micon"><i
                                    class="feather icon-command"></i></span><span class="">Change
                                Password</span></a>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin || hasPermission('Logs Report', $privileges, $roleData['0']['role_name'])): ?>
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