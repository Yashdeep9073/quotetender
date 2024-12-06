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
    <title>Terms and Condition</title>
    <link rel="shortcut icon" href="assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/icofont.min.css">
    <link rel="stylesheet" href="assets/css/swiper.min.css">
    <link rel="stylesheet" href="assets/css/lightcase.css">
    <link rel="stylesheet" href="assets/css/style.css">
     <style>
        .terms-conditions {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .terms-conditions h2,
        .terms-conditions h3,
        .terms-conditions h4 {
            margin-top: 20px;
        }
        .terms-conditions p {
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
                        <h2>Terms and Condition</h2>
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
    <br><br>
    <div class="container">
        <div class="terms-conditions">
            <!--<h2>Terms and Conditions</h2>-->

            <h3>Introduction</h3>
            <p>Welcome to DV Electromatic Pvt Ltd (DVEPL). These terms and conditions outline the rules and regulations for the use of our website and services. By accessing this website, we assume you accept these terms and conditions. Do not continue to use the website if you do not agree to all of the terms and conditions stated on this page.</p>

            <h3>Cookies</h3>
            <p>We employ the use of cookies. By accessing DVEPL's website, you agreed to use cookies in agreement with our Privacy Policy.</p>

            <h3>License</h3>
            <p>Unless otherwise stated, DVEPL and/or its licensors own the intellectual property rights for all material on the website. All intellectual property rights are reserved. You may access this from DVEPL for your own personal use subjected to restrictions set in these terms and conditions.</p>
            <p>You must not:</p>
            <ul>
                <li>Republish material from DVEPL</li>
                <li>Sell, rent, or sub-license material from DVEPL</li>
                <li>Reproduce, duplicate, or copy material from DVEPL</li>
                <li>Redistribute content from DVEPL</li>
            </ul>

            <h3>Hyperlinking to Our Content</h3>
            <p>The following organizations may link to our website without prior written approval:</p>
            <ul>
                <li>Government agencies;</li>
                <li>Search engines;</li>
                <li>News organizations;</li>
                <li>Online directory distributors may link to our website in the same manner as they hyperlink to the websites of other listed businesses; and</li>
                <li>System-wide Accredited Businesses except soliciting non-profit organizations, charity shopping malls, and charity fundraising groups which may not hyperlink to our Web site.</li>
            </ul>
            <p>These organizations may link to our home page, to publications, or to other website information so long as the link: (a) is not in any way deceptive; (b) does not falsely imply sponsorship, endorsement, or approval of the linking party and its products and/or services; and (c) fits within the context of the linking party’s site.</p>

            <h3>Liability</h3>
            <p>We shall not be held responsible for any content that appears on your website. You agree to protect and defend us against all claims that are rising on your website. No link(s) should appear on any website that may be interpreted as libelous, obscene, or criminal, or which infringes, otherwise violates, or advocates the infringement or other violation of, any third-party rights.</p>

            <h3>Variation of Terms</h3>
            <p>DVEPL is permitted to revise these terms at any time as it sees fit, and by using this website you are expected to review these terms on a regular basis.</p>

            <h3>Governing Law</h3>
            <p>These terms and conditions are governed by and construed in accordance with the laws of [Your Country] and you irrevocably submit to the exclusive jurisdiction of the courts in that State or location.</p>

            <!--<h3>Contact Information</h3>-->
            <!--<p>If you have any questions about these Terms and Conditions, please contact us:</p>-->
            <!--<p><strong>Email:</strong> [your contact email]<br>-->
            <!--   <strong>Postal Address:</strong> [your postal address]</p>-->
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