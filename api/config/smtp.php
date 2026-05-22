<?php
require_once dirname(__DIR__) . '/PHPMailer/src/Exception.php';
require_once dirname(__DIR__) . '/PHPMailer/src/PHPMailer.php';
require_once dirname(__DIR__) . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOTP($email, $otp) {

    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'roy338004@gmail.com';
        $mail->Password   = 'npny pdiu brbj tlly';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('roy338004@gmail.com', 'NEXUS Auth API');

        $mail->addAddress($email);

        $mail->isHTML(true);

        $mail->Subject = 'NEXUS Login OTP';

        $mail->Body = "
        <div style='font-family:Arial;padding:20px'>
            <h2>NEXUS Login Verification</h2>

            <p>Your Login OTP:</p>

            <div style='
                font-size:40px;
                font-weight:bold;
                color:#007bff;
                letter-spacing:5px;
                margin:20px 0;
            '>
                $otp
            </div>

            <p>OTP valid for 10 minutes.</p>
        </div>
        ";

        $mail->AltBody = "Your OTP is: $otp";

        $mail->send();

        return [
            'status' => true,
            'message' => 'OTP sent successfully'
        ];

    } catch (Exception $e) {

        error_log($mail->ErrorInfo);

        return [
            'status' => false,
            'message' => $mail->ErrorInfo
        ];
    }
}
?>