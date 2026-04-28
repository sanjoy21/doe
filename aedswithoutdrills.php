<?php 
include "mysql.php";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="aeds.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

$sql = "select a.serial, a.location, c.schoolcode, c.companyname 
        from company_esi c, aed_esi a 
        where a.clientid = c.id 
        and a.deleted = 0 
        and c.deleted = 0 
        and c.showsondrillreports = 1 
        and c.iscorp = 0 
        and a.serial not in ( select serial from aed_to_drill ) 
        and a.serial not in ( select serial from aed_to_servicecall )";

// Prepare and write headers
$headers = array("School", "School Code", "Serial", "Location");
fputcsv($output, $headers);

$res = db_query_rows( $sql );
foreach( $res as $r )
{
    $row_data = array(
        $r["companyname"] ?? '',
        $r["schoolcode"] ?? '',
        $r["serial"] ?? '',
        $r["location"] ?? ''
    );
    
    fputcsv($output, $row_data);
}

fclose($output);
exit;
?>