<?php
use PHPMailer\PHPMailer\PHPMailer;

require '../vendor/autoload.php';

function sendMail($to, $subject, $body){

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'roy338004@gmail.com';
$mail->Password = 'npny pdiu brbj tlly';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('roy338004@gmail.com', 'Mandal Variety');
$mail->addAddress($to);

$mail->Subject = $subject;
$mail->Body = $body;

$mail->send();
}