<?php 
include "mysql.php";

// Initialize assumed database link for escaping functions if required
$db_link = $GLOBALS['link'] ?? $link; 

// --- 1. File Handling ---
$file_path = "/tmp/sherryaeds.csv";
$handle = fopen($file_path, "r");

if ($handle === false) {
    die("Error: Could not open file '{$file_path}' for reading.");
}

$headers = [];
$already = [];
$rowcnt = 0;

// --- 2. CSV Processing Loop ---
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    
    // Skip and map headers on the first iteration
    if( !$headers )
    {
        $headers = array_flip( $data );
        echo "<pre>";
        print_r( $headers );
        echo "</pre><Br>";
        continue;
    }
    
    // --- Data Extraction and Preparation (Safe access) ---
    // The serial number is used from column index 1 (DFTA ID)
    $serial_raw = trim( $data[1] ?? '' );
    $serial = escMe( $serial_raw ); // Assumed safe escaping function
    
    $certexp_raw = trim( $data[$headers["Certification Exp date"]] ?? '' );
    $certexp = escMe( $certexp_raw ); 
    
    $location_raw = trim( $data[$headers["AED LOCATION"]] ?? '' );
    $location = escMe( $location_raw );
    
    $company_raw = trim( $data[$headers["PROGRAM NAME"]] ?? '' );
    $company = escMe( $company_raw );
    
    $name_raw = trim( $data[$headers["Life Savers"]] ?? '' );
    $name = escMe( $name_raw );
    
    // --- 3. Find Company ID ---
    $company_safe_for_query = mysqli_real_escape_string($db_link, $company_raw);
    $sql_company_id = "SELECT id FROM company_esi WHERE companyname = '{$company_safe_for_query}' AND deleted = 0";
    $companyid = db_query_first_cell( $sql_company_id ); // Assumed function that returns a single value
    
    if( !$companyid )
    {
        echo "no match for company: " . htmlspecialchars($company_raw) . " -- " . htmlspecialchars($name_raw) . " <Br>";
    }
    else
    {
        // --- 4. Company Found: Prepare Training Dates ---
        
        // Calculate training date (2 years prior to certification expiration)
        $certdate = date( "Y-m-d", strtotime( "{$certexp_raw} - 2 years" ) );
        $companyid_int = (int)$companyid;
        
        // --- 5. Conditional Import Logic (Original code was disabled with 'if (1 == 0)') ---
        if( 1 == 0 ) // This block is currently disabled/skipped
        {
            // Extract and escape contact information
            $email_raw = trim( $data[$headers["Email Address"]] ?? '' );
            $email = escMe( $email_raw );
            
            // Prevent processing the same email address multiple times
            if( $already[$email] ?? false ) continue;
            $already[$email] = 1;
            
            // Split name into first and last
            $exp = explode( " ", $name_raw );
            $first_raw = array_shift( $exp );
            $last_raw = implode( " ", $exp );
            $first = escMe( $first_raw );
            $last = escMe( $last_raw );
            
            $phone_raw = trim( $data[$headers["Telephone #"]] ?? '' );
            $phone = escMe( $phone_raw );

            // Insert Responder Record
            $sql_insert_responder = "INSERT INTO responders_esi ( firstname, lastname, email, dayphone, clientid ) 
                                     VALUES ( '{$first}', '{$last}', '{$email}', '{$phone}', {$companyid_int} )";
                                     
            $repsid = db_query_insert_id( $sql_insert_responder ); // Assumed function to execute and return last insert ID
            echo htmlspecialchars($sql_insert_responder) . "<br>";
            
            // Insert Training Date Record
            $sql_insert_training = "INSERT INTO responder_training_dates ( responderid, trainingdate, program ) 
                                    VALUES ( {$repsid}, '{$certdate}', 'Non ESI Training' )";
            
            db_query( $sql_insert_training );
            echo htmlspecialchars($sql_insert_training) . "<br>";
        }
    }
}

// --- 6. Close File Handle (Corrected variable name) ---
fclose( $handle ); 

?>