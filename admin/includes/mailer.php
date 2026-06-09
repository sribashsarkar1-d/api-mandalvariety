<?php
// Correct relative path from admin/includes to the root PHPMailer folder
require_once dirname(dirname(__DIR__)) . '/PHPMailer/src/Exception.php';
require_once dirname(dirname(__DIR__)) . '/PHPMailer/src/PHPMailer.php';
require_once dirname(dirname(__DIR__)) . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendAdminPasswordResetLink($email, $resetLink) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'roy338004@gmail.com';
        $mail->Password   = 'npny pdiu brbj tlly';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('roy338004@gmail.com', 'NEXUS Auth API Admin');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Admin Password Reset Request';

        $mail->Body = "
        <div style='font-family:Arial, sans-serif; padding:20px; color:#333;'>
            <h2 style='color:#198754;'>Admin Password Reset</h2>
            <p>You requested a password reset for your admin account.</p>
            <p>Please click the button below to reset your password:</p>
            
            <a href='$resetLink' style='
                display:inline-block;
                background-color:#198754;
                color:#ffffff;
                padding:12px 24px;
                text-decoration:none;
                border-radius:6px;
                font-weight:bold;
                margin:20px 0;
            '>Reset Password</a>
            
            <p>If the button doesn't work, you can copy and paste the following link into your browser:</p>
            <p><a href='$resetLink'>$resetLink</a></p>
            
            <p style='color:#777; font-size:12px; margin-top:30px;'>
                If you did not request this, please ignore this email. This link will expire in 1 hour.
            </p>
        </div>
        ";

        $mail->AltBody = "You requested an admin password reset.\n\nPlease go to the following link to reset your password:\n$resetLink\n\nThis link will expire in 1 hour.";

        $mail->send();

        return [
            'status' => true,
            'message' => 'Reset link sent successfully'
        ];

    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return [
            'status' => false,
            'message' => $mail->ErrorInfo
        ];
    }
}
?>
