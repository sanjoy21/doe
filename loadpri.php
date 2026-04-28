<?php 
$nologinrequired = true;
include "mysql.php";

// Ensure $link (the mysqli connection object) is available for real_escape_string
global $link;

echo( "<table>" );

// Safely open the input file
$handle = fopen("/tmp/principals.csv", "r");

if ($handle === false) {
    echo "<tr><td colspan='3'>Error: Could not open /tmp/principals.csv</td></tr>";
    echo "</table>";
    exit;
}

$rowcnt= 0;
$stob = array();

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    
    // Safely extract data from the CSV row, defaulting to empty string
    $raw_code = $data[0] ?? '';
    $pname_first = $data[1] ?? '';
    $pname_last = $data[2] ?? '';
    $pemail_raw = $data[3] ?? '';
    
    // Process school code: format is expected to be XX-X-XXX
    $schoolcode = '';
    if (strlen($raw_code) >= 6) {
        $schoolcode = substr( $raw_code, 0, 2 ) . "-" . substr( $raw_code, 2, 1 ) . "-" . substr( $raw_code, 3, 3 );
    }

    echo( "<tr><td>" . htmlspecialchars($schoolcode) . "</td><td>" );
    
    // Escape school code for query
    $schoolcode_safe = $link->real_escape_string($schoolcode);
    
    // Find matching school in the database
    $res = db_query_first( "select id, companyname from company_esi where schoolcode = '$schoolcode_safe' and deleted = 0 and iscorp = 0" );
    
    // Safely access quoted array keys
    $company_id_safe = $res["id"] ?? null;
    $company_name_safe = $res["companyname"] ?? null;

    if( $company_id_safe ) 
    {
        echo( "matched: " . htmlspecialchars($company_name_safe) );
        
        $pname = trim($pname_first . " " . $pname_last);
        $pemail = trim($pemail_raw);

        // Escape principal data for update query
        $pname_safe = $link->real_escape_string( $pname );
        $pemail_safe = $link->real_escape_string( $pemail );
        
        $sql = "UPDATE company_esi 
                SET principalname = '" . $pname_safe . "', 
                    principalemail = '" . $pemail_safe . "' 
                WHERE id = " . (int)$company_id_safe;
        
        echo( "</td><td>" . htmlspecialchars($sql) ); // Output the executed SQL query
        db_query( $sql );
    }
    else
    {
        echo( "<font color='red'>no match</font>" );
    }

    echo( "</td></tr>" );
}

fclose($handle);
echo( "</table>" );
?>