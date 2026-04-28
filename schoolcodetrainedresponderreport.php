<?php
// Initialize external variables safely
$session_iscorp = $session_iscorp ?? 0;

include "mysql.php";
// Assumed external functions: db_query_rows, db_query_first_cell, getCurrentResponders

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="report_responders.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Safely escape session_iscorp for SQL
$iscorp_safe = is_numeric($session_iscorp) ? (int)$session_iscorp : 0;

// Main query to get list of companies
$sql = "SELECT id, companyname, schoolcode, campusid 
        FROM company_esi c 
        WHERE iscorp = '{$iscorp_safe}' 
        AND c.deleted = 0 
        AND c.donotinclude = 0 
        ORDER BY companyname";
$result = db_query_rows($sql); 

// Write Header Row
$headers = array(
    "School",
    "SchoolCode",
    "Current Responders (Campus/School Total)"
);
fputcsv($output, $headers);

// Write Data Rows
foreach ($result as $row) {
    $company_id = $row['id'] ?? 0;
    $campus_id = $row['campusid'] ?? null;
    $dt = 0; // Current Responders count
    
    // Logic to calculate responder count (Campus vs. Single School)
    if ($campus_id) {
        // If part of a campus, fetch all school IDs in that campus.
        // Use integer casting for security
        $clean_campus_id = (int)$campus_id;
        $schools_in_campus = db_query_first_cell("SELECT GROUP_CONCAT(id) FROM company_esi WHERE campusid = {$clean_campus_id}");
        
        // Count combined current responders for all schools in the campus
        if (!empty($schools_in_campus)) {
            // Note: getCurrentResponders might need to handle comma-separated IDs
            // If it expects a single ID, this logic may need adjustment
            $dt = count(getCurrentResponders($schools_in_campus));
        }
    } else {
        // Single school responder count
        $dt = count(getCurrentResponders($company_id));
    }
    
    // Prepare row data
    $row_data = array(
        $row["companyname"] ?? '',
        $row["schoolcode"] ?? '',
        $dt
    );
    
    fputcsv($output, $row_data);
}

fclose($output);
exit;
?>