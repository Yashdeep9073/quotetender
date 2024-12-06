<?php
session_start();


error_reporting(0);
include("login/db/config.php");
// Storing google recaptcha response
// in $recaptcha variable
$recaptcha = $_POST['g-recaptcha-response'];

// Put secret key here, which we get
// from google console
$secret_key = '6LeyShEqAAAAAKVRQAie1sCk9E5rBjvR9Ce0x5k_';

// Hitting request to the URL, Google will
// respond with success or error scenario
$url = 'https://www.google.com/recaptcha/api/siteverify?secret='
    . $secret_key . '&response=' . $recaptcha;

// Making request to verify captcha
$response = file_get_contents($url);

// Response return by google is in
// JSON format, so we have to parse
// that json
$response = json_decode($response);

// Checking, if response is true or not
if ($response->success == true) {
    $msg = "Google reCAPTACHA verified";
} else {
    $msg = "Error in Google reCAPTACHA";
}

if (!isset($_SESSION["login_register"])) {

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // username and password sent from form 


        $myusername = mysqli_real_escape_string($db, $_POST['username']);
        $mypassword = mysqli_real_escape_string($db, $_POST['password']);
        $mypassword = md5($mypassword);

        $sql = "SELECT * FROM members WHERE email_id = '$myusername' and password = '$mypassword' and status=1";
        $result = mysqli_query($db, $sql);
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $active = $row['active'];
        $userLogin = $row['name'];
        $count = mysqli_num_rows($result);

        // If result matched $myusername and $mypassword, table row must be 1 row

        if ($count == 1) {

            $_SESSION['login_register'] = $myusername;
            $_SESSION['login_username'] = $userLogin;

            /*?>setcookie('password',$myusername,time() + (86400 * 7));<?php */

            $_SESSION['id'] = session_id(); // hold the user id in session
            $ipAddress = $_SERVER['REMOTE_ADDR']; // get the user ip

            // Your API Key from ipinfo.io (you can use a free tier key or subscribe for more features)
            $accessToken = 'c922e696cae131'; // Replace with your ipinfo.io token

            // IPinfo API endpoint
            $url = "http://ipinfo.io/{$ipAddress}/json?token={$accessToken}";

            // Initialize a cURL session
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Execute the cURL session and get the response
            $response = curl_exec($ch);

            // Close cURL session
            curl_close($ch);

            // Decode the JSON response
            $data = json_decode($response, true);
            
            $ip = $data['ip'];
            $city = $data['city'];
            $region = $data['region'];

            date_default_timezone_set('Asia/Kolkata');
            $action = date('Y-m-d H:i:s A'); // query for inser user log in to data base

            mysqli_query($db, "insert into user_logs(user_id,username,user_ip,login_time,city,region) values('" . $_SESSION['id'] . "','" . $_SESSION['login_username'] . "','$ip','$action','$city','$region')");

            session_regenerate_id(true);
            $st = 1;

            $st = base64_encode($st);
            header("location: index.php?loginin=$st");
            exit();
        } else {
            $error = "! Your registration process is already completed. Please wait for your account to be Authenticated.";
            $status = 1;
        }
    }
} else {




    header("location: index.php");
}
$web = "SELECT * FROM web_content  ";
$contet = mysqli_query($db, $web);
$cont = mysqli_fetch_row($contet);

$q = "SELECT * FROM category where show_in_menu='yes'";
$q = mysqli_query($db, $q);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Here</title>
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
        <div class="header-bottom">
            <?php include_once("menu.php"); ?>
        </div>
    </header>

    <!-- Page Header section start here -->
    <div class="pageheader-section">
       
    </div>
    <!-- Page Header section ending here -->

    <!-- Login Section Section Starts Here -->
    <div class="login-section padding-tb section-bg">
        <div class="container">
            <div class="account-wrapper">
                <?php if (isset($status)) {
                    echo " <div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='successMessage'>
  <strong><i class=' feather  icon icon-info'></i>Error!</strong>$error.
  
</div> ";
                }
                ?><br />
                <h3 class="title">Login</h3>
                <form class="account-form" action="" method="post" autocomplete="off">
                    <div class="form-group">
                        <input type="text" placeholder="Email id" name="username" required>
                    </div>
                    <div class="form-group">
                        <input type="password" placeholder="Password" name="password" required>
                    </div>

                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="6LeyShEqAAAAAJIMoyXfN7DmfesxwLNYOgBHIh4N"
                            data-callback="callback" style="border:none;">
                        </div>

                    </div>

                    <div class="form-group text-center">
                        <input type="submit" id="submit" value="Sign In" class="btn btn-block btn-primary mb-0"
                            name="POST" disabled>
                    </div>
                </form>
                <div class="account-bottom">
                    <span class="d-block cate pt-10">Don’t Have any Account? <a href="registration.php">Sign
                            Up</a> | <a href="forgetpass.php">Forget Password?</a></span>

                    <span class="or"><span>or</span></span>
                    <h5 class="subtitle">Login With Social Media</h5>
                    <ul class="lab-ul social-icons justify-content-center">
                        <li>
                            <a href="#" class="facebook"><i class="icofont-facebook"></i></a>
                        </li>
                        <li>
                            <a href="#" class="twitter"><i class="icofont-twitter"></i></a>
                        </li>
                        <li>
                            <a href="#" class="linkedin"><i class="icofont-linkedin"></i></a>
                        </li>
                        <li>
                            <a href="#" class="instagram"><i class="icofont-instagram"></i></a>
                        </li>
                        <li>
                            <a href="#" class="pinterest"><i class="icofont-pinterest"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Login Section Section Ends Here -->


    <!-- footer -->
    <div class="news-footer-wrap">
        <div class="fs-shape">
            <img src="assets/images/shape-img/03.png" alt="fst" class="fst-1">
            <img src="assets/images/shape-img/04.png" alt="fst" class="fst-2">
        </div>
        <!-- Newsletter Section Start Here -->
        <div class="news-letter">
            <div class="container">
                <div class="section-wrapper">
                    <div class="news-title">
                        <h3>Want Us To Email You About Special Offers And Updates?</h3>
                    </div>
                    <div class="news-form">
                        <form action="">
                            <div class="nf-list">
                                <input type="email" name="email" placeholder="Enter Your Email">
                                <input type="submit" name="submit" value="Subscribe Now">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Newsletter Section Ending Here -->

        <!-- Footer Section Start Here -->
        <footer>
            <?php include_once("footer.php"); ?>
        </footer>
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

        <script type="text/javascript">
    function callback() {
        const submitButton = document.getElementById("submit");
        submitButton.removeAttribute("disabled");
    }
    </script>
</body>


</html>