<?php

session_start();


if (!isset($_SESSION["login_user"]))

{
	header ("location: index.php");
   
}
  $name=$_SESSION['login_user'];

include("db/config.php");

$en=$_GET ["id"];



 $de=base64_decode($en);

$query="SELECT * FROM members WHERE member_id='" . $de . "'";
$result= mysqli_query($db,$query);
$row= mysqli_fetch_row($result);

 
    ?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
<title>Print Form Details</title>



<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="description" content="" />
<meta name="keywords" content="">
<meta name="author" content="Codedthemes" />

<link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

<link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="">

<div class="loader-bg">
<div class="loader-track">
<div class="loader-fill"></div>
</div>
</div>




<nav class="pcoded-navbar menupos-fixed menu-light ">
<div class="navbar-wrapper  ">
<div class="navbar-content scroll-div ">
<ul class="nav pcoded-inner-navbar ">
<li class="nav-item pcoded-menu-caption">
<label>Navigation</label>
</li>
<li class="nav-item">
<a href="dashboard.php" class="nav-link " style="background:#0e7360; color:#fff;"><span class="pcoded-micon"><i class="feather icon-home"></i></span><span class="">Dashboard</span></a>
</li>


<li class="nav-item pcoded-hasmenu">
						<a href="#!" class="nav-link " style="background:#0e7360; color:#fff;"><span class="pcoded-micon"><i class="feather icon-globe"></i></span><span class="pcoded-mtext">Gold</span></a>
						<ul class="pcoded-submenu">
							<li><a href="gold.php">Add Gold Rate</a></li>
							<li><a href="view_gold.php">View Gold Rate</a></li>
							
</ul>
</li>


<li class="nav-item pcoded-hasmenu">
						<a href="#!" class="nav-link " style="background:#0e7360; color:#fff;"><span class="pcoded-micon"><i class="feather icon-file"></i></span><span class="pcoded-mtext">Menu</span></a>
						<ul class="pcoded-submenu">
							<li><a href="#">Add Menu</a></li>
							<li><a href="#">Add Sub Menu</a></li>
                            <li><a href="#">View Menu</a></li>
							
</ul>
</li>

<li class="nav-item pcoded-hasmenu">
						<a href="#!" class="nav-link " style="background:#0e7360; color:#fff;"><span class="pcoded-micon"><i class="feather icon-edit"></i></span><span class="pcoded-mtext">Pages</span></a>
						<ul class="pcoded-submenu">
							<li><a href="#">Add page</a></li>
							<li><a href="#">view Page</a></li>
                            
							
</ul>
</li>

<li class="nav-item">
<a href="reports.php" class="nav-link " style="background:#0e7360; color:#fff;"><span class="pcoded-micon"><i class="feather icon-image"></i></span><span class="">Reports</span></a>
</li>


<li class="nav-item pcoded-hasmenu">
						<a href="#!" class="nav-link " style="background:#0e7360; color:#fff;"><span class="pcoded-micon"><i class="feather icon-users"></i></span><span class="pcoded-mtext">Membership</span></a>
						<ul class="pcoded-submenu">
                        
                        <li><a href="view_member.php">Register Members</a></li>
                          <li><a href="active_member.php">Active Members</a></li>
						
							
							
</ul>
</li>


<li class="nav-item pcoded-hasmenu">
						<a href="#!" class="nav-link " style="background:#0e7360; color:#fff;"><span class="pcoded-micon"><i class="feather icon-users"></i></span><span class="pcoded-mtext">User</span></a>
						<ul class="pcoded-submenu">
                        
                        <li><a href="add-user.php">Add User</a></li>
                          <li><a href="view-user.php">View User</a></li>
						
							
							
</ul>
</li>


<li class="nav-item pcoded-hasmenu">
						<a href="#!" class="nav-link " style="background:#0e7360; color:#fff;"><span class="pcoded-micon"><i class="feather icon-credit-card"></i></span><span class="pcoded-mtext">Payments</span></a>
						<ul class="pcoded-submenu">
                        
                        <li><a href="#">Success Payment</a></li>
                          <li><a href="#">Failure Payment</a></li>
							
							
</ul>
</li>



<li class="nav-item">
<a href="changepass.php" class="nav-link " style="background:#0e7360; color:#fff;"><span class="pcoded-micon"><i class="feather icon-droplet"></i></span><span class="">Change Password</span></a>
</li>	

<li class="nav-item">
<a href="configuration.php" class="nav-link " style="background:#0e7360; color:#fff;"><span class="pcoded-micon"><i class="feather icon-server"></i></span><span class="">Setting</span></a>
</li>	
<li class="nav-item">
<a href="logout.php" class="nav-link " style="background:#0e7360; color:#fff;"><span class="pcoded-micon"><i class="feather icon-power"></i></span><span class="">Log out</span></a>
</li>	                        

</ul>

</div>
</div>
</nav>


<header class="navbar pcoded-header navbar-expand-lg navbar-light headerpos-fixed header-blue">
<div class="m-header">
<a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>
<a href="#!" class="b-brand" style="font-size:24px;">
ADMIN PANEL

</a>
<a href="#!" class="mob-toggler">
<i class="feather icon-more-vertical"></i>
</a>
</div>
<div class="collapse navbar-collapse">
<ul class="navbar-nav mr-auto">
<li class="nav-item">

<div class="search-bar">

<button type="button" class="close" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
</li>
<li class="nav-item">
<a href="#!" class="full-screen" onClick="javascript:toggleFullScreen()"><i class="feather icon-maximize"></i></a>
</li>
</ul>


</div>
</div>
</li>

<div class="dropdown drp-user">
<a href="#!" class="dropdown-toggle" data-toggle="dropdown">
<img src="assets/images/user.png" class="img-radius wid-40" alt="User-Profile-Image">
</a>
<div class="dropdown-menu dropdown-menu-right profile-notification">
<div class="pro-head">
<img src="assets/images/user.png" class="img-radius" alt="User-Profile-Image">
<span><?php echo $name ?></span>
<a href="logout.php" class="dud-logout" title="Logout">
<i class="feather icon-log-out"></i>
</a>
</div>
<ul class="pro-body">
<li><a href="logout.php" class="dropdown-item"><i class="feather icon-lock"></i> Log out</a></li>
</ul>
</div>
</div>
</li>
</ul>
</div>
</header>


<section class="pcoded-main-container">
<div class="pcoded-content">




<div class="row">

<div class="col-sm-12">
<div class="card">


<div class="card-header table-card-header">
 
 
 
</div>
<div class="card-body">
<div class="dt-responsive table-responsive">

<table  border="1"  style="width:100%; border-collapse:collapse;" id="printTable" >
  <tr>
    <td width="15%" height="82"><h1 align="center"><img src="assets/images/print.png" width="132" height="118"></h1></td>
    <td height="82" colspan="5"><div align="center">
      <h1>Brofin Mutual India Nidhi Limited</h1>  <h6>(1857, Front Portion, Ground Floor, Malka Ganj North Delhi-110007 | Website: wwww.brofinindia.com | Contact Us: 1800-891-7932 )   </h6>
    </div></td>
  </tr>
  <tr>
    <td height="41" colspan="2"><div align="center"><strong>Personal Details</strong></div></td>
    <td height="41" colspan="3"><div align="center"><strong>Member Id: </strong></div></td>
    <td width="17%" ><div align="center"></div>
      <?php echo $row['3'];?></td>
  </tr>
  <tr>
    <td  height="55" colspan="2"><div align="center"><strong>Name:</strong></div></td>
    <td colspan="2"><div align="center"></div><?php echo $row['0'];?></td>
    <td width="28%"><div align="center"><strong>Father Name</strong></div></td>
    <td><div align="center"></div><?php echo $row['1'];?></td>
  </tr>
  <tr>
    <td height="56" colspan="2"><div align="center"><strong>Mobile:</strong></div></td>
    <td colspan="2"><div align="center"></div><?php echo $row['2'];?></td>
    <td><div align="center"><strong>Sex</strong></div></td>
    <td><div align="center"></div><?php echo $row['4'];?></td>
  </tr>
  <tr>
    <td height="58" colspan="2"><div align="center"><strong>Date of Birth:</strong></div></td>
    <td colspan="2"><div align="center"></div><?php echo $row['5'];?></td>
    <td><div align="center"><strong>State:</strong></div></td>
    <td><div align="center"></div><?php echo $row['6'];?></td>
  </tr>
  <tr>
    <td height="55" colspan="2"><div align="center"><strong>City:</strong></div></td>
    <td colspan="2"><div align="center"></div><?php echo $row['7'];?></td>
    <td><div align="center"><strong>Education</strong></div></td>
    <td><div align="center"></div><?php echo $row['8'];?></td>
  </tr>
  <tr>
    <td height="56" colspan="2"><div align="center"><strong>Present Occupation </strong></div></td>
    <td colspan="2"><div align="center"></div><?php echo $row['9'];?></td>
    <td><div align="center"><strong>Pan Card Number*</strong></div></td>
    <td><div align="center"></div><?php echo $row['10'];?></td>
  </tr>
  <tr>
    <td height="52" colspan="2"><div align="center"><strong>Identity Proof*</strong></div></td>
    <td colspan="2"><div align="center"></div><?php echo $row['11'];?></td>
    <td><div align="center"><strong>Identity Proof Number*</strong></div></td>
    <td><div align="center"></div><?php echo $row['12'];?></td>
  </tr>
  <tr>
    <td height="36" colspan="2"><div align="center"><strong>Loan Id Type*</strong></div></td>
    <td colspan="2"><div align="center"></div><?php echo $row['13'];?></td>
    <td><div align="center"><strong>Loan Id Number*</strong></div></td>
    <td><div align="center"></div><?php echo $row['14'];?></td>
  </tr>
  <tr>
    <td height="58" colspan="2"><div align="center"><strong>Address Proof*</strong></div></td>
    <td colspan="2"><div align="center"></div><?php echo $row['15'];?></td>
    <td><div align="center"><strong>Address Proof Number *</strong></div></td>
    <td><div align="center"></div><?php echo $row['16'];?></td>
  </tr>
  <tr>
    <td height="51" colspan="2"><div align="center"><strong>Permanent Address*</strong></div></td>
    <td colspan="4"><div align="center"></div><?php echo $row['17'];?></td>
  </tr>
  
  <tr>
    <td height="79" colspan="2"><div align="center"><strong>Date:</strong></div></td>
    <td width="18%"><div align="center"></div><?php echo date("d-m-Y");?></td>
    <td width="6%"><div align="center"><strong>Sign:</strong></div></td>
    <td colspan="2">&nbsp;</td>
  </tr>
</table>

<br/>
<br/>

                                      
                                              
</div>
<div class="row">
<div class="col-md-10 col-offset-md-10"></div>
<div class="col-md-2"><button type='button' class='btn btn-primary'><i class=' feather  icon icon-printer'> &nbsp;</i>Print Details</button></div>
                                                 
           </div> 
</div>
</div>
</div>








</div>

</div>
</section>





<script src="assets/js/vendor-all.min.js"></script>
<script src="assets/js/plugins/bootstrap.min.js"></script>
<script src="assets/js/pcoded.min.js"></script>
<!--<script src="assets/js/menu-setting.min.js"></script>-->

<script src="assets/js/plugins/jquery.dataTables.min.js"></script>
<script src="assets/js/plugins/dataTables.bootstrap4.min.js"></script>
<script src="assets/js/plugins/buttons.colVis.min.js"></script>
<script src="assets/js/plugins/buttons.print.min.js"></script>
<script src="assets/js/plugins/pdfmake.min.js"></script>
<script src="assets/js/plugins/jszip.min.js"></script>
<script src="assets/js/plugins/dataTables.buttons.min.js"></script>
<script src="assets/js/plugins/buttons.html5.min.js"></script>
<script src="assets/js/plugins/buttons.bootstrap4.min.js"></script>
<script src="assets/js/pages/data-export-custom.js"></script>
</body>
<script>
function printData()
{
   var divToPrint=document.getElementById("printTable");
   
    var htmlToPrint = '' +
        '<style type="text/css">' +
        'table th, table td {' +
        'border-collapse:collapse;' +
        'padding:0.5em;' +
        '}' +
        '</style>'
		htmlToPrint += divToPrint.outerHTML;
   newWin= window.open("");
   newWin.document.write(htmlToPrint);
   newWin.print();
   newWin.close();
}

$('button').on('click',function(){
printData();
})
</script>
</html>
