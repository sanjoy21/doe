<?php 
include "mysql.php";

// Fetch drills that are not yet linked in the drill_to_companyid table
$drills = db_query_rows( "select * from drill where drillid not in ( select distinct( drillid ) from drill_to_companyid );" );

foreach( $drills as $d )
{
    // Safely access quoted array keys
    $drillid_safe = $d["drillid"] ?? 0;
    $companyid_safe = $d["companyid"] ?? 0;
    
    // Construct the INSERT query
    $insert_sql = "insert into drill_to_companyid values ( '" . (int)$drillid_safe . "', " . (int)$companyid_safe . ", 1 )";
    
    // Execute the insertion
    db_query( $insert_sql );
    
    // Echo the executed query for confirmation/debugging
    echo( $insert_sql . "<br>" );
}

?>