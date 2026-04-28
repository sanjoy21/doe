<?php
include "mysql.php";

// Make sure $id is properly sanitized to prevent SQL injection
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Or if $id comes from elsewhere, ensure it's sanitized:
// $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

$sql = "SELECT * FROM responders_esi r, company_esi c, responder_to_class cl 
        WHERE cl.responderid = r.responderid 
        AND c.id = r.clientid 
        AND c.deleted = 0 
        AND r.deleted = 0 
        AND cl.classid = " . (int)$id . " 
        ORDER BY lastname, firstname";
        
$result = db_query_rows($sql);

// Set headers for CSV download
$filename = "attendees_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fwrite($output, "\xEF\xBB\xBF");

// Write CSV headers
fputcsv($output, [
    "First Name",
    "Last Name", 
    (function_exists('getSchoolStr') ? getSchoolStr("School") : "School") . " Name",
    (function_exists('getSchoolStr') ? getSchoolStr("School") : "School") . " Code",
    "Phone",
    "Email"
]);

foreach ($result as $row) {
    // Write data row to CSV with null safety
    fputcsv($output, [
        $row["firstname"] ?? '',
        $row["lastname"] ?? '',
        $row["companyname"] ?? '',
        $row["schoolcode"] ?? '',
        $row["dayphone"] ?? '',
        $row["email"] ?? ''
    ]);
}

// Close output stream
fclose($output);
exit;
?>