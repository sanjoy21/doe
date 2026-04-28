<?php 
$nologinrequired = 1;
include "mysql.php";

// Assuming $link is the global mysqli connection object established by mysql.php
global $link;

// Default date to today
$dt = date( "Y-m-d" );

// If $testing is defined and true (defaulting to false if not defined for safety)
if( $testing ?? false )
    $dt = "2020-06-12";

// Prepare today's date for use in the second query (safety measure)
$today_ymd_safe = $link->real_escape_string(date("Y-m-d"));

// Query: Find all trainers scheduled for the given date and group their class IDs
$todays = db_query_rows( "SELECT group_concat( classid ) AS classids, u.userid 
                          FROM trainer_to_class ttc, class c, user u 
                          WHERE u.id = ttc.trainerid 
                            AND c.id = classid 
                            AND startdate LIKE '{$dt}%' 
                          GROUP BY u.userid" );

$body = "";

foreach( $todays as $t )
{
    // PHP 8.2 Fix: Quote array key
    $trainer_id = $t['userid'] ?? null;
    if (!$trainer_id) continue;

    // Get the trainer's user row for name lookup
    // PHP 8.2 Fix: Quote array key, use safe concatenation
    $recipientrow = db_query_first( "SELECT * FROM user WHERE userid = '" . $link->real_escape_string($trainer_id) . "'" );

    // Check if COVID questions were answered today
    // PHP 8.2 Fix: Quote array key, use safe concatenation
    $answered = db_query_first_cell( "SELECT count(*) FROM covidquestions WHERE userid = '" . $link->real_escape_string($trainer_id) . "' AND dateadded LIKE '{$today_ymd_safe}%'" );

    if( !$answered )
    {
        // PHP 8.2 Fix: Quote array keys for first_name and last_name
        $first_name = $recipientrow['first_name'] ?? 'Unknown';
        $last_name = $recipientrow['last_name'] ?? 'Trainer';

        $body .= "{$first_name} {$last_name} did not answer the questions in time.\n";
    }
}

if( $body )
{
    // Send email report if there are any unanswered trainers
    mail( "sarahg@emergencyskills.com", "Covid questions not answered in time", $body, "From: info@emergencyskills.com" );
}
?>