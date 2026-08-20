<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: text/plain; charset=utf-8');

/* ---------------------------------------------------------------- *
 * 1. Localised responses
 *    The form posts a hidden `lang` field so the visitor gets an answer
 *    in the language they were reading.
 * ---------------------------------------------------------------- */
const MESSAGES = [
    'en' => [
        'success'    => 'Thank you for your message!',
        'required'   => 'Please fill in all required fields.',
        'bad_email'  => 'Please enter a valid e-mail address.',
        'too_long'   => 'One of the fields is too long.',
        'bad_chars'  => 'Invalid characters in name or e-mail.',
        'rate_limit' => 'Too many messages sent. Please try again later.',
        'not_config' => 'The contact form is not configured. Please e-mail us directly.',
        'send_error' => 'Error sending message. Please try again later.',
    ],
    'is' => [
        'success'    => 'Takk fyrir að hafa samband!',
        'required'   => 'Vinsamlegast fylltu út alla nauðsynlega reiti.',
        'bad_email'  => 'Sláðu inn gilt netfang.',
        'too_long'   => 'Einn reitanna er of langur.',
        'bad_chars'  => 'Ógild tákn í nafni eða netfangi.',
        'rate_limit' => 'Of mörg skilaboð send. Reyndu aftur síðar.',
        'not_config' => 'Tengiliðaformið er ekki uppsett. Sendu okkur tölvupóst beint.',
        'send_error' => 'Villa kom upp við að senda skilaboð. Reyndu aftur síðar.',
    ],
];

$lang = ($_POST['lang'] ?? 'en') === 'is' ? 'is' : 'en';

/** Send a status code with the localised body and stop. */
function respond(int $code, string $key, string $lang): never
{
    http_response_code($code);
    exit(MESSAGES[$lang][$key] ?? MESSAGES['en'][$key]);
}

// vendor/ is git-ignored, so a fresh clone has no dependencies until
// `composer install` has been run. Fail with a readable message, not a
// white-screen fatal error.
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
    error_log('contact.php: vendor/autoload.php missing — run "composer install"');
    respond(500, 'not_config', $lang);
}
require_once $autoload;

/* ---------------------------------------------------------------- *
 * 2. Method guard — nothing else runs on a non-POST request.
 * ---------------------------------------------------------------- */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

/* ---------------------------------------------------------------- *
 * 3. Honeypot — a field no human ever fills in.
 *    Answer 200 so the bot believes it succeeded and moves on.
 * ---------------------------------------------------------------- */
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    respond(200, 'success', $lang);
}

/* ---------------------------------------------------------------- *
 * 4. Input
 * ---------------------------------------------------------------- */
$name    = trim((string) ($_POST['name'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$rawCat  = trim((string) ($_POST['category'] ?? ''));

// Canonical category slugs. These MUST match the <option value="..."> attributes
// in index.html and index_is.html.
const CATEGORIES = [
    'pet-care'  => 'Pet Care & Services',
    'errands'   => 'Errands & Queuing',
    'pickup'    => 'Pickup & Delivery',
    'shopping'  => 'Shopping & Gift Handling',
    'home-help' => 'Home Help & Coordination',
    'booking'   => 'Booking & Scheduling',
];

$category = array_key_exists($rawCat, CATEGORIES) ? CATEGORIES[$rawCat] : 'Unspecified';

/* ---------------------------------------------------------------- *
 * 5. Validation
 * ---------------------------------------------------------------- */
if ($name === '' || $email === '' || $message === '') {
    respond(400, 'required', $lang);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(400, 'bad_email', $lang);
}

if (mb_strlen($name) > 200 || mb_strlen($email) > 254 || mb_strlen($message) > 5000) {
    respond(400, 'too_long', $lang);
}

// Reject CR/LF in the values that end up in mail headers.
if (preg_match('/[\r\n]/', $name . $email)) {
    respond(400, 'bad_chars', $lang);
}

/* ---------------------------------------------------------------- *
 * 6. Rate limit — 3 submissions per IP per 10 minutes.
 *    File-based, so it works on shared hosting without Redis.
 * ---------------------------------------------------------------- */
$ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$bucket  = sys_get_temp_dir() . '/spyrja_rl_' . sha1($ip);
$window  = 600;
$maxHits = 3;

$hits = [];
if (is_readable($bucket)) {
    $hits = array_filter(
        (array) json_decode((string) file_get_contents($bucket), true),
        static fn($t) => is_int($t) && $t > time() - $window
    );
}
if (count($hits) >= $maxHits) {
    header('Retry-After: ' . $window);
    respond(429, 'rate_limit', $lang);
}
$hits[] = time();
@file_put_contents($bucket, json_encode(array_values($hits)), LOCK_EX);

/* ---------------------------------------------------------------- *
 * 7. Configuration — every value comes from the environment.
 * ---------------------------------------------------------------- */
$config = [
    'host' => getenv('SMTP_HOST') ?: '',
    'port' => (int) (getenv('SMTP_PORT') ?: 587),
    'user' => getenv('SMTP_USER') ?: '',
    'pass' => getenv('SMTP_PASS') ?: '',
    'from' => getenv('MAIL_FROM') ?: '',
    'to'   => getenv('MAIL_TO') ?: '',
];

foreach (['host', 'user', 'pass', 'from', 'to'] as $key) {
    if ($config[$key] === '') {
        error_log("contact.php: missing environment variable for '{$key}'");
        respond(500, 'not_config', $lang);
    }
}

/* ---------------------------------------------------------------- *
 * 8. Send — ONE PHPMailer instance, configured once.
 * ---------------------------------------------------------------- */
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $config['host'];
    $mail->Port       = $config['port'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['user'];
    $mail->Password   = $config['pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->CharSet    = PHPMailer::CHARSET_UTF8;

    // Envelope sender belongs to our own domain, so SPF/DKIM stay aligned.
    $mail->setFrom($config['from'], 'Spyrja Contact Form');
    $mail->addAddress($config['to']);
    $mail->addReplyTo($email, $name);   // replies go straight to the visitor

    $mail->Subject = 'New enquiry: ' . $category;
    $mail->Body    = "Name:     {$name}\n"
                   . "E-mail:   {$email}\n"
                   . "Category: {$category}\n"
                   . "IP:       {$ip}\n"
                   . str_repeat('-', 40) . "\n\n"
                   . $message;

    $mail->send();
} catch (Exception $e) {
    error_log('contact.php: ' . $mail->ErrorInfo);
    respond(500, 'send_error', $lang);
}

http_response_code(200);
echo MESSAGES[$lang]['success'];
