<?php 
include "mysql.php";

$res = db_query_rows( "select zip, id, schoolcode, companyname from company_esi where deleted = 0 and iscorp=0", "id" );

$already = array();

foreach( $res as $row )
{
    $zip_safe = $row["zip"] ?? null; 
    $r = null; // Initialize trainer result
    
    if( isset($already[$zip_safe]) )
        $r = $already[$zip_safe];
    else
    {
        $r = getTrainersForZip( $zip_safe );
        $already[$zip_safe] = $r;
    }
    
    if( !$r || !$zip_safe )
    {
        echo( "no trainer for: " . ($row["companyname"] ?? 'N/A') . " " . $zip_safe . "<br>" );
    }
}
?>