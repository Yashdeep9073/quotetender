<?php
session_start();
error_reporting(E_ALL);

// username and password sent from form 
require "./db/config.php";

if (!isset($_SESSION["login_user"])) {


    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $myusername2 = mysqli_real_escape_string($db, $_POST['username']);
        $mypassword = mysqli_real_escape_string($db, $_POST['password']);
        $mypassword = md5($mypassword);


        $sql = "SELECT * FROM admin WHERE username = '$myusername2' and password = '$mypassword' and status='1'";
        $result = mysqli_query($db, $sql);
        $adminData = mysqli_fetch_row($result);
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $active = $row['active'];


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
        $count = mysqli_num_rows($result);

        // If result matched $myusername2 and $mypassword, table row must be 1 row

        if ($count == 1) {

            $_SESSION['login_user'] = $myusername2;
            $_SESSION['login_user_id'] = $adminData[9];

            /*?>setcookie('password',$myusername2,time() + (86400 * 7));<?php */

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
            mysqli_query($db, "insert into user_logs(user_id,username,user_ip,login_time,city,region) values('" . $_SESSION['id'] . "','" . $_SESSION['login_user'] . "','$ip','$action','$city','$region')");

            session_regenerate_id(true);
            $st = 1;

            $st = base64_encode($st);
            header("location: dashboard.php?loginin=$st");
        } else {
            $error = " ! Your Username or Password is invalid";
            $status = 1;
        }
    }
} else {


    header("location: dashboard.php");
}







?>


<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <title>Welcome to Quote tender</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="#" />

    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/style.css">
    <script language="javascript" type="text/javascript">
        window.history.forward();
    </script>

    <script src="https://www.google.com/recaptcha/api.js" async defer>
    </script>
</head>

<div class="auth-wrapper align-items-stretch aut-bg-img">
    <div class="flex-grow-1">
        <div class="h-100 d-md-flex align-items-center auth-side-img">

        </div>
        <div class="auth-side-form" style="background-color:#f8f7f2;">

            <form action="" method="post">


                <div class=" auth-content">
                    <img src="assets/images/admin.png" alt="" class="img-fluid">
                    <hr />

                    <h3 class="mb-4 f-w-400">Signin</h3>
                    <?php if (isset($status)) {
                        echo " <div class='alert alert-danger alert-dismissible fade show' role='alert' style='font-size:16px;' id='successMessage'>
                        <strong><i class=' feather  icon icon-info'></i>Error!</strong> $error.
                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                        </div> ";
                    }
                    ?>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background-color:#33cc33;color:#fff;"><i
                                    class="feather icon-mail"></i></span>
                        </div>

                        <input type="text" class="form-control" placeholder="Username" name="username" required
                            oninvalid="this.setCustomValidity('Please Enter Username')" oninput="setCustomValidity('')"
                            style="border-color:#33cc33">
                    </div>
                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background-color:#33cc33;color:#fff;"><i
                                    class="feather icon-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" placeholder="Password" name="password" required
                            oninvalid="this.setCustomValidity('Please Enter Password')" oninput="setCustomValidity('')"
                            style="border-color:#33cc33">
                    </div>
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="6LeyShEqAAAAAJIMoyXfN7DmfesxwLNYOgBHIh4N"
                            data-callback="callback" style="border:none;" align="center">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-secondary " name="submit" id="submit" disabled>
                        <i class="feather icon-save lg"></i>&nbsp;Sign In
                    </button>
            </form>
            <br /> <br />
            <hr style="border-color:#33cc33">
            <p style="color:#000;">HelpDesk/Helpline No:+91-9870443528</p>
        </div>
    </div>
</div>
</div>
</div>


<script src="assets/js/vendor-all.min.js"></script>
<script src="assets/js/plugins/bootstrap.min.js"></script>
<script src="assets/js/waves.min.js"></script>
</body>

<script>
    $(document).ready(function () {
        $("#successMessage").delay(5000).slideUp(300);
    });
</script>
<script type="text/javascript">
    function callback() {
        const submitButton = document.getElementById("submit");
        submitButton.removeAttribute("disabled");
    }
</script>

</html>