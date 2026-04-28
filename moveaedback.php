<?php 
include "mysql.php";

// Ensure $link (the mysqli connection object) and $session_userid are accessible
global $link, $session_userid;

// Default session_userid if not set externally
$session_userid = $session_userid ?? 0; 

if (($handle = fopen("/tmp/tomove.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        $serial_raw = $data[0] ?? '';
        
        // Skip if the serial number is empty
        if( empty($serial_raw) ) continue;
        
        // Escape the serial number for safe use in the SQL query (PHP 8.2/Security fix)
        $serial_safe = $link->real_escape_string( $serial_raw );
        
        // Lookup the AED record
        $aedrow = db_query_first( "SELECT * FROM aed_esi WHERE serial = '$serial_safe'" );
        
        $aedid = $aedrow['aedid'] ?? null;
        $current_company_id = $aedrow['clientid'] ?? null;
        $serial_echo = htmlspecialchars($aedrow['serial'] ?? $serial_raw);

        if( !$aedid ) {
            echo( "no match for " . htmlspecialchars($serial_raw) . "<br>" );
            continue;
        }

        $target_company_id = 12424; // The new school/client ID
        
        // Check if the AED needs to be moved
        if( $current_company_id != $target_company_id )
        {
            $newschoolid = $target_company_id;
            $schoolid = $current_company_id;
            
            // Log the move in oldaedschools
            // Note: $aedid and $schoolid are already safe (integers)
            db_query( "INSERT INTO oldaedschools ( aedid, clientid, movedate, movedby ) 
                       VALUES ( '$aedid', '$schoolid', NOW(), '$session_userid' )" );
            
            // Update the AED record to the new client ID
            db_query( "UPDATE aed_esi SET clientid = '$newschoolid' WHERE aedid = '$aedid'" );
            
            echo ("moving AED ID " . htmlspecialchars($aedid) . " (Serial: " . $serial_echo . ")<br>" );
        }
        else
        {
            echo( "it was already there (" . $serial_echo . ")<br>" );
        }
    }
    fclose($handle);
}
?>