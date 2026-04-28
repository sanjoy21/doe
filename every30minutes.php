<?php
$nologinrequired = true;
require_once "mysql.php";
require_once "services.php";

// Get the database connection link for escaping strings (if needed)
$db_link = $GLOBALS['link'] ?? $link; 

// --- 1. Calculate the 3-week cutoff time ---
// Using time() for the current timestamp and simple addition for the 3-week future date.
// 3 weeks = 21 days * 24 hours * 60 minutes * 60 seconds
$current_timestamp = time();
$threeweeks_timestamp = $current_timestamp + (3 * 7 * 24 * 60 * 60);

// Format the date for SQL comparison
$threeweeks_sql = date("Y-m-d H:i:s", $threeweeks_timestamp );

// --- 2. Query for upcoming, accepted, corporate ('%TSI%') classes with trainers assigned ---
$sql = "SELECT 
            class.id, company_esi.companyname 
        FROM 
            class 
        JOIN 
            company_esi ON class.companyid = company_esi.id 
        WHERE 
            class.startdate > NOW() 
            AND class.startdate <= '{$threeweeks_sql}' 
            AND company_esi.companyname LIKE '%TSI%' 
            AND company_esi.iscorp = 1 
            AND class.accepted = 1 
            AND class.canceldate IS NULL 
            AND class.numtrainers > 0";

//echo( $sql . "<br>"); // Debugging output retained from original script
$expiring = db_query_rows( $sql );

// --- 3. Loop through classes and send roster email to each trainer ---
foreach( $expiring as $e )
{
    $class_id = (int)($e['id'] ?? 0);
    $company_name_safe = htmlspecialchars($e['companyname'] ?? 'Unknown Company');
    $safe_class_id = htmlspecialchars($class_id);

    if ($class_id === 0) continue; // Skip if no valid class ID

    // Fetch all trainers assigned to this class
    $trainers = db_query_rows( "SELECT 
                                    user.* FROM 
                                    trainer_to_class 
                                JOIN 
                                    user ON user.id = trainerid 
                                WHERE 
                                    classid = {$class_id}" );

    // Fetch the list of approved responders (students) for this class
    $responders = db_query_rows( "SELECT 
                                    r.firstname, r.lastname, rtc.position 
                                FROM 
                                    responder_to_class rtc 
                                JOIN 
                                    responders_esi r ON rtc.responderid = r.responderid 
                                WHERE 
                                    rtc.classid = {$class_id} 
                                ORDER BY 
                                    rtc.position" );

    foreach( $trainers as $t )
    {
        $trainer_first_name_safe = htmlspecialchars($t['first_name'] ?? '');
        $trainer_last_name_safe = htmlspecialchars($t['last_name'] ?? '');
        $trainer_userid = $t['userid'] ?? '';
        
        // --- Construct Email Body (HTML format is often better for mail) ---
        $body = "Dear {$trainer_first_name_safe} {$trainer_last_name_safe}, 

The following students are authorized to take program 
<a href='http://".SUB_DOE.".".URL_WITHOUT_SUBDOMAIN."/class_detail.php?id={$safe_class_id}'>#{$safe_class_id}</a> at {$company_name_safe}. 

<br><br>";

        $roster_list = "";
        foreach( $responders as $r )
        {
            $position_safe = htmlspecialchars($r['position'] ?? '');
            $first_name_safe = htmlspecialchars($r['firstname'] ?? '');
            $last_name_safe = htmlspecialchars($r['lastname'] ?? '');

            $roster_list .= "{$position_safe}. {$first_name_safe} {$last_name_safe}<br>"; // Use <br> for line breaks in HTML email
        }
        $body .= $roster_list;
        
        $body .= "<br><br>
Walk-ins are not allowed at TSI classes, as all students must either pay in advance or have prior approval.

Please remember to scan and email your completed roster to cards@emergencyskills.com.

Thanks!
";
        // --- Send Mail (Assuming sendMail function is available and handles HTML content) ---
        sendMail( $trainer_userid, "Your Upcoming TSI Class Roster", $body, "info@emergencyskills.com" );
//        sendMail( "rachelc@gmail.com", "Your Upcoming TSI Class Roster ({$trainer_userid})", $body, "info@emergencyskills.com" ); // Debugging mail
//        echo( "sending {$t['login']}<br>{$body}<br><br>" ); // Debugging output
    }
}


?>