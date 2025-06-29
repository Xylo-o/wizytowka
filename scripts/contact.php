<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Load environment variables from local_test/.env if present.
 */
function get_env(string $key, $default = null) {
    static $vars;
    if ($vars === null) {
        $file = __DIR__ . '/../local_test/.env';
        if (is_readable($file)) {
            $vars = parse_ini_file($file, false, INI_SCANNER_RAW);
        } else {
            $vars = [];
        }
    }
    return $vars[$key] ?? getenv($key) ?? $default;
}

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
        $mail->isSMTP();
        $mail->Host       = get_env('SMTP_HOST', 'smtp.gmail.com');
        $mail->Port       = (int) get_env('SMTP_PORT', 587);
        $mail->SMTPAuth   = true;
        $mail->Username   = get_env('SMTP_USER');
        $mail->Password   = get_env('SMTP_PASS');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $from      = get_env('SMTP_FROM', get_env('SMTP_USER'));
        $fromName  = get_env('SMTP_FROM_NAME', 'Spyrja Contact Form');
        $recipient = get_env('SMTP_TO', 'samband@spyrja.com');

        $mail->setFrom($from, $fromName);
        $mail->addAddress($recipient);
        $mail->addReplyTo($email, $name);

        $mail->Subject = 'New contact form submission';
        $mail->Body    = "Name: {$name}\nEmail: {$email}\n\n{$message}";

        $mail->send();
        http_response_code(200);
        echo 'Thank you for your message!';
    } catch (Exception $e) {
        http_response_code(500);
        echo 'Error sending message.';
    }
    
} else {
    http_response_code(405);
    echo 'Method Not Allowed';
}