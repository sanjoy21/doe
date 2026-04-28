<?php
include "mysql.php";

// Ensure $link (the mysqli connection object) is available for real_escape_string
global $link;

// Safely open the input and output files
$handle = fopen("/tmp/esi.csv", "r");
$h = fopen( "/tmp/export.csv", "w+" );

if ($handle === false || $h === false) {
    echo "Error opening one or both CSV files.";
    // Exit gracefully if files can't be opened
    if ($handle !== false) fclose($handle);
    if ($h !== false) fclose($h);
    exit;
}

$rowcnt= 0;

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    // Safely access the serial number from the first column
    $serial_safe = $data[0] ?? ''; 

    // Skip if the serial is empty or null
    if (empty($serial_safe)) continue; 

    // Replace deprecated mysqli_real_escape_string with $link->real_escape_string()
    $escaped_serial = $link->real_escape_string( $serial_safe );
    
    $res = db_query_first( "SELECT * FROM aed_esi WHERE serial = '$escaped_serial' AND deleted = 0" );
    
    // Safely access quoted array keys
    $aedid_safe = $res["aedid"] ?? null; 

    if( $aedid_safe )
    {
        $clientid_safe = $res["clientid"] ?? null;
        
        echo( "found a match for " . htmlspecialchars($serial_safe) . " <br>" );
        
        $crow = getCompanyRow( $clientid_safe );
        
        // Safely access company row keys, defaulting to empty string
        $companyname_safe = $crow["companyname"] ?? '';
        $address_safe = $crow["address"] ?? '';
        $city_safe = $crow["city"] ?? '';
        $state_safe = $crow["state"] ?? '';
        $zip_safe = $crow["zip"] ?? '';
        
        // Array structure: Serial, Company Name, Address, "", "", City, State, Zip
        $arr = array( $serial_safe, $companyname_safe, $address_safe, "", "", $city_safe, $state_safe, $zip_safe );
        fputcsv( $h, $arr ) ;
    }
    else
    {
        echo( "didn't find a match for " . htmlspecialchars($serial_safe) . "<br> " );
        $arr = array( $serial_safe, "NOT ESI" );
        fputcsv( $h, $arr ) ;
    }
}

fclose( $handle );
fclose( $h );

?>