<?php

session_start();
include("db/config.php");
if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}

$roleId = null;
$role = [];
$name = $_SESSION['login_user'];

try {
    $stmtPermissions = $db->prepare("SELECT * FROM permissions WHERE status = 1");
    $stmtPermissions->execute();
    $permissionsData = $stmtPermissions->get_result()->fetch_all(MYSQLI_ASSOC);

    // Handle GET request to fetch roleId
    if (isset($_GET['id'])) {
        $encryptedId = $_GET['id'];
        if ($encryptedId === null || $encryptedId === false) {
            throw new Exception("Invalid role ID provided");
        }
        $roleId = base64_decode($encryptedId);
        if ($roleId === false) {
            throw new Exception("Failed to decode role ID");
        }
        $roleId = (int) $roleId; // Ensure roleId is an integer


        $stmtRole = $db->prepare("SELECT role_name FROM roles WHERE role_status = 1 AND role_id = ?");
        $stmtRole->bind_param('i', $roleId);
        $stmtRole->execute();
        $role = $stmtRole->get_result()->fetch_array(MYSQLI_ASSOC);

        $stmtRolePermission = $db->prepare("SELECT permissions.*, role_permissions.role_permission_id 
            FROM role_permissions
            INNER JOIN permissions ON role_permissions.permission_id = permissions.permission_id
            WHERE role_permissions.role_id = ? ");
        $stmtRolePermission->bind_param('i', $roleId);
        $stmtRolePermission->execute();
        $assignedPermissions = $stmtRolePermission->get_result()->fetch_all(MYSQLI_ASSOC);
    }


} catch (\Throwable $th) {
    $_SESSION['error'] = $th->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulkDeletePermissionsIds'])) {


    $permissionsIds = $_POST['bulkDeletePermissionsIds'];

    // Validate: Must be an array of integers
    if (!is_array($permissionsIds)) {
        echo json_encode([
            'status' => 400,
            'message' => 'Invalid data format.'
        ]);
        exit;
    }

    try {
        // Prepare the SQL dynamically
        $placeholders = implode(',', array_fill(0, count($permissionsIds), '?'));
        $types = str_repeat('i', count($permissionsIds)); // All integers

        $stmt = $db->prepare("DELETE FROM role_permissions WHERE role_permission_id IN ($placeholders)");
        $stmt->bind_param($types, ...$permissionsIds);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 200,
                'message' => 'Selected records deleted successfully.',
                'deleted_ids' => $permissionsIds
            ]);
        } else {
            echo json_encode([
                'status' => 400,
                'message' => $stmt->error
            ]);
        }

        exit;
    } catch (Exception $e) {
        echo json_encode([
            'status' => 500,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['assignPermissionIds'])) {
    try {

        $assignPermissionIds = isset($_POST['assignPermissionIds']) && is_array($_POST['assignPermissionIds'])
            ? array_map('intval', $_POST['assignPermissionIds'])
            : [];


        $roleId = base64_decode($_GET['id']) ?? null;

        if (!$roleId) {
            throw new Exception("Role ID is required");
        }

        if (empty($assignPermissionIds)) {
            throw new Exception("No permissions selected");
        }

        $db->begin_transaction();
        $stmtRolePermission = $db->prepare("
            INSERT INTO role_permissions (role_id, permission_id) 
            VALUES (?, ?)
        ");
        foreach ($assignPermissionIds as $assignPermissionId) {
            $stmtRolePermission->bind_param('ii', $roleId, $assignPermissionId);
            $stmtRolePermission->execute();
        }

        $db->commit(); // Commit the transaction

        echo json_encode([
            "status" => 200,
            "message" => "Permission Assigned successfully",
        ]);
        exit;

    } catch (\Throwable $th) {
        $db->rollback(); // Rollback on error
        echo json_encode([
            "status" => 500,
            "error" => "Database error: " . $th->getMessage(),
        ]);
        exit;
    }
}



if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['deleteRolePermissionId'])) {
    try {
        $deleteRolePermissionId = $_POST['deleteRolePermissionId'];


        $db->begin_transaction();

        $stmtRolePermission = $db->prepare("DELETE FROM role_permissions WHERE role_permission_id = ?");
        $stmtRolePermission->bind_param('i', $deleteRolePermissionId);
        $stmtRolePermission->execute();

        $db->commit(); // Commit the transaction

        echo json_encode([
            "status" => 200,
            "message" => "Assign Permission deleted successfully",
        ]);
        exit;

    } catch (\Throwable $th) {
        $db->rollback(); // Rollback on error
        echo json_encode([
            "status" => 500,
            "error" => "Database error: " . $th->getMessage(),
        ]);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Assign Permissions for <?= htmlspecialchars($role['role_name'] ?? 'Role') ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="Codedthemes" />
    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">



    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
            color: black !important;
        }
    </style>
</head>

<body class="">

    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>


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
                                <h5 class="m-b-10">Assign Permissions for
                                    <?= htmlspecialchars($role['role_name'] ?? 'Role') ?>
                                </h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="manage-roles.php">Roles</a></li>
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Assign Permission</a></li>
                            </ul>

                        </div>
                    </div>
                </div>
            </div>


            <div class="row">

                <div class="col-sm-12">
                    <div class="card">


                        <div class="card-header table-card-header">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-end">
                                        <a class="btn btn-primary rounded-sm" href="javascript:void(0);"
                                            data-bs-toggle="modal" data-bs-target="#assign-permission-model"
                                            title="Create Role" href="javascript:void(0);">Assign Permission</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="dt-responsive table-responsive">

                                <div class='col-md-4 col-l-4'>
                                    <a href='#' id='delete_records' class='btn btn-danger btn-sm'>
                                        <i class='feather icon-trash'></i> &nbsp; Delete Permissions
                                    </a>
                                </div>
                                <br />

                                <table id="basic-btn2" class="table table-striped table-bordered nowrap">
                                    <thead>
                                        <tr>
                                            <th>
                                                <label class="checkboxs">
                                                    <input type="checkbox" id="select-all">
                                                    <span class="checkmarks"></span>
                                                </label>
                                                &nbsp;SNO
                                            </th>
                                            <th>Permission Name</th>
                                            <th>Created At</th>
                                            <th>Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 1;
                                        foreach ($assignedPermissions as $key => $value) {
                                            ?>
                                            <tr class='record'>
                                                <td>
                                                    <div class='custom-control custom-checkbox'>
                                                        <input type='checkbox' class='custom-control-input member_checkbox'
                                                            id='customCheck<?php echo $count; ?>'
                                                            data-role-permission-id='<?php echo $value['role_permission_id']; ?>'>
                                                        <label class='custom-control-label'
                                                            for='customCheck<?php echo $count; ?>'>
                                                            <?php echo $count; ?>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($value['permission_name']); ?></td>
                                                <td><?php echo $value['created_at']; ?></td>

                                                <td>
                                                    <a href='javascript:void(0);'
                                                        data-role-permission-id="<?php echo $value['role_permission_id']; ?>"
                                                        class='delbutton btn btn-danger' title='Click To Delete'>
                                                        <i class='feather icon-trash'></i> &nbsp; Delete
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
                                            $count++;
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                    </tfoot>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="modal fade" id="assign-permission-model" tabindex="-1" aria-labelledby="editUnitsLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUnitsLabel">Assign Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="assign-permission-form">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-md-12 mb-3">
                                <label for="editReferenceCode" class="form-label">Permission <span
                                        class="text-danger">*</span></label>
                                <div class="input-group ">
                                    <select class="form-select" name="permission-ids[]" id="permission-ids" multiple>
                                        <option>Select</option>
                                        <?php foreach ($permissionsData as $key => $value) { ?>
                                            <option value="<?= $value['permission_id'] ?>"><?= $value['permission_name'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit-permission-model" tabindex="-1" aria-labelledby="editUnitsLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUnitsLabel">Edit Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="edit-permission-form">
                    <input type="hidden" name="edit-permission-id" id="edit-permission-id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-md-12 mb-3">
                                <label for="editReferenceCode" class="form-label">Permission Name <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="edit-permission-name"
                                        name="edit-permission-name" placeholder="Enter Permission Name">
                                </div>

                            </div>
                            <div class="col-12 col-md-12 mb-3">
                                <label for="" class="form-label">Role <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select" name="edit-permission-status"
                                        id="edit-permission-status">
                                        <option>Select</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 (must come AFTER jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script>
        $(document).ready(function () {
            $("#updateuser").delay(5000).slideUp(300);
        });
    </script>



    <script type="text/javascript">
        $(document).ready(function () {

            $('#permission-ids').select2({
                placeholder: "Select Permission",
                dropdownParent: $('#assign-permission-model'), // Attach dropdown to the modal
                width: "100%"
            });

            $(document).on("submit", ".assign-permission-form", function (e) {
                e.preventDefault();

                // Get values correctly using the name attributes
                let permissionIds = $("select[name='permission-ids[]']").val();

                // Your AJAX submission logic here
                $.ajax({
                    url: window.location.href, // Change to your actual endpoint
                    method: 'POST',
                    data: {
                        assignPermissionIds: permissionIds
                    },

                    success: function (response) {
                        $('#assign-permission-model').modal('hide');

                        let result = JSON.parse(response);
                        if (result.status == 200) {

                            // Show success message
                            Swal.fire({
                                title: 'Permission Assigned!',
                                text: result.message,
                                icon: 'success',
                                confirmButtonColor: "#33cc33",
                                timer: 1000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });

                        } else {
                            // Show error message
                            Swal.fire({
                                title: 'Error!',
                                text: result.error || 'Something went wrong',
                                icon: 'error',
                                confirmButtonColor: "#dc3545",
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                        }

                        console.log(response);

                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                        // Show error message
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to update reference code',
                            icon: 'error',
                            confirmButtonColor: "#dc3545",
                            timer: 1500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    }
                });
            });



            $(document).on("click", ".edit-permission-button", async function (e) {
                let permissionId = $(this).data("permission-id");
                let permissionName = $(this).data("permission-name");
                let permissionStatus = $(this).data("permission-status");

                $("#edit-permission-id").val(permissionId);
                $("#edit-permission-name").val(permissionName);
                $("#edit-permission-status").val(permissionStatus);
            })


            $(document).on("submit", ".edit-permission-form", function (e) {
                e.preventDefault();

                // Get values correctly using the name attributes
                let permissionId = $("input[name='edit-permission-id']").val();
                let permissionName = $("input[name='edit-permission-name']").val();
                let permissionStatus = $("select[name='edit-permission-status']").val();


                // Your AJAX submission logic here
                $.ajax({
                    url: window.location.href, // Change to your actual endpoint
                    method: 'POST',
                    data: {
                        editPermissionId: permissionId,
                        editPermissionName: permissionName,
                        editPermissionStatus: permissionStatus
                    },

                    success: function (response) {
                        $('#edit-permission-model').modal('hide');

                        let result = JSON.parse(response);
                        if (result.status == 200) {

                            // Show success message
                            Swal.fire({
                                title: 'Permission Updated!',
                                text: result.message,
                                icon: 'success',
                                confirmButtonColor: "#33cc33",
                                timer: 1000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                // Reload page after animation
                                window.location.reload();
                            });

                        } else {
                            // Show error message
                            Swal.fire({
                                title: 'Error!',
                                text: result.error || 'Something went wrong',
                                icon: 'error',
                                confirmButtonColor: "#dc3545",
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                        }

                        console.log(response);

                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                        // Show error message
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to update reference code',
                            icon: 'error',
                            confirmButtonColor: "#dc3545",
                            timer: 1500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    }
                });
            });



            $(document).on("click", ".delbutton", function (e) {
                e.preventDefault();

                let rolePermissionId = $(this).data("role-permission-id");



                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this Record!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#33cc33",
                    cancelButtonColor: "#ff5471",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: window.location.href, // Change to your actual endpoint
                            method: 'POST',
                            data: {
                                deleteRolePermissionId: rolePermissionId,
                            },
                            success: function () {
                                // Show success message
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'The record has been deleted.',
                                    icon: 'success',
                                    confirmButtonColor: "#33cc33",
                                    timer: 1500,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Animate and remove the record
                                    $(".record").animate({
                                        backgroundColor: "#FF3"
                                    }, "fast")
                                        .animate({
                                            opacity: "hide"
                                        }, "slow");

                                    // Reload page after animation
                                    setTimeout(function () {
                                        window.location.reload();
                                    }, 2000);
                                });
                            },
                            error: function (error) {
                                console.log(error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Something went wrong while deleting the record.',
                                    icon: 'error',
                                    confirmButtonColor: "#33cc33"
                                });
                            }
                        });
                    }
                });

                return false;
            });




            $(document).on('change', '#select-all', function (e) {
                var isChecked = $(this).prop('checked');

                // Select/Deselect all checkboxes with class 'member_checkbox'
                $('.member_checkbox').prop('checked', isChecked);

                // Stop propagation
                e.stopPropagation();
            });

            // Prevent sorting when clicking on checkbox area in header
            $('.checkboxs').on('click', function (e) {
                e.stopPropagation();
            });

            // Handle individual checkbox clicks to update select-all state
            $(document).on('click', '.member_checkbox', function () {
                updateSelectAllState();
            });

            // Function to update select-all checkbox state
            function updateSelectAllState() {
                var totalCheckboxes = $('.member_checkbox').length;
                var checkedCheckboxes = $('.member_checkbox:checked').length;

                // Update select all checkbox state
                $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
            }

            $(document).on('click', '#delete_records', function (e) {
                e.preventDefault();

                let assignPermissions = [];
                $(".member_checkbox:checked").each(function () {
                    assignPermissions.push($(this).data('role-permission-id'));
                });

                if (assignPermissions.length == 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Please select record!",
                        confirmButtonColor: "#33cc33"
                    });
                    return;
                }


                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    showCancelButton: true,
                    confirmButtonColor: "#33cc33",
                    cancelButtonColor: "#ff5471",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: window.location.href,
                            type: "post",
                            data: { bulkDeletePermissionsIds: assignPermissions },
                            success: function (response) {
                                let result = JSON.parse(response);

                                if (result.status == 200) {
                                    Swal.fire(
                                        'Deleted!',
                                        result.message,
                                        'success'
                                    ).then(() => {
                                        // Reload the page
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Deleted!',
                                        result.message,
                                        'error'
                                    ).then(() => {
                                        // Reload the page
                                        location.reload();
                                    });
                                }


                            },
                            error: function (error) {
                                console.log(error);
                            },
                        });

                    }
                })
            });


            // Initialize the DataTable with buttons
            var table = $('#basic-btn2').DataTable({
                pageLength: 100,
                lengthMenu: [25, 50, 100, 200, 500, 1000], // Custom dropdown options
                responsive: true,
                ordering: true,
                searching: true
            });

            // Fetch the number of entries
            var info = table.page.info();
            var totalEntries = info.recordsTotal;

            $('#new').text(totalEntries);
        });
    </script>

</body>

</html>