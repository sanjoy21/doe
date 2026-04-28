<?php
include "mysql.php";

// --- Report 1: AEDs Missing Building Code ---
$sql = "SELECT * FROM aed_esi r, company_esi c WHERE c.id = r.clientid AND c.iscorp = 0 AND c.deleted = 0 AND r.deleted = 0 AND ( r.buildingcode IS NULL OR r.buildingcode = '' ) ";
$res = db_query_rows( $sql );

echo( "<table>" );

foreach( $res as $r )
{
    // PHP 8.2 Compliance: Array keys quoted ($r['aedid']) and htmlspecialchars added for security.
    $aedid = htmlspecialchars($r['aedid'] ?? '');
    $serial = htmlspecialchars($r['serial'] ?? '');
    $clientid = $r['clientid'] ?? null;

    echo( "<tr><td><a href='editaed.php?aedid=" . $aedid . "'>" . $serial . "</a></td><td>" . htmlspecialchars(getCompanyName( $clientid )) . "</td></tr>" );
}
echo( "</table>" );

// --- Report 2: Responders Missing Building Code ---
$sql = "SELECT * FROM responders_esi r, company_esi c WHERE c.id = r.clientid AND c.iscorp = 0 AND c.deleted = 0 AND r.deleted = 0 AND ( r.buildingcode IS NULL OR r.buildingcode = '' ) ";
$res = db_query_rows( $sql );

echo( "<table>" );

foreach( $res as $r )
{
    // Safely access clientid
    $clientid_check = $r['clientid'] ?? 0;

    if( $clientid_check == 2810 || $clientid_check == 2878 )
        continue;

    // PHP 8.2 Compliance: Array keys quoted and htmlspecialchars added for security.
    $responderid = htmlspecialchars($r['responderid'] ?? '');
    $firstname = htmlspecialchars($r['firstname'] ?? '');
    $lastname = htmlspecialchars($r['lastname'] ?? '');
    $clientid = $r['clientid'] ?? null;

    echo( "<tr><td><a href='editresponder.php?responderid=" . $responderid . "'>" . $firstname . " " . $lastname . "</a></td><td>" . htmlspecialchars(getCompanyName( $clientid )) . "</td></tr>" );
}
echo( "</table>" );

?>