<?php

// $specialadmin = $specialadmin;
// $classid = $classid;
// $trainerid = $trainerid;
$body = $_POST['body'];

require_once('mysql.php');

if (!$specialadmin) {
    header("location: login.php");
    exit;
}

// --- Fetch Class, Company, and Trainer Details ---
$crow = getClassRow($classid);
$comrow = getCompanyRow($crow["companyid"]);
$trow = getUserRow($trainerid);
$iscorp = $comrow["iscorp"];

// Assumes $allclass_names is available globally
$class_names = $allclass_names[$iscorp];
$class_name = $class_names[$crow["code"]];
$school_str = getSchoolStr("School", $iscorp);

// --- Build Email Content ---
$trainer_first_name = htmlspecialchars($trow['first_name']);
$class_date_time = htmlspecialchars(getFormattedDateWTime($crow['startdate']));
$end_date_str = htmlspecialchars(getEndDateStr($crow['enddate']));
$company_name = htmlspecialchars($comrow['companyname']);
$company_address = htmlspecialchars($comrow['address']);
$company_city = htmlspecialchars($comrow['city']);
$company_zip = htmlspecialchars($comrow['zip']);
$training_location = htmlspecialchars(getTrainingAddress($crow));

$initbody = "
Hi {$trainer_first_name},

The host school has requested you as the instructor for the following program.  Please let me know in the next 24 hours if you are available and would like to teach the class.

Class: {$class_name} 
Course Number: {$classid} 
Class Date: {$class_date_time} {$end_date_str}
{$school_str}: {$company_name}
Entrance: {$company_address}, {$company_city}, {$company_zip}
Training Location: {$training_location}

Thanks,
Barbara
";

// --- PHPMailer Setup and Send ---
$to = $trow['userid']; // Trainer's email address
$trainer_email = trim($to);

if (empty($trainer_email)) {
    // Handle case where trainer email is missing/invalid, potentially redirect with an error message
    // For now, we'll just log an error or redirect silently
    // In a real application, you'd want better error handling here.
    error_log("Attempted to send special request for class $classid to trainer $trainerid, but email is missing.");
    header("Location: class_detail.php?id=$classid&error=noemail");
    exit;
}

$body = stripslashes($body); // This line remains but has no practical effect since $body is empty
require_once "class.phpmailer.php";

$mail = new PHPMailer();
$mail->FromName = "Dylan Zamos";
$mail->From = "dzamos@emergencyskills.com";
$mail->AddReplyTo("dzamos@emergencyskills.com");
$mail->AddCC("barbara@emergencyskills.com");

$mail->Subject = stripslashes("Special Instructor Request");
$mail->IsHTML(false); // Set email format to plain text
$mail->Body = $initbody;

$mail->AddAddress($trainer_email);

if ($mail->Send()) {
    // Update the class record with the time the request was sent
    db_query("UPDATE class SET lastspecialrequestdate = NOW() WHERE id = '{$classid}'");
    // Redirect to class detail page with success message
    header("Location: class_detail.php?id={$classid}&sent=1");
} else {
    // Redirect with an error message
    error_log("PHPMailer Error for class $classid: " . $mail->ErrorInfo);
    header("Location: class_detail.php?id={$classid}&error=sendfailed");
}

exit;
?>