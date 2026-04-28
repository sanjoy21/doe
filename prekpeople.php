<?php
// This script reads a local CSV file, matches responders by PMS ID or name,
// and reports training status.
require_once('mysql.php');

// Assumed external functions:
// function getCompanyName(int $client_id): string;
// function db_escape(string $s): string; // Using assumed escape function for safety

function db_escape($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

$nologinrequired = true;

// Define file path
$csv_file_path = "/tmp/prekpeople.csv";

// Attempt to open the CSV file
$handle = @fopen($csv_file_path, "r");

if ($handle === false) {
    die("<h1>Error: Cannot open CSV file at " . htmlspecialchars($csv_file_path) . ".</h1>");
}

// --- 1. Fetch List of Pre-K Responder IDs for name matching fallback ---
// Finds all responder IDs associated with classes at 'pre-k' companies
$pkresps_ids = db_query_array("
    SELECT r.responderid 
    FROM responder_to_class r
    JOIN class c ON c.id = r.classid
    JOIN company_esi co ON c.companyid = co.id  
    WHERE co.companyname LIKE '%pre-k%'
    ", "responderid", "responderid"
);
// Implode or use '0' to prevent SQL errors if no IDs are found
$pkresps_list = !empty($pkresps_ids) ? implode(", ", $pkresps_ids) : '0';

// --- 2. HTML Table Setup ---
echo <<<HTML
<table border=1 cellpadding=4 cellspacing=0 style="width:100%; font-family: Arial, sans-serif; font-size: 14px;">
    <tr style="background-color: #f0f0f0;">
        <th>PMS ID from file</th>
        <th>Name From File</th>
        <th>Name From DB</th>
        <th>School</th>
        <th>Most Recent Training Date</th>
        <th>Upcoming Classes Start Date</th>
    </tr>
HTML;

// --- 3. Process CSV Rows ---
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { 
    // Check for required data (column 10) and skip header row
    if (empty($data[10]) || ($data[0] ?? '') == "Fiscal Year") {
        continue;
    }
    
    // Assign variables for current row
    $pmsid_current = trim($data[10]);
    $name_from_file = htmlspecialchars(trim($data[11] ?? ''));
    $resp = false; // Initialize responder match

    // Start row output (PMS ID and File Name are always available here)
    echo "<tr>";
    // Using the correctly assigned variable
    echo "<td>" . htmlspecialchars($pmsid_current) . "</td>"; 
    echo "<td>{$name_from_file}</td>";

    // --- A. Search Attempt 1: Match by PMS ID (Exact) ---
    $pmsid_safe = db_escape($pmsid_current);
    $resp = db_query_first("SELECT r.* FROM responders_esi r WHERE r.pmsid = '{$pmsid_safe}' AND deleted = 0");
    
    // --- Search Attempt 2: Match by PMS ID (Integer conversion fallback) ---
    if (!$resp) {
        $tmppmsid = (int) $pmsid_current;
        if ($tmppmsid > 0) {
            $resp = db_query_first("SELECT r.* FROM responders_esi r WHERE r.pmsid = '{$tmppmsid}' AND deleted = 0");
        }
    }

    // --- Search Attempt 3: Match by Name (Fallback for Pre-K group) ---
    if (!$resp) {
        $name_parts = explode(", ", $data[11] ?? '');
        if (count($name_parts) === 2) {
            $lastname = db_escape(trim($name_parts[0]));
            $firstname = db_escape(trim($name_parts[1]));

        
            $resp = db_query_first("
                SELECT r.* FROM responders_esi r 
                WHERE r.firstname = '".mysqli_real_escape_string($link, $firstname)."' 
                AND r.lastname = '".mysqli_real_escape_string($link, $lastname)."' 
                AND r.deleted = 0 
                AND r.responderid IN ({$pkresps_list})
            ");
        }
    }
    
    // --- No Match Found ---
    if (!$resp) {
        echo "<td colspan='4' style='color: red;'>No database match found</td>";
        echo "</tr>\n";
        continue; 
    }
    
    // --- Match Found: Retrieve Details ---
    $responder_id = (int) $resp['responderid'];
    $client_id = (int) $resp['clientid'];
    
    // 1. Database Match Name
    $db_name = htmlspecialchars($resp['firstname'] . " " . $resp['lastname']);
    echo "<td>{$db_name}</td>";
    
    // 2. School Name
    $school_name = htmlspecialchars(getCompanyName($client_id));
    echo "<td><a href='viewcompany.php?companyid={$client_id}'>{$school_name}</a></td>";

    // 3. Most Recent Training Date
    $training = db_query_first("
        SELECT rtc.trainingdate FROM responder_training_dates rtc
        JOIN class c ON c.id = rtc.classid 
        WHERE rtc.responderid = {$responder_id} 
        AND rtc.trainingdate > '2013-11-01' 
        ORDER BY rtc.trainingdate DESC LIMIT 1
    ");
    $training_date = htmlspecialchars($training['trainingdate'] ?? 'N/A');
    echo "<td>{$training_date}</td>\n";

    // 4. Upcoming Classes
    $upcoming = db_query_first("
        SELECT c.startdate FROM responder_to_class rtc
        JOIN class c ON c.id = rtc.classid 
        WHERE rtc.responderid = {$responder_id} 
        AND c.startdate > NOW() 
        ORDER BY c.startdate ASC LIMIT 1
    ");
    $upcoming_date = htmlspecialchars($upcoming['startdate'] ?? 'N/A');
    echo "<td>{$upcoming_date}</td>\n";
    
    echo "</tr>";
}

echo "</table>";

// Close the file handle
fclose($handle);
?>