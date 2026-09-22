<?php
session_start();
include("db/config.php");
error_reporting(1);

if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
    exit();
}

$currentAdminId = (int)($_SESSION['login_user_id'] ?? 0);
$employeeId = (int)($_GET['id'] ?? 0);

if ($employeeId <= 0) {
    http_response_code(400);
    die('Invalid employee ID.');
}

function safeDate($value, $format = 'M d, Y') {
    if (empty($value)) return '—';
    $timestamp = strtotime($value);
    return $timestamp ? date($format, $timestamp) : htmlspecialchars((string)$value);
}

function statusBadgeClass($status) {
    switch (strtolower((string)$status)) {
        case 'completed': return 'success';
        case 'in progress': return 'info';
        case 'cancelled': return 'secondary';
        case 'pending': return 'warning';
        case 'requested': return 'danger';
        case 'sent': return 'warning';
        case 'allotted': return 'info';
        default: return 'primary';
    }
}

try {
    // Current logged-in employee + permission.
    $stmtCurrent = $db->prepare("
        SELECT a.id, a.username, a.role_id, r.role_name
        FROM admin a
        LEFT JOIN roles r ON r.role_id = a.role_id
        WHERE a.id = ?
        LIMIT 1
    ");
    $stmtCurrent->bind_param('i', $currentAdminId);
    $stmtCurrent->execute();
    $currentUser = $stmtCurrent->get_result()->fetch_assoc();

    $currentRoleName = strtolower(trim((string)($currentUser['role_name'] ?? '')));
    $canViewAllEmployees = in_array($currentRoleName, ['admin', 'super admin'], true);

    if (!$canViewAllEmployees && !empty($currentUser['role_id'])) {
        $stmtPerm = $db->prepare("
            SELECT 1
            FROM role_permissions rp
            INNER JOIN permissions p ON p.permission_id = rp.permission_id
            WHERE rp.role_id = ?
              AND p.permission_name IN ('Dashboard Employee Stats', 'Task Management')
            LIMIT 1
        ");
        $roleId = (int)$currentUser['role_id'];
        $stmtPerm->bind_param('i', $roleId);
        $stmtPerm->execute();
        $canViewAllEmployees = (bool)$stmtPerm->get_result()->fetch_row();
    }

    if (!$canViewAllEmployees && $employeeId !== $currentAdminId) {
        http_response_code(403);
        die('Permission denied.');
    }

    // Employee profile.
    $stmtEmployee = $db->prepare("
        SELECT a.id, a.username, a.email, a.mobile, a.status, a.created_at, a.updated_at,
               r.role_name
        FROM admin a
        LEFT JOIN roles r ON r.role_id = a.role_id
        WHERE a.id = ?
        LIMIT 1
    ");
    $stmtEmployee->bind_param('i', $employeeId);
    $stmtEmployee->execute();
    $employee = $stmtEmployee->get_result()->fetch_assoc();

    if (!$employee) {
        http_response_code(404);
        die('Employee not found.');
    }

    $employeeUsername = (string)$employee['username'];
    $employeeIdString = (string)$employeeId;

    // Assigned task stats. Supports both legacy assigned_to and task_assignees.
    $stmtAssignedStats = $db->prepare("
        SELECT
            COUNT(DISTINCT t.id) AS total_tasks,
            COUNT(DISTINCT CASE WHEN t.status = 'Pending' THEN t.id END) AS pending_tasks,
            COUNT(DISTINCT CASE WHEN t.status = 'In Progress' THEN t.id END) AS in_progress_tasks,
            COUNT(DISTINCT CASE WHEN t.status = 'Completed' THEN t.id END) AS completed_tasks,
            COUNT(DISTINCT CASE
                WHEN t.due_date IS NOT NULL
                 AND t.due_date < CURDATE()
                 AND t.status NOT IN ('Completed', 'Cancelled')
                THEN t.id END
            ) AS overdue_tasks
        FROM tasks t
        LEFT JOIN task_assignees ta ON ta.task_id = t.id
        WHERE t.assigned_to = ? OR ta.employee_id = ?
    ");
    $stmtAssignedStats->bind_param('ii', $employeeId, $employeeId);
    $stmtAssignedStats->execute();
    $assignedStats = $stmtAssignedStats->get_result()->fetch_assoc() ?: [];

    // Tasks created by this employee.
    $stmtCreatedTasks = $db->prepare("
        SELECT
            COUNT(*) AS total_created,
            COUNT(CASE WHEN status = 'Completed' THEN 1 END) AS completed_created
        FROM tasks
        WHERE created_by = ?
    ");
    $stmtCreatedTasks->bind_param('i', $employeeId);
    $stmtCreatedTasks->execute();
    $createdTaskStats = $stmtCreatedTasks->get_result()->fetch_assoc() ?: [];

    /*
     * Legacy tender table has no created_by column.
     * The employee attribution available in the existing schema is user_tender_requests.updated_by.
     * We therefore show tender records handled/created-updated by this employee using updated_by.
     */
    $stmtTenderStats = $db->prepare("
        SELECT
            COUNT(*) AS total_tenders,
            COUNT(CASE WHEN status = 'Requested' THEN 1 END) AS requested_tenders,
            COUNT(CASE WHEN status = 'Sent' THEN 1 END) AS sent_tenders,
            COUNT(CASE WHEN status = 'Allotted' THEN 1 END) AS allotted_tenders,
            COUNT(CASE WHEN remark = 'accepted' THEN 1 END) AS awarded_tenders
        FROM user_tender_requests
        WHERE delete_tender = '0'
          AND (updated_by = ? OR updated_by = ?)
    ");
    $stmtTenderStats->bind_param('ss', $employeeUsername, $employeeIdString);
    $stmtTenderStats->execute();
    $tenderStats = $stmtTenderStats->get_result()->fetch_assoc() ?: [];

    // Recent assigned tasks.
    $stmtAssignedTasks = $db->prepare("
        SELECT DISTINCT
            t.id, t.title, t.task_type, t.priority, t.status,
            t.start_date, t.due_date, t.created_at,
            creator.username AS creator_name,
            ur.tenderID, ur.reference_code
        FROM tasks t
        LEFT JOIN task_assignees ta ON ta.task_id = t.id
        LEFT JOIN admin creator ON creator.id = t.created_by
        LEFT JOIN user_tender_requests ur ON ur.id = t.tender_request_id
        WHERE t.assigned_to = ? OR ta.employee_id = ?
        ORDER BY t.created_at DESC
        LIMIT 15
    ");
    $stmtAssignedTasks->bind_param('ii', $employeeId, $employeeId);
    $stmtAssignedTasks->execute();
    $assignedTasks = $stmtAssignedTasks->get_result()->fetch_all(MYSQLI_ASSOC);

    // Recent tasks created by employee.
    $stmtRecentCreated = $db->prepare("
        SELECT t.id, t.title, t.priority, t.status, t.due_date, t.created_at,
               assignee.username AS assignee_name
        FROM tasks t
        LEFT JOIN admin assignee ON assignee.id = t.assigned_to
        WHERE t.created_by = ?
        ORDER BY t.created_at DESC
        LIMIT 10
    ");
    $stmtRecentCreated->bind_param('i', $employeeId);
    $stmtRecentCreated->execute();
    $recentCreatedTasks = $stmtRecentCreated->get_result()->fetch_all(MYSQLI_ASSOC);

    // Recent tender activity attributed through updated_by.
    $stmtTenderActivity = $db->prepare("
        SELECT
            ur.id, ur.tenderID, ur.tender_no, ur.reference_code,
            ur.status, ur.remark, ur.created_at, ur.updated_at,
            d.department_name,
            s.section_name,
            dv.division_name
        FROM user_tender_requests ur
        LEFT JOIN department d ON d.department_id = ur.department_id
        LEFT JOIN section s ON s.section_id = ur.section_id
        LEFT JOIN division dv ON dv.division_id = ur.division_id
        WHERE ur.delete_tender = '0'
          AND (ur.updated_by = ? OR ur.updated_by = ?)
        ORDER BY COALESCE(ur.updated_at, ur.created_at) DESC
        LIMIT 15
    ");
    $stmtTenderActivity->bind_param('ss', $employeeUsername, $employeeIdString);
    $stmtTenderActivity->execute();
    $tenderActivity = $stmtTenderActivity->get_result()->fetch_all(MYSQLI_ASSOC);

    // Recent task activity performed by employee.
    $stmtTaskHistory = $db->prepare("
        SELECT th.task_id, th.action, th.created_at, t.title
        FROM task_history th
        LEFT JOIN tasks t ON t.id = th.task_id
        WHERE th.user_id = ?
        ORDER BY th.created_at DESC
        LIMIT 10
    ");
    $stmtTaskHistory->bind_param('i', $employeeId);
    $stmtTaskHistory->execute();
    $taskHistory = $stmtTaskHistory->get_result()->fetch_all(MYSQLI_ASSOC);

} catch (Throwable $e) {
    http_response_code(500);
    die('Unable to load employee dashboard: ' . htmlspecialchars($e->getMessage()));
}

$name = $_SESSION['login_user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <title>Employee Dashboard - <?php echo htmlspecialchars($employee['username']); ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .employee-detail-page { padding: 16px; }
        .employee-detail-page .card {
            border: 0;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(16,24,40,.05), 0 1px 3px rgba(16,24,40,.08);
            margin-bottom: 16px;
        }
        .employee-profile-card .card-body {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px 20px;
        }
        .employee-avatar {
            width: 64px;
            height: 64px;
            flex: 0 0 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: linear-gradient(135deg,#4099ff,#73b4ff);
            color: #fff;
            font-size: 25px;
            font-weight: 700;
        }
        .employee-profile-name {
            margin: 0 0 4px;
            color: #1e293b;
            font-size: 20px;
            font-weight: 700;
        }
        .employee-profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            color: #64748b;
            font-size: 13px;
        }
        .employee-back-btn {
            margin-left: auto;
            align-self: flex-start;
        }
        .employee-kpi-card .card-body { padding: 16px; }
        .employee-kpi-icon {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            margin-bottom: 10px;
            background: #f1f5f9;
            color: #4099ff;
            font-size: 18px;
        }
        .employee-kpi-label {
            display: block;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .employee-kpi-value {
            margin: 0;
            color: #0f172a;
            font-size: 24px;
            font-weight: 700;
        }
        .employee-section-title {
            margin: 0;
            color: #1e293b;
            font-size: 15px;
            font-weight: 600;
        }
        .employee-detail-page .table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .employee-detail-page .table td {
            vertical-align: middle;
            font-size: 13px;
        }
        .employee-detail-page .table td,
        .employee-detail-page .table th {
            padding: 9px 12px;
        }
        .employee-detail-page .table-responsive {
            max-height: 430px;
            overflow: auto;
        }
        .employee-empty {
            padding: 26px;
            text-align: center;
            color: #94a3b8;
        }
        .status-pill {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 600;
        }
        .status-pill.success { background:#dcfce7; color:#15803d; }
        .status-pill.info { background:#e0f2fe; color:#0369a1; }
        .status-pill.warning { background:#fef3c7; color:#a16207; }
        .status-pill.danger { background:#fee2e2; color:#b91c1c; }
        .status-pill.secondary { background:#e2e8f0; color:#475569; }
        .status-pill.primary { background:#dbeafe; color:#1d4ed8; }
        @media (max-width: 767.98px) {
            .employee-detail-page { padding: 10px; }
            .employee-profile-card .card-body { align-items: flex-start; flex-wrap: wrap; }
            .employee-back-btn { margin-left: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include 'navbar.php'; ?>

    <header class="navbar pcoded-header navbar-expand-lg navbar-light headerpos-fixed header-blue">
        <div class="m-header">
            <a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>
            <a href="#!" class="b-brand" style="font-size:24px;">ADMIN PANEL</a>
            <a href="#!" class="mob-toggler"><i class="feather icon-more-vertical"></i></a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a href="#!" class="full-screen" onClick="javascript:toggleFullScreen()">
                        <i class="feather icon-maximize"></i>
                    </a>
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
                    <span><?php echo htmlspecialchars($name); ?></span>
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
        <div class="pcoded-content employee-detail-page">

            <div class="card employee-profile-card">
                <div class="card-body">
                    <div class="employee-avatar">
                        <?php echo strtoupper(substr((string)$employee['username'], 0, 1)); ?>
                    </div>
                    <div>
                        <h4 class="employee-profile-name"><?php echo htmlspecialchars($employee['username']); ?></h4>
                        <div class="employee-profile-meta">
                            <span><i class="feather icon-shield"></i> <?php echo htmlspecialchars($employee['role_name'] ?: 'No Role'); ?></span>
                            <span><i class="feather icon-mail"></i> <?php echo htmlspecialchars($employee['email']); ?></span>
                            <span><i class="feather icon-phone"></i> <?php echo htmlspecialchars($employee['mobile']); ?></span>
                            <span>
                                <i class="feather icon-activity"></i>
                                <?php echo ((int)$employee['status'] === 1) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                    <a href="dashboard.php" class="btn btn-outline-secondary employee-back-btn">
                        <i class="feather icon-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Assigned Task KPIs -->
            <div class="row">
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card employee-kpi-card">
                        <div class="card-body">
                            <div class="employee-kpi-icon"><i class="feather icon-clipboard"></i></div>
                            <span class="employee-kpi-label">Assigned Tasks</span>
                            <h3 class="employee-kpi-value"><?php echo (int)($assignedStats['total_tasks'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card employee-kpi-card">
                        <div class="card-body">
                            <div class="employee-kpi-icon"><i class="feather icon-clock"></i></div>
                            <span class="employee-kpi-label">Pending</span>
                            <h3 class="employee-kpi-value"><?php echo (int)($assignedStats['pending_tasks'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card employee-kpi-card">
                        <div class="card-body">
                            <div class="employee-kpi-icon"><i class="feather icon-loader"></i></div>
                            <span class="employee-kpi-label">In Progress</span>
                            <h3 class="employee-kpi-value"><?php echo (int)($assignedStats['in_progress_tasks'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card employee-kpi-card">
                        <div class="card-body">
                            <div class="employee-kpi-icon"><i class="feather icon-check-circle"></i></div>
                            <span class="employee-kpi-label">Completed</span>
                            <h3 class="employee-kpi-value"><?php echo (int)($assignedStats['completed_tasks'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card employee-kpi-card">
                        <div class="card-body">
                            <div class="employee-kpi-icon"><i class="feather icon-alert-circle"></i></div>
                            <span class="employee-kpi-label">Overdue</span>
                            <h3 class="employee-kpi-value"><?php echo (int)($assignedStats['overdue_tasks'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card employee-kpi-card">
                        <div class="card-body">
                            <div class="employee-kpi-icon"><i class="feather icon-plus-square"></i></div>
                            <span class="employee-kpi-label">Tasks Created</span>
                            <h3 class="employee-kpi-value"><?php echo (int)($createdTaskStats['total_created'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tender activity summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="employee-section-title"><i class="feather icon-file-text"></i> Tender Activity</h5>
                    <small class="text-muted">
                        Legacy tender records are attributed using the existing <code>updated_by</code> field.
                    </small>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 col-md">
                            <h4 class="mb-1"><?php echo (int)($tenderStats['total_tenders'] ?? 0); ?></h4>
                            <span class="text-muted">Total Handled</span>
                        </div>
                        <div class="col-6 col-md">
                            <h4 class="mb-1 text-danger"><?php echo (int)($tenderStats['requested_tenders'] ?? 0); ?></h4>
                            <span class="text-muted">Requested</span>
                        </div>
                        <div class="col-6 col-md">
                            <h4 class="mb-1 text-warning"><?php echo (int)($tenderStats['sent_tenders'] ?? 0); ?></h4>
                            <span class="text-muted">Sent</span>
                        </div>
                        <div class="col-6 col-md">
                            <h4 class="mb-1 text-info"><?php echo (int)($tenderStats['allotted_tenders'] ?? 0); ?></h4>
                            <span class="text-muted">Allotted</span>
                        </div>
                        <div class="col-6 col-md">
                            <h4 class="mb-1 text-success"><?php echo (int)($tenderStats['awarded_tenders'] ?? 0); ?></h4>
                            <span class="text-muted">Awarded</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Assigned Tasks -->
                <div class="col-xl-7">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="employee-section-title"><i class="feather icon-check-square"></i> Recent Assigned Tasks</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($assignedTasks)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Task</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                                <th>Due</th>
                                                <th>Tender</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($assignedTasks as $task): ?>
                                                <tr>
                                                    <td>
                                                        <strong>#<?php echo (int)$task['id']; ?> <?php echo htmlspecialchars($task['title']); ?></strong>
                                                        <small class="d-block text-muted">Created by <?php echo htmlspecialchars($task['creator_name'] ?: 'Unknown'); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($task['priority']); ?></td>
                                                    <td>
                                                        <span class="status-pill <?php echo statusBadgeClass($task['status']); ?>">
                                                            <?php echo htmlspecialchars($task['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo safeDate($task['due_date']); ?></td>
                                                    <td>
                                                        <?php if (!empty($task['tenderID'])): ?>
                                                            <?php echo htmlspecialchars($task['tenderID']); ?>
                                                            <small class="d-block text-muted"><?php echo htmlspecialchars($task['reference_code'] ?? ''); ?></small>
                                                        <?php else: ?>
                                                            —
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="employee-empty">No assigned tasks found.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Created Tasks -->
                <div class="col-xl-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="employee-section-title"><i class="feather icon-edit-3"></i> Tasks Created by Employee</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recentCreatedTasks)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Task</th>
                                                <th>Assigned To</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentCreatedTasks as $task): ?>
                                                <tr>
                                                    <td>
                                                        <strong>#<?php echo (int)$task['id']; ?></strong>
                                                        <small class="d-block"><?php echo htmlspecialchars($task['title']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($task['assignee_name'] ?: 'Unknown'); ?></td>
                                                    <td>
                                                        <span class="status-pill <?php echo statusBadgeClass($task['status']); ?>">
                                                            <?php echo htmlspecialchars($task['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="employee-empty">No tasks created by this employee.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tender activity -->
            <div class="card">
                <div class="card-header">
                    <h5 class="employee-section-title"><i class="feather icon-briefcase"></i> Recent Tender Requests / Activity</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($tenderActivity)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Tender ID</th>
                                        <th>Tender No</th>
                                        <th>Reference</th>
                                        <th>Department</th>
                                        <th>Section / Division</th>
                                        <th>Status</th>
                                        <th>Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tenderActivity as $tender): ?>
                                        <?php
                                            $displayStatus = ($tender['remark'] === 'accepted') ? 'Awarded' : $tender['status'];
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($tender['tenderID']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($tender['tender_no'] ?: '—'); ?></td>
                                            <td><?php echo htmlspecialchars($tender['reference_code'] ?: '—'); ?></td>
                                            <td><?php echo htmlspecialchars($tender['department_name'] ?: '—'); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($tender['section_name'] ?: '—'); ?>
                                                <small class="d-block text-muted"><?php echo htmlspecialchars($tender['division_name'] ?: ''); ?></small>
                                            </td>
                                            <td>
                                                <span class="status-pill <?php echo statusBadgeClass($displayStatus); ?>">
                                                    <?php echo htmlspecialchars($displayStatus); ?>
                                                </span>
                                            </td>
                                            <td><?php echo safeDate($tender['updated_at'] ?: $tender['created_at'], 'M d, Y h:i A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="employee-empty">No tender activity is attributed to this employee.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Employee task activity -->
            <div class="card">
                <div class="card-header">
                    <h5 class="employee-section-title"><i class="feather icon-activity"></i> Recent Task Activity</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($taskHistory)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Action</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($taskHistory as $history): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo (int)$history['task_id']; ?></strong>
                                                <small class="d-block text-muted"><?php echo htmlspecialchars($history['title'] ?: 'Task'); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($history['action']); ?></td>
                                            <td><?php echo safeDate($history['created_at'], 'M d, Y h:i A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="employee-empty">No recent task activity found.</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
</body>
</html>
