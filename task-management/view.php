<?php
require_once __DIR__ . '/inc/init.php';

$taskId = task_get_int(isset($_GET['id']) ? $_GET['id'] : null);
if ($taskId === false) {
    task_redirect('index.php', 'danger', 'Invalid task ID.');
}

$task = task_load($db, $taskId);
if (!task_can_view_row($task, $taskUserId, $taskCanViewAll)) {
    task_redirect('index.php', 'danger', 'Task not found or you do not have permission to view it.');
}

// ---------- POST actions ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? (string) $_POST['action'] : '';

    if ($action === 'status') {
        // Employees may update the status of their own tasks; managers/admins any task.
        $canUpdateStatus = $taskCanEdit || ((int) $task['assigned_to'] === $taskUserId);
        if (!$canUpdateStatus) {
            task_redirect('view.php?id=' . $taskId, 'danger', 'You do not have permission to update this task status.');
            return;
        }
        $newStatus = isset($_POST['status']) ? (string) $_POST['status'] : '';
        if (!in_array($newStatus, task_statuses(), true)) {
            task_redirect('view.php?id=' . $taskId, 'danger', 'Invalid status.');
            return;
        }
        if ($newStatus !== $task['status']) {
            $stmtUpd = $db->prepare("UPDATE tasks SET status = ? WHERE id = ?");
            $stmtUpd->bind_param('si', $newStatus, $taskId);
            if ($stmtUpd->execute()) {
                task_log_history($db, $taskId, $taskUserId, 'Status changed', $task['status'], $newStatus);
                task_redirect('view.php?id=' . $taskId, 'success', 'Task status updated.');
            } else {
                task_redirect('view.php?id=' . $taskId, 'danger', 'Failed to update task status.');
            }
            return;
        }
        task_redirect('view.php?id=' . $taskId);
        return;
    }

    if ($action === 'comment') {
        $comment = isset($_POST['comment']) ? trim((string) $_POST['comment']) : '';
        if ($comment === '') {
            task_redirect('view.php?id=' . $taskId, 'danger', 'Comment cannot be empty.');
            return;
        }
        if (mb_strlen($comment) > 5000) {
            task_redirect('view.php?id=' . $taskId, 'danger', 'Comment is too long (max 5000 characters).');
            return;
        }
        $stmtComment = $db->prepare("INSERT INTO task_comments (task_id, user_id, comment) VALUES (?, ?, ?)");
        $stmtComment->bind_param('iis', $taskId, $taskUserId, $comment);
        if ($stmtComment->execute()) {
            task_redirect('view.php?id=' . $taskId, 'success', 'Comment added.');
        } else {
            task_redirect('view.php?id=' . $taskId, 'danger', 'Failed to add the comment.');
        }
        return;
    }

    task_redirect('view.php?id=' . $taskId);
}

// ---------- Related data ----------
$stmtComments = $db->prepare(
    "SELECT c.comment, c.created_at, a.username
       FROM task_comments c
       JOIN admin a ON a.id = c.user_id
      WHERE c.task_id = ?
      ORDER BY c.created_at ASC, c.id ASC"
);
$stmtComments->bind_param('i', $taskId);
$stmtComments->execute();
$comments = $stmtComments->get_result()->fetch_all(MYSQLI_ASSOC);

$stmtHistory = $db->prepare(
    "SELECT h.action, h.old_value, h.new_value, h.created_at, a.username
       FROM task_history h
       JOIN admin a ON a.id = h.user_id
      WHERE h.task_id = ?
      ORDER BY h.created_at DESC, h.id DESC"
);
$stmtHistory->bind_param('i', $taskId);
$stmtHistory->execute();
$history = $stmtHistory->get_result()->fetch_all(MYSQLI_ASSOC);

$canUpdateStatus = $taskCanEdit || ((int) $task['assigned_to'] === $taskUserId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Task</title>
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
                                <h5 class="m-b-10">Task Details</h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="../task-management/index.php">Tasks</a></li>
                                <li class="breadcrumb-item"><a href="#!">View Task #<?php echo (int) $task['id']; ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <?php task_render_flash(); ?>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Task Information -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Task #<?php echo (int) $task['id']; ?>: <?php echo e($task['title']); ?></h5>
                            <div>
                                <?php if ($taskCanEdit): ?>
                                    <a href="../task-management/edit.php?id=<?php echo (int) $task['id']; ?>" class="btn btn-warning btn-sm"><i class="feather icon-edit"></i> Edit Task</a>
                                <?php endif; ?>
                                <?php if ($taskCanDelete): ?>
                                    <form action="../task-management/delete.php" method="post" class="d-inline delete-task-form">
                                        <input type="hidden" name="id" value="<?php echo (int) $task['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="feather icon-trash-2"></i> Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> <span class="badge <?php echo task_status_badge($task['status']); ?>"><?php echo e($task['status']); ?></span></p>
                                    <p><strong>Priority:</strong> <span class="badge <?php echo task_priority_badge($task['priority']); ?>"><?php echo e($task['priority']); ?></span></p>
                                    <p>
                                        <strong>Task Type:</strong>
                                        <?php if ($task['task_type'] === 'Tender/Query'): ?>
                                            <span class="badge badge-primary">Tender/Query</span>
                                        <?php else: ?>
                                            <span class="badge badge-light">General</span>
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>Task Assigned To:</strong> <?php echo e($task['assigned_username'] ?? '—'); ?></p>
                                    <p><strong>Task Created By:</strong> <?php echo e($task['created_username'] ?? '—'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Start Date:</strong> <?php echo fmt($task['start_date']); ?></p>
                                    <p><strong>Due Date:</strong> <?php echo fmt($task['due_date']); ?></p>
                                    <p><strong>Created Date:</strong> <?php echo fmt($task['created_at'], true); ?></p>
                                    <p><strong>Updated Date:</strong> <?php echo fmt($task['updated_at'], true); ?></p>
                                </div>
                            </div>

                            <?php if ($task['task_type'] === 'Tender/Query'): ?>
                                <hr>
                                <h6 class="mb-3">Related Tender/Query</h6>
                                <?php if ($task['tender_id_number'] !== null): ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Related Tender:</strong>
                                                <a href="../login/sent-edit.php?id=<?php echo base64_encode((string) $task['tender_request_id']); ?>">
                                                    <?php echo e($task['tender_id_number']); ?>
                                                </a>
                                            </p>
                                            <p><strong>Reference Number:</strong> <?php echo e($task['tender_reference_code'] ?: '—'); ?></p>
                                            <p><strong>Tender Status:</strong> <?php echo e($task['tender_status'] ?: '—'); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Tender Registered By:</strong>
                                                <?php echo e(trim((string) $task['tender_member_name']));
                                                if ($task['tender_member_firm'] !== null && $task['tender_member_firm'] !== '') {
                                                    echo ' (' . e($task['tender_member_firm']) . ')';
                                                } ?>
                                            </p>
                                            <p><strong>Tender Created Date:</strong> <?php echo fmt($task['tender_created_at'], true); ?></p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">The linked tender/query record no longer exists.</p>
                                <?php endif; ?>
                            <?php endif; ?>

                            <hr>
                            <h6 class="mb-3">Description</h6>
                            <p class="text-muted"><?php echo nl2br(e($task['description'] ?? '')); ?></p>
                        </div>
                    </div>

                    <!-- Status Update -->
                    <?php if ($canUpdateStatus): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5>Update Status</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="post" class="form-inline">
                                <input type="hidden" name="action" value="status">
                                <select name="status" class="form-control mr-2">
                                    <?php foreach (task_statuses() as $s): ?>
                                        <option value="<?php echo e($s); ?>" <?php echo $task['status'] === $s ? 'selected' : ''; ?>><?php echo e($s); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary"><i class="feather icon-refresh-cw"></i> Change Status</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Comments Section -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Comments (<?php echo count($comments); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($comments)): ?>
                                <p class="text-muted text-center mb-4">No comments yet.</p>
                            <?php else: ?>
                                <?php foreach ($comments as $comment): ?>
                                    <div class="media mb-4">
                                        <img src="assets/images/user.png" alt="user image" class="img-radius wid-40 align-top m-r-15">
                                        <div class="media-body">
                                            <h6 class="mb-1">
                                                <?php echo e($comment['username']); ?>
                                                <span class="text-muted float-right" style="font-size:12px;"><?php echo fmt($comment['created_at'], true); ?></span>
                                            </h6>
                                            <p class="mb-0 text-muted"><?php echo nl2br(e($comment['comment'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <hr>
                            <form action="" method="post">
                                <input type="hidden" name="action" value="comment">
                                <div class="form-group">
                                    <textarea class="form-control" name="comment" rows="3" placeholder="Add a comment..." required></textarea>
                                </div>
                                <button class="btn btn-primary" type="submit"><i class="feather icon-message-square"></i> Add Comment</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Activity / History -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Activity / History</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($history)): ?>
                                <div class="text-center text-muted p-4">
                                    <em>No history recorded yet.</em>
                                </div>
                            <?php else: ?>
                                <ul class="list-unstyled timeline-style">
                                    <?php foreach ($history as $entry): ?>
                                        <li class="mb-3">
                                            <div class="border rounded p-2">
                                                <strong><?php echo e($entry['action']); ?></strong>
                                                <span class="text-muted float-right" style="font-size:11px;"><?php echo fmt($entry['created_at'], true); ?></span>
                                                <div class="text-muted" style="font-size:12px;">
                                                    by <?php echo e($entry['username']); ?>
                                                    <?php if ($entry['old_value'] !== null || $entry['new_value'] !== null): ?>
                                                        <br>
                                                        <?php if ($entry['old_value'] !== null): ?><span class="text-danger"><?php echo e($entry['old_value']); ?></span> &rarr;<?php endif; ?>
                                                        <?php if ($entry['new_value'] !== null): ?><span class="text-success"><?php echo e($entry['new_value']); ?></span><?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
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
