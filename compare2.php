<?php
include "mysql.php";
// /tmp/currentreport.csv /tmp/report_responders_2008-09-30.csv

$handle = fopen("/tmp/current2.csv", "r");

$ccodes = array();
$currents = array();
$i = 0; // Initialize $i
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    $i++;
    if( $i == 1 )
    continue; // Skip header
    
    // Safely access array elements
    $school_id_key = str_replace( "-", "", $data[1] ?? '' );
    $currents[$school_id_key] = $data[2] ?? '';
    $ccodes[$school_id_key] = $data[1] ?? '';
}

// Reopen the handle for the second file
$handle = fopen("/tmp/sept2.csv", "r");

$olds = array();
$i = 0; // Reset $i for the second file
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    $i++;
    if( $i == 1 )
    continue; // Skip header
    
    // Safely access array elements
    $school_id_key = str_replace( "-", "", $data[0] ?? '' );
    $olds[$school_id_key] = $data[2] ?? '';
}

$cnt = 0;
echo( "<table>" );
echo( "<tr><td></td><td>old</td><td>current</td></tr>" );

foreach( $olds as $oid => $oval )
{
    // Check if the old value is different from the current value AND if the current value exists
    // Use quoted array keys for $currents[$oid] and safe checks
    if( $oval != ($currents[$oid] ?? null) && isset( $currents[$oid] ) ) 
    {
        $cnt++;
        
        // Lookup company ID using the full school code
        $schoolcode_safe = $ccodes[$oid] ?? '';
        $coid = db_query_first_cell( "select id from company_esi where schoolcode = '$schoolcode_safe'" );
        
        // Output the row with the mismatch details
        echo( "<tr><td>$cnt. $oid</td><td>" . $oval . "</td><td>" . ($currents[$oid] ?? 'N/A') . "</td><td> <a href='viewcompany.php?id=" . ($coid ?? '') . "&donthide=true#resps'>view company</a></td></tr>" );
    }
}
echo( "</table>" );
?>