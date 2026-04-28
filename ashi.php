<?php
require_once('mysql.php');
$crow = getClassRow($id);
$comrow = getCompanyRow($crow["companyid"]);

// Use prepared statement or type casting for security
$class_id = (int)$id;
$res = db_query_rows("select r.*, rtd.trainingdate as thedate from responder_training_dates rtd, responders_esi r where classid = '$class_id' and r.responderid = rtd.responderid");

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="ecards_' . $class_id . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Write headers
$headers = array(
    "Last Name",
    "First Name",
    "Email",
    "Department",
    "Acct Code"
);
fputcsv($output, $headers);

foreach ($res as $row) {
    // Prepare row data with null safety
    $row_data = array(
        $row["lastname"] ?? '',
        $row["firstname"] ?? '',
        $row["email"] ?? '',
        '', // Empty Department column
        ''  // Empty Acct Code column
    );
    
    fputcsv($output, $row_data);
}

fclose($output);
exit;
?>