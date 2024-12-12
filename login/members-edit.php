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

/* Attempt to connect to MySQL database */




if(count($_POST)>0) {
mysqli_query($db,"UPDATE members set name ='" . $_POST["name"] . "',father ='" . $_POST["father"] . "' ,mobile='" . $_POST["mobile"] . "' ,sex ='" . $_POST["sex"] . "',dob ='" . $_POST["dob"] . "',state ='" . $_POST["state"] . "',city ='" . $_POST["city"] . "',education ='" . $_POST["edu"] . "',occupation ='" . $_POST["ocu"] . "',pancard ='" . $_POST["pan"] . "',identity_proof ='" . $_POST["proof"] . "',identity_number ='" . $_POST["idp"] . "',loanid_type ='" . $_POST["loan"] . "',loanid_number ='" . $_POST["loanid"] . "',address_proof ='" . $_POST["address"] . "' ,address_proof_number ='" . $_POST["apr"] . "' ,permanent_address ='" . $_POST["paddress"] . "' ,status ='" . $_POST["status"] . "' WHERE member_id='"  . $de . "'");

$stat=1;

$res=base64_encode($stat);

echo ("<SCRIPT LANGUAGE='JavaScript'>
   
    window.location.href='view_member.php?status=$res';
    </SCRIPT>");

}





$result = mysqli_query($db,"SELECT * FROM members WHERE member_id='" . $de . "'");
$row= mysqli_fetch_row($result);


    ?>

<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
<title>Update Members </title>



<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="description" content="" />
<meta name="keywords" content="">
<meta name="author" content="Codedthemes" />

<link rel="shortcut icon" href="../assets/images/x-icon.png" type="image/x-icon">

<link rel="stylesheet" href="assets/css/plugins/dataTables.bootstrap4.min.css">

<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="">

<div class="loader-bg">
<div class="loader-track">
<div class="loader-fill"></div>
</div>
</div>



<?php include 'navbar.php'; ?>


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

<div class="page-header">
<div class="page-block">
<div class="row align-items-center">
<div class="col-md-12">
<div class="page-header-title">
<h5 class="m-b-10">Update Members
</h5>
</div>

</div>
</div>
</div>
</div>


<div class="row">

<div class="col-sm-12">
<div class="card">

<div class="card-header table-card-header">
 <form class="contact-us" method="post" action="" enctype="multipart/form-data"  autocomplete="off">
                                    <div class=" ">
                                        <!-- Text input-->
                                        <div class="row">
                                            <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">Name*
                                                    <label class="sr-only control-label" for="name">Username<span class=" "> </span></label>
                                                      <input name="name" type="text" class="form-control" placeholder="Applicant Name " value="<?php echo $row['0'];?>" required />
                                                </div>
                                            </div>
                                                <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">Father Name
                                                 
               <input name="father" type="text" class="form-control" placeholder="Fathers Name" required  value="<?php echo $row['1'];?>" />
                                                </div>
                                            </div>
                                            
                                             
                                            <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">Mobile*
                                                    
                                                    <input name="mobile" type="number"  class="form-control" placeholder="98704435** *" required  value="<?php echo $row['2'];?>" />
       
                                                </div>
                                            </div>
                                            
                                            
                                               <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">Member Id*
                                             
                                <input name="memberid" type="number" class="form-control" required placeholder="Enter Member Id "  value="<?php echo $row['3'];?>" readonly />
                                                </div>
                                            </div>
                                            
                                            
                                             <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">Sex*
                                                   
                                         <select  name="sex"  class="form-control" required>
                                         <option value="<?php echo $row['4'];?>"> <?php echo $row['4'];?> </option>
	<option  value="Male">Male</option>
	<option value="Female">Female</option>
	<option value="Other">Other</option>
</select>
                                                 </div>
                                            </div>
                                            
                                                <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">DOB*
                                                    <label class="sr-only control-label" for="name">User Type<span class=" "> </span></label>
                                                      <input name="dob" type="date"  class="form-control" required   value="<?php echo $row['5'];?>">
                                                </div>
                                            </div>
                                            <!-- Text input-->
                                              <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">
                                                   <label>State*</label>
                              <select class="form-control" required name="state"> 
	<option  value="<?php echo $row['6'];?>"><?php echo $row['6'];?></option>
	<option value="Jammu and Kashmir">Jammu and Kashmir</option>
	<option value="Himachal Pradesh">Himachal Pradesh</option>
	<option value="Punjab">Punjab</option>
	<option value="Chandigarh">Chandigarh</option>
	<option value="Uttarakhand">Uttarakhand</option>
	<option value="Haryana">Haryana</option>
	<option value="Delhi">Delhi</option>
	<option value="Rajasthan">Rajasthan</option>
	<option value="Uttar Pradesh">Uttar Pradesh</option>
	<option value="Bihar">Bihar</option>
	<option value="Sikkim">Sikkim</option>
	<option value="Arunachal Pradesh">Arunachal Pradesh</option>
	<option value="Nagaland">Nagaland</option>
	<option value="Manipur">Manipur</option>
	<option value="Mizoram">Mizoram</option>
	<option value="Tripura">Tripura</option>
	<option value="Meghalaya">Meghalaya</option>
	<option value="Assam">Assam</option>
	<option value="West Bengal">West Bengal</option>
	<option value="Jharkhand">Jharkhand</option>
	<option value="Odisha">Odisha</option>
	<option value="Chattisgarh">Chattisgarh</option>
	<option value="Madhya Pradesh">Madhya Pradesh</option>
	<option value="Gujarat">Gujarat</option>
	<option value="Daman and Diu">Daman and Diu</option>
	<option value="Dadra and Nagar Haveli">Dadra and Nagar Haveli</option>
	<option value="Maharashtra">Maharashtra</option>
	<option value="Andhra Pradesh (Before)">Andhra Pradesh (Before)</option>
	<option value="Karnataka">Karnataka</option>
	<option value="Goa">Goa</option>
	<option value="Lakshadweep Islands">Lakshadweep Islands</option>
	<option value="Kerala">Kerala</option>
	<option value="Tamil Nadu">Tamil Nadu</option>
	<option value="Pondicherry">Pondicherry</option>
	<option value="Andaman and Nicobar Islands">Andaman and Nicobar Islands</option>
	<option value="Telangana">Telangana</option>
	<option value="Andhra Pradesh (New)">Andhra Pradesh (New)</option>
</select>
                                                </div>
                                            </div>
                                            
                                            
                                              <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">City*
                                                    <label class="sr-only control-label" for="name">Email<span class=" "> </span></label>
                                                    <input name="city" type="text"  class="form-control" required placeholder="Enter City"  value=" <?php echo $row['7'];?>"/>
                                                </div>
                                            </div>
                                            
                                            
                                              <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group"><label>Education Qualification*</label>
                                <input name="edu" type="text"  class="form-control" placeholder="Qualification *" required  value=" <?php echo $row['8'];?>"/>
                                                </div>
                                            </div>
                                            
                                            
                                              <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">
                                                
                                                <label>Present Occupation *</label>
                                <input name="ocu" type="text"  class="form-control" placeholder=" Enter Occupation" required value=" <?php echo $row['9'];?>" />
                                                </div>
                                            </div>
                                          
                                          
                                          
                                           <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">PAN Card*
                                                    <label class="sr-only control-label" for="name">User Type<span class=" "> </span></label>
                                                      <input name="pan" type="text"  class="form-control" placeholder="BINPR00**" required value=" <?php echo $row['10'];?>" />
                                                </div>
                                            </div>
                                            
                                            
                                            
                                             <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">Identity Proof*
                                                    <select name="proof"  class="form-control" required>
	<option selected="selected" value=" <?php echo $row['11'];?>"><?php echo $row['11'];?></option>
	<option value="PAN Card">PAN Card</option>
	<option value="Passport">Passport</option>
	<option value="Driving License">Driving License</option>
	<option value="Voter ID">Voter ID</option>
	<option value="UID">UID</option>
	<option value="Rashan Card">Rashan Card</option>
	<option value="Other">Other</option>
</select>                                  </div>
                                            </div>
                                          
                                
                                
                                <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">Proof Number*
                                                    <label class="sr-only control-label" for="name">Username<span class=" "> </span></label>
                                                    <input name="idp" type="text"  class="form-control" placeholder="Identity Number" required  value="<?php echo $row['12'];?>"/>
                                                </div>
                                            </div>
                                            
                                            
                                            
                                            <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">Loan ID Type*
                                                   <select name="loan"  class="form-control" required>
	<option selected="selected" value="<?php echo $row['13'];?>"><?php echo $row['13'];?></option>
	<option value="PAN Card"> Loan id</option>
	<option value="Passport">Daily Deposit id</option>
	<option value="Driving License">Recurring Deposit id</option>

</select>
                                                </div>
                                            </div>
                                            
                                            
                                            <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">Loan Id Number
                                                    <label class="sr-only control-label" for="name">Username<span class=" "> </span></label>
                                                     <input name="loanid" type="text"  class="form-control" placeholder="Enter Id Number" required value="<?php echo $row['14'];?>" />
                                                </div>
                                            </div>
                                            
                                            
                                            <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">Address Proof*
                                                    <label class="sr-only control-label" for="name">Username<span class=" "> </span></label>
                                                    <select name="address" class="form-control" required>
	<option value="<?php echo $row['15'];?>"><?php echo $row['15'];?></option>
	<option value="UID">UID</option>
	<option value="Electricity Bill">Electricity Bill</option>
	<option value="Passport">Passport</option>
	<option value="Telephone Bill">Telephone Bill</option>
	<option value="Voter ID">Voter ID</option>
	<option value="Driving License">Driving License</option>
	<option value="Rashan Card">Rashan Card</option>
	<option value="Other">Other</option>
</select>
             
                                                </div>
                                            </div>
                                            
                                            
                                            <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">Address Proof Number
                                                    <label class="sr-only control-label" for="name"><span class=" "> </span></label>
                                                    <input name="apr" type="text"  class="form-control" placeholder="Address Proof Number" required  value="<?php echo $row['16'];?>"/>
                                                </div>
                                            </div>
                                            
                                            <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">Permanent Address
                                                    <label class="sr-only control-label" for="name">Username<span class=" "> </span></label>
                                                    <textarea name="paddress" rows="2" cols="20" class="form-control" placeholder="Enter Permanent Address" required > <?php echo $row['17'];?></textarea>
                                                </div>
                                            </div>
                                            
                                            
                                            <div class="col-xl-6 col-lg-6 col-md-4 col-sm-12 col-12">
                                                <div class="form-group">Status*
                                                    <label class="sr-only control-label" for="name">Username<span class=" "> </span></label>
                                                                            <select id="" name="status" class="form-control"  required>
                                                     <option value="<?php  echo $row[18];?>"><?php  echo $row[18];?></option>
  <option value="1">Enable</option>
  <option value="0">Disabe</option>

</select>
                                                </div>
                                            </div>
                                
                                
                                            
                                            <!-- Button -->
                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                                                       <button type="submit" class="btn btn-secondary" name="submit" id="submit">
   <i class="feather icon-save lg"></i>&nbsp; Update
</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
</div>
<div class="card-body">
<div class="dt-responsive table-responsive">


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

</html>
