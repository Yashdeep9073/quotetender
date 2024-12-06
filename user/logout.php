<?php
session_start();

unset($_SESSION["login_register"]);
// session_destroy();


header('Location: ../index.php');

?>