<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Adjust these paths if PHPMailer is located elsewhere:
require __DIR__ . '/assets/php/Exception.php';
require __DIR__ . '/assets/php/PHPMailer.php';
require __DIR__ . '/assets/php/SMTP.php';

// Error handling: no display, log to file
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

function log_mail_event($msg){
  $date = date('[Y-m-d H:i:s]');
  file_put_contents(__DIR__ . '/mail.log', "$date $msg\n", FILE_APPEND);
}

// Spam honeypot
if (!empty($_POST['website'])) {
  http_response_code(200);
  exit; // silently drop
}

// Input validation
$name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');

if (!$name || !$email || !$message) {
  http_response_code(400);
  exit('Ungültige Eingabe.');
}

// Simple IP-based rate limit (best-effort)
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$key = sys_get_temp_dir() . '/form_' . md5($ip);
$hits = (int)@file_get_contents($key);
if ($hits > 40) { http_response_code(429); exit('Zu viele Versuche.'); }
file_put_contents($key, (string)($hits + 1), LOCK_EX);

$subject = "Neue Kontaktanfrage von $name";
$bodyText = "Von: $name\nE-Mail: $email\nNachricht:\n$message";
$bodyHTML = "<h3>Neue Nachricht über das Kontaktformular</h3>
<p><strong>Name:</strong> {$name}</p>
<p><strong>E-Mail:</strong> {$email}</p>
<p><strong>Nachricht:</strong><br>" . nl2br($message) . "</p>";

try {
  $mail = new PHPMailer(true);
  // SMTP (Strato)
  $mail->isSMTP();
  $mail->Host = 'smtp.strato.de';
  $mail->SMTPAuth = true;
  $mail->Username = 'info@it-services-level-1.de';            // TODO: set your mailbox
  $mail->Password = 'HM1978.@Email007';                // TODO: set your password (do NOT commit to Git)
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port = 587;
  $mail->CharSet = 'UTF-8';

  // Sender/recipient (must match login for SPF/DMARC)
  $mail->setFrom('info@it-services-level-1.de', 'IT Service Level1');
  $mail->addAddress('info@it-services-level-1.de', 'IT Service Level1');
  $mail->addReplyTo($email, $name);

  // Content
  $mail->isHTML(true);
  $mail->Subject = $subject;
  $mail->Body    = $bodyHTML;
  $mail->AltBody = $bodyText;

  $mail->send();
  log_mail_event("OK SMTP from [$email] $name");
  http_response_code(200);
  echo 'OK';
} catch (Exception $e) {
  log_mail_event('SMTP failed: ' . ($mail->ErrorInfo ?? ''));
  // Fallback via mail()
  $headers  = "From: IT Service Level1 <info@it-services-level-1.de>\r\n";
  $headers .= "Reply-To: $email\r\n";
  $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
  if (mail('info@it-services-level-1.de', $subject, $bodyText, $headers)) {
    log_mail_event('Fallback mail() OK');
    echo 'OK';
  } else {
    log_mail_event('Fallback mail() failed');
    http_response_code(500);
    echo 'ERR';
  }
}
