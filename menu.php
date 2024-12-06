
<div class="container">
    <div class="header-wrapper">
        <div class="logo">
            <a href="index.php"><img src="assets/images/logo/logo.png" width="200px"="logo"  alt="quotetender-logo"/></a>
        </div>
        <div class="menu-area">
            <div class="menu">
                <ul class="lab-ul">
                    <li>
                        <a href="index.php">Home</a>
                    </li>

                    <li>
                        <a href="about-us.php">About us</a>
                    </li>
                    <li>
                        <a href="#">Our Category</a>
                        <?php
                        echo '<ul class="lab-ul">';
                        while ($row22 = mysqli_fetch_row($q)) {
                            $res = $row22[0];
                            $rt = base64_encode($res);
                            echo "<li> <a href='single-category.php?id=$rt'>" . $row22[1] . " </a></li>";
                        }
                        echo ' </ul>';
                        ?>
                    </li>
                    

                    <li>
                        <a href="user/tender-request.php">Amendment Tender</a>
                    </li>
                    <?php
                    
                    $memberQuery1 = "SELECT name FROM members WHERE email_id='" . $_SESSION["login_register"] . "'";
$memberData1 = mysqli_query($db, $memberQuery1);
$member1 = mysqli_fetch_row($memberData1);
                    
                    if (isset($_SESSION["login_register"])) {
                          
                     echo '<li><a href="user/home.php" class="btn lab-btn">Welcome ' . $member1[0]. '</a>';

                                    echo '<ul class="lab-ul">';
                                    
                                     echo '<li><a href="user/edit-profile.php">update Profile</a></li>' ;
                                       echo ' <li><a href="user/tender-request.php">View Tender Request</a></li>';
                                       
                                        echo '<li><a href="user/logout.php">Sign Out</a></li>
                                    </ul>
                                </li>';
                    }
                    ?>
                </ul>
            </div>
            <?php
            if (!isset($_SESSION["login_register"])) {
                echo '<a href="login.php" class="login"><i class="icofont-users"></i> <span>LOG IN</span>
             
    </a>';
     echo '<a href="registration.php" class="signup"><i class="icofont-users"></i> <span>SIGN UP</span> </a>';
            }


            ?>


            <!-- toggle icons -->
            <div class="header-bar d-lg-none">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="ellepsis-bar d-lg-none">
                <i class="icofont-info-square"></i>
            </div>
        </div>
    </div>
</div>