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
    <title>Privacy Policy</title>
    <link rel="shortcut icon" href="assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/icofont.min.css">
    <link rel="stylesheet" href="assets/css/swiper.min.css">
    <link rel="stylesheet" href="assets/css/lightcase.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .privacy-policy {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .privacy-policy h2,
        .privacy-policy h3,
        .privacy-policy h4 {
            margin-top: 20px;
        }
        .privacy-policy p {
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
                        <h2>Privacy Policy</h2>
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
        <div class="privacy-policy">
            <br><br>
            <p style="color: black;">   Welcome to All About Labs, your trusted partner in diagnostic excellence. We are dedicated to providing reliable and convenient diagnostic services right at your doorstep.</p>


<p style="color: black;"><strong>Interpretation and definitions </strong><br />
<strong style="color: black;">Device:</strong> means any device that has access to the Service, such as a computer, mobile phone or  digital tablet.<br />
<strong style="color: black;"> Personal Data:</strong> means any information relating to an identified or identifiable individual.<br />
<strong style="color: black;">Service:</strong> refers to the website. Service provider: natural or legal person who processes  data on behalf of the Company. This refers to external companies or individuals employed by the Company to facilitate the provision of the Service, to provide the Service on behalf of the Company, to provide services related to the Service or to help the Company analyze the use of the Service.<br />
<strong style="color: black;">Usage data:</strong> refers to automatically collected data generated either  by the use of the Service or by the infrastructure of the Service  itself (for example, the duration of a page visit).  Website: Refers to allaboutlab.com<br />
<strong style="color: black;">You: </strong>means the person using or using the Service or the company or other legal entity on whose behalf that person uses or uses the Service. </p>
<p style="color: black;"><strong> Collection and use of your personal information </strong><br />
Types of Information Collected<br />
Personal information:  When you use our Service, we may ask you to provide us with certain personally identifiable information that can be used to contact you or identify you. This may include, but is not limited to, personally identifiable information<br />
 Email address<br />
 First name and last name<br />
 Telephone number<br />
 Address, State, Province, Zip Code, City </p>
<p style="color: black;"> <strong style="color: black;">Usage information: </strong><br />
  Usage data is collected automatically when you use the Service.  Usage Data may include information such as your device&#8217;s Internet Protocol address (such as an IP address), browser type, browser version, the pages of our Service you visit, the time and date of your visit, the time spent on those pages, unique device. identifiers, and other diagnostic information. </p>
<p style="color: black;"> When you use the Service on or through a mobile device, we may automatically collect certain information, including, but not limited to, the type of mobile device you are using, the unique identifier of your mobile device, the IP address of your mobile device, your mobile number operating system , the type of mobile Internet browser you are using, unique device identifiers and other diagnostic information. </p>
<p style="color: black;"> We may also collect information that your browser sends whenever you visit our Service or use the Service on or through a mobile device. </p>
<p style="color: black;"><strong style="color: black;">Tracking technologies and cookies: </strong><br />
 We use cookies and similar tracking technologies to track your activity on the Service and store certain information. The tracking technologies used are beacons, identifiers and scripts to collect and track information and to improve and analyze our service. Technologies used may include </p>
<p style="color: black;">Cookies or browser cookies: A cookie is a small file placed on your device. You can instruct your browser to disable all cookies or to notify you when a cookie is  sent. However, if you do not accept cookies, you may not be able to use all parts of our service. If you have not adjusted your browser settings to disable cookies, our service may use cookies. Network signs. Some sections of our Service and our emails may contain small electronic files called web beacons (also called clear gifs, pixel tags and single pixel gifs) that allow the Company, for example, to count users who have visited those pages. or opened e-mail and  other related website statistics (for example, to record the popularity of a certain section and to check the integrity of the system and server). Use of your personal information </p>
<p style="color: black;"><strong style="color: black;">The company may use personal data for the following purposes </strong><br />
Providing and maintaining our Service: including monitoring the use of our Service. </p>
<p style="color: black;">Account management: Management of registration of user services. The personal information you provide may give you access to various features of the Service that are available to you as a registered user. </p>
<p style="color: black;">Contract Performance: The development, execution and performance of a  purchase contract for  products, goods or services  purchased by you or  any other contract entered into with us through the Service. </p>
<p style="color: black;">Contacting you: Contacting you via email, telephone, text message or other similar forms of electronic communication, such as  mobile application push notifications, about updates or  communications related to features, products or contractual services, including  security updates as necessary or reasonable to implement them. </p>
<p style="color: black;">To provide you with: news, special offers and general information about other products, services and events  we offer that are similar to those  you have already purchased or inquired about, unless you have opted out of receiving such information.</p>
<p style="color: black;"><strong style="color: black;">Disclosure of your personal information </strong><br />
Transfers of companies:  If the Company is involved in a merger, acquisition or sale of assets, your personal information may be transferred. We will notify you before we transfer your personal information and it will be subject to  different privacy policies. </p>
<p style="color: black;">Legal protection:  In some circumstances, the Company may be required to hand over your personal information when required  by law or to respond to valid requests from authorities (such as a court or  government agency). </p>
<p style="color: black;"><strong style="color: black;">Security of your personal information </strong><br />
The security of your personal information is important to us, but please be aware that no data transmission over the Internet or  electronic storage is 100% secure. We strive to use commercially reasonable means to protect your personal information and never sell of any of our data but still we cannot guarantee its complete security.  </p>
<p style="color: black;"> <strong style="color: black;">Changes to this Privacy Policy </strong><br />
 We may update our privacy policy from time to time. We will notify you of any changes by posting a new Privacy Policy on this page. We encourage you to check this privacy policy regularly for  changes. Changes to this Privacy Policy will be effective when they are posted on this page.  Contact us </p>
<p style="color: black;">If you have any questions about this Privacy Policy, please <a href="https://allaboutlab.com/contact.php">contact us</a></p>
                
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