<?php
$nologinrequired = true;
include "mysql.php";

// Ensure $link (the mysqli connection object) is available for real_escape_string
global $link;

/**
 * Converts an Excel date serial number to a Unix timestamp.
 * (Note: This function is defined in the original code but not used in the main loop.)
 * @param float $excel_v The Excel date serial number.
 * @return int The Unix timestamp.
 */
function excel2unix($excel_v) {
    // The Unix epoch is 1970-01-01. Excel's epoch is 1899-12-30.
    // Difference is 25569 days. 86400 seconds in a day.
    $unix_v = (($excel_v - 25569) * 86400); 
    // Format to a string, then use strtotime to get the final Unix timestamp 
    // (This step handles time zone issues inherent in Excel's system).
    $text_v = gmdate("m/d/Y H:i:s", $unix_v);
    $unix_v = strtotime($text_v);
    return $unix_v;
}

// Safely open the CSV file
$handle = fopen("/tmp/esi.csv", "r");

if ($handle === false) {
    echo "Error: Could not open /tmp/esi.csv";
    exit;
}

$rowcnt = 0;

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    $rowcnt++;
    
    // Safely extract data from the CSV row
    $received_raw = $data[0] ?? '';
    $code_raw = $data[1] ?? '';

    // Skip the header row
    if( $received_raw == "Received" )
    {
        continue;
    }
    
    // Process code to get the standardized school code (XX-X-XXX format)
    $schoolcode = '';
    if (strlen($code_raw) >= 6) {
        $schoolcode = substr( $code_raw, 0, 2 ) . "-" . substr( $code_raw, 2, 1 ) . "-" . substr( $code_raw, 3 );
    }

    // Escape school code for database query
    $schoolcode_safe = $link->real_escape_string( $schoolcode );
    
    // Find matching company in the database
    $sql = "SELECT * FROM company_esi WHERE schoolcode = '$schoolcode_safe'";
    $company = db_query_first( $sql );
    
    if( !$company ) 
    {
        echo( "no company matching " . htmlspecialchars($code_raw) . "<br>" );
    }
    else
    {
        echo( "matched! Company ID: " . htmlspecialchars($company["id"] ?? 'N/A') . "<br>" );
    }
}

fclose($handle);
?>