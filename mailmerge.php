<?php
// Include the database connection file
require_once('mysql.php');

// Initialize the campus ID variable safely
$campusid = $_REQUEST['campusid'];

// Validate campus ID to prevent generating an empty or bad report
if (empty($campusid)) {
    // If running from command line or cron, this error would be helpful
    // If run via web, you might redirect or display an error page.
    // For this utility script, we stop execution.
    die("Error: Campus ID is required.");
}

// --- CSV File Generation Setup (Replacing Excel Writer) ---
$filename = "campuses_" . intval($campusid) . "_" . date('Ymd') . ".csv";

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$output = fopen('php://output', 'w');

// CSV Header
$header = array(
    "Contacts last name", 
    "First name", 
    "Company name", 
    "Address", 
    "City", 
    "ST", 
    "Zip", 
    "Email", 
    "Phone"
);
fputcsv($output, $header);

// --- Fetch Data ---
$sql = "SELECT contactname, companyname, address, city, state, zip, contactemail, contactphone 
        FROM company_esi 
        WHERE campusid = " . intval($campusid) . " 
        AND deleted = 0";

$schools = db_query_rows($sql);

// --- Process Data and Write to CSV ---
foreach ($schools as $row) {
    $contact_name = $row["contactname"];
    
    // Attempt to split the contact name into first and last name
    $exp = explode(" ", $contact_name);
    // The last word is assumed to be the last name
    $last = array_pop($exp); 
    // Remaining words are assumed to be the first name
    $first = implode(" ", $exp); 

    // Construct the data row
    $data_row = array(
        $last,
        $first,
        $row["companyname"],
        $row["address"],
        $row["city"],
        $row["state"],
        $row["zip"],
        // NOTE: The original script duplicated "Zip" and then wrote "Email" and "Phone".
        // The header has 9 columns, so we map to the intended columns.
        $row["contactemail"],
        $row["contactphone"]
    );
    
    fputcsv($output, $data_row);
}

fclose($output);
exit; 
?>