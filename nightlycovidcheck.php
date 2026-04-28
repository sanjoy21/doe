<?php 
$nologinrequired = 1;
include "mysql.php";

// Assume $testing and URL_WITHOUT_SUBDOMAIN are defined externally.
$testing = $testing ?? false;
$dt = date( "Y-m-d" );

if( $testing )
    $dt = "2020-06-12";

// Retrieve distinct user IDs (trainers) and concatenate the class IDs they are teaching today
$todays = db_query_rows( "SELECT group_concat( classid ) AS classids, userid 
                          FROM trainer_to_class ttc, class c, user u 
                          WHERE u.id = ttc.trainerid 
                            AND c.id = classid 
                            AND startdate LIKE '{$dt}%' 
                          GROUP BY userid" );

$body = "";
$lastcompany = "";

foreach( $todays as $t )
{
    // PHP 8.2 Fix: Quote array keys
    $trainer_userid = $t['userid'] ?? '';
    $class_ids_url = $t['classids'] ?? '';

    if( $testing ) {
        // Use a fixed email for testing
        $recipientrow = db_query_first( "SELECT * FROM user WHERE userid = 'sarahg@emergencyskills.com'" );
    } else {
        // Use the trainer's actual userid from the query
        // PHP 8.2 Fix: Quote array keys inside string concatenation
        $recipientrow = db_query_first( "SELECT * FROM user WHERE userid = '" . $trainer_userid . "'" );
    }

    $url = "https://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/covidchecklist.php?classid=" . $class_ids_url;
    $message = "Click here for ESI work health check. " . $url . "  ";

    // PHP 8.2 Fix: Quote array keys inside string concatenation for echo
    echo( "Click here for ESI work health check. https://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/covidchecklist.php?classid=" . $class_ids_url . "  " );

    // PHP 8.2 Fix: Quote array keys inside string concatenation for sendText
    sendText( "Covid Check", $message, $recipientrow );
}
?>