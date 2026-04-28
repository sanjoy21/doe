<?php 
include "mysql.php";

$sql = "select * from company_esi where iscorp = 1 and deleted = 0 and contactemail > ''";
$result = db_query_rows( $sql );

$xls_var = 1;

// Generate CSV instead of Excel
$filename = "report_companies_" . time() . ".csv";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen('php://output', 'w');

// Write UTF-8 BOM for Excel compatibility
fwrite($output, "\xEF\xBB\xBF");

// Write header row
$header = ["Contact Name", "Email", "Company"];
fputcsv($output, $header);

foreach( $result as $row )
{
    // Prepare data row
    $rowData = [
        $row["contactname"] ?? '',
        $row["contactemail"] ?? '',
        $row["companyname"] ?? ''
    ];
    
    // Escape any formulas that might start with =, +, - or @ to prevent CSV injection
    foreach($rowData as &$value) {
        if($value !== null && $value !== '') {
            $firstChar = substr($value, 0, 1);
            if(in_array($firstChar, array('=', '+', '-', '@'))) {
                $value = "'" . $value;
            }
        }
    }
    
    fputcsv($output, $rowData);
}

// Close the output stream
fclose($output);
exit();
?>