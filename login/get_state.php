<?php
include("db/config.php");
if(!empty($_POST["coutrycode"])) 
{
$query =mysqli_query($db,"SELECT * FROM  division WHERE section_id = '" . $_POST["coutrycode"] . "'");
?>
<option value="">Select State</option>
<?php
while($row=mysqli_fetch_array($query))   
{
?>
<option value="<?php echo $row["division_id"]; ?>"><?php echo $row["division_name"]; ?></option>
<?php
}
}



?>
