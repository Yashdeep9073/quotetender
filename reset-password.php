<?php
require("login/db/config.php");
error_reporting(0);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_GET['token'];
    $newPassword = md5($_POST['new_password']);
    $confirmPassword = md5($_POST['confirm_password']);

    // Verify token, expiration time, and passwords match
    if ($newPassword === $confirmPassword) {
        // Update password in the database
        $sql = "SELECT email_id, expiry_time FROM  members WHERE activation_token = '"  . $token . "'";
        $re = mysqli_query($db, $sql);
        $row1 = mysqli_fetch_row($re);
        $email = $row1['0'];
        $expiryTime = $row1['1'];
        if ($re) {
            if (strtotime($expiryTime) > time()) {
                $updateSql = "UPDATE members SET password = '$confirmPassword' WHERE email_id = '"  . $email . "'";
                mysqli_query($db, $updateSql);

                $msg = " <div class='alert alert-success  alert-dismissible fade show' role='alert' style='font-size:16px;' id='successMessage'>
                <strong><i class=' feather  icon icon-info'></i>Success !</strong> Password reset successful!
                
              </div> ";


                $sql = "UPDATE members SET activation_token = 0, expiry_time=0 WHERE email_id = '"  . $email . "'";
                mysqli_query($db, $sql);
                header("refresh:5;url=login.php");
            } else {

                $msg = " <div class='alert alert-danger  alert-dismissible fade show' role='alert' style='font-size:16px;' id='successMessage'>
  <strong><i class=' feather  icon icon-info'></i>Error !</strong>Link has been expired
  
</div> ";
            }
        }
        // Invalidate the reset token

        else {

            $msg = " <div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='successMessage'>
            <strong><i class=' feather  icon icon-info'></i>Error! </strong>Invalid token.
            
          </div> ";
        }
    } else {
        $msg = " <div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='successMessage'>
        <strong><i class=' feather  icon icon-info'></i>Error !</strong>Passwords do not match.
        
      </div> ";
    }
}
$web = "SELECT * FROM web_content  ";
$contet = mysqli_query($db, $web);
$cont = mysqli_fetch_row($contet);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="shortcut icon" href="assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/icofont.min.css">
    <link rel="stylesheet" href="assets/css/swiper.min.css">
    <link rel="stylesheet" href="assets/css/lightcase.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer>
    </script>
</head>

<body>

    <!-- preloader start here -->
    <div class="preloader">
        <div class="preloader-inner">
            <div class="preloader-icon">
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
    <!-- preloader ending here -->


    <!-- scrollToTop start here -->
    <a href="#" class="scrollToTop"><i class="icofont-rounded-up"></i></a>
    <!-- scrollToTop ending here -->


    <header class="header-section">
        <div class="header-top">
            <?php include_once("header.php"); ?>
        </div>

    </header>



    <!-- Login Section Section Starts Here -->
    <div class="login-section padding-tb section-bg">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <?php if (isset($token)) {
                                echo $msg;
                            }

                            ?>
                            <h4 class="card-title">Reset Password</h4>

                            <form action="" method="post">
                                <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" required>
                                </div>
                                <br />
                                <button type="submit" class="btn btn-success">Reset Password</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Login Section Section Ends Here -->




    <!-- Footer Section Ending Here -->
    </div>
    <!-- footer -->



    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/swiper.min.js"></script>
    <script src="assets/js/progress.js"></script>
    <script src="assets/js/lightcase.js"></script>
    <script src="assets/js/counter-up.js"></script>
    <script src="assets/js/isotope.pkgd.js"></script>
    <script src="assets/js/functions.js"></script>
    <script>
    $(document).ready(function() {
        $("#successMessage").delay(5000).slideUp(300);
    });
    </script>
</body>

</html>