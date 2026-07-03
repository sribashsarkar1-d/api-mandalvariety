<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendInvoiceMail($toEmail, $toName, $subject, $body, $pdfPath = null)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'roy338004@gmail.com';
        $mail->Password   = 'npny pdiu brbj tlly'; // not normal gmail password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // for 465
        $mail->Port       = 465;

        $mail->CharSet = 'UTF-8';
        $mail->setFrom('roy338004@gmail.com', 'Mandal Variety');
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo('roy338004@gmail.com', 'Mandal Variety');

        if (!empty($pdfPath) && file_exists($pdfPath)) {
            $mail->addAttachment($pdfPath);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));

        return [
            'success' => $mail->send(),
            'error'   => ''
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error'   => $mail->ErrorInfo
        ];
    }
}