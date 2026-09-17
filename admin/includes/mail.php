<?php
use PHPMailer\PHPMailer\PHPMailer;

require '../vendor/autoload.php';

function sendMail($to, $subject, $body){

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'sribashsarkarblp@gmail.com';
$mail->Password = 'mjkl wzow ycsq jnps';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('sribashsarkarblp@gmail.com', 'Mandal Variety');
$mail->addAddress($to);

$mail->Subject = $subject;
$mail->Body = $body;

$mail->send();
}