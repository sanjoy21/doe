<?php
// not used 

// Declare that a login session is not required for this page
$nologinrequired = 1;

// Include the database connection file and utility functions
include "mysql.php";

// Initialize external variables safely
$testing = $testing ?? null;

// Determine the target date for the query
$dt = date("Y-m-d");

if ($testing) {
    // Override for testing purposes
    $dt = "2020-06-20";
}

// SQL Query to find attendees for today's specific classes
// Assumed external functions: escMe(), db_query_rows()
$dt_safe = escMe($dt);

$sql = "SELECT 
            co.iscorp, c.code, u.email, c.id AS classid, u.responderid, 
            c.startdate, c.training_location, c.training_city, 
            c.training_state, c.training_zip 
        FROM responder_to_class ttc
        JOIN class c ON c.id = ttc.classid
        JOIN responders_esi u ON u.responderid = ttc.responderid
        JOIN company_esi co ON co.id = c.companyid
        WHERE 
            c.startdate LIKE '{$dt_safe}%' 
            AND (c.isconferenceroom = 1 OR co.region = 'Parks')";
            
$todays = db_query_rows($sql);

$body = ""; // Initialize body, though it's rebuilt in the loop
// Assumed external functions: getTrainingAddress(), allclass_names[], sendHTMLMail()

foreach ($todays as $t) {
    // Determine the cancellation email address
    $em = ($t["iscorp"] ?? 0) ? "tcadmin@emergencyskills.com" : "scott@emergencyskills.com"; 

    // Retrieve class code name (assumes $allclass_names is a globally available array)
    $class_code_name = $allclass_names[$t['iscorp'] ?? 0][$t['code'] ?? ''] ?? 'N/A';
    
    // Build the email body
    $body = "
You are registered for CPR/AED Training today at: 
<br>
Class Code: " . htmlspecialchars($class_code_name) . "<br>
Time: " . htmlspecialchars($t['startdate'] ?? '') . "<br>
Location: " . htmlspecialchars(getTrainingAddress($t)) . "<br>
<br>

<b> Face coverings are required for all ESI in person classes. </b>

<br>
If you are concerned you might have COVID either because of exposure or symptoms, please postpone your training. To reschedule, please call Emergency Skills, Inc. at 212-564-6833.
<br>
If you must cancel, please forward this email to " . htmlspecialchars($em) . ", indicating CANCEL CLASS REGISTRATION in the subject. 
";
    
    // Send the email (assumes sendHTMLMail is defined)
    sendHTMLMail(
        $t['email'] ?? '', 
        "CPR/AED confirmation", 
        $body
    );
}
?>