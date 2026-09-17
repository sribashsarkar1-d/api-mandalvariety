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
        $mail->Username   = 'sribashsarkarblp@gmail.com';
        $mail->Password   = 'mjkl wzow ycsq jnps'; // not normal gmail password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
        $mail->Port       = 465;                                   

        $mail->CharSet = 'UTF-8';
        //Recipients
        $mail->setFrom('sribashsarkarblp@gmail.com', 'Mandal Variety');
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo('sribashsarkarblp@gmail.com', 'Mandal Variety');

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