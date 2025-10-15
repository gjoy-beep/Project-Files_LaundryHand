<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require 'connection.php';
date_default_timezone_set('Asia/Manila');
$conn->query("SET time_zone = '+08:00'");

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $query = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ✅ Check 7-day limit
        $seven_days_ago = date("Y-m-d H:i:s", strtotime("-7 days"));
        if (!empty($user['last_reset_request']) && $user['last_reset_request'] > $seven_days_ago) {
            $_SESSION['forgot_message'] = '⚠️ You can only request a password reset once per week. Please try again later.';
            $_SESSION['forgot_success'] = false;
            header('Location: forgotpassword.php');
            exit;
        }

        // ✅ Generate token and expiry
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // ✅ Save token + expiry + last_reset_request
        $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ?, last_reset_request = NOW() WHERE email = ?");
        $update->bind_param("sss", $token, $expiry, $email);
        $update->execute();

        // ✅ Create reset link
        $reset_link = "http://localhost/PHP-LaundryHand/reset_password.php?token=" . urlencode($token);

        // ✅ Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'gloribelaguipo@gmail.com';
            $mail->Password = 'louufgmwsuxhndor'; // Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('gloribelaguipo@gmail.com', 'LaundryHand Support');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h3>Hi!</h3>
                    <p>We received a password reset request for your <b>LaundryHand</b> account.</p>
                    <p>Click the button below to reset your password:</p>
                    <p style='text-align:center;'>
                        <a href='" . htmlspecialchars($reset_link) . "' 
                           style='background-color:#ff80a0;color:#fff;
                                  padding:10px 20px;border-radius:6px;
                                  text-decoration:none;' target='_blank'>Reset Password</a>
                    </p>
                    <p>This link will expire in <b>10 minutes.</b></p>
                    <p>If you didn’t request this, please ignore this email.</p>
                    <hr>
                    <p style='font-size:12px;color:#888;'>If the button doesn’t work, copy and paste this link:<br>
                    <a href='" . htmlspecialchars($reset_link) . "'>" . htmlspecialchars($reset_link) . "</a></p>
                </div>
            ";
            $mail->send();

            $_SESSION['forgot_message'] = '✅ A password reset link has been sent to your email.';
            $_SESSION['forgot_success'] = true;

        } catch (Exception $e) {
            $_SESSION['forgot_message'] = '❌ Failed to send email: ' . $mail->ErrorInfo;
            $_SESSION['forgot_success'] = false;
        }

    } else {
        $_SESSION['forgot_message'] = '⚠️ Email not found in our system.';
        $_SESSION['forgot_success'] = false;
    }

    $conn->close();
    header('Location: forgotpassword.php');
    exit;
}
?>
