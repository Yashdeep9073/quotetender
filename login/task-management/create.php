<?php
require_once __DIR__ . '/inc/init.php';
require_once __DIR__ . '/../service/NotificationService.php';

if (!$taskCanCreate) {
    task_redirect('index.php', 'danger', 'You do not have permission to create tasks.');
    return;
}

$errors = [];
// Values kept for re-display after a validation failure
$form = [
    'title'             => '',
    'description'       => '',
    'task_type'         => 'General',
    'tender_request_id' => '',
    'tender_label'      => '',
    'assigned_to'       => '',
    'priority'          => 'Medium',
    'status'            => 'Pending',
    'start_date'        => '',
    'due_date'          => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['title']       = isset($_POST['title']) ? trim((string) $_POST['title']) : '';
    $form['description'] = isset($_POST['description']) ? trim((string) $_POST['description']) : '';
    $form['task_type']   = isset($_POST['task_type']) ? (string) $_POST['task_type'] : 'General';
    $form['tender_request_id'] = isset($_POST['tender_request_id']) ? (string) $_POST['tender_request_id'] : '';
    $form['assigned_to'] = isset($_POST['assigned_to']) ? (string) $_POST['assigned_to'] : '';
    $form['priority']    = isset($_POST['priority']) ? (string) $_POST['priority'] : '';
    $form['status']      = isset($_POST['status']) ? (string) $_POST['status'] : 'Pending';
    $form['start_date']  = isset($_POST['start_date']) ? (string) $_POST['start_date'] : '';
    $form['due_date']    = isset($_POST['due_date']) ? (string) $_POST['due_date'] : '';

    // 1. Required fields
    if ($form['title'] === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($form['title']) > 255) {
        $errors[] = 'Title must be at most 255 characters.';
    }

    if (!in_array($form['task_type'], task_types(), true)) {
        $errors[] = 'Invalid task type.';
    }

    // 2. Assigned employee must exist
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

    // 3. Priority whitelist
    if (!in_array($form['priority'], task_priorities(), true)) {
        $errors[] = 'Invalid priority.';
    }

    // 4. Status whitelist (only if submitted; default Pending)
    if (!in_array($form['status'], task_statuses(), true)) {
        $errors[] = 'Invalid status.';
    }

    // 5. Dates
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

    // 6. Tender/Query relationship (only for Tender/Query tasks)
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
        // Server determines the creator – never trust client input.
        $stmtInsert = $db->prepare(
            "INSERT INTO tasks
                (title, description, task_type, tender_request_id, created_by, assigned_to,
                 priority, status, start_date, due_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmtInsert->bind_param(
            'sssiiissss',
            $form['title'],
            $form['description'],
            $form['task_type'],
            $tenderRequestId,
            $taskUserId,
            $assignedTo,
            $form['priority'],
            $form['status'],
            $startDate,
            $dueDate
        );

        if ($stmtInsert->execute()) {
            $newTaskId = (int) $stmtInsert->insert_id;
            task_log_history($db, $newTaskId, $taskUserId, 'Task created');

            // Notify the assigned employee (in-app + email handled by the service)
            $notificationService = new NotificationService($db);
            if ($form['task_type'] === 'Tender/Query') {
                $notificationService->notifyTenderTaskAssigned($newTaskId, $assignedTo, $form['title']);
            } else {
                $notificationService->notifyTaskAssigned($newTaskId, $assignedTo, $form['title']);
            }

            task_redirect('view.php?id=' . $newTaskId, 'success', 'Task created successfully.');
        } else {
            $errors[] = 'Failed to save the task. Please try again.';
        }
    }
}

// Pre-selected tender/query (after a validation error) to render the summary preview
$preselectedTender = null;
if ($form['tender_request_id'] !== '') {
    $ptId = task_get_int($form['tender_request_id']);
    if ($ptId !== false) {
        $stmtPT = $db->prepare(
            "SELECT utr.id, utr.tenderID, utr.reference_code, utr.status, utr.created_at,
                    m.name AS member_name, m.firm_name AS member_firm
               FROM user_tender_requests utr
               LEFT JOIN members m ON m.member_id = utr.member_id
              WHERE utr.id = ? AND utr.delete_tender = '0'"
        );
        $stmtPT->bind_param('i', $ptId);
        $stmtPT->execute();
        $preselectedTender = $stmtPT->get_result()->fetch_assoc() ?: null;
        if ($preselectedTender) {
            $preselectedTender['created_date'] = !empty($preselectedTender['created_at'])
                ? date('d M Y', strtotime($preselectedTender['created_at']))
                : '';
        }
    }
}

// Employees for the assignment dropdown
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
    <title>Create Task</title>
    <base href="../">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/plugins/select2.min.css">
    <link rel="stylesheet" href="assets/css/plugins/select.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ---- Related Tender/Query selector (create task page) ---- */
        .tender-picker { position: relative; }
        .tender-picker .tender-search-icon {
            position: absolute; top: 11px; left: 12px; z-index: 1;
            color: #6b7a8d; font-size: 15px; pointer-events: none;
        }
        .tender-picker .select2-container--bootstrap4 .select2-selection {
            padding-left: 34px;
        }
        .tender-picker .select2-container--bootstrap4 .select2-selection--single {
            height: 38px;
        }
        .tender-picker .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }
        .tender-picker .select2-dropdown {
            background-color: #ffffff;
            border: 1px solid #e3e8ee;
            border-radius: 6px;
            box-shadow: 0 8px 24px rgba(4, 26, 55, 0.12);
            z-index: 1060;
            margin-top: 4px;
        }
        .tender-picker .select2-container--bootstrap4 .select2-results__option {
            padding: 8px 10px;
            border-radius: 4px;
        }
        .tender-picker .select2-container--bootstrap4 .select2-results__option--highlighted {
            background-color: rgba(51, 204, 51, 0.08);
            color: #222222;
        }
        .tender-chip {
            display: inline-flex; align-items: center;
            background-color: rgba(51, 204, 51, 0.1);
            color: #1e7e1e; border-radius: 4px;
            padding: 2px 8px; font-size: 13px;
        }
        .tender-chip i { margin-right: 4px; font-size: 13px; }
        .tender-searching {
            padding: 12px; text-align: center; color: #6b7a8d; font-size: 13px;
        }
        .tender-no-results {
            padding: 16px 12px; text-align: center; color: #6b7a8d; font-size: 13px;
        }
        .tender-no-results > i {
            font-size: 24px; display: block; margin-bottom: 6px; color: #c3cdd8;
        }
        #tender-summary .tender-selected-title { font-size: 14px; }
        #tender-summary .tender-selected-sub { font-size: 12px; color: #6b7a8d; margin-top: 2px; }
        .tender-selected-clear {
            border: none; background: transparent; color: #b02a37;
            font-size: 16px; line-height: 1; padding: 2px 4px; cursor: pointer;
        }
        .tender-selected-clear:hover { color: #8f1d29; }
    </style>
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
                                <h5 class="m-b-10">Create Task</h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="task-management/index.php">Tasks</a></li>
                                <li class="breadcrumb-item"><a href="#!">Create</a></li>
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
                            <h5>Create New Task</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Title</label>
                                            <input type="text" name="title" class="form-control" placeholder="Task title"
                                                   value="<?php echo e($form['title']); ?>" required>
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
                                            <textarea name="description" class="form-control" rows="4"
                                                      placeholder="Task description..."><?php echo e($form['description']); ?></textarea>
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
                                            <label><i class="feather icon-paperclip mr-1"></i>Related Tender / Query</label>
                                            <div class="tender-picker">
                                                <i class="feather icon-search tender-search-icon"></i>
                                                <select name="tender_request_id" id="tender_request_id" class="form-control" style="width:100%;">
                                                    <?php if ($form['tender_request_id'] !== ''): ?>
                                                        <option value="<?php echo e($form['tender_request_id']); ?>" selected>
                                                            <?php echo e($form['tender_label'] !== '' ? $form['tender_label'] : 'Selected tender #' . $form['tender_request_id']); ?>
                                                        </option>
                                                    <?php endif; ?>
                                                </select>
                                                <small class="form-text text-muted">Search by Tender ID or Reference Code · Minimum 2 characters</small>
                                            </div>
                                            <!-- Selected tender/query card -->
                                            <div id="tender-summary" class="mt-2 rounded" style="display:none;background:#f8fafc;border:1px solid #e3e8ee;padding:10px 12px;"></div>
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
                                        <a href="task-management/index.php" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary">Create Task</button>
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

            function esc(value) {
                return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
            }

            // Reads a field from either the AJAX payload (snake_case) or the
            // static preselected option (data-* attributes, camelCase).
            function tenderField(item, key) {
                if (item == null) { return ''; }
                if (item[key] !== undefined && item[key] !== null && item[key] !== '') { return item[key]; }
                var camel = key.replace(/_([a-z])/g, function (m, c) { return c.toUpperCase(); });
                if (item[camel] !== undefined && item[camel] !== null && item[camel] !== '') { return item[camel]; }
                var alt = camel.charAt(0).toLowerCase() + camel.slice(1);
                return item[alt] !== undefined && item[alt] !== null ? item[alt] : '';
            }

            function tenderStatusBadge(status) {
                var map = {
                    'Sent': 'badge-success',
                    'Awarded': 'badge-success',
                    'Requested': 'badge-info',
                    'In Progress': 'badge-warning',
                    'Cancelled': 'badge-danger',
                    'Pending': 'badge-secondary'
                };
                var cls = map[status] || 'badge-secondary';
                return '<span class="badge ' + cls + '">' + esc(status) + '</span>';
            }

            // Dropdown result: compact card with tender ID + status, reference, member, date
            function formatTenderResult(item) {
                if (item.loading) { return item.text; }
                if (!item.id) { return item.text; }
                var $wrap = $('<div style="padding:3px 0;"></div>');
                var $head = $('<div style="display:flex;justify-content:space-between;align-items:center;"></div>');
                $head.append($('<strong style="font-size:13px;"></strong>').text(tenderField(item, 'tenderID') || 'No Tender ID'));
                var status = tenderField(item, 'status');
                if (status) { $head.append($(tenderStatusBadge(status))); }
                $wrap.append($head);
                var ref = tenderField(item, 'reference_code');
                if (ref) { $wrap.append($('<div style="font-size:12px;color:#6b7a8d;"></div>').text(ref)); }
                var meta = [];
                var member = tenderField(item, 'member_name');
                if (member) {
                    var firm = tenderField(item, 'member_firm');
                    meta.push(member + (firm ? ' (' + firm + ')' : ''));
                }
                var created = tenderField(item, 'created_date');
                if (created) { meta.push(created); }
                if (meta.length) {
                    $wrap.append($('<div style="font-size:12px;color:#8a97a5;"></div>').text(meta.join(' · ')));
                }
                return $wrap;
            }

            // Selected chip inside the search control: paperclip + tender ID + status
            function formatTenderSelection(item) {
                if (!item.id) { return 'Search tender ID or reference…'; }
                var id = tenderField(item, 'tenderID');
                if (id) {
                    var $chip = $('<span class="tender-chip"></span>');
                    $chip.append($('<i class="feather icon-paperclip"></i>'));
                    $chip.append($('<strong></strong>').text(id));
                    var status = tenderField(item, 'status');
                    if (status) { $chip.append(' ').append($(tenderStatusBadge(status))); }
                    return $chip;
                }
                return item.text;
            }

            // Selected tender/query card (shown below the search control)
            function renderSummary(item) {
                if (!item || !item.id) {
                    $('#tender-summary').hide().empty();
                    return;
                }
                var id = tenderField(item, 'tenderID') || item.text || item.id;
                var ref = tenderField(item, 'reference_code');
                var status = tenderField(item, 'status');
                var member = tenderField(item, 'member_name');
                var firm = tenderField(item, 'member_firm');
                var created = tenderField(item, 'created_date');

                var html = '<div class="d-flex justify-content-between align-items-start">'
                    + '<div>'
                    + '<div class="tender-selected-title"><i class="feather icon-paperclip mr-1"></i><strong>' + esc(id) + '</strong>'
                    + (status ? ' ' + tenderStatusBadge(status) : '')
                    + '</div>'
                    + (ref ? '<div class="tender-selected-sub">' + esc(ref) + '</div>' : '')
                    + (status ? '<div class="tender-selected-sub">Status: ' + esc(status) + '</div>' : '')
                    + (member ? '<div class="tender-selected-sub">Registered: ' + esc(member + (firm ? ' (' + firm + ')' : '')) + (created ? ' · ' + esc(created) : '') + '</div>' : '')
                    + '</div>'
                    + '<button type="button" class="tender-selected-clear" title="Change tender"><i class="feather icon-x"></i></button>'
                    + '</div>';

                $('#tender-summary').html(html).show();
                $('#tender-summary .tender-selected-clear').on('click', function () {
                    $('#tender_request_id').val(null).trigger('change');
                    var $search = $('.tender-picker .select2-search__field');
                    if ($search.length) { $search.focus(); }
                });
            }

            $('#tender_request_id').select2({
                theme: 'bootstrap4',
                placeholder: 'Search tender ID or reference…',
                allowClear: true,
                minimumInputLength: 2,
                templateResult: formatTenderResult,
                templateSelection: formatTenderSelection,
                language: {
                    inputTooShort: function () {
                        return '<span class="tender-searching">Type at least 2 characters to search</span>';
                    },
                    searching: function () {
                        return '<div class="tender-searching"><i class="feather icon-loader anim-rotate mr-1"></i>Searching tenders...</div>';
                    },
                    noResults: function () {
                        return '<div class="tender-no-results"><i class="feather icon-inbox"></i>'
                            + '<div>No tender/query found</div>'
                            + '<div class="small text-muted">Try another Tender ID or Reference Code.</div></div>';
                    }
                },
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

            $('#tender_request_id').on('select2:select', function (e) {
                renderSummary(e.params.data);
            });
            $('#tender_request_id').on('select2:clear', function () {
                renderSummary(null);
            });

            // Initial card when a tender is already selected (e.g. after a validation error)
            <?php if ($preselectedTender): ?>
                renderSummary(<?php echo json_encode($preselectedTender, JSON_UNESCAPED_UNICODE); ?>);
            <?php endif; ?>
        });
    </script>
</body>
</html>
