<?php

session_start();
include("db/config.php");

    $id=$_GET['id'];
   
  $sql="DELETE from members where member_id = '$id'";
	$result=mysqli_query($db,$sql);
  



?>

