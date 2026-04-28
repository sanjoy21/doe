<?php 
include "mysql.php";

echo( "<table border=1 cellpadding=2 cellspacing=0>" );

// Fetch companies added after 2015-07-01 that are non-corporate and not deleted
$res = db_query_rows( "select * from company_esi where date > '2015-07-01' and iscorp = 0 and deleted = 0 " );

foreach( $res as $r ) 
{
    // Safely access quoted array keys for company data
    $company_id_safe = $r["id"] ?? 0;
    $company_name_safe = $r["companyname"] ?? 'N/A';
    $schoolcode_safe = $r["schoolcode"] ?? 'N/A';
    
    // Start company row
    echo( "<tr><td><a href='viewcompany.php?id=" . (int)$company_id_safe . "'>" . htmlspecialchars($company_name_safe) . "</a></td><td>" . htmlspecialchars($schoolcode_safe) . "</td><td>" );
    
    // Fetch all AEDs for the current company
    $aeds = db_query_rows( "select * from aed_esi where clientid = " . (int)$company_id_safe );
    
    foreach( $aeds as $a )
    { 
        // Safely access quoted array keys for AED data
        $aed_id_safe = $a["id"] ?? 0;
        $serial_safe = $a["serial"] ?? 'N/A';
        
        // List AED serial number with a link
        echo( "<a href='viewaed.php?aedid=" . (int)$aed_id_safe . "'>" . htmlspecialchars($serial_safe) . "</a><br>" );
    }
    
    // Close company row
    echo( "</td></tr>" );
}

echo( "</table>" );
?>