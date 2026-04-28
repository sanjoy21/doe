<?php
include "mysql.php";

// Ensure $link (the mysqli connection object) is available for real_escape_string
global $link;

/**
 * Converts a date string (expected format M/D) or 'N/A'/'empty' to a SQL-friendly date string (D-M-01) or NULL.
 * NOTE: The original logic for date manipulation is preserved but may be incorrect for actual date formatting.
 * @param string|null $dt The date string from the CSV.
 * @return string The formatted date string quoted or "NULL".
 */
function getDbData( $dt )
{
    if( !$dt || $dt == "N/A" || empty(trim($dt))) {
        return "NULL";
    }
    else
    {
        // Original logic: splits by '/' and reverses (e.g., 03/25 -> 25-03-01). 
        // This is kept for conversion fidelity, but might be wrong for Y-m-d format.
        $spl = explode( "/", $dt );
        $month = $spl[0] ?? null;
        $day = $spl[1] ?? null;
        
        if ($month && $day) {
            $dt = $day . "-" . $month . "-01";
            return "'" . $dt . "'";
        }
        return "NULL";
    }
}

// Safely open the CSV file
$handle = fopen("/tmp/mta.csv", "r");

if ($handle === false) {
    echo "Error: Could not open /tmp/mta.csv";
    exit;
}

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    // Safely extract data from the CSV row, defaulting to empty string
    $aedserial_raw = $data[0] ?? '';
    $aedtype_raw = $data[1] ?? '';
    $g2005_raw = $data[2] ?? '';
    $aedlocation_raw = $data[3] ?? '';
    $padaexp_raw = $data[4] ?? '';
    $padbexp_raw = $data[5] ?? '';
    $aedpurchased_raw = $data[6] ?? '';
    $sparebatt_raw = $data[7] ?? '';
    $batteryinstalldate_raw = $data[8] ?? '';

    // The following variables were commented out in the original data extraction block,
    // but were used in the SQL query, so they are assumed to be empty or defined elsewhere.
    // They are defaulted here for safety.
    $aedfloor_raw = $data[15] ?? '';
    $pedexp_raw = $data[19] ?? '';
    $medicaldirector_raw = $data[23] ?? '';
    $internalreference_raw = $data[28] ?? '';
    
    // Process flags and escape strings for the query
    $g2005 = (strtoupper($g2005_raw) == "X") ? 1 : 0;
    $aedserial_safe = $link->real_escape_string( $aedserial_raw );
    $aedtype_safe = $link->real_escape_string( $aedtype_raw );
    $aedlocation_safe = $link->real_escape_string( $aedlocation_raw );
    $aedfloor_safe = $link->real_escape_string( $aedfloor_raw );
    $medicaldirector_safe = $link->real_escape_string( $medicaldirector_raw );
    $internalreference_safe = $link->real_escape_string( $internalreference_raw );
    
    $companyid = 13481; // Hardcoded company ID
    
    // Construct the INSERT query
    $sql = "INSERT INTO aed_esi (
        clientid, serial, model, location, floor, purchasedate, 
        padaexpiration, padbexpiration, pediatricpads, sparedate, batterydate, 
        directorname, irn, hasbeenupdated
    ) VALUES ( 
        $companyid, 
        '$aedserial_safe', 
        '$aedtype_safe', 
        '$aedlocation_safe', 
        '$aedfloor_safe', 
        " . getDbData( $aedpurchased_raw ) . ", 
        " . getDbData( $padaexp_raw ) . ", 
        " . getDbData( $padbexp_raw ) . ", 
        " . getDbData( $pedexp_raw ) . ", 
        " . getDbData( $sparebatt_raw ) . ", 
        " . getDbData( $batteryinstalldate_raw ) . ", 
        '$medicaldirector_safe', 
        '$internalreference_safe', 
        '$g2005' 
    )";
    
    echo( htmlspecialchars($sql) . "<br>");
    $serial = db_query_insert_id( $sql ); // Execute the query
}

fclose($handle);
?>