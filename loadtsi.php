<?php
// Include the database connection file
require_once "mysql.php";

// Define the file path
$file_path = "/tmp/tsi.csv";

// --- Custom Function for Date Formatting ---
/**
 * Converts a string date (M/Y format) into a MySQL DATE string (Y-M-01) or NULL.
 * @param string $dt The date string in "Month/Year" format.
 * @return string The formatted date string in quotes, or "NULL".
 */
function getDbData($dt)
{
    // Check for empty or "N/A" values
    if (!$dt || strtolower($dt) === "n/a") {
        return "NULL";
    } else {
        // Attempt to parse M/Y (e.g., 12/2008)
        $spl = explode("/", $dt);
        if (count($spl) >= 2) {
            // Reformat to YYYY-MM-01
            $formatted_dt = $spl[1] . "-" . $spl[0] . "-01";
            
            // Output for debugging (original script kept this)
            echo $dt . "-";
            echo $formatted_dt . "<br>";
            
            // Return safe, quoted date string
            return "'" . mysqli_real_escape_string($GLOBALS['link'] ?? '', $formatted_dt) . "'";
        }
        // If parsing fails, treat it as null for safety
        return "NULL";
    }
}

// --- Main CSV Processing Loop ---
// Use fopen and check if the file exists and is readable
if (!file_exists($file_path) || !is_readable($file_path)) {
    die("Error: CSV file not found or is unreadable at: {$file_path}");
}

$handle = fopen($file_path, "r");

if ($handle === FALSE) {
    die("Error: Failed to open CSV file.");
}

// Skip header row if necessary (uncomment if the CSV has a header)
// $header = fgetcsv($handle, 9999, ","); 

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    // Check if the row has the expected number of columns (at least 29, based on $data[28])
    if (count($data) < 29) {
        echo "Skipping row: insufficient columns.<br>";
        continue; 
    }
    
    // Assign data to variables
    // Note: Most of these variables are unused in the final logic, but kept for context.
    $groupname          = $data[0];
    $companyname        = $data[1];
    $buildingno         = $data[2];
    $address            = $data[3];
    $city               = $data[4];
    $state              = $data[5];
    $zip                = $data[6];
    $region             = $data[7];
    $borough            = $data[8];
    $contactname        = $data[9];
    $contactphone       = $data[10];
    $contactemail       = $data[11];
    $aedserial          = $data[12];
    $g2005              = ($data[13] === "Yes") ? 1 : 0;
    $aedtype            = $data[14];
    $aedfloor           = $data[15];
    $aedlocation        = $data[16];
    $padaexp            = $data[17];
    $padbexp            = $data[18];
    $pedexp             = $data[19];
    $aedpurchased       = $data[20];
    $sparebatt          = $data[21];
    $batteryinstalldate = $data[22];
    $medicaldirector    = $data[23];
    $internalreference  = $data[28];
    
    // --- Data Sanitization for SQL ---
    // Use mysqli_real_escape_string for all values going into the database
    $db_conn = $GLOBALS['link'] ?? null; // Assume DB connection link is available

    $companyname_safe = mysqli_real_escape_string($db_conn, $companyname);
    $buildingno_safe = mysqli_real_escape_string($db_conn, $buildingno);
    $aedserial_safe = mysqli_real_escape_string($db_conn, $aedserial);
    $aedpurchased_safe = mysqli_real_escape_string($db_conn, $aedpurchased);

    // --- Active Database Updates (Original Logic) ---

    // 1. Update AED purchase date
    $sql_aed_update = "UPDATE aed_esi 
                       SET purchasedate = '{$aedpurchased_safe}' 
                       WHERE serial = '{$aedserial_safe}'";
    db_query($sql_aed_update);
    echo "Query 1: " . htmlspecialchars($sql_aed_update) . "<br>";

    // 2. Update company building number (updates only the most recently added company with that name)
    $sql_company_update = "UPDATE company_esi 
                           SET buildingno = '{$buildingno_safe}' 
                           WHERE companyname = '{$companyname_safe}' 
                           ORDER BY id DESC LIMIT 1";
    db_query($sql_company_update);
    echo "Query 2: " . htmlspecialchars($sql_company_update) . "<Br>";
}

fclose($handle);

// echo "--- Script Finished ---<br>";
?>