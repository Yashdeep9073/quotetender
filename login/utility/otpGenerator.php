<?php

// require '../../env.php';
// require '../../vendor/autoload.php';

function otpGenerate($adminId, $db, $mail)
{
    // Generate 6-digit OTP
    $otp = rand(100000, 999999);

    // Extract timezone from settings
    $timezone = 'UTC';

    // Create DateTime object with specified timezone
    $dateTime = new DateTime('now', new DateTimeZone($timezone));
    $dateTime->modify('+2 minutes');
    $expiresAt = $dateTime->format('Y-m-d H:i:s');

    // Insert OTP into table
    $stmt = $db->prepare("INSERT INTO admin_otp (admin_id, otp_code, expires_at) VALUES (?, ?, ?)");
    if (!$stmt) {
        error_log("OTP Insert Prepare Error: " . $db->error);
        return [
            "success" => false,
            "message" => "Failed to prepare OTP insertion: " . $db->error
        ];
    }

    $stmt->bind_param("iss", $adminId, $otp, $expiresAt);

    if ($stmt->execute()) {
        // Get last inserted ID
        $lastInsertedId = $db->insert_id;

        // Admin
        $stmtIsValidAdmin = $db->prepare("SELECT * FROM admin WHERE id = ? AND status = 1");
        if (!$stmtIsValidAdmin) {
            error_log("Admin Select Prepare Error: " . $db->error);
            return [
                "success" => false,
                "message" => "Failed to prepare admin selection: " . $db->error
            ];
        }

        $stmtIsValidAdmin->bind_param("i", $adminId);
        if (!$stmtIsValidAdmin->execute()) {
            error_log("Admin Select Execute Error: " . $stmtIsValidAdmin->error);
            return [
                "success" => false,
                "message" => "Failed to execute admin selection: " . $stmtIsValidAdmin->error
            ];
        }

        $resultAdmin = $stmtIsValidAdmin->get_result();
        if ($resultAdmin->num_rows === 0) {
            return [
                "success" => false,
                "message" => "Admin not found or inactive"
            ];
        }

        $rowAdmin = $resultAdmin->fetch_assoc();
        $adminName = $rowAdmin['username'];
        $adminEmail = $rowAdmin['email'];


        // Configure PHPMailer
        try {
            $mail->SMTPDebug = 2; // Enable verbose debug output
            $mail->Debugoutput = function ($str, $level) {
                error_log("PHPMailer [$level] $str");
            };

            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER_NAME');
            $mail->Password = getenv('SMTP_PASSCODE');
            $mail->SMTPSecure = "ssl";
            $mail->Port = getenv('SMTP_PORT');
            $mail->CharSet = 'UTF-8';

            // Validate SMTP configuration
            if (empty($mail->Host)) {
                throw new Exception("SMTP Host is not configured");
            }
            if (empty($mail->Username)) {
                throw new Exception("SMTP Username is not configured");
            }
            if (empty($mail->Password)) {
                throw new Exception("SMTP Password is not configured");
            }
            if (empty($mail->Port)) {
                throw new Exception("SMTP Port is not configured");
            }

            $mail->setFrom(getenv('SMTP_USER_NAME'), "Quote Tender");
            $mail->addAddress($adminEmail, htmlspecialchars($adminName));
            $mail->isHTML(true);


            $mail->Subject = "Your One-Time Password (OTP) for Login";

            // Email body
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <img src='https://www.quotetender.in/assets/images/logo/logo.png' alt='Quote Tender Logo' style='max-width: 200px; height: auto;'>
                    </div>
                    <p style='font-size: 18px; color: #555;'>Dear <strong>" . htmlspecialchars($adminName) . "</strong>,</p>
                    <p>We have received a login attempt on your account. For security, please verify your identity using the One-Time Password (OTP) below: <strong>" . htmlspecialchars($otp) . "</strong>.</p>
                    <p>This OTP will expire in 2 minutes.</p>
                    <p>If you have any questions or need further assistance regarding the process, feel free to reach out to us. We are here to help!</p>
                    
                    <p style='margin-top: 20px;'>
                        <strong>Thanks & Regards,</strong><br/>
                        <span style='color: #4CBB17;'>Admin, Quote Tender</span><br/>
                        <span>Mobile: <a href='tel:+919417601244' style='color: #4CBB17; text-decoration: none;'>+91-9417601244</a></span><br/>
                        <span>Email: <a href='mailto:help@quotetender.in' style='color: #4CBB17; text-decoration: none;'>help@quotetender.in</a></span>
                    </p>

                    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>

                    <p style='text-align: center; font-size: 12px; color: #888;'>
                        © 2024 Quote Tender. All Rights Reserved.
                    </p>
                </div>";


            
            // Try to send email
            if (!$mail->send()) {
                $errorMessage = "Mailer Error: " . $mail->ErrorInfo;
                error_log($errorMessage);
                return [
                    "success" => false,
                    "message" => "Failed to send OTP email: " . $mail->ErrorInfo
                ];
            }

            // Success
            return [
                "success" => true,
                "otp" => $otp,
                "otpId" => $lastInsertedId,
                "expiresAt" => $expiresAt
            ];

        } catch (Exception $e) {
            $errorMessage = "PHPMailer Exception: " . $e->getMessage();
            error_log($errorMessage);
            return [
                "success" => false,
                "message" => "Email configuration error: " . $e->getMessage()
            ];
        }

    } else {
        $errorMessage = "OTP Generation Error: " . $stmt->error;
        error_log($errorMessage);
        return [
            "success" => false,
            "message" => "Failed to generate OTP: " . $stmt->error
        ];
    }
}
?>