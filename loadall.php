<?php
$nologinrequired = true;
include "mysql.php";

// Ensure $link (the mysqli connection object) is available for real_escape_string
global $link;

// Safely open the CSV file
$handle = fopen("/tmp/research.csv", "r");

if ($handle === false) {
    echo "Error: Could not open /tmp/research.csv";
    exit;
}

$rowcnt = 0;
$i = 0;

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    $i++;

    // Safely extract DBN from the first column and skip header row
    $dbn_raw = $data[0] ?? '';
    if( $dbn_raw == "DBN" ) {
        continue;
    }

    // Process DBN to get the standardized school code (XX-X-XXX format)
    $schoolcode = '';
    if (strlen($dbn_raw) >= 6) {
        $schoolcode = substr( $dbn_raw, 0, 2 ) . "-" . substr( $dbn_raw, 2, 1 ) . "-" . substr( $dbn_raw, 3, 3 );
    } else {
        // Skip row if DBN is too short to parse
        continue;
    }
    
    // Check if the company/school already exists
    $schoolcode_safe = $link->real_escape_string($schoolcode);
    $cid = db_query_first_cell( "select id from company_esi where schoolcode = '" . $schoolcode_safe . "'" );

    if( !$cid )
    {
        // --- Data Extraction and Cleanup ---
        
        // CSV Index Map (Approximate based on original code usage):
        // 0: DBN, 2: Building Code, 3: Principal Name, 4: Location Code, 5: Company Name,
        // 6: Address, 7: City/Borough, 8: State, 9: Zip, 10: Phone, 11: Email
        
        $borough_raw = $data[7] ?? '';
        $borough = $borough_raw;
        
        // Clean up and standardize Borough name
        if( strtoupper($borough_raw) == "BRONX")
            $borough = "Bronx";
        else if( strtoupper($borough_raw) == "MANHATTAN" )
            $borough = "Manhattan";
        else if( strtoupper($borough_raw) == "BROOKLYN" )
            $borough = "Brooklyn";
        else if( strtoupper($borough_raw) == "QUEENS" )
            $borough = "Queens";
        else if( strtoupper($borough_raw) == "R" )
            $borough = "Staten Island";
        
        // Safely extract and escape all data fields for insertion
        $locationcode_raw = $data[4] ?? '';
        $companyname_raw = $data[5] ?? '';
        $address_raw = $data[6] ?? '';
        $city_raw = $data[7] ?? '';
        $state_raw = $data[8] ?? '';
        $zip_raw = $data[9] ?? '';
        $principalname_raw = $data[3] ?? '';
        $principalemail_raw = $data[11] ?? '';
        $contactphone_raw = $data[10] ?? '';
        $buildingcode_raw = $data[2] ?? '';
        
        // Escaped values
        $locationcode_safe = $link->real_escape_string( $locationcode_raw );
        $companyname_safe = $link->real_escape_string( $companyname_raw );
        $address_safe = $link->real_escape_string( $address_raw );
        $city_safe = $link->real_escape_string( $city_raw );
        $state_safe = $link->real_escape_string( $state_raw );
        $zip_safe = $link->real_escape_string( $zip_raw );
        $borough_safe = $link->real_escape_string( $borough );
        $principalname_safe = $link->real_escape_string( $principalname_raw );
        $principalemail_safe = $link->real_escape_string( $principalemail_raw );
        $contactphone_safe = $link->real_escape_string( $contactphone_raw );
        $buildingcode_safe = $link->real_escape_string( $buildingcode_raw );
        
        // --- 1. INSERT into company_esi ---
        $sql_company = "INSERT INTO company_esi (
            iscorp, schoolcode, locationcode, companyname, address, city, state, zip, borough, 
            principalname, principalemail, contactphone 
        ) VALUES ( 
            '0', '$schoolcode_safe', '$locationcode_safe', '$companyname_safe', '$address_safe', 
            '$city_safe', '$state_safe', '$zip_safe', '$borough_safe', 
            '$principalname_safe', '$principalemail_safe', '$contactphone_safe' 
        )";
        $cid = db_query_insert_id( $sql_company );
        
        // --- 2. INSERT into buildings ---
        $sql_building = "INSERT INTO buildings ( 
            buildingcode, buildingname, address, city, state, zip 
        ) VALUES ( 
            '$buildingcode_safe', '$companyname_safe', '$address_safe', '$city_safe', '$state_safe', '$zip_safe' 
        )";
        $b = db_query_insert_id( $sql_building );
        
        // --- 3. INSERT into location_to_building ---
        // NOTE: The original code swapped $data[2] (Building Code) and $data[4] (Location Code) here. 
        // I'm preserving the original variable mapping ($buildingcode_raw / $locationcode_raw) for correctness.
        // Original: values ( '$data[2]', '$data[4]' ) -> ( Building Code, Location Code )
        // SQL: (locationcode, buildingcode) -> values( Location Code, Building Code )
        // Assuming the table column order is correct, the original was: (Building Code, Location Code)
        
        $sql_loc_build = "INSERT INTO location_to_building (locationcode, buildingcode) 
                          VALUES ( '$locationcode_safe', '$buildingcode_safe' )";
        db_query( $sql_loc_build );
        
        echo( "not found and inserted: " . htmlspecialchars($schoolcode) . "<br>" );
    }
    else
    {
        // Record already exists
        // echo( " found: " . htmlspecialchars($schoolcode) . "<br>" );
    }
}

fclose($handle);
?>