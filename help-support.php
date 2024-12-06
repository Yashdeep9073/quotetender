<?php
session_start();
require("login/db/config.php");

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
    <title>Help and Support</title>
    <link rel="shortcut icon" href="assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/icofont.min.css">
    <link rel="stylesheet" href="assets/css/swiper.min.css">
    <link rel="stylesheet" href="assets/css/lightcase.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .help-support {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .help-support h2,
        .help-support h3,
        .help-support h4 {
            margin-top: 20px;
        }
        .help-support p {
            text-align: justify;
        }
    </style>
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


    <!-- header section start here -->
    <header class="header-section">
        <div class="header-top">
            <?php include_once("header.php"); ?>
        </div>
        <div class="header-bottom">
            <?php include_once("menu.php"); ?>
        </div>
    </header>
    <!-- header section ending here -->

    <!-- Pageheader section start here -->
    <div class="pageheader-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="pageheader-content text-center">
                        <h2>Help and Support</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>

                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Pageheader section ending here -->

    <!-- About Us Section Start Here -->
 <div class="container">
        <div class="help-support">
            <!--<h3>Help and Support</h3>-->

            <h3>Introduction</h3>
            <p>Welcome to DV Electromatic Pvt Ltd (DVEPL). This Help and Support page aims to provide assistance and guidance on how to use our services effectively. If you have any questions or need further assistance beyond what is provided here, please feel free to contact us.</p>
<br>
            <h3>FAQs (Frequently Asked Questions)</h3>
            <h4>What services does DVEPL offer?</h4>
            <p>DVEPL offers a range of Engineering and Manufacturing services, including the installation, testing, commissioning, and maintenance of HT/LT Panels, Relays, Automation systems, and more. Our expertise extends to handling PLC and microprocessor-based equipment.</p>

            <h4>How can I contact DVEPL for support?</h4>
            <p>You can contact our support team via email at [your support email] or by phone at +91 9417021685. Our support hours are [your support hours].</p>

            <h4>What are the payment options available?</h4>
            <p>We accept payments via [list payment methods accepted]. For specific queries related to payments or invoices, please contact our billing department.</p>

            <!--<h3>Contact Information</h3>-->
            <!--<p>If you have any further questions or need assistance, please don't hesitate to contact us:</p>-->
            <!--<p><strong>Email:</strong> [your support email]<br>-->
            <!--   <strong>Phone:</strong> [your support phone number]<br>-->
            <!--   <strong>Support Hours:</strong> [your support hours]</p>-->

            <h3>Feedback</h3>
            <p>We value your feedback! If you have any suggestions, comments, or concerns about our services or website, please let us know. Your feedback helps us improve our offerings and customer experience.</p>

            <h3>Additional Resources</h3>
            <p>For more detailed information about our services, please visit our <a href="services.html">Services</a> page.</p>
        </div>
    </div>

    <br><br>
    <!-- About Us Section Ending Here -->





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
</body>

</html>