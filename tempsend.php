<?php
$nologinrequired = true;
include "mysql.php";
require_once "services.php";

// Safely handle external constant, defaulting to a placeholder
$url_subdomain = defined('URL_WITHOUT_SUBDOMAIN') ? URL_WITHOUT_SUBDOMAIN : 'emergencyskills.com';

// --- 1. Query Classes Needing Trainers ---
$sql = "SELECT 
            class.id 
        FROM 
            class, 
            company_esi 
        WHERE 
            ( startdate LIKE '2022-06-09%' ) 
            AND accepted = 1 
            AND canceldate IS NULL 
            AND code NOT IN ( 'MHFA', 'AEDI', 'Inspections', 'TCF Meeting', 'Esinew', 'Misc', 'Trade', 'Call', 'call', 'Office', 'party', 'misc' ) 
            AND iscorp <> 3 
            AND companyid = company_esi.id 
            AND isnational = 0 
            AND companyname NOT LIKE 'Sample%' 
            AND companyname NOT LIKE 'Open Registration' 
            AND numtrainers > 0";

echo( htmlspecialchars($sql) . "<br>" );

$expiring = db_query_rows( $sql );
$newemails = array();

// --- 2. Check for Missing Trainers and Find Candidates ---
foreach( $expiring as $e )
{
    $id = $e['id'] ?? null;
    if (!$id) continue;

    echo( "would send to $id<br>" );

    $num = db_query_first_cell("SELECT count(*) FROM trainer_to_class WHERE classid = " . (int)$id );
    
    if( $num ) continue;

    $tmp = requestTrainers( $id, false, false );

    foreach( $tmp as $tid )
    {
        $newemails[$tid] = $tid;
    }

    if( count($tmp) ) {
        db_query( "UPDATE class SET lasttrainerreqdate = NOW() WHERE id = " . (int)$id );
    }
}

// --- 3. Send Alert Emails to Unique Trainers ---
foreach( $newemails as $trainerid )
{
    $subject = "Alert! Instructors needed for upcoming classes!";
    $obfuscated_id = (int)$trainerid * 1234;
    

    $body = " Instructors needed!
        Click here to view all available instructor opportunities:

https://".SUB_DOE."." . $url_subdomain . "/requesttotrain.php?trainerid=" . $obfuscated_id . "\n\n";

    echo( "would send to " . htmlspecialchars($trainerid) . " <br>" );

    sendMail( 
        getUserEmail( $trainerid ), 
        $subject, 
        $body, 
        "barbara@emergencyskills.com", 
        "Scheduling Alert" 
    );
    
    // sendMail( "rachelc@gmail.com", "$subject", "$body", "barbara@emergencyskills.com", "Scheduling Alert" );
}
?>