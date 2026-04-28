<?php

require_once('mysql.php');

if (!$specialadmin) {
    header("location: login.php");
    exit;
}

// --- Fetch Class and Company Details ---
$crow = getClassRow($classid);
$comrow = getCompanyRow($crow["companyid"]);
$iscorp = $comrow["iscorp"];

// PHPMailer is required before the loop
require_once "class.phpmailer.php";

// Assumes $allclass_names is available globally
$class_names = $allclass_names[$iscorp];

// --- Fetch All Training Faculty Members ---
$trows = db_query_rows("SELECT * FROM user WHERE tcfaculty = 1");

// --- FIX: Compile List of Assigned Instructors ---
$current_trainers_data = getTrainers($classid);
$instructor_names = [];

if (is_array($current_trainers_data)) {
    foreach ($current_trainers_data as $trainer_id => $data) {
        // Assuming 'name' field is available in the data returned by getTrainers
        $instructor_names[] = htmlspecialchars($data['name']);
    }
}
$tstr = empty($instructor_names) ? "None Assigned Yet" : implode(", ", $instructor_names);


// --- Prepare Template Variables ---
$class_name = htmlspecialchars($class_names[$crow["code"]]);
$class_date_time = htmlspecialchars(getFormattedDateWTime($crow['startdate']));
$end_date_str = htmlspecialchars(getEndDateStr($crow['enddate']));
$school_str = getSchoolStr("School", $iscorp);
$company_name = htmlspecialchars($comrow['companyname']);
$company_address = htmlspecialchars($comrow['address']);
$company_city = htmlspecialchars($comrow['city']);
$company_zip = htmlspecialchars($comrow['zip']);
$training_location = htmlspecialchars(getTrainingAddress($crow));

// --- Mass Email Loop ---
foreach ($trows as $trow) {
    $to_email = $trow['userid'];

    // --- Generate Email Body for THIS recipient ---
    $initbody = "
Dear Training Faculty,

A Training Faculty member is needed to monitor an instructor at this class.  Please let me know in the next 48 hours if you are available.


Class: {$class_name} 
Course Number: {$classid} 
Class Date: {$class_date_time} {$end_date_str}
{$school_str}: {$company_name}
Entrance: {$company_address}, {$company_city}, {$company_zip}
Training Location: {$training_location}
Instructor(s):  {$tstr}


Thanks,
Barbara
";

    if (!empty(trim($to_email))) {
        $mail = new PHPMailer();
        $mail->IsSMTP(); // Added later to fix html format by Sanjoy Dey
        $mail->Host = 'localhost'; // Added later to fix html format by Sanjoy Dey
        $mail->SMTPAuth = false; // Added later to fix html format by Sanjoy Dey
        $mail->Port = 25; // Added later to fix html format by Sanjoy Dey
        $mail->From = "dzamos@emergencyskills.com";
        $mail->FromName = "Dylan Zamos";
        $mail->AddReplyTo("dzamos@emergencyskills.com");
        $mail->AddCC("barbara@emergencyskills.com");
        
        $mail->Subject = stripslashes("Training Faculty Needed"); // Subject is constant and plain text
        $mail->IsHTML(false); // Set email format to plain text
        $mail->Body = $initbody;
        
        $mail->AddAddress(trim($to_email));
        $mail->Send();
    }
}

// --- Database Update and Redirect ---
// Log the time of the last special TCF request for this class
db_query("UPDATE class SET lastspecialtfrequestdate = NOW() WHERE id = '{$classid}'");  
// Redirect to class detail page with success message
header("Location: class_detail.php?id={$classid}&sent=1");
exit;
?>