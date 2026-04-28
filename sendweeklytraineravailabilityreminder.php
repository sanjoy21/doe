<?php
// Set flag to indicate login is not required for this utility script
$nologinrequired = true;
include "mysql.php";
// Assumed external function: getAllTrainers, URL_WITHOUT_SUBDOMAIN

$subject = "Update Availability";

// Define the email body template
$body_template = "Dear Instructor, 

Please click the link below to review your current availability and make any necessary updates. Remember to add any individual dates you will be out in the next 2 - 4 week, especially if they occur on dates that you would generally be available. 

http://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/trainer_availability.php?theid=TRAINERID

Thank you,

Barbara Kinter
Program Manager
Emergency Skills, Inc.
305 7th Avenue, Suite 1100
New York, NY 	10001
212-564-6833
www.emergencyskills.com
";

require_once "class.phpmailer.php";

// Fetch all trainers
$trainers = getAllTrainers();

// Loop through each trainer to send a personalized email
foreach ($trainers as $tid => $thisarr) {
    // Ensure we have a valid email address (usually stored in 'userid' field)
    $to_email = $thisarr['userid'] ?? '';
    
    if (empty(trim($to_email))) {
        // Skip trainers without a valid email address
        error_log("Skipping trainer ID {$tid}: no email address found.");
        continue;
    }

    $mail = new PHPMailer();

    // --- Sender Details ---
    $mail->From = "info@emergencyskills.com";
    $mail->FromName = "Emergencyskills.com";
    $mail->AddReplyTo("info@emergencyskills.com", "Emergencyskills.com");
    $mail->WordWrap = 50; // Set word wrap to 50 characters

    // --- Personalize Body ---
    // Replace the TRAINERID placeholder with the actual trainer's ID
    $trainer_id = $thisarr['id'] ?? 0;
    $tmpbody = str_replace("TRAINERID", urlencode((string)$trainer_id), $body_template);

    // --- Email Content Settings ---
    $mail->Subject = $subject;
    $mail->IsHTML(false); // Set email format to plain text
    $mail->Body = $tmpbody;

    // --- Recipient ---
    $mail->AddAddress(trim($to_email));
    
    // --- Send Email ---
    if (!$mail->Send()) {
        // Log failure but continue to the next trainer
        error_log("PHPMailer failed to send availability update to trainer {$trainer_id} ({$to_email}). Error: " . $mail->ErrorInfo);
    }
}
?>