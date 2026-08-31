<?php
session_start();
$_SESSION["login_user"] = "test";
$_SESSION["login_user_id"] = 3; // admin id
include "login/dashboard.php";
if(isset($_SESSION['error'])) {
    echo "ERROR: " . $_SESSION['error'] . "\n";
} else {
    echo "SUCCESS\n";
}
