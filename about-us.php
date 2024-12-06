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
    <title>About us</title>
    <link rel="shortcut icon" href="assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/icofont.min.css">
    <link rel="stylesheet" href="assets/css/swiper.min.css">
    <link rel="stylesheet" href="assets/css/lightcase.css">
    <link rel="stylesheet" href="assets/css/style.css">
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
                        <h2>About us</h2>
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
    <div class="about-section style-3 padding-tb section-bg">
        <div class="container">
            <div class="row justify-content-center row-cols-xl-2 row-cols-1 align-items-center">
                <div class="col">
                    <div class="about-left">
                        <div class="about-thumb">
                            <img src="assets/images/about/03.jpg" alt="about">
                        </div>
                        <div class="abs-thumb">
                            <img src="assets/images/about/02.jpg" alt="about">
                        </div>
                        <div class="about-left-content">
                            <h3>30+</h3>
                            <p>Years Of Experiences</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="about-right">
                        <div class="section-header">
                            <span class="subtitle">About us</span>

                            <p align="justify">We "DVEPL." DV. Electromatic Pvt Ltd. introduce ourselves as a quick-moving, customer-focused business with a solid background in offering Engineering and Manufacturing services. For example, we install, test, commission, and run all HT/LT Panels, Relays, Automation, VCB Panels, and Retrofit Panels of 11/33 KV Sub Stations. Almost any PLC and microprocessor-based equipment can be handled by us. Our team is composed of qualified and committed individuals that are capable of handling the complexity of on-site repair, maintenance, installation, testing, commissioning, and operation. Our staff of support engineers is always on the go to satisfy customers completely by responding quickly to urgent requests. We have state-of-the-art technology integrated into the newest tools, equipment, and trade supplies in our arsenal to handle the challenges of service and maintenance at the customer's location.</p>
                        </div>
                        <div class="section-wrapper">
                            <ul class="lab-ul">
                                
                                <li>
                                    <div class="sr-left">
                                        <img src="assets/images/about/icon/02.jpg" alt="about icon">
                                    </div>
                                    <div class="sr-right" style=text-align:"justify" >
                                        <h5>Our Mission</h5>
                                        <p align="justify"> By employing devoted, competent, and professional staff, the proper level of technology,
                                             and the proper management techniques intended to ensure the highest levels of safety, quality, and environmental standards, 
                                            DVEPL will provide the highest standards of engineering and consulting services to its clients. </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="sr-left">
                                        <img src="assets/images/about/icon/03.jpg" alt="about icon">
                                    </div>
                                    <div class="sr-right">
                                        <h5>Our Vision</h5>
                                      <p align="justify">DVEPL vision is to be a premier global provider of engineering services exceeding our clients’ 
                                            expectations on every project through a focus on safety, quality and integrity. </p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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