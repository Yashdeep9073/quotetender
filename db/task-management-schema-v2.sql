-- ============================================================================
-- Task Management – Phase 2: full DB objects (tables + RBAC permissions)
-- ----------------------------------------------------------------------------
-- Creates the `tasks`, `task_comments` and `task_history` tables and the
-- task/tender relationship, plus the RBAC permissions used by the module.
--
-- The `tasks` table links to the EXISTING `user_tender_requests` table via
-- `tender_request_id` (stores only the FK id, no tender data is duplicated).
-- `assigned_to` and `created_by` reference the EXISTING `admin` table.
--
-- Run this once on each environment before deploying the task-management pages.
-- ============================================================================

USE `quotetender`;

-- ---------------------------------------------------------------------------
-- 1) tasks
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `task_type` enum('General','Tender/Query') NOT NULL DEFAULT 'General',
  `tender_request_id` int(11) DEFAULT NULL COMMENT 'FK -> user_tender_requests.id (only for Tender/Query tasks)',
  `created_by` int(11) NOT NULL COMMENT 'FK -> admin.id (task creator, server-set)',
  `assigned_to` int(11) NOT NULL COMMENT 'FK -> admin.id (assigned employee)',
  `priority` enum('Low','Medium','High','Urgent') NOT NULL DEFAULT 'Medium',
  `status` enum('Pending','In Progress','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_assigned_to` (`assigned_to`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_due_date` (`due_date`),
  KEY `idx_tender_request_id` (`tender_request_id`),
  CONSTRAINT `fk_tasks_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `admin` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_created_by` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_tender_request` FOREIGN KEY (`tender_request_id`) REFERENCES `user_tender_requests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------------
-- 2) task_comments
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `task_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_task_comments_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_task_comments_user` FOREIGN KEY (`user_id`) REFERENCES `admin` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------------
-- 3) task_history
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `task_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_task_history_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_task_history_user` FOREIGN KEY (`user_id`) REFERENCES `admin` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------------
-- 4) RBAC permissions (existing convention: "Task Management", "Add X", ...)
--    Idempotent: only inserts if the permission name does not already exist.
-- ---------------------------------------------------------------------------
INSERT INTO `permissions` (`permission_name`, `status`)
SELECT * FROM (
    SELECT 'Task Management' AS n, 1 AS s
    UNION ALL SELECT 'Add Task', 1
    UNION ALL SELECT 'Edit Task', 1
    UNION ALL SELECT 'Delete Task', 1
) AS newperms
WHERE NOT EXISTS (
    SELECT 1 FROM `permissions` p WHERE p.permission_name = newperms.n
);

-- Grant to Admin (role 20) and Manager (role 23), avoiding duplicates.
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 20, `permission_id` FROM `permissions`
 WHERE `permission_name` IN ('Task Management', 'Add Task', 'Edit Task', 'Delete Task');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 23, `permission_id` FROM `permissions`
 WHERE `permission_name` IN ('Task Management', 'Add Task', 'Edit Task', 'Delete Task');
