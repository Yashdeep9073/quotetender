<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../service/ScheduledTaskNotificationService.php';

try {
    $service = new ScheduledTaskNotificationService($db);
    $processed = $service->processDue(25);
    echo "Processed scheduled notifications: " . $processed . PHP_EOL;
} catch (Throwable $e) {
    error_log('Scheduled Notification Cron Failed: ' . $e->getMessage());
    echo 'Scheduled Notification Cron Failed: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
