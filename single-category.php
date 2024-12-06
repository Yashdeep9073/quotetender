<?php
session_start();

require("login/db/config.php");

$web = "SELECT * FROM web_content  ";
$contet = mysqli_query($db, $web);
$cont = mysqli_fetch_row($contet);


$q = "SELECT * FROM category where show_in_menu='yes'";
$q = mysqli_query($db, $q);
$en = $_GET["id"];
$de = base64_decode($en);
$query = "SELECT * FROM category WHERE 	category_id='"  . $de . "'";
$result = mysqli_query($db, $query);
$row = mysqli_fetch_row($result);
$r = $row['1'];
$plist = "SELECT * FROM price_list WHERE category='$r'";
$plist = mysqli_query($db, $plist);


?>
<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category</title>
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

    <!-- Page Header section start here -->
    <div class="pageheader-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="pageheader-content text-center">
                        <h3>Category : <?php echo $row[1]; ?></h3>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header section ending here -->


    <!-- blog section start here -->
    <div class="blog-section padding-tb section-bg">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-12">
                    <article>
                        <div class="section-wrapper">
                            <h3>Price list in <?php echo $row[1]; ?> Category</h3>
                            <div class="row row-cols-1 justify-content-center g-4">

                                <?php

                                while ($row = mysqli_fetch_row($plist)) {
                                    echo '  <div class="col">';
                                    echo ' <div class="post-item style-2">';
                                    echo '  <div class="post-inner">';
                                    echo '  <div class="post-content">';
                                    echo ' <h4>' . $row[2] . '</h4>';
                                    echo '  <div class="meta-post">';
                                    echo '   <ul class="lab-ul">
<li><i class="icofont-ui-home"></i> Brand Name :' . $row[3] . '</li>
                        <li><i class="icofont-calendar">  </i> Date Added :' .  $row[5] . '</li>
                        
                    
                    </ul>';
                                    echo '  </div>';
                                    echo '   <a  href="login/pricelist/' . $row['4'] . '" class="lab-btn mt-2" target="_blank"><span>View File</span></a>';
                                    echo '  </div>';
                                    echo '  </div>';
                                    echo ' </div>';
                                    echo ' </div>';
                                }
                                ?>



                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-lg-4 col-12">
                    <aside>
                        <div class="widget widget-search">

                            <img src="assets/images/advt-1.jpg">

                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
    <!-- blog section ending here -->


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
                        <form action="https://demos.codexcoder.com/">
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