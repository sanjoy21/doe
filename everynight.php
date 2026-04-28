<?php
$nologinrequired = true;
require "mysql.php";
require_once "services.php";

$fromautomated = true;

// Get the database connection link for safe queries
$db_link = $GLOBALS['link'] ?? $link; 

// --- 1. Date/Time Calculations ---
// Calculate timestamps for future and past dates
$today = time();
$fourweeks_ts = mktime( 0, 0, 0, date( "m", $today ), date( "d", $today ) + 28, date("Y", $today ) );
$threeweeks_ts = mktime( 0, 0, 0, date( "m", $today ), date( "d", $today ) + 21, date("Y", $today ) );
$yesterday_ts = mktime( 0, 0, 0, date( "m", $today ), date( "d", $today ) - 1, date("Y", $today ) );
$threedays_ts = mktime( 0, 0, 0, date( "m", $today ), date( "d", $today ) + 3, date("Y", $today ) );

// Format for SQL (Y-m-d)
$fourweeks_sql = date("Y-m-d", $fourweeks_ts );
$threeweeks_sql = date("Y-m-d", $threeweeks_ts );
$yesterday_sql = date("Y-m-d", $yesterday_ts );
$threedays_sql = date("Y-m-d", $threedays_ts );

// --- 2. Automated Trainer Request for Upcoming Classes ---

// Query: Classes starting in 3 weeks OR confirmed yesterday AND starting in 4 weeks, with no trainers yet assigned.
$sql_trainer_request = "SELECT 
                            class.id 
                        FROM 
                            class, company_esi 
                        WHERE 
                            ( startdate LIKE '{$threeweeks_sql}%' 
                            OR ( confirmdate LIKE '{$yesterday_sql}%' AND startdate <= '{$fourweeks_sql}' ) ) 
                            AND accepted = 1 
                            AND canceldate IS NULL 
                            AND code NOT IN ( 'MHFA', 'AEDI', 'Inspections', 'TCF Meeting', 'Esinew', 'Misc', 'Trade', 'Call', 'call', 'Office', 'party', 'misc' ) 
                            AND iscorp <> 3 
                            AND companyid = company_esi.id 
                            AND isnational = 0 
                            AND companyname NOT LIKE 'Sample%' 
                            AND companyname NOT LIKE 'Open Registration' 
                            AND numtrainers > 0";

$expiring = db_query_rows( $sql_trainer_request );
$newemails = array();

foreach( $expiring as $e )
{
    $class_id = (int)($e['id'] ?? 0);
    if ($class_id === 0) continue;

    // echo( "would send to {$class_id}<br>" );
    
    // Check if trainers have already been assigned (numtrainers in the main query is an inconsistent filter here)
    $num = db_query_first_cell("SELECT COUNT(*) FROM trainer_to_class WHERE classid = {$class_id}" );
    if( $num ) continue;

    // Request trainers for the class (assuming requestTrainers() exists and returns an array of trainer IDs)
    $tmp = requestTrainers( $class_id, false, false );
    
    foreach( $tmp as $tid )
    {
        $newemails[$tid] = $tid;
    }

    if( count($tmp ) > 0 ) {
        // Log that a request was sent
        db_query( "UPDATE class SET lasttrainerreqdate = NOW() WHERE id = {$class_id}" ); 
    }
}

// --- 3. Send Alert Email to Trainers with New Opportunities ---
foreach( $newemails as $trainerid )
{
    $trainerid_safe = (int)$trainerid;
    
    $subject = "Alert! Instructors needed for upcoming classes!";
    
    // Assuming URL_WITHOUT_SUBDOMAIN is a global constant/variable
    $url_domain = $GLOBALS['URL_WITHOUT_SUBDOMAIN'] ?? URL_WITHOUT_SUBDOMAIN ?? 'emergencyskills.com'; 
    $sub_doe = $GLOBALS['SUB_DOE'] ?? SUB_DOE ?? 'doe';
    $encoded_trainer_id = $trainerid_safe * 1234; // Obfuscation retained from original logic
    
    $body = " Instructors needed!
        Click here to view all available instructor opportunities:

https://{$sub_doe}.{$url_domain}/requesttotrain.php?trainerid={$encoded_trainer_id}\n\n";
    
    // Assuming getUserEmail() and sendMail() exist
    sendMail( getUserEmail( $trainerid_safe ), $subject, $body, "barbara@emergencyskills.com", "Scheduling Alert" );
    // sendMail( "rachelc@gmail.com", $subject, $body, "barbara@emergencyskills.com", "Scheduling Alert" ); // Debugging
}


// --- 4. Post-Class Task: Update Responder Records (Non-corporate, 2 years ago) ---
if( 1 == 1 )
{
    // Check for classes completed 2 years ago yesterday (for non-corporate locations)
    $lastnight_minus_2_years_ts = mktime( 0,0,0,date( "m", $today ), date( "d", $today )-1, date("Y", $today )-2 );
    $lastnight_minus_2_years_sql = date("Y-m-d", $lastnight_minus_2_years_ts );

    $sql_responder_update = "SELECT 
                                rtc.classid, rtc.responderid 
                            FROM 
                                responder_to_class rtc
                            JOIN 
                                class c ON rtc.classid = c.id
                            JOIN 
                                company_esi ce ON c.companyid = ce.id
                            WHERE 
                                c.startdate LIKE '{$lastnight_minus_2_years_sql}%' 
                                AND ce.iscorp = 0";
    
    // echo( $sql_responder_update . "<br>" ); // Debugging
    $expiring = db_query_rows( $sql_responder_update );

    foreach( $expiring as $e )
    {
        $responder_id = (int)($e['responderid'] ?? 0);
        
        // echo( $e['classid'] . ", {$responder_id} : " );
        $arow = getResponderRow($responder_id); // Assuming getResponderRow() exists
        // echo( $arow['pmsid'] . ", " );
        
        $res = updateResponder( $arow ); // Assuming updateResponder() exists and updates responder status
        // echo( $res . "<br>" );
    }
}

// --- 5. Post-Class Task: Update Instructor Stage (Yesterday's Classes) ---
if( 1 == 1 )
{
    // Check for classes completed yesterday
    $yesterday_ts = mktime( 0,0,0,date( "m", $today ), date( "d", $today )-1, date("Y", $today ) );
    $yesterday_sql = date("Y-m-d", $yesterday_ts );

    $sql_trainer_stage_update = "SELECT 
                                    ttc.classid, ttc.trainerid 
                                FROM 
                                    trainer_to_class ttc
                                JOIN
                                    class c ON ttc.classid = c.id
                                WHERE 
                                    c.startdate LIKE '{$yesterday_sql}%'";
    
    // echo( $sql_trainer_stage_update . "<br>" ); // Debugging
    $increasing = db_query_rows( $sql_trainer_stage_update );
    
    foreach( $increasing as $e )
    {
        $current_trainer_id = (int)($e['trainerid'] ?? 0);
        if ($current_trainer_id === 0) continue;
        
        // Check current instructor stage
        $stage = db_query_first_cell( "SELECT instructorstage FROM user WHERE id = {$current_trainer_id}" );
        $stage_safe = (string)$stage;
        
        if( $stage_safe != "Completed" )
        {
            // If stage is a number, increment it; otherwise, this logic might be flawed 
            // but we follow the intent of the original logic.
            if (is_numeric($stage_safe)) {
                $new_stage = (int)$stage_safe + 1;
            } else {
                // If stage is non-numeric string (other than 'Completed'), treat as 1
                $new_stage = 1; 
            }
            
            // Update the user's instructor stage
            db_query( "UPDATE user SET instructorstage = '{$new_stage}' WHERE id = {$current_trainer_id}" );
        }
    }
}


// --- 6. Pre-Class Task: Send 3-Day Attendee Reminder ---
$sql_attendee_reminder = "SELECT 
                            id 
                        FROM 
                            class 
                        WHERE 
                            startdate LIKE '{$threedays_sql}%'
                            AND accepted = 1 
                            AND canceldate IS NULL";
// echo( $sql_attendee_reminder . "<br>" ); // Debugging
$expiring = db_query_rows( $sql_attendee_reminder );

foreach( $expiring as $e )
{
    $class_id = (int)($e['id'] ?? 0);
    if ($class_id === 0) continue;
    
     echo( "sending to {$class_id}<br>" );
    sendToAttendees( $class_id, true ); // Assuming sendToAttendees() exists
}

// --- 7. (Optional/Disabled Block) Additional Logic for 15-Day Trainer Request ---
/*
// $fifteendays = mktime( 0,0,0,date( "m" ), date( "d" )+21, date("Y" ) );
// $sql = ( "select id from class where startdate like '".date("Y-m-d", $fifteendays )."%'  and accepted = 1 and canceldate is null" );
// // echo( $sql . "<br>");
// $upcoming = db_query_array( $sql, "id", "id" );

// foreach( $upcoming as $id )
// {
//     requestTrainers( $id );
// }
*/



if( 1 == 0 )
{
$lastnight = mktime( 0,0,0,date( "m" ), date( "d" )-1, date("Y" )-2 );
$lastnight_sql = date("Y-m-d", $lastnight);

$sql = ( "select * from class, company_esi where startdate like '{$lastnight_sql}%' and class.companyid = company_esi.id and iscorp = 0" );
// // echo( $sql . "<br>");
$expiring = db_query_rows( $sql );

foreach( $expiring as $e )
{
 echo( $e['classid'] . "<br>" );
// echo( $res . "<br>" );
}
}

?>