<?php 
include "mysql.php";

$res = db_query_rows( "select aed_esi.*, schoolcode, companyname from aed_esi, company_esi where clientid = company_esi.id and iscorp = 0 and aedmissing = 1 and serial not in ( select serial from aed_to_drill ) and serial not in ( select serial from aed_to_servicecall ) and aed_esi.deleted = 0 and company_esi.deleted = 0 " );
echo( "<table>" );
foreach( $res as $r )
{
    // PHP 8.2 Improvement: Use htmlspecialchars() and null coalescing (??) for safe HTML output
    $aedid = htmlspecialchars($r['aedid'] ?? '');
    $serial = htmlspecialchars($r['serial'] ?? 'N/A');
    $schoolcode = htmlspecialchars($r['schoolcode'] ?? 'N/A');
    $companyname = htmlspecialchars($r['companyname'] ?? 'N/A');
    
    echo( "<tr><td><a href='editaed.php?aedid=" . $aedid . "'>" . $serial . "</a></td><td>" . $schoolcode . "</td><td>" . $companyname . "</td></tr>" );
}
echo( "</table>" );
?>