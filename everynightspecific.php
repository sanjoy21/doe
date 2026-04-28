<?php
// Note: This script is intended to be run by a scheduler (cron job) and has no HTML output.
$nologinrequired = true;
include "mysql.php";
require_once "services.php";

// Set flag for automated processes
$fromautomated = true;

// --- 1. Data Cleanup: Replace special hyphen with standard hyphen ---
// These operations clean up character encoding issues in company names and addresses.
db_query("UPDATE company_esi SET companyname = REPLACE(companyname, '‐', '-') WHERE companyname LIKE '%‐%'");
db_query("UPDATE company_esi SET address = REPLACE(address, '‐', '-') WHERE address LIKE '%‐ %'");

// --- 2. Calculate Target Date (3 Weeks Out) ---
// Calculates the date 19 days from the current date (used for scheduling checks)
$threeweeks = strtotime("+19 days"); 
$target_date = date("Y-m-d", $threeweeks);

// --- 3. Trainer Request Logic (Main Function) ---

// Select classes scheduled for the target date that meet specific criteria:
// - Accepted (accepted = 1)
// - Not canceled (canceldate is null)
// - Exclude internal/misc class codes ('MHFA', 'AEDI', 'Inspections', etc.)
// - Not corporate types (iscorp < 3)
// - Company is linked and not a sample/open registration
// - Not national (isnational = 0)
// - Requires trainers (numtrainers > 0)
$sql = "SELECT class.id 
        FROM class, company_esi 
        WHERE DATE(startdate) = '{$target_date}' 
        AND startdate NOT LIKE '2018-06-07%' 
        AND accepted = 1 
        AND canceldate IS NULL 
        AND code NOT IN ('MHFA', 'AEDI', 'Inspections', 'TCF Meeting', 'Esinew', 'Misc', 'Trade', 'Call', 'call', 'Office', 'party', 'misc') 
        AND iscorp < 3 
        AND companyid = company_esi.id 
        AND isnational = 0 
        AND companyname NOT LIKE 'Sample%' 
        AND companyname NOT LIKE 'Open Registration' 
        AND numtrainers > 0";

echo $sql . "<br>";

$expiring = db_query_rows($sql);

foreach ($expiring as $e) {
    $id = $e['id'];
    
    // Check if trainers have already been assigned
    $num = db_query_first_cell("SELECT COUNT(*) FROM trainer_to_class WHERE classid = {$id}");
    
    if ($num) {
        continue; // Skip if trainers are already assigned
    }

    // Request trainers for this class (Assumed external function)
    // echo "sending trainer request for class ID: {$id}<br>";
    requestTrainers($id);
}

// --- 4. Historical / Conditional Blocks (Original logic preserved but wrapped in if(0)) ---

if (1 == 0) {
    // Original logic: Specific date check for responder updates (May 20, two years ago)
    $lastnight = strtotime("-2 years", strtotime("May 20th")); 
    $lastnight_date = date("Y-m-d", $lastnight);
    
    $sql = "SELECT classid, responderid 
            FROM responder_to_class, class, company_esi 
            WHERE DATE(startdate) = '{$lastnight_date}' 
            AND classid = class.id 
            AND class.companyid = company_esi.id 
            AND iscorp = 0";
    // echo $sql . "<br>";
    $expiring = db_query_rows($sql);

    foreach ($expiring as $e) {
        $responder_id = $e['responderid'];
        // echo $e['classid'] . ", " . $responder_id . " : ";
        
        // Assumed external functions
        $arow = getResponderRow($responder_id);
        // echo $arow['pmsid'] . ", ";
        $res = updateResponder($arow);
        // echo $res . "<br>";
    }
}

// Original logic for increasing instructor stage is also wrapped in a commented block.
// if (1 == 0) {
//     $lastnight = mktime( 0,0,0,5, 20, date("Y" )-2 ); // specific date
//     $sql = ( "select classid, trainer_to_class.trainerid from trainer_to_class,class where startdate like '".date("Y-m-d", $lastnight )."%' and classid = class.id" );
//     $increasing = db_query_rows( $sql );
    
//     foreach( $increasing as $e )
//     {
//         $trainerid = $e['trainerid'];
//         $stage = db_query_first_cell( "select instructorstage from user where id = '{$trainerid}'" );
//         if( $stage != "Completed" )
//         {
//             $stage++;
//             db_query( "update user set instructorstage = '{$stage}' where id = '{$trainerid}'" );
//         }
//     }
// }

if (1 == 0) {
    // Original logic: Check classes from two years ago yesterday
    $lastnight = strtotime("-2 years -1 day"); 
    $lastnight_date = date("Y-m-d", $lastnight);

    $sql = "SELECT * FROM class, company_esi 
            WHERE DATE(startdate) = '{$lastnight_date}' 
            AND class.companyid = company_esi.id 
            AND iscorp = 0";
    // echo $sql . "<br>";
    $expiring = db_query_rows($sql);

    foreach ($expiring as $e) {
        // echo $e['classid'] . "<br>";
    }
}

// Original logic for sending attendee emails for classes 3 days out is wrapped in a commented block.
// $threedays = mktime( 0,0,0,date( "m" ), date( "d" )+3, date("Y" ) );
// $sql = ( "select id from class where startdate like '".date("Y-m-d", $threedays )."%'  and accepted = 1 and canceldate is null" );
// $expiring = db_query_rows( $sql );
// foreach( $expiring as $e )
// {
//  $id = $e['id'];
//  sendToAttendees( $id, true );
// }

// Original logic for requesting trainers for classes 15 days out is wrapped in a commented block.
// $fifteendays = mktime( 0,0,0,date( "m" ), date( "d" )+15, date("Y" ) );
// $sql = ( "select id from class where startdate like '".date("Y-m-d", $fifteendays )."%'  and accepted = 1 and canceldate is null" );
// $upcoming = db_query_array( $sql, "id", "id" );
// foreach( $upcoming as $id )
// {
//  requestTrainers( $id );
// }
?>