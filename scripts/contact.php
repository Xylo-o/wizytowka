<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$smtpHost = $_ENV['SMTP_HOST'];
$smtpUser = $_ENV['SMTP_USER'];
$smtpPass = $_ENV['SMTP_PASS'];
$smtpPort = $_ENV['SMTP_PORT'];

// Simple sanitizer
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Form submission
$errors = [];
$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['name']    ?? '');
    $email   = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (!$name)    $errors[] = 'Please enter your name.';
    if (!$email)   $errors[] = 'Please enter a valid email address.';
    if (!$subject) $errors[] = 'Please enter a subject.';
    if (!$message) $errors[] = 'Please enter your message.';

    if (empty($errors)) {
        $mail = new PHPMailer(true);
        try {
            // SMTP setup
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            $mail->SMTPSecure = ($smtpPort == 465)
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtpPort;

            // From and to
            $mail->setFrom($smtpUser, 'SPYRJA Contact Form');
            $mail->addAddress($smtpUser);            // sends to your SPYRJA inbox
            $mail->addReplyTo($email, $name);       // reply goes to visitor

            // Content
            $mail->isHTML(true);
            $mail->Subject = '[SPYRJA Contact] ' . $subject;
            $mail->Body    = "
                <p><strong>From:</strong> {$name} &lt;{$email}&gt;</p>
                <p><strong>Subject:</strong> {$subject}</p>
                <hr>
                <p>" . nl2br($message) . "</p>
            ";
            $mail->AltBody = "From: {$name} <{$email}>\nSubject: {$subject}\n\n{$message}";

            $mail->send();
            $sent = true;
        } catch (Exception $e) {
            $errors[] = 'Message could not be sent: ' . $mail->ErrorInfo;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact SPYRJA</title>
  <style>
    body { font-family: sans-serif; padding: 1rem; max-width: 600px; margin: auto; }
    .error { color: #c00; }
    .success { color: #080; }
    form > div { margin-bottom: 1em; }
    label { display: block; margin-bottom: .5em; }
    input, textarea { width: 100%; padding: .5em; box-sizing: border-box; }
    button { padding: .7em 1.2em; }
  </style>
</head>
<body>

<h1>Contact SPYRJA</h1>

<?php if ($sent): ?>
  <p class="success">Thank you! Your message has been sent.</p>
<?php else: ?>
  <?php if ($errors): ?>
    <ul class="error">
      <?php foreach ($errors as $err): ?>
        <li><?= $err ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form method="post" action="">
    <div>
      <label for="name">Name</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    </div>
    <div>
      <label for="email">Your Email</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div>
      <label for="subject">Subject</label>
      <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
    </div>
    <div>
      <label for="message">Message</label>
      <textarea id="message" name="message" rows="6"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
    </div>
    <button type="submit">Send Message</button>
  </form>
<?php endif; ?>

</body>
</html>