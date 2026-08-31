<?php
$file = __DIR__ . '/../login/dashboard.php';
$content = file_get_contents($file);

// 1. Tender KPIs - remove float-left
$content = str_replace('<i class="feather icon-message-square float-left"></i>', '<i class="feather icon-message-square"></i>', $content);
$content = str_replace('<i class="feather icon-mail float-left"></i>', '<i class="feather icon-mail"></i>', $content);
$content = str_replace('<i class="feather icon-user-check float-left"></i>', '<i class="feather icon-user-check"></i>', $content);
$content = str_replace('<i class="feather icon-check-circle float-left"></i>', '<i class="feather icon-check-circle"></i>', $content);

// 2. Members Overview
$content = str_replace('<h3 class="mb-1 text-primary"><?php echo (int)($memberTotalCount', '<h3 class="mb-1 text-primary"><i class="feather icon-users"></i> <?php echo (int)($memberTotalCount', $content);
$content = str_replace('<h3 class="mb-1 text-success"><?php echo (int)($activeMemberRealCount', '<h3 class="mb-1 text-success"><i class="feather icon-user-check"></i> <?php echo (int)($activeMemberRealCount', $content);
$content = str_replace('<h3 class="mb-1 text-danger"><?php echo (int)($inactiveMemberCount', '<h3 class="mb-1 text-danger"><i class="feather icon-user-x"></i> <?php echo (int)($inactiveMemberCount', $content);
$content = str_replace('<h3 class="mb-1 text-info"><?php echo (int)($newMemberCount', '<h3 class="mb-1 text-info"><i class="feather icon-user-plus"></i> <?php echo (int)($newMemberCount', $content);

// 3. Employee Overview
$content = str_replace('<h3 class="mb-1 text-primary"><?php echo (int)($memberCount', '<h3 class="mb-1 text-primary"><i class="feather icon-users"></i> <?php echo (int)($memberCount', $content);
$content = str_replace('<h3 class="mb-1 text-success"><?php echo (int)($activeMemberCount', '<h3 class="mb-1 text-success"><i class="feather icon-user-check"></i> <?php echo (int)($activeMemberCount', $content);

// 4. Task Statistics
$content = str_replace('<h3 class="mb-1"><?php echo (int)($taskStats[\'total_tasks\']', '<h3 class="mb-1"><i class="feather icon-clipboard"></i> <?php echo (int)($taskStats[\'total_tasks\']', $content);
$content = str_replace('<h3 class="mb-1 text-warning"><?php echo (int)($taskStats[\'pending_tasks\']', '<h3 class="mb-1 text-warning"><i class="feather icon-clock"></i> <?php echo (int)($taskStats[\'pending_tasks\']', $content);
$content = str_replace('<h3 class="mb-1 text-info"><?php echo (int)($taskStats[\'in_progress_tasks\']', '<h3 class="mb-1 text-info"><i class="feather icon-loader"></i> <?php echo (int)($taskStats[\'in_progress_tasks\']', $content);
$content = str_replace('<h3 class="mb-1 text-success"><?php echo (int)($taskStats[\'completed_tasks\']', '<h3 class="mb-1 text-success"><i class="feather icon-check-circle"></i> <?php echo (int)($taskStats[\'completed_tasks\']', $content);
$content = str_replace('<h3 class="mb-1 text-danger"><?php echo (int)($taskStats[\'overdue_tasks\']', '<h3 class="mb-1 text-danger"><i class="feather icon-alert-circle"></i> <?php echo (int)($taskStats[\'overdue_tasks\']', $content);

file_put_contents($file, $content);
echo "Icons added.\n";
