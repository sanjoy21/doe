<?php
include "mysql.php";

// Ensure $link (the mysqli connection object) is available for real_escape_string
global $link;

// Assume necessary external variables are defined or set safe defaults
$allclass_names = $allclass_names ?? [[]]; // Default to an empty array of class names
$class_names = $allclass_names[0] ?? [];

$i = 0;
// Safely open files
$handle = fopen("/tmp/users.csv", "r");
$f = fopen( "/tmp/newusers.csv", "w+" );

if ($handle === false || $f === false) {
    echo "Error opening CSV files.";
    if ($handle !== false) fclose($handle);
    if ($f !== false) fclose($f);
    exit;
}

$arr = array();

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    $i++;

    // Safely extract and sanitize CSV data
    $schoolcode_raw = $data[4] ?? '';
    $lastname_raw = $data[2] ?? '';
    $firstname_raw = $data[3] ?? '';

    // Safely escape and sanitize names for SQL
    $lastname_safe = $link->real_escape_string(str_replace('-', '', $lastname_raw));
    $firstname_safe = $link->real_escape_string(str_replace('-', '', $firstname_raw));
    $schoolcode_clean = str_replace('-', '', $schoolcode_raw);

    // Write header row to new CSV and skip processing
    if( $i == 1 )
    {
        fputcsv( $f, $data );
        continue;
    }
    
    $esirow = null;
    
    if( $schoolcode_raw == "RESPONDER HO" )
    {
        // Special case: Home Office responder (clientid = 2810)
        $sql = "SELECT r.* FROM responders_esi r, company_esi 
                WHERE replace( firstname, '-', '' ) LIKE '" . $firstname_safe . "' 
                  AND replace( lastname, '-', '' ) LIKE '" . $lastname_safe . "' 
                  AND clientid = company_esi.id 
                  AND company_esi.iscorp = 0 
                  AND clientid = 2810";
        $esirow = db_query_first( $sql );
    }
    else
    {
        // Standard case: Match by sanitized school code
        $sql = "SELECT r.* FROM responders_esi r, company_esi 
                WHERE replace( firstname, '-', '' ) LIKE '" . $firstname_safe . "' 
                  AND replace( lastname, '-', '' ) LIKE '" . $lastname_safe . "' 
                  AND clientid = company_esi.id 
                  AND company_esi.iscorp = 0 
                  AND replace( schoolcode, '-', '' ) = '" . $schoolcode_clean . "'";
        $esirow = db_query_first( $sql );
    }
    
    if( $esirow )
    {
        // Safely access responder ID
        $esiid = $esirow["responderid"] ?? 0;
        $recrenewal = "";
        $mostrecent = "";
        $classtype = "";

        // Find most recent confirmed class attendance
        $sql_class = "SELECT id, startdate, code FROM class, responder_to_class 
                      WHERE responderid = " . (int)$esiid . " 
                        AND classid = class.id 
                        AND startdate < NOW() 
                        AND confirmdate > '0000-00-00' 
                        AND accepted = 1 
                        AND deleted = 0 
                      ORDER BY startdate DESC LIMIT 1";
        $classdata_fetched = db_query_first($sql_class);

        if( $classdata_fetched )
        {
            $startdate = $classdata_fetched["startdate"] ?? null;
            $class_code = $classdata_fetched["code"] ?? null;

            if ($startdate) {
                $td = strtotime( $startdate );
                // Calculate 2-year renewal date
                $td2 = mktime( 0, 0, 0, date("m", $td ), date("d", $td ), date("Y", $td ) + 2 ); 
                
                $recrenewal = date( "Y-m-d", $td2 );
                $mostrecent = date( "Y-m-d", $td );
                $classtype = $class_names[$class_code] ?? 'N/A'; // Safely look up class name
            }
        }
        
        // Update data array with new columns
        $data[10] = $esiid;
        $data[20] = $recrenewal;
        $data[21] = $mostrecent;
        $data[22] = $classtype;
    }
    
    // Write the row (original or updated) to the new CSV
    fputcsv( $f, $data );
}

fclose( $handle );
fclose( $f );
?>