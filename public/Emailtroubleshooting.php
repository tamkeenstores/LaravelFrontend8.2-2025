<?php
// Enable debugging output
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';
require __DIR__ . '/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = '65-108-232-29.cprapid.com';    // Your SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'sales@tamkeenstores.com.sa'; // SMTP username
    $mail->Password   = 'Tamkeen@9200!@#';    // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Or ENCRYPTION_SMTPS
    $mail->Port       = 587; // or 465 for SMTPS

    // Sender and recipient
    $mail->setFrom('qaiserabbas613@gmail.com', 'Your Name');
    $mail->addAddress('qaiserabbas613@gmail.com', 'Recipient Name');

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from PHPMailer';
    $mail->Body    = '<h1>This is a test email</h1><p>Sent via PHPMailer using SMTP.</p>';
    $mail->AltBody = 'This is a plain-text version of the email content.';

    $mail->send();
    echo 'Message has been sent successfully.';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
