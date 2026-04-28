<?php
$nologinrequired = 1;
include "mysql.php";

// Ensure $link (the mysqli connection object) is available for real_escape_string
global $link;

echo("loading?" );

// Safely open the CSV file
$handle = fopen("oow.csv", "r");

if ($handle === false) {
    echo "Error: Could not open oow.csv";
    exit;
}

$nomatch = array();
$match = array();

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    
    // Safely extract the serial number from the first column
    $serial_raw = $data[0] ?? '';
    $serial = trim($serial_raw);

    // Skip if the serial is empty
    if( !$serial ) {
        continue;
    }

    // Escape the serial for safe use in the SQL query
    $serial_safe = $link->real_escape_string($serial);
    $serial_safe_prefixed = $link->real_escape_string('0' . $serial);

    // Query to find AED, checking both serial and '0'+serial
    // NOTE: The original query had a logical error: 'serial = '0$serial' and deleted = 0'
    // The correct logic is to check if 'serial' matches one OR the other, AND it's not deleted.
    $sql_select = "
        SELECT aedid FROM aed_esi 
        WHERE (serial = '$serial_safe' OR serial = '$serial_safe_prefixed') 
          AND deleted = 0
    ";
    
    $res = db_query_first_cell( $sql_select );
    
    if( $res )
    {
        $aedid = (int)$res;
        
        // Update the outofwarranty status
        $sql_update = "UPDATE aed_esi SET outofwarranty = 1 WHERE aedid = " . $aedid;
        
        db_query( $sql_update );
        $match[$serial] = $serial;
    }
    else
    {
        $nomatch[$serial] = $serial;
    }
}

fclose($handle);

// Output results
echo( "<br>No Match Count: " . count( $nomatch ) );
echo( ", Match Count: " . count( $match ) );

// List serials that did not match
echo( "<br>--- No Match Serials ---<br>" );
foreach( $nomatch as $n ) {
    echo( htmlspecialchars($n) . "<br>" );
}
?>