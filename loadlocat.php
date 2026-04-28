<?php 
require_once "mysql.php";

$handle = @fopen("/tmp/dcas.csv", "r"); // Use @ to suppress file-not-found warnings initially
$nomatch = array();
$match = array();

if ($handle === FALSE) {
    echo "Error: Could not open file /tmp/dcas.csv for reading.";
    exit;
}

// Get the database connection link to use with mysqli_real_escape_string
// Assuming your "mysql.php" sets up a connection available via $GLOBALS['link'] or $link
$db_link = $GLOBALS['link'] ?? $link; 

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    
    // PHP 8.2 Fix: Quote array keys and use null coalescing for safety
    $schoolcode = trim($data[0] ?? ''); 
    
    if( !$schoolcode ) {
        continue;
    }

    // --- Data Sanitization ---
    // Extract and escape data from CSV columns 1 through 8 (indices 1 to 8)
    $companyname_safe = mysqli_real_escape_string($db_link, $data[1] ?? '');
    $address_safe = mysqli_real_escape_string($db_link, $data[2] ?? '');
    $city_safe = mysqli_real_escape_string($db_link, $data[3] ?? '');
    $state_safe = mysqli_real_escape_string($db_link, $data[4] ?? '');
    $zip_safe = mysqli_real_escape_string($db_link, $data[5] ?? '');
    $contactphone_safe = mysqli_real_escape_string($db_link, $data[6] ?? '');
    $contactname_safe = mysqli_real_escape_string($db_link, $data[7] ?? '');
    $contactemail_safe = mysqli_real_escape_string($db_link, $data[8] ?? '');
    
    // Additional fields for insertion (indices 9 and 11)
    $region_safe = mysqli_real_escape_string($db_link, $data[9] ?? '');
    $borough_safe = mysqli_real_escape_string($db_link, $data[11] ?? '');

    // --- 1. Attempt to find existing non-corporate (iscorp=1 in original query is non-standard, assuming 1 means corporate/DCAS) company by schoolcode ---
    $safe_schoolcode = mysqli_real_escape_string($db_link, $schoolcode);
    
    $res = db_query_first_cell( "SELECT id FROM company_esi WHERE schoolcode = '{$safe_schoolcode}' AND iscorp = 1 LIMIT 1" );
    
    if( $res )
    {
        // --- MATCH: Update existing record ---
        $safe_id = (int)$res;
        
        $sql = "UPDATE company_esi SET 
                    companyname = '{$companyname_safe}', 
                    address = '{$address_safe}', 
                    city = '{$city_safe}', 
                    state = '{$state_safe}', 
                    zip = '{$zip_safe}', 
                    contactphone = '{$contactphone_safe}', 
                    contactname = '{$contactname_safe}', 
                    contactemail = '{$contactemail_safe}' 
                WHERE id = {$safe_id}";
                
        echo( "UPDATE: ID {$safe_id} - " . htmlspecialchars($schoolcode) . "<br>");
        db_query( $sql );
        
        $match[$schoolcode] = $schoolcode;
    }
    else
    {
        // --- NO MATCH: Insert new record ---
        $sql = "INSERT INTO company_esi ( 
                    iscorp, schoolcode, region, borough, 
                    companyname, address, city, state, zip, contactphone, contactname, contactemail, esinotes 
                ) VALUES ( 
                    1, '{$safe_schoolcode}', '{$region_safe}', '{$borough_safe}', 
                    '{$companyname_safe}', '{$address_safe}', '{$city_safe}', '{$state_safe}', '{$zip_safe}', '{$contactphone_safe}', '{$contactname_safe}', '{$contactemail_safe}', 
                    'added on " . date('m/d/Y') . " via spreadsheet' 
                )";
        
        echo( "INSERT: " . htmlspecialchars($schoolcode) . "<br>" );
        db_query( $sql );
        
        $nomatch[$schoolcode] = $schoolcode;
    }
}

fclose($handle);

// echo( "<br>Summary:<br>" );
// echo( "No matches (New Inserts): " . count( $nomatch ) . "<br>" );
// echo( "Matches (Updates): " . count( $match ) . "<br>" );
// if (count($nomatch) > 0) {
//     echo( "<br>New School Codes Added:<br>" );
//     foreach( $nomatch as $n ) {
//         echo( htmlspecialchars($n) . "<br>" );
//     }
// }
?>