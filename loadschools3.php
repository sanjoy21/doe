<?php
include "mysql.php";

// Array
// (
//     [0] => RegID
//     [1] => SeqID
//     [2] => LicenseID
//     [3] => ProjectID
//     [4] => BadgeID
//     [5] => App 
//     [6] => FirstName
//     [7] => MiddleName
//     [8] => LastName
//     [9] => Suffix
//     [10] => NickName
//     [11] => Certifications
//     [12] => Title
//     [13] => OrganizationName
//     [14] => Division
//     [15] => Address1
//     [16] => Address2
//     [17] => Address3
//     [18] => City
//     [19] => State
//     [20] => Country
//     [21] => Zip
//     [22] => Phone
//     [23] => fax
//     [24] => AltPhone
//     [25] => Email
//     [26] => ScannedDateTime
//     [27] => LastUpdated
//     [28] => notes
//     [29] => Qualifiers
//     [30] => Interest
//     [31] => Which of the following best describes your title or level of responsibility? (select only one)
//     [32] => My company's type of business at this location is: (select only one)
//     [33] => What is your role relative to the purchase of security technology, products and/or services?
//     [34] => What is your organization's annual budget for security (including outside contractors)?
//     [35] => Which of the following technology, products, and services do you currently purchase?
// )

// Array
// (
//         [0] => 777
//             [1] => 1939
//             [2] => 7D41F9D5-F8EE-413B-B80A-73D051D9E1A7
//             [3] => SE2015
//             [4] => 1939-777
//             [5] => Dr.
//             [6] => Ronald
//             [7] => O
//             [8] => Powers
//             [9] =>
//             [10] =>
//             [11] =>
//             [12] => Physical Security Director
//             [13] => Santander Bank
//             [14] =>
//             [15] => 2 Morrissey Blvd
//             [16] =>
//             [17] => Dorchester
//             [18] => Dorchester
//             [19] => MA
//             [20] => United States of America
//             [21] => 2125
//         [22] => (617)379-4033
//             [23] =>
//             [24] =>
//             [25] => ronald.powers@santander.us
//         [26] => 4/23/2015 18:23
//         [27] => 4/23/2015 18:39
//             [28] => Wants a meeting right away.
//             [29] => ImmConReq
//             [30] => 5
//             [31] => Director
//             [32] => Banking & financial services
//         [33] => Recommend products and/or specify vendors, Influence purchase decisions, Research new products, No role
//         [34] => $2,500,000+ - $5,000,000+
//         [35] => Access Control, Badge & badge printers, Alarms, CCTV Cameras/Systems, Central Station Monitoring
//  )

//Array ( [0] => Builidng No: [1] => Company Name [2] => Display Name [3] => ADDRESS [4] => ZIP [5] => CITY [6] => State [7] => Region )
//Array ( [0] => 10101 [1] => PSS DAVIDSON NEIGHBORHOOD SENIOR CENTER [2] => PSS DAVIDSON NEIGHBORHOOD SENIOR CENTER [3] => 950 Union Avenue [4] => 10459 [5] => Bronx [6] => NY [7] => DCAS-DFTA ) 



// Initialize assumed database link for escaping functions
$db_link = $GLOBALS['link'] ?? null; 

// --- 1. File Handling ---
$file_path = "/tmp/newcompanies.csv";
$handle = fopen($file_path, "r");

if ($handle === false) {
    die("Error: Could not open file '{$file_path}' for reading.");
}

// Initialize placeholder contact variables (they are not in the CSV data structure provided)
$contactname = ""; 
$email = "";
$phone = "";
$title = "";

$first = true;

// --- 2. CSV Processing Loop ---
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    
    // Skip header row
    if( $first )
    {
        $first = false;
        continue;
    }
    
    // Safely retrieve and trim data fields from the CSV
    // Array structure assumed from the comments:
    // [0] => Builidng No: [1] => Company Name [2] => Display Name [3] => ADDRESS [4] => ZIP [5] => CITY [6] => State [7] => Region 
    $name_raw = trim($data[1] ?? '');
    $schoolcode_raw = trim($data[0] ?? '');
    $address_raw = trim($data[3] ?? '');
    $city_raw = trim($data[5] ?? '');
    $state_raw = trim($data[6] ?? '');
    $zip_raw = trim($data[4] ?? '');
    $region_raw = trim($data[7] ?? '');

    // Skip if the necessary company name or school code is missing
    if (empty($name_raw) || empty($schoolcode_raw)) {
        continue;
    }

    // --- 3. Escape Data for SQL Injection Prevention ---
    $name_safe = mysqli_real_escape_string($db_link, $name_raw);
    $schoolcode_safe = mysqli_real_escape_string($db_link, $schoolcode_raw);
    $address_safe = mysqli_real_escape_string($db_link, $address_raw);
    $city_safe = mysqli_real_escape_string($db_link, $city_raw);
    $state_safe = mysqli_real_escape_string($db_link, $state_raw);
    $zip_safe = mysqli_real_escape_string($db_link, $zip_raw);
    $region_safe = mysqli_real_escape_string($db_link, $region_raw);
    
    // Escape placeholder variables
    $contactname_safe = mysqli_real_escape_string($db_link, $contactname);
    $email_safe = mysqli_real_escape_string($db_link, $email);
    $phone_safe = mysqli_real_escape_string($db_link, $phone);
    $title_safe = mysqli_real_escape_string($db_link, $title);
    
    // --- 4. Check for Existing Record (by schoolcode) ---
    $sql_check = "SELECT id FROM company_esi WHERE schoolcode = '{$schoolcode_safe}'";
    $res = db_query_first_cell( $sql_check ); // Assumed function to return single cell value
    
    if( !$res )
    {
        // --- 5. Record Not Found: Insert New Company ---
        // $sql_insert = "INSERT INTO company_esi 
        //                 (iscorp, schoolcode, region, companyname, address, city, state, zip, 
        //                  contactname, contactemail, contactphone, contacttitle, esinotes) 
        //                VALUES 
        //                 (1, '{$schoolcode_safe}', '{$region_safe}', '{$name_safe}', '{$address_safe}', 
        //                  '{$city_safe}', '{$state_safe}', '{$zip_safe}', '{$contactname_safe}', 
        //                  '{$email_safe}', '{$phone_safe}', '{$title_safe}', 
        //                  'added on 11/19/2020 via spreadsheet')";
                         
        // Uncomment the next line to actually run the insert:
        // $companyid = db_query_insert_id($sql_insert);
        
        // Output result (showing what the ID *would be* if inserted, or a descriptive message)
        // Since $res is empty, this means it's a new record
        echo "<font color='red'>New Record: {$schoolcode_safe} - INSERT PENDING</font><br>";
        
        // Output the SQL statement for verification (optional)
        // echo htmlspecialchars($sql_insert) . "<br>";
    }
    else
    {
        // --- 6. Record Found: Output Existing ID ---
        $res_safe = htmlspecialchars($res);
        echo "<font color='black'>Existing Record: {$res_safe}, {$schoolcode_safe}</font><br>";
    }
}

// --- 7. Close File Handle ---
fclose( $handle );

?>