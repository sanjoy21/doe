<?php 

require_once('mysql.php');

// Safely retrieve assumed global/session variables
$thisusersrow = $thisusersrow ?? [];
$session_iscorp = $session_iscorp ?? 0;
$zips = ""; // Initialize ZIP filter string

// Get the database connection link for safe queries
$db_link = $GLOBALS['link'] ?? $link; 

// --- 1. Apply Trainer ZIP Code Filtering ---
if( ($thisusersrow["usertype"] ?? null) == "trainer" ) {
    // Assuming getVisibleZipsString() exists and returns a WHERE clause fragment (e.g., "AND c.zip IN ('10001', '10002')")
    // The original code uses $zips inside the main WHERE clause without a leading "AND". 
    // We adjust it here to be a full AND clause fragment, or an empty string.
    $zips_clause_raw = getVisibleZipsString( "c" );
    // Check if the returned string is valid and wrap it safely. 
    // The original script assumes $zips will be appended directly, so we'll wrap it in "AND" if present.
    $zips = !empty($zips_clause_raw) ? " AND ({$zips_clause_raw}) " : "";
}


// --- 2. Calculate Date Cutoff (Next Month) ---
// Note: mktime is retained but $nextmonth is formatted for SQL comparison
$nextmonth_ts = mktime( 0, 0, 0, date( "m" ) + 1, date( "d" ), date( "Y" ) );
$nextmonth_sql = date( "Y-m-d", $nextmonth_ts );

// --- 3. Construct the SQL Query ---
$sql = "SELECT 
            c.buildingcode, c.id, c.companyname, c.city, c.zip, c.address, 
            CONCAT( c.contactname, ' ', c.contactphone, ' ', c.contactemail) AS contactinfo,
            a.serial, a.padaexpiration, a.padbexpiration, a.location, 
            a.pediatricpads, a.sparedate 
        FROM 
            company_esi c
        JOIN
            aed_esi a ON c.id = a.clientid 
        WHERE 
            c.iscorp = '{$session_iscorp}' 
            AND a.aedmissing = 0 
            AND a.outofservice = 0 
            AND c.isactive = 1 
            AND c.deleted = 0 
            AND a.deleted = 0 
            AND a.aedstolen = 0 
            AND (
                -- Pad A expiring by next month
                ( '{$nextmonth_sql}' >= a.padaexpiration AND a.padaexpiration <> '' )
                -- OR Pad B expiring by next month
                OR ( '{$nextmonth_sql}' >= a.padbexpiration AND a.padbexpiration <> '' ) 
                -- OR Pediatric pads expiring by next month (only for non-FRX models)
                OR ( a.model <> 'FRX' AND a.pediatricpads <> '' AND '{$nextmonth_sql}' >= a.pediatricpads )
            ) 
            {$zips} 
        ORDER BY 
            c.companyname";

// echo( $sql ); // Debugging output retained from original script

// --- 4. Set Filename and Include Reporting File ---
$filename = "report_expiring";

// Assuming traineraedslisting.php handles the execution of the $sql query
// and generates the report (either HTML or XLS/CSV depending on its internal logic).
// This file likely expects $sql, $filename, and potentially $res to be set.
include "traineraedslisting.php";
?>