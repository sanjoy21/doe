<?php
include "mysql.php";
require_once "birdie/api.php";

// Placeholder for data cleaning before writing to CSV
if (!function_exists('sanitize_csv_output')) {
    function sanitize_csv_output($data) {
        if (is_null($data)) return '';
        if (is_bool($data)) return $data ? "Yes" : "No";
        if (is_numeric($data)) return $data;
        // Basic string cleaning: remove newlines/tabs that could break CSV format
        return str_replace(array("\r", "\n", "\t"), ' ', (string)$data); 
    }
}

$tmparr = getBirdieFields( $type );

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="shipping_export.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// --- Write Headers ---
$headers = array();
foreach( $tmparr as $o ) { 
    $headers[] = sanitize_csv_output($o);
}
fputcsv($output, $headers);

// --- SQL Injection Mitigation ---
// 1. Filter out non-array types for safety.
if (!is_array($ids)) {
    $ids = [];
}

// 2. Cast all IDs to integers and create a comma-separated string for the IN clause.
// This is the CRITICAL security fix.
$safe_ids = implode(",", array_map('intval', $ids));

// 3. Construct the query using the safe list. If $safe_ids is empty, use a dummy value like 0.
if (empty($safe_ids)) {
    $safe_ids = '0';
}
$sql = "select * from class where id IN ({$safe_ids})";

$res = db_query_rows( $sql );

// --- Write Data Rows ---
foreach( $res as $r )
{
    $row_data = array();
    
    // Ensure ID is an integer before fetching class info
    $class_id = (int)($r['id'] ?? 0);
    $classinfo = getClassInfo( $class_id );

    foreach( $tmparr as $t => $throwaway )
    {
        $val = $classinfo[$t]["value"] ?? '';
        
        // Handle "Order Date" formatting
        if (isset($throwaway) && $throwaway == "Order Date")
        {
            if ($val) {
                // Ensure $val is treated as a string for strtotime
                $val = date( "Y-m-d", strtotime( (string)$val ) );
            }
        }
        
        // Add sanitized value to row data
        $row_data[] = sanitize_csv_output($val);
    }
    
    // Write row to CSV (fputcsv handles proper escaping)
    fputcsv($output, $row_data);
}

fclose($output);
exit;

?>