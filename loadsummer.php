<?php 
include "mysql.php";

// Ensure $link (the mysqli connection object) is available for real_escape_string
global $link;

// 1. Reset the 'summer' flag for all companies
db_query( "UPDATE company_esi SET summer = 0" );

// Safely open the CSV file
$handle = fopen("/tmp/summer.csv", "r");

if ($handle === false) {
    echo "Error: Could not open /tmp/summer.csv";
    exit;
}

$nomatch = array();
$match = array();

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    
    // Safely extract the first column's raw data
    $schoolcode_raw = $data[0] ?? ''; 

    // Remove all spaces from the raw data
    $schoolcode_no_spaces = str_replace( " ", "", $schoolcode_raw );
    
    if( !$schoolcode_no_spaces ) {
        continue;
    }

    // Split the string by semicolon to handle multiple codes per row
    $exp = explode( ";", $schoolcode_no_spaces );
    
    foreach( $exp as $e_raw )
    {
        $e = trim( $e_raw ); // The individual school code
        
        if (empty($e)) continue;

        // Prepare the school code for the lookup (remove hyphens)
        $lookup_code = str_replace( "-", "", $e );
        
        // Escape the lookup code for safe use in the SQL query
        $lookup_code_safe = $link->real_escape_string( $lookup_code );
        
        // Query to find matching company (by sanitized schoolcode, must be active and non-corporate)
        $res = db_query_first_cell( "SELECT id FROM company_esi WHERE replace( schoolcode, '-', '' ) = '$lookup_code_safe' AND deleted = 0 AND iscorp = 0" );
        
        if( $res )
        {
            $company_id = (int)$res;
            
            // 2. Update the company to set the summer flag
            $sql = ( "UPDATE company_esi SET summer = 1 WHERE id = " . $company_id );
            
            echo( htmlspecialchars($e) . " - " . htmlspecialchars($sql) . "<br>");
            db_query( $sql );
            
            $match[$e] = $e;
        }
        else
        {
            $nomatch[$e] = $e;
        }
    }
}

fclose($handle);

// 3. Output results
echo( "<br>Unmatched Codes Count: " . count( $nomatch ) );
echo( ", Matched Codes Count: " . count( $match ) );

echo( "<br>--- Unmatched Codes ---<br>" );
foreach( $nomatch as $n ) {
    echo( htmlspecialchars($n) . "<br>" );
}
?>