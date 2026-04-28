<?php

$nologinrequired = true;
include "mysql.php";

// Set the start and end dates for the upcoming week (Monday to Monday)
$sat = strtotime("this Monday");
$nextsat = mktime(0, 0, 0, date("m", $sat), date("d", $sat) + 7, date("Y", $sat));

$start_date = date("Y-m-d", $sat);
$end_date = date("Y-m-d", $nextsat);

// --- 1. Fetch Confirmed Classes for the Upcoming Week ---
// Query for confirmed, undeleted classes within the date range
// NOTE: Variables are being used unsafely in the query string.
$sql_classes = "SELECT * FROM class 
                WHERE confirmdate <> '' 
                AND confirmdate IS NOT NULL 
                AND deleted = 0 
                AND startdate >= '{$start_date}' 
                AND startdate < '{$end_date}'";
$classes = db_query_rows($sql_classes, "id");
$trainers = array();

// Aggregate classes by trainer ID
foreach ($classes as $c) {
    // 1a. Get assigned trainers (assuming getTrainers is defined in mysql.php)
    $tr = getTrainers($c['id']);

    foreach ($tr as $trow) {
        $tid = $trow['trainerid'];
        if (!isset($trainers[$tid])) {
            $trainers[$tid] = array();
        }
        $trainers[$tid][] = $c['id'];
    }

    // 1b. Include TC Faculty
    if ($c['tcfacultyid']) {
        $tid = $c['tcfacultyid'];
        if (!isset($trainers[$tid])) {
            $trainers[$tid] = array();
        }
        $trainers[$tid][] = $c['id'];
    }
}

// --- 2. Send Upcoming Class Notifications to Trainers ---
foreach ($trainers as $tid => $thisarr) {
    // Assuming getUserEmail($tid) is defined in mysql.php
    $temail = getUserEmail($tid);

    // Skip if email is empty (no user found or no email set)
    if (empty($temail)) {
        continue;
    }

    require_once "class.phpmailer.php";
    $mail = new PHPMailer();

    $body = "Here are your upcoming classes for the week of " . date("m/d/Y", $sat) . ":\n";

    foreach ($thisarr as $cid) {
        $crow = $classes[$cid];
        // Assuming getCompanyRow and allclass_names are defined elsewhere
        $comrow = getCompanyRow($crow['companyid']);
        $class_names = ($comrow['iscorp'] ?? 0) ? ($allclass_names[1] ?? []) : ($allclass_names[0] ?? []);

        $body .= "\n";
        // Assuming getFormattedDateWTime is defined elsewhere
        $body .= getFormattedDateWTime($crow['startdate']) . "\n";
        $body .= ($comrow['companyname'] ?? 'N/A') . " \n";
        $body .= ($class_names[$crow['code']] ?? 'Unknown Class') . "\n";
        $body .= "\n";

        if ($crow['tcfacultyid'] == $tid) {
            $body .= "You are attending as TC Faculty\n";
        }

        // Location Contact Information
        $body .= "Location Contact: {$crow['firstname']} {$crow['lastname']}
Contact Phone Number: {$crow['phone']}
Emergency Contact: {$crow['emergency_name']}
Emergency Phone Number: {$crow['emergency_cell']}
";

        if ($crow["remote"]) {
            // Remote Class details
            $body .= "REMOTE CLASS\n";
            $body .= "Link: " . ($crow['teamslink'] ?? '') . "\n";
        } else {
            // In-person class details
            // Assuming getSchoolStr and getTrainingAddress are defined elsewhere
            $body .= "\n";
            $body .= "Parking/Security: " . ($crow["parking_security"] ?? '') . "\n";
            $body .= "Nearest Subway Line/Station: " . ($crow["nearest_subway"] ?? '') . "\n";
            $body .= getSchoolStr("Training Location", $crow['iscorp'] ?? 0) . ": " . getTrainingAddress($crow) . "\n";
            $body .= getSchoolStr("School Entrance", $crow['iscorp'] ?? 0) . ": " . ($crow["school_entrance"] ?? '');
            $body .= "\n";
        }
        
        // Instructor Notes (formatted for plain text body)
        $body .= "Instructor Notes: " . ($crow["instructornotes"] ?? '') . "\n";
    }

    $mail->From = "info@emergencyskills.com";
    $mail->FromName = "Emergencyskills.com";
    $mail->AddReplyTo("info@emergencyskills.com", "Emergencyskills.com");
    $mail->WordWrap = 50;
    
    $mail->Subject = "Your classes for the week";
    $mail->IsHTML(false);
    $mail->Body = $body;
    
    $mail->AddAddress($temail);
    $mail->Send(); // Error handling simplified to remove comments
}

// --- 3. Send Drill/Inspection Reminders to Inspectors/Drill Schedulers ---
// Assuming getAllTrainers() is defined in mysql.php and returns all users/trainers
$trainers = getAllTrainers();

foreach ($trainers as $t) {
    // Assuming getVisibleZips($t['id']) is defined in mysql.php
    $vis = getVisibleZips($t['id']);
    
    // Check if user has visible zips defined
    if (!$vis) {
        continue;
    }
    
    // Sanitizing the $vis string is crucial as it goes into the SQL query
    // Using addslashes as a placeholder for proper escaping
    $safe_vis = addslashes($vis);

    // Find schools in their Zips with expired AED pads (limited to 15)
    // NOTE: Variables are being used unsafely in the query string.
    $sql_aed = "SELECT company_esi.id, companyname, pediatricpads, padaexpiration 
                FROM aed_esi, company_esi 
                WHERE deleted = 0 
                AND iscorp = 0 
                AND clientid = company_esi.id 
                AND zip IN ({$safe_vis}) 
                AND (padaexpiration < NOW() OR pediatricpads < NOW()) 
                GROUP BY company_esi.id 
                LIMIT 15";
                
    $thisarr = db_query_rows($sql_aed);
    
    if (count($thisarr)) {
        $body = "Dear Drill/Inspectors,

Please respond to this email by Sunday with an update of the schools you serviced in the past week. This is mandatory.

The following is the list of schools due for a drill/inspection next week. Please make schools with expired pads your top priority.

";

        require_once "class.phpmailer.php";
        $mail = new PHPMailer();

        foreach ($thisarr as $crow) {
            // Find last drill date for the company
            // NOTE: Variables are being used unsafely in the query string.
            $sql_last_drill = "SELECT drilldate FROM drill 
                               LEFT JOIN drill_to_companyid dtc ON drill.drillid = dtc.drillid 
                               WHERE (dtc.companyid = '{$crow['id']}' OR drill.companyid = '{$crow['id']}') 
                               ORDER BY drilldate DESC LIMIT 1";
            $lastdate = db_query_first_cell($sql_last_drill);
            $lastdrilldate = $lastdate ? date("m/d/Y", strtotime($lastdate)) : "Never";
            
            $expstr = "";
            
            // Check for expired pads
            if (strtotime($crow["padaexpiration"] ?? '9999-12-31') < time()) {
                $expstr .= " - Pad A Expired"; 
            }
            if (strtotime($crow["pediatricpads"] ?? '9999-12-31') < time()) {
                $expstr .= " - Pediatric Pads Expired"; // Original code had 'Pad A Expired' twice
            }
            
            $body .= $crow['companyname'] . " - Last Drill " . $lastdrilldate . " " . $expstr . " \n\n";
        }
        
        $body .= "
Thank you,
Sarah Gillen
";
        
        $mail->From = "sarahg@emergencyskills.com";
        $mail->FromName = "Sarah Gillen";
        $mail->AddReplyTo("sarahg@emergencyskills.com", "Sarah Gillen");
        $mail->WordWrap = 50;
        
        $mail->Subject = "Your drills for the week";
        $mail->IsHTML(true); // Email is sent as HTML
        $mail->Body = nl2br($body); // Convert newlines to <br> for HTML
        $mail->AltBody = $body; // Plain text fallback
        
        $mail->AddAddress($t['userid']);
        $mail->AddBCC("rachel@vireo.org");
        $mail->Send(); // Error handling simplified
    }
}
?>