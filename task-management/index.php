<?php
require_once __DIR__ . '/inc/init.php';

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
    $where[]  = 't.assigned_to = ?';
    $params[] = $taskUserId;
    $types   .= 'i';
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
        $where[]  = 't.assigned_to = ?';
        $params[] = $employeeId;
        $types   .= 'i';
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
               assigned.username AS assigned_username,
               creator.username  AS created_username,
               utr.tenderID      AS tender_id_number,
               utr.reference_code AS tender_reference_code
          FROM tasks t
          LEFT JOIN admin assigned ON assigned.id = t.assigned_to
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
    <base href="../login/">
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

    <?php include '../login/navbar.php'; ?>

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
                                <li class="breadcrumb-item"><a href="../task-management/index.php">Tasks</a></li>
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
                                <a href="../task-management/create.php" class="btn btn-primary"><i class="feather icon-plus"></i> Create Task</a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <!-- Filters (server-side) -->
                            <form method="get" action="../task-management/index.php" class="row mb-4">
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
                                    <a href="../task-management/index.php" class="btn btn-secondary btn-block">Reset</a>
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
                                                    <a href="../task-management/view.php?id=<?php echo (int) $task['id']; ?>"><?php echo e($task['title']); ?></a>
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
                                                        <a href="../login/sent-edit.php?id=<?php echo base64_encode((string) $task['tender_request_id']); ?>"
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
                                                <td><?php echo e($task['assigned_username'] ?? '—'); ?></td>
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
                                                    <a href="../task-management/view.php?id=<?php echo (int) $task['id']; ?>" class="btn btn-info btn-sm"><i class="feather icon-eye"></i> View</a>
                                                    <?php if ($taskCanEdit): ?>
                                                        <a href="../task-management/edit.php?id=<?php echo (int) $task['id']; ?>" class="btn btn-warning btn-sm"><i class="feather icon-edit"></i> Edit</a>
                                                    <?php endif; ?>
                                                    <?php if ($taskCanDelete): ?>
                                                        <form action="../task-management/delete.php" method="post" class="d-inline delete-task-form">
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
    </script>
</body>
</html>
