<?php
require_once __DIR__ . '/inc/init.php';
require_once __DIR__ . '/../service/ScheduledTaskNotificationService.php';

// ---------- Filters (executed against the database via prepared statements) ----------
$filters = [
    'q'          => isset($_GET['q']) ? trim((string) $_GET['q']) : '',
    'status'     => isset($_GET['status']) ? (string) $_GET['status'] : '',
    'priority'   => isset($_GET['priority']) ? (string) $_GET['priority'] : '',
    'employee'   => isset($_GET['employee']) ? (string) $_GET['employee'] : '',
    'task_type'  => isset($_GET['task_type']) ? (string) $_GET['task_type'] : '',
    'tender_ref' => isset($_GET['tender_ref']) ? trim((string) $_GET['tender_ref']) : '',
];

if ($filters['status'] !== '' && !in_array($filters['status'], task_statuses(), true)) {
    $filters['status'] = '';
}
if ($filters['priority'] !== '' && !in_array($filters['priority'], task_priorities(), true)) {
    $filters['priority'] = '';
}
if ($filters['task_type'] !== '' && !in_array($filters['task_type'], task_types(), true)) {
    $filters['task_type'] = '';
}

$where  = [];
$params = [];
$types  = '';

// Employees only ever see their own tasks; managers/admins see everything.
if (!$taskCanViewAll) {
    $where[]  = '(t.assigned_to = ? OR EXISTS(SELECT 1 FROM task_assignees ta WHERE ta.task_id = t.id AND ta.employee_id = ?))';
    $params[] = $taskUserId;
    $params[] = $taskUserId;
    $types   .= 'ii';
}

if ($filters['q'] !== '') {
    $where[]  = '(t.title LIKE ? OR t.description LIKE ?)';
    $like     = '%' . $filters['q'] . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

if ($filters['status'] !== '') {
    $where[]  = 't.status = ?';
    $params[] = $filters['status'];
    $types   .= 's';
}

if ($filters['priority'] !== '') {
    $where[]  = 't.priority = ?';
    $params[] = $filters['priority'];
    $types   .= 's';
}

if ($taskCanViewAll && $filters['employee'] !== '') {
    $employeeId = task_get_int($filters['employee']);
    if ($employeeId !== false) {
        $where[]  = '(t.assigned_to = ? OR EXISTS(SELECT 1 FROM task_assignees ta WHERE ta.task_id = t.id AND ta.employee_id = ?))';
        $params[] = $employeeId;
        $params[] = $employeeId;
        $types   .= 'ii';
    }
}

if ($filters['task_type'] !== '') {
    $where[]  = 't.task_type = ?';
    $params[] = $filters['task_type'];
    $types   .= 's';
}

if ($filters['tender_ref'] !== '') {
    $where[]  = '(utr.tenderID LIKE ? OR utr.reference_code LIKE ?)';
    $like     = '%' . $filters['tender_ref'] . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

$sql = "SELECT t.id, t.title, t.task_type, t.priority, t.status,
               t.start_date, t.due_date, t.created_at, t.updated_at, t.tender_request_id,
               (
                   SELECT GROUP_CONCAT(DISTINCT a.username SEPARATOR ', ')
                   FROM admin a
                   WHERE a.id = t.assigned_to 
                      OR a.id IN (SELECT ta.employee_id FROM task_assignees ta WHERE ta.task_id = t.id)
               ) AS assigned_username,
               (
                   SELECT GROUP_CONCAT(DISTINCT a.id ORDER BY a.username SEPARATOR ',')
                   FROM admin a
                   WHERE a.id = t.assigned_to
                      OR a.id IN (SELECT ta.employee_id FROM task_assignees ta WHERE ta.task_id = t.id)
               ) AS assigned_employee_ids,
               creator.username  AS created_username,
               utr.tenderID      AS tender_id_number,
               utr.reference_code AS tender_reference_code
          FROM tasks t
          LEFT JOIN admin creator  ON creator.id  = t.created_by
          LEFT JOIN user_tender_requests utr ON utr.id = t.tender_request_id";
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY t.created_at DESC, t.id DESC';

$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$taskCanNotify = $taskCanViewAll || $taskCanCreate || $taskCanEdit;
$taskNotificationService = new ScheduledTaskNotificationService($db);
$taskRecipients = [];
$taskScheduleStatuses = [];
if (!empty($tasks) && $taskCanNotify) {
    foreach ($tasks as $taskRow) {
        $taskIdForNotify = (int) $taskRow['id'];
        $taskRecipients[$taskIdForNotify] = $taskNotificationService->recipientsForTask($taskIdForNotify);
        $taskScheduleStatuses[$taskIdForNotify] = $taskNotificationService->statusesForTask($taskIdForNotify);
    }
}

// Employee dropdown for the filter (managers/admins only)
$employees = [];
if ($taskCanViewAll) {
    $empResult = mysqli_query($db, "SELECT id, username FROM admin ORDER BY username ASC");
    if ($empResult) {
        while ($row = mysqli_fetch_assoc($empResult)) {
            $employees[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Task Management</title>
    <base href="../">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
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
                                <h5 class="m-b-10"><?php echo $taskCanViewAll ? 'Task Management' : 'My Tasks'; ?></h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="task-management/index.php">Tasks</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <?php task_render_flash(); ?>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header table-card-header d-flex justify-content-between align-items-center">
                            <h5><?php echo $taskCanViewAll ? 'Task List' : 'My Tasks'; ?></h5>
                            <?php if ($taskCanCreate): ?>
                                <a href="task-management/create.php" class="btn btn-primary"><i class="feather icon-plus"></i> Create Task</a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <!-- Filters (server-side) -->
                            <form method="get" action="task-management/index.php" class="row mb-4">
                                <div class="col-md-3">
                                    <input type="text" name="q" class="form-control" placeholder="Search tasks..." value="<?php echo e($filters['q']); ?>">
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option value="">Status ▼</option>
                                        <?php foreach (task_statuses() as $s): ?>
                                            <option value="<?php echo e($s); ?>" <?php echo $filters['status'] === $s ? 'selected' : ''; ?>><?php echo e($s); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="priority" class="form-control">
                                        <option value="">Priority ▼</option>
                                        <?php foreach (task_priorities() as $p): ?>
                                            <option value="<?php echo e($p); ?>" <?php echo $filters['priority'] === $p ? 'selected' : ''; ?>><?php echo e($p); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="task_type" class="form-control">
                                        <option value="">Task Type ▼</option>
                                        <?php foreach (task_types() as $t): ?>
                                            <option value="<?php echo e($t); ?>" <?php echo $filters['task_type'] === $t ? 'selected' : ''; ?>><?php echo e($t); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if ($taskCanViewAll): ?>
                                <div class="col-md-3 mt-2">
                                    <select name="employee" class="form-control">
                                        <option value="">Employee ▼</option>
                                        <?php foreach ($employees as $emp): ?>
                                            <option value="<?php echo e($emp['id']); ?>" <?php echo $filters['employee'] === (string) $emp['id'] ? 'selected' : ''; ?>><?php echo e($emp['username']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                <div class="col-md-3 mt-2">
                                    <input type="text" name="tender_ref" class="form-control" placeholder="Tender/Query reference..." value="<?php echo e($filters['tender_ref']); ?>">
                                </div>
                                <div class="col-md-3 mt-2">
                                    <button type="submit" class="btn btn-primary btn-block"><i class="feather icon-filter"></i> Filter</button>
                                </div>
                                <div class="col-md-3 mt-2">
                                    <a href="task-management/index.php" class="btn btn-secondary btn-block">Reset</a>
                                </div>
                            </form>

                            <div class="dt-responsive table-responsive">
                                <table class="table table-striped table-bordered nowrap">
                                    <thead>
                                        <tr>
                                            <th>Task</th>
                                            <th>Task Type</th>
                                            <th>Related Tender/Query</th>
                                            <?php if ($taskCanViewAll): ?>
                                            <th>Assigned To</th>
                                            <th>Created By</th>
                                            <th>Start Date</th>
                                            <?php endif; ?>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Due Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($tasks)): ?>
                                            <tr>
                                                <td colspan="<?php echo $taskCanViewAll ? 10 : 7; ?>" class="text-center text-muted">
                                                    No tasks found.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($tasks as $task): ?>
                                            <tr>
                                                <td>
                                                    <a href="task-management/view.php?id=<?php echo (int) $task['id']; ?>"><?php echo e($task['title']); ?></a>
                                                </td>
                                                <td>
                                                    <?php if ($task['task_type'] === 'Tender/Query'): ?>
                                                        <span class="badge badge-primary">Tender/Query</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-light">General</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($task['tender_id_number'] !== null): ?>
                                                        <a href="sent-edit.php?id=<?php echo base64_encode((string) $task['tender_request_id']); ?>"
                                                           title="Open tender/query">
                                                            <?php echo e($task['tender_id_number']); ?>
                                                        </a>
                                                        <?php if ($task['tender_reference_code'] !== null && $task['tender_reference_code'] !== ''): ?>
                                                            <span class="text-muted" style="font-size:12px;">(<?php echo e($task['tender_reference_code']); ?>)</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <?php if ($taskCanViewAll): ?>
                                                <td>
                                                    <?php
                                                    $assignedNames = preg_split('/,\s*/', (string) ($task['assigned_username'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
                                                    $assignedIds = array_values(array_filter(array_map('intval', explode(',', (string) ($task['assigned_employee_ids'] ?? '')))));
                                                    if (empty($assignedIds)): ?>
                                                        <?php echo e($task['assigned_username'] ?? '—'); ?>
                                                    <?php else: ?>
                                                        <?php foreach ($assignedIds as $assignedIndex => $assignedId):
                                                            $assignedName = $assignedNames[$assignedIndex] ?? 'Employee';
                                                            $initial = strtoupper(substr($assignedName, 0, 1)); ?>
                                                            <a href="employee-dashboard.php?id=<?php echo $assignedId; ?>"
                                                               class="employee-dashboard-link d-inline-flex align-items-center mr-2 mb-1"
                                                               title="Open employee dashboard">
                                                                <span class="employee-avatar-mini mr-1" style="width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#e8f0fe;color:#2563eb;font-weight:700;font-size:12px;">
                                                                    <?php echo e($initial); ?>
                                                                </span>
                                                                <span><strong><?php echo e($assignedName); ?></strong><small class="d-block text-muted">View dashboard</small></span>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo e($task['created_username'] ?? '—'); ?></td>
                                                <td><?php echo $task['start_date'] ? e(date('d M Y', strtotime($task['start_date']))) : '—'; ?></td>
                                                <?php endif; ?>
                                                <td><span class="badge <?php echo task_priority_badge($task['priority']); ?>"><?php echo e($task['priority']); ?></span></td>
                                                <td><span class="badge <?php echo task_status_badge($task['status']); ?>"><?php echo e($task['status']); ?></span></td>
                                                <td>
                                                    <?php
                                                    if ($task['due_date']) {
                                                        $dueTs = strtotime($task['due_date']);
                                                        echo e(date('d M Y', $dueTs));
                                                        if ($task['status'] !== 'Completed' && $task['status'] !== 'Cancelled' && $dueTs < strtotime('today')) {
                                                            echo ' <span class="badge badge-danger">Overdue</span>';
                                                        }
                                                    } else {
                                                        echo '—';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="task-management/view.php?id=<?php echo (int) $task['id']; ?>" class="btn btn-info btn-sm"><i class="feather icon-eye"></i> View</a>
                                                    <?php if ($taskCanEdit): ?>
                                                        <a href="task-management/edit.php?id=<?php echo (int) $task['id']; ?>" class="btn btn-warning btn-sm"><i class="feather icon-edit"></i> Edit</a>
                                                    <?php endif; ?>
                                                    <?php if ($taskCanNotify): ?>
                                                        <?php
                                                        $notifyTaskId = (int) $task['id'];
                                                        $recipients = $taskRecipients[$notifyTaskId] ?? [];
                                                        $recipientNames = array_map(function ($recipient) {
                                                            return $recipient['username'];
                                                        }, $recipients);
                                                        $taskLabel = 'Task #' . $notifyTaskId . ' - ' . $task['title'];
                                                        if (!empty($task['tender_id_number'])) {
                                                            $taskLabel .= ' - ' . $task['tender_id_number'];
                                                        }
                                                        $recipientJson = e(json_encode(array_values($recipientNames)));
                                                        ?>
                                                        <button type="button"
                                                                class="btn btn-light btn-sm task-notify-btn"
                                                                title="Email notification"
                                                                data-task-id="<?php echo $notifyTaskId; ?>"
                                                                data-task-label="<?php echo e($taskLabel); ?>"
                                                                data-channel="EMAIL"
                                                                data-recipients="<?php echo $recipientJson; ?>">
                                                            <i class="feather icon-mail"></i>
                                                        </button>
                                                        <button type="button"
                                                                class="btn btn-success btn-sm task-notify-btn"
                                                                title="WhatsApp notification"
                                                                data-task-id="<?php echo $notifyTaskId; ?>"
                                                                data-task-label="<?php echo e($taskLabel); ?>"
                                                                data-channel="WHATSAPP"
                                                                data-recipients="<?php echo $recipientJson; ?>">
                                                            <i class="fab fa-whatsapp"></i>
                                                        </button>
                                                        <button type="button"
                                                                class="btn btn-secondary btn-sm task-schedule-status-btn"
                                                                title="Scheduled notification status"
                                                                data-task-id="<?php echo $notifyTaskId; ?>">
                                                            <i class="feather icon-clock"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($taskCanDelete): ?>
                                                        <form action="task-management/delete.php" method="post" class="d-inline delete-task-form">
                                                            <input type="hidden" name="id" value="<?php echo (int) $task['id']; ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="feather icon-trash-2"></i> Delete</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($taskCanNotify): ?>
    <div class="modal fade" id="taskNotificationModal" tabindex="-1" role="dialog" aria-labelledby="taskNotificationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content" id="taskNotificationForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskNotificationModalLabel">Send Task Notification</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="task_id" id="notifyTaskId">
                    <input type="hidden" name="channel" id="notifyChannel">
                    <div class="mb-3">
                        <strong>Task:</strong>
                        <div id="notifyTaskLabel" class="text-muted"></div>
                    </div>
                    <div class="mb-3">
                        <strong>Channel:</strong>
                        <span id="notifyChannelLabel" class="badge badge-info"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Recipients:</strong>
                        <ul id="notifyRecipients" class="mb-0 pl-3"></ul>
                    </div>
                    <div class="form-group">
                        <label>Delivery</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="deliveryNow" name="delivery" value="now" class="custom-control-input" checked>
                            <label class="custom-control-label" for="deliveryNow">Send Now</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="deliveryLater" name="delivery" value="later" class="custom-control-input">
                            <label class="custom-control-label" for="deliveryLater">Schedule for Later</label>
                        </div>
                    </div>
                    <div id="scheduleFields" class="row" style="display:none;">
                        <div class="form-group col-md-6">
                            <label for="scheduleDate">Schedule Date</label>
                            <input type="date" class="form-control" id="scheduleDate" name="schedule_date">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="scheduleTime">Schedule Time</label>
                            <input type="time" class="form-control" id="scheduleTime" name="schedule_time">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notifyMessage">Message / notification note</label>
                        <textarea class="form-control" id="notifyMessage" name="message" rows="3" maxlength="1000"></textarea>
                    </div>
                    <div class="alert d-none" id="notifyModalAlert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="notifySubmitBtn">Send Now</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="taskScheduleStatusModal" tabindex="-1" role="dialog" aria-labelledby="taskScheduleStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskScheduleStatusModalLabel">Scheduled Notification Status</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php foreach ($tasks as $task): ?>
                        <?php $notifyTaskId = (int) $task['id']; ?>
                        <div class="task-status-table" data-task-id="<?php echo $notifyTaskId; ?>" style="display:none;">
                            <?php if (empty($taskScheduleStatuses[$notifyTaskId])): ?>
                                <p class="text-muted mb-0">No scheduled notifications found for this task.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Channel</th>
                                                <th>Recipient</th>
                                                <th>Scheduled At</th>
                                                <th>Status</th>
                                                <th>Error</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($taskScheduleStatuses[$notifyTaskId] as $statusRow): ?>
                                                <tr data-schedule-row="<?php echo (int) $statusRow['id']; ?>">
                                                    <td><?php echo e($statusRow['channel']); ?></td>
                                                    <td><?php echo e($statusRow['username']); ?></td>
                                                    <td><?php echo fmt($statusRow['scheduled_at'], true); ?></td>
                                                    <td><span class="badge badge-secondary"><?php echo e($statusRow['status']); ?></span></td>
                                                    <td class="text-muted" style="max-width:220px;white-space:normal;"><?php echo e($statusRow['error_message'] ?? ''); ?></td>
                                                    <td>
                                                        <?php if ($statusRow['status'] === 'PENDING'): ?>
                                                            <button type="button" class="btn btn-danger btn-sm cancel-schedule-btn" data-schedule-id="<?php echo (int) $statusRow['id']; ?>">Cancel</button>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script>
        document.addEventListener('submit', function (evt) {
            if (evt.target.classList.contains('delete-task-form')) {
                if (!confirm('Are you sure you want to delete this task? Task history and comments will also be deleted. This cannot be undone.')) {
                    evt.preventDefault();
                }
            }
        });

        <?php if ($taskCanNotify): ?>
        (function () {
            var modalAlert = document.getElementById('notifyModalAlert');
            var scheduleFields = document.getElementById('scheduleFields');
            var submitBtn = document.getElementById('notifySubmitBtn');
            var form = document.getElementById('taskNotificationForm');

            function showAlert(type, message) {
                modalAlert.className = 'alert alert-' + type;
                modalAlert.textContent = message;
            }

            function clearAlert() {
                modalAlert.className = 'alert d-none';
                modalAlert.textContent = '';
            }

            function refreshDeliveryState() {
                var later = document.getElementById('deliveryLater').checked;
                scheduleFields.style.display = later ? '' : 'none';
                submitBtn.textContent = later ? 'Schedule Notification' : 'Send Now';
            }

            document.querySelectorAll('input[name="delivery"]').forEach(function (input) {
                input.addEventListener('change', refreshDeliveryState);
            });

            document.querySelectorAll('.task-notify-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    var recipients = [];
                    try {
                        recipients = JSON.parse(button.getAttribute('data-recipients') || '[]');
                    } catch (e) {}
                    document.getElementById('notifyTaskId').value = button.getAttribute('data-task-id');
                    document.getElementById('notifyChannel').value = button.getAttribute('data-channel');
                    document.getElementById('notifyTaskLabel').textContent = button.getAttribute('data-task-label');
                    document.getElementById('notifyChannelLabel').textContent = button.getAttribute('data-channel');
                    document.getElementById('notifyMessage').value = '';
                    document.getElementById('deliveryNow').checked = true;
                    document.getElementById('scheduleDate').value = '';
                    document.getElementById('scheduleTime').value = '';

                    var list = document.getElementById('notifyRecipients');
                    list.innerHTML = '';
                    if (recipients.length === 0) {
                        var empty = document.createElement('li');
                        empty.className = 'text-muted';
                        empty.textContent = 'No active recipients found';
                        list.appendChild(empty);
                    } else {
                        recipients.forEach(function (name) {
                            var li = document.createElement('li');
                            li.textContent = name;
                            list.appendChild(li);
                        });
                    }
                    clearAlert();
                    refreshDeliveryState();
                    $('#taskNotificationModal').modal('show');
                });
            });

            form.addEventListener('submit', function (evt) {
                evt.preventDefault();
                clearAlert();

                var delivery = document.querySelector('input[name="delivery"]:checked').value;
                var channel = document.getElementById('notifyChannel').value;
                var recipientCount = document.querySelectorAll('#notifyRecipients li:not(.text-muted)').length;
                var confirmText = delivery === 'later'
                    ? 'Schedule ' + channel + ' notification for ' + recipientCount + ' assigned employee(s)?'
                    : 'Send ' + channel + ' notification to ' + recipientCount + ' assigned employee(s)?';

                if (!window.confirm(confirmText)) {
                    return;
                }

                submitBtn.disabled = true;
                var data = new FormData(form);
                data.append('action', delivery === 'later' ? 'schedule' : 'send_now');

                fetch('task-management/notification-action.php', {
                    method: 'POST',
                    body: data,
                    credentials: 'same-origin'
                }).then(function (response) {
                    return response.json();
                }).then(function (payload) {
                    if (!payload.success) {
                        showAlert('danger', payload.message || 'Notification action failed.');
                        return;
                    }
                    var message = payload.message || 'Notification action completed.';
                    if (delivery === 'later' && payload.scheduled_at) {
                        message = 'Notification scheduled successfully for ' + payload.scheduled_at + '.';
                    }
                    showAlert('success', message);
                    setTimeout(function () {
                        window.location.reload();
                    }, 900);
                }).catch(function () {
                    showAlert('danger', 'Notification action failed.');
                }).finally(function () {
                    submitBtn.disabled = false;
                });
            });

            document.querySelectorAll('.task-schedule-status-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    var taskId = button.getAttribute('data-task-id');
                    document.querySelectorAll('.task-status-table').forEach(function (table) {
                        table.style.display = table.getAttribute('data-task-id') === taskId ? '' : 'none';
                    });
                    $('#taskScheduleStatusModal').modal('show');
                });
            });

            document.querySelectorAll('.cancel-schedule-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!window.confirm('Cancel this pending scheduled notification?')) {
                        return;
                    }
                    button.disabled = true;
                    var data = new FormData();
                    data.append('action', 'cancel_schedule');
                    data.append('schedule_id', button.getAttribute('data-schedule-id'));
                    fetch('task-management/notification-action.php', {
                        method: 'POST',
                        body: data,
                        credentials: 'same-origin'
                    }).then(function (response) {
                        return response.json();
                    }).then(function (payload) {
                        if (payload.success) {
                            window.location.reload();
                            return;
                        }
                        alert(payload.message || 'Unable to cancel scheduled notification.');
                        button.disabled = false;
                    }).catch(function () {
                        alert('Unable to cancel scheduled notification.');
                        button.disabled = false;
                    });
                });
            });
        })();
        <?php endif; ?>
    </script>
</body>
</html>
