<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../env.php';


define('DB_SERVER', getenv("DB_SERVER"));
define('DB_USERNAME', getenv("DB_USERNAME"));
define('DB_PASSWORD', getenv("DB_PASSWORD"));
define('DB_NAME', getenv("DB_NAME"));

$db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

mysqli_select_db($db, DB_NAME);


$stmtFetchEmailSettingData = $db->prepare("SELECT * FROM email_settings");
$stmtFetchEmailSettingData->execute();
$emailSettingData = $stmtFetchEmailSettingData->get_result()->fetch_array(MYSQLI_ASSOC);

if ($emailSettingData) {
    // Set environment variables dynamically from database
    putenv("SMTP_HOST=" . $emailSettingData['email_host']);
    putenv("SMTP_USER_NAME=" . $emailSettingData['email_address']);
    putenv("SMTP_PASSCODE=" . $emailSettingData['email_password']);
    putenv("SMTP_PORT=" . $emailSettingData['email_port']);
} else {
    putenv("SMTP_HOST=smtp.hostinger.com");
    putenv('SMTP_USER_NAME=mailerbot@vibrantick.in');
    putenv('SMTP_PASSCODE=Mailerbot@123');
    putenv('SMTP_PORT=465');
}

?>