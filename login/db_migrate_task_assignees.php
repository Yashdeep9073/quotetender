<?php
require 'db/config.php';
$sql = "
CREATE TABLE IF NOT EXISTS `task_assignees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_task_employee` (`task_id`,`employee_id`),
  CONSTRAINT `fk_ta_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ta_employee` FOREIGN KEY (`employee_id`) REFERENCES `admin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if(mysqli_query($db, $sql)) {
    echo "Migration successful.\n";
} else {
    echo "Error: " . mysqli_error($db) . "\n";
}
