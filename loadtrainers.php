<?php 
// Include the database connection setup
require_once('mysql.php');

// Initialize assumed database link for escaping functions
$db_link = $GLOBALS['link'] ?? null; 

// Initialize non-CSV variables used in the INSERT query
$cell = "";
$otherphone = "";
$otherphoneext = "";
$salutation = "";
$mi = "";
$password1 = "aksdskajdskajds123"; // Hardcoded password (NOTE: Should be replaced with a secure HASH in production!)
$usertype = 'trainer';

// Check if the form was submitted and a file was uploaded
if (isset($_POST['go']) && !empty($_FILES["gofile"]["tmp_name"]))
{
    $file_tmp_name = $_FILES["gofile"]["tmp_name"];

    // Safely open the uploaded file
    $handle = fopen($file_tmp_name, "r");
    if ($handle === false) {
        die("Error: Could not open uploaded file.");
    }
    
    $rowcnt = 0;
    $headerrow = [];

    // Loop through each row in the CSV file
    while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
        
        // Identify and skip the header row based on the first column value
        if (($data[0] ?? '') === "Salutation:")
        {
            // Map the header names to their column indexes for safe access
            $headerrow = array_flip( $data );
            /* // Header structure referenced in original code:
            // Array ( [Salutation:] => 0 [First Name:] => 1 [Last Name:] => 2 [Street Address 1:] => 3 [Street Address 2:] => 4 [AHA ID:] => 5 [City:] => 6 [State:] => 7 [Zip:] => 8 [Phone Number:] => 9 [Ext:] => 10 [Email Address:] => 11 [Notes:] => 12 )
            */
            continue;
        }

        // Ensure we have headers before proceeding
        if (empty($headerrow)) {
            continue; 
        }

        // --- Data Extraction (Using header map for robust column access) ---
        $first_name_raw = trim($data[$headerrow["First Name:"]] ?? '');
        $last_name_raw = trim($data[$headerrow["Last Name:"]] ?? '');
        $address1_raw = trim($data[$headerrow["Street Address 1:"]] ?? '');
        $address2_raw = trim($data[$headerrow["Street Address 2:"]] ?? '');
        $ahaid_raw = trim($data[$headerrow["AHA ID:"]] ?? '');
        $city_raw = trim($data[$headerrow["City:"]] ?? '');
        $state_raw = trim($data[$headerrow["State:"]] ?? '');
        $zip_raw = trim($data[$headerrow["Zip:"]] ?? '');
        $phone_raw = trim($data[$headerrow["Phone Number:"]] ?? '');
        $phone_ext_raw = trim($data[$headerrow["Ext:"]] ?? '');
        $login_raw = trim($data[$headerrow["Email Address:"]] ?? ''); // Used as userid
        $notes_raw = trim($data[$headerrow["Notes:"]] ?? '');

        // Skip records with no email (used as userid/login)
        if (empty($login_raw)) {
            continue;
        }

        $password1_safe = mysqli_real_escape_string($db_link, $password1); 
        $first_name_safe = mysqli_real_escape_string($db_link, $first_name_raw);
        $last_name_safe = mysqli_real_escape_string($db_link, $last_name_raw);
        $address1_safe = mysqli_real_escape_string($db_link, $address1_raw);
        $address2_safe = mysqli_real_escape_string($db_link, $address2_raw);
        $city_safe = mysqli_real_escape_string($db_link, $city_raw);
        $state_safe = mysqli_real_escape_string($db_link, $state_raw);
        $zip_safe = mysqli_real_escape_string($db_link, $zip_raw);
        $phone_safe = mysqli_real_escape_string($db_link, $phone_raw);
        $phone_ext_safe = mysqli_real_escape_string($db_link, $phone_ext_raw);
        $login_safe = mysqli_real_escape_string($db_link, $login_raw);
        $notes_safe = mysqli_real_escape_string($db_link, $notes_raw);
        $ahaid_safe = mysqli_real_escape_string($db_link, $ahaid_raw);

        // Escape placeholder variables
        $cell_safe = mysqli_real_escape_string($db_link, $cell);
        $otherphone_safe = mysqli_real_escape_string($db_link, $otherphone);
        $otherphoneext_safe = mysqli_real_escape_string($db_link, $otherphoneext);
        $salutation_safe = mysqli_real_escape_string($db_link, $salutation);
        $mi_safe = mysqli_real_escape_string($db_link, $mi);
        $usertype_safe = mysqli_real_escape_string($db_link, $usertype);
        
        // --- Insert Query (User Record) ---
        $sql_insert = "INSERT INTO user 
                        (signupdate, redirectURL, password, first_name, last_name, address1, address2, city, state, zip, phone, phone_ext, cell, userid, otherphone, otherphoneext, salutation, mi, usertype) 
                       VALUES 
                        (NOW(), '/tcalendar.php', '{$password1_safe}', '{$first_name_safe}', '{$last_name_safe}', '{$address1_safe}', '{$address2_safe}', '{$city_safe}', '{$state_safe}', '{$zip_safe}', '{$phone_safe}', '{$phone_ext_safe}', '{$cell_safe}', '{$login_safe}', '{$otherphone_safe}', '{$otherphoneext_safe}', '{$salutation_safe}', '{$mi_safe}', '{$usertype_safe}')";

        $session_id = db_query_insert_id( $sql_insert ); // Execute INSERT and get the new user's ID
        
        // --- Update Query (Trainer Metadata) ---
        if ($session_id) {
            $tid = (int)$session_id; // Use the newly created ID
            
            // Build the set clause for the update
            $fpr = "";
            $fpr .= ", trainingsites = '1'"; // Set trainingsites flag
            $fpr .= ", ahaid = '{$ahaid_safe}'"; // Set AHA ID
            // Original code had other commented-out fields:
            // $fpr = ", fingerprinted = '$fingerprinted'";
            // $fpr .= ", corporate = '$corporate'";
            // $fpr .= ", rollout2010 = '$rollout2010'";
            // $fpr .= ", tcfaculty = '$tcfaculty'";
            // $fpr .= ", lastrenewaldate = '$lastrenewaldate'";
            // $fpr .= ", firstaid = '$firstaid'";
            // $fpr .= ", cellprovider = '$cellprovider'";
            // $fpr .= ", ucp = '$ucp'";
            
            $fpr .= ", newui = '1'"; // Set new UI flag
            
            // Execute the update to set the notes and trainer-specific flags
            $sql_update = "UPDATE user SET notes = '{$notes_safe}' {$fpr} WHERE id = {$tid}";
            db_query( $sql_update );
        }
    }
    // Close the file handle (this was originally inside the loop, now correctly outside)
    fclose( $handle );
}
?>
<form method='post' enctype='multipart/form-data'>
<input type='file' name='gofile' value=''><input type='submit' name='go' value='Go'>
</form>