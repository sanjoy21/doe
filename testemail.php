<?php

$nologinrequired = true;
include "mysql.php";

sendHTMLMail_Office( "rachelc@gmail.com", "hello world", "whatever body", "info@emergencyskills.com" );

exit;

// --- PHPMailer Example (Original code, preserved for context) ---

/**
 * This example shows making an SMTP connection with authentication.
 */

// Import the PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

// Define placeholder variables for sensitive credentials (BEST PRACTICE)
// NOTE: You should use secure environment variables or a configuration file here.
$smtpUsername = 'info@emergencyskills.com';
$smtpPassword = 'Alive!1504'; 

date_default_timezone_set('Etc/UTC');

try {
    $mail = new PHPMailer(true);
    $mail->IsSMTP();
    $mail->Host = 'smtp.office365.com';
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    
    $mail->Username = $smtpUsername;
    $mail->Password = $smtpPassword;
    
    $mail->From = $mail->Username; // mandatory and identical to Username property
    $mail->FromName = "Emergency Skills"; // mandatory and identical to Username property

    $mail->Subject = "Hello message";
    $mail->Body = "Body goes here...";
    $mail->addAddress('rachelc@gmail.com');


    if(!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'Message has been sent';
    }
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$e->getMessage()}";
}
?>