<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../env.php';


define('DB_SERVER', getenv("DB_SERVER"));
define('DB_USERNAME', getenv("DB_USERNAME"));
define('DB_PASSWORD', getenv("DB_PASSWORD"));
define('DB_NAME', getenv("DB_NAME"));

$db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

mysqli_select_db($db, DB_NAME);

?>