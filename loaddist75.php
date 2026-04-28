<?php
$nologinrequired = true;
include "mysql.php";

// Ensure $link (the mysqli connection object) is available for real_escape_string
global $link;

// Safely open the input file
$handle = fopen("/tmp/batterydates.csv", "r");

if ($handle === false) {
    echo "Error opening CSV file at /tmp/batterydates.csv";
    exit;
}

$arr = array();

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    // Skip the header row (assuming "Serial Number" is the header text)
    if( ($data[0] ?? '') == "Serial Number" )
    {
        continue;
    }
    
    // Safely extract and sanitize data
    $serial_raw = $data[0] ?? '';
    $date_raw = $data[2] ?? '';
    
    // Convert date string to Y-m-d format
    $date_formatted = date( "Y-m-d", strtotime( $date_raw ) );
    
    // Escape values for SQL query
    $serial_safe = $link->real_escape_string($serial_raw);
    $date_safe = $link->real_escape_string($date_formatted);
    
    // Find the AED ID based on the serial number
    $res = db_query_first_cell( "select aedid from aed_esi where serial = '$serial_safe' and deleted = 0" );
    
    if( $res )
    {
        // AED found, perform the update
        $aedid = (int)$res;
        $sql = "update aed_esi set batterydate = '$date_safe' where aedid = $aedid";
        
        // db_query( $sql ); // Execute the update
        // The original echo was commented out, we execute the update
        db_query( $sql );
    }
    else
    {
        // Log unmatched serial number
        echo( "didn't find a match for " . htmlspecialchars($date_raw) . ", " . htmlspecialchars($serial_raw) . "<br> " );
    }
}

fclose( $handle );
?>