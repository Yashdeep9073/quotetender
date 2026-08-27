-- ============================================================================
-- Task Management – Phase 2: schema additions
-- ----------------------------------------------------------------------------
-- Adds task-type and the tender/query relationship to the `tasks` table and
-- creates the RBAC permissions used by the module (following the existing
-- `permissions` / `role_permissions` naming convention).
--
-- The local development database already has these changes applied.
-- Run this file on other environments (e.g. production) before deploying
-- the task-management pages.
-- ============================================================================

USE `quotetender`;

-- 1) Task type + relationship to the EXISTING tender/query table.
--    No tender data is duplicated: only user_tender_requests.id is stored.
ALTER TABLE `tasks`
  ADD COLUMN `task_type` enum('General','Tender/Query') NOT NULL DEFAULT 'General' AFTER `description`,
  ADD COLUMN `tender_request_id` int(11) DEFAULT NULL AFTER `task_type`,
  ADD KEY `idx_tender_request_id` (`tender_request_id`);

ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_tender_request`
  FOREIGN KEY (`tender_request_id`) REFERENCES `user_tender_requests` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- 2) RBAC permissions (existing convention: "Task Management", "Add X", "Edit X", "Delete X")
INSERT INTO `permissions` (`permission_name`, `status`) VALUES
('Task Management', 1),
('Add Task', 1),
('Edit Task', 1),
('Delete Task', 1);

-- 3) Grant to Admin (role 20) and Manager (role 23).
--    Admin role is granted everything by the existing hasPermission() logic;
--    these rows keep the role data consistent for the role-permission UI.
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 20, `permission_id` FROM `permissions`
 WHERE `permission_name` IN ('Task Management', 'Add Task', 'Edit Task', 'Delete Task');

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 23, `permission_id` FROM `permissions`
 WHERE `permission_name` IN ('Task Management', 'Add Task', 'Edit Task', 'Delete Task');
