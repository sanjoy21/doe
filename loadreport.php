<?php 
include "mysql.php";

// Initialize assumed database link for escaping functions if required
$db_link = $GLOBALS['link'] ?? $link; 

// --- 1. File Handling ---
$file_path = "/tmp/sherryaeds2.csv";
$handle = fopen($file_path, "r");

if ($handle === false) {
    die("Error: Could not open file '{$file_path}' for reading.");
}

$headers = [];
$rowcnt = 0;

// Mapping of column names to array index (assumed structure for clarity based on original comment)
// $header_map = [
//     'NYCHA' => 0,
//     'AED SERIAL NUMBER' => 1,
//     'DFTA ID' => 2,
//     '4 YEARS AFTER INSTALLED' => 3,
//     'PADS EXPIRATION DATE' => 4,
//     'First Name' => 5,
//     'Last Name' => 6,
//     'PROGRAM NAME' => 7,
//     'ADDRESS' => 8,
//     'CITY' => 9,
//     'ZIP CODE' => 10,
//     'Email Address' => 11,
//     'Telephone #' => 12,
//     'Battery in AED' => 13,
//     'AS OF MAY 2018 NEW BATTERY' => 14,
//     'FAST RESPONSE KIT' => 15,
//     'AED LOCATION' => 16,
// ];

// --- 2. CSV Processing Loop ---
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    
    // Skip and map headers on the first iteration
    if( !$headers )
    {
        // Re-calculate headers based on the file's first row, overriding the hardcoded map above
        // This makes the script flexible to changes in the file structure
        $headers = array_flip( $data );
        continue;
    }
    
    // --- Data Extraction and Preparation ---
    
    // Serial number (trim whitespace)
    $serial_raw = trim( $data[$headers["AED SERIAL NUMBER"]] ?? '' );
    $serial = escMe( $serial_raw );

    // Pad expiration date
    $padaexp_raw = trim( $data[$headers["PADS EXPIRATION DATE"]] ?? '' );
    $padaexp = escMe( $padaexp_raw );

    // Battery installed year/date
    $battery_raw = trim( $data[$headers["4 YEARS AFTER INSTALLED"]] ?? '' );
    $battery = escMe( $battery_raw );
    
    // AED Location
    $location_raw = trim( $data[$headers["AED LOCATION"]] ?? '' );
    $location = escMe( $location_raw );
    
    // Program/Company Name
    $company_raw = trim( $data[$headers["PROGRAM NAME"]] ?? '' );
    $company = escMe( $company_raw );
    
    // --- 3. Check for Existing AED Record ---
    
    // Use the safe variable $serial, and ensure escaping is done correctly (reverting to safe escMe/real_escape_string)
    $serial_safe_for_query = mysqli_real_escape_string($db_link, $serial_raw);
    
    $sql_check = "SELECT aedid, clientid FROM aed_esi WHERE serial = '{$serial_safe_for_query}' AND deleted = 0";
    $res = db_query_first( $sql_check ); // Assumed function that returns an associative array
    
    if( $res['aedid'] )
    {
        echo "found a match for " . htmlspecialchars($serial_raw) . " <br>";
        // Note: Original code commented out $crow = getCompanyRow( $res[clientid] );
    }
    else
    {
        // --- 4. AED Not Found: Check for Company ---
        $company_safe_for_query = mysqli_real_escape_string($db_link, $company_raw);
        $sql_company_id = "SELECT id FROM company_esi WHERE companyname = '{$company_safe_for_query}' AND deleted = 0";
        $companyid = db_query_first_cell( $sql_company_id ); // Assumed function that returns a single value
        
        if( !$companyid )
        {
            echo "no match for company: " . htmlspecialchars($company_raw) . " (" . htmlspecialchars($serial_raw) . ") <Br>";
        }
        else
        {
            // --- 5. Company Found: Insert New AED and Update Contact Info ---
            
            // Date conversions (assuming valid date formats in CSV)
            $padaexp_date = date( "Y-m-d", strtotime( $padaexp_raw ) );
            $batterydate_install = date( "Y-m-d", strtotime( "{$battery_raw} - 4 years" ) );
            
            // Insert AED Record
            $sql_insert = "INSERT INTO aed_esi ( serial, clientid, location, padaexpiration, batterydate ) 
                           VALUES ( '{$serial_safe_for_query}', " . (int)$companyid . ", 
                                    '{$location}', '{$padaexp_date}', '{$batterydate_install}' )";
            db_query( $sql_insert ); // Assumed function
            echo htmlspecialchars($sql_insert) . " <br>";
            
            // Update Company Contact Info
            $email_raw = trim( $data[$headers["Email Address"]] ?? '' );
            $first_raw = trim( $data[$headers["First Name"]] ?? '' );
            $last_raw = trim( $data[$headers["Last Name"]] ?? '' );
            $phone_raw = trim( $data[$headers["Telephone #"]] ?? '' );

            $email = escMe( $email_raw );
            $first = escMe( $first_raw );
            $last = escMe( $last_raw );
            $phone = escMe( $phone_raw );
            
            $full_contact_name = "{$first} {$last}";
            
            $sql_update_company = "UPDATE company_esi SET 
                                     contactname = '{$full_contact_name}', 
                                     contactemail = '{$email}', 
                                     contactphone = '{$phone}' 
                                   WHERE id = " . (int)$companyid;
            
            db_query( $sql_update_company );
            echo htmlspecialchars($sql_update_company) . "<br>";
        }
    }
}

// --- 6. Close File Handle ---
fclose( $handle ); 

?>