CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `type` VARCHAR(100) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `reference_type` VARCHAR(50) DEFAULT NULL,
    `reference_id` INT(11) DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `read_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notifications_user` (`user_id`),
    KEY `idx_notifications_user_read` (`user_id`, `is_read`),
    KEY `idx_notifications_reference` (`reference_type`, `reference_id`),
    KEY `idx_notifications_created` (`created_at`),
    CONSTRAINT `fk_notifications_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `admin` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Per-user notification preferences
-- ============================================================
CREATE TABLE IF NOT EXISTS `notification_settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,

    `user_id` INT(11) NOT NULL,

    `email_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `in_app_enabled` TINYINT(1) NOT NULL DEFAULT 1,

    `task_assigned` TINYINT(1) NOT NULL DEFAULT 1,
    `task_reassigned` TINYINT(1) NOT NULL DEFAULT 1,
    `task_status_changed` TINYINT(1) NOT NULL DEFAULT 1,
    `task_updated` TINYINT(1) NOT NULL DEFAULT 1,
    `task_commented` TINYINT(1) NOT NULL DEFAULT 1,
    `task_due_soon` TINYINT(1) NOT NULL DEFAULT 1,
    `task_overdue` TINYINT(1) NOT NULL DEFAULT 1,

    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),

    UNIQUE KEY `uk_notification_settings_user` (`user_id`),

    CONSTRAINT `fk_notification_settings_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `admin` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- SMTP configuration (no credentials inserted; provided later)
-- ============================================================
CREATE TABLE IF NOT EXISTS `smtp_settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,

    `host` VARCHAR(255) NOT NULL,
    `port` INT(11) NOT NULL DEFAULT 587,

    `username` VARCHAR(255) DEFAULT NULL,
    `password` TEXT DEFAULT NULL,

    `encryption` VARCHAR(20) NOT NULL DEFAULT 'tls',

    `from_email` VARCHAR(255) NOT NULL,
    `from_name` VARCHAR(255) NOT NULL,

    `is_active` TINYINT(1) NOT NULL DEFAULT 1,

    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),

    KEY `idx_smtp_active` (`is_active`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
