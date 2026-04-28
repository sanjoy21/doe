<?php
$nologinrequired = true;
include "mysql.php";

// Ensure $link (the mysqli connection object) is available for real_escape_string
global $link;

// Safely open the CSV file
$handle = fopen("/tmp/parks.csv", "r");

if ($handle === false) {
    echo "Error: Could not open /tmp/parks.csv";
    exit;
}

$rowcnt = 0;
$i = 0;

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    $i++;

    // Skip the header row
    if( ($data[0] ?? '') == "City" )
    {
        continue;
    }
    
    // Safely extract and escape the schoolcode (from index 3 - "Company Field")
    $schoolcode_raw = $data[3] ?? '';
    $schoolcode_safe = $link->real_escape_string( $schoolcode_raw );

    // Check if the company already exists
    $cid = db_query_first_cell( "select id from company_esi where schoolcode = '" . $schoolcode_safe . "'" );

    if( !$cid )
    {
        // Data extraction and escaping for INSERT query
        $borough_raw = $data[0] ?? ''; // City/Borough from index 0
        $companyname_raw = $data[3] ?? '';
        $address_raw = $data[5] ?? '';
        $city_raw = $data[6] ?? '';
        $state_raw = $data[7] ?? '';
        $zip_raw = $data[8] ?? '';
        $region_raw = $data[12] ?? '';
        $phone_raw = $data[9] ?? '';
        $email_raw = $data[10] ?? '';

        $borough_safe = $link->real_escape_string( $borough_raw );
        $companyname_safe = $link->real_escape_string( $companyname_raw );
        $address_safe = $link->real_escape_string( $address_raw );
        $city_safe = $link->real_escape_string( $city_raw );
        $state_safe = $link->real_escape_string( $state_raw );
        $zip_safe = $link->real_escape_string( $zip_raw );
        $region_safe = $link->real_escape_string( $region_raw );
        $phone_safe = $link->real_escape_string( $phone_raw );
        $email_safe = $link->real_escape_string( $email_raw );
        
        // Construct the INSERT query
        $sql = "INSERT INTO company_esi (campusid, iscorp, schoolcode, companyname, address, city, state, zip, borough, region, contactphone, contactemail) 
                VALUES (
                    3565, 
                    '0', 
                    '$schoolcode_safe', 
                    '$companyname_safe', 
                    '$address_safe', 
                    '$city_safe', 
                    '$state_safe', 
                    '$zip_safe', 
                    '$borough_safe', 
                    '$region_safe', 
                    '$phone_safe', 
                    '$email_safe'
                )";
        
        db_query( $sql ) ;
        echo( htmlspecialchars($sql) . "<br>" );
    }
    else
    {
        echo( " already in there !!!! found: " . htmlspecialchars($schoolcode_safe) . "<br>" );
        // The original commented-out update query is skipped as it was not active.
    }
}

fclose($handle);
?>