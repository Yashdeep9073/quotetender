<?php
session_start();
include("db/config.php");
error_reporting(1);
if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
    exit();
}

$adminId = $_SESSION['login_user_id'] ?? 0;

// Safe defaults so the charts never receive null even if a query fails.
$tenderDist    = [];
$monthlyTrend  = [];
$taskDist      = [];
$recentTenders = [];
$recentTasks   = [];
$memberStatusDist = [];
$memberTrend      = [];
$recentMembers    = [];

try {
    // 1. Tender Request
    $stmtFetchTenderRequested = $db->prepare("SELECT COUNT(*) AS total FROM (SELECT MIN(id) AS min_id FROM user_tender_requests WHERE status = 'Requested' AND delete_tender = '0' GROUP BY tenderID) x");
    $stmtFetchTenderRequested->execute();
    $tenderRequestedCount = $stmtFetchTenderRequested->get_result()->fetch_array(MYSQLI_ASSOC);

    // 2. Sent Tender
    $stmtFetchTenderSent = $db->prepare("SELECT COUNT(*) AS total FROM (SELECT MIN(sent.id) AS min_id FROM user_tender_requests sent WHERE sent.status = 'Sent' AND sent.delete_tender = '0' AND NOT EXISTS (SELECT 1 FROM user_tender_requests a WHERE a.tenderID = sent.tenderID AND a.status = 'Allotted' AND a.delete_tender = '0') GROUP BY sent.tenderID) x");
    $stmtFetchTenderSent->execute();
    $tenderSentCount = $stmtFetchTenderSent->get_result()->fetch_array(MYSQLI_ASSOC);

    // 3. Allot Tender
    $stmtFetchTenderAllotted = $db->prepare("SELECT COUNT(*) AS total FROM user_tender_requests WHERE status = 'Allotted' AND delete_tender = '0' AND (remark IS NULL OR remark != 'accepted')");
    $stmtFetchTenderAllotted->execute();
    $tenderAllottedCount = $stmtFetchTenderAllotted->get_result()->fetch_array(MYSQLI_ASSOC);

    // 4. Confirm Tender
    $stmtFetchTenderAwarded = $db->prepare("SELECT COUNT(*) AS total FROM user_tender_requests WHERE remark = 'accepted' AND delete_tender = '0'");
    $stmtFetchTenderAwarded->execute();
    $tenderAwardedCount = $stmtFetchTenderAwarded->get_result()->fetch_array(MYSQLI_ASSOC);

    // Members Stats
    $stmtFetchMemberTotal = $db->prepare("SELECT COUNT(*) AS total FROM members");
    $stmtFetchMemberTotal->execute();
    $memberTotalCount = $stmtFetchMemberTotal->get_result()->fetch_array(MYSQLI_ASSOC);
    
    $stmtFetchActiveMemberReal = $db->prepare("SELECT COUNT(*) AS total FROM members WHERE status = '1'");
    $stmtFetchActiveMemberReal->execute();
    $activeMemberRealCount = $stmtFetchActiveMemberReal->get_result()->fetch_array(MYSQLI_ASSOC);

    $stmtFetchInactiveMember = $db->prepare("SELECT COUNT(*) AS total FROM members WHERE status != '1'");
    $stmtFetchInactiveMember->execute();
    $inactiveMemberCount = $stmtFetchInactiveMember->get_result()->fetch_array(MYSQLI_ASSOC);

    $stmtFetchNewMember = $db->prepare("
        SELECT COUNT(*) AS total 
        FROM members 
        WHERE STR_TO_DATE(created_date, '%Y-%m-%d %h:%i:%s %p') >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");
    $stmtFetchNewMember->execute();
    $newMemberCount = $stmtFetchNewMember->get_result()->fetch_array(MYSQLI_ASSOC);

    // Employee Stats
    $stmtFetchMember = $db->prepare("SELECT count(*) AS total FROM admin");
    $stmtFetchMember->execute();
    $memberCount = $stmtFetchMember->get_result()->fetch_array(MYSQLI_ASSOC);
    
    $stmtFetchActiveMember = $db->prepare("SELECT count(*) AS total FROM admin WHERE status = 1");
    $stmtFetchActiveMember->execute();
    $activeMemberCount = $stmtFetchActiveMember->get_result()->fetch_array(MYSQLI_ASSOC);

    // Role Check for Tasks
    $stmtAdmin = $db->prepare("SELECT r.role_name FROM admin a JOIN roles r ON a.role_id = r.role_id WHERE a.id = ?");
    $stmtAdmin->bind_param('i', $adminId);
    $stmtAdmin->execute();
    $adminRow = $stmtAdmin->get_result()->fetch_array(MYSQLI_ASSOC);
    $isDashAdmin = ($adminRow && strtolower($adminRow['role_name']) === 'admin');

    // Task KPIs
    if ($isDashAdmin) {
        $stmtTaskStats = $db->prepare("
            SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_tasks,
                SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_tasks,
                SUM(CASE WHEN due_date IS NOT NULL AND due_date < CURDATE() AND status NOT IN ('Completed', 'Cancelled') THEN 1 ELSE 0 END) as overdue_tasks
            FROM tasks
        ");
        $stmtTaskStats->execute();
    } else {
        $stmtTaskStats = $db->prepare("
            SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_tasks,
                SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_tasks,
                SUM(CASE WHEN due_date IS NOT NULL AND due_date < CURDATE() AND status NOT IN ('Completed', 'Cancelled') THEN 1 ELSE 0 END) as overdue_tasks
            FROM tasks WHERE assigned_to = ?
        ");
        $stmtTaskStats->bind_param('i', $adminId);
        $stmtTaskStats->execute();
    }
    $taskStats = $stmtTaskStats->get_result()->fetch_array(MYSQLI_ASSOC);

    // Employee Performance
    $stmtEmpPerformance = $db->prepare("
        SELECT
            a.id,
            a.username,
            COUNT(t.id) AS assigned_tasks,
            SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) AS completed_tasks,
            SUM(
                CASE
                    WHEN t.due_date IS NOT NULL
                     AND t.due_date < CURDATE()
                     AND t.status NOT IN ('Completed', 'Cancelled')
                    THEN 1
                    ELSE 0
                END
            ) AS overdue_tasks
        FROM admin a
        LEFT JOIN tasks t ON t.assigned_to = a.id
        GROUP BY a.id, a.username
        ORDER BY assigned_tasks DESC
        LIMIT 10
    ");
    $stmtEmpPerformance->execute();
    $empPerformance = $stmtEmpPerformance->get_result()->fetch_all(MYSQLI_ASSOC);

    // Task Distribution
    if ($isDashAdmin) {
        $stmtTaskDist = $db->prepare("SELECT status, COUNT(*) AS total FROM tasks GROUP BY status ORDER BY total DESC");
        $stmtTaskDist->execute();
    } else {
        $stmtTaskDist = $db->prepare("SELECT status, COUNT(*) AS total FROM tasks WHERE assigned_to = ? GROUP BY status ORDER BY total DESC");
        $stmtTaskDist->bind_param('i', $adminId);
        $stmtTaskDist->execute();
    }
    $taskDist = $stmtTaskDist->get_result()->fetch_all(MYSQLI_ASSOC);

    // Tender Status Distribution (using grouped tenderID to match logic)
    $stmtTenderDist = $db->prepare("
        SELECT
            status,
            COUNT(DISTINCT tenderID) AS total
        FROM user_tender_requests
        WHERE delete_tender = '0'
        GROUP BY status
        ORDER BY total DESC
    ");
    $stmtTenderDist->execute();
    $tenderDist = $stmtTenderDist->get_result()->fetch_all(MYSQLI_ASSOC);

    // Monthly Tender Trend
    $stmtMonthlyTrend = $db->prepare("
        SELECT
            DATE_FORMAT(created_at, '%Y-%m') AS month,
            COUNT(DISTINCT tenderID) AS total
        FROM user_tender_requests
        WHERE delete_tender = '0'
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
        LIMIT 12
    ");
    $stmtMonthlyTrend->execute();
    $monthlyTrend = $stmtMonthlyTrend->get_result()->fetch_all(MYSQLI_ASSOC);

    // Member Status Distribution
    $stmtMemberStatusDist = $db->prepare("
        SELECT
            CASE
                WHEN status = '1' THEN 'Active'
                ELSE 'Inactive'
            END AS member_status,
            COUNT(*) AS total
        FROM members
        GROUP BY
            CASE
                WHEN status = '1' THEN 'Active'
                ELSE 'Inactive'
            END
    ");
    $stmtMemberStatusDist->execute();
    $memberStatusDist = $stmtMemberStatusDist->get_result()->fetch_all(MYSQLI_ASSOC);

    // Member Registration Trend
    $stmtMemberTrend = $db->prepare("
        SELECT
            DATE_FORMAT(
                STR_TO_DATE(created_date, '%Y-%m-%d %h:%i:%s %p'),
                '%Y-%m'
            ) AS month,
            COUNT(*) AS total
        FROM members
        WHERE STR_TO_DATE(created_date, '%Y-%m-%d %h:%i:%s %p') IS NOT NULL
        GROUP BY
            DATE_FORMAT(
                STR_TO_DATE(created_date, '%Y-%m-%d %h:%i:%s %p'),
                '%Y-%m'
            )
        ORDER BY month ASC
        LIMIT 12
    ");
    $stmtMemberTrend->execute();
    $memberTrend = $stmtMemberTrend->get_result()->fetch_all(MYSQLI_ASSOC);

    // Recent Members
    $stmtRecentMembers = $db->prepare("
        SELECT member_id, name, firm_name, mobile, email_id, city_state, state_code, status, created_date
        FROM members
        ORDER BY member_id DESC
        LIMIT 10
    ");
    $stmtRecentMembers->execute();
    $recentMembers = $stmtRecentMembers->get_result()->fetch_all(MYSQLI_ASSOC);

    // Recent Tender Activity
    $stmtRecentTender = $db->prepare("
        SELECT id, tenderID, reference_code, status, created_at
        FROM user_tender_requests
        WHERE delete_tender = '0'
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmtRecentTender->execute();
    $recentTenders = $stmtRecentTender->get_result()->fetch_all(MYSQLI_ASSOC);

    // Recent Task Activity
    $stmtRecentTask = $db->prepare("
        SELECT
            th.id,
            th.task_id,
            th.action,
            th.old_value,
            th.new_value,
            th.created_at,
            a.username
        FROM task_history th
        LEFT JOIN admin a ON a.id = th.user_id
        ORDER BY th.created_at DESC
        LIMIT 10
    ");
    $stmtRecentTask->execute();
    $recentTasks = $stmtRecentTask->get_result()->fetch_all(MYSQLI_ASSOC);

} catch (\Throwable $th) {
    $_SESSION['error'] = $th->getMessage();
    header("Location: dashboard.php");
    exit();
}

$name = $_SESSION['login_user'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <title>Quote Tender - Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="Codedthemes" />

    <link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" />
    
    <!-- Chart.js for charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
</head>

<body class="">

    <?php if (isset($_SESSION['success'])) { ?>
        <script>
            const notyf = new Notyf({
                position: { x: 'center', y: 'top' },
                types: [{ type: 'success', background: '#26c975', textColor: '#FFFFFF', dismissible: true, duration: 10000 }]
            });
            notyf.success("<?php echo $_SESSION['success']; ?>");
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php } ?>

    <?php if (isset($_SESSION['error'])) { ?>
        <script>
            const notyf = new Notyf({
                position: { x: 'center', y: 'top' },
                types: [{ type: 'error', background: '#ff1916', textColor: '#FFFFFF', dismissible: true, duration: 10000 }]
            });
            notyf.error("<?php echo $_SESSION['error']; ?>");
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php } ?>

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
                    <div class="search-bar">
                        <button type="button" class="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </li>
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
        <div class="pcoded-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5 style="color:#006666; font-size:24px; font-weight:500;">
                                            <i class="feather icon-clock"></i> &nbsp; <span id='ct6' style="color:#006666; font-size:24px; font-weight:500; letter-spacing:2px;"></span>
                                        </h5>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 style="color:#006666; font-size:22px; font-weight:500;">Welcome: &nbsp;<?php echo htmlspecialchars($name); ?></h6>
                                    </div>
                                </div>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#!">Dashboard</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            if (isset($_GET['loginin'])) {
                $st = $_GET['loginin'];
                $st1 = base64_decode($st);
                if ($st1 > 0) {
                    echo " <div class='alert alert-success alert-dismissible fade show' role='alert' style='font-size:16px;' id='logged'>
                        <strong><i class='feather icon-check'></i>Welcome!</strong> User has been Login Successfully.
                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                        </div> ";
                }
            }
            ?>

            <!-- ROW 1: TENDER KPIs -->
            <div class="row">
                <div class="col-md-6 col-xl-3">
                    <div class="card bg-c-red order-card">
                        <a href="tender-request2.php">
                            <div class="card-body">
                                <h6 class="text-white">Tender Request</h6>
                                <h2 class="text-right text-white">
                                    <i class="feather icon-message-square"></i>
                                    <span>
                                        <?php echo ($isAdmin || hasPermission('Dashboard Tenders Request Count', $privileges, $roleData['role_name'])) ? (int)($tenderRequestedCount['total'] ?? 0) : 0; ?>
                                    </span>
                                </h2>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card bg-c-yellow order-card">
                        <a href="sent-tender2.php">
                            <div class="card-body">
                                <h6 class="text-white">Sent Tender</h6>
                                <h2 class="text-right text-white">
                                    <i class="feather icon-mail"></i>
                                    <span>
                                        <?php echo ($isAdmin || hasPermission('Dashboard Sent Tenders Count', $privileges, $roleData['role_name'])) ? (int)($tenderSentCount['total'] ?? 0) : 0; ?>
                                    </span>
                                </h2>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card bg-c-orange order-card" style="background: linear-gradient(to right, #ff8c00, #ffba56);">
                        <a href="alot-tender.php">
                            <div class="card-body">
                                <h6 class="text-white">Alot Tender</h6>
                                <h2 class="text-right text-white">
                                    <i class="feather icon-user-check"></i>
                                    <span>
                                        <?php echo ($isAdmin || hasPermission('Dashboard Alot Tenders Count', $privileges, $roleData['role_name'])) ? (int)($tenderAllottedCount['total'] ?? 0) : 0; ?>
                                    </span>
                                </h2>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card bg-c-green order-card">
                        <a href="alot-tender.php"> <!-- Or link to confirm-tender page if exists -->
                            <div class="card-body">
                                <h6 class="text-white">Confirm Tender</h6>
                                <h2 class="text-right text-white">
                                    <i class="feather icon-check-circle"></i>
                                    <span>
                                        <?php echo ($isAdmin || hasPermission('Dashboard Awarded Tenders Count', $privileges, $roleData['role_name'])) ? (int)($tenderAwardedCount['total'] ?? 0) : 0; ?>
                                    </span>
                                </h2>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ROW 1.5: Members Summary -->
            <?php if ($isAdmin || hasPermission('Dashboard Registered Members Count', $privileges, $roleData['role_name'])): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="feather icon-users"></i> Members Overview</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="row text-center">
                                <div class="col">
                                    <h3 class="mb-1 text-primary"><i class="feather icon-users"></i> <?php echo (int)($memberTotalCount['total'] ?? 0); ?></h3>
                                    <span class="text-muted">Total Members</span>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 text-success"><i class="feather icon-user-check"></i> <?php echo (int)($activeMemberRealCount['total'] ?? 0); ?></h3>
                                    <span class="text-muted">Active Members</span>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 text-danger"><i class="feather icon-user-x"></i> <?php echo (int)($inactiveMemberCount['total'] ?? 0); ?></h3>
                                    <span class="text-muted">Inactive Members</span>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 text-info"><i class="feather icon-user-plus"></i> <?php echo (int)($newMemberCount['total'] ?? 0); ?></h3>
                                    <span class="text-muted">New This Month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ROW 2: Employee + Task Summary -->
            <div class="row">
                <!-- Employee Summary (Only for Admins / Privileged) -->
                <?php if ($isAdmin || hasPermission('Dashboard Employee Stats', $privileges, $roleData['role_name'])): ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="feather icon-user-check"></i> Employee Overview</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <h3 class="mb-1 text-primary"><i class="feather icon-users"></i> <?php echo (int)($memberCount['total'] ?? 0); ?></h3>
                                    <span class="text-muted">Total Employees</span>
                                </div>
                                <div class="col-6 text-center">
                                    <h3 class="mb-1 text-success"><i class="feather icon-user-check"></i> <?php echo (int)($activeMemberCount['total'] ?? 0); ?></h3>
                                    <span class="text-muted">Active Employees</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Task Summary -->
                <div class="col-md-<?php echo ($isAdmin || hasPermission('Dashboard Employee Stats', $privileges, $roleData['role_name'])) ? '8' : '12'; ?>">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="feather icon-check-square"></i> <?php echo $isDashAdmin ? "Task Statistics" : "My Tasks"; ?></h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="row text-center">
                                <div class="col">
                                    <h3 class="mb-1"><i class="feather icon-clipboard"></i> <?php echo (int)($taskStats['total_tasks'] ?? 0); ?></h3>
                                    <span class="text-muted">Total</span>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 text-warning"><i class="feather icon-clock"></i> <?php echo (int)($taskStats['pending_tasks'] ?? 0); ?></h3>
                                    <span class="text-muted">Pending</span>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 text-info"><i class="feather icon-loader"></i> <?php echo (int)($taskStats['in_progress_tasks'] ?? 0); ?></h3>
                                    <span class="text-muted">In Progress</span>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 text-success"><i class="feather icon-check-circle"></i> <?php echo (int)($taskStats['completed_tasks'] ?? 0); ?></h3>
                                    <span class="text-muted">Completed</span>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 text-danger"><i class="feather icon-alert-circle"></i> <?php echo (int)($taskStats['overdue_tasks'] ?? 0); ?></h3>
                                    <span class="text-muted">Overdue</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 3: CHARTS -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Tender Pipeline</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="tenderChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Monthly Tender Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="trendChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Task Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="taskChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 3.5: Member Charts -->
            <?php if ($isAdmin || hasPermission('Dashboard Registered Members Count', $privileges, $roleData['role_name'])): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Member Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="memberStatusChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Member Registration Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="memberTrendChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ROW 4 & 5: Tables -->
            <div class="row">
                <!-- Employee Performance -->
                <?php if ($isAdmin || hasPermission('Dashboard Employee Stats', $privileges, $roleData['role_name'])): ?>
                <div class="col-md-6">
                    <div class="card table-card">
                        <div class="card-header">
                            <h5>Employee Performance</h5>
                        </div>
                        <div class="card-body px-0 py-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th class="text-center">Assigned</th>
                                            <th class="text-center">Completed</th>
                                            <th class="text-center text-danger">Overdue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($empPerformance as $emp): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($emp['username']); ?></td>
                                            <td class="text-center"><?php echo (int)$emp['assigned_tasks']; ?></td>
                                            <td class="text-center text-success"><?php echo (int)$emp['completed_tasks']; ?></td>
                                            <td class="text-center text-danger"><?php echo (int)$emp['overdue_tasks']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($empPerformance)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">No task data available</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent Tender Activity -->
                <div class="col-md-6">
                    <div class="card table-card">
                        <div class="card-header">
                            <h5>Recent Tender Activity</h5>
                        </div>
                        <div class="card-body px-0 py-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tender ID</th>
                                            <th>Reference</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentTenders as $t): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($t['tenderID']); ?></td>
                                            <td><?php echo htmlspecialchars($t['reference_code']); ?></td>
                                            <td><span class="badge badge-primary"><?php echo htmlspecialchars($t['status']); ?></span></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($t['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($recentTenders)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">No tender activity found</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Task Activity -->
                <div class="col-md-12 mt-3">
                    <div class="card table-card">
                        <div class="card-header">
                            <h5>Recent Task Activity</h5>
                        </div>
                        <div class="card-body px-0 py-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Task ID</th>
                                            <th>Action</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentTasks as $taskLog): ?>
                                        <tr>
                                            <td><i class="feather icon-user"></i> <?php echo htmlspecialchars($taskLog['username'] ?? 'Unknown'); ?></td>
                                            <td>#<?php echo htmlspecialchars($taskLog['task_id']); ?></td>
                                            <td><?php echo htmlspecialchars($taskLog['action']); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($taskLog['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($recentTasks)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">No task activity found</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
<!-- Recent Members -->
                <?php if ($isAdmin || hasPermission('Dashboard Registered Members Count', $privileges, $roleData['role_name'])): ?>
                <div class="col-md-12 mt-3">
                    <div class="card table-card">
                        <div class="card-header">
                            <h5>Recent Members</h5>
                        </div>
                        <div class="card-body px-0 py-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Member</th>
                                            <th>Firm</th>
                                            <th>Mobile</th>
                                            <th>State</th>
                                            <th>Status</th>
                                            <th>Registered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentMembers as $mem): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($mem['name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($mem['firm_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($mem['mobile'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($mem['state_code'] ?? ''); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo ($mem['status'] == '1') ? 'success' : 'danger'; ?>">
                                                    <?php echo ($mem['status'] == '1') ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($mem['created_date'] ?? ''); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($recentMembers)): ?>
                                        <tr><td colspan="6" class="text-center text-muted">No recent members found</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="assets/js/plugins/jquery.dataTables.min.js"></script>
    <script src="assets/js/plugins/dataTables.bootstrap4.min.js"></script>
    
    <!-- Render Charts using Chart.js -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tender Chart
            var tenderCtx = document.getElementById('tenderChart').getContext('2d');
            var tenderLabels = <?php echo json_encode(array_column(is_array($tenderDist) ? $tenderDist : [], 'status')); ?>;
            var tenderData = <?php echo json_encode(array_column(is_array($tenderDist) ? $tenderDist : [], 'total')); ?>;
            new Chart(tenderCtx, {
                type: 'doughnut',
                data: {
                    labels: tenderLabels,
                    datasets: [{
                        data: tenderData,
                        backgroundColor: ['#ff5252', '#ffb74d', '#ff8c00', '#26c975', '#4099ff', '#73b4ff']
                    }]
                },
                options: { maintainAspectRatio: false }
            });

            // Monthly Trend Chart
            var trendCtx = document.getElementById('trendChart').getContext('2d');
            var trendLabels = <?php echo json_encode(array_column(is_array($monthlyTrend) ? $monthlyTrend : [], 'month')); ?>;
            var trendData = <?php echo json_encode(array_column(is_array($monthlyTrend) ? $monthlyTrend : [], 'total')); ?>;
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Tenders',
                        data: trendData,
                        borderColor: '#4099ff',
                        fill: false,
                        tension: 0.1
                    }]
                },
                options: { maintainAspectRatio: false }
            });

            // Task Chart
            var taskCtx = document.getElementById('taskChart').getContext('2d');
            var taskLabels = <?php echo json_encode(array_column(is_array($taskDist) ? $taskDist : [], 'status')); ?>;
            var taskData = <?php echo json_encode(array_column(is_array($taskDist) ? $taskDist : [], 'total')); ?>;
            new Chart(taskCtx, {
                type: 'bar',
                data: {
                    labels: taskLabels,
                    datasets: [{
                        label: 'Tasks',
                        data: taskData,
                        backgroundColor: '#26c975'
                    }]
                },
                options: { maintainAspectRatio: false }
            });
           // Member Status Chart
            var memberStatusCtx = document.getElementById('memberStatusChart');
            if (memberStatusCtx) {
                var memberStatusLabels = <?php echo json_encode(array_column(is_array($memberStatusDist) ? $memberStatusDist : [], 'member_status')); ?>;
                var memberStatusData = <?php echo json_encode(array_column(is_array($memberStatusDist) ? $memberStatusDist : [], 'total')); ?>;
                new Chart(memberStatusCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: memberStatusLabels,
                        datasets: [{
                            data: memberStatusData,
                            backgroundColor: ['#26c975', '#ff5252']
                        }]
                    },
                    options: { maintainAspectRatio: false }
                });
            }

            // Member Trend Chart
            var memberTrendCtx = document.getElementById('memberTrendChart');
            if (memberTrendCtx) {
                var memberTrendLabels = <?php echo json_encode(array_column(is_array($memberTrend) ? $memberTrend : [], 'month')); ?>;
                var memberTrendData = <?php echo json_encode(array_column(is_array($memberTrend) ? $memberTrend : [], 'total')); ?>;
                new Chart(memberTrendCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: memberTrendLabels,
                        datasets: [{
                            label: 'New Members',
                            data: memberTrendData,
                            borderColor: '#ffba56',
                            fill: false,
                            tension: 0.1
                        }]
                    },
                    options: { maintainAspectRatio: false }
                });
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#logged").delay(5000).slideUp(300);
        });

        function display_ct6() {
            var x = new Date()
            var ampm = x.getHours() >= 12 ? ' PM' : ' AM';
            var hours = x.getHours() % 12;
            hours = hours ? hours : 12;
            var x1 = (x.getMonth() + 1) + "-" + x.getDate() + "-" + x.getFullYear();
            x1 = x1 + " - " + hours + ":" + (x.getMinutes() < 10 ? '0' : '') + x.getMinutes() + ":" + (x.getSeconds() < 10 ? '0' : '') + x.getSeconds() + ampm;
            document.getElementById('ct6').innerHTML = x1;
            display_c6();
        }

        function display_c6() {
            var refresh = 1000;
            setTimeout('display_ct6()', refresh)
        }
        display_c6();
    </script>
</body>
</html>
