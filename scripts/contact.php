<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        http_response_code(400);
        echo 'Please fill in all required fields.';
        exit;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->setFrom('no-reply@spyrja.com', 'Spyrja Contact Form');
        // Temporary test address
        $mail->addAddress('tuadrian1327@gmail.com');
        $mail->addReplyTo($email, $name);

        $mail->Subject = 'New contact form submission';
        $mail->Body    = "Name: {$name}\nEmail: {$email}\n\n{$message}";
        $mail->send();
        echo 'Thank you for your message!';
    } catch (Exception $e) {
        http_response_code(500);
        echo 'Error sending message.';
    }
} else {
    http_response_code(405);
    echo 'Method Not Allowed';
}

