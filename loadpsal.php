<?php 
require "mysql.php"; 

// Get the database connection link for safe queries (assuming $db_link is the mysqli connection handle)
$db_link = $GLOBALS['link'] ?? $link ?? null; 

if (!$db_link) {
    die("Error: Database connection link not found.");
}

$file_path = "/tmp/psal.csv";

// Check if file exists
if (!file_exists($file_path) || !is_readable($file_path)) {
    die("Error: CSV file not found or not readable at {$file_path}");
}

// Open file handle
$handle = fopen($file_path, "r");

echo( "<table border='1'><tr><th>Found?</th><th>Our Name</th><th>Their Name</th><th>Our Phone</th><th>Their Phone</th></tr>" );

$matches = 0;
$nonmatches = 0;
$row_count = 0;

// Read CSV file line by line
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    $row_count++;
    
    // Skip header row (assuming first row is header)
    if ($row_count === 1) {
        continue;
    }
    
    // Unpack data columns
    // "School Name", Borough, Address, "School Phone", Principal, "Athletic Director (AD)", "AD Home Phone", "Ad Work Phone"
 $schoolname = $data[0] ?? '';
 $borough = $data[1] ?? '';
 $address = $data[2] ?? '';
 $schoolphone = $data[3] ?? '';
 $principal = $data[4] ?? ''; 
 $adname = $data[5] ?? '';
 $adhomephone = $data[6] ?? '';
 $adwork = $data[7] ?? '';
    
    // Sanitize input variables
    $schoolname_esc = mysqli_real_escape_string($db_link, $schoolname);
    $schoolphone_esc = mysqli_real_escape_string($db_link, $schoolphone);
    
    $school = false;

    // --- Heuristic 1: Exact Name Match ---
 $school = db_query_first( "SELECT id, companyname, schoolphone FROM company_esi WHERE companyname = '{$schoolname_esc}' AND deleted = 0" );
    
    // --- Heuristic 2: Name LIKE Match (Prefix) ---
 if( !$school ) {
 $school = db_query_first( "SELECT id, companyname, schoolphone FROM company_esi WHERE companyname LIKE '{$schoolname_esc}%' AND deleted = 0" );
    }
    
    // --- Heuristic 3: Phone Number Match ---
 if( !$school ) {
 $school = db_query_first( "SELECT id, companyname, schoolphone FROM company_esi WHERE schoolphone LIKE '{$schoolphone_esc}' AND deleted = 0" );
    }
    
    // --- Output Results ---
 if( $school )
 {
        // Data Sanitization
        $school_id_safe = htmlspecialchars($school['id'] ?? '');
        $school_name_safe = htmlspecialchars($school['companyname'] ?? '');
        $school_phone_safe = htmlspecialchars($school['schoolphone'] ?? '');
        $csv_school_name_safe = htmlspecialchars($schoolname);
        $csv_school_phone_safe = htmlspecialchars($schoolphone);
        
 echo( "<tr><td><a target='_blank' href='viewcompany.php?id={$school_id_safe}'>found {$school_id_safe}</a></td><td>{$school_name_safe}</td><td>{$csv_school_name_safe}</td><td>{$school_phone_safe}</td><td>{$csv_school_phone_safe}</td></tr>" );
 $matches++;
 }
 else
 {
        $csv_school_name_safe = htmlspecialchars($schoolname);
        $csv_borough_safe = htmlspecialchars($borough);

 echo( "<tr><td colspan='5'>found nothing for {$csv_school_name_safe}, {$csv_borough_safe}</td></tr>" );
 $nonmatches++;
 }
}

fclose($handle);

echo( "</table>" );
echo( "<br><br>Non-Matches: {$nonmatches} <br>Matches: {$matches}<br>" );
?>