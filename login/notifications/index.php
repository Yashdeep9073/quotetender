<?php
session_start();
require_once __DIR__ . '/../db/config.php';

if (!isset($_SESSION["login_user_id"])) {
    header("Location: ../index.php");
    exit();
}

$userId = (int)$_SESSION['login_user_id'];
$name = isset($_SESSION['login_user']) ? $_SESSION['login_user'] : 'User';

// Mark all as read if requested via GET
if (isset($_GET['mark_all'])) {
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

$stmt = $db->prepare("SELECT id, type, title, message, reference_type, reference_id, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->bind_param('i', $userId);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Notifications</title>
    <base href="../">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
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
                                <h5 class="m-b-10">Notifications</h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="notifications/index.php">Notifications</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Your Notifications</h5>
                            <a href="notifications/index.php?mark_all=1" class="btn btn-sm btn-outline-secondary">Mark All as Read</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Title</th>
                                            <th>Message</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($notifications)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No notifications</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($notifications as $n): ?>
                                                <?php 
                                                    $isRead = (int)$n['is_read'] === 1;
                                                    $fw = $isRead ? '' : 'font-weight-bold';
                                                    $url = 'notifications/read.php?id=' . $n['id'];
                                                ?>
                                                <tr class="<?php echo $isRead ? '' : 'table-light'; ?>">
                                                    <td>
                                                        <?php if ($isRead): ?>
                                                            <span class="badge badge-light-secondary">Read</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-primary">Unread</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="<?php echo $fw; ?>"><?php echo htmlspecialchars($n['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($n['message']); ?></td>
                                                    <td><span class="badge badge-light-info"><?php echo htmlspecialchars($n['type']); ?></span></td>
                                                    <td><?php echo date('d M Y, h:i A', strtotime($n['created_at'])); ?></td>
                                                    <td>
                                                        <a href="<?php echo $url; ?>" class="btn btn-sm btn-primary">
                                                            View
                                                        </a>
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
</body>
</html>
