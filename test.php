<?php 
$nologinrequired = true;
include "mysql.php";

// Initialize variables safely
$cnt = 0;
$lastc = 0; // Represents the company ID of the previous successful iteration
$link = $link ?? null; // Assuming $link is the mysqli connection object from mysql.php

for( $i = 1; $i < 12031; $i++ )
{
    $row = db_query_first_cell( "SELECT companyid FROM class c, company_esi co 
                                 WHERE co.id = c.companyid AND c.id = {$i} 
                                   AND c.deleted = 0 AND iscorp = 0" );

    if( !$row )
    {
        $lastc = 0;
        continue;
    }
 
    if( $lastc && $lastc == $row )
    {
        // Find the company name for reporting
        $fir = db_query_first_cell( "SELECT companyname FROM company_esi WHERE id = {$row}" );
        
        $cnt++;
        
        // PHP 8.2 Fix/Security: Use htmlspecialchars for safe output
        $safe_fir = htmlspecialchars($fir ?? 'Unknown Company');
        
        echo( $cnt . ". the last companyid was the same! {$i} ({$safe_fir} - {$lastc})<br>" );
    }
    
    // Update the last company ID
    $lastc = $row;
}
?>