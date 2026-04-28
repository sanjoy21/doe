<?php
include "mysql.php";
$sql = "select * from blackstonesignups order by dateadded desc ";
$results = db_query_rows( $sql );

// The condition '|| 1' forces this block to always execute, regardless of $xls value.
if( $xls || 1 ) { 
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="blackstone.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write Header Row
    $headers = array(
        "Date Added",
        "First Name",
        "Name",
        "Email",
        "Location",
        // "IP Address"
    );
    fputcsv($output, $headers);
    
    // Write Data Rows
    foreach( $results as $row )
    { 
        $row_data = array(
            $row["dateadded"] ?? '',
            $row["first_name"] ?? '',
            $row["name"] ?? '',
            $row["email"] ?? '',
            $row["location"] ?? '',
            // $row["ipaddress"] ?? ''
        );
        
        fputcsv($output, $row_data);
    }
    
    fclose($output);
    exit;
}

?>