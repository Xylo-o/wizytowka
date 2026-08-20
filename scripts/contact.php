<?php

// Auto loader import
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = getenv('SMTP_HOST');
$mail->SMTPAuth   = true;
$mail->Username   = getenv('SMTP_USER');
$mail->Password   = getenv('SMTP_PASS');
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = (int) getenv('SMTP_PORT');
$mail->CharSet    = 'UTF-8';

$allowed  = ['pet-care','errands','pickup','shopping','home-help','booking'];
$category = in_array($_POST['category'] ?? '', $allowed, true) ? $ $_POST['category'] : 'other';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        http_response_code(400);
        echo 'Please fill in all required fields.';
        exit;
    }

    // Sending mail
    try {
        $mail->setFrom(getenv('MAIL_FROM'), 'Spyrja Contact Form');
        $mail->addAddress(getenv('MAIL_TO'));
        $mail->addReplyTo($email, $name);

        $mail->Subject = 'New contact form submission';
        $mail->Body    = "Name: {$name}\nEmail: {$email}\n\n{$message}";
        $mail->send();
    } catch (Exception $e) {
        http_response_code(500);
        echo 'Error sending message.';
        exit;
    }
    
    http_response_code(200);
    echo 'Thank you for your message!';
    
} else {
    http_response_code(405);
    echo 'Method Not Allowed';
}
