<?php
include("db/config.php");
if(!empty($_POST["statecode"])) 
{
$statecode=$_POST["statecode"];
$query1 =mysqli_query($db,"SELECT sub_division.id ,sub_division.subdivision FROM sub_division INNER JOIN division ON sub_division.division_id = division.division_id
	WHERE sub_division.division_id  = '$statecode'");
?>
<option value="">Select Subsection </option>
<?php
while($row1=mysqli_fetch_array($query1))  
{
?>
<option value="<?php echo $row1["id"]; ?>"><?php echo $row1["subdivision"]; ?></option>
<?php
}
}
?>