<?php 
include "mysql.php";

$handle = @fopen("/tmp/osyd school list by isc.csv", "r"); // Use @ to suppress file-not-found warnings initially

if ($handle === FALSE) {
    echo "Error: Could not open file /tmp/osyd school list by isc.csv for reading.";
    exit;
}

// Get the database connection link for escaping strings
$db_link = $GLOBALS['link'] ?? $link; 
$update_count = 0;
$no_match_count = 0;

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    
    // PHP 8.2 Fix: Quote array keys and use null coalescing for safety
    $ats_code = trim($data[0] ?? ''); 
    
    // Skip header row
    if( $ats_code == "ATS" ) {
        continue;
    }
    
    if( !$ats_code ) {
        continue;
    }

    // --- 1. Format school code (e.g., A001 -> A-0-001) ---
    // Safety check for length before substring
    if (strlen($ats_code) >= 6) {
        $newcode = substr( $ats_code, 0, 2 ) . "-". substr( $ats_code, 2, 1 ) . "-" . substr( $ats_code, 3, 3 );
    } else {
        // Skip or log invalid format
        echo( "Skipped due to invalid ATS format: " . htmlspecialchars($ats_code) . "<br>" );
        continue;
    }
    
    // --- 2. Find matching company ID ---
    $safe_newcode = mysqli_real_escape_string($db_link, $newcode);
    $res = db_query_first_cell( "SELECT id FROM company_esi WHERE schoolcode = '{$safe_newcode}' AND iscorp = 0" );
    
    // --- 3. Process Phone Number (Assuming $data[3] is the combined phone/fax field) ---
    $raw_phone_fax = $data[3] ?? '';
    $phonearra = explode( "(", $raw_phone_fax );
    // The original logic assumes the phone number starts after the first '('.
    // We try to reconstruct the phone number safely.
    $phone = (isset($phonearra[1]) ? '(' . $phonearra[1] : $raw_phone_fax);

    // --- 4. Sanitize and Escape Data ---
    $companyname_safe = mysqli_real_escape_string($db_link, $data[1] ?? '');
    $principalname_safe = mysqli_real_escape_string($db_link, $data[2] ?? '');
    $schoolphone_safe = mysqli_real_escape_string($db_link, $phone);
    $address_safe = mysqli_real_escape_string($db_link, $data[4] ?? '');
    $city_safe = mysqli_real_escape_string($db_link, $data[5] ?? '');
    $state_safe = mysqli_real_escape_string($db_link, $data[6] ?? '');
    $zip_safe = mysqli_real_escape_string($db_link, $data[7] ?? '');

    if( $res )
    {
        // --- MATCH: Update existing record ---
        $safe_id = (int)$res;
        
        $sql = "UPDATE company_esi SET 
                    companyname = '{$companyname_safe}', 
                    principalname = '{$principalname_safe}',
                    schoolphone = '{$schoolphone_safe}',
                    address = '{$address_safe}', 
                    city = '{$city_safe}', 
                    state = '{$state_safe}', 
                    zip = '{$zip_safe}'
                WHERE id = {$safe_id}";

        db_query( $sql ); 
        // echo( htmlspecialchars($sql) . "<br>");
        $update_count++;
    }
    else
    {
        // --- NO MATCH: Report the missing entry ---
        echo( "didn't find a match for " . htmlspecialchars($ats_code) . ", " . htmlspecialchars($newcode) . ", " . htmlspecialchars($data[1] ?? 'N/A') . "<br> " );
        $no_match_count++;
    }
}

@fclose($handle);

// echo "<br>--- Summary ---<br>";
// echo "Records Updated: {$update_count}<br>";
// echo "Records Not Matched: {$no_match_count}<br>";

?>