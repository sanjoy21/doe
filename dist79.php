<?php 
require "mysql.php";

// Get the database connection link for escaping strings (needed if using mysqli_real_escape_string)
$db_link = $GLOBALS['link'] ?? $link; 

// --- 1. Set Headers for CSV Download ---
$filename = "dist79.xls"; // Preserving original filename as requested (will be a CSV file)

header("Content-Disposition: attachment; filename=\"{$filename}\"");
header("Content-Type: application/vnd.ms-excel"); // Use Excel MIME type for better client handling
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

$output = fopen('php://output', 'w');

// --- 2. Write Header Row ---
$headers = [ "School Code", "Name", "# Responders", "Last Exp Date" ];
fputcsv($output, $headers);

// --- 3. Determine the set of currently trained responder IDs ---
// This finds responders who have trained after a specific cut-off date.
$currentlytrained_rows = db_query_rows( "SELECT DISTINCT( responderid ) FROM responder_training_dates WHERE trainingdate > '2011-10-01'", "responderid", "responderid" );
$currentlytrained_ids = array_keys($currentlytrained_rows);

if (empty($currentlytrained_ids)) {
    // If no one is currently trained, the IN clause will be set to avoid an SQL error later
    $currentlytrained_clause = "0"; 
} else {
    // Escape IDs and implode for use in the SQL IN clause
    $safe_ids = array_map('intval', $currentlytrained_ids);
    $currentlytrained_clause = implode( ", ", $safe_ids );
}


// --- 4. Main Query: Fetch schools in District 79 ---
$sql = "SELECT id, schoolcode, companyname FROM company_esi WHERE deleted = 0 AND schoolcode LIKE '79%' AND iscorp = 0 ";
$res = db_query_rows( $sql );

// --- 5. Loop through schools and write data rows ---
foreach( $res as $r )
{
    $company_id = (int)($r['id'] ?? 0);
    
    // A. Count responders currently trained (trained after 2011-10-01)
    $sql_num = "SELECT 
                    COUNT(*) 
                FROM 
                    responders_esi 
                WHERE 
                    clientid = {$company_id} 
                    AND deleted = 0 
                    AND responderid IN ( {$currentlytrained_clause} )";
    $num = db_query_first_cell( $sql_num );
    
    // B. Find the latest training date for *any* responder in this school
    $sql_max_date = "SELECT 
                        MAX( rt.trainingdate ) 
                    FROM 
                        responder_training_dates rt
                    JOIN 
                        responders_esi r ON r.responderid = rt.responderid 
                    WHERE 
                        r.clientid = {$company_id}";

    $num2 = db_query_first_cell( $sql_max_date );
    
    $last_exp_date = "";
    if( $num2 )
    {
        // Calculate expiration date (Training Date + 2 years)
        $last_exp_date = date( "m/d/Y", strtotime( $num2 . " + 2 years" ) );
    }

    // Write the data row to the CSV file
    fputcsv($output, [
        $r['schoolcode'] ?? '', 
        $r['companyname'] ?? '', 
        $num, 
        $last_exp_date
    ]);
}

fclose($output);
exit;
?>