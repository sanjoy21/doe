<?php

include "mysql.php";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="doe_monthly_responders.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Prepare headers
$headers = array(
    "TRID",
    "First Name",
    "Last Name",
    "PMS ID",
    "DBN",
    "Building Code"
);

// Write headers
fputcsv($output, $headers);

// Calculate the date two years ago, on the 1st of the current month
$dt = date( "Y-m-d", mktime( 0, 0, 0, date( "m" ), 1, date( "Y" )-2 ) );

$res = db_query_rows( "select r.*, schoolcode from responders_esi r, company_esi c 
                       where c.id = r.clientid 
                         and r.deleted = 0 
                         and c.deleted = 0 
                         and c.showsondrillreports = 1 
                         and c.iscorp = 0 
                         and pmsidvalidated = 1 
                         and responderid in ( select responderid from responder_training_dates where trainingdate > '$dt' ) 
                         and buildingcode > '' 
                         and pmsid not in ( select acceptcode from free_registrants where acceptcode > '' ) " );

foreach( $res as $r )
{
    $row = array(
        $r["responderid"] ?? '',
        $r["firstname"] ?? '',
        $r["lastname"] ?? '',        
        $r["pmsid"] ?? '',
        $r["schoolcode"] ?? '',
        $r["buildingcode"] ?? ''
    );
    
    // Write row to CSV
    fputcsv($output, $row);
}

fclose($output);
exit;

?>