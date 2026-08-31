<?php
require_once __DIR__ . '/inc/init.php';

if (!$taskCanEdit) {
    task_redirect('index.php', 'danger', 'You do not have permission to edit tasks.');
    return;
}

$taskId = task_get_int(isset($_GET['id']) ? $_GET['id'] : null);
if ($taskId === false) {
    task_redirect('index.php', 'danger', 'Invalid task ID.');
}

$task = task_load($db, $taskId);
if (!$task) {
    task_redirect('index.php', 'danger', 'Task not found.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'title'             => isset($_POST['title']) ? trim((string) $_POST['title']) : '',
        'description'       => isset($_POST['description']) ? trim((string) $_POST['description']) : '',
        'task_type'         => isset($_POST['task_type']) ? (string) $_POST['task_type'] : 'General',
        'tender_request_id' => isset($_POST['tender_request_id']) ? (string) $_POST['tender_request_id'] : '',
        'tender_label'      => '',
        'assigned_to'       => isset($_POST['assigned_to']) ? (string) $_POST['assigned_to'] : '',
        'priority'          => isset($_POST['priority']) ? (string) $_POST['priority'] : '',
        'status'            => isset($_POST['status']) ? (string) $_POST['status'] : '',
        'start_date'        => isset($_POST['start_date']) ? (string) $_POST['start_date'] : '',
        'due_date'          => isset($_POST['due_date']) ? (string) $_POST['due_date'] : '',
    ];

    // ----- Validation (same rules as create) -----
    if ($form['title'] === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($form['title']) > 255) {
        $errors[] = 'Title must be at most 255 characters.';
    }

    if (!in_array($form['task_type'], task_types(), true)) {
        $errors[] = 'Invalid task type.';
    }

    $assignedTo = task_get_int($form['assigned_to']);
    if ($assignedTo === false) {
        $errors[] = 'Please select a valid employee.';
    } else {
        $stmtEmp = $db->prepare("SELECT id FROM admin WHERE id = ? AND status = 1");
        $stmtEmp->bind_param('i', $assignedTo);
        $stmtEmp->execute();
        if (!$stmtEmp->get_result()->fetch_assoc()) {
            $errors[] = 'The selected employee does not exist or is inactive.';
        }
    }

    if (!in_array($form['priority'], task_priorities(), true)) {
        $errors[] = 'Invalid priority.';
    }

    if (!in_array($form['status'], task_statuses(), true)) {
        $errors[] = 'Invalid status.';
    }

    $startDate = task_valid_date($form['start_date']);
    if ($startDate === false) {
        $errors[] = 'Start date must be a valid date (YYYY-MM-DD).';
    }
    $dueDate = task_valid_date($form['due_date']);
    if ($dueDate === false) {
        $errors[] = 'Due date must be a valid date (YYYY-MM-DD).';
    }
    if ($startDate !== null && $dueDate !== null && $startDate > $dueDate) {
        $errors[] = 'Due date cannot be earlier than start date.';
    }

    $tenderRequestId = null;
    if ($form['task_type'] === 'Tender/Query') {
        $tenderRequestId = task_get_int($form['tender_request_id']);
        if ($tenderRequestId === false) {
            $errors[] = 'Please select a valid tender/query for a Tender/Query task.';
        } else {
            $stmtTender = $db->prepare(
                "SELECT id, tenderID, reference_code, status FROM user_tender_requests WHERE id = ? AND delete_tender = '0'"
            );
            $stmtTender->bind_param('i', $tenderRequestId);
            $stmtTender->execute();
            $tenderRow = $stmtTender->get_result()->fetch_assoc();
            if (!$tenderRow) {
                $errors[] = 'The selected tender/query does not exist.';
            } else {
                $form['tender_label'] = implode(' | ', array_filter([
                    $tenderRow['tenderID'],
                    $tenderRow['reference_code'],
                    $tenderRow['status'],
                ]));
            }
        }
    }

    if (empty($errors)) {
        // Username of the new assignee (for history values)
        $newAssignedUsername = null;
        $stmtName = $db->prepare("SELECT username FROM admin WHERE id = ?");
        $stmtName->bind_param('i', $assignedTo);
        $stmtName->execute();
        $rowName = $stmtName->get_result()->fetch_assoc();
        if ($rowName) {
            $newAssignedUsername = $rowName['username'];
        }

        $stmtUpdate = $db->prepare(
            "UPDATE tasks
                SET title = ?, description = ?, task_type = ?, tender_request_id = ?,
                    assigned_to = ?, priority = ?, status = ?, start_date = ?, due_date = ?
              WHERE id = ?"
        );
        $stmtUpdate->bind_param(
            'sssiissssi',
            $form['title'],
            $form['description'],
            $form['task_type'],
            $tenderRequestId,
            $assignedTo,
            $form['priority'],
            $form['status'],
            $startDate,
            $dueDate,
            $taskId
        );

        if ($stmtUpdate->execute()) {
            // ----- Record meaningful changes in task history -----
            $old = $task; // loaded before update
            if ($form['title'] !== (string) $old['title']) {
                task_log_history($db, $taskId, $taskUserId, 'Title changed', (string) $old['title'], $form['title']);
            }
            if ($form['description'] !== (string) ($old['description'] ?? '')) {
                task_log_history($db, $taskId, $taskUserId, 'Description changed');
            }
            if ($form['task_type'] !== (string) $old['task_type']) {
                task_log_history($db, $taskId, $taskUserId, 'Task type changed', (string) $old['task_type'], $form['task_type']);
            }
            $oldTenderId = $old['tender_request_id'] !== null ? (int) $old['tender_request_id'] : null;
            if ($oldTenderId !== $tenderRequestId) {
                task_log_history(
                    $db,
                    $taskId,
                    $taskUserId,
                    'Related tender changed',
                    $old['tender_id_number'] !== null ? (string) $old['tender_id_number'] : 'None',
                    isset($tenderRow['tenderID']) ? (string) $tenderRow['tenderID'] : 'None'
                );
            }
            if ((int) $old['assigned_to'] !== $assignedTo) {
                task_log_history($db, $taskId, $taskUserId, 'Assigned to changed', (string) $old['assigned_username'], $newAssignedUsername);
            }
            if ($form['priority'] !== (string) $old['priority']) {
                task_log_history($db, $taskId, $taskUserId, 'Priority changed', (string) $old['priority'], $form['priority']);
            }
            if ($form['status'] !== (string) $old['status']) {
                task_log_history($db, $taskId, $taskUserId, 'Status changed', (string) $old['status'], $form['status']);
            }
            if ($startDate !== $old['start_date']) {
                task_log_history($db, $taskId, $taskUserId, 'Start date changed', (string) $old['start_date'], $startDate);
            }
            if ($dueDate !== $old['due_date']) {
                task_log_history($db, $taskId, $taskUserId, 'Due date changed', (string) $old['due_date'], $dueDate);
            }

            task_redirect('view.php?id=' . $taskId, 'success', 'Task updated successfully.');
        } else {
            $errors[] = 'Failed to update the task. Please try again.';
        }
    }
} else {
    // Initial form values come from the database
    $form = [
        'title'             => (string) $task['title'],
        'description'       => (string) ($task['description'] ?? ''),
        'task_type'         => (string) $task['task_type'],
        'tender_request_id' => $task['tender_request_id'] !== null ? (string) $task['tender_request_id'] : '',
        'tender_label'      => $task['tender_id_number'] !== null
            ? implode(' | ', array_filter([$task['tender_id_number'], $task['tender_reference_code'], $task['tender_status']]))
            : '',
        'assigned_to'       => (string) $task['assigned_to'],
        'priority'          => (string) $task['priority'],
        'status'            => (string) $task['status'],
        'start_date'        => $task['start_date'] !== null ? (string) $task['start_date'] : '',
        'due_date'          => $task['due_date'] !== null ? (string) $task['due_date'] : '',
    ];
}

$employees = [];
$empResult = mysqli_query($db, "SELECT id, username FROM admin ORDER BY username ASC");
if ($empResult) {
    while ($row = mysqli_fetch_assoc($empResult)) {
        $employees[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Task</title>
    <base href="../">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/plugins/select2.min.css">
    <link rel="stylesheet" href="assets/css/plugins/select.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="">
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include '../navbar.php'; ?>

    <header class="navbar pcoded-header navbar-expand-lg navbar-light headerpos-fixed header-blue">
        <div class="m-header">
            <a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>
            <a href="#!" class="b-brand" style="font-size:24px;">ADMIN PANEL</a>
            <a href="#!" class="mob-toggler"><i class="feather icon-more-vertical"></i></a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a href="#!" class="full-screen" onClick="javascript:toggleFullScreen()"><i class="feather icon-maximize"></i></a>
                </li>
            </ul>
        </div>
        <div class="dropdown drp-user">
            <a href="#!" class="dropdown-toggle" data-toggle="dropdown">
                <img src="assets/images/user.png" class="img-radius wid-40" alt="User-Profile-Image">
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-notification">
                <div class="pro-head">
                    <img src="assets/images/user.png" class="img-radius" alt="User-Profile-Image">
                    <span><?php echo e($taskUserName); ?></span>
                    <a href="logout.php" class="dud-logout" title="Logout">
                        <i class="feather icon-log-out"></i>
                    </a>
                </div>
                <ul class="pro-body">
                    <li><a href="logout.php" class="dropdown-item"><i class="feather icon-lock"></i> Log out</a></li>
                </ul>
            </div>
        </div>
    </header>

    <section class="pcoded-main-container">
        <div class="pcoded-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h5 class="m-b-10">Edit Task</h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="task-management/index.php">Tasks</a></li>
                                <li class="breadcrumb-item"><a href="#!">Edit Task #<?php echo (int) $task['id']; ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="font-size:15px;">
                    <strong><i class="feather icon-alert-triangle"></i></strong>
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo e($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Edit Task #<?php echo (int) $task['id']; ?></h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Title</label>
                                            <input type="text" name="title" class="form-control" value="<?php echo e($form['title']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Assigned Employee</label>
                                            <select name="assigned_to" class="form-control" required>
                                                <option value="">Select Employee ▼</option>
                                                <?php foreach ($employees as $emp): ?>
                                                    <option value="<?php echo e($emp['id']); ?>" <?php echo $form['assigned_to'] === (string) $emp['id'] ? 'selected' : ''; ?>>
                                                        <?php echo e($emp['username']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Description</label>
                                            <textarea name="description" class="form-control" rows="4"><?php echo e($form['description']); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Task Type</label>
                                            <div>
                                                <label class="radio-inline mr-3">
                                                    <input type="radio" name="task_type" value="General" class="task-type-radio"
                                                        <?php echo $form['task_type'] === 'General' ? 'checked' : ''; ?>> General Task
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="task_type" value="Tender/Query" class="task-type-radio"
                                                        <?php echo $form['task_type'] === 'Tender/Query' ? 'checked' : ''; ?>> Tender/Query Task
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group" id="tender-select-group" style="<?php echo $form['task_type'] === 'Tender/Query' ? '' : 'display:none;'; ?>">
                                            <label>Related Tender/Query</label>
                                            <select name="tender_request_id" id="tender_request_id" class="form-control" style="width:100%;">
                                                <?php if ($form['tender_request_id'] !== ''): ?>
                                                    <option value="<?php echo e($form['tender_request_id']); ?>" selected>
                                                        <?php echo e($form['tender_label'] !== '' ? $form['tender_label'] : 'Selected tender #' . $form['tender_request_id']); ?>
                                                    </option>
                                                <?php endif; ?>
                                            </select>
                                            <small class="form-text text-muted">Search by tender/query number, reference code, member or firm name.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Priority</label>
                                            <select name="priority" class="form-control" required>
                                                <?php foreach (task_priorities() as $p): ?>
                                                    <option value="<?php echo e($p); ?>" <?php echo $form['priority'] === $p ? 'selected' : ''; ?>><?php echo e($p); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="status" class="form-control">
                                                <?php foreach (task_statuses() as $s): ?>
                                                    <option value="<?php echo e($s); ?>" <?php echo $form['status'] === $s ? 'selected' : ''; ?>><?php echo e($s); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Start Date</label>
                                            <input type="date" name="start_date" class="form-control" value="<?php echo e($form['start_date']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Due Date</label>
                                            <input type="date" name="due_date" class="form-control" value="<?php echo e($form['due_date']); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12 text-right">
                                        <a href="task-management/view.php?id=<?php echo (int) $task['id']; ?>" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-warning">Update Task</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/plugins/select2.full.min.js"></script>
    <script src="assets/js/plugins/select.bootstrap4.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script>
        $(function () {
            function toggleTenderGroup() {
                var isTenderTask = $('input.task-type-radio:checked').val() === 'Tender/Query';
                $('#tender-select-group').toggle(isTenderTask);
            }
            $('input.task-type-radio').on('change', toggleTenderGroup);
            toggleTenderGroup();

            $('#tender_request_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Search tender/query…',
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: 'task-management/ajax-tenders.php',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return { q: params.term || '' };
                    },
                    processResults: function (data) {
                        return { results: data.results };
                    }
                }
            });
        });
    </script>
</body>
</html>
