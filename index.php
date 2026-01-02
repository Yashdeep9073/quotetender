<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(1);

session_start();

require("login/db/config.php");
require_once "./vendor/autoload.php";
require_once "./env.php";
require "./login/utility/referenceCodeGenerator.php";



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Asia/Kolkata');
$sent_at = date('Y-m-d H:i:s');


function processTenderRequest(mysqli $db, array $data): array
{
    $db->begin_transaction();

    try {
        // Count tender usage (lock rows to avoid race)
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM user_tender_requests
            WHERE tenderID = ?
            FOR UPDATE
        ");
        $stmt->bind_param("s", $data['tender_id']);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        // Block third attempt
        if ($count >= 2) {
            $db->rollback();
            return [
                'success' => false,
                'message' => 'This Tender ID has already been used twice.'
            ];
        }

        // Decide status + email
        if ($count === 0) {
            $status = 'Requested';
            $emailTemplate = 'TENDER_REQUEST';
        } else {
            $status = 'Sent';
            $emailTemplate = 'SENT_TENDER';
        }

        $refResponse = referenceCode($db, "REF");
        $refCode = $refResponse['data']; 

        //  Insert request
        $stmt = $db->prepare("
            INSERT INTO user_tender_requests
            (
                member_id,
                tenderID,
                reference_code,
                department_id,
                due_date,
                file_name,
                file_name2,
                status,
                auto_quotation,
                created_at
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())
        ");

        $stmt->bind_param(
            "isssssss",
            $data['member_id'],
            $data['tender_id'],
            $refCode,
            $data['department_id'],
            $data['due_date'],
            $data['file1'],
            $data['file2'],
            $status
        );

        if (!$stmt->execute()) {
            throw new Exception('Insert failed');
        }

        $stmt->close();

        //  Reduce pending_request
        $stmt = $db->prepare("
            UPDATE members
            SET pending_request = pending_request - 1
            WHERE member_id = ? AND pending_request > 0
        ");
        $stmt->bind_param("i", $data['member_id']);
        $stmt->execute();
        $stmt->close();

        $db->commit();

        return [
            'success' => true,
            'status' => $status,
            'email_template' => $emailTemplate
        ];

    } catch (Throwable $e) {
        $db->rollback();
        return [
            'success' => false,
            'message' => 'Something went wrong. Please try again.'
        ];
    }
}

function replaceTemplateVars(string $content, array $vars): string
{
    foreach ($vars as $key => $value) {
        $content = str_replace('{$' . $key . '}', $value, $content);
    }
    return $content;
}


function sendMail(
    array $template,
    string $toEmail,
    string $toName,
    array $placeholders,
    array $ccEmails = [],
    ?string $logo = null
): bool {

    $mail = new PHPMailer(true);

    try {
        // SMTP config
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = getenv('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USER_NAME');
        $mail->Password = getenv('SMTP_PASSCODE');
        $mail->SMTPSecure = 'ssl';
        $mail->Port = getenv('SMTP_PORT');

        // From / To
        $mail->setFrom(
            getenv('SMTP_USER_NAME'),
            $template['email_from_title'] ?? 'Dvepl'
        );
        $mail->addAddress($toEmail, $toName);

        // CC (optional)
        $mail->clearCCs();
        if (!empty($ccEmails)) {
            foreach ($ccEmails as $cc) {
                if (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                    $mail->addCC($cc);
                }
            }
        }

        $mail->isHTML(true);

        // Subject
        $subject = $template['email_template_subject'] ?? 'Notification';
        $mail->Subject = replaceTemplateVars($subject, $placeholders);

        // Body
        $body = nl2br($template['content_1'] ?? '');
        $body .= "<br><br>" . nl2br($template['content_2'] ?? '');

        $finalBody = replaceTemplateVars($body, $placeholders);

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color:#333; line-height:1.6;'>
                " . ($logo ? "<div style='text-align:center; margin-bottom:20px;'>
                        <img src='{$logo}' alt='Logo' style='max-width:150px;'>
                   </div>" : "") . "
                {$finalBody}
            </div>
        ";

        return $mail->send();

    } catch (Exception $e) {
        // You may log this if needed
        return false;
    }
}



// Register user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    if (!isset($_SESSION['login_register'])) {
        header("Location: login.php");
        exit;
    }

    // Get member & pending_request
    $stmt = $db->prepare("
        SELECT member_id, name, email_id, pending_request
        FROM members
        WHERE email_id = ?
    ");
    $stmt->bind_param("s", $_SESSION['login_register']);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$member || (int) $member['pending_request'] <= 0) {
        $_SESSION['error'] = "You have reached the maximum allowed requests.";
        header("Location: index.php");
        exit;
    }

    //  Validate tender
    $tender = trim($_POST['tenderid']);
    if (!preg_match('/^[A-Za-z]+([_-][0-9]+)+$/', $tender)) {
        $_SESSION['error'] = "Invalid Tender ID format.";
        header("Location: index.php");
        exit;
    }

    // Process tender (SERVICE)
    $result = processTenderRequest($db, [
        'member_id' => (int) $member['member_id'],
        'tender_id' => $tender,
        'department_id' => $_POST['dept'],
        'due_date' => $_POST['datepicker'],
        'file1' => $unique_filename1 ?? null,
        'file2' => $unique_filename2 ?? null,
    ]);

    if (!$result['success']) {
        $_SESSION['error'] = $result['message'];
        header("Location: index.php");
        exit;
    }

    // Send correct email
    $template = emailTemplate($db, $result['email_template']);

    sendMail(
        template: $template,
        toEmail: $member['email_id'],
        toName: $member['name'],
        placeholders: [
            'name' => $member['name'],
            'tenderId' => $tender,
            'firmName' => $member['firm_name'] ?? '',
            'supportPhone' => $supportPhone ?? 'N/A',
            'enquiryEmail' => $enquiryMail ?? 'N/A',
            'supportEmail' => $supportEmail ?? 'N/A',
        ],
        ccEmails: array_column($ccEmailData ?? [], 'cc_email'),
        logo: $logo ?? null
    );


    $_SESSION['success'] = "Tender request submitted successfully.";
    header("Location: index.php");
    exit;
}


$ba = "SELECT * FROM banner";
$ba = mysqli_query($db, $ba);

$dept = "SELECT * FROM department where status=1 ";
$dept = mysqli_query($db, $dept);

$brand1 = "SELECT * FROM brand where status=1 ";
$brand = mysqli_query($db, $brand1);


$brand1 = "SELECT * FROM brand where status=1 ";
$brand = mysqli_query($db, $brand1);
$web = "SELECT * FROM web_content  ";
$contet = mysqli_query($db, $web);
$cont = mysqli_fetch_row($contet);

$p = "SELECT * FROM category where status=1";
$p = mysqli_query($db, $p);
$plist = "SELECT * FROM price_list LIMIT 5; ";
$plist = mysqli_query($db, $plist);

$q = "SELECT * FROM category where show_in_menu='yes'";
$q = mysqli_query($db, $q);




?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <meta name="description"
        content="Quotetender is your leading edge partner in finding the right tenders. Government Tenders , e-Tenders , online Tender Information." />
    <meta name="keywords"
        content="Online Tenders , Tender Info , Free government Tenders , government tenders , e Tenders India , Indian Tender notifications, Industry Tenders , Tender India , best tender sites, get tenders online ,  Indian Tenders portal ,quotetender.in/,Government eMarket place,Tender submission" />

    <!-- canonical tags -->
    <link rel="canonical" href="https://www.quotetender.in/" />
    <link rel="preconnect" href="https://www.quotetender.in/" />
    <link rel="dns-prefetch" href="https://www.quotetender.in/" />
    <link rel="preconnect" href="https://www.quotetender.in/" />
    <link rel="dns-prefetch" href="https://www.quotetender.in/" />
    <!-- closing canonical tags -->

    <!-- open graph tags -->
    <meta property="og:title" content="Your Business Solutions:Price List & Tender Quote">
    <meta property="og:site_name" content="Quetetender">
    <meta property="og:url" content="https://www.quotetender.in/">
    <meta property="og:description"
        content="Quotetender is your leading edge partner in finding the right tenders. Government Tenders, e-Tenders, online Tender Information.">
    <meta property="og:type" content="">
    <meta property="og:image" content="https://dvepl.com/quotetender/assets/images/logo/logo.png" />
    <!-- closing open graph tags -->

    <title>Comprehensive Price List & Tender Quote: Your Business Solutions</title>
    <link rel="shortcut icon" href="assets/images/x-icon.png" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/animate.css" />
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/icofont.min.css" />
    <link rel="stylesheet" href="assets/css/swiper.min.css" />
    <link rel="stylesheet" href="assets/css/lightcase.css" />
    <link rel="stylesheet" href="assets/css/style.css" />

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <script src="https://www.google.com/recaptcha/api.js" async defer>
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <style>
        /* Custom CSS styles for Datepicker */
        .ui-datepicker {
            background-color: #198754;
        }

        .ui-datepicker {
            color: #fff;
        }

        .ui-datepicker-current-day {
            background-color: #198754;
            color: #fff;
        }

        .ui-state-default {
            background-color: #198754;
            color: #333;
        }

        .ui-datepicker-month,
        .ui-datepicker-year {
            color: #007bff;
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

    <!-- banner section start here -->
    <section class="banner-section">
        <div class="container">
            <div class="section-wrapper">
                <div class="row align-items-center">
                    <div class="col-xxl-5 col-xl-6 col-lg-10">
                        <div>
                            <h1 class="title">
                                <span class="d-lg-block" style="color: #4CBB17">Get Online Instant</span>Quotation for
                                Tender
                            </h1>
                            <div class="row">
                                <?php if (isset($_SESSION['success'])) { ?>
                                    <script>
                                        const notyf = new Notyf({
                                            position: {
                                                x: 'center',
                                                y: 'top'
                                            },
                                            types: [
                                                {
                                                    type: 'success',
                                                    background: '#26c975', // Change background color
                                                    textColor: '#FFFFFF',  // Change text color
                                                    dismissible: true,
                                                    duration: 10000
                                                }
                                            ]
                                        });
                                        notyf.success("<?php echo $_SESSION['success']; ?>");
                                    </script>
                                    <?php
                                    unset($_SESSION['success']);
                                    ?>
                                <?php } ?>

                                <?php if (isset($_SESSION['error'])) { ?>
                                    <script>
                                        const notyf = new Notyf({
                                            position: {
                                                x: 'center',
                                                y: 'top'
                                            },
                                            types: [
                                                {
                                                    type: 'error',
                                                    background: '#ff1916',
                                                    textColor: '#FFFFFF',
                                                    dismissible: true,
                                                    duration: 10000
                                                }
                                            ]
                                        });
                                        notyf.error("<?php echo $_SESSION['error']; ?>");
                                    </script>
                                    <?php
                                    unset($_SESSION['error']);
                                    ?>
                                <?php } ?>

                                <br />
                                <form action="" method="post" autocomplete="off" enctype="multipart/form-data"
                                    id="myForm">
                                    <div class="col-lg-12">

                                        <?php
                                        if (isset($_SESSION["login_register"]) && $_SESSION["login_register"] == TRUE) {


                                            echo '<select name="dept" required = "true" class="dept"  required style="border-color: #4CBB17" >';
                                            echo "<option value=''>Select Department</option>";
                                            while ($row = mysqli_fetch_row($dept)) {

                                                echo "<option value='" . $row['0'] . "'>" . $row['1'] . "</option>";
                                            }
                                            echo "</select>";
                                        } else {

                                            echo '<select name="dept" required="true" style="border-color: #4CBB17" onchange="window.location.href = \'login.php\'">';


                                            echo "<option value=''>Select Department</option>";
                                            while ($row = mysqli_fetch_row($dept)) {


                                                echo "<option value='" . $row['1'] . "'>" . $row['1'] . "</option>";
                                            }

                                            echo "</select>";
                                        }


                                        ?>
                                        <br />

                                    </div>
                                    <br />
                                    <div class="col-lg-12">
                                        <input type="text" class="" placeholder="Tender ID (e.g. ABC_2025_12_14)"
                                            name="tenderid" style="border-color: #4CBB17" required />

                                    </div>
                                    <br />

                                    <div class="col-lg-12">
                                        <input type="text" class="" placeholder="Enter Bid End Date " required
                                            name="datepicker" style="border-color: #4CBB17" id="datepicker" />
                                    </div>
                                    <br />

                                    <div class="col-lg-12 d-flex">
                                        <div class="col-lg-6" style="padding-right: 4px;">
                                            <input name="uploaded_file1" id="uploaded_file1" type="file"
                                                class="form-control input-md"
                                                accept="application/pdf,application/vnd.ms-excel"
                                                style="background-color: #fff; border-color: #4CBB17;">
                                        </div>
                                        <div class="col-lg-6">
                                            <input name="uploaded_file2" id="uploaded_file2" type="file"
                                                class="form-control input-md"
                                                accept="application/pdf,application/vnd.ms-excel"
                                                style="background-color: #fff; border-color: #4CBB17" disabled>
                                        </div>
                                    </div>
                                    <br />
                                    <div class="col-lg-12">
                                        <div class="g-recaptcha" data-sitekey="6LeyShEqAAAAAJIMoyXfN7DmfesxwLNYOgBHIh4N"
                                            data-callback="callback" style="border:none;">
                                        </div>
                                    </div>
                                    <br />
                                    <div class="col-lg-12">

                                        <button type="submit" class="btn lab-btn btn-block" name="submit" id="submit"
                                            disabled>
                                            <i class="feather icon-save"></i>&nbsp; Send Request
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <br />
                            <div class="banner-catagory d-flex flex-wrap content">

                                <p align="justify" style="color: #000;">
                                    Are you looking for quotations of electrical switchgear,
                                    Panels, Pumps & Motors for filing tenders? Register & Submit
                                    above to get instant quotation for any tender!<br /><br />
                                    <span style="color:#fff; background-color:#33cc33; padding:8px;">Minimun Days
                                        Required to Quote any Tender is 7-10 working Days </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6 col-xl-6">
                        <div class="banner-thumb">
                            <img src="assets/images/banner/12.png" alt="img" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="all-shapes"></div>

    </section>
    <!-- banner section ending here -->

    <!-- sponsor section start here -->
    <div class="sponsor-section section-bg">
        <div class="container">
            <div class="section-wrapper">
                <div class="sponsor-slider">
                    <div class="swiper-wrapper">
                        <?php
                        while ($row = mysqli_fetch_row($brand)) {



                            echo '<div class="swiper-slide">';
                            echo ' <div class="sponsor-iten">';
                            echo ' <div class="sponsor-thumb">';
                            echo '<img src="login/brand/' . $row['2'] . '" alt="sponsor" />';
                            echo ' </div>';
                            echo '</div>';
                            echo '</div>';
                        }

                        ?>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- sponsor section ending here -->

    <!-- category section start here -->
    <div class="category-section padding-tb  style-2">
        <div class="container">
            <div class="section-header text-center">
                <span class="subtitle">Popular Category</span>
                <h2 class="title">Popular Price Category </h2>
            </div>
            <div class="section-wrapper">
                <div class="row g-4 justify-content-center row-cols-xl-4 row-cols-lg-3 row-cols-sm-2 row-cols-1">


                    <?php

                    while ($row = mysqli_fetch_row($p)) {

                        echo '<div class="col">';
                        echo ' <div class="category-item text-center">';
                        echo ' <div class="category-inner">';
                        echo '  <div class="category-thumb">';
                        echo '<img src="login/category/' . $row['5'] . '" height="100px;" width="100px;" alt="sponsor" />';
                        echo ' </div>';
                        echo ' <div class="category-content">';
                        $res = $row[0];
                        $r = base64_encode($res);
                        echo " <a href='single-category.php?id=$r'><h4 style='color:#fff;'>" . $row[1] . "</h4> </a>";
                        // echo ' <span>Price List </span>';
                        echo '  </div>';
                        echo '</div>';
                        echo ' </div>';
                        echo ' </div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center mt-5">
        <a href="category.php" class="lab-btn"><span>Browse All Categories</span></a>
    </div>
    </div>
    <!-- category section start here -->

    <!-- blog section start here -->
    <div class="blog-section padding-tb">
        <div class="container">
            <div class="section-header text-center">
                <span class="subtitle">Sector Lists</span>
                <h2 class="title">In Area We Are Working with</h2>
            </div>
            <div class="section-wrapper">
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 justify-content-center g-4">
                    <div class="col">
                        <div class="post-item">
                            <div class="post-inner">
                                <div class="post-thumb">
                                    <a href="public-sector.php"><img src="assets/images/blog/public.jpg"
                                            alt="blog thumb"></a>
                                </div>
                                <div class="post-content">
                                    <a href="public-sector.php">
                                        <h4>Semi Govt. Sector</h4>
                                    </a>
                                </div>
                                <div class="post-footer">
                                    <div class="pf-left">
                                        <a href="public-sector.php" class="lab-btn-text">Read more <i
                                                class="icofont-external-link"></i></a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="post-item">
                            <div class="post-inner">
                                <div class="post-thumb">
                                    <a href="private-sector.php"><img src="assets/images/blog/private.jpg"
                                            alt="blog thumb"></a>
                                </div>
                                <div class="post-content">
                                    <a href="private-sector.php">
                                        <h4>Private Sector</h4>
                                    </a>

                                </div>
                                <div class="post-footer">
                                    <div class="pf-left">
                                        <a href="private-sector.php" class="lab-btn-text">Read more <i
                                                class="icofont-external-link"></i></a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="post-item">
                            <div class="post-inner">
                                <div class="post-thumb">
                                    <a href="Govt-Sector.php"><img src="assets/images/blog/govt.jpg"
                                            alt="blog thumb"></a>
                                </div>
                                <div class="post-content">
                                    <a href="Govt-Sector.php">
                                        <h4>Govt Sector</h4>
                                    </a>


                                </div>
                                <div class="post-footer">
                                    <div class="pf-left">
                                        <a href="Govt-Sector.php" class="lab-btn-text">Read more <i
                                                class="icofont-external-link"></i></a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- sponsor section start here -->
    <div class="blog-section padding-tb">
        <div class="container">
            <div class="row justify-content-center">
                <h4>Latest Update
                </h4>
                <div class="col-lg-8 col-12">
                    <article>
                        <div class="section-wrapper">
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
                                                        <li><i class="icofont-calendar">  </i> Date Added :' . $row[5] . '</li>
                                                        
                                                    
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
                            <img src="assets/images/advt-1.jpg" alt="advertise">

                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
    <!-- blog section ending here -->

    <!-- sponsor section ending here -->
    <!-- blog section ending here -->

    <!-- category section start here -->


    <!-- category section start here -->
    <!-- <div class="event-section padding-tb">
        <div class="container">
            <div class="section-header text-center">
                <span class="subtitle">Don’t Miss the Day</span>
                <h2 class="title">Latest Updates</h2>
                <p class="desc"></p>
            </div>
            <div class="section-wrapper">
                <div class="row row-cols-lg-2 row-cols-1 g-4">
                    <div class="col">
                        <div class="event-left">
                            <div class="event-item">
                                <div class="event-inner">
                                    <div class="event-thumb">
                                        <img src="assets/images/event/003.jpg" alt="education">
                                    </div>
                                    <div class="event-content">
                                        <div class="event-date-info">
                                            <div class="edi-box">
                                                <h4>13</h4>
                                                <p>Nav 2021</p>
                                            </div>
                                        </div>
                                        <div class="event-content-info">
                                            <a href="#">
                                                <h4>
                                                    INDO ASIAN SWITCH GEAR</h4>
                                            </a>
                                            <ul class="lab-ul">
                                                <li><i class="icofont-clock-time"></i> 08:30 am</li>
                                                <li><i class="icofont-google-map"></i> Brand Name : HPL</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="event-right">
                            <div class="event-item">
                                <div class="event-inner">
                                    <div class="event-content">
                                        <div class="event-date-info">
                                            <div class="edi-box">
                                                <h4>13</h4>
                                                <p>Nav 2021</p>
                                            </div>
                                        </div>
                                        <div class="event-content-info">
                                            <a href="#">
                                                <h5>
                                                    SIEMENS SWITCH GERA</h5>
                                            </a>
                                            <ul class="lab-ul">
                                                <li><i class="icofont-clock-time"></i> 08:30 am</li>
                                                <li><i class="icofont-google-map"></i> Brand Name : HPL</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="event-item">
                                <div class="event-inner">
                                    <div class="event-content">
                                        <div class="event-date-info">
                                            <div class="edi-box">
                                                <h4>13</h4>
                                                <p>Nav 2021</p>
                                            </div>
                                        </div>
                                        <div class="event-content-info">
                                            <a href="#">
                                                <h5>SCHNEIDER MCB-DB</h5>
                                            </a>
                                            <ul class="lab-ul">
                                                <li><i class="icofont-clock-time"></i> 08:30 am</li>
                                                <li><i class="icofont-google-map"></i> Brand Name : HPL</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="event-item">
                                <div class="event-inner">
                                    <div class="event-content">
                                        <div class="event-date-info">
                                            <div class="edi-box">
                                                <h4>13</h4>
                                                <p>Nav 2021</p>
                                            </div>
                                        </div>
                                        <div class="event-content-info">
                                            <a href="#">
                                                <h5>SCHNEIDER SWITCH GEAR</h5>
                                            </a>
                                            <ul class="lab-ul">
                                                <li><i class="icofont-clock-time"></i> 08:30 am</li>
                                                <li><i class="icofont-google-map"></i> Brand Name : HPL</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="event-item">
                                <div class="event-inner">
                                    <div class="event-content">
                                        <div class="event-date-info">
                                            <div class="edi-box">
                                                <h4>13</h4>
                                                <p>Nav 2021</p>
                                            </div>
                                        </div>
                                        <div class="event-content-info">
                                            <a href="#">
                                                <h5>HPL- SWITCH GEAR</h5>
                                            </a>
                                            <ul class="lab-ul">
                                                <li><i class="icofont-clock-time"></i> 08:30 am</li>
                                                <li><i class="icofont-google-map"></i> Brand Name : HPL</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    <!-- Event Section Ending Here -->
    <!-- course section start here -->
    <div class="course-section padding-tb section-bg">
        <div class="container">
            <div class="section-header text-center">
                <span class="subtitle">Featured </span>
                <h2 class="title">Top Rated</h2>
            </div>
            <div class="section-wrapper">
                <div class="row g-4 justify-content-center row-cols-xl-3 row-cols-md-2 row-cols-1">
                    <div class="col">
                        <div class="course-item">
                            <div class="course-inner">
                                <div class="course-thumb">
                                    <img src="assets/images/course/p1.jpg" alt="course" />
                                </div>
                                <div class="course-content">
                                    <div class="course-category">

                                    </div>
                                    <a href="#">
                                        <h5>Cable & wires</h5>
                                    </a>

                                    <div class="course-footer">
                                        <div class="course-btn">
                                            <a href="#" class="lab-btn-text">Read More </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="course-item">
                            <div class="course-inner">
                                <div class="course-thumb">
                                    <img src="assets/images/course/p2.jpg" alt="course" />
                                </div>
                                <div class="course-content">
                                    <div class="course-category">

                                    </div>
                                    <a href="#">
                                        <h5>
                                            Switchgear
                                        </h5>
                                    </a>

                                    <div class="course-footer">
                                        <div class="course-btn">
                                            <a href="#" class="lab-btn-text">Read More </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="course-item">
                            <div class="course-inner">
                                <div class="course-thumb">
                                    <img src="assets/images/course/p3.jpg" alt="course" />
                                </div>
                                <div class="course-content">

                                    <div class="course-category">


                                    </div>
                                    <a href="#">
                                        <h5>Motors</h5>
                                    </a>
                                    <div class="course-details">


                                    </div>


                                    <div class="course-btn">
                                        <a href="#" class="lab-btn-text">Read More </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <!-- course section ending here -->

    <!-- abouts section start here -->
    <div class="about-section">
        <div class="container">
            <div class="row justify-content-center row-cols-xl-2 row-cols-1 align-items-end flex-row-reverse">
                <div class="col">
                    <div class="about-right padding-tb">
                        <div class="section-header">
                            <span class="subtitle">About Our Quote tender</span>
                            <h2 class="title">Good Quality of Products</h2>
                            <p>
                                We pride ourselves on delivering top-notch products that meet the highest standards of
                                quality and reliability.
                                Our commitment to quality ensures that you receive only the best, tailored to meet your
                                specific needs.
                            </p>
                        </div>
                        <div class="section-wrapper">
                            <ul class="lab-ul">
                                <li>
                                    <div class="sr-left">
                                        <img src="assets/images/about/icon/01.jpg" alt="about icon" />
                                    </div>
                                    <div class="sr-right">
                                        <h5>Skilled Teams</h5>
                                        <p>
                                            Our experts ensure the highest standards of service and support.
                                        </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="sr-left">
                                        <img src="assets/images/about/icon/02.jpg" alt="about icon" />
                                    </div>
                                    <div class="sr-right">
                                        <h5>Get Price</h5>
                                        <p>
                                            Contact us for competitive rates and tailored quotes to meet your needs.
                                        </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="sr-left">
                                        <img src="assets/images/about/icon/03.jpg" alt="about icon" />
                                    </div>
                                    <div class="sr-right">
                                        <h5>Register Today</h5>
                                        <p>
                                            Join us now and unlock exclusive benefits tailored just for you.
                                        </p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="about-left">
                        <div class="about-thumb">
                            <img src="assets/images/banner/011.png" alt="about" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- about section ending here -->
    <!-- sponsor section start here -->
    <div class="sponsor-section section-bg">
        <div class="container">
            <div class="section-wrapper">
                <div class="sponsor-slider">
                    <div class="swiper-wrapper">

                        <div class="col-lg-8">
                            <h2>Get instant quotations for your tenders.</h2>
                            <p>No more hassel, no long waiting time, get quotation on your registered email and in your
                                account on
                                quotetender.com</p>
                        </div>

                        <div class="col-lg-4"><br /> <a href="registration.php" align="right"
                                style="background-color: #4CBB17; color: #fff;padding:10px ;">Get Registered & Try
                                Now</a></div>
                    </div>



                </div>
            </div>
        </div>
    </div>
    <!-- student feedbak section start here -->
    <div class="student-feedbak-section padding-tb shape-img">
        <div class="container">
            <div class="section-header text-center">
                <span class="subtitle">Loved by Clients</span>
                <h2 class="title">Clients Community Feedback</h2>
            </div>
            <div class="section-wrapper">
                <div class="row justify-content-center row-cols-lg-2 row-cols-1">
                    <div class="col">
                        <div class="sf-left">
                            <div class="sfl-thumb">
                                <img src="assets/images/feedback/youtube.jpg" alt="student feedback" />
                                <a href="https://www.youtube.com/embed/Fu7eJeot8Xg" class="video-button"
                                    data-rel="lightcase"><i class="icofont-ui-play"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="stu-feed-item">
                            <div class="stu-feed-inner">
                                <div class="stu-feed-top">
                                    <div class="sft-left">
                                        <div class="sftl-thumb">
                                            <img src="assets/images/feedback/student/2.jpg" alt="student feedback" />
                                        </div>
                                        <div class="sftl-content">
                                            <a href="#">
                                                <h6>Rakesh</h6>
                                            </a>
                                            <span>Jammu</span>
                                        </div>
                                    </div>
                                    <div class="sft-right">
                                        <span class="ratting">
                                            <i class="icofont-ui-rating"></i>
                                            <i class="icofont-ui-rating"></i>
                                            <i class="icofont-ui-rating"></i>
                                            <i class="icofont-ui-rating"></i>
                                            <i class="icofont-ui-rating"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="stu-feed-bottom">
                                    <p>
                                        Quotetender has been instrumental in shaping the trajectory of our venture
                                        towards obtaining resources for a groundbreaking project. The distinctive
                                        approach of Quotetender has undeniably laid the foundation for the ambitious
                                        pursuits of our company. We wholeheartedly endorse their services and commend
                                        the profound impact they have had on our journey.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="stu-feed-item">
                            <div class="stu-feed-inner">
                                <div class="stu-feed-top">
                                    <div class="sft-left">
                                        <div class="sftl-thumb">
                                            <img src="assets/images/feedback/student/3.jpg" alt="student feedback" />
                                        </div>
                                        <div class="sftl-content">
                                            <a href="#">
                                                <h6>Aman</h6>
                                            </a>
                                            <span>Jalandhar</span>
                                        </div>
                                    </div>
                                    <div class="sft-right">
                                        <span class="ratting">
                                            <i class="icofont-ui-rating"></i>
                                            <i class="icofont-ui-rating"></i>
                                            <i class="icofont-ui-rating"></i>
                                            <i class="icofont-ui-rating"></i>
                                            <i class="icofont-ui-rating"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="stu-feed-bottom">
                                    <p>
                                        Quotetender played a big role in helping us get the things we needed for a new
                                        and creative project. Their advice was really important & changed our first
                                        project meetings into team efforts. Quotetender's special approach not only made
                                        things different but also gave a strong base for our company's big plans. We
                                        strongly suggest their help for anyone starting a similar journey.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- student feedbak section ending here -->

    <!-- blog section start here -->
    <div class="achievement-section">


        <div class="container">

            <div id="myCarousel" class="carousel slide" data-ride="carousel">
                <!-- Indicators (optional) -->


                <!-- Slides -->
                <div class="carousel-inner">
                    <?php

                    while ($row = mysqli_fetch_row($ba)) {

                        echo ' <div class="carousel-item active">';

                        echo '<a href=""><img src="login/banner/' . $row['2'] . '" alt="sponsor" /></a>';

                        echo ' </div>';
                    }



                    ?>
                </div>

                <!-- Controls -->



            </div>
        </div>
        <!-- Achievement section start here -->
        <div class="achievement-section padding-tb">
            <div class="container">
                <div class="section-header text-center">
                    <span class="subtitle">START TO Upload</span>
                    <h2 class="title">Our Expertize</h2>
                </div>
                <div class="section-wrapper">
                    <div class="counter-part mb-4">
                        <div class="row g-4 row-cols-lg-4 row-cols-sm-2 row-cols-1 justify-content-center">
                            <div class="col">
                                <div class="count-item">
                                    <div class="count-inner">
                                        <div class="count-content">
                                            <h2>
                                                <span class="count" data-to="20" data-speed="1500"></span><span>+</span>
                                            </h2>
                                            <p>Years of Experience</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="count-item">
                                    <div class="count-inner">
                                        <div class="count-content">
                                            <h2>
                                                <span class="count" data-to="200"
                                                    data-speed="1500"></span><span>+</span>
                                            </h2>
                                            <p>Projects</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="count-item">
                                    <div class="count-inner">
                                        <div class="count-content">
                                            <h2>
                                                <span class="count" data-to="10" data-speed="1500"></span><span>+</span>
                                            </h2>
                                            <p>Qualified Experts</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="count-item">
                                    <div class="count-inner">
                                        <div class="count-content">
                                            <h2>
                                                <span class="count" data-to="300"
                                                    data-speed="1500"></span><span>+</span>
                                            </h2>
                                            <p>Clients</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="achieve-part">
                        <div class="row g-4 row-cols-1 row-cols-lg-2">
                            <div class="col">
                                <div class="achieve-item">
                                    <div class="achieve-inner">
                                        <div class="achieve-thumb">
                                            <img src="assets/images/achive/01.png" alt="achieve thumb" />
                                        </div>
                                        <div class="achieve-content">
                                            <h4>Start Tendering Today</h4>
                                            <p>
                                                Seamlessly engage technically sound coaborative
                                                reintermed goal oriented content rather than ethica
                                            </p>
                                            <a href="#" class="lab-btn"><span>Become A Partner</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="achieve-item">
                                    <div class="achieve-inner">
                                        <div class="achieve-thumb">
                                            <img src="assets/images/achive/02.png" alt="achieve thumb" />
                                        </div>
                                        <div class="achieve-content">
                                            <h4>If You Join us</h4>
                                            <p>
                                                Seamlessly engage technically sound coaborative
                                                reintermed goal oriented content rather than ethica
                                            </p>
                                            <a href="registration.php" class="lab-btn"><span>Register For
                                                    Free</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Achievement section ending here -->
        <!-- sponsor section start here -->
        <!--<div class="sponsor-section section-bg">-->
        <!--    <div class="container">-->
        <!--        <div class="section-wrapper">-->
        <!--            <div class="sponsor-slider">-->
        <!--                <div class="swiper-wrapper">-->
        <!--                    <div class="swiper-slide">-->
        <!--                        <div class="sponsor-iten">-->
        <!--                            <div class="sponsor-thumb"><img src="login/brand/64f5ba719885e_s4.png" alt="sponsor" /> </div>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="swiper-slide">-->
        <!--                        <div class="sponsor-iten">-->
        <!--                            <div class="sponsor-thumb"><img src="login/brand/64f5ba7b711d8_s3.png" alt="sponsor" /> </div>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="swiper-slide">-->
        <!--                        <div class="sponsor-iten">-->
        <!--                            <div class="sponsor-thumb"><img src="login/brand/64f5ba8664785_s5.png" alt="sponsor" /> </div>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="swiper-slide">-->
        <!--                        <div class="sponsor-iten">-->
        <!--                            <div class="sponsor-thumb"><img src="login/brand/64f5ed927dfd5_1461329264.png" alt="sponsor" /> </div>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="swiper-slide">-->
        <!--                        <div class="sponsor-iten">-->
        <!--                            <div class="sponsor-thumb"><img src="login/brand/64fc0932272a2_L&T.png" alt="sponsor" /> </div>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="swiper-slide">-->
        <!--                        <div class="sponsor-iten">-->
        <!--                            <div class="sponsor-thumb"><img src="login/brand/64fc09c75feb3_indoasian.png" alt="sponsor" /> </div>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="swiper-slide">-->
        <!--                        <div class="sponsor-iten">-->
        <!--                            <div class="sponsor-thumb"><img src="login/brand/64fc09d89ec21_HPL.jpg" alt="sponsor" /> </div>-->
        <!--                        </div>-->
        <!--                    </div>-->

        <!--                </div>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->
        <!-- footer -->
        <div class="news-footer-wrap">
            <div class="fs-shape">
                <img src="assets/images/shape-img/03.png" alt="fst" class="fst-1" />
                <img src="assets/images/shape-img/04.png" alt="fst" class="fst-2" />
            </div>
            <!-- Newsletter Section Start Here -->
            <div class="news-letter">
                <div class="container">
                    <div class="section-wrapper">
                        <div class="news-title">
                            <h3>Want Us To Email You About Special Offers And Updates?</h3>
                        </div>
                        <div class="news-form">
                            <form action="#">
                                <div class="nf-list">
                                    <input type="email" name="email" placeholder="Enter Your Email" />
                                    <input type="submit" name="submit" value="Subscribe Now" />
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


        <!-- Add these links in the head section of your HTML file -->

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

        <script>
            var url = 'https://wati-integration-prod-service.clare.ai/v2/watiWidget.js?1511';
            var s = document.createElement('script');
            s.type = 'text/javascript';
            s.async = true;
            s.src = url;
            var options = {
                "enabled": true,
                "chatButtonSetting": {
                    "backgroundColor": "#4CBB17",
                    "ctaText": "Chat with us",
                    "borderRadius": "25",
                    "marginLeft": "35",
                    "marginRight": "0",
                    "marginBottom": "20",
                    "ctaIconWATI": false,
                    "position": "left"
                },
                "brandSetting": {
                    "brandName": "Quote Tender",
                    "brandSubTitle": "undefined",
                    "brandImg": "https://www.wati.io/wp-content/uploads/2023/04/Wati-logo.svg",
                    "welcomeText": "Hi there!\nHow can I help you?",
                    "messageText": "{{page_link}}Hello, %0A I have a question about {{page_link}}",
                    "backgroundColor": "#f5831f",
                    "ctaText": "Chat with us",
                    "borderRadius": "25",
                    "autoShow": false,
                    "phoneNumber": "+919417601244"
                }
            };
            s.onload = function () {
                CreateWhatsappChatWidget(options);
            };
            var x = document.getElementsByTagName('script')[0];
            x.parentNode.insertBefore(s, x);
        </script>
        <script>
            $(document).ready(function () {
                $("#goldmessage").delay(8000).slideUp(300);
            });
        </script>

        <script type="text/javascript">
            function callback() {
                const submitButton = document.getElementById("submit");
                submitButton.removeAttribute("disabled");
            }
        </script>

        <script>
            $(document).ready(function () {
                $("#datepicker").datepicker({
                    dateFormat: 'yy-mm-dd'
                });
            });

            document.getElementById('uploaded_file1').addEventListener('change', function () {
                var file1Input = this;
                var file2Input = document.getElementById('uploaded_file2');

                file2Input.disabled = !file1Input.value;
            });
        </script>



        <script>
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        </script>


</body>

</html>