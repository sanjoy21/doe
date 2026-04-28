<?php 
require "mysql.php";

// Safely retrieve assumed global/external variables
// The import logic is now triggered by a 'go' parameter in the URL (e.g., script.php?go=1)
$go = $_POST['go'] ?? $_GET['go'] ?? null;
$err = ""; // Initialize error/status message

// Get the database connection link for safe queries (assuming $db_link is the mysqli connection handle)
$db_link = $GLOBALS['link'] ?? $link ?? null; 

if (!$db_link) {
    die("Error: Database connection link not found.");
}

// Display status message
echo "<p>{$err}</p>"; 

// --- Import Logic ---
// Note: To run this script, you must access it with a URL parameter like: 
// http://yourdomain.com/this_script.php?go=1
if( $go )
{
    $file_path = "/tmp/newresponders.csv";

    // Check if file exists
    if (!file_exists($file_path) || !is_readable($file_path)) {
        $err = "Error: CSV file not found or not readable at {$file_path}.";
        echo $err;
        exit;
    }
    
    // Use 'r' mode as we are only reading, not writing back to the file
    $handle = fopen( $file_path, "r" );
    $numloaded = 0;
    
    // Read the CSV data
    while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
        // CSV Format: [0] Last Name, [1] First Name, [2] School Code, [3] Date
        
        // Skip header row
        if( ($data[0] ?? '') == "Last Name" )
        {
            continue;
        }
        
        // Ensure required fields exist
        if (count($data) < 4) {
            echo "Skipping malformed row.<br>";
            continue;
        }

        $numloaded++;
        
        // Sanitize CSV data
        $schoolcode_esc = mysqli_real_escape_string($db_link, $data[2]);
        $firstname_esc = mysqli_real_escape_string($db_link, $data[1]);
        $lastname_esc = mysqli_real_escape_string($db_link, $data[0]);
        $date_raw = $data[3];
        
        // --- 1. Find Company ID ---
        $companyid = db_query_first_cell( "SELECT id FROM company_esi WHERE deleted = 0 AND schoolcode = '{$schoolcode_esc}'" );
        $companyid = (int)$companyid; // Cast to integer for safe SQL insertion
        
        if( !$companyid )
        {
            echo( "no match for school code: " . htmlspecialchars($data[2]) . "<br>" );
            $numloaded--; // Don't count responder if no company match
            continue;
        }
        
        // --- 2. Insert Responder ---
        $sql_responder = "INSERT INTO responders_esi 
                          (clientid, firstname, lastname, trainingsite, date) 
                          VALUES ({$companyid}, '{$firstname_esc}', '{$lastname_esc}', 'notsure', NOW())";
                          
        echo( htmlspecialchars($sql_responder) ."<br>" );
        $resp = db_query_insert_id( $sql_responder ); // Get the newly inserted responder ID
        
        if (!$resp) {
            echo "<font color='red'>Error inserting responder: " . mysqli_error($db_link) . "</font><br>";
            continue;
        }
        
        // --- 3. Insert Training Date ---
        // Convert the date string to YYYY-MM-DD format
        $tm = date( "Y-m-d", strtotime( $date_raw ) );
        
        $sql_training = "INSERT INTO responder_training_dates 
                         (responderid, trainingdate) 
                         VALUES ({$resp}, '{$tm}')";
                         
        echo( htmlspecialchars($sql_training) . "<br>");
        db_query( $sql_training );
    }
    
    fclose($handle);

    $err = "<font color='green'>Successfully attempted to load {$numloaded} responders (see output for details).</font><br>";
    echo $err;
}
?>