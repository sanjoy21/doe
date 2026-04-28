<?php
include "mysql.php";

// --- 1. Database Query ---
// Select non-corporate companies that are not deleted and are missing a locationcode.
$sql = "SELECT 
            companyname, schoolcode, c.address, c.city, c.borough, c.region 
        FROM 
            company_esi c 
        WHERE 
            iscorp = '0' 
            AND c.deleted = 0 
            AND ( c.locationcode IS NULL OR c.locationcode = '' ) 
            AND companyname NOT LIKE 'P%@%' 
        ORDER BY 
            c.borough, c.address";

// Assuming db_query_rows returns an array of associative arrays (rows)
$schools = db_query_rows( $sql );

// --- 2. CSV Report Generation ---
// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="nolocations.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// --- Write Header Row ---
$headers = array(
    "Name",
    "Code",
    "Address",
    "City",
    "Borough",
    "Region",
    "Schools"
);
fputcsv($output, $headers);

// --- Write Data Rows ---
foreach( $schools as $sid => $crow )
{
    // Prepare row data with null safety
    $row_data = array(
        $crow['companyname'] ?? '',
        $crow['schoolcode'] ?? '',
        $crow['address'] ?? '',
        $crow['city'] ?? '',
        $crow['borough'] ?? '',
        $crow['region'] ?? '',
        ''
    );
    
    fputcsv($output, $row_data);
} 

// --- Close and Send File ---
fclose($output);
exit;
?>