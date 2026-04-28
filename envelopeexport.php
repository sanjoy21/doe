<?php
include "mysql.php";
$data = "responders";
$table = "responders_esi";

// Secure the ID parameter
$id = (int)($id ?? 0);
$cl = "and c.id = $id";

$sql = "select companyname, c.*, address, floor, city, state, zip from class c, company_esi where company_esi.id = c.companyid $cl ";
$result = db_query_rows($sql);

// Set headers for CSV download
$filename = "envelopeexport_{$id}.csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Write headers
$headers = array(
    "Print?",
    "School",
    "Contact Name",
    "Address Line 1",
    "Address Line 2",
    "City, State Zip",
    "Class Number",
    "Class Date",
    "Date Printed"
);
fputcsv($output, $headers);

foreach ($result as $row) {
    // Build contact name with null safety
    $contact_parts = array();
    if (!empty($row["firstname"])) $contact_parts[] = $row["firstname"];
    if (!empty($row["mi"])) $contact_parts[] = $row["mi"];
    if (!empty($row["lastname"])) $contact_parts[] = $row["lastname"];
    $contactName = implode(" ", $contact_parts);
    
    // Build city/state/zip with null safety
    $cityStateZip_parts = array();
    if (!empty($row["city"])) $cityStateZip_parts[] = $row["city"];
    
    $state_zip = '';
    if (!empty($row["state"])) {
        $state_zip = $row["state"];
    }
    if (!empty($row["zip"])) {
        if ($state_zip) {
            $state_zip .= " " . $row["zip"];
        } else {
            $state_zip = $row["zip"];
        }
    }
    if ($state_zip) {
        $cityStateZip_parts[] = $state_zip;
    }
    $cityStateZip = implode(", ", $cityStateZip_parts);
    
    // Format class date
    $classDate = '';
    if (!empty($row["startdate"])) {
        $classDate = date("m/d/y", strtotime($row["startdate"]));
    }
    
    // Prepare row data with null safety
    $row_data = array(
        "", // Print? (empty checkbox column)
        $row["companyname"] ?? '',
        $contactName,
        $row["address"] ?? '',
        $row["floor"] ?? '',
        $cityStateZip,
        $row["id"] ?? '',
        $classDate,
        "" // Date Printed (empty column)
    );
    
    fputcsv($output, $row_data);
}

fclose($output);
exit;
?>