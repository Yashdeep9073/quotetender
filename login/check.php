<?php
include_once("db/config.php");
if(!empty($_POST["username"])) {
  $_POST["username"] = trim($_POST["username"]);
  $sql = "SELECT username FROM admin WHERE username='" . $_POST["username"] . "'";
  $resultset = mysqli_query($db, $sql) or die("database error:". mysqli_error($db));
  $row = mysqli_fetch_assoc($resultset);		
  if($row['username']) {
		echo "1";
  } else {
  		echo "0";      
  }
}

?>